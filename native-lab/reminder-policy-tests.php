<?php
require __DIR__.'/test-bootstrap.php';$n=0;function check($x,$label){global $n;if(!$x)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
$p=app(Modules\AiReception\Native\ReminderPolicy::class);$previous=$p->get();$input=['offsets'=>implode(',',$previous['offsets']),'window_start'=>$previous['window_start'],'window_end'=>$previous['window_end'],'enabled'=>$previous['enabled']];
try{
 $p->save(['offsets'=>'1440,120,60,120','window_start'=>'00:00','window_end'=>'23:59','enabled'=>true]);check($p->get()['offsets']===[1440,120,60],'custom offsets normalized without duplicates');
 try{$p->save(['offsets'=>'-1','window_start'=>'00:00','window_end'=>'23:59','enabled'=>true]);throw new LogicException('Invalid offset accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===422,'negative reminder offset rejected');}
 $b=$db->table('aireception_bookings')->where('business_id',$business)->where('status','confirmed')->first();if(!$b)throw new RuntimeException('Confirmed fixture required');
 $id=$db->table('aireception_reminders')->insertGetId(['booking_id'=>$b->id,'scheduled_at'=>date('Y-m-d H:i:s',time()-30),'status'=>'simulated_pending']);
 $p->save(['offsets'=>'120','window_start'=>'00:00','window_end'=>'23:59','enabled'=>false]);check(app(Modules\AiReception\Native\ReminderProcessor::class)->process($id)==='paused' && (int)$db->table('aireception_reminders')->where('id',$id)->value('attempts')===0,'paused automation preserves pending reminder');
 $clock=date('H:i');$window=$clock==='00:00'?'12:00':'00:00';$p->save(['offsets'=>'120','window_start'=>$window,'window_end'=>$window,'enabled'=>true]);check(app(Modules\AiReception\Native\ReminderProcessor::class)->process($id)==='outside_window','quiet hours prevent processing');
 $db->table('aireception_reminders')->where('id',$id)->update(['status'=>'cancelled']);
}finally{$p->save($input);}
echo "RESULT $n reminder policy MySQL checks.\n";
