<?php

namespace App\Helpers;

use App\Models\UserShare;
use App\Services\ShareStatusService;

class StatusHelper
{
    /**
     * Get consistent status information for any share
     * This ensures all pages show the same status using ShareStatusService
     * 
     * @param UserShare $share The share to get status for
     * @param string|null $context 'bought' for bought-shares page, 'sold' for sold-shares page, null for auto-detect
     */
    public static function getShareStatus(UserShare $share, ?string $context = null): array
    {
        $statusService = app(ShareStatusService::class);
        return $statusService->getShareStatus($share, $context);
    }
    
    /**
     * Get consistent pairing statistics for any share
     */
    public static function getPairingStats(UserShare $share): array
    {
        $statusService = app(ShareStatusService::class);
        return $statusService->getPairingStats($share);
    }
    
    /**
     * Get time remaining information for a share
     * 
     * @param UserShare $share The share to get timer for
     * @param string|null $context 'bought' for bought-shares page, 'sold' for sold-shares page, null for auto-detect
     */
    public static function getTimeRemaining(UserShare $share, ?string $context = null): array
    {
        $statusService = app(ShareStatusService::class);
        return $statusService->getTimeRemaining($share, $context);
    }
    
    /**
     * Render status badge HTML for consistent display
     * 
     * @param UserShare $share The share to render badge for
     * @param array $additionalClasses Additional CSS classes
     * @param string|null $context 'bought' for bought-shares page, 'sold' for sold-shares page, null for auto-detect
     */
    public static function renderStatusBadge(UserShare $share, array $additionalClasses = [], ?string $context = null): string
    {
        $statusInfo = self::getShareStatus($share, $context);
        $classes = array_merge(['badge', $statusInfo['class']], $additionalClasses);
        $classString = implode(' ', $classes);
        
        return "<span class=\"{$classString}\" title=\"{$statusInfo['description']}\">
                    {$statusInfo['status']}
                </span>";
    }
    
    /**
     * Get formatted statistics summary
     */
    public static function getStatsSummary(UserShare $share): array
    {
        $stats = self::getPairingStats($share);
        
        return [
            'total_transactions' => $stats['total'],
            'confirmed' => $stats['paid'],
            'awaiting_confirmation' => $stats['awaiting_confirmation'],
            'unpaid' => $stats['unpaid'],
            'failed' => $stats['failed'],
            'pending_total' => $stats['awaiting_confirmation'] + $stats['unpaid']
        ];
    }
}