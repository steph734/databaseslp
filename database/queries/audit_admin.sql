-- Recent admin activities
SELECT 
    al.auditlog_id,
    al.login_datetime,
    a.username,
    al.description,
    al.status
FROM Audit_Log al
JOIN Admin a ON al.admin_id = a.admin_id
ORDER BY al.login_datetime DESC
LIMIT 10;
 
-- Admin activity summary 
SELECT 
    a.username,
    COUNT(al.auditlog_id) AS total_actions,
    SUM(CASE WHEN al.status = 'Success' THEN 1 ELSE 0 END) AS successful_actions
FROM Admin a
LEFT JOIN Audit_Log al ON a.admin_id = al.admin_id
GROUP BY a.admin_id, a.username
ORDER BY total_actions DESC;