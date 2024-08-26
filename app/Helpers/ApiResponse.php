<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data, $message = "Ok", $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    public static function error($message, $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    public static function invalid($message, $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    public static function notFound($message, $statusCode = 404)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    public static function unauthorized($message, $statusCode = 401)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    public static function login($data, $token, $message, $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'access_token' => $token,
            'message' => $message
        ], $statusCode);
    }

    public static function logout($message, $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ], $statusCode);
    }
}
