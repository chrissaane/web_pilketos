<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$user = User::factory()->create([
    'role' => 'siswa',
    'identity_number' => '20260022',
    'password' => bcrypt('oldpass'),
    'login_password' => 'oldpass',
    'is_active' => true,
]);

$user->password = 'NewSecurePass123';
$user->login_password = 'NewSecurePass123';
$user->save();
$fresh = $user->fresh();
var_dump($fresh->password);
var_dump(Hash::check('NewSecurePass123', $fresh->password));
