<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$rows = DB::table('migrations')->get();
foreach ($rows as $r) {
    echo $r->migration . ' (batch ' . $r->batch . ')' . PHP_EOL;
}
