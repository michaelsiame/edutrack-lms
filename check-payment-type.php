<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = Illuminate\Support\Facades\Schema::getColumns('payments');
foreach ($cols as $c) {
    if ($c['name'] === 'payment_type') {
        echo 'payment_type: ' . $c['type'] . PHP_EOL;
    }
}
