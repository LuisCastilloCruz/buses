<?php

namespace Modules\TattooInbox\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\TattooInbox\Models\TattooInboxConversation;
use Modules\TattooInbox\Models\TattooInboxLead;
use Modules\TattooInbox\Models\TattooInboxMessage;
use Modules\TattooInbox\Models\TattooInboxSetting;

class MetaInboxService
{
    /**
     * @var \Modules\TattooInbox\Services\MetaMessageParserService
     */
    protected $parser;

    /**
     * @var \Modules\TattooInbox\Services\TattooQuoteService
     */
    protected $quoteService;

    /**
     * @var \Modules\TattooInbox\Services\TattooInboxAiService
     */
    protected $aiService;

    /**
     * @var \Modules\TattooInbox\Services\MetaTenantResolverService
     */
    protected $tenantResolver;

    /**
     * @var \Hyn\Tenancy\Environment
     */
    protected $tenancyEnvironment;
    protected $tenantContext;

    public function __construct(
        MetaMessageParserService $parser,
        TattooQuoteService $quoteService,
        TattooInboxAiService $aiService,
        MetaTenantResolverService $tenantResolver,
        Environment $tenancyEnvironment,
        TenantContext $tenantContext
    ) {
        $this->parser = $parser;
        $this->quoteService = $quoteService;
        $this->aiService = $aiService;
        $this->tenantResolver = $tenantResolver;
        $this->tenancyEnvironment = $tenancyEnvironment;
        $this->tenantContext = $tenantContext;
    }

    /**
     * Procesa el payload completo recibido desde Meta.
     *
     * @param array $payload
     * @return array
     */
    public function processWebhook(array $payload): array
    {
        $parsedItems = $this->parser->parse($payload);

        Log::info('[TATTOOINBOX] processWebhook:parsed', [
            'parsed_count' => count($parsedItems),
        ]);

        $processed = [];

        foreach ($parsedItems as $itemIndex => $item) {
            try {
                $processed[] = $this->processSingleItem($item, $itemIndex);
            } catch (Exception $e) {
                Log::error('[TATTOOINBOX] Error procesando item del webhook', [
                    'item_index' => $itemIndex,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'platform' => $item['platform'] ?? null,
                    'external_message_id' => $item['external_message_id'] ?? null,
                ]);

                $processed[] = [
                    'success' => false,
                    'error' => true,
                    'message' => $e->getMessage(),
                    'item_index' => $itemIndex,
                    'platform' => $item['platform'] ?? null,
                    'external_message_id' => $item['external_message_id'] ?? null,
                ];
            }
        }

        return $processed;
    }

