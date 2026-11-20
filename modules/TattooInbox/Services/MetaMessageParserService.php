<?php

namespace Modules\TattooInbox\Services;

use Illuminate\Support\Facades\Log;

class MetaMessageParserService
{
    public function parse(array $payload): array
    {
        $items = [];
        $entries = $payload['entry'] ?? [];

        Log::info('[TATTOOINBOX][PARSER] parse:start', [
            'object' => $payload['object'] ?? null,
            'entry_count' => is_array($entries) ? count($entries) : 0,
        ]);

        if (!is_array($entries) || empty($entries)) {
            return [];
        }

        foreach ($entries as $entryIndex => $entryRow) {
            if (!is_array($entryRow)) {
                continue;
            }

            $whatsAppItems = $this->parseWhatsAppEntry($entryRow);
            $instagramItems = $this->parseInstagramEntry($entryRow);

            Log::info('[TATTOOINBOX][PARSER] entry:parsed', [
                'entry_index' => $entryIndex,
                'whatsapp_count' => count($whatsAppItems),
                'instagram_count' => count($instagramItems),
            ]);

            $items = array_merge($items, $whatsAppItems, $instagramItems);
        }

        Log::info('[TATTOOINBOX][PARSER] parse:end', [
            'items_count' => count($items),
        ]);

        return array_values($items);
    }

    protected function parseWhatsAppEntry(array $entryRow): array
    {
        $items = [];
        $changes = $entryRow['changes'] ?? [];

        if (!is_array($changes) || empty($changes)) {
            return [];
        }

        foreach ($changes as $changeIndex => $change) {
            if (!is_array($change)) {
                continue;
            }

            $field = $change['field'] ?? null;
            $value = $change['value'] ?? [];

            if ($field !== 'messages' || !is_array($value)) {
                continue;
            }

            $messages = $value['messages'] ?? [];
            $contacts = $value['contacts'] ?? [];
            $metadata = $value['metadata'] ?? [];

            $contactName = $this->extractWhatsAppContactName($contacts);
            $phoneNumberId = data_get($metadata, 'phone_number_id');
            $displayPhoneNumber = data_get($metadata, 'display_phone_number');

            Log::info('[TATTOOINBOX][PARSER] whatsapp:change', [
                'change_index' => $changeIndex,
                'phone_number_id' => $phoneNumberId,
                'display_phone_number' => $displayPhoneNumber,
                'messages_count' => is_array($messages) ? count($messages) : 0,
            ]);

            if (!is_array($messages) || empty($messages)) {
                continue;
            }

            foreach ($messages as $messageIndex => $message) {
                if (!is_array($message)) {
                    continue;
                }

                $normalized = $this->normalizeWhatsAppMessage(
                    $message,
                    $contactName,
                    [
                        'entry_id' => $entryRow['id'] ?? null,
                        'phone_number_id' => $phoneNumberId,
                        'display_phone_number' => $displayPhoneNumber,
                    ]
                );

                if ($normalized !== null) {
                    Log::info('[TATTOOINBOX][PARSER] whatsapp:item_ok', [
                        'message_index' => $messageIndex,
                        'external_user_id' => $normalized['external_user_id'] ?? null,
                        'external_message_id' => $normalized['external_message_id'] ?? null,
                        'message_type' => $normalized['message_type'] ?? null,
                        'destination_key' => $normalized['destination_key'] ?? null,
                        'destination_value' => $normalized['destination_value'] ?? null,
                    ]);

                    $items[] = $normalized;
                }
            }
        }

        return $items;
    }

    protected function parseInstagramEntry(array $entryRow): array
    {
        $items = [];
        $messagingEvents = $entryRow['messaging'] ?? [];
        $entryId = $entryRow['id'] ?? null;

        if (!is_array($messagingEvents) || empty($messagingEvents)) {
            return [];
        }

        foreach ($messagingEvents as $eventIndex => $event) {
            if (!is_array($event)) {
                continue;
            }

            $senderId = data_get($event, 'sender.id');
            $recipientId = data_get($event, 'recipient.id');
            $message = $event['message'] ?? null;

            if (!is_array($message)) {
                continue;
            }

            $text = $message['text'] ?? null;
            $attachments = $message['attachments'] ?? [];
            if (!is_array($attachments)) {
                $attachments = [];
            }

            $mediaUrl = null;
            $mediaMime = null;
            $messageType = 'text';
            $hasReference = false;
            $referenceCount = count($attachments);

            if (!empty($attachments)) {
                $firstAttachment = $attachments[0] ?? [];
                $attachmentType = $firstAttachment['type'] ?? 'attachment';
                $mediaUrl = data_get($firstAttachment, 'payload.url');
                $mediaMime = $attachmentType;
                $messageType = $attachmentType;
                $hasReference = true;
            }

            if (empty($senderId) && empty($text) && !$hasReference) {
                continue;
            }

            $normalized = [
                'platform' => 'instagram',
                'destination_key' => 'instagram_page_id',
                'destination_value' => $entryId ?: $recipientId,
                'external_user_id' => $senderId,
                'external_message_id' => $message['mid'] ?? null,
                'external_thread_id' => data_get($event, 'conversation.id'),
                'sender_name' => null,
                'sender_username' => data_get($event, 'sender.username'),
                'message_type' => $messageType,
                'text' => $this->sanitizeText($text),
                'media_url' => $mediaUrl,
                'media_mime' => $mediaMime,
                'has_reference' => $hasReference,
                'reference_count' => $referenceCount,
                'raw' => $event,
                'meta' => [
                    'entry_id' => $entryId,
                    'recipient_id' => $recipientId,
                    'sender_id' => $senderId,
                ],
            ];

            Log::info('[TATTOOINBOX][PARSER] instagram:item_ok', [
                'event_index' => $eventIndex,
                'external_user_id' => $normalized['external_user_id'],
                'external_message_id' => $normalized['external_message_id'],
                'message_type' => $normalized['message_type'],
                'destination_key' => $normalized['destination_key'],
                'destination_value' => $normalized['destination_value'],
            ]);

            $items[] = $normalized;
        }

        return $items;
    }

