

CREATE PROCEDURE ProcessReceiving(
    IN p_supplier_id INT,
    IN p_product_id INT,
    IN p_quantity INT,
    IN p_unit_cost DECIMAL(10,2),
    IN p_createdbyid INT
)
BEGIN
    DECLARE v_receiving_id INT;
    DECLARE v_total_cost DECIMAL(10,2);
    
    SET v_total_cost = p_quantity * p_unit_cost;
    
    INSERT INTO Receiving (supplier_id, receiving_date, total_quantity, total_cost, status)
    VALUES (p_supplier_id, CURDATE(), p_quantity, v_total_cost, 'Received');
    
    SET v_receiving_id = LAST_INSERT_ID();
    
    INSERT INTO Receiving_Details (receiving_id, product_id, quantity_furnished, unit_cost, subtotal_cost, createdbyid)
    VALUES (v_receiving_id, p_product_id, p_quantity, p_unit_cost, v_total_cost, p_createdbyid);
    
    UPDATE Product 
    SET quantity = quantity + p_quantity
    WHERE product_id = p_product_id;
END //

DELIMITER ;