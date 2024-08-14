<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('forms', FormController::class);
        Route::prefix('forms')->group(function () {
            Route::post('/{slug}/questions', [QuestionController::class, 'store']);
            Route::delete('/{slug}/questions/{id}', [QuestionController::class, 'destroy']);
            Route::post('/{slug}/responses', [ResponseController::class, 'store']);
            Route::get('/{slug}/responses', [ResponseController::class, 'index']);
        });
    });
});
