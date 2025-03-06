-- Active memberships
SELECT 
    m.membership_id,
    c.name AS customer_name,
    m.status,
    m.date_renewal,
    SUM(p.total_points) AS total_points
FROM Membership m
JOIN Customer c ON m.customer_id = c.customer_id
LEFT JOIN Points pts ON m.membership_id = pts.membership_id
LEFT JOIN Points_Details p ON pts.points_id = p.points_id
WHERE m.status = 'Active'
GROUP BY m.membership_id, c.name, m.status, m.date_renewal;

-- Points expiring soon s
SELECT 
    pd.pd_id,
    c.name AS customer_name,
    pd.total_points,
    m.date_renewal AS next_redeemable_date,
    pd.redeemed_amount
FROM Points_Details pd
JOIN Points p ON pd.points_id = p.points_id
JOIN Membership m ON p.membership_id = m.membership_id
JOIN Customer c ON m.customer_id = c.customer_id
WHERE 
    -- Adjust redeemable_date to the next annual renewal date
    DATE_ADD(m.date_renewal, INTERVAL 
             FLOOR(DATEDIFF(CURDATE(), m.date_renewal) / 365) + 1 YEAR) 
    <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    -- Ensure points haven't been fully redeemed
    AND pd.redeemed_amount < pd.total_points
    -- Only active memberships
    AND m.status = 'Active';