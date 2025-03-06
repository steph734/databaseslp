

CREATE PROCEDURE UpdateInventoryStock(
    IN p_product_id INT,
    IN p_price DECIMAL(10,2),
    IN p_quantity_to_add INT,
    IN p_received_date DATE,
    IN p_createdbyid INT
)
BEGIN
    DECLARE v_existing_quantity INT DEFAULT 0;
    DECLARE v_new_quantity INT;
    DECLARE v_new_total_value DECIMAL(10,2);

    -- Check if inventory record exists for the product
    SELECT stock_quantity INTO v_existing_quantity
    FROM Inventory
    WHERE product_id = p_product_id
    LIMIT 1;

    -- Calculate new quantity and total value
    SET v_new_quantity = v_existing_quantity + p_quantity_to_add;
    SET v_new_total_value = v_new_quantity * p_price;

    IF v_existing_quantity IS NULL THEN
        -- Insert new inventory record if it doesn't exist
        INSERT INTO Inventory (
            product_id,
            price,
            stock_quantity,
            total_value,
            received_date,
            last_restock_date,
            damage_stock,
            createdbyid
        ) VALUES (
            p_product_id,
            p_price,
            p_quantity_to_add,
            v_new_total_value,
            p_received_date,
            p_received_date,  -- Initial restock date
            0,                -- No damage initially
            p_createdbyid
        );
    ELSE
        -- Update existing inventory record
        UPDATE Inventory
        SET 
            price = p_price,
            stock_quantity = v_new_quantity,
            total_value = v_new_total_value,
            last_restock_date = p_received_date,
            updatedbyid = p_createdbyid,
            updatedate = CURDATE()
        WHERE product_id = p_product_id;
    END IF;
END //

DELIMITER ;