<?php

$app = (require __DIR__ . '/schema-bootstrap.php');
if (!$db->table('configurations')->exists()) {
    $db->table('configurations')->insert(['send_auto' => false, 'locked_tenant' => false, 'locked_emission' => false, 'limit_users' => 20, 'limit_documents' => 99999, 'visual' => json_encode(['bg' => 'light', 'header' => 'light', 'sidebars' => 'light']), 'login' => $app['db']->connection('system')->table('configurations')->value('login')]);
}
echo "PASS local configuration {$name}\n";
