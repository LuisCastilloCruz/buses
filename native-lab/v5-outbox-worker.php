<?php
require __DIR__.'/test-bootstrap.php';
echo app(Modules\AiReception\Native\Outbox::class)->process((int)$argv[3]).PHP_EOL;