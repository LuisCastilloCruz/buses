<?php
require __DIR__.'/schema-bootstrap.php';echo $name.' migrations='.$db->table('migrations')->count().PHP_EOL;
