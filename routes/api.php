<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix'=> 'admin'], function () {
    Route::post('/login', AdminAuthController::class)->name('admin.login');
});


Route::middleware(['admin.role'])
      ->group(function () {
         Route::prefix('admin')->group(function () {
            Route::apiResource('subjects', SubjectController::class)
                  ->names('admin.subject');
            Route::apiResource("quizzes", QuizController::class)
                 ->names("admin.quizzes");
         });
});