<?php
require __DIR__.'/test-bootstrap.php';
$config=['phone_id'=>'99000000001','waba_id'=>'99000000002','label'=>'Canal FICTICIO V4','app_secret'=>'fake-sandbox-app-secret-v4','verify_token'=>'fake-sandbox-verification-v4','access_token'=>'','version'=>'v23.0','enabled'=>true];
$id=app(Modules\AiReception\Native\ChannelSettings::class)->save($config);
file_put_contents(dirname(__DIR__).'/.native-runtime/webhook-fixture.json',json_encode(['id'=>$id,'website'=>$business]+$config));
echo "PASS fictional channel configured; secrets remain private; no external request.\n";