    protected function normalizeWhatsAppMessage(array $message, ?string $contactName, array $context = []): ?array
    {
        $messageType = $message['type'] ?? 'text';
        $from = $message['from'] ?? null;
        $externalMessageId = $message['id'] ?? null;

        if (empty($from) && empty($externalMessageId)) {
            return null;
        }

        $text = null;
        $mediaUrl = null;
        $mediaMime = null;
        $hasReference = false;
        $referenceCount = 0;

        switch ($messageType) {
            case 'text':
                $text = data_get($message, 'text.body');
                break;

            case 'image':
                $text = data_get($message, 'image.caption');
                $mediaUrl = data_get($message, 'image.id');
                $mediaMime = data_get($message, 'image.mime_type', 'image');
                $hasReference = true;
                $referenceCount = 1;
                break;

            case 'video':
                $text = data_get($message, 'video.caption');
                $mediaUrl = data_get($message, 'video.id');
                $mediaMime = data_get($message, 'video.mime_type', 'video');
                $hasReference = true;
                $referenceCount = 1;
                break;

            case 'document':
                $text = data_get($message, 'document.caption');
                $mediaUrl = data_get($message, 'document.id');
                $mediaMime = data_get($message, 'document.mime_type', 'document');
                $hasReference = true;
                $referenceCount = 1;
                break;

            case 'audio':
                $mediaUrl = data_get($message, 'audio.id');
                $mediaMime = data_get($message, 'audio.mime_type', 'audio');
                $hasReference = true;
                $referenceCount = 1;
                break;

            case 'sticker':
                $mediaUrl = data_get($message, 'sticker.id');
                $mediaMime = 'sticker';
                $hasReference = true;
                $referenceCount = 1;
                break;

            case 'interactive':
                $text = $this->extractInteractiveText($message);
                break;

            case 'button':
                $text = data_get($message, 'button.text');
                break;

            default:
                $text = $this->extractFallbackText($message);
                break;
        }

        $text = $this->sanitizeText($text);

        if (empty($from) && empty($text) && !$hasReference) {
            return null;
        }

        return [
            'platform' => 'whatsapp',
            'destination_key' => 'whatsapp_phone_number_id',
            'destination_value' => $context['phone_number_id'] ?? null,
            'external_user_id' => $from,
            'external_message_id' => $externalMessageId,
            'external_thread_id' => null,
            'sender_name' => $contactName,
            'sender_username' => null,
            'message_type' => $messageType,
            'text' => $text,
            'media_url' => $mediaUrl,
            'media_mime' => $mediaMime,
            'has_reference' => $hasReference,
            'reference_count' => $referenceCount,
            'raw' => $message,
            'meta' => [
                'entry_id' => $context['entry_id'] ?? null,
                'phone_number_id' => $context['phone_number_id'] ?? null,
                'display_phone_number' => $context['display_phone_number'] ?? null,
            ],
        ];
    }

    protected function extractWhatsAppContactName($contacts): ?string
    {
        if (!is_array($contacts) || empty($contacts)) {
            return null;
        }

        return data_get($contacts, '0.profile.name');
    }

    protected function extractInteractiveText(array $message): ?string
    {
        $interactive = $message['interactive'] ?? [];

        if (!is_array($interactive)) {
            return null;
        }

        $buttonReplyTitle = data_get($interactive, 'button_reply.title');
        if (!empty($buttonReplyTitle)) {
            return $buttonReplyTitle;
        }

        $listReplyTitle = data_get($interactive, 'list_reply.title');
        if (!empty($listReplyTitle)) {
            return $listReplyTitle;
        }

        $listReplyDescription = data_get($interactive, 'list_reply.description');
        if (!empty($listReplyDescription)) {
            return $listReplyDescription;
        }

        return null;
    }

    protected function extractFallbackText(array $message): ?string
    {
        if (!empty(data_get($message, 'text.body'))) {
            return data_get($message, 'text.body');
        }

        if (!empty(data_get($message, 'button.text'))) {
            return data_get($message, 'button.text');
        }

        if (!empty(data_get($message, 'image.caption'))) {
            return data_get($message, 'image.caption');
        }

        if (!empty(data_get($message, 'video.caption'))) {
            return data_get($message, 'video.caption');
        }

        if (!empty(data_get($message, 'document.caption'))) {
            return data_get($message, 'document.caption');
        }

        return null;
    }

    protected function sanitizeText($text): ?string
    {
        if ($text === null) {
            return null;
        }

        if (is_array($text) || is_object($text)) {
            return null;
        }

        $text = trim((string) $text);

        return $text !== '' ? $text : null;
    }
}