<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class GustavoDuffelController extends Controller
{

    public function fetchSanitized(Request $r)
    {
        $url = $r->query('url');
        // validate & whitelist host (important)
        // ...
    
        $resp = Http::get($url);
        if (! $resp->successful()) {
            return response()->json(['error' => 'fetch failed', 'status' => $resp->status()], $resp->status());
        }
    
        $html = $resp->body();
    
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); // avoid charset issues
        libxml_clear_errors();
    
        $xpath = new \DOMXPath($dom);
    
        // Try a few common container selectors
        $candidates = $xpath->query('//main | //article | //*[@id="main"] | //*[@id="content"]');
    
        $node = $candidates->length ? $candidates->item(0) : $dom->getElementsByTagName('body')->item(0);
    
        // remove script and iframe tags
        foreach ($node->getElementsByTagName('script') as $s) { $s->parentNode->removeChild($s); }
        foreach ($node->getElementsByTagName('iframe') as $f) { $f->parentNode->removeChild($f); }
    
        // Optionally remove on* attributes and javascript: links for safety
        $badAttrs = [];
        $xpath2 = new \DOMXPath($dom);
        foreach ($xpath2->query('//*') as $el) {
            // remove inline event handlers
            foreach (iterator_to_array($el->attributes ?? []) as $attr) {
                if (preg_match('/^on/i', $attr->name) || stripos($attr->value, 'javascript:') !== false) {
                    $el->removeAttribute($attr->name);
                }
            }
        }
    
        $cleanHtml = '';
        foreach ($node->childNodes as $child) {
            $cleanHtml .= $dom->saveHTML($child);
        }
    
        // Wrap in minimal HTML and return
        $out = "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head><body>{$cleanHtml}</body></html>";
    
        return response($out, 200)->header('Content-Type', 'text/html');
    }
    
