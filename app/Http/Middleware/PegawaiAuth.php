<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user sudah login via token Sanctum
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'status' => 401,    
                'success' => false,
                'message' => 'Token tidak valid atau tidak ada.'
            ], 401);
        }

        return $next($request);
    }
}