    /**
     * Procesa un mensaje normalizado y lo guarda en el tenant correcto.
     *
     * @param array $item
     * @param int|null $itemIndex
     * @return array
     *
     * @throws \Exception
     */
    public function processSingleItem(array $item, ?int $itemIndex = null): array
    {
        $this->validateParsedItem($item);

        $resolved = $this->tenantResolver->resolveFromParsedItem($item);

        if (!$resolved || empty($resolved['website'])) {
            Log::warning('[TATTOOINBOX] No se pudo resolver tenant para item', [
                'item_index' => $itemIndex,
                'platform' => $item['platform'] ?? null,
                'destination_key' => $item['destination_key'] ?? null,
                'destination_value' => $item['destination_value'] ?? null,
                'external_message_id' => $item['external_message_id'] ?? null,
            ]);

            return [
                'success' => false,
                'skipped' => true,
                'reason' => 'tenant_not_resolved',
                'platform' => $item['platform'] ?? null,
                'destination_key' => $item['destination_key'] ?? null,
                'destination_value' => $item['destination_value'] ?? null,
            ];
        }

        $website = $resolved['website'];
        $hostname = $resolved['hostname'] ?? null;
        $channel = $resolved['channel'] ?? null;

        Log::info('[TATTOOINBOX] Tenant resuelto para item', [
            'item_index' => $itemIndex,
            'tenant_website_id' => $website->id ?? null,
            'tenant_hostname' => $hostname ? $hostname->fqdn : null,
            'platform' => $item['platform'] ?? null,
            'destination_key' => $item['destination_key'] ?? null,
            'destination_value' => $item['destination_value'] ?? null,
            'mapping_id' => $channel->id ?? null,
        ]);

        return $this->tenantContext->run($website, function () use ($item, $website, $hostname, $channel) {
            return DB::connection(config('tenancy.db.tenant-connection-name', 'tenant'))->transaction(function () use ($item, $website, $hostname, $channel) {
                $settings = TattooInboxSetting::current();

                $existingMessage = $this->findExistingIncomingMessage($item);
                if ($existingMessage) {
                    Log::info('[TATTOOINBOX] Mensaje entrante duplicado detectado', [
                        'tenant_website_id' => $website->id ?? null,
                        'hostname' => $hostname ? $hostname->fqdn : null,
                        'existing_message_id' => $existingMessage->id,
                        'external_message_id' => $item['external_message_id'] ?? null,
                        'platform' => $item['platform'] ?? null,
                    ]);

                    return [
                        'success' => true,
                        'duplicated' => true,
                        'tenant_website_id' => $website->id ?? null,
                        'tenant_hostname' => $hostname ? $hostname->fqdn : null,
                        'lead_id' => $existingMessage->lead_id,
                        'conversation_id' => $existingMessage->conversation_id,
                        'message_id' => $existingMessage->id,
                    ];
                }

                $lead = $this->findOrCreateLead($item);
                $conversation = $this->findOrCreateConversation($lead, $item, $settings);

                $message = TattooInboxMessage::create([
                    'conversation_id' => $conversation->id,
                    'lead_id' => $lead->id,
                    'channel' => $item['platform'],
                    'direction' => 'in',
                    'message_type' => $item['message_type'] ?? 'text',
                    'external_message_id' => $item['external_message_id'] ?? null,
                    'message_text' => $item['text'] ?? null,
                    'media_url' => $item['media_url'] ?? null,
                    'media_mime' => $item['media_mime'] ?? null,
                    'payload' => $item['raw'] ?? null,
                    'status' => 'received',
                    'received_at' => now(),
                ]);

                $this->storeIncomingMediaIfNeeded($message, $item, $settings);

                $lead->last_message_at = now();
                $lead->meta = array_merge($lead->meta ?? [], [
                    'last_platform' => $item['platform'] ?? null,
                    'last_destination_key' => $item['destination_key'] ?? null,
                    'last_destination_value' => $item['destination_value'] ?? null,
                    'last_channel_mapping_id' => $channel->id ?? null,
                ]);
                $lead->save();

                $conversation->last_message_at = now();
                $conversation->last_incoming_at = now();
                $conversation->status = 'open';

                if (empty($conversation->stage)) {
                    $conversation->stage = 'new';
                }

                $conversation->meta = array_merge($conversation->meta ?? [], [
                    'meta_channel_mapping_id' => $channel->id ?? null,
                    'last_incoming_platform' => $item['platform'] ?? null,
                    'last_destination_key' => $item['destination_key'] ?? null,
                    'last_destination_value' => $item['destination_value'] ?? null,
                ]);
                $conversation->save();

                $quoteResult = $this->quoteService->buildOrUpdateQuote($conversation);

                if ($conversation->quote_status === 'quoted') {
                    $conversation->stage = 'quoted';
                } elseif (empty($conversation->stage) || $conversation->stage === 'new') {
                    $conversation->stage = 'waiting_data';
                }

                $conversation->save();

                $outMessage = null;
                $replyText = null;

                $globalBotEnabled = (bool) $settings->bot_enabled;
                $conversationBotEnabled = (bool) $conversation->bot_enabled;

                if ($globalBotEnabled && $conversationBotEnabled) {
                    $replyText = $this->buildAutoReply(
                        $conversation,
                        $item,
                        $quoteResult,
                        $settings
                    );

                    if (!empty($replyText)) {
                        $sendResult = $this->sendTextMessage(
                            $item['platform'],
                            $lead->external_user_id,
                            $replyText
                        );

                        $outMessage = TattooInboxMessage::create([
                            'conversation_id' => $conversation->id,
                            'lead_id' => $lead->id,
                            'channel' => $item['platform'],
                            'direction' => 'out',
                            'message_type' => 'text',
                            'external_message_id' => $sendResult['external_message_id'] ?? null,
                            'message_text' => $replyText,
                            'payload' => $sendResult,
                            'is_automated' => true,
                            'status' => ($sendResult['success'] ?? false) ? 'sent' : 'failed',
                            'sent_at' => now(),
                        ]);

                        $conversation->last_outgoing_at = now();
                        $conversation->last_message_at = now();
                        $conversation->save();
                    }
                }

                return [
                    'success' => true,
                    'duplicated' => false,
                    'tenant_website_id' => $website->id ?? null,
                    'tenant_hostname' => $hostname ? $hostname->fqdn : null,
                    'mapping_id' => $channel->id ?? null,
                    'lead_id' => $lead->id,
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                    'out_message_id' => optional($outMessage)->id,
                    'bot_enabled' => $globalBotEnabled && $conversationBotEnabled,
                ];
            });
        });
    }

