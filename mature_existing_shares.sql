-- MATURE EXISTING SHARES SCRIPT
-- This script will make all current shares mature immediately
-- without affecting the logic for future trades

-- Show current state before changes
SELECT 'BEFORE CHANGES - Current Shares Status:' as info;
SELECT 
    id,
    ticket_no,
    status,
    get_from,
    is_ready_to_sell,
    start_date,
    period,
    matured_at,
    created_at,
    CASE 
        WHEN start_date IS NOT NULL AND period > 0 THEN 
            DATE_ADD(start_date, INTERVAL period DAY) 
        ELSE NULL 
    END as calculated_maturity,
    CASE 
        WHEN start_date IS NOT NULL AND period > 0 THEN 
            (NOW() >= DATE_ADD(start_date, INTERVAL period DAY)) 
        ELSE 0 
    END as is_past_maturity
FROM user_shares 
ORDER BY created_at DESC;

-- Preview what will be updated (shows records that will be affected)
SELECT 'PREVIEW - Shares that will be matured:' as info;
SELECT 
    id,
    ticket_no,
    status,
    get_from,
    is_ready_to_sell as current_ready_status,
    start_date,
    period,
    matured_at as current_matured_at
FROM user_shares 
WHERE is_ready_to_sell = 0
    AND status = 'completed'
    AND start_date IS NOT NULL 
    AND period > 0;

-- Update existing shares to be mature
-- This sets:
-- 1. is_ready_to_sell = 1 (makes them ready to sell)
-- 2. matured_at = NOW() (records when they were matured)
-- Only affects shares that are:
-- - Currently not ready to sell (is_ready_to_sell = 0)
-- - Have completed status
-- - Have valid start_date and period
UPDATE user_shares 
SET 
    is_ready_to_sell = 1,
    matured_at = NOW()
WHERE is_ready_to_sell = 0
    AND status = 'completed'
    AND start_date IS NOT NULL 
    AND period > 0;

-- Show how many shares were affected
SELECT ROW_COUNT() as 'Number of shares matured';

-- Show final state after changes
SELECT 'AFTER CHANGES - Updated Shares Status:' as info;
SELECT 
    id,
    ticket_no,
    status,
    get_from,
    is_ready_to_sell,
    start_date,
    period,
    matured_at,
    created_at,
    CASE 
        WHEN start_date IS NOT NULL AND period > 0 THEN 
            DATE_ADD(start_date, INTERVAL period DAY) 
        ELSE NULL 
    END as calculated_maturity,
    CASE 
        WHEN start_date IS NOT NULL AND period > 0 THEN 
            (NOW() >= DATE_ADD(start_date, INTERVAL period DAY)) 
        ELSE 0 
    END as is_past_maturity
FROM user_shares 
ORDER BY created_at DESC;

-- Final summary
SELECT 'SUMMARY:' as info;
SELECT 
    COUNT(*) as total_shares,
    COUNT(CASE WHEN is_ready_to_sell = 0 THEN 1 END) as not_ready,
    COUNT(CASE WHEN is_ready_to_sell = 1 THEN 1 END) as ready,
    COUNT(CASE WHEN matured_at IS NOT NULL THEN 1 END) as has_matured_timestamp
FROM user_shares;