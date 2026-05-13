<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$rows = DB::table('migrations')->where('migration', 'like', '%testimonials%')->get();
foreach ($rows as $r) {
    echo $r->migration . ' (batch ' . $r->batch . ')' . PHP_EOL;
}
echo "---\n";
$tables = DB::select('SHOW TABLES LIKE "testimonials"');
echo count($tables) > 0 ? "Table exists\n" : "Table missing\n";
