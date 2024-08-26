<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Country;
use App\Models\Destination;
use App\Models\NaturalDestination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = $request->category;
        $id = $request->id;

        switch ($category) {
            case 'natural_destination':
                $destination = NaturalDestination::with('destination')->where('t_natural_id', $id)->first();
                break;
            case 'country':
                $destination = Country::with('destination')->where('t_country_id', $id)->first();
                break;
            case 'city':
                $destination = City::with('destination')->where('t_city_id', $id)->first();
                break;
            default:
                return ApiResponse::invalid('Invalid category');
        }

        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destination not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'overview' => 'required|string',
            'quick_facts' => 'required|string',
            'things_to_do' => 'required|string',
            'travel_tips' => 'required|string',
            'best_time_to_visit' => 'required|string',
            'slug' => 'required|string|unique:destinations,slug,' . ($destination->destination->id ?? 'null'),
            'excerpt' => 'required|string',
            'meta_description' => 'required|string',
        ]);

        $destination_detail = $destination->destination;

        if ($destination_detail) {
            $destination_detail->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => $destination_detail,
                'message' => 'Destination updated successfully',
            ], 200);
        } else {
            $newDestination = Destination::create($validatedData);
            $destination->update(['destination_id' => $newDestination->id]);

            $destination->save();

            return response()->json([
                'success' => true,
                'data' => $newDestination,
                'message' => 'Destination created successfully',
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $category = $request->query('category');
        $destination = null;

        switch ($category) {
            case 'natural_destination':
                $destination = NaturalDestination::with('destination')->where('t_natural_id', $id)->first();;
                break;
            case 'country':
                $destination = Country::with('destination')->where('t_country_id', $id)->first();
                break;
            case 'city':
                $destination = City::with('destination')->where('t_city_id', $id)->first();
                break;
            default:
                return ApiResponse::invalid('Invalid category');
        }

        if (!$destination) {
            return ApiResponse::notFound('Destination not found');
        }

        return ApiResponse::success($destination);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
