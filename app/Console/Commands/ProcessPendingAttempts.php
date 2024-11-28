<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TourRadarController;

class ProcessPendingAttempts extends Command
{
    protected $signature = 'attempts:process';
    protected $description = 'Process pending attempts for TourRadar bookings';

    public function handle()
    {
        Log::info('Starting to process pending attempts.');

        $pendingAttempts = DB::table('attempts')
            ->where('status', 'pending')
            ->where('expiration', '>=', now())
            ->get();

        foreach ($pendingAttempts as $attempt) {
            $ResponseTour = json_decode($attempt->tourradar_res, true);
            $tBookingId = $ResponseTour['id'];
            Log::info('Processing booking ID: ' . $tBookingId);

            $statusResponse = TourRadarController::checkBooking($tBookingId);
            Log::info('Status response for booking ID ' . $tBookingId . ': ' . json_encode($statusResponse));

            if (isset($statusResponse['status']) && $statusResponse['status'] == "confirmed") {
                DB::table('attempts')->where('id', $attempt->id)->update(['status' => 'confirmed']);
                Log::info('Booking ID ' . $tBookingId . ' confirmed.');
            }
        }

        Log::info('Finished processing pending attempts.');
    }
}
