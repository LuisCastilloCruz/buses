<?php

declare(strict_types=1);
require __DIR__ . '/../Services/MetaWebhook.php';

use Modules\AiReception\Demo\MetaWebhook;

$checks = 0;
function ok($condition, string $name): void
{
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($name);
}
function no(callable $fn): bool
{
    try { $fn(); } catch (InvalidArgumentException $e) { return true; }
    return false;
}
$secret = 'fake-app-secret';
$payload = json_encode([
    'object' => 'whatsapp_business_account',
    'entry' => [['changes' => [[
        'field' => 'messages',
        'value' => [
            'metadata' => ['phone_number_id' => '123456'],
            'messages' => [['id' => 'wamid.fake.1', 'from' => '51999888777', 'type' => 'text', 'text' => ['body' => 'Quiero una cita']]],
        ],
    ]]]],
]);
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
MetaWebhook::verify($payload, $signature, $secret);
ok(true, 'Firma correcta aceptada');
ok(no(static function () use ($payload, $secret) { MetaWebhook::verify($payload, 'sha256=123', $secret); }), 'Firma corta rechazada');
ok(no(static function () use ($payload, $signature) { MetaWebhook::verify($payload, $signature, 'secret-equivocado'); }), 'Firma con secreto equivocado rechazada');
ok(MetaWebhook::challenge('subscribe', 'token-ok', '123456', 'token-ok') === '123456', 'Challenge válido');
ok(no(static function () { MetaWebhook::challenge('subscribe', 'invalido', '123456', 'token-ok'); }), 'Challenge falso rechazado');
$messages = MetaWebhook::messages($payload, ['123456' => 'clinica-demo']);
ok(count($messages) === 1 && $messages[0]['business_id'] === 'clinica-demo', 'Webhook asignado al negocio por número empresarial');
ok(count(MetaWebhook::messages($payload, ['98765' => 'otro-tenant'])) === 0, 'Número empresarial desconocido descartado');
ok($messages[0]['message_id'] === 'wamid.fake.1', 'ID conservado para futura deduplicación');
$bad = json_encode(['object' => 'nada', 'entry' => []]);
ok(no(static function () use ($bad) { MetaWebhook::messages($bad, []); }), 'Evento no WhatsApp rechazado');
echo "OK: $checks verificaciones Meta webhook, firma y aislamiento, sin tráfico real.\n";
