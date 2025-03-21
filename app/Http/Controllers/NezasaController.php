<?

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\TourItineraryLocations;
use App\Services\OpenAIService;
use App\Helpers\ApiResponse;

class NezasaController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
      $this->openAIService = $openAIService;
    }

    public function getItineraryTour(Request $r)
    {
      $tourId = $r->tourId;
      if (!$tourId) {
        return ApiResponse::error('tour ID empty', 404);
      }
      
      $tour = DB::table('tours')
          ->select(
              'tours.tour_id',
              'tours.tour_name',
              'tours.tour_length_days',
              'tours.start_city',
              'tours.end_city',
              'tours.price_currency',
              'tours.description',
              'cities_start.city_name as start_city_name',
              'cities_start.t_country_id as start_country_code',
              'cities_end.city_name as end_city_name',
              'cities_end.t_country_id as end_country_code'
          )
          ->leftJoin('cities as cities_start', 'cities_start.t_city_id', '=', 'tours.start_city')
          ->leftJoin('cities as cities_end', 'cities_end.t_city_id', '=', 'tours.end_city')
          ->where('tours.tour_id', $tourId)
          ->first();

      if (!$tour) {
        Log::error("Tour not found: $tourId");
        return ApiResponse::error('Tour not found', 404);
      }

      // Step 2: Fetch itinerary JSON
      $tourItinerary = DB::table('tour_itineraries_json')
          ->where('tour_id', $tourId)
          ->value('json_data');

      if (!$tourItinerary) {
        Log::error("Itinerary not found for tour: $tourId");
        return ApiResponse::error('Itinerary not found', 404);
      }

      $itineraryData = json_decode($tourItinerary, true);

      $messages = [
          ['role' => 'user', 'content' => 'Rephrase this title and shorten it if possible: ' . $tour->tour_name]
      ];
      $openAiResponse = $this->openAIService->getOpenAiChatSimple($messages);
      
      $rephrasedTitle = isset($openAiResponse['choices']) && !empty($openAiResponse['choices']) ? $openAiResponse['choices'][0]['message']['content'] : $tour->tour_name;
      // Step 4: Extract itinerary and departure date
      $legs = [];
      if (!empty($itineraryData['itinerary'])) {
        foreach ($itineraryData['itinerary'] as $day) {
            if (!empty($day['locations'])) {
                foreach ($day['locations'] as $location) {
                    $legs[] = [
                        'stop' => [
                            'location' => [
                                'name' => $location['city_name'] ?? '',
                                'coordinate' => [
                                    'lat' => $location['latitude'] ?? null,
                                    'lng' => $location['longitude'] ?? null,
                                ],
                                'country_code' => $location['country_code'] ?? '',
                            ],
                            'nights' => $day['duration'] ?? 1,
                            'description' => $day['description'] ?? '',
                        ]
                    ];
                }
            }
        }
      }

      $startDate = null;
      if (!empty($itineraryData['availableDepartures'])) {
          $startDate = $itineraryData['availableDepartures'][0]['date'] ?? null;
      }

      // Step 5: Build the nesazaData array
      $nesazaData = [
          'title' => $rephrasedTitle,
          'departureLocationRef' => [
              'countryCode' => $tour->start_country_code,
              'name' => $tour->start_city_name,
          ],
          'endLocationRef' => [
              'countryCode' => $tour->end_country_code,
              'name' => $tour->end_city_name,
          ],
          'legs' => $legs,
          'startDate' => $startDate,
      ];

      return $this->addNezasaItinerary($nesazaData);

    }// end public function getItineraryTour($tourId)

    protected function addNezasaItinerary($nesazaData)
    {
      $apiUrl = 'https://api.stg.tripbuilder.app';
      $apiUsername = env('NEZASA_PROD_USER');
      $apiPassword = env('NEZASA_PROD_PWD');
      $authorization = base64_encode("$apiUsername:$apiPassword");
      $headers = [
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
          'Authorization' => "Basic $authorization",
      ];
      $response = Http::withHeaders($headers)->post($apiUrl, $nesazaData);
      
      if ($response->failed()) {
        Log::error('Failed to send itinerary to Nezasa', [
            'response' => $response->body(),
            'request' => $nesazaData
        ]);
        return ApiResponse::error('Failed to send itinerary to Nezasa'. $response->body(), 500);
      }

      return response()->json([
        'message' => 'Itinerary successfully sent',
        'response' => $response->json()
      ]);
    }

    public function getNezasaLocations()
    {
      
      $countriesList = [
        "MX"
      ];
    
    
      $baseUrl = "https://api.tripbuilder.app/location/v1/vibeadventures/areas";
      $pageSize = 100;

      foreach ($countriesList as $country) {

          $isoCode = $country;
          $locations = array();
          $pageNumber = 1;
          $hasMore = true;

          while ($hasMore) {

              $url = "{$baseUrl}?countryCodes={$isoCode}&page[size]={$pageSize}&page[number]={$pageNumber}";

              try {

                $apiUsername = env('NEZASA_PROD_USER');
                $apiPassword = env('NEZASA_PROD_PWD');
                $authorization = base64_encode("$apiUsername:$apiPassword");
                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => "Basic $authorization",
                ];
                $response = Http::withHeaders($headers)->get($url);
                
                if ($response->failed()) {
                  Log::error('Failed to send itinerary to Nezasa', [
                      'response' => $response->body(),
                      'request' => $nesazaData
                  ]);
                  return ApiResponse::error('Failed to send itinerary to Nezasa'. $response->body(), 500);
                }

                $data = $response->json();

                if (isset($data['areas'])) {
                  // Store the locations for the current page in the database
                  TourItineraryLocations::create([
                    'countryISOCode' => $isoCode,
                    'locationsList' => $data['areas']
                  ]);
                }

                if (isset($data['meta']['page']['hasMore'])) {
                  $hasMore = $data['meta']['page']['hasMore'];
                  if ($hasMore) {
                    $pageNumber++;
                  }
                } else {
                  $hasMore = false;
                }

              } catch (\Illuminate\Http\Client\RequestException $e) {
                // Handle the error (log it, display a message, etc.)
                \Log::error("Error fetching locations for {$isoCode} (Page {$pageNumber}): " . $e->getMessage());
                $hasMore = false; // Stop trying for this country on error
              }
          }

        \Log::info("Successfully fetched and stored " . count($locations) . " locations for {$isoCode}.");
      }

      return response()->json([
        'message' => 'Successfully fetched and stored locations for all countries in the list.',
        'response' => $response->json()
      ]);
    }

    public function getLocationsFromDatabase()
    {
      $tourData = TourItineraryLocations::where('locationsList', '!=', '[]')
      ->orderBy('countryISOCode', 'asc')
      ->get();

      $data = [];

      foreach ($tourData as $item) {
        $countryCode = $item->countryISOCode;
        $locations = $item->locationsList; // Decode the JSON string to an associative array

        if (!isset($data[$countryCode])) {
          $data[$countryCode] = [];
        }

        if (is_array($locations)) {
          $data[$countryCode] = array_merge($data[$countryCode], $locations);
        }
      }

      return response()->json([
        'message' => 'Successfully fetched and stored locations for all countries in the list.',
        'response' => $data
      ]);
    }
}
