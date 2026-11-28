<?php
foreach(['ReceptionAct','ReceptionTools','ReceptionReply'] as $c)require __DIR__.'/../modules/AiReception/Native/'.$c.'.php';
use Modules\AiReception\Native\ReceptionTools;use Modules\AiReception\Native\ReceptionReply;
$n=0;function ok72($x,$name){global $n;if(!$x)throw new RuntimeException($name);echo 'PASS '.++$n.' '.$name.PHP_EOL;}
$t=new ReceptionTools;$s=['service_id'=>7,'professional_id'=>3,'day'=>'2027-02-12'];$state=['explicit_fields'=>['service_id','professional_id','day']];
foreach(['Quisiera una cita para el servicio elegido','Necesito agendar el servicio que pedi','Me gustaria reservar ese servicio'] as $text){$names=array_column($t->availableDefinitions($s,$text,$state,true),'name');ok72($names===['availability'],'verified current request advances rather than summarizing');}
ok72(array_column($t->availableDefinitions($s,'Necesito reservar',$state,false),'name')===['human_handoff'],'no adapter cannot offer a reservation');
ok72(!in_array('request_summary',array_column($t->availableDefinitions($s,'Cual es su direccion',$state),'name'),true),'summary unavailable without summary request');
ok72(in_array('request_summary',array_column($t->availableDefinitions($s,'Puedes recapitular lo que elegi',$state),'name'),true),'explicit recap preserved');
ok72(array_column($t->availableDefinitions(['service_id'=>7],'Quisiera ese servicio',['explicit_fields'=>['service_id']],true),'name')===['clarify_selection'],'missing fields cannot query or book');
$r=['reply'=>'Atencion clinica inmediata; no puedo diagnosticar.','handoff'=>true];ok72(ReceptionReply::present($r,'No puedo respirar')===$r,'clinical warning never overwritten by presentation');
$r=['reply'=>'Confirmada cita #7 para 2027-02-12','booking'=>['id'=>7]];ok72(ReceptionReply::present($r,'CONFIRMAR ABCD1234')===$r,'presentation preserves actual confirmation');
ok72(strpos(ReceptionReply::present(['reply'=>'Handoff','handoff'=>true],'Estoy molesto')['reply'],'Lamento')===0,'complaint receives empathetic handoff');
echo "RESULT $n V72 policy/presentation assertions\n";

$names=array_column($t->availableDefinitions($s,'Mejor con otro profesional',['intent'=>'book','offered'=>['10:00']]),'name');ok72(!in_array('prepare_rescheduling',$names,true)&&!in_array('prepare_cancellation',$names,true),'no existing booking identity means no change tools');
ok72(in_array('knowledge_search',array_column($t->availableDefinitions($s,'Cuales son las politicas de cancelacion',[],false),'name'),true),'cancellation policy is information even without booking adapter');
ok72(!in_array('availability',array_column($t->availableDefinitions($s,'Quiero revisar los servicios antes de decidir',[],false),'name'),true),'catalogue browsing never creates booking capability');
echo "FINAL $n V72 policy assertions\n";
