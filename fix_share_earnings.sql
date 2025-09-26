-- FIX MISSING EARNINGS FOR MATURED SHARES
-- Calculate and add earnings based on trade period percentage

-- Show current state
SELECT 'CURRENT STATE - Matured shares with missing earnings:' as info;
SELECT 
    us.id,
    us.ticket_no,
    us.period,
    us.share_will_get as investment,
    us.profit_share as current_earning,
    us.total_share_count,
    us.matured_at,
    tp.percentage as period_rate,
    ROUND((tp.percentage / 100) * us.total_share_count, 2) as calculated_earning,
    (us.share_will_get + ROUND((tp.percentage / 100) * us.total_share_count, 2)) as expected_total
FROM user_shares us
LEFT JOIN trade_periods tp ON us.period = tp.days
WHERE us.is_ready_to_sell = 1 
    AND us.matured_at IS NOT NULL
    AND (us.profit_share IS NULL OR us.profit_share = 0)
    AND tp.percentage IS NOT NULL;

-- Update earnings for shares that are matured but missing profit calculation
UPDATE user_shares us
JOIN trade_periods tp ON us.period = tp.days
SET 
    us.profit_share = ROUND((tp.percentage / 100) * us.total_share_count, 2),
    us.updated_at = NOW()
WHERE us.is_ready_to_sell = 1 
    AND us.matured_at IS NOT NULL
    AND (us.profit_share IS NULL OR us.profit_share = 0)
    AND tp.percentage IS NOT NULL;

-- Show how many shares were updated
SELECT ROW_COUNT() as 'Shares with earnings calculated';

-- Show updated results
SELECT 'AFTER UPDATE - Fixed shares with calculated earnings:' as info;
SELECT 
    us.id,
    us.ticket_no,
    us.period,
    us.share_will_get as investment,
    us.profit_share as earning,
    (us.share_will_get + us.profit_share) as total,
    us.total_share_count,
    us.matured_at,
    tp.percentage as period_rate,
    CONCAT(tp.percentage, '%') as rate_display
FROM user_shares us
LEFT JOIN trade_periods tp ON us.period = tp.days
WHERE us.ticket_no = 'AB-17582976921162'
   OR (us.is_ready_to_sell = 1 AND us.matured_at IS NOT NULL AND us.profit_share > 0)
ORDER BY us.updated_at DESC
LIMIT 10;

-- Verification summary
SELECT 'VERIFICATION SUMMARY:' as info;
SELECT 
    COUNT(*) as total_matured_shares,
    COUNT(CASE WHEN profit_share > 0 THEN 1 END) as shares_with_earnings,
    COUNT(CASE WHEN profit_share IS NULL OR profit_share = 0 THEN 1 END) as shares_without_earnings,
    SUM(share_will_get) as total_investment,
    SUM(COALESCE(profit_share, 0)) as total_earnings,
    SUM(share_will_get + COALESCE(profit_share, 0)) as total_value
FROM user_shares 
WHERE is_ready_to_sell = 1 AND matured_at IS NOT NULL;