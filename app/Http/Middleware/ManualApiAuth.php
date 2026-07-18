<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ManualApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $parts = explode('|', $token);
        $tokenId = $parts[0];
        $tokenSecret = $parts[1] ?? '';

        $accessToken = DB::table('personal_access_tokens')
            ->where('id', $tokenId)
            ->where('token', hash('sha256', $tokenSecret))
            ->first();

        if (!$accessToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::find($accessToken->tokenable_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
