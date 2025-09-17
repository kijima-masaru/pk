<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// AI最適化関連のルート
Route::prefix('ai-optimization')->group(function () {
    Route::post('/start', [App\Http\Controllers\AIOptimizationController::class, 'startOptimization']);
    Route::post('/save-party', [App\Http\Controllers\AIOptimizationController::class, 'saveOptimizedParty']);
    Route::post('/simulate-battle', [App\Http\Controllers\AIOptimizationController::class, 'simulateBattle']);
    Route::post('/evaluate-party', [App\Http\Controllers\AIOptimizationController::class, 'evaluateParty']);
    Route::get('/progress', [App\Http\Controllers\AIOptimizationController::class, 'getOptimizationProgress']);
});
