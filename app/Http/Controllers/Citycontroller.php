<?php

namespace App\Http\Controllers;

use App\Filters\ToursFilters;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CitiesImport;
use App\Models\City;
use App\Models\Order;
use App\Http\Resources\CityResource;
use App\Helpers\ApiResponse;
use App\Models\Country;
use App\Models\NaturalDestination;
use App\Http\Resources\CountryResource;
use App\Http\Resources\NaturalDestinationResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use App\Exports\DestinationsExport;


use Exception;

class Citycontroller extends Controller
{
    protected $list;
    protected $column;

    public function __construct()
    {
        $this->list = [
            1 => 't_country_id as id, name as name',
            2 => 't_city_id as id, city_name as name',
            3 => 't_natural_id as id, destination_name as name',
        ];
        $this->column = [
            1 => 'name',
            2 => 'city_name',
            3 => 'destination_name',
        ];
    }

    /**
     * Import cities.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */ 
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');

        try {
            Excel::import(new CitiesImport, $file);
        } catch (\Exception $e) {

            return ApiResponse::success([], 'Import not successful', dd($e));
        }

        return ApiResponse::success([], 'Successful import');
    }

    /**
     * Get all cities.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function index(Request $request)
    {
        $perPage = 10;

        $q = $request->input('q');

        if ($q) {
            $paginatedData = City::where('city_name', 'like', $q . '%')->paginate($perPage);
        } else {
            $paginatedData = City::paginate($perPage);
        }

        $responseData = $paginatedData->toArray();

        $responseData['data'] = CityResource::collection($paginatedData->items());

        return ApiResponse::success($responseData);
    }

    /**
     * Get all cities, countries, and natural destinations.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function DestinatioCityCountryNaturalDestination(Request $request)
    {

        $perPage = 5;

        $q = $request->input('q');

        if ($q) {

            $country = Country::where('name', 'like', $q . '%')->paginate($perPage);
            $responseDataCountry = $country->toArray();
            $responseDataCountry['data'] = CountryResource::collection($country->items());


            $city = City::where('city_name', 'like', $q . '%')->with('country')->paginate($perPage);
            $responseDataCity = $city->toArray();
            $responseDataCity['data'] = CityResource::collection($city->items());


            $cityNames = $city->pluck('city_name')->toArray();

            $natural = NaturalDestination::where('destination_name', 'like', $q . '%')->whereNotIn('destination_name', $cityNames)->paginate($perPage);
            $responseDataNatural = $natural->toArray();
            $responseDataNatural['data'] = NaturalDestinationResource::collection($natural->items());


            $responseData = [
                'country' => $responseDataCountry,
                'city' => $responseDataCity,
                'natural_destinations' => $responseDataNatural
            ];

        }
        return ApiResponse::success($responseData);
    }

    /**
     * Get codes.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function codes(Request $request)
{
    $q = $request->input('q');
    $responseData = [];

    if ($q) {
        // Query the countries table
        $country = Country::where('t_country_id', $q)->get();
        if ($country->isNotEmpty()) {
            $responseData['country'] = CountryResource::collection($country);
        }

        // Query the cities table
        $city = City::where('t_city_id', $q)->get();
        if ($city->isNotEmpty()) {
            $responseData['city'] = CityResource::collection($city);
        }

        // Query the natural destinations table
        $natural = NaturalDestination::where('t_natural_id', $q)->get();
        if ($natural->isNotEmpty()) {
            $responseData['natural'] = NaturalDestinationResource::collection($natural);
        }
    }

    return ApiResponse::success($responseData);
}


    /**
     * Get destinations.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function destinations(Request $r)
    {
        try {
            $destinations = ToursFilters::destinations($r);
            return response()->json(['status' => true, 'count' => count($destinations), 'response' => $destinations]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'response' => $e->getMessage()]);
        }
    }

    /**
     * Get destinations.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $request Request object
     * @return array
     */
    public function destinationsV2(Request $request)
    {
        $categoryFilter = $request->input('categoryFilter', '');
        $categories = array_map('trim', explode(',', $categoryFilter));
        $totalAdventuresRangeFilter = $request->input('totalAdventuresRange', '');
        $totalAdventuresRange = array_map('trim', explode(',', $totalAdventuresRangeFilter));
        $totalCommissionRangeFilter = $request->input('totalCommissionRange', '');
        $totalCommissionRange = array_map('trim', explode(',', $totalCommissionRangeFilter));
        $totalPaidRangeFilter = $request->input('totalPaidRange', '');
        $totalPaidRange = array_map('trim', explode(',', $totalPaidRangeFilter));
        $q = $request->input('q');
        $totalPaidFilter = $request->input('totalPaidFilter');
        $adventuresFilter = $request->input('adventuresFilter');
        $commissionFilter = $request->input('commissionFilter');
        $alphabeticOrder = $request->input('alphabeticOrderFilter');
        $perPage = $request->input('perPage', 15);
        $page = $request->input('page', 1);
        $result = [];

        if (empty($categories) || $categories == ['']) {
            return ApiResponse::error('Category filter cannot be empty');
        }

        foreach ($categories as $category) {
            if ($category == 'city') {
                $entities = City::get();
            } elseif ($category == 'country') {
                $entities = Country::get();
            } elseif ($category == 'natural_destination') {
                $entities = NaturalDestination::get();
            } else {
                continue;
            }

            foreach ($entities as $entity) {
                $totalCommission = 0;
                $totalPaid = 0;

                $tours = $entity->tours()->get();
                $tourIds = $tours->pluck('tour_id')->toArray();
                $orders = Order::with(['flightTour', 'travelers', 'user', 'tour'])->whereIn('tour_id', $tourIds)->get();

                foreach ($orders as $order) {
                    $totalCommission += $order->commission_value_tour ?? 0;
                    $totalPaid += $order->paid ?? 0;
                }

                $item = [
                    'id' => $category == 'city' ? $entity->t_city_id : ($category == 'country' ? $entity->t_country_id : $entity->t_natural_id),
                    'name' => $category == 'city' ? $entity->city_name : ($category == 'country' ? $entity->name : $entity->destination_name),
                    'number_of_tours' => count($tourIds),
                    'commission' => $totalCommission,
                    'total_paid' => $totalPaid,
                    'category' => $category
                ];

                if ($q && stripos($item['name'], $q) !== 0) {
                    continue;
                }

                $result[] = $item;
            }
        }

        $result = collect($result);


        if ($alphabeticOrder == 'desc') {
            $result = $result->sortByDesc('name')->values();
        } else {
            $result = $result->sortBy('name')->values();
        }

        if ($commissionFilter) {
            $result = $commissionFilter == 'asc' ? $result->sortBy('commission')->values() : $result->sortByDesc('commission')->values();
        }

        if ($adventuresFilter) {
            $result = $adventuresFilter == 'asc' ? $result->sortBy('number_of_tours')->values() : $result->sortByDesc('number_of_tours')->values();
        }

        if ($totalPaidFilter) {
            $result = $totalPaidFilter == 'asc' ? $result->sortBy('total_paid')->values() : $result->sortByDesc('total_paid')->values();
        }


        if ($totalAdventuresRangeFilter) {
            $minAdventures = $totalAdventuresRange[0];
            $maxAdventures = $totalAdventuresRange[1];

            $result = $result->filter(function ($item) use ($minAdventures, $maxAdventures) {
                return $item['number_of_tours'] >= $minAdventures && $item['number_of_tours'] <= $maxAdventures;
            })->values();
        }

        if ($totalCommissionRangeFilter) {
            $minCommission = $totalCommissionRange[0];
            $maxCommission = $totalCommissionRange[1];

            $result = $result->filter(function ($item) use ($minCommission, $maxCommission) {
                return $item['commission'] >= $minCommission && $item['commission'] <= $maxCommission;
            })->values();
        }

        if ($totalPaidRangeFilter) {
            $minPaid = $totalPaidRange[0];
            $maxPaid = $totalPaidRange[1];

            $result = $result->filter(function ($item) use ($minPaid, $maxPaid) {
                return $item['total_paid'] >= $minPaid && $item['total_paid'] <= $maxPaid;
            })->values();
        }

        //implementing export to excel
        if ($request->input('export') === 'excel') {
            return Excel::download(new DestinationsExport($result), 'destinations.xlsx');
        }

        //implementing pagination
        $totalRecords = $result->count();
        $currentPageResults = $result->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedResult = new Paginator($currentPageResults, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'total' => $totalRecords,
        ]);
        $responseData = $paginatedResult->toArray();
        $responseData['total'] = $totalRecords;

        return ApiResponse::success($responseData);
    }


    /**
     * Get selection table.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */ 
    public function selectiontable(Request $r)
    {
        try {
            $selection = $r->code == 3 ? NaturalDestination::query() : ($r->code == 2 ? City::query() : Country::query());
            $selection->selectRaw($this->list[$r->code]);
            !$r->name ?: $selection->where($this->column[$r->code], 'like', "%{$r->name}%");
            $selection = $selection->limit(15)->get();
            return response()->json(['status' => true, 'response' => $selection]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'response' => $e->getMessage()]);
        }
    }

    /**
     * Get cities.
     * 
     * Updated at 10/12/2024 (user)
     * 
     * @param Request $r Request object
     * @return array
     */
    public function cities(Request $r)
    {
        try {
            $city = (new City)->newQuery();
            !$r->city_name ?: $city->where('city_name', 'like', "%{$r->city_name}%");
            !$r->limit ?: $city->limit($r->limit);
            $city = $city->get();
            return response()->json(['status' => true, 'response' => $city]);
        } catch (Error $e) {
            return response()->json(['status' => false, 'response' => $e]);
        }
    }
}
