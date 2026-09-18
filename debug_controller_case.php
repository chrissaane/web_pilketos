<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\VoterController;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

$user = User::factory()->create([
    'role' => 'siswa',
    'identity_number' => '20260022',
    'password' => bcrypt('oldpass'),
    'login_password' => 'oldpass',
    'is_active' => true,
]);

$admin = User::factory()->create([
    'role' => 'admin',
    'identity_number' => 'admin-01',
    'password' => bcrypt('admin123'),
]);

$request = Request::create('/admin/voters/'.$user->identity_number.'/password', 'PUT', [
    'password' => 'NewSecurePass123',
    'password_confirmation' => 'NewSecurePass123',
]);

app('auth')->setUser($admin);
$controller = new VoterController;
$controller->updatePassword($request, $user->identity_number);

$fresh = User::find($user->id);
var_dump($fresh->password);
var_dump(Hash::check('NewSecurePass123', $fresh->password));
var_dump($fresh->login_password);
