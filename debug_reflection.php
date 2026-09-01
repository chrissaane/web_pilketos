<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(App\Services\SiPintuGatewayService::class);
$method = new ReflectionMethod($svc, 'fetchAllUsersFromGateway');
$method->setAccessible(true);
$items = $method->invoke($svc);

echo 'COUNT=' . count($items) . PHP_EOL;
if (count($items) > 0) {
    $first = $items[0];
    echo json_encode([
        'nis' => $first['nis'] ?? null,
        'nip' => $first['nip'] ?? null,
        'name' => $first['name'] ?? $first['nama'] ?? null,
        'role' => $first['role'] ?? null,
        'id' => $first['id'] ?? null,
    ]);
    echo PHP_EOL;
}
