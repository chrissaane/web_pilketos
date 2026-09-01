<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(App\Services\SiPintuGatewayService::class);
$items = $svc->fetchAllUsersFromGateway();

echo 'COUNT=' . count($items) . PHP_EOL;
if (count($items) > 0) {
    $first = $items[0];
    echo json_encode(array_intersect_key($first, array_flip(['nis','nip','nama','role','user_id','id','name','email']))) . PHP_EOL;
}
