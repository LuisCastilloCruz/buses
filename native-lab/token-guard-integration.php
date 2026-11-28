<?php
require __DIR__.'/test-bootstrap.php';
$checks = 0;
foreach (['api', 'system_api'] as $name) {
    $guard = auth($name);
    if (!$guard instanceof App\Auth\StrictTokenGuard) {
        throw new RuntimeException('Strict guard not registered: '.$name);
    }
    $checks++;
    foreach ([true, 1, 1.5, ['invalid']] as $token) {
        $db->flushQueryLog();
        $db->enableQueryLog();
        $system = app('db')->connection('system');
        $system->flushQueryLog();
        $system->enableQueryLog();
        if ($guard->validate(['api_token'=>$token]) !== false) {
            throw new RuntimeException('Invalid credential accepted');
        }
        if (count($db->getQueryLog()) || count($system->getQueryLog())) {
            throw new RuntimeException('Invalid token reached a database');
        }
        $checks++;
    }
}
// Test a real tenant provider without persisting a credential change.
$db->beginTransaction();
try {
    $token = bin2hex(random_bytes(32));
    $db->table('users')->where('id', 1)->update(['api_token'=>$token]);
    if (!auth('api')->validate(['api_token'=>$token])) {
        throw new RuntimeException('Valid tenant token rejected');
    }
    $checks++;
    if (auth('api')->validate(['api_token'=>'nonexistent-fixture-token'])) {
        throw new RuntimeException('Unknown token accepted');
    }
    $checks++;
} finally {
    $db->rollBack();
}
echo 'PASS '.$checks.' Laravel guard integration checks; temporary credential rolled back.'.PHP_EOL;
