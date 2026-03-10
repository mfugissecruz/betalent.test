<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class Authenticate extends \Illuminate\Auth\Middleware\Authenticate
{
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
