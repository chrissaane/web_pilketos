<?php

namespace App\Console\Commands;

use App\Services\SiPintuGatewayService;
use Illuminate\Console\Command;

class SipintuClientCheckCommand extends Command
{
    protected $signature = 'sipintu:client-check {--client-id= : Client ID to check} {--secret= : Client Secret to check}';

    protected $description = 'Periksa dan uji status koneksi ke SiPintu Identity & API Gateway';

    public function handle(SiPintuGatewayService $sipintuService)
    {
        $clientId = $this->option('client-id') ?? config('services.sipintu.client_id');
        $clientSecret = $this->option('secret') ?? config('services.sipintu.client_secret');
        $apiUrl = config('services.sipintu.api_url');

        $this->info("Checking SiPintu Gateway Connection...");
        $this->line("Target URL   : {$apiUrl}");
        $this->line("Client ID    : {$clientId}");
        $this->line("Client Secret: " . ($clientSecret ? '********' : 'Not set'));

        $pingResult = $sipintuService->ping();

        if ($pingResult) {
            $this->info("\n[SUCCESS] Heartbeat Ping Response:");
            $this->line(json_encode($pingResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->warn("\n[WARNING] Heartbeat Ping failed or gateway unreachable at {$apiUrl}");
        }

        $validateResult = $sipintuService->validateClient();

        if ($validateResult) {
            $this->info("\n[SUCCESS] Client Validation Response:");
            $this->line(json_encode($validateResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->warn("[WARNING] Client Validation failed or returned error.");
        }

        return Command::SUCCESS;
    }
}
