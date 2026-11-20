<?php

namespace Modules\TattooInbox\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Modules\TattooInbox\Models\TattooInboxPromptRule;
use Modules\TattooInbox\Models\TattooInboxSetting;

class TattooInboxAiService
{
    public function buildSystemPrompt(TattooInboxSetting $settings = null): string
    {
        $settings = $settings ?: TattooInboxSetting::current();

        $promptRules = TattooInboxPromptRule::where('rule_type', 'prompt_rule')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $blockedTopics = TattooInboxPromptRule::where('rule_type', 'blocked_topic')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $parts = [];
        $parts[] = 'Eres un asistente comercial para un tatuador independiente.';
        $parts[] = 'Hablas con clientes por WhatsApp o Instagram para entender su idea, pedir datos faltantes, revisar referencias visuales y orientar una cotizacion.';
        $parts[] = 'Analiza las imagenes como referencias aproximadas: identifica estilo, zona probable si se menciona, nivel de detalle, color, complejidad, sombras, lineas finas, realismo, cover-up o dudas.';
        $parts[] = 'No prometas precios definitivos. Usa rangos estimados y deja claro que el artista puede ajustar el precio tras revisar la referencia final.';
        $parts[] = 'Si falta informacion importante, pide solo lo necesario: zona del cuerpo, tamano aproximado en cm, estilo, color/negro, si es cover-up y referencia.';
        $parts[] = 'Si el cliente quiere reservar o hablar directamente con el tatuador, sugiere derivacion humana.';

        if (!empty($settings->artist_name)) {
            $parts[] = "Nombre del tatuador: {$settings->artist_name}.";
        }

        if (!empty($settings->studio_name)) {
            $parts[] = "Nombre comercial: {$settings->studio_name}.";
        }

        if (!empty($settings->assistant_tone)) {
            $parts[] = "Tono deseado: {$settings->assistant_tone}.";
        }

        if (!empty($settings->assistant_prompt)) {
            $parts[] = "Instrucciones adicionales del tatuador: {$settings->assistant_prompt}";
        }

        if (!empty($settings->booking_instructions)) {
            $parts[] = "Indicaciones para reservas: {$settings->booking_instructions}";
        }

        if (!empty($settings->aftercare_instructions)) {
            $parts[] = "Indicaciones post cuidado: {$settings->aftercare_instructions}";
        }

        foreach ($promptRules as $rule) {
            $parts[] = "Regla personalizada: {$rule->rule_key} => {$rule->rule_value}";
        }

        if ($blockedTopics->count()) {
            $parts[] = 'Temas bloqueados:';
            foreach ($blockedTopics as $rule) {
                $parts[] = "- {$rule->rule_value}";
            }
        }

        return implode("\n", $parts);
    }

    public function generateReply(array $conversationContext, TattooInboxSetting $settings = null): array
    {
        $settings = $settings ?: TattooInboxSetting::current();

        $enabled = (bool) ($settings->openai_enabled ?? config('tattooinbox.ai.enabled'));
        $provider = config('tattooinbox.ai.provider');
        $apiKey = $settings->openai_api_key ?: config('tattooinbox.ai.api_key');
        $model = $settings->openai_model ?: config('tattooinbox.ai.model', 'gpt-4.1-mini');

        if (!$enabled || !$apiKey || $provider !== 'openai') {
            return [
                'success' => false,
                'message' => 'IA no configurada',
                'reply' => null,
            ];
        }

        $systemPrompt = $this->buildSystemPrompt($settings);
        $userMessage = $conversationContext['last_user_message'] ?? '';
        $summary = $conversationContext['summary'] ?? '';
        $imageUrls = $this->normalizeImageUrls($conversationContext['image_urls'] ?? []);

        $inputContent = [
            [
                'type' => 'input_text',
                'text' => "Resumen de conversacion y datos estructurados:\n{$summary}\n\nUltimo mensaje del cliente:\n{$userMessage}\n\nResponde al cliente en espanol, breve y comercial. Si puedes estimar rango, mencionalo. Si falta informacion, pidela con claridad.",
            ],
        ];

        if ((bool) $settings->openai_vision_enabled) {
            foreach ($imageUrls as $imageUrl) {
                $inputContent[] = [
                    'type' => 'input_image',
                    'image_url' => $imageUrl,
                    'detail' => $settings->openai_vision_detail ?: 'auto',
                ];
            }
        }

        $result = $this->postJsonWithToken('https://api.openai.com/v1/responses', $apiKey, [
            'model' => $model,
            'instructions' => $systemPrompt,
            'input' => [
                [
                    'role' => 'user',
                    'content' => $inputContent,
                ],
            ],
            'temperature' => 0.5,
        ]);

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => 'Error llamando IA',
                'reply' => null,
                'raw' => $result['json'],
            ];
        }

        return [
            'success' => true,
            'reply' => $this->extractResponseText($result['json']),
            'raw' => $result['json'],
        ];
    }

    protected function normalizeImageUrls(array $imageUrls): array
    {
        return array_values(array_filter(array_unique(array_map(function ($url) {
            $url = trim((string) $url);
            return preg_match('/^https?:\/\//i', $url) ? $url : null;
        }, $imageUrls))));
    }

    protected function extractResponseText(array $json): ?string
    {
        if (!empty($json['output_text'])) {
            return trim((string) $json['output_text']);
        }

        $outputs = $json['output'] ?? [];

        if (is_array($outputs)) {
            foreach ($outputs as $output) {
                foreach (($output['content'] ?? []) as $content) {
                    if (!empty($content['text'])) {
                        return trim((string) $content['text']);
                    }
                }
            }
        }

        return null;
    }

    protected function postJsonWithToken(string $url, string $token, array $payload): array
    {
        $client = new Client(['timeout' => 60]);

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

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
}
