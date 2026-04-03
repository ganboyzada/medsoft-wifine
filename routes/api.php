<?php

use App\Http\Controllers\Api\GatewaySessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('gateway')
    ->middleware(['gateway.signature', 'throttle:gateway-api'])
    ->group(function () {
        Route::post('/sessions/open', [GatewaySessionController::class, 'open']);
        Route::get('/sessions/{sessionToken}', [GatewaySessionController::class, 'status']);
        Route::post('/sessions/{sessionToken}/authorize', [GatewaySessionController::class, 'authorizeAccess']);
    });
