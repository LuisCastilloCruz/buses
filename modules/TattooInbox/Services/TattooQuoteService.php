<?php

namespace Modules\TattooInbox\Services;

use Modules\TattooInbox\Models\TattooInboxConversation;
use Modules\TattooInbox\Models\TattooInboxQuote;
use Modules\TattooInbox\Models\TattooInboxQuoteRule;

class TattooQuoteService
{
    public function buildOrUpdateQuote(TattooInboxConversation $conversation): array
    {
        $compiled = $this->compileConversationData($conversation);

        $quote = TattooInboxQuote::firstOrNew([
            'conversation_id' => $conversation->id,
            'lead_id' => $conversation->lead_id,
            'status' => 'draft',
        ]);

        $quote->fill([
            'body_zone' => $compiled['body_zone'],
            'style' => $compiled['style'],
            'size_cm' => $compiled['size_cm'],
            'size_text' => $compiled['size_text'],
            'is_color' => $compiled['is_color'],
            'is_coverup' => $compiled['is_coverup'],
            'detail_level' => $compiled['detail_level'],
            'has_reference' => $compiled['has_reference'],
            'reference_count' => $compiled['reference_count'],
            'customer_idea' => $compiled['customer_idea'],
            'currency' => 'PEN',
            'meta' => $compiled,
        ]);

        $missing = $this->detectMissingFields($compiled);

        if (empty($missing)) {
            $prices = $this->calculatePrice($compiled);

            $quote->price_min = $prices['price_min'];
            $quote->price_max = $prices['price_max'];
            $quote->suggested_price = $prices['suggested_price'];
            $quote->status = 'quoted';
            $quote->quoted_at = now();

            $conversation->quote_status = 'quoted';
        } else {
            $quote->status = 'draft';
            $conversation->quote_status = 'pending_data';
        }

        $quote->save();
        $conversation->save();

        return [
            'quote' => $quote,
            'compiled' => $compiled,
            'missing' => $missing,
            'reply' => empty($missing)
                ? $this->buildQuotedReply($quote, $compiled)
                : $this->buildMissingDataReply($missing, $compiled),
        ];
    }

    public function compileConversationData(TattooInboxConversation $conversation): array
    {
        $messages = $conversation->messages()
            ->where('direction', 'in')
            ->orderBy('id')
            ->get();

        $allText = trim($messages->pluck('message_text')->filter()->implode("\n"));

        $nonTextMediaCount = $messages->filter(function ($row) {
            return isset($row->message_type) && $row->message_type !== 'text';
        })->count();

        $mediaUrlCount = $messages->filter(function ($row) {
            return !empty($row->media_url);
        })->count();

        $hasReference = ($nonTextMediaCount > 0) || ($mediaUrlCount > 0);

        $referenceCount = $messages->filter(function ($row) {
            return (isset($row->message_type) && $row->message_type !== 'text') || !empty($row->media_url);
        })->count();

        $size = $this->extractSize($allText);

        return [
            'body_zone' => $this->extractBodyZone($allText),
            'style' => $this->extractStyle($allText),
            'size_cm' => $size['size_cm'],
            'size_text' => $size['size_text'],
            'is_color' => $this->extractIsColor($allText),
            'is_coverup' => $this->extractIsCoverup($allText),
            'detail_level' => $this->extractDetailLevel($allText),
            'has_reference' => $hasReference,
            'reference_count' => $referenceCount,
            'customer_idea' => $allText,
        ];
    }

    public function detectMissingFields(array $compiled): array
    {
        $missing = [];

        if (empty($compiled['body_zone'])) {
            $missing[] = 'zona del cuerpo';
        }

        if (empty($compiled['style'])) {
            $missing[] = 'estilo';
        }

        if (empty($compiled['size_cm'])) {
            $missing[] = 'tamaño aproximado en cm';
        }

        if (!$compiled['has_reference']) {
            $missing[] = 'imagen o referencia';
        }

        return $missing;
    }

    public function calculatePrice(array $compiled): array
    {
        $rules = $this->getRules();

        $sizeCm = (float) $compiled['size_cm'];
        $priceMin = 0;
        $priceMax = 0;

        if ($sizeCm <= $rules['small_max_cm']) {
            $priceMin = $rules['small_min_price'];
            $priceMax = $rules['small_max_price'];
        } elseif ($sizeCm <= $rules['medium_max_cm']) {
            $priceMin = $rules['medium_min_price'];
            $priceMax = $rules['medium_max_price'];
        } elseif ($sizeCm <= $rules['large_max_cm']) {
            $priceMin = $rules['large_min_price'];
            $priceMax = $rules['large_max_price'];
        } else {
            $priceMin = $rules['xlarge_min_price'];
            $priceMax = $rules['xlarge_max_price'];
        }

        $multiplier = 1;

        if ($compiled['is_color']) {
            $multiplier *= $rules['color_multiplier'];
        }

        if (in_array($compiled['style'], ['realismo', 'realism', 'microrealismo'])) {
            $multiplier *= $rules['realism_multiplier'];
        }

        if ($compiled['is_coverup']) {
            $multiplier *= $rules['coverup_multiplier'];
        }

        if (in_array($compiled['body_zone'], ['costillas', 'cuello', 'mano', 'dedos', 'cara', 'pie'])) {
            $multiplier *= $rules['difficult_zone_multiplier'];
        }

        if ($compiled['detail_level'] === 'alto') {
            $multiplier *= $rules['high_detail_multiplier'];
        }

        $priceMin = round($priceMin * $multiplier, 2);
        $priceMax = round($priceMax * $multiplier, 2);
        $suggested = round(($priceMin + $priceMax) / 2, 2);

        if ($priceMin < $rules['minimum_base_price']) {
            $priceMin = (float) $rules['minimum_base_price'];
        }

        return [
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'suggested_price' => $suggested,
        ];
    }

