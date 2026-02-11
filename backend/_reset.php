<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'admin@plataforma.com')->first();
if ($u) {
    $u->password = 'password';
    $u->save();
    echo "Password reset to 'password' for admin@plataforma.com (role={$u->role})\n";
} else {
    echo "User not found\n";
}
