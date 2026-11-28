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
    // Reproduce MySQL coercion with a fictitious token, without exposing its value.
    $db->table('users')->where('id', 1)->update(['api_token'=>'1'.bin2hex(random_bytes(24))]);
    $request=Illuminate\Http\Request::create('/','POST',[],[],[],['CONTENT_TYPE'=>'application/json'],json_encode(['api_token'=>1]));
    $legacy=new Illuminate\Auth\TokenGuard(auth('api')->getProvider(),$request);
    $legacyAccepted=$legacy->validate(['api_token'=>1]);
    echo 'OBSERVATION legacy numeric credential on fictitious MySQL row: '.($legacyAccepted?'ACCEPTED':'REJECTED').PHP_EOL;
    $strict=new App\Auth\StrictTokenGuard(auth('api')->getProvider(),$request);
    if ($strict->validate(['api_token'=>1]) || $strict->user()!==null) {
        throw new RuntimeException('Numeric credential crossed the strict guard');
    }
    $checks++;
} finally {
    $db->rollBack();
}
echo 'PASS '.$checks.' Laravel guard integration checks; temporary credential rolled back.'.PHP_EOL;
