

CREATE PROCEDURE ProcessCustomerReturn(
    IN p_customer_id INT,
    IN p_product_id INT,
    IN p_quantity INT,
    IN p_unit_price DECIMAL(10,2),
    IN p_reason VARCHAR(255),
    IN p_createdbyid INT
)
BEGIN
    DECLARE v_return_id INT;
    DECLARE v_subtotal DECIMAL(10,2);
    
    SET v_subtotal = p_quantity * p_unit_price;
    
    INSERT INTO CustomerReturn (customer_id, return_reason, return_date, refund_status, total_amount, createdbyid)
    VALUES (p_customer_id, p_reason, CURDATE(), 'Pending', v_subtotal, p_createdbyid);
    
    SET v_return_id = LAST_INSERT_ID();
    
    INSERT INTO CustomerReturnDetails (customer_return_id, product_id, quantity_returned, unit_price, subtotal)
    VALUES (v_return_id, p_product_id, p_quantity, p_unit_price, v_subtotal);
    
    UPDATE Product 
    SET quantity = quantity + p_quantity
    WHERE product_id = p_product_id;
END //

DELIMITER ;