<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Traveler;

class TravelersController extends Controller
{
    // Method to get travelers
    public function getTravelers(Request $request)
    {
        if ($request->has('traveler_id')) {
            $traveler_id = $request->query('traveler_id');
            $traveler = Traveler::where('traveler_id', $traveler_id)->first();

            if ($traveler) {
                return response()->json($traveler);
            } else {
                return response()->json(['message' => 'Traveler not found'], 404);
            }
        } else {
            $travelers = Traveler::all();
            return response()->json($travelers);
        }
    }


    // Method to write travelers
    public function writeTravelers(Request $request)
    {
        $request->validate([
            'traveler_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'last' => 'required|string|max:255',
            'birth' => 'required|date',
            'passport' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'issue' => 'required|date',
            'expire' => 'required|date',
            'mail' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string',
            'country' => 'required|string|max:255',
            'lead' => 'required|string|max:255',
        ]);

        $traveler = Traveler::create($request->all());

        return response()->json($traveler, 201);
    }
}
