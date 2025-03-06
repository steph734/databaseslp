-- Get current stock levels for all productss
SELECT 
    p.product_id,
    p.product_name,
    p.quantity AS current_stock,
    p.price,
    c.category_name,
    s.supplier_name
FROM Product p
LEFT JOIN Category c ON p.category_id = c.category_id
LEFT JOIN Supplier s ON p.supplier_id = s.supplier_id
ORDER BY p.quantity DESC;

-- Identify low stock products
SELECT 
    product_id,
    product_name,
    quantity,
    price
FROM Product
WHERE quantity < 10
ORDER BY quantity ASC;

-- Get total inventory value
SELECT 
    SUM(quantity * price) AS total_inventory_value
FROM Product;