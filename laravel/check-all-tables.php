<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = DB::select('SHOW TABLES');
$key = 'Tables_in_' . DB::getDatabaseName();
foreach($tables as $t) {
    $name = $t->$key;
    $count = DB::table($name)->count();
    echo "$name: $count rows\n";
}
