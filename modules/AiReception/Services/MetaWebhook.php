<?php

namespace Modules\AiReception\Services;

use InvalidArgumentException;

/**
 * Validates Meta's webhook signature and resolves business from trusted phone-ID map.
 * No network I/O, no automatic WhatsApp response, no tenant data access.
 */
final class MetaWebhook
{
    public static function verifiedMessages(string $rawBody, string $header, string $appSecret, array $phoneToBusiness): array
    {
        self::verify($rawBody, $header, $appSecret);
        return self::messages($rawBody, $phoneToBusiness);
    }

    public static function challenge(string $mode, string $token, string $challenge, string $expectedToken): string
    {
        if ($expectedToken === '' || $mode !== 'subscribe' || !hash_equals($expectedToken, $token)
            || !preg_match('/^[A-Za-z0-9_-]{1,200}$/D', $challenge)) {
            throw new InvalidArgumentException('Verificación del webhook rechazada.');
        }
        return $challenge;
    }

    public static function verify(string $rawBody, string $header, string $appSecret): void
    {
        if ($appSecret === '' || strlen($rawBody) > 256000 || !preg_match('/^sha256=[a-fA-F0-9]{64}$/D', $header)) {
            throw new InvalidArgumentException('Firma Meta ausente o inválida.');
        }
        $signed = 'sha256=' . hash_hmac('sha256', $rawBody, $appSecret);
        if (!hash_equals($signed, strtolower($header))) {
            throw new InvalidArgumentException('No coincide la firma del webhook.');
        }
    }

    /**
     * @param array<string,string> $phoneToBusiness Map stored server-side, NEVER from incoming payload.
     * @return array<int,array<string,string>>
     */
    public static function messages(string $rawBody, array $phoneToBusiness): array
    {
        if (strlen($rawBody) > 256000) {
            throw new InvalidArgumentException('Evento Meta demasiado grande.');
        }
        $payload = json_decode($rawBody, true);
        if (!is_array($payload) || ($payload['object'] ?? '') !== 'whatsapp_business_account') {
            throw new InvalidArgumentException('Evento Meta no compatible.');
        }
        $result = [];
        foreach (self::rows($payload['entry'] ?? []) as $entry) {
            if (!is_array($entry)) continue;
            foreach (self::rows($entry['changes'] ?? []) as $change) {
                if (!is_array($change)) continue;
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }
                $value = $change['value'] ?? [];
                if (!is_array($value) || !is_array($value['metadata'] ?? null)) continue;
                $phoneId = $value['metadata']['phone_number_id'] ?? null;
                if (!is_string($phoneId) || !isset($phoneToBusiness[$phoneId])
                    || !is_string($phoneToBusiness[$phoneId]) || trim($phoneToBusiness[$phoneId]) === '') {
                    // Never guess the tenant from the sender or recipient display name.
                    continue;
                }
                foreach (self::rows($value['messages'] ?? []) as $message) {
                    if (!is_array($message)) continue;
                    $id = $message['id'] ?? null;
                    $sender = $message['from'] ?? null;
                    if (!is_string($id) || !preg_match('/^[\w.-]{1,160}$/D', $id)
                        || !is_string($sender) || !preg_match('/^[0-9]{8,16}$/D', $sender)) {
                        continue;
                    }
                    $type = $message['type'] ?? '';
                    if ($type !== 'text' && $type !== 'audio') {
                        continue;
                    }
                    $body = $type === 'text' ? ($message['text']['body'] ?? '') : '';
                    if (!is_string($body) || strlen($body) > 2000) {
                        continue;
                    }
                    $result[] = [
                        'business_id' => $phoneToBusiness[$phoneId],
                        'phone_number_id' => $phoneId,
                        'message_id' => $id,
                        'from' => $sender,
                        'type' => $type,
                        'text' => $body,
                    ];
                }
            }
        }
        return $result;
    }

    private static function rows($value): array
    {
        if (!is_array($value)) throw new InvalidArgumentException('Estructura Meta invalida.');
        return $value;
    }
}
