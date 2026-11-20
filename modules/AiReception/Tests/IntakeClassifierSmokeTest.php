<?php

// Ejecutar sin credenciales, sin red y sin framework:
// php modules/AiReception/Tests/IntakeClassifierSmokeTest.php

require __DIR__ . '/../Services/OpenAiIntakeClassifier.php';

use Modules\AiReception\Services\OpenAiIntakeClassifier;

class FakeIntakeResponse
{
    private $status;
    private $body;

    public function __construct($data, $status = 200)
    {
        $this->status = $status;
        $this->body = json_encode($data);
    }

    public function getStatusCode()
    {
        return $this->status;
    }

    public function getBody()
    {
        return $this->body;
    }
}

class FakeIntakeHttpClient
{
    public $options;
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function request($method, $url, array $options)
    {
        if ($method !== 'POST' || $url !== 'https://api.openai.com/v1/responses') {
            throw new RuntimeException('Unexpected request destination.');
        }
        $this->options = $options;
        return $this->response;
    }
}

function assertIntake($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function fakeOutput($intent, $needsHuman)
{
    return ['output' => [[
        'content' => [[
            'type' => 'output_text',
            'text' => json_encode([
                'intent' => $intent,
                'needs_human' => $needsHuman,
            ]),
        ]],
    ]]];
}

$http = new FakeIntakeHttpClient(new FakeIntakeResponse(fakeOutput('appointment_request', false)));
$service = new OpenAiIntakeClassifier($http, 'test-key', 'gpt-4.1-mini');
$result = $service->classify('Quiero una cita para un tatuaje', [
    'business_name' => 'Estudio Demo',
    'business_type' => 'tatuajes',
]);

assertIntake($result['intent'] === 'appointment_request', 'Wrong intent');
assertIntake($result['needs_human'] === false, 'Wrong handoff status');
assertIntake($http->options['json']['store'] === false, 'Response storage must be disabled');
assertIntake($http->options['json']['text']['format']['strict'] === true, 'Schema must be strict');
assertIntake(!isset($http->options['json']['tools']), 'No booking tools may run');

$badHttp = new FakeIntakeHttpClient(new FakeIntakeResponse(fakeOutput('unknown_value', false)));
$bad = (new OpenAiIntakeClassifier($badHttp, 'test-key'))->classify('Hola');
assertIntake($bad['intent'] === 'human_handoff' && $bad['needs_human'], 'Invalid output must hand off');

$failureWasCaught = false;
try {
    (new OpenAiIntakeClassifier($http, 'test-key'))->classify('');
} catch (InvalidArgumentException $error) {
    $failureWasCaught = true;
}
assertIntake($failureWasCaught, 'Empty message must fail');

$failureWasCaught = false;
try {
    (new OpenAiIntakeClassifier(new FakeIntakeHttpClient(
        new FakeIntakeResponse(['error' => 'bad request'], 401)
    ), 'test-key'))->classify('Hola');
} catch (RuntimeException $error) {
    $failureWasCaught = true;
}
assertIntake($failureWasCaught, 'HTTP failures must fail closed');

echo "OK: 4 pruebas de clasificacion, privacidad y manejo de errores.\n";
