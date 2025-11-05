<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Obtener el usuario autenticado si existe (con Sanctum)
        $user = Auth::guard('sanctum')->user();

        // Agregar el usuario (o null) al request
        $request->merge(['auth_user' => $user]);

        return $next($request);
    }
}
