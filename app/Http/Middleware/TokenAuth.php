<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token not provided',
            ], 401);
        }

        $validToken  = env('APP_TOKEN');
        $expiresAt   = (int) env('APP_TOKEN_EXPIRATION');

        if (!$validToken || $token !== $validToken) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid token',
            ], 401);
        }

        if (!$expiresAt || time() > $expiresAt) {
            return response()->json([
                'status'  => false,
                'message' => 'Token expired, please login again',
            ], 401);
        }

        return $next($request);
    }
}
