<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$users = DB::table('users')->select('id', 'username', 'password_hash')->limit(5)->get();
foreach ($users as $u) {
    echo $u->id . ' | ' . $u->username . ' | ' . substr($u->password_hash, 0, 20) . '...' . PHP_EOL;
}
