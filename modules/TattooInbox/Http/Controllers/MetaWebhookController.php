<?php

namespace Modules\TattooInbox\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\TattooInbox\Jobs\ProcessMetaWebhook;
use Modules\TattooInbox\Services\MetaInboxService;
use Throwable;

class MetaWebhookController extends Controller
{
    protected $service;

    public function __construct(MetaInboxService $service)
    {
        $this->service = $service;
    }

    public function verify(Request $request)
    {
        $verifyToken = config('tattooinbox.meta.verify_token');

        $mode = $request->query('hub.mode');
        $token = $request->query('hub.verify_token');
        $challenge = $request->query('hub.challenge');

        Log::info('[TATTOOINBOX][WEBHOOK] verify_request', [
            'mode' => $mode,
            'token_present' => !empty($token),
        ]);

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        Log::warning('[TATTOOINBOX][WEBHOOK] invalid_verify_token', [
            'mode' => $mode,
        ]);

        return response('Invalid verify token', 403);
    }

    public function receive(Request $request)
    {
        if (!$this->hasValidSignature($request)) {
            Log::warning('[TATTOOINBOX][WEBHOOK] invalid_signature', [
                'signature_present' => !empty($request->header('X-Hub-Signature-256')),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        $payload = $this->extractPayload($request);

        if (empty($payload)) {
            Log::warning('[TATTOOINBOX][WEBHOOK] payload_vacio', [
                'content_type' => $request->header('Content-Type'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payload vacío',
            ], 200);
        }

        try {
            Log::info('[TATTOOINBOX][WEBHOOK] payload_recibido', [
                'object' => $payload['object'] ?? null,
                'entry_count' => is_array($payload['entry'] ?? null) ? count($payload['entry']) : 0,
            ]);

            ProcessMetaWebhook::dispatch($payload);

            return response()->json([
                'success' => true,
                'queued' => true,
            ], 200);
        } catch (Throwable $e) {
            Log::error('[TATTOOINBOX][WEBHOOK] error_general', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'object' => $payload['object'] ?? null,
                'entry_count' => is_array($payload['entry'] ?? null) ? count($payload['entry']) : 0,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    protected function extractPayload(Request $request): array
    {
        $payload = $request->all();

        if (!empty($payload)) {
            return $payload;
        }

        if ($request->isJson()) {
            $jsonPayload = $request->json()->all();
            if (!empty($jsonPayload)) {
                return $jsonPayload;
            }
        }

        $raw = $request->getContent();

        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    protected function hasValidSignature(Request $request): bool
    {
        $requireSignature = filter_var(config('tattooinbox.meta.require_signature'), FILTER_VALIDATE_BOOLEAN);
        $appSecret = config('tattooinbox.meta.app_secret');

        if (!$requireSignature) {
            return true;
        }

        if (empty($appSecret)) {
            Log::error('[TATTOOINBOX][WEBHOOK] signature_required_without_app_secret');
            return false;
        }

        $signature = (string) $request->header('X-Hub-Signature-256', '');

        if (strpos($signature, 'sha256=') !== 0) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals($expected, $signature);
    }
}
