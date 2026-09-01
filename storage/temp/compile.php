<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$blade = app('blade.compiler');
$str = file_get_contents(__DIR__ . '/../resources/views/admin/candidates/index.blade.php');
$compiled = $blade->compileString($str);
file_put_contents(__DIR__ . '/compiled.php', $compiled);
echo "OK\n";
