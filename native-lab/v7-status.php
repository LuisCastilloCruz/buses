<?php
$app=require __DIR__.'/schema-bootstrap.php';foreach(['mf_v7_system','mf_v7_tattoo','mf_v7_dental','mf_v7_commerce'] as $database){$exists=$db->selectOne('SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=? AND table_name=?',[$database,'migrations']);echo $database.': '.($exists->n?$db->selectOne('SELECT COUNT(*) n FROM `'.$database.'`.migrations')->n:0)." migrations\n";}
