

-- Add new product
CREATE PROCEDURE AddProduct(
    IN p_product_name VARCHAR(255),
    IN p_quantity INT,
    IN p_price DECIMAL(10,2),
    IN p_unitofmeasurement VARCHAR(50),
    IN p_category_id VARCHAR(25),
    IN p_supplier_id INT,
    IN p_createdbyid INT
)
BEGIN
    INSERT INTO Product (product_name, quantity, price, unitofmeasurement, category_id, supplier_id, createdbyid)
    VALUES (p_product_name, p_quantity, p_price, p_unitofmeasurement, p_category_id, p_supplier_id, p_createdbyid);
END //

-- Update product price
CREATE PROCEDURE UpdateProductPrice(
    IN p_product_id INT,
    IN p_new_price DECIMAL(10,2),
    IN p_updatedbyid INT
)
BEGIN
    UPDATE Product 
    SET price = p_new_price,
        updatedbyid = p_updatedbyid
    WHERE product_id = p_product_id;
END //

DELIMITER ;