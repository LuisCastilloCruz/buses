<?php
require __DIR__.'/test-bootstrap.php';$n=0;function check($x,$label){global $n;if(!$x)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
$f=json_decode(file_get_contents(dirname(__DIR__).'/.native-runtime/webhook-fixture.json'),true);$system=app('db')->connection('system');
$events=$system->table('aireception_webhook_events')->where('channel_id',$f['id'])->get();check(count($events)>0,'signed event audit exists');
check($db->table('aireception_inbound_messages')->where('channel_id',$f['id'])->count()===count($events),'one tenant message per unique event');
foreach($events as $e)check($e->state==='processed','event processed by real worker');
$m=$db->table('aireception_inbound_messages')->where('channel_id',$f['id'])->where('state','awaiting_identity')->first();check($m && $m->state==='awaiting_identity','unverified sender cannot reserve automatically');check(strpos($m->encrypted_content,'Solicitud ficticia')===false && strpos($m->sender_identity,'US.')===false,'message and sender identity protected at rest');
(new Modules\AiReception\Jobs\ProcessInbound((int)$events[0]->id))->handle();check($db->table('aireception_inbound_messages')->where('channel_id',$f['id'])->count()===count($events),'worker replay does not duplicate message');
try{app(Modules\AiReception\Native\ChannelOwnership::class)->claim($f['phone_id'],$business,'tattoo_inbox');throw new RuntimeException('Double receiver allowed');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===409,'same channel cannot be connected to both inboxes');}
$env=app(Hyn\Tenancy\Environment::class);$previous=$env->tenant();try{foreach(['mf_v5_tattoo','mf_v5_commerce'] as $uuid){$env->tenant(Hyn\Tenancy\Models\Website::where('uuid',$uuid)->firstOrFail());check(app('db')->connection('tenant')->table('aireception_inbound_messages')->where('channel_id',$f['id'])->count()===0,'other business cannot receive channel messages');}}finally{$env->tenant($previous);}
echo "RESULT $n inbound MySQL checks.\n";
