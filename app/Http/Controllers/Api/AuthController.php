<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function createToken(Request $request)
    {
        $request->validate(['name' => 'nullable|string|max:100']);

        $plain = Str::random(40);
        $hash = hash('sha256', $plain);

        $token = ApiToken::create([
            'name' => $request->input('name', 'cli'),
            'token' => $hash,
            'abilities' => json_encode(['*']),
        ]);

        return response()->json(['token' => $plain, 'id' => $token->id]);
    }
}
