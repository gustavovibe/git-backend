<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Services\TourRadarService;

class FetchFeaturedTours extends Command
{
    // accept an optional comma-separated list of category codes
    protected $signature = 'tours:fetch-featured {categories? : Comma-separated category codes (e.g. 178,189)}';
    protected $description = 'Fetch and cache featured tours for categories (optional list)';

    protected $service;

    public function __construct(TourRadarService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        // default categories if none provided
        $defaultCategories = ['4', '32', '56', '73', '178', '189', '209', '381', '383'];

        // read argument (comma-separated string) and parse it
        $input = $this->argument('categories');

        if ($input) {
            // allow both comma-separated or space-separated entries by normalizing
            $raw = str_replace(' ', '', $input); // remove spaces
            $parts = explode(',', $raw);
            // filter empties and non-numeric values; keep as strings
            $categories = array_values(array_filter(array_map('trim', $parts), fn($c) => $c !== ''));
        } else {
            $categories = $defaultCategories;
        }

        $this->info('Categories to fetch: ' . implode(', ', $categories));
        Log::info('Starting fetchFeaturedTours', ['categories' => $categories]);

        foreach ($categories as $code) {
            $this->info("Fetching featured tours for category: {$code}");

            try {
                // Call the service
                $tours = $this->service->getFeaturedToursForCategory($code);

                // Log the raw result for debugging
                Log::info('Fetched tours', ['category' => $code, 'tours' => $tours]);

                // Optionally, print count and first item
                $count = count($tours);
                $this->info("Total tours fetched for {$code}: {$count}");
                if ($count > 0) {
                    $this->info('Sample tour: ' . json_encode($tours[0]));
                }
            } catch (\Throwable $e) {
                // don't stop the whole loop on a single category error
                Log::error('Error fetching featured tours for category', [
                    'category' => $code,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error("Failed fetching category {$code}: " . $e->getMessage());
                // continue next category
                continue;
            }
        }

        $this->info('Finished fetching featured tours.');
        return 0;
    }
}
