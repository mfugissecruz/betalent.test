<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Response;

;
use Illuminate\Support\Facades\{Auth, RateLimiter};
use Illuminate\Support\Str;

class Login extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        $credentials = $request->validated();
        $throttleKey = $this->throttleKey($credentials['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey);

            return response()->json(['message' => trans('auth.failed')], Response::HTTP_UNAUTHORIZED);
        }

        RateLimiter::clear($throttleKey);

        $token = $request->user()->createToken('api')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    private function throttleKey(string $email): string
    {
        return Str::transliterate(Str::lower($email) . '|' . request()->ip());
    }

}