    /**
     * @param array $item
     * @return void
     */
    protected function validateParsedItem(array $item): void
    {
        $required = [
            'platform',
            'external_user_id',
            'message_type',
            'destination_key',
            'destination_value',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $item) || $item[$field] === null || $item[$field] === '') {
                throw new Exception("Falta el campo requerido '{$field}' en el item normalizado.");
            }
        }

        if (!in_array($item['platform'], ['whatsapp', 'instagram'], true)) {
            throw new Exception("Plataforma no soportada: {$item['platform']}");
        }
    }

    /**
     * Evita guardar el mismo incoming dos veces.
     *
     * @param array $item
     * @return \Modules\TattooInbox\Models\TattooInboxMessage|null
     */
    protected function findExistingIncomingMessage(array $item)
    {
        $externalMessageId = $item['external_message_id'] ?? null;

        if (!$externalMessageId) {
            return null;
        }

        return TattooInboxMessage::query()
            ->where('channel', $item['platform'])
            ->where('direction', 'in')
            ->where('external_message_id', $externalMessageId)
            ->first();
    }

    /**
     * @param \Modules\TattooInbox\Models\TattooInboxConversation $conversation
     * @param array $item
     * @param array $quoteResult
     * @param \Modules\TattooInbox\Models\TattooInboxSetting $settings
     * @return string|null
     */
    protected function buildAutoReply(
        TattooInboxConversation $conversation,
        array $item,
        array $quoteResult,
        TattooInboxSetting $settings
    ): ?string {
        $fallbackReply = $quoteResult['reply'] ?? null;

        $aiEnabled = (bool) config('tattooinbox.ai.enabled');
        $autoQuoteEnabled = (bool) $settings->auto_quote_enabled;

        if (!$autoQuoteEnabled) {
            return null;
        }

        if (!$aiEnabled) {
            return $fallbackReply;
        }

        $messages = $conversation->messages()
            ->orderBy('id', 'asc')
            ->limit(20)
            ->get();

        $summary = $messages->map(function ($m) {
            $who = $m->direction === 'in' ? 'cliente' : 'asistente';
            return strtoupper($who) . ': ' . ($m->message_text ?: '[sin texto]');
        })->implode("\n");

        $quote = $quoteResult['quote'] ?? null;
        $compiled = $quoteResult['compiled'] ?? [];
        $missing = $quoteResult['missing'] ?? [];

        $quoteSummary = [
            'quote_status' => $conversation->quote_status,
            'stage' => $conversation->stage,
            'body_zone' => $compiled['body_zone'] ?? null,
            'style' => $compiled['style'] ?? null,
            'size_text' => $compiled['size_text'] ?? null,
            'is_color' => $compiled['is_color'] ?? null,
            'is_coverup' => $compiled['is_coverup'] ?? null,
            'has_reference' => $compiled['has_reference'] ?? null,
            'missing' => $missing,
            'price_min' => optional($quote)->price_min,
            'price_max' => optional($quote)->price_max,
            'suggested_price' => optional($quote)->suggested_price,
        ];

        $aiResponse = $this->aiService->generateReply([
            'last_user_message' => $item['text'] ?? '',
            'summary' => $summary . "\n\nDATOS ESTRUCTURADOS:\n" . json_encode($quoteSummary, JSON_UNESCAPED_UNICODE),
            'image_urls' => $this->collectConversationImageUrls($conversation),
        ], $settings);

        if (!empty($aiResponse['success']) && !empty($aiResponse['reply'])) {
            return trim($aiResponse['reply']);
        }

        return $fallbackReply;
    }

    /**
     * @param array $item
     * @return \Modules\TattooInbox\Models\TattooInboxLead
     */
    protected function findOrCreateLead(array $item): TattooInboxLead
    {
        $lead = TattooInboxLead::firstOrNew([
            'channel' => $item['platform'],
            'external_user_id' => $item['external_user_id'],
        ]);

        if (!$lead->exists) {
            $lead->status = 'new';
        }

        if (!empty($item['sender_name']) && empty($lead->full_name)) {
            $lead->full_name = $item['sender_name'];
        }

        if (($item['platform'] ?? null) === 'whatsapp') {
            $lead->phone = $item['external_user_id'];
        }

        if (($item['platform'] ?? null) === 'instagram' && !empty($item['sender_username']) && empty($lead->instagram_username)) {
            $lead->instagram_username = $item['sender_username'];
        }

        $lead->meta = array_merge($lead->meta ?? [], [
            'last_platform' => $item['platform'] ?? null,
            'last_destination_key' => $item['destination_key'] ?? null,
            'last_destination_value' => $item['destination_value'] ?? null,
            'last_sender_name' => $item['sender_name'] ?? null,
        ]);

        $lead->save();

        return $lead;
    }

    /**
     * @param \Modules\TattooInbox\Models\TattooInboxLead $lead
     * @param array $item
     * @param \Modules\TattooInbox\Models\TattooInboxSetting $settings
     * @return \Modules\TattooInbox\Models\TattooInboxConversation
     */
    protected function findOrCreateConversation(
        TattooInboxLead $lead,
        array $item,
        TattooInboxSetting $settings
    ): TattooInboxConversation {
        $conversation = TattooInboxConversation::query()
            ->where('lead_id', $lead->id)
            ->where('channel', $item['platform'])
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (!$conversation) {
            $conversation = TattooInboxConversation::create([
                'lead_id' => $lead->id,
                'channel' => $item['platform'],
                'external_thread_id' => $item['external_thread_id'] ?? null,
                'status' => 'open',
                'quote_status' => 'pending_data',
                'bot_enabled' => (bool) $settings->bot_enabled,
                'stage' => 'new',
                'last_message_at' => now(),
                'meta' => [
                    'meta_destination_key' => $item['destination_key'] ?? null,
                    'meta_destination_value' => $item['destination_value'] ?? null,
                ],
            ]);
        } elseif (!empty($item['external_thread_id']) && empty($conversation->external_thread_id)) {
            $conversation->external_thread_id = $item['external_thread_id'];
            $conversation->save();
        }

        return $conversation;
    }

    /**
     * @param string $platform
     * @param string $recipientId
     * @param string $text
     * @return array
     */
    public function sendTextMessage(string $platform, string $recipientId, string $text): array
    {
        try {
            if ($platform === 'whatsapp') {
                return $this->sendWhatsAppText($recipientId, $text);
            }

            if ($platform === 'instagram') {
                return $this->sendInstagramText($recipientId, $text);
            }

            return [
                'success' => false,
                'message' => 'Plataforma no soportada',
            ];
        } catch (Exception $e) {
            Log::error('[TATTOOINBOX] Error enviando mensaje', [
                'platform' => $platform,
                'recipient' => $recipientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param string $to
     * @param string $text
     * @return array
     */
    protected function sendWhatsAppText(string $to, string $text): array
    {
        $settings = TattooInboxSetting::current();

        $token = $settings->whatsapp_access_token ?: config('tattooinbox.meta.whatsapp_access_token');
        $phoneNumberId = $settings->whatsapp_phone_number_id ?: config('tattooinbox.meta.whatsapp_phone_number_id');
        $version = $settings->meta_graph_version ?: config('tattooinbox.meta.graph_version', 'v22.0');

        if (!$token || !$phoneNumberId) {
            return [
                'success' => false,
                'message' => 'WhatsApp no configurado para este tatuador',
            ];
        }

        $url = "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages";

        $result = $this->postJsonWithToken($url, $token, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $text,
            ],
        ]);

        Log::info('[TATTOOINBOX] sendWhatsAppText', [
            'url' => $url,
            'to' => $to,
            'status_code' => $result['status_code'],
            'success' => $result['success'],
        ]);

        return [
            'success' => $result['success'],
            'status_code' => $result['status_code'],
            'response' => $result['json'],
            'external_message_id' => data_get($result['json'], 'messages.0.id'),
        ];
    }

    /**
     * @param string $recipientId
     * @param string $text
     * @return array
     */
    protected function sendInstagramText(string $recipientId, string $text): array
    {
        $settings = TattooInboxSetting::current();

        $token = $settings->meta_page_access_token ?: config('tattooinbox.meta.page_access_token');
        $version = $settings->meta_graph_version ?: config('tattooinbox.meta.graph_version', 'v22.0');
        $pageId = $settings->instagram_page_id ?: config('tattooinbox.meta.instagram_page_id');

        if (!$token || !$pageId) {
            return [
                'success' => false,
                'message' => 'Instagram no configurado para este tatuador',
            ];
        }

        $url = "https://graph.facebook.com/{$version}/{$pageId}/messages";

        $result = $this->postJsonWithToken($url, $token, [
            'recipient' => [
                'id' => $recipientId,
            ],
            'message' => [
                'text' => $text,
            ],
            'messaging_type' => 'RESPONSE',
        ]);

        Log::info('[TATTOOINBOX] sendInstagramText', [
            'url' => $url,
            'recipient_id' => $recipientId,
            'status_code' => $result['status_code'],
            'success' => $result['success'],
        ]);

        return [
            'success' => $result['success'],
            'status_code' => $result['status_code'],
            'response' => $result['json'],
            'external_message_id' => data_get($result['json'], 'message_id'),
        ];
    }

    protected function storeIncomingMediaIfNeeded(
        TattooInboxMessage $message,
        array $item,
        TattooInboxSetting $settings
    ): void {
        if (empty($item['media_url']) || ($item['message_type'] ?? 'text') === 'text') {
            return;
        }

        try {
            if (($item['platform'] ?? null) === 'whatsapp') {
                $token = $settings->whatsapp_access_token ?: config('tattooinbox.meta.whatsapp_access_token');
                $version = $settings->meta_graph_version ?: config('tattooinbox.meta.graph_version', 'v22.0');

                if (!$token) {
                    return;
                }

                $mediaInfoUrl = "https://graph.facebook.com/{$version}/{$item['media_url']}";
                $mediaInfo = $this->getJsonWithToken($mediaInfoUrl, $token);
                $downloadUrl = data_get($mediaInfo['json'], 'url');

                if (!$mediaInfo['success'] || !$downloadUrl) {
                    Log::warning('[TATTOOINBOX] No se pudo obtener URL de media WhatsApp', [
                        'message_id' => $message->id,
                        'status_code' => $mediaInfo['status_code'],
                    ]);
                    return;
                }

                $binary = $this->getBinaryWithToken($downloadUrl, $token);

                if (!$binary['success']) {
                    Log::warning('[TATTOOINBOX] No se pudo descargar media WhatsApp', [
                        'message_id' => $message->id,
                        'status_code' => $binary['status_code'],
                    ]);
                    return;
                }

                $mime = data_get($mediaInfo['json'], 'mime_type') ?: $item['media_mime'];
                $extension = $this->extensionFromMime($mime);
                $path = sprintf(
                    'tattoo-inbox/%s/%s.%s',
                    $message->conversation_id,
                    $message->id,
                    $extension
                );

                Storage::disk('public')->put($path, $binary['body']);

                $message->media_url = Storage::disk('public')->url($path);
                $message->media_mime = $mime;
                $message->save();
            }
        } catch (\Throwable $e) {
            Log::warning('[TATTOOINBOX] Error guardando media entrante', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function collectConversationImageUrls(TattooInboxConversation $conversation): array
    {
        return $conversation->messages()
            ->whereNotNull('media_url')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->filter(function (TattooInboxMessage $message) {
                $mime = (string) $message->media_mime;
                $type = (string) $message->message_type;

                return strpos($mime, 'image/') === 0
                    || $type === 'image'
                    || preg_match('/\.(jpg|jpeg|png|webp|gif)(\?.*)?$/i', (string) $message->media_url);
            })
            ->pluck('media_url')
            ->filter(function ($url) {
                return preg_match('/^https?:\/\//i', (string) $url);
            })
            ->reverse()
            ->values()
            ->all();
    }

    protected function postJsonWithToken(string $url, string $token, array $payload): array
    {
        return $this->requestJson('POST', $url, $token, [
            'json' => $payload,
        ]);
    }

    protected function getJsonWithToken(string $url, string $token): array
    {
        return $this->requestJson('GET', $url, $token);
    }

    protected function requestJson(string $method, string $url, string $token, array $options = []): array
    {
        $client = new Client(['timeout' => 45]);

        try {
            $response = $client->request($method, $url, array_merge([
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ], $options));

            $json = json_decode((string) $response->getBody(), true);

            return [
                'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
                'status_code' => $response->getStatusCode(),
                'json' => is_array($json) ? $json : [],
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $body = $response ? (string) $response->getBody() : null;
            $json = $body ? json_decode($body, true) : null;

            return [
                'success' => false,
                'status_code' => $response ? $response->getStatusCode() : 0,
                'json' => is_array($json) ? $json : ['message' => $e->getMessage()],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'json' => ['message' => $e->getMessage()],
            ];
        }
    }

    protected function getBinaryWithToken(string $url, string $token): array
    {
        $client = new Client(['timeout' => 60]);

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            return [
                'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
                'status_code' => $response->getStatusCode(),
                'body' => (string) $response->getBody(),
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'status_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 0,
                'body' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'body' => null,
            ];
        }
    }

    protected function extensionFromMime(?string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'video/mp4' => 'mp4',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'application/pdf' => 'pdf',
        ];

        return $map[$mime] ?? 'bin';
    }
}
