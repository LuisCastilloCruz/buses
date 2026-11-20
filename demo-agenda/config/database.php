<?php

$s = Modules\AiReception\Agenda\LabSafety::settings();
return ['default' => 'agenda', 'connections' => ['agenda' => ['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 13317, 'database' => 'aireception_lab_v1', 'username' => $s['db_user'], 'password' => $s['db_password'], 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true, 'engine' => 'InnoDB', 'options' => [PDO::ATTR_TIMEOUT => 5]]]];
