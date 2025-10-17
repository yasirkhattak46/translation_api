<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->bearerToken();
        if (!$header) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = ApiToken::where('token', hash('sha256', $header))->first();
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token->update(['last_used_at' => now()]);
        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
