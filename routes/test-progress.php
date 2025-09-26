<?php

use Illuminate\Support\Facades\Route;
use App\Services\ProgressCalculationService;

/**
 * Test route to verify the progress calculation fix
 * Access via: /test-progress/{tradeId}
 */
Route::get('/test-progress/{tradeId}', function($tradeId) {
    try {
        $progressService = new ProgressCalculationService();
        $result = $progressService->computeTradeProgress($tradeId);
        
        return response()->json([
            'success' => true,
            'message' => 'Progress calculation test completed',
            'trade_id' => $tradeId,
            'result' => $result,
            'explanation' => [
                'formula' => '100% - (bought_shares / total_shares * 100)',
                'interpretation' => 'Progress decreases as more shares are bought',
                'example' => 'If 1000 out of 80000 shares are bought: 100% - (1000/80000*100) = 98.75%'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/test-progress-stats/{tradeId}', function($tradeId) {
    try {
        $progressService = new ProgressCalculationService();
        $stats = $progressService->getProgressStatistics($tradeId);
        
        return response()->json([
            'success' => true,
            'message' => 'Progress statistics test completed',
            'trade_id' => $tradeId,
            'statistics' => $stats
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('/test-progress-failure/{tradeId}', function($tradeId) {
    try {
        $progressService = new ProgressCalculationService();
        $failedShares = request('failed_shares', 100);
        $reason = request('reason', 'payment_failed');
        
        $result = $progressService->handleFailedTradeProgressRestoration($tradeId, $failedShares, $reason);
        
        return response()->json([
            'success' => true,
            'message' => 'Progress failure restoration test completed',
            'trade_id' => $tradeId,
            'result' => $result
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});