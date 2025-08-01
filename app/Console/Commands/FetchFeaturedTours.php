<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TourRadarService;

class FetchFeaturedTours extends Command
{
    protected $signature = 'tours:fetch-featured';
    protected $description = 'Fetch and cache featured tours for all categories';

    protected $service;


    public function __construct(TourRadarService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {

        $categories = {'4', '32', '56'};

        foreach ($categories as $code) {
            $this->info("Fetching featured tours for category: {$code}");

            // Call the service
            $tours = $this->service->getFeaturedToursForCategory($code);

            // Log the raw result for debugging
            Log::info('Fetched tours', ['category' => $code, 'tours' => $tours]);

            // Optionally, print count and first item
            $count = count($tours);
            $this->info("Total tours fetched: {$count}");
            if ($count > 0) {
                $this->info('Sample tour: ' . json_encode($tours[0]));
            }
        }

        return 0;
    }
}
