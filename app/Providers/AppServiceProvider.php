<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

/**
 * @mixin \Illuminate\Contracts\Routing\ResponseFactory
 */

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       app(Response::macro('success', function (mixed $data = null, int $code = 200): JsonResponse {
            return response()->json([
                'success'=> true,
                'data' => $data
            ], $code);
        }));

        app(Response::macro('error', function (string $message, mixed $errors = null, int $code = 500): JsonResponse             {
            return response()->json([
                'message' => $message, 
                'errors' => $errors
            ], $code);
        }));
    }
}