public function fetch(Request $r)
{
    $url = $r->query('url');
    if (! $url) {
        return response()->json(['error' => 'url required'], 400);
    }

    // Normalize - ensure a scheme so parse_url works
    if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }

    $host = parse_url($url, PHP_URL_HOST);
    if ($host === false || $host === null) {
        return response()->json(['error' => 'invalid url'], 400);
    }

    // Whitelist hosts (adjust as needed)
    $allowedHosts = [
        'www.iberia.com',
        'iberia.com',
        'assets.duffel.com',
        // add other allowed hosts here
    ];

    //if (! in_array($host, $allowedHosts, true)) {
    //    Log::warning('[proxy] Host not allowed', ['host' => $host, 'url' => $url, 'ip' => $r->ip()]);
    //    return response()->json(['error' => 'host not allowed', 'host' => $host], 403);
    //}

    try {
        // Set options: timeout, connect_timeout, verify (TLS), allow redirects
        $resp = Http::withOptions([
                'verify' => true,       // set false only for debugging (not recommended for prod)
                'connect_timeout' => 5,
                'timeout' => 15,
            ])
            ->withHeaders([
                'User-Agent' => 'VibeAdventuresProxy/1.0 (+https://vibeadventures.be)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])
            ->get($url);

        // For debugging: log status and a few headers
        Log::info('[proxy] fetched', [
            'url' => $url,
            'status' => $resp->status(),
            'content_type' => $resp->header('Content-Type'),
        ]);

        // If remote returned 2xx, return the body and content-type with remote status
        if ($resp->successful()) {
            $contentType = $resp->header('Content-Type', 'text/html');
            return response($resp->body(), $resp->status())
                   ->header('Content-Type', $contentType);
        }

        // Non-2xx: return debug JSON (and remote status)
        $snippet = mb_substr($resp->body(), 0, 800); // return first 800 chars for debug
        return response()->json([
            'error' => 'fetch failed',
            'remote_status' => $resp->status(),
            'remote_content_type' => $resp->header('Content-Type'),
            'remote_body_snippet' => $snippet,
        ], max(400, $resp->status())); // keep remote status code where reasonable

    } catch (\Throwable $e) {
        // log error with stack for server-side debugging
        Log::error('[proxy] exception fetching URL', [
            'url' => $url,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'fetch exception',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function offerRequests(Request $request)
    {
        // Retrieve query parameters from the request
        $origin = $request->query('origin');
        $startCity = $request->query('startCity');
        $endCity = $request->query('endCity');
        $departureDate = $request->query('departure');
        $arrivalDate = $request->query('arrival');
        $adultsCount = $request->query('adultsCount');
        $childrenCount = $request->query('childrenCount');
        $page = $request->query('page', 1); // Default to page 1 if not provided

        // Validate that required parameters are provided
        $missingParameters = [];
        if (!$origin) {
            $missingParameters[] = 'origin';
        }
        if (!$startCity) {
            $missingParameters[] = 'startCity';
        }
        if (!$endCity) {
            $missingParameters[] = 'endCity';
        }
        if (!$departureDate) {
            $missingParameters[] = 'departure';
        }
        if (!$arrivalDate) {
            $missingParameters[] = 'arrival';
        }
        if (!$adultsCount) {
            $missingParameters[] = 'adultsCount';
        }

        if (!empty($missingParameters)) {
            return response()->json(['error' => 'Missing required parameters: ' . implode(', ', $missingParameters)], 400);
        }

        try {
            // Construct the passengers array based on counts
            $passengers = [];
            for ($i = 0; $i < $adultsCount; $i++) {
                $passengers[] = ['type' => 'adult'];
            }
            for ($i = 0; $i < $childrenCount; $i++) {
                $passengers[] = ['type' => 'child'];
            }

            // Construct the request body
            $requestBody = [
                'data' => [
                    'slices' => [
                        [
                            'origin' => $origin,
                            'destination' => $startCity,
                            'departure_date' => $departureDate,
                        ],
                        [
                            'origin' => $endCity,
                            'destination' => $origin,
                            'departure_date' => $arrivalDate,
                        ]
                    ],
                    'passengers' => $passengers,
                    'cabin_class' => null
                ]
            ];
            // Make the request to the Duffel API
            $response = Http::withHeaders([
               'Accept-Encoding' => 'gzip, deflate, br',
                'Accept' => 'application/json',
                'Duffel-Version' => 'v2',
                'Authorization' => 'Bearer duffel_test_tfNofacp8LVcPjSf7OA0Q78ghrmuoakwtBhjbxaRrs2',
            ])->post('https://api.duffel.com/air/offer_requests?supplier_timeout=5000&limit=5&sort=total_amount&max_connections=1', $requestBody);

            if ($response->status() >= 400) {
                return $response->json();
            }

            // Extract the offers from the response
            $offers = $response->json()['data']['offers'];

            // Filter out offers with operating carrier name "Duffel Airways"
            $filteredOffers = array_filter($offers, function ($offer) {
                foreach ($offer['slices'] as $slice) {
                    if (!isset($slice['segments'])) {
                        continue; // Skip this slice if 'segments' key is missing
                    }
                    foreach ($slice['segments'] as $segment) {
                        if (!isset($segment['operating_carrier']['name'])) {
                            continue; // Skip this segment if 'operating_carrier' or 'name' key is missing
                        }
                        if ($segment['operating_carrier']['name'] === 'Duffel Airways') {
                            return false; // Skip this offer if operating carrier is "Duffel Airways"
                        }
                    }
                }
                return true; // Include this offer if operating carrier is not "Duffel Airways"
            });

            // Filter offers with at least one checked or carry-on baggage
            $baggageOffers = array_filter($filteredOffers, function ($offer) {
                foreach ($offer['slices'] as $slice) {
                    if (!isset($slice['segments'])) {
                        continue; // Skip this slice if 'segments' key is missing
                    }
                    foreach ($slice['segments'] as $segment) {
                        if (!isset($segment['passengers'])) {
                            continue; // Skip this segment if 'passengers' key is missing
                        }
                        foreach ($segment['passengers'] as $passenger) {
                            if (!isset($passenger['baggages'])) {
                                continue; // Skip this passenger if 'baggages' key is missing
                            }
                            foreach ($passenger['baggages'] as $baggage) {
                                if ($baggage['type'] === 'checked' || $baggage['type'] === 'carry_on') {
                                    return true;
                                }
                            }
                        }
                    }
                }
                return false;
            });

            $filteredOffers = array_values($baggageOffers); // Re-index the array to remove numeric keys

            // Extract the offers from the response
            $offers = collect($filteredOffers);
            // Paginate the offers with 3 offers per page
            $perPage = 3;

            $paginatedOffers = new LengthAwarePaginator(
                $offers->forPage($page, $perPage),
                $offers->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Return the paginated offers along with total number of pages
            return response()->json([
                'offers' => $paginatedOffers->items(),
                'total_pages' => $paginatedOffers->lastPage()
            ]);
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
