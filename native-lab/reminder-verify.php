<?php
require __DIR__.'/test-bootstrap.php';
$f=json_decode(file_get_contents(dirname(__DIR__).'/.native-runtime/reminder-fixture.json'),true);
$r=$db->table('aireception_reminders')->where('id',$f['reminder'])->first();
if($r->status!=='simulated_sent' || (int)$r->attempts!==1 || $db->table('aireception_reminder_attempts')->where('reminder_id',$r->id)->count()!==1)throw new RuntimeException('Duplicate queue handling failure.');
if(app('db')->connection('system')->table('aireception_failed_jobs')->exists())throw new RuntimeException('Inspect private queue failures.');
echo "PASS real database worker processed duplicate jobs exactly once; no external delivery.\n";
