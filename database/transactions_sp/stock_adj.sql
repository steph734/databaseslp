

CREATE PROCEDURE ProcessStockAdjustment(
    IN p_product_id INT,
    IN p_quantity INT,
    IN p_adj_type ENUM('Damaged item', 'Expired item'),
    IN p_description VARCHAR(255),
    IN p_createdbyid INT
)
BEGIN
    DECLARE v_adjustment_id INT;
    
    INSERT INTO AdjustmentLine (product_id, adjustment_type, adjustment_date, createdbyid, total_adjusted_quantity)
    VALUES (p_product_id, p_adj_type, CURDATE(), p_createdbyid, p_quantity);
    
    SET v_adjustment_id = LAST_INSERT_ID();
    
    INSERT INTO StockAdjustmentDetails (adjustment_id, product_id, adjusted_quantity, description, date_adjusted)
    VALUES (v_adjustment_id, p_product_id, p_quantity, p_description, CURDATE());
    
    UPDATE Product 
    SET quantity = quantity - p_quantity
    WHERE product_id = p_product_id;
END //

DELIMITER ;