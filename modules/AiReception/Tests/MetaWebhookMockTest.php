<?php

declare(strict_types=1);
require __DIR__ . '/../Services/MetaWebhook.php';

use Modules\AiReception\Services\MetaWebhook;

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
$modern=json_decode($payload,true);
$modern['entry'][0]['changes'][0]['value']['messages'][0]=['id'=>'wamid.HBgLMTY1MDM4Nzk0MzkVAgASGBQzQTRB=','from_user_id'=>'US.13491208655302741918','timestamp'=>'1795200000','type'=>'audio','audio'=>['id'=>'123456789']];
$m=MetaWebhook::messages(json_encode($modern),['123456'=>'clinica-demo']);
ok(count($m)===1 && $m[0]['from']==='' && $m[0]['sender_identity']==='bsuid:US.13491208655302741918','Business-scoped user identity without phone');
ok($m[0]['media_id']==='123456789' && $m[0]['timestamp']==='1795200000','Authorized media identifier and event timestamp preserved');
ok(substr($m[0]['message_id'],-1)==='=','Official base64 message identifier accepted');

$receipt=json_decode($payload,true);$value=&$receipt['entry'][0]['changes'][0]['value'];unset($value['messages']);$value['statuses']=[['id'=>'wamid.fake.receipt=','status'=>'read','timestamp'=>'1795200000','recipient_id'=>'not-a-tenant']];
$r=MetaWebhook::statuses(json_encode($receipt),['123456'=>'clinica-demo']);ok(count($r)===1 && $r[0]['business_id']==='clinica-demo' && !isset($r[0]['recipient_id']),'receipt uses trusted channel and minimizes recipient data');
ok(MetaWebhook::statuses(json_encode($receipt),['99999'=>'other'])===[],'unknown receipt channel ignored');
$value['statuses'][0]['status']='unexpected';ok(MetaWebhook::statuses(json_encode($receipt),['123456'=>'clinica-demo'])===[],'unknown delivery status ignored');
$value['statuses'][0]['status']='delivered';$value['statuses'][0]['timestamp']='-1';ok(MetaWebhook::statuses(json_encode($receipt),['123456'=>'clinica-demo'])===[],'invalid receipt timestamp rejected');
echo "OK: $checks verificaciones Meta webhook, firma y aislamiento, sin tráfico real.\n";
