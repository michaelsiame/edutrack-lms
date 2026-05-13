<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = DB::select('SHOW COLUMNS FROM certificates');
foreach ($cols as $c) {
    echo $c->Field . PHP_EOL;
}
echo "---\n";
$cols2 = DB::select('SHOW COLUMNS FROM newsletter_subscribers');
foreach ($cols2 as $c) {
    echo $c->Field . PHP_EOL;
}
