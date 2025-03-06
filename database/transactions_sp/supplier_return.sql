

CREATE PROCEDURE ProcessSupplierReturn(
    IN p_supplier_id INT,
    IN p_product_id INT,
    IN p_quantity INT,
    IN p_unit_price DECIMAL(10,2),
    IN p_reason VARCHAR(255)
)
BEGIN
    DECLARE v_sup_return_id INT;
    DECLARE v_subtotal DECIMAL(10,2);
    
    SET v_subtotal = p_quantity * p_unit_price;
    
    INSERT INTO SupplierReturn (supplier_id, return_reason, return_date, refund_status)
    VALUES (p_supplier_id, p_reason, CURDATE(), 'Pending');
    
    SET v_sup_return_id = LAST_INSERT_ID();
    
    INSERT INTO SupplierReturnDetails (supplier_return_id, product_id, quantity_returned, unit_price, subtotal)
    VALUES (v_sup_return_id, p_product_id, p_quantity, p_unit_price, v_subtotal);
    
    UPDATE Product 
    SET quantity = quantity - p_quantity
    WHERE product_id = p_product_id;
END //

DELIMITER ;