<?php

namespace App\Http\Controllers;

use App\Models\User;
use Error;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserById(Request $request)
    {
        $id = $request->query('id');

        if (!$id) {
            return response()->json([
                'status' => false,
                'message' => 'id query parameter is required.'
            ], 400);
        }

        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Customize the attributes you want to return
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'country' => $user->country,
            'role' => $user->role,
            'active' => $user->active,
            'suscribed' => $user->suscribed,
            'hear' => $user->hear,
        ];

        return response()->json([
            'status' => true,
            'user' => $userData
        ], 200);
    }

}
