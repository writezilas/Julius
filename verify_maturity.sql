-- VERIFY MATURITY UPDATE RESULTS
-- Check that all shares are now mature

SELECT 'CURRENT STATUS OF ALL SHARES:' as info;
SELECT 
    id,
    ticket_no,
    status,
    get_from,
    is_ready_to_sell as ready_to_sell,
    start_date,
    period,
    matured_at,
    CASE 
        WHEN is_ready_to_sell = 1 THEN '✅ MATURE'
        ELSE '⏳ NOT MATURE'
    END as maturity_status,
    CASE 
        WHEN start_date IS NOT NULL AND period > 0 THEN 
            DATE_ADD(start_date, INTERVAL period DAY) 
        ELSE NULL 
    END as original_maturity_date
FROM user_shares 
ORDER BY created_at DESC;

SELECT 'SUMMARY:' as info;
SELECT 
    COUNT(*) as total_shares,
    COUNT(CASE WHEN is_ready_to_sell = 0 THEN 1 END) as still_not_ready,
    COUNT(CASE WHEN is_ready_to_sell = 1 THEN 1 END) as now_ready,
    COUNT(CASE WHEN matured_at IS NOT NULL THEN 1 END) as has_matured_timestamp,
    CASE 
        WHEN COUNT(CASE WHEN is_ready_to_sell = 0 THEN 1 END) = 0 
        THEN '✅ ALL SHARES MATURED' 
        ELSE '❌ SOME SHARES NOT MATURED' 
    END as result
FROM user_shares;