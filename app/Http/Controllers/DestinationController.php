<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ActionLog;
use App\Models\City;
use App\Models\Country;
use App\Models\Destination;
use App\Models\NaturalDestination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{

    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        $user = auth()->user();
        $category = $request->category;
        $id = $request->id;
        $destination_name = '';
        switch ($category) {
            case 'natural_destination':
                $destination = NaturalDestination::with('destination')->where('t_natural_id', $id)->first();
                $destination_name = $destination->destination_name;

                break;
            case 'country':
                $destination = Country::with('destination')->where('t_country_id', $id)->first();
                $destination_name = $destination->name;
                break;
            case 'city':
                $destination = City::with('destination')->where('t_city_id', $id)->first();
                $destination_name = $destination->city_name;
                break;
            default:
                $destination = null;
        }


        if ($destination === null) {
            return ApiResponse::notFound('Destination not found');
        }

        try {
            $validatedData = $request->validate([
                'overview' => 'required|string',
                'quick_facts' => 'required|string',
                'things_to_do' => 'required|string',
                'travel_tips' => 'required|string',
                'best_time_to_visit' => 'required|string',
                'slug' => 'required|string',
                'excerpt' => 'required|string',
                'meta_description' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();

            $customErrors = [];
            foreach ($errors as $field => $messages) {
                $customErrors[] = implode(' ', $messages); // Unir todos los mensajes de un campo en una sola cadena
            }

            return ApiResponse::error([implode(' ', $customErrors)]);
        }

        $destination_detail = $destination->destination;

        if ($destination_detail) {

            $destination_detail->update($validatedData);

            ActionLog::create([
                'user_id' => $user->id,
                'type' => 'Update',
                'action' => 'Destination update successfully' . ' ' . $category . ' ' . $destination_name,
                'item' => 'Destination',
            ]);

            return ApiResponse::success($destination_detail, 'Destination updated successfully');
        } else {
            $newDestination = Destination::create($validatedData);

            $destination->update(['destination_id' => $newDestination->id]);

            $destination->save();

            ActionLog::create([
                'user_id' => $user->id,
                'type' => 'Create',
                'action' => 'Destination created successfully' . ' ' . $category . ' ' . $destination_name,
                'item' => 'Destination',
            ]);

            return ApiResponse::success($newDestination, 'Destination created successfully');


        }
    }

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

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {

    }
}
