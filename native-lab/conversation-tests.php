<?php
require __DIR__.'/test-bootstrap.php';
$n=0;function check($ok,$label){global $n;if(!$ok)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
$service=app(Modules\AiReception\Native\ConversationCoordinator::class);$tools=app(Modules\AiReception\Native\ReceptionTools::class);$person=(int)$db->table('persons')->where('type','customers')->value('id');$key='conv-'.bin2hex(random_bytes(10));
$before=$db->table('aireception_bookings')->count();$result=$service->turn($person,'Quiero una cita',null,$key);
check(strpos($result['reply'],'Simulacion local:')===0,'deterministic reply identified as simulation');
check($db->table('aireception_bookings')->count()===$before,'conversation does not create appointment');
$id=$result['conversation_id'];$count=$db->table('aireception_messages')->where('conversation_id',$id)->count();
check($service->turn($person,'Quiero una cita',null,$key)===$result && $db->table('aireception_messages')->where('conversation_id',$id)->count()===$count,'turn idempotency no duplicate messages');
try{$service->turn($person,'Otro texto',null,$key);throw new LogicException('Expected conflict');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===409,'idempotency key rejects different payload');}
$message=$db->table('aireception_messages')->where('conversation_id',$id)->where('direction','incoming')->first();check($message->is_encrypted && strpos($message->body,'Quiero una cita')===false && app('encrypter')->decrypt($message->body)==='Quiero una cita','message encrypted at rest');
$clinical=$service->turn($person,'Tengo dolor y sangrado',$id,'clinical-'.bin2hex(random_bytes(8)));check(!empty($clinical['handoff']) && $db->table('aireception_conversations')->where('id',$id)->value('status')==='human','clinical request routed to human without provider');
$paused=$service->turn($person,'Quiero una cita',$id,'paused-'.bin2hex(random_bytes(8)));check(!empty($paused['handoff']),'human pause persists on next message');
try{$tools->run('prepare_booking',['tenant_id'=>999],$person);throw new LogicException('Unexpected tool');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===422,'tool rejects tenant supplied by model');}
try{$tools->run('execute_sql',[],$person);throw new LogicException('Unexpected tool');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===422,'unknown model tool rejected');}
$reply=$tools->simulated('Quiero cancelar una cita',$person);check(strpos($reply['reply_hint'],'cancelacion')!==false,'cancellation intent precedes generic appointment intent');
$other=(int)$db->table('persons')->where('type','customers')->where('id','<>',$person)->value('id');if(!$other)throw new RuntimeException('Second fictional customer required');
try{$service->turn($other,'Hola',$id,'wrong-'.bin2hex(random_bytes(8)));throw new LogicException('Cross person');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===404,'conversation cannot switch customer identity');}
echo "RESULT $n conversation MySQL checks; providers disabled.\n";