    protected function buildMissingDataReply(array $missing, array $compiled): string
    {
        return "¡Gracias! Para cotizarte mejor necesito estos datos:\n- " .
            implode("\n- ", $missing) .
            "\n\nEjemplo: “10 cm en antebrazo, fine line, en negro” y me mandas la referencia.";
    }

    protected function buildQuotedReply(TattooInboxQuote $quote, array $compiled): string
    {
        $style = $compiled['style'] ?: 'personalizado';
        $zone = $compiled['body_zone'] ?: 'zona por confirmar';
        $size = $compiled['size_text'] ?: ($compiled['size_cm'] . ' cm');

        return "¡Perfecto! Con los datos que me enviaste, tu tatuaje tendría una cotización estimada de S/ {$quote->price_min} a S/ {$quote->price_max}.\n\n" .
            "Resumen:\n" .
            "- Zona: {$zone}\n" .
            "- Tamaño: {$size}\n" .
            "- Estilo: {$style}\n" .
            "- " . ($compiled['is_color'] ? 'A color' : 'En negro') . "\n" .
            "- " . ($compiled['is_coverup'] ? 'Es cover-up' : 'No es cover-up') . "\n\n" .
            "Este monto es referencial y puede ajustarse según el nivel final de detalle. Si deseas, te paso al siguiente paso para reservar.";
    }

    protected function getRules(): array
    {
        $defaults = config('tattooinbox.quote_defaults', []);

        $dbRules = TattooInboxQuoteRule::query()
            ->where('active', true)
            ->pluck('rule_value', 'rule_key')
            ->toArray();

        return array_merge($defaults, $dbRules);
    }

    protected function extractSize(string $text): array
    {
        $text = mb_strtolower($text);

        if (preg_match('/(\d{1,3})(?:\s?x\s?(\d{1,3}))?\s?(cm|cent[ií]metros?)/u', $text, $m)) {
            $first = (float) $m[1];
            $second = isset($m[2]) ? (float) $m[2] : null;

            $sizeCm = $second ? max($first, $second) : $first;
            $sizeText = $second ? "{$first}x{$second} cm" : "{$first} cm";

            return [
                'size_cm' => $sizeCm,
                'size_text' => $sizeText,
            ];
        }

        if (strpos($text, 'pequeño') !== false || strpos($text, 'pequeno') !== false) {
            return ['size_cm' => 5, 'size_text' => 'pequeño'];
        }

        if (strpos($text, 'mediano') !== false) {
            return ['size_cm' => 10, 'size_text' => 'mediano'];
        }

        if (strpos($text, 'grande') !== false) {
            return ['size_cm' => 20, 'size_text' => 'grande'];
        }

        return ['size_cm' => null, 'size_text' => null];
    }

    protected function extractBodyZone(string $text): ?string
    {
        $zones = [
            'antebrazo', 'brazo', 'hombro', 'espalda', 'pecho', 'pierna',
            'muslo', 'pantorrilla', 'cuello', 'mano', 'dedos', 'costillas',
            'tobillo', 'pie', 'cara', 'nuca', 'abdomen', 'muñeca', 'muneca'
        ];

        $text = mb_strtolower($text);

        foreach ($zones as $zone) {
            if (strpos($text, $zone) !== false) {
                return $zone === 'muneca' ? 'muñeca' : $zone;
            }
        }

        return null;
    }

    protected function extractStyle(string $text): ?string
    {
        $styles = [
            'fine line' => 'fine line',
            'fineline' => 'fine line',
            'blackwork' => 'blackwork',
            'realismo' => 'realismo',
            'realism' => 'realism',
            'microrealismo' => 'microrealismo',
            'anime' => 'anime',
            'lettering' => 'lettering',
            'minimalista' => 'minimalista',
            'tradicional' => 'tradicional',
            'japones' => 'japones',
            'japonés' => 'japonés',
            'geométrico' => 'geométrico',
            'geometrico' => 'geométrico',
        ];

        $text = mb_strtolower($text);

        foreach ($styles as $needle => $value) {
            if (strpos($text, $needle) !== false) {
                return $value;
            }
        }

        return null;
    }

    protected function extractIsColor(string $text): bool
    {
        $text = mb_strtolower($text);

        return strpos($text, 'a color') !== false
            || strpos($text, 'con color') !== false
            || strpos($text, 'colores') !== false
            || strpos($text, 'full color') !== false
            || strpos($text, 'colorido') !== false;
    }

    protected function extractIsCoverup(string $text): bool
    {
        $text = mb_strtolower($text);

        return strpos($text, 'cover') !== false
            || strpos($text, 'cover up') !== false
            || strpos($text, 'cover-up') !== false
            || strpos($text, 'tapar') !== false
            || strpos($text, 'cubrir otro tatuaje') !== false;
    }

    protected function extractDetailLevel(string $text): string
    {
        $text = mb_strtolower($text);

        if (
            strpos($text, 'muy detallado') !== false ||
            strpos($text, 'bastante detalle') !== false ||
            strpos($text, 'realista') !== false
        ) {
            return 'alto';
        }

        if (
            strpos($text, 'simple') !== false ||
            strpos($text, 'minimalista') !== false
        ) {
            return 'bajo';
        }

        return 'medio';
    }
}