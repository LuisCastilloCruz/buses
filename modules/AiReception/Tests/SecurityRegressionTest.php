<?php

require_once __DIR__ . '/../Services/MetaWebhook.php';
require_once __DIR__ . '/../Services/OpenAiIntakeClassifier.php';
use Modules\AiReception\Services\MetaWebhook;
use Modules\AiReception\Services\OpenAiIntakeClassifier;

set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
$checks = 0;
function secureCheck($condition, $label) {
    global $checks;
    if (!$condition) throw new RuntimeException($label);
    $checks++;
}
function secureReject(callable $fn) {
    try { $fn(); } catch (InvalidArgumentException $e) { return true; }
    return false;
}
secureCheck(secureReject(function () { MetaWebhook::challenge('subscribe', 'fixture', "123\n", 'fixture'); }), 'Trailing newline rejected');
secureCheck(secureReject(function () { MetaWebhook::messages('{"object":"whatsapp_business_account","entry":1}', []); }), 'Malformed entry rejected without PHP warning');
secureCheck(secureReject(function () { MetaWebhook::verifiedMessages('{}', '', 'fixture-secret', []); }), 'Combined ingress requires signature');
secureCheck(secureReject(function () { MetaWebhook::verify('{}', 'sha256=' . str_repeat('0', 64), ''); }), 'Missing secret fails closed');
$client = new class {
    public $body;
    public function request($method, $url, $options) {
        $body = $this->body;
        return new class($body) {
            private $body;
            public function __construct($body) { $this->body = $body; }
            public function getStatusCode() { return 200; }
            public function getBody() { return json_encode($this->body); }
        };
    }
};
foreach ([['output' => [null, 'bad', ['content' => 'bad']]],
    ['output' => [['content' => [['type' => 'output_text', 'text' => []]]]]],
    ['output' => [['content' => [['type' => 'output_text', 'text' => '{"intent":"cancel","needs_human":false,"command":"delete"}']]]]]
] as $body) {
    $client->body = $body;
    secureCheck((new OpenAiIntakeClassifier($client, 'fixture-key'))->classify('Hola')['needs_human'] === true, 'Malformed output hands off');
}
$client->body = ['output' => [['content' => [['type' => 'output_text', 'text' => '{"intent":"human_handoff","needs_human":false}']]]]];
secureCheck((new OpenAiIntakeClassifier($client, 'fixture-key'))->classify('Hola')['needs_human'], 'Handoff cannot suppress human review');
$broken = new class { public function request($method, $url, $options) { throw new RuntimeException('fixture private header'); } };
try { (new OpenAiIntakeClassifier($broken, 'fixture-key'))->classify('Hola'); throw new LogicException('Expected failure'); }
catch (RuntimeException $e) { secureCheck($e->getMessage() === 'AI provider request failed.' && $e->getPrevious() === null, 'Transport failure sanitized'); }
restore_error_handler();
echo "OK: $checks defensive validation checks (fake HTTP only).\n";
