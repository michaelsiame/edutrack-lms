<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE');
$key = 'Tables_in_' . $dbName;
foreach ($tables as $t) {
    echo $t->$key . PHP_EOL;
}
