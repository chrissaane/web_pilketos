<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(App\Services\SiPintuGatewayService::class);
$result = $svc->syncAllUsersFromGateway();

echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
echo 'DB_USERS=' . App\Models\User::query()->whereIn('role', ['siswa', 'guru'])->count() . PHP_EOL;
