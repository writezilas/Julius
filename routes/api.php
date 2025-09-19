<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TradeProgressController;

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

// Trade Progress API Routes
Route::prefix('trade-progress')->group(function () {
    // Get all trades progress
    Route::get('/all', [TradeProgressController::class, 'getAllTrades']);
    
    // Get specific trade progress
    Route::get('/{tradeId}', [TradeProgressController::class, 'getTradeProgress']);
    
    // Bulk update progress for multiple trades
    Route::post('/bulk-update', [TradeProgressController::class, 'bulkUpdateProgress']);
    
    // Get progress history for a specific trade
    Route::get('/{tradeId}/history', [TradeProgressController::class, 'getProgressHistory']);
});
