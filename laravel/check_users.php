<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$users = DB::table('users')->select('id', 'username', 'email', 'first_name', 'last_name', 'status')->limit(5)->get();
foreach ($users as $u) {
    echo $u->id . ' | ' . $u->username . ' | ' . $u->email . ' | ' . $u->first_name . ' ' . $u->last_name . ' | ' . $u->status . PHP_EOL;
}
echo "---roles---\n";
$roles = DB::table('user_roles')->select('user_id', 'role_id')->limit(10)->get();
foreach ($roles as $r) {
    echo 'user:' . $r->user_id . ' -> role:' . $r->role_id . PHP_EOL;
}
