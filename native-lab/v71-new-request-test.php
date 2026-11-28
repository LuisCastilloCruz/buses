<?php
require __DIR__.'/../modules/AiReception/Native/DialogueState.php';
$s=Modules\AiReception\Native\DialogueState::advance(['intent'=>'cancel','phase'=>'cancelled','booking_id'=>17,'selection'=>['service_id'=>1,'professional_id'=>1,'day'=>'2026-12-04']],['service_id'=>1,'professional_id'=>1,'day'=>'2026-12-07'],'Quiero una nueva cita');
if($s['intent']!=='book'||$s['booking_id']!==null)throw new RuntimeException('A new booking after cancellation must not retain cancellation intent or old booking identity');
echo "PASS new booking after cancellation resets prior operation\n";

require __DIR__.'/../modules/AiReception/Native/ReceptionTools.php';
$tools=(new Modules\AiReception\Native\ReceptionTools)->availableDefinitions(['service_id'=>1,'professional_id'=>1,'day'=>'2026-12-07'],'Quiero una nueva cita');
if(array_column($tools,'name')!==['availability','human_handoff'])throw new RuntimeException('New booking must progress to verified availability or handoff, never replay prior operation');
echo "PASS new booking exposes verified next steps only\n";
