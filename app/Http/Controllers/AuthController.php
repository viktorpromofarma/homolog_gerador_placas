<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('USERNAME', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->PASSWORD)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token     = Str::random(80);
        $expiresAt = now()->addMinutes(30)->timestamp;

        $this->writeEnv([
            'APP_TOKEN'            => $token,
            'APP_TOKEN_EXPIRATION' => $expiresAt,
        ]);

        return response()->json([
            'status'     => true,
            'token'      => $token,
            'expired_at' => now()->addMinutes(30)->toDateTimeString(),
        ]);
    }

    private function writeEnv(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $content);
    }
}
