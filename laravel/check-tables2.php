<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = ['testimonials', 'events', 'institution_photos', 'hero_slides'];
foreach($tables as $t) {
    try {
        $cols = DB::getSchemaBuilder()->getColumnListing($t);
        $count = DB::table($t)->count();
        echo "$t: " . count($cols) . " columns, $count rows\n";
        echo "  cols: " . implode(', ', $cols) . "\n";
    } catch(Exception $e) {
        echo "$t: ERROR - " . $e->getMessage() . "\n";
    }
}
