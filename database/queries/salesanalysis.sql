-- Daily sales summary
SELECT 
    s.sale_date,
    COUNT(s.sales_id) AS total_sales,
    SUM(s.total_amount) AS total_revenue,
    SUM(sl.quantity) AS total_items_sold
FROM Sales s
JOIN SalesLine sl ON s.sales_id = sl.sales_id
GROUP BY s.sale_date
ORDER BY s.sale_date DESC;

-- Top selling products (last 30 days)s
SELECT 
    p.product_name,
    SUM(sl.quantity) AS total_sold,
    SUM(sl.subtotal_price) AS total_sales_amount
FROM SalesLine sl
JOIN Product p ON sl.product_id = p.product_id
JOIN Sales s ON sl.sales_id = s.sales_id
WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY p.product_id, p.product_name
ORDER BY total_sold DESC
LIMIT 5;