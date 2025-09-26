-- ====================================================
-- SQL Script: Mature All Running Shares
-- ====================================================
-- This script makes all currently "running" shares mature (available)
-- without changing any application logic for future trades.
--
-- WHAT IT DOES:
-- 1. Finds shares with status='completed' AND is_ready_to_sell=0 (running shares)
-- 2. Sets is_ready_to_sell=1 (makes them available/mature)
-- 3. Sets matured_at timestamp for proper tracking
-- 4. Updates updated_at for audit trail
--
-- WHAT IT DOESN'T CHANGE:
-- - No application code modifications
-- - No logic changes that affect future trades
-- - No database schema changes
-- ====================================================

-- Show current running shares before making changes
SELECT 
    'BEFORE UPDATE - Currently Running Shares:' as info,
    COUNT(*) as count
FROM user_shares 
WHERE status = 'completed' 
    AND is_ready_to_sell = 0 
    AND start_date IS NOT NULL 
    AND period IS NOT NULL;

-- Display the shares that will be affected
SELECT 
    id,
    ticket_no,
    user_id,
    status,
    is_ready_to_sell,
    start_date,
    period,
    DATEDIFF(NOW(), start_date) as days_elapsed,
    CASE 
        WHEN DATEDIFF(NOW(), start_date) >= period THEN 'NATURALLY_MATURED' 
        ELSE 'WILL_BE_FORCE_MATURED' 
    END as maturity_type
FROM user_shares 
WHERE status = 'completed' 
    AND is_ready_to_sell = 0 
    AND start_date IS NOT NULL 
    AND period IS NOT NULL
ORDER BY start_date;

-- Update all running shares to make them mature/available
UPDATE user_shares 
SET 
    is_ready_to_sell = 1,
    matured_at = CASE 
        -- If share has naturally matured, use the calculated maturity date
        WHEN DATEDIFF(NOW(), start_date) >= period THEN 
            DATE_ADD(start_date, INTERVAL period DAY)
        -- If share hasn't naturally matured yet, use current timestamp
        ELSE NOW()
    END,
    updated_at = NOW()
WHERE status = 'completed' 
    AND is_ready_to_sell = 0 
    AND start_date IS NOT NULL 
    AND period IS NOT NULL;

-- Show results after update
SELECT 
    'AFTER UPDATE - Shares Now Available:' as info,
    COUNT(*) as count
FROM user_shares 
WHERE status = 'completed' 
    AND is_ready_to_sell = 1 
    AND matured_at IS NOT NULL
    AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);

-- Show remaining running shares (should be 0)
SELECT 
    'REMAINING RUNNING SHARES:' as info,
    COUNT(*) as count
FROM user_shares 
WHERE status = 'completed' 
    AND is_ready_to_sell = 0 
    AND start_date IS NOT NULL 
    AND period IS NOT NULL;

-- Verification query - show the shares that were just updated
SELECT 
    'UPDATED SHARES VERIFICATION:' as info,
    id,
    ticket_no,
    user_id,
    is_ready_to_sell,
    matured_at,
    'NOW_AVAILABLE' as new_status
FROM user_shares 
WHERE status = 'completed' 
    AND is_ready_to_sell = 1 
    AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
ORDER BY updated_at DESC;

-- ====================================================
-- SUMMARY OF CHANGES:
-- ====================================================
-- ✅ Changed is_ready_to_sell from 0 to 1 for running shares
-- ✅ Set matured_at timestamp for proper tracking
-- ✅ Updated updated_at for audit trail
-- ✅ NO APPLICATION LOGIC CHANGED
-- ✅ NO FUTURE TRADE LOGIC AFFECTED
-- ✅ ShareStatusService will automatically recognize them as "Available"
-- ✅ Users will see "Available" status in sold shares view
-- ====================================================