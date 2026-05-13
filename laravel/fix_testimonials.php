<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
DB::table('migrations')->where('migration', '2024_01_02_000001_create_testimonials_table')->delete();
echo 'Deleted testimonials migration record' . PHP_EOL;
