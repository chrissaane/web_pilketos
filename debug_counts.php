<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = rtrim(env('SIPINTU_API_URL', 'https://sipintu.smkn1bangsri.sch.id'), '/');
$clientId = env('SIPINTU_CLIENT_ID');
$clientSecret = env('SIPINTU_CLIENT_SECRET');

foreach (['/api/v1/sijuna/students', '/api/v1/sijuna/teachers'] as $endpoint) {
    $response = Illuminate\Support\Facades\Http::withHeaders([
        'Accept' => 'application/json',
        'X-Client-ID' => $clientId,
        'X-Client-Secret' => $clientSecret,
    ])->timeout(10)->get($baseUrl.$endpoint);

    $json = $response->json();
    $count = 0;
    if (is_array($json)) {
        if (isset($json['count'])) {
            $count = (int) $json['count'];
        } elseif (isset($json['data']) && is_array($json['data'])) {
            $count = count($json['data']);
        }
    }

    echo $endpoint . ' => ' . $count . PHP_EOL;
}
