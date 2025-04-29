<?php

namespace App\Console\Commands;

use App\Services\BiddingApiService;
use Illuminate\Console\Command;

class FetchBiddings extends Command
{
    protected $signature = 'biddings:fetch {--days=7} {--status=open}';
    protected $description = 'Fetch biddings from external API';

    public function handle(BiddingApiService $apiService)
    {
        $this->info('Fetching biddings from external API...');

        $days = $this->option('days');
        $status = $this->option('status');

        $filters = [
            'published_after' => now()->subDays($days)->format('Y-m-d'),
            'status' => $status,
        ];

        $apiData = $apiService->fetchBiddings($filters);

        if (isset($apiData['error'])) {
            $this->error('Error fetching biddings: ' . $apiData['error']);
            return 1;
        }

        $this->info('Found ' . count($apiData['data'] ?? []) . ' biddings.');

        $result = $apiService->saveBiddingsFromApi($apiData);

        $this->info('Saved ' . $result['saved'] . ' out of ' . $result['total'] . ' biddings.');

        if (count($result['errors']) > 0) {
            $this->warn('Errors occurred when saving some biddings:');
            foreach ($result['errors'] as $error) {
                $this->line('- ' . $error['reference'] . ': ' . $error['message']);
            }
        }

        return 0;
    }
}
