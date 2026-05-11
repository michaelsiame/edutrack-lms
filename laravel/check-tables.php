<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = ['testimonials', 'events', 'institution_photos', 'hero_slides', 'team_members'];
foreach($tables as $t) {
    try {
        $cols = DB::getSchemaBuilder()->getColumnListing($t);
        echo $t . ': ' . implode(', ', array_slice($cols, 0, 8)) . (count($cols) > 8 ? '...' : '') . "\n";
    } catch(Exception $e) {
        echo $t . ': MISSING' . "\n";
    }
}
