<?php
namespace Modules\AiReception\ProvidersApi;

/** Official Cloud API transport; caller supplies trusted channel and consent state. */
final class WhatsAppClient
{
    private $http;
    public function __construct($http) { $this->http = $http; }
    public function send(array $channel, string $recipient, array $message, bool $consent, ?int $lastInbound, int $now): string
    {
        if (($channel['mode'] ?? '') !== 'sandbox' || empty($channel['enabled']) || empty($channel['token'])) throw new \RuntimeException('Channel disabled.');
        if (!preg_match('/^v[0-9]{2}\.0$/D', $channel['version'] ?? '') || !preg_match('/^[0-9]{5,30}$/D', $channel['phone_number_id'] ?? '') || !preg_match('/^[0-9]{6,15}$/D', $recipient)) throw new \InvalidArgumentException('Invalid channel.');
        if (!$consent) throw new \RuntimeException('Consent required.');
        $type = $message['type'] ?? '';
        if ($type === 'text') {
            if (!$lastInbound || $lastInbound > $now || $now-$lastInbound >= 86400) throw new \RuntimeException('Approved template required outside service window.');
            if (!is_string($message['body'] ?? null) || trim($message['body']) === '' || strlen($message['body']) > 4096) throw new \InvalidArgumentException('Invalid text.');
            $content=['text'=>['body'=>$message['body'], 'preview_url'=>false]];
        } elseif ($type === 'template') {
            $name=$message['name'] ?? ''; $language=$message['language'] ?? '';
            if (!in_array($name.':'.$language, $channel['approved_templates'] ?? [], true)) throw new \RuntimeException('Template not approved for channel.');
            $content=['template'=>['name'=>$name, 'language'=>['code'=>$language]]];
        } else throw new \InvalidArgumentException('Unsupported message.');
        try {
            $r=$this->http->request('POST', 'https://graph.facebook.com/'.$channel['version'].'/'.$channel['phone_number_id'].'/messages', ['headers'=>['Authorization'=>'Bearer '.$channel['token']], 'json'=>['messaging_product'=>'whatsapp','recipient_type'=>'individual','to'=>$recipient,'type'=>$type]+$content,'connect_timeout'=>5,'timeout'=>20,'allow_redirects'=>false,'http_errors'=>false]);
            if ($r->getStatusCode() !== 200) throw new \RuntimeException();
            $body=json_decode((string)$r->getBody(), true); $id=$body['messages'][0]['id'] ?? null;
            if (!is_string($id) || !$id || strlen($id)>255) throw new \RuntimeException();
            return $id;
        } catch (\Throwable $e) { throw new \RuntimeException('Delivery outcome unknown; reconcile before retry.'); }
    }
}
