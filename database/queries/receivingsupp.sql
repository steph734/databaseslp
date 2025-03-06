-- Recent receiving records
SELECT 
    r.receiving_id,
    r.receiving_date,
    s.supplier_name,
    r.total_quantity,
    r.total_cost,
    r.status
FROM Receiving r
JOIN Supplier s ON r.supplier_id = s.supplier_id
ORDER BY r.receiving_date DESC
LIMIT 10;

-- Supplier performance s
SELECT 
    s.supplier_name,
    COUNT(r.receiving_id) AS total_receivings,
    SUM(r.total_quantity) AS total_items_received,
    SUM(r.total_cost) AS total_cost
FROM Supplier s
LEFT JOIN Receiving r ON s.supplier_id = r.supplier_id
GROUP BY s.supplier_id, s.supplier_name
ORDER BY total_items_received DESC;