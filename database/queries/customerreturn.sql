-- Recent customer returns
SELECT 
    cr.customer_return_id,
    cr.return_date,
    c.name AS customer_name,
    cr.return_reason,
    cr.total_amount,
    cr.refund_status
FROM CustomerReturn cr
JOIN Customer c ON cr.customer_id = c.customer_id
ORDER BY cr.return_date DESC
LIMIT 10;

-- Most returned products s
SELECT 
    p.product_name,
    COUNT(crd.return_detail_id) AS total_returns,
    SUM(crd.quantity_returned) AS total_quantity_returned,
    SUM(crd.subtotal) AS total_return_value
FROM CustomerReturnDetails crd
JOIN Product p ON crd.product_id = p.product_id
GROUP BY p.product_id, p.product_name
ORDER BY total_returns DESC;