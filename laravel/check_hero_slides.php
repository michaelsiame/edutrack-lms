<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = DB::select('SHOW COLUMNS FROM hero_slides');
foreach ($cols as $c) {
    echo $c->Field . PHP_EOL;
}
