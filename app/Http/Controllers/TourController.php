<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $allResults = collect();

        if ($request->has('country')) {
            $countries = $this->extractArrayFromQueryParam($request->input('country'));
            $allResults = $allResults->merge($this->filterByCountry($countries));
        }

        if ($request->has('city')) {
            $cities = $this->extractArrayFromQueryParam($request->input('city'));
            $allResults = $allResults->merge($this->filterByCity($cities));
        }

        if ($request->has('natural_destination')) {
            $naturalDestinations = $this->extractArrayFromQueryParam($request->input('natural_destination'));
            $allResults = $allResults->merge($this->filterByNaturalDestination($naturalDestinations));
        }

        // Paginate manually
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginator = new LengthAwarePaginator(
            $allResults->slice($offset, $perPage),
            $allResults->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return ApiResponse::success($paginator);
    }

    protected function extractArrayFromQueryParam($param)
    {

        $param = trim($param, '[]');

        $values = explode(',', $param);

        return array_map('trim', $values);
    }

    protected function filterByCountry($countryIds)
    {
        return Tour::with(['cities', 'natural_destination', 'type', 'countries'])
            ->whereHas('countries', function ($query) use ($countryIds) {
                $query->whereIn('t_country_id', $countryIds);
            })->get();
    }

    protected function filterByCity($cityIds)
    {
        return Tour::with(['cities', 'natural_destination', 'type', 'countries'])
            ->whereHas('cities', function ($query) use ($cityIds) {
                $query->whereIn('t_city_id', $cityIds);
            })->get();
    }

    protected function filterByNaturalDestination($naturalIds)
    {
        return Tour::with(['cities', 'natural_destination', 'type', 'countries'])
            ->whereHas('natural_destination', function ($query) use ($naturalIds) {
                $query->whereIn('t_natural_id', $naturalIds);
            })->get();
    }


}
