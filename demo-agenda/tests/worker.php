<?php

$app = (require dirname(__DIR__) . '/bootstrap.php');
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$job = json_decode(file_get_contents($argv[1]), true);
while (microtime(true) < $job['at']) {
    usleep(10000);
}
try {
    $result = app(Modules\AiReception\Agenda\BookingService::class)->execute($job['actor'], 1, 'book', $job['data'], $job['key']);
    echo json_encode(['status' => 200, 'result' => $result]);
} catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
    echo json_encode(['status' => $e->getStatusCode()]);
}
