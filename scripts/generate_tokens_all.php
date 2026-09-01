<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Election;
use Illuminate\Support\Facades\Log;

echo "Starting token generation for all non-finished elections...\n";
try {
    $elections = Election::where('end_time', '>=', now())->get();
    $count = 0;
    foreach ($elections as $election) {
        echo "Processing election: {$election->id} - {$election->title}\n";
        Election::generateTokensForElection($election);
        $count++;
    }
    echo "Done. Processed {$count} elections.\n";
} catch (Throwable $t) {
    echo "Error: " . $t->getMessage() . "\n";
}

return 0;
