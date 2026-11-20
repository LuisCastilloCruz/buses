<?php

$app = (require __DIR__ . '/schema-bootstrap.php');
$schema = $db->getSchemaBuilder();
if (!$schema->hasTable('migrations')) {
    $schema->create('migrations', function ($t) {
        $t->increments('id');
        $t->string('migration');
        $t->integer('batch');
    });
}
$files = glob(dirname(__DIR__) . '/database/migrations' . ($name === 'mf_v4_system' ? '' : '/tenant') . '/*.php');
sort($files);
$done = 0;
foreach ($files as $f) {
    $base = basename($f, '.php');
    // These exact duplicated Tattoo schema files belong to tenant scope, never system.
    if ($name === 'mf_v4_system' && in_array($base, ['2026_04_12_000001_add_photo_path_to_tattoo_artists_table', '2026_04_12_000002_create_tattoo_artist_portfolios_table'], true)) {
        continue;
    }
    if ($db->table('migrations')->where('migration', $base)->exists()) {
        continue;
    }
    $source = file_get_contents($f);
    if (!preg_match('/class\\s+(\\w+)\\s+extends/', $source, $m)) {
        throw new RuntimeException('Unknown migration class: ' . $base);
    }
    require_once $f;
    try {
        (new $m[1]())->up();
        $db->table('migrations')->insert(['migration' => $base, 'batch' => 1]);
        $done++;
    } catch (Throwable $e) {
        fwrite(STDERR, 'FAILED ' . $base . ': ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
echo "PASS {$name}: {$done} original migrations applied; prior markers preserved.\n";
