<?php
require __DIR__.'/test-bootstrap.php';
$a=app(Modules\AiReception\Native\Access::class);$u=auth('web')->user();
if($host!=='cuarta'||!$u->locked||!$a->allows('aireception','calendar')||!$a->allows('odontologia','calendar'))throw new RuntimeException('Fourth-company permissions mismatch.');
$u->aireception_suspended=true;if($a->allows('aireception','calendar'))throw new RuntimeException('Suspension bypass');$u->aireception_suspended=false;
echo "PASS fourth administrator retains legacy protection and new module access; dedicated suspension denies access.\n";