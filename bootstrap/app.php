<?php

declare(strict_types = 1);

use App\Exceptions\GatewayException;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth'  => Authenticate::class,
            'role'  => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $_e, Request $_request): \Illuminate\Http\JsonResponse {
            return response()->json(['message' => 'Resource not found.'], 404);
        });

        $exceptions->render(function (GatewayException $e, Request $_request): \Illuminate\Http\JsonResponse {
            return response()->json(['message' => $e->getMessage()], 502);
        });
    })->create();
