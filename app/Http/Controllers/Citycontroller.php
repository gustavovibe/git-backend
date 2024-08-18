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


    public function DestinatioCityCountryNaturalDestination(Request $request)
    {

        $perPage = 5;

        $q = $request->input('q');

        if ($q) {

            $country = Country::where('name', 'like', $q . '%')->paginate($perPage);
            $responseDataCountry = $country->toArray();
            $responseDataCountry['data'] = CountryResource::collection($country->items());


            $city = City::where('city_name', 'like', $q . '%')->paginate($perPage);
            $responseDataCity = $city->toArray();
            $responseDataCity['data'] = CityResource::collection($city->items());


            $natural = NaturalDestination::where('destination_name', 'like', $q . '%')->paginate($perPage);
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

    public function destinations(Request $r)
    {
        try {
            $destinations = ToursFilters::destinations($r);
            return response()->json(['status' => true, 'count' => count($destinations), 'response' => $destinations]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'response' => $e->getMessage()]);
        }
    }


    public function destinationsV2(Request $request)
    {
        $q = $request->input('q');
        $category = $request->input('categoryFilter');
        $totalPaidFilter = $request->input('totalPaidFilter');
        $commissionFilter = $request->input('commissionFilter');
        $alphabeticOrder = $request->input('alphabeticOrderFilter');
        $perPage = $request->input('perPage', 15);
        $page = $request->input('page', 1);
        $result = [];

        if ($category == 'city') {
            $entities = City::get();
        } elseif ($category == 'country') {
            $entities = Country::get();
        } elseif ($category == 'natural_destination') {
            $entities = NaturalDestination::get();
        } else {
            return ApiResponse::error('Invalid category filter');
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
                'total_paid' => $totalPaid
            ];

            if ($q && stripos($item['name'], $q) !== 0) {
                continue;
            }

            $result[] = $item;
        }

        $result = collect($result);
        $totalRecords = $result->count();
        $totalPages = ceil($totalRecords / $perPage);

        if ($alphabeticOrder == 'desc') {
            $result = $result->sortByDesc('name')->values();
        } else {
            $result = $result->sortBy('name')->values();
        }

        if ($commissionFilter) {
            $result = $commissionFilter == 'asc' ? $result->sortBy('commission')->values() : $result->sortByDesc('commission')->values();
        }

        if ($totalPaidFilter) {
            $result = $totalPaidFilter == 'asc' ? $result->sortBy('total_paid')->values() : $result->sortByDesc('total_paid')->values();
        }

        $currentPageResults = $result->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedResult = new Paginator($currentPageResults, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'total' => $totalRecords,
        ]);

        $responseData = $paginatedResult->toArray();
        $responseData['total'] = $totalRecords;

        return ApiResponse::success($responseData);
    }


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
