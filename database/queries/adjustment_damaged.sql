-- Recent stock adjustments
SELECT 
    al.adjustment_id,
    p.product_name,
    al.adjustment_type,
    al.total_adjusted_quantity,
    al.adjustment_date,
    sad.description
FROM AdjustmentLine al
JOIN Product p ON al.product_id = p.product_id
JOIN StockAdjustmentDetails sad ON al.adjustment_id = sad.adjustment_id
ORDER BY al.adjustment_date DESC
LIMIT 10;

-- Total damaged stock by category 
SELECT 
    c.category_name,
    SUM(sad.adjusted_quantity) AS total_damaged,
    COUNT(DISTINCT al.adjustment_id) AS adjustment_count
FROM StockAdjustmentDetails sad
JOIN AdjustmentLine al ON sad.adjustment_id = al.adjustment_id
JOIN Product p ON sad.product_id = p.product_id
JOIN Category c ON p.category_id = c.category_id
WHERE al.adjustment_type = 'Damaged item'
GROUP BY c.category_name
ORDER BY total_damaged DESC;