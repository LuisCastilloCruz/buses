<?php

require __DIR__ . '/test-bootstrap.php';
$input = json_decode(file_get_contents($argv[3]), true);
while (microtime(true) < $input['at']) {
    usleep(10000);
}
try {
    $result = $engine->execute($actor, $business, 'book', $input['data'], $input['key']);
    echo json_encode(['status' => 200, 'replayed' => $result['replayed']]);
} catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
    echo json_encode(['status' => $e->getStatusCode()]);
} catch (Throwable $e) {
    echo json_encode(['status' => 500, 'class' => get_class($e), 'message' => $e->getMessage()]);
    exit(1);
}
