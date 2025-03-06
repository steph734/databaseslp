

CREATE PROCEDURE AwardPoints(
    IN p_sales_id INT,
    IN p_membership_id INT,
    IN p_createdbyid INT
)
BEGIN
    DECLARE v_total_purchase DECIMAL(10,2);
    DECLARE v_points_earned INT;
    DECLARE v_date_renewal DATE;
    DECLARE v_next_redeemable_date DATE;
    DECLARE v_points_id INT;
    DECLARE v_stored_points INT;

    -- Get the total purchase amount from the Sales table
    SELECT total_amount INTO v_total_purchase
    FROM Sales
    WHERE sales_id = p_sales_id;

    -- Calculate points: PHP 10 for every PHP 1000 spent (rounded down)
    SET v_points_earned = FLOOR(v_total_purchase / 1000) * 10;

    -- Get the membership renewal date
    SELECT date_renewal INTO v_date_renewal
    FROM Membership
    WHERE membership_id = p_membership_id;

    -- Calculate the next annual redeemable date based on renewal
    SET v_next_redeemable_date = DATE_ADD(v_date_renewal, 
        INTERVAL FLOOR(DATEDIFF(CURDATE(), v_date_renewal) / 365) + 1 YEAR);

    -- Step 1: Insert into Points table with calculated points
    INSERT INTO Points (
        membership_id,
        sales_id,
        total_purchase,
        points_amount
    ) VALUES (
        p_membership_id,
        p_sales_id,
        v_total_purchase,
        v_points_earned
    );

    -- Get the last inserted points_id
    SET v_points_id = LAST_INSERT_ID();

    -- Step 2: Retrieve the stored points_amount from Points
    SELECT points_amount INTO v_stored_points
    FROM Points
    WHERE points_id = v_points_id;

    -- Step 3: Insert into Points_Details using the stored points_amount
    INSERT INTO Points_Details (
        points_id,
        total_points,
        redeemable_date,
        redeemed_amount,
        createdbyid
    ) VALUES (
        v_points_id,
        v_stored_points,  -- Use the value stored in Points.points_amount
        v_next_redeemable_date,
        0,                -- Initially no points redeemed
        p_createdbyid
    );
END //

DELIMITER ;