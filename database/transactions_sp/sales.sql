

-- Create new sale
CREATE PROCEDURE ProcessSale(
    IN p_customer_id INT,
    IN p_product_id INT,
    IN p_quantity INT,
    IN p_unit_price DECIMAL(10,2),
    IN p_createdbyid INT
)
BEGIN
    DECLARE v_sales_id INT;
    DECLARE v_subtotal DECIMAL(10,2);
    
    SET v_subtotal = p_quantity * p_unit_price;
    
    INSERT INTO Sales (customer_id, sale_date, total_amount, createdbyid)
    VALUES (p_customer_id, CURDATE(), v_subtotal, p_createdbyid);
    
    SET v_sales_id = LAST_INSERT_ID();
    
    INSERT INTO SalesLine (sales_id, product_id, quantity, unit_price, subtotal_price)
    VALUES (v_sales_id, p_product_id, p_quantity, p_unit_price, v_subtotal);
    
    UPDATE Product 
    SET quantity = quantity - p_quantity
    WHERE product_id = p_product_id;
END //

-- Record payment
CREATE PROCEDURE RecordPayment(
    IN p_customer_id INT,
    IN p_sales_id INT,
    IN p_amount_paid DECIMAL(10,2),
    IN p_payment_method ENUM('Cash', 'Credit', 'GCash'),
    IN p_createdbyid INT
)
BEGIN
    INSERT INTO Payments (customer_id, sales_id, amount_paid, payment_date, payment_method, createdbyid)
    VALUES (p_customer_id, p_sales_id, p_amount_paid, CURDATE(), p_payment_method, p_createdbyid);
END //

DELIMITER ;