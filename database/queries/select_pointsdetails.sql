SELECT 
    p.points_id,
    p.membership_id,
    p.sales_id,
    p.total_purchase,
    p.points_amount,
    pd.total_points,
    pd.redeemable_date,
    pd.redeemed_amount
FROM Points p
JOIN Points_Details pd ON p.points_id = pd.points_id
WHERE p.sales_id = 1;
--s