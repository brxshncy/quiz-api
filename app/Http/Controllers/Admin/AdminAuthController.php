<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
/**
     * Handle the incoming request.
     */
    public function __invoke(AdminLoginRequest $request, AuthService $authService)
    {
        try {
            $data = $authService->loginAsAdmin($request->validated());
            return response()->success($data);
        } catch (ValidationException $e) {  
            return response()->error('Login failed', $e->errors(), 422);
        }
    }
}
