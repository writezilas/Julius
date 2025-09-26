<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Services\ProgressCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TradeProgressController extends Controller
{
    protected $progressService;

    public function __construct(ProgressCalculationService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Get progress data for all active trades
     *
     * @return JsonResponse
     */
    public function getAllTrades(): JsonResponse
    {
        try {
            $trades = Trade::whereStatus('1')->get();
            $progressData = [];

            foreach ($trades as $trade) {
                $progress = $this->progressService->computeTradeProgress($trade->id);
                
                $progressData[] = [
                    'trade_id' => $trade->id,
                    'trade_name' => $trade->name,
                    'progress_percentage' => round($progress['progress_percentage'], 2),
                    'shares_sold' => $progress['shares_bought'], // Fix: use shares_bought key
                    'total_shares' => $progress['total_shares'],
                    'available_shares' => checkAvailableSharePerTrade($trade->id),
                    'amount' => $trade->amount ?? 0,
                    'category' => $trade->category ?? 'General',
                    'description' => $trade->description ?? 'Trading Opportunity',
                    'status' => $trade->status,
                    'last_updated' => now()->toISOString()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $progressData,
                'message' => 'Trade progress data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching trade progress data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve trade progress data',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get progress data for a specific trade
     *
     * @param Request $request
     * @param int $tradeId
     * @return JsonResponse
     */
    public function getTradeProgress(Request $request, int $tradeId): JsonResponse
    {
        try {
            $trade = Trade::find($tradeId);

            if (!$trade) {
                return response()->json([
                    'success' => false,
                    'error' => 'Trade not found',
                    'message' => "Trade with ID {$tradeId} does not exist"
                ], 404);
            }

            $progress = $this->progressService->computeTradeProgress($tradeId);
            
            $responseData = [
                'trade_id' => $trade->id,
                'trade_name' => $trade->name,
                'progress_percentage' => round($progress['progress_percentage'], 2),
                'shares_sold' => $progress['shares_bought'], // Fix: use shares_bought key
                'total_shares' => $progress['total_shares'],
                'available_shares' => checkAvailableSharePerTrade($tradeId),
                'amount' => $trade->amount ?? 0,
                'category' => $trade->category ?? 'General',
                'description' => $trade->description ?? 'Trading Opportunity',
                'status' => $trade->status,
                'last_updated' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Trade progress data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching progress for trade {$tradeId}: " . $e->getMessage(), [
                'trade_id' => $tradeId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve trade progress data',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Bulk update progress for multiple trades (for real-time updates)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdateProgress(Request $request): JsonResponse
    {
        try {
            $tradeIds = $request->input('trade_ids', []);
            
            if (empty($tradeIds) || !is_array($tradeIds)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid trade IDs provided',
                    'message' => 'Please provide an array of trade IDs to update'
                ], 400);
            }

            $progressData = [];
            $errors = [];

            foreach ($tradeIds as $tradeId) {
                try {
                    $trade = Trade::find($tradeId);
                    
                    if (!$trade) {
                        $errors[] = "Trade with ID {$tradeId} not found";
                        continue;
                    }

                    $progress = $this->progressService->computeTradeProgress($tradeId);
                    
                    $progressData[] = [
                        'trade_id' => $trade->id,
                        'trade_name' => $trade->name,
                        'progress_percentage' => round($progress['progress_percentage'], 2),
                        'shares_sold' => $progress['shares_bought'], // Fix: use shares_bought key
                        'total_shares' => $progress['total_shares'],
                        'available_shares' => checkAvailableSharePerTrade($tradeId),
                        'amount' => $trade->amount ?? 0,
                        'last_updated' => now()->toISOString()
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error processing trade {$tradeId}: " . $e->getMessage();
                    Log::error("Bulk progress update error for trade {$tradeId}: " . $e->getMessage());
                }
            }

            $response = [
                'success' => true,
                'data' => $progressData,
                'message' => 'Bulk progress update completed'
            ];

            if (!empty($errors)) {
                $response['warnings'] = $errors;
                $response['message'] .= ' with some warnings';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error in bulk progress update: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to perform bulk progress update',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get progress history for a specific trade (for analytics)
     *
     * @param Request $request
     * @param int $tradeId
     * @return JsonResponse
     */
    public function getProgressHistory(Request $request, int $tradeId): JsonResponse
    {
        try {
            $trade = Trade::find($tradeId);

            if (!$trade) {
                return response()->json([
                    'success' => false,
                    'error' => 'Trade not found',
                    'message' => "Trade with ID {$tradeId} does not exist"
                ], 404);
            }

            // Get user shares for this trade to build history
            $userShares = \App\Models\UserShare::where('trade_id', $tradeId)
                ->orderBy('created_at', 'asc')
                ->get(['id', 'amount', 'share_quantity', 'status', 'created_at', 'updated_at']);

            $history = [];
            $cumulativeShares = 0;
            $totalShares = $trade->share_quantity ?? 1;

            foreach ($userShares as $share) {
                if ($share->status === 'completed') {
                    $cumulativeShares += $share->share_quantity;
                }

                $progressAtTime = ($cumulativeShares / $totalShares) * 100;

                $history[] = [
                    'timestamp' => $share->created_at->toISOString(),
                    'shares_sold' => $cumulativeShares,
                    'total_shares' => $totalShares,
                    'progress_percentage' => round($progressAtTime, 2),
                    'action' => $share->status === 'completed' ? 'share_completed' : 'share_purchased',
                    'share_quantity' => $share->share_quantity,
                    'amount' => $share->amount
                ];
            }

            // Add current state
            $currentProgress = $this->progressService->computeTradeProgress($tradeId);
            $history[] = [
                'timestamp' => now()->toISOString(),
                'shares_sold' => $currentProgress['shares_sold'],
                'total_shares' => $currentProgress['total_shares'],
                'progress_percentage' => round($currentProgress['progress_percentage'], 2),
                'action' => 'current_state',
                'is_current' => true
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'trade_id' => $tradeId,
                    'trade_name' => $trade->name,
                    'history' => $history,
                    'total_events' => count($history)
                ],
                'message' => 'Trade progress history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching progress history for trade {$tradeId}: " . $e->getMessage(), [
                'trade_id' => $tradeId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve trade progress history',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
