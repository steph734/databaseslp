-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2025 at 12:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventorysystem`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddProduct` (IN `p_product_name` VARCHAR(255), IN `p_quantity` INT, IN `p_price` DECIMAL(10,2), IN `p_unitofmeasurement` VARCHAR(50), IN `p_category_id` VARCHAR(50), IN `p_supplier_id` INT, IN `p_createdbyid` INT)   BEGIN
    DECLARE p_status ENUM('available', 'unavailable');
    
    -- Set status based on quantity
    SET p_status = IF(p_quantity > 0, 'available', 'unavailable');

    INSERT INTO Product (
        product_name, 
        quantity, 
        price, 
        unitofmeasurement, 
        category_id, 
        supplier_id, 
        createdbyid, 
        createdate, 
        status
    ) VALUES (
        p_product_name, 
        p_quantity, 
        p_price, 
        p_unitofmeasurement, 
        p_category_id, 
        p_supplier_id, 
        p_createdbyid, 
        NOW(), 
        p_status
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddSupplier` (IN `p_supplier_name` VARCHAR(255), IN `p_contact_info` VARCHAR(255), IN `p_address` VARCHAR(255), IN `p_status` ENUM('active','inactive'), IN `p_createdbyid` INT(11))   BEGIN
    INSERT INTO Supplier (supplier_name, contact_info, address, status, createdbyid)
    VALUES (p_supplier_name, p_contact_info, p_address, p_status, p_createdbyid);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_customer` (IN `p_name` VARCHAR(250), IN `p_contact` VARCHAR(20), IN `p_address` VARCHAR(250), IN `p_is_member` BOOLEAN, IN `p_type_id` INT, IN `p_createdbyid` INT)   BEGIN
    INSERT INTO Customer (name, contact, address, is_member, type_id, createdbyid)
    VALUES (p_name, p_contact, p_address, p_is_member, p_type_id, p_createdbyid);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AwardPoints` (IN `p_sales_id` INT, IN `p_membership_id` INT, IN `p_createdbyid` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteInventory` (IN `p_inventory_id` INT, IN `p_updatedbyid` INT, OUT `p_message` VARCHAR(255))   BEGIN
    DECLARE product_id INT;
    DECLARE stock_quantity INT;
    DECLARE damage_stock INT;
    START TRANSACTION;
    SELECT product_id, stock_quantity, damage_stock INTO product_id, stock_quantity, damage_stock
    FROM Inventory WHERE inventory_id = p_inventory_id;
    IF ROW_COUNT() = 0 THEN
        SET p_message = 'Inventory record not found.';
        ROLLBACK;
    ELSE
        DELETE FROM Inventory WHERE inventory_id = p_inventory_id;
        IF ROW_COUNT() > 0 THEN
            COMMIT;
            SET p_message = 'Inventory record deleted successfully.';
            -- Optional: Update Product quantity
            -- UPDATE Product SET quantity = quantity - (stock_quantity - damage_stock) WHERE product_id = product_id;
        ELSE
            ROLLBACK;
            SET p_message = 'Error deleting inventory record.';
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `EditInventory` (IN `p_inventory_id` INT, IN `p_product_id` INT, IN `p_price` DECIMAL(10,2), IN `p_stock_quantity` INT, IN `p_total_value` DECIMAL(10,2), IN `p_received_date` DATE, IN `p_last_restock_date` DATE, IN `p_damage_stock` INT, IN `p_updated_by` INT, IN `p_updated_date` DATETIME, OUT `p_message` VARCHAR(255), OUT `p_stock_level` VARCHAR(50))   BEGIN
    DECLARE v_existing_count INT;
    DECLARE v_new_stock INT;
    DECLARE v_new_total_value DECIMAL(10,2);
    DECLARE v_old_stock INT;
    DECLARE v_old_total_value DECIMAL(10,2);
    DECLARE v_product_name VARCHAR(100);
    DECLARE v_price_change DECIMAL(10,2);
    DECLARE v_damage_message VARCHAR(255);
    DECLARE v_safety_stock INT DEFAULT 115; -- From previous calculation
    DECLARE v_reorder_point INT DEFAULT 280; -- From previous calculation

    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
        SET p_message = 'Error updating inventory!';
        SET p_stock_level = 'Unknown';
    END;

    START TRANSACTION;

    -- Check for invalid negative values
    IF p_price < 0 OR p_stock_quantity < 0 OR p_damage_stock < 0 THEN
        ROLLBACK;
        SET p_message = 'Error: Negative values are not allowed!';
        SET p_stock_level = 'Unknown';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = p_message;
    END IF;

    -- Check if product_id is already assigned to another inventory record
    SELECT COUNT(*) INTO v_existing_count 
    FROM Inventory 
    WHERE product_id = p_product_id AND inventory_id != p_inventory_id;

    IF v_existing_count > 0 THEN
        ROLLBACK;
        SET p_message = 'Error: Product already exists in another inventory record!';
        SET p_stock_level = 'Unknown';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = p_message;
    END IF;

    -- Validate product existence
    IF NOT EXISTS (SELECT 1 FROM Product WHERE product_id = p_product_id) THEN
        ROLLBACK;
        SET p_message = 'Error: Product does not exist!';
        SET p_stock_level = 'Unknown';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = p_message;
    END IF;

    -- Validate inventory record existence
    IF NOT EXISTS (SELECT 1 FROM Inventory WHERE inventory_id = p_inventory_id) THEN
        ROLLBACK;
        SET p_message = 'Error: Inventory record does not exist!';
        SET p_stock_level = 'Unknown';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = p_message;
    END IF;

    -- Get the current stock and total value before update
    SELECT stock_quantity, total_value INTO v_old_stock, v_old_total_value
    FROM Inventory 
    WHERE inventory_id = p_inventory_id;

    -- Get the product name for messaging
    SELECT product_name INTO v_product_name 
    FROM Product 
    WHERE product_id = p_product_id;

    -- Adjust stock and total value considering damage stock
    SET v_new_stock = p_stock_quantity - p_damage_stock;
    SET v_new_total_value = v_new_stock * p_price;

    -- Calculate value change for the message
    SET v_price_change = v_new_total_value - v_old_total_value;

    -- Determine stock level based on safety stock and reorder point
    IF v_new_stock <= 0 THEN
        SET p_stock_level = 'Out of Stock';
    ELSEIF v_new_stock <= v_safety_stock THEN
        SET p_stock_level = 'Low Stock';
    ELSEIF v_new_stock <= v_reorder_point THEN
        SET p_stock_level = 'Reorder Needed';
    ELSE
        SET p_stock_level = 'In Stock';
    END IF;

    -- Update inventory record
    UPDATE Inventory 
    SET 
        product_id = p_product_id,
        price = p_price,
        stock_quantity = v_new_stock,
        total_value = v_new_total_value,
        received_date = p_received_date,
        last_restock_date = p_last_restock_date,
        damage_stock = p_damage_stock,
        updatedbyid = p_updated_by,
        updatedate = p_updated_date
    WHERE inventory_id = p_inventory_id;

    -- Generate damage stock message if applicable
    IF p_damage_stock > 0 THEN
        SET v_damage_message = CONCAT(
            ' Stock reduced by ', p_damage_stock, 
            ' and value deducted by ', FORMAT((p_damage_stock * p_price), 2), '.'
        );
    ELSE
        SET v_damage_message = '';
    END IF;

    -- Set detailed success message
    SET p_message = CONCAT(
        'Inventory updated! Product: ', v_product_name, 
        '. Stock: ', v_old_stock, ' → ', v_new_stock, 
        '. Total Value: ', FORMAT(v_old_total_value, 2), ' → ', FORMAT(v_new_total_value, 2),
        '. Change: ', FORMAT(v_price_change, 2), 
        '. Stock Level: ', p_stock_level,
        '. Updated by Admin ID: ', p_updated_by, 
        ' on ', DATE_FORMAT(p_updated_date, '%Y-%m-%d %H:%i:%s'), 
        '.', v_damage_message
    );

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessCustomerReturn` (IN `p_customer_id` INT, IN `p_product_id` INT, IN `p_quantity` INT, IN `p_unit_price` DECIMAL(10,2), IN `p_reason` VARCHAR(255), IN `p_createdbyid` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `PullOutInventory` (IN `p_inventory_id` INT, IN `p_updatedbyid` INT, OUT `p_message` VARCHAR(255))   BEGIN
    DECLARE product_id INT;
    DECLARE stock_quantity INT;
    DECLARE damage_stock INT;
    START TRANSACTION;
    SELECT product_id, stock_quantity, damage_stock INTO product_id, stock_quantity, damage_stock
    FROM Inventory WHERE inventory_id = p_inventory_id;
    IF ROW_COUNT() = 0 THEN
        SET p_message = 'Inventory record not found.';
        ROLLBACK;
    ELSE
        DELETE FROM Inventory WHERE inventory_id = p_inventory_id;
        UPDATE Product SET quantity = 0, updatedbyid = p_updatedbyid, updatedate = NOW()
        WHERE product_id = product_id;
        IF ROW_COUNT() > 0 THEN
            COMMIT;
            SET p_message = 'Inventory record and product pulled out successfully.';
        ELSE
            ROLLBACK;
            SET p_message = 'Error processing pull-out.';
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateCategory` (IN `old_category_id` VARCHAR(25), IN `new_category_id` VARCHAR(25), IN `new_category_name` VARCHAR(255), IN `updated_by` INT)   BEGIN
    DECLARE id_exists INT;
    DECLARE name_exists INT;
    DECLARE error_msg VARCHAR(255);

    -- Enhanced error handler
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        SET error_msg = CONCAT('SQL Error: ', IFNULL(SQLERRM, 'Unknown error'));
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = error_msg;
    END;

    START TRANSACTION;

    -- Check if old_category_id exists
    IF NOT EXISTS (SELECT 1 FROM Category WHERE category_id = old_category_id) THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Old Category ID does not exist!';
    END IF;

    -- Check if new_category_id is different and already exists
    IF old_category_id != new_category_id THEN
        SELECT COUNT(*) INTO id_exists FROM Category WHERE category_id = new_category_id;
        IF id_exists > 0 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'New Category ID already exists!';
        END IF;
    END IF;

    -- Check if category_name already exists (excluding current category)
    SELECT COUNT(*) INTO name_exists FROM Category 
    WHERE category_name = new_category_name AND category_id != old_category_id;
    IF name_exists > 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Category name already exists!';
    END IF;

    -- Update Category (cascade will update Product automatically)
    UPDATE Category 
    SET category_id = new_category_id, 
        category_name = new_category_name, 
        updatedbyid = updated_by, 
        updatedate = NOW() 
    WHERE category_id = old_category_id;

    -- Check if update succeeded
    IF ROW_COUNT() = 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Category update failed unexpectedly!';
    END IF;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateInventoryStock` (IN `p_product_id` INT, IN `p_price` DECIMAL(10,2), IN `p_quantity_to_add` INT, IN `p_received_date` DATE, IN `p_createdbyid` INT)   BEGIN
    -- Start with a safety net: if something goes wrong, undo changes
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Something went wrong!';
    END;

    START TRANSACTION;

    -- Check if the product exists in the Product table
    IF NOT EXISTS (SELECT 1 FROM Product WHERE product_id = p_product_id) THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Product ID does not exist!';
    END IF;

    -- Check if quantity and price make sense
    IF p_quantity_to_add <= 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity must be more than zero!';
    END IF;

    IF p_price < 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Price can’t be negative!';
    END IF;

    -- If inventory already exists for this product, update it
    IF EXISTS (SELECT 1 FROM Inventory WHERE product_id = p_product_id) THEN
        UPDATE Inventory
        SET 
            stock_quantity = stock_quantity + p_quantity_to_add,
            price = p_price,
            total_value = (stock_quantity + p_quantity_to_add) * p_price,
            last_restock_date = p_received_date,
            updatedbyid = p_createdbyid,
            updatedate = CURDATE()
        WHERE product_id = p_product_id;
    ELSE
        -- If no inventory exists, add a new row
        INSERT INTO Inventory (
            product_id, price, stock_quantity, total_value, received_date,
            last_restock_date, damage_stock, createdbyid, createdate
        ) VALUES (
            p_product_id, p_price, p_quantity_to_add, p_quantity_to_add * p_price,
            p_received_date, p_received_date, 0, p_createdbyid, CURDATE()
        );
    END IF;

    -- Save the changes
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateProduct` (IN `p_product_id` INT, IN `p_product_name` VARCHAR(255), IN `p_quantity` INT, IN `p_price` DECIMAL(10,2), IN `p_unitofmeasurement` VARCHAR(50), IN `p_category_id` VARCHAR(50), IN `p_supplier_id` INT, IN `p_updatedbyid` INT)   BEGIN
    DECLARE p_status ENUM('available', 'unavailable');
    
    -- Set status based on quantity
    SET p_status = IF(p_quantity > 0, 'available', 'unavailable');

    UPDATE Product 
    SET 
        product_name = p_product_name,
        quantity = p_quantity,
        price = p_price,
        unitofmeasurement = p_unitofmeasurement,
        category_id = p_category_id,
        supplier_id = p_supplier_id,
        updatedbyid = p_updatedbyid,
        updatedate = NOW(),
        status = p_status
    WHERE product_id = p_product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateSupplier` (IN `p_supplier_id` INT, IN `p_supplier_name` VARCHAR(255), IN `p_contact_info` VARCHAR(255), IN `p_address` TEXT, IN `p_updatedbyid` INT)   BEGIN
    UPDATE Supplier
    SET supplier_name = p_supplier_name,
        contact_info = p_contact_info,
        address = p_address,
        updatedbyid = p_updatedbyid,
        updatedate = NOW()
    WHERE supplier_id = p_supplier_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `adjustmentline`
--

CREATE TABLE `adjustmentline` (
  `adjustment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `adjustment_type` enum('Damaged item','Expired item') NOT NULL,
  `adjustment_date` date NOT NULL DEFAULT curdate(),
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `total_adjusted_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phonenumber` varchar(20) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `first_name`, `middle_name`, `last_name`, `username`, `password`, `email`, `phonenumber`, `role`) VALUES
(1, 'admin', 'admin', 'admin', 'admin', '$2y$10$C1WbHbFtldWGq4LBB3lmiu0g6RRaF5UP7FqsJ4Kqj8W6L06OVb/DC', 'admin@gmail.com', '09663050832', 'admin'),
(3, 'asd', 'dsa', 'hjbh', 'ali', '$2y$10$n7tuECu0vTmoBKDvxTjj7eHG8sLR7sP/tfLbViHhkRII6iIYLTabW', 'iuib@gmail.com', '004234932', 'admin'),
(4, 'wda', 'daw', 'daw', 'eli', '$2y$10$2GVhPOujgJzRzMWRVdPM2etDkHSV2Sc.Ih7BFSFMPGIQ.W.Vw/ToK', 'dwa@gmail.com', '124', 'admin'),
(5, 'Elisio', 'Corpin', 'Soroño', 'admin1', '$2y$10$Z4LdC2Bfpuw/Sher5N4X0engQySBW.pnUt2ylQuAGTtfV815UCKdm', 'sronoile@gmail.com', '09663050832', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `auditlog`
--

CREATE TABLE `auditlog` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditlog`
--

INSERT INTO `auditlog` (`log_id`, `admin_id`, `action`, `description`, `timestamp`) VALUES
(1, 1, 'Login', 'User logged in from IP 192.168.1.1', '2025-03-14 10:00:00'),
(2, 1, 'Add User', 'Added new user with username \"staff1\"', '2025-03-14 10:05:00'),
(3, 3, 'Update Inventory', 'Updated stock for Product ID 101 by +5 units', '2025-03-14 11:00:00'),
(4, 1, 'Delete User', 'Deleted user with ID 4', '2025-03-14 12:00:00'),
(5, 4, 'Login', 'User logged in from IP 10.0.0.1', '2025-03-14 13:00:00'),
(6, 5, 'Update Profile', 'Admin updated their profile details', '2025-03-14 19:20:28');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` varchar(25) NOT NULL,
  `category_name` varchar(225) NOT NULL,
  `description` text DEFAULT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `description`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
('beverage', 'beverage', NULL, 1, '2025-03-09 03:28:31', 1, '2025-03-09 16:44:28'),
('can', 'wda', NULL, 1, '2025-03-09 16:47:12', NULL, NULL),
('carne', 'beverages', 'Long shelf-life items like sardines, corned beef, and meatloaf.', 1, '2025-03-08 13:09:17', 1, '2025-03-10 09:21:44'),
('desert', 'desert', NULL, 1, '2025-03-10 14:16:38', NULL, NULL),
('wad', 'qwe', NULL, 1, '2025-03-09 16:49:29', NULL, NULL),
('water', 'wa', NULL, 1, '2025-03-09 16:48:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `address` varchar(250) DEFAULT NULL,
  `is_member` tinyint(1) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `name`, `contact`, `address`, `is_member`, `type_id`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(1, 'Eli Corpin Soroño', '124234', 'P-5 Brgy. Mambago-A', 1, 1, 1, '2025-03-10 15:04:56', 1, '2025-03-10 15:23:20'),
(2, 'Stephen', '124234', 'Davao City', 1, 2, 1, '2025-03-10 15:23:52', 1, '2025-03-10 15:26:20'),
(3, 'Eli Soroño', '124234', 'P-5 Brgy. Mambago-A', 0, NULL, 1, '2025-03-11 10:06:27', NULL, NULL),
(4, 'Eli Soroño', '124234', 'P-5 Brgy. Mambago-A', 0, NULL, 1, '2025-03-11 10:06:40', NULL, NULL),
(5, 'Eli Corpin Soroño', '124234', 'P-5 Brgy. Mambago-A', 0, NULL, 1, '2025-03-11 10:06:59', NULL, NULL),
(6, 'Eli Corpin Soroño', '124234', 'P-5 Brgy. Mambago-A', 0, NULL, 1, '2025-03-11 10:07:16', NULL, NULL),
(7, 'Eli Corpin Soroño', '124234', 'P-5 Brgy. Mambago-A', 0, NULL, 1, '2025-03-11 10:19:16', NULL, NULL),
(8, 'Eli Corpin Soroño', '124234', 'P-5 Brgy. Mambago-A', 1, NULL, 1, '2025-03-11 10:24:30', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customerreturn`
--

CREATE TABLE `customerreturn` (
  `customer_return_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `return_reason` varchar(255) NOT NULL,
  `return_date` date NOT NULL DEFAULT curdate(),
  `refund_status` enum('Refunded','Replaced','Pending') NOT NULL DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL CHECK (`total_amount` >= 0),
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customerreturndetails`
--

CREATE TABLE `customerreturndetails` (
  `return_detail_id` int(11) NOT NULL,
  `customer_return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_returned` int(11) NOT NULL CHECK (`quantity_returned` > 0),
  `unit_price` decimal(10,2) NOT NULL CHECK (`unit_price` > 0),
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_type`
--

CREATE TABLE `customer_type` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_type`
--

INSERT INTO `customer_type` (`type_id`, `type_name`) VALUES
(1, 'Member'),
(2, 'Regular');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `total_value` decimal(10,2) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `last_restock_date` date DEFAULT NULL,
  `damage_stock` int(11) DEFAULT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` date NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `price`, `stock_quantity`, `total_value`, `received_date`, `last_restock_date`, `damage_stock`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(7, 12, 8.00, 0, 40.00, '2025-03-10', '2025-03-10', 0, 1, '2025-03-10', 1, '2025-03-11'),
(8, 25, 12.00, 60, 1452.00, '2025-03-11', '2025-03-11', 0, 1, '2025-03-11', 1, '2025-03-11'),
(10, 8, 10.00, 0, 80.00, '2025-03-11', '2025-03-11', 0, 1, '2025-03-11', NULL, NULL),
(12, 9, 12.00, 0, 24.00, '2025-03-11', '2025-03-11', 0, 1, '2025-03-11', 1, '2025-03-11'),
(13, 13, 21.00, 12, 294.00, '2025-03-14', '2025-03-14', 0, 1, '2025-03-14', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `membership_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `date_repairs` date NOT NULL,
  `date_renewal` date DEFAULT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership`
--

INSERT INTO `membership` (`membership_id`, `customer_id`, `status`, `date_repairs`, `date_renewal`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(1, NULL, 'on going', '2025-03-20', '2025-03-20', 1, '2025-03-11 09:40:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `points`
--

CREATE TABLE `points` (
  `points_id` int(11) NOT NULL,
  `membership_id` int(11) DEFAULT NULL,
  `sales_id` int(11) DEFAULT NULL,
  `total_purchase` decimal(10,2) NOT NULL,
  `points_amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `points_details`
--

CREATE TABLE `points_details` (
  `pd_id` int(11) NOT NULL,
  `points_id` int(11) DEFAULT NULL,
  `total_points` int(11) NOT NULL,
  `redeemable_date` date NOT NULL,
  `redeemed_amount` int(11) NOT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `price` decimal(10,2) NOT NULL,
  `unitofmeasurement` varchar(50) NOT NULL,
  `category_id` varchar(25) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `quantity`, `status`, `price`, `unitofmeasurement`, `category_id`, `supplier_id`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(7, 'MEGA sardines', 0, 'available', 25.00, 'can', 'carne', NULL, 1, '2025-03-08 13:49:01', 1, '2025-03-14 15:09:08'),
(8, 'sardinas ni nene', 8, 'available', 20.00, 'can', 'carne', NULL, 1, '2025-03-08 14:04:34', 1, '2025-03-14 14:51:33'),
(9, 'sardines', 31, 'available', 25.00, 'can', 'carne', 4, 1, '2025-03-08 14:56:51', 1, '2025-03-14 14:51:56'),
(10, 'sardinessd', 0, 'available', 25.00, 'can', 'carne', 5, 1, '2025-03-08 14:58:00', 1, '2025-03-14 15:09:11'),
(12, 'sardinessds', 8, 'available', 25.00, 'can', 'carne', NULL, 1, '2025-03-08 15:02:33', NULL, NULL),
(13, 'tunaflakeswithchili', 14, 'available', 30.00, 'can', 'carne', 3, 1, '2025-03-08 15:04:14', 1, '2025-03-13 21:13:22'),
(14, 'tunaflakes', 3, 'available', 25.00, 'can', 'carne', 3, 1, '2025-03-08 15:06:37', 1, '2025-03-11 08:44:06'),
(17, 'corn beef loaf ni daddy', 0, 'available', 25.00, 'can', 'carne', NULL, NULL, '2025-03-08 16:05:26', 1, '2025-03-13 08:31:02'),
(25, 'carne norte', 1, 'available', 2.00, '2', 'carne', 4, 1, '2025-03-09 02:32:18', NULL, NULL),
(26, 'meat loafs', 23, 'available', 2313.00, 'pcs', 'carne', 6, 1, '2025-03-09 14:55:47', NULL, NULL),
(27, 'sinigang', 12, 'available', 12.00, 'kg', 'carne', 3, 1, '2025-03-11 08:36:44', NULL, NULL),
(28, 'hello', 12, 'available', 2.00, 'kg', 'beverage', 4, 1, '2025-03-13 20:58:25', NULL, '2025-03-13 20:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `receiving`
--

CREATE TABLE `receiving` (
  `receiving_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `receiving_date` date NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `status` enum('Received','Pending','Cancelled') NOT NULL DEFAULT 'Pending',
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receiving`
--

INSERT INTO `receiving` (`receiving_id`, `supplier_id`, `receiving_date`, `total_quantity`, `total_cost`, `status`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(1, 3, '2025-03-11', 3, 36.00, 'Received', NULL, '2025-03-13 20:54:19', NULL, '2025-03-13 21:13:22'),
(2, 4, '2025-03-11', 23, 276.00, 'Received', NULL, '2025-03-13 20:54:19', NULL, '2025-03-13 21:17:14'),
(3, 4, '2025-03-13', 12, 24.00, 'Received', NULL, '2025-03-13 20:58:41', NULL, '2025-03-13 20:58:56'),
(4, 4, '2025-03-13', 13, 26.00, 'Cancelled', NULL, '2025-03-13 21:17:32', NULL, '2025-03-14 12:26:18'),
(5, 10, '2025-03-13', 50, 600.00, 'Received', NULL, '2025-03-13 22:44:04', NULL, '2025-03-13 22:44:27');

-- --------------------------------------------------------

--
-- Table structure for table `receiving_details`
--

CREATE TABLE `receiving_details` (
  `receiving_detail_id` int(11) NOT NULL,
  `receiving_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity_furnished` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `subtotal_cost` decimal(10,2) NOT NULL,
  `condition` enum('Good','Damaged') NOT NULL DEFAULT 'Good',
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` date NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receiving_details`
--

INSERT INTO `receiving_details` (`receiving_detail_id`, `receiving_id`, `product_id`, `quantity_furnished`, `unit_cost`, `subtotal_cost`, `condition`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(1, 1, 13, 3, 12.00, 36.00, 'Good', 1, '2025-03-11', NULL, NULL),
(2, 2, 9, 23, 12.00, 276.00, 'Good', 1, '2025-03-11', NULL, NULL),
(3, 3, 28, 12, 2.00, 24.00, 'Good', 1, '2025-03-13', NULL, NULL),
(4, 4, 25, 13, 2.00, 26.00, 'Good', 1, '2025-03-13', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sales_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `sale_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','GCash') NOT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sales_id`, `customer_id`, `sale_date`, `total_amount`, `payment_method`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(4, NULL, '2025-03-11', 46.00, 'Cash', 1, '2025-03-11 15:36:34', NULL, NULL),
(5, NULL, '2025-03-14', 856.00, 'Cash', NULL, '2025-03-14 15:10:01', NULL, NULL),
(6, NULL, '2025-03-14', 120.00, 'Cash', NULL, '2025-03-14 15:25:55', NULL, NULL),
(7, NULL, '2025-03-14', 120.00, 'Cash', NULL, '2025-03-14 15:33:27', NULL, NULL),
(8, NULL, '2025-03-14', 120.00, 'Cash', NULL, '2025-03-14 15:41:28', NULL, NULL),
(9, NULL, '2025-03-14', 20.00, 'Cash', NULL, '2025-03-14 15:48:58', NULL, NULL),
(10, NULL, '2025-03-14', 1.00, 'Cash', NULL, '2025-03-14 15:49:43', NULL, NULL),
(11, NULL, '2025-03-14', 14.00, 'Cash', NULL, '2025-03-14 16:36:28', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `salesline`
--

CREATE TABLE `salesline` (
  `salesline_id` int(11) NOT NULL,
  `sales_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `unit_price` decimal(10,2) NOT NULL CHECK (`unit_price` > 0),
  `subtotal_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salesline`
--

INSERT INTO `salesline` (`salesline_id`, `sales_id`, `product_id`, `quantity`, `unit_price`, `subtotal_price`) VALUES
(7, 4, 17, 2, 23.00, 46.00),
(8, 5, 8, 8, 23.00, 184.00),
(9, 5, 25, 21, 32.00, 672.00),
(10, 6, 25, 10, 12.00, 120.00),
(11, 7, 25, 10, 12.00, 120.00),
(12, 8, 25, 10, 12.00, 120.00),
(13, 9, 13, 2, 10.00, 20.00),
(14, 10, 25, 1, 1.00, 1.00),
(15, 11, 25, 7, 2.00, 14.00);

-- --------------------------------------------------------

--
-- Table structure for table `stockadjustmentdetails`
--

CREATE TABLE `stockadjustmentdetails` (
  `sad_id` int(11) NOT NULL,
  `adjustment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `adjusted_quantity` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_adjusted` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','','') NOT NULL,
  `createdbyid` int(11) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedbyid` int(11) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `supplier_name`, `contact_info`, `address`, `status`, `createdbyid`, `createdate`, `updatedbyid`, `updatedate`) VALUES
(3, 'NCCCs', '01234', 'Bruno Gempesaw St, Poblacion District, Davao City, Davao del Sur.', 'active', 1, '2025-03-08 23:23:15', 1, '2025-03-12 13:40:37'),
(4, 'SM Ecoland', '09213', 'Quimpo Blvd cor. Tulip and Ecoland Drive, Ecoland Subd., Matina, Davao City, Philippines.', 'active', 1, '2025-03-08 23:23:31', NULL, '2025-03-12 13:40:46'),
(5, 'Gmall', '01234', 'Bajada 8000 Davao City, Philippines Davao Region ·', 'active', 1, '2025-03-08 23:24:45', NULL, '2025-03-12 13:40:48'),
(6, 'SM Lanang', '092213', 'Quimpo Blvd cor. Tulip and Ecoland Drive, Ecoland Subd., Matina, Davao City, Philippines.', 'active', 1, '2025-03-08 23:24:53', 1, '2025-03-12 13:40:51'),
(7, 'Unitop', '0214', 'Quimpo Blvd cor. Tulip and Ecoland Drive, Ecoland Subd., Matina, Davao City, Philippines.', 'active', 1, '2025-03-08 23:40:10', NULL, '2025-03-13 22:45:05'),
(10, 'Abreeza', '12342', 'J.P Laurel, Bajada', 'active', 1, '2025-03-12 13:49:42', NULL, '2025-03-13 22:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `supplierreturn`
--

CREATE TABLE `supplierreturn` (
  `supplier_return_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `return_reason` varchar(255) NOT NULL,
  `return_date` date NOT NULL DEFAULT curdate(),
  `refund_status` enum('Refunded','Replaced','Pending') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplierreturndetails`
--

CREATE TABLE `supplierreturndetails` (
  `return_detail_id` int(11) NOT NULL,
  `supplier_return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_returned` int(11) NOT NULL CHECK (`quantity_returned` > 0),
  `unit_price` decimal(10,2) NOT NULL CHECK (`unit_price` > 0),
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adjustmentline`
--
ALTER TABLE `adjustmentline`
  ADD PRIMARY KEY (`adjustment_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `auditlog`
--
ALTER TABLE `auditlog`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `customerreturn`
--
ALTER TABLE `customerreturn`
  ADD PRIMARY KEY (`customer_return_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `customerreturndetails`
--
ALTER TABLE `customerreturndetails`
  ADD PRIMARY KEY (`return_detail_id`),
  ADD KEY `customer_return_id` (`customer_return_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `customer_type`
--
ALTER TABLE `customer_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`membership_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `points`
--
ALTER TABLE `points`
  ADD PRIMARY KEY (`points_id`),
  ADD KEY `sales_id` (`sales_id`),
  ADD KEY `membership_id` (`membership_id`);

--
-- Indexes for table `points_details`
--
ALTER TABLE `points_details`
  ADD PRIMARY KEY (`pd_id`),
  ADD KEY `points_id` (`points_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_name` (`product_name`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`),
  ADD KEY `product_ibfk_1` (`category_id`);

--
-- Indexes for table `receiving`
--
ALTER TABLE `receiving`
  ADD PRIMARY KEY (`receiving_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `receiving_details`
--
ALTER TABLE `receiving_details`
  ADD PRIMARY KEY (`receiving_detail_id`),
  ADD KEY `receiving_id` (`receiving_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sales_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `salesline`
--
ALTER TABLE `salesline`
  ADD PRIMARY KEY (`salesline_id`),
  ADD KEY `sales_id` (`sales_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stockadjustmentdetails`
--
ALTER TABLE `stockadjustmentdetails`
  ADD PRIMARY KEY (`sad_id`),
  ADD KEY `adjustment_id` (`adjustment_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`supplier_id`),
  ADD KEY `createdbyid` (`createdbyid`),
  ADD KEY `updatedbyid` (`updatedbyid`);

--
-- Indexes for table `supplierreturn`
--
ALTER TABLE `supplierreturn`
  ADD PRIMARY KEY (`supplier_return_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplierreturndetails`
--
ALTER TABLE `supplierreturndetails`
  ADD PRIMARY KEY (`return_detail_id`),
  ADD KEY `supplier_return_id` (`supplier_return_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adjustmentline`
--
ALTER TABLE `adjustmentline`
  MODIFY `adjustment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `auditlog`
--
ALTER TABLE `auditlog`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customerreturn`
--
ALTER TABLE `customerreturn`
  MODIFY `customer_return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customerreturndetails`
--
ALTER TABLE `customerreturndetails`
  MODIFY `return_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_type`
--
ALTER TABLE `customer_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `membership_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `points`
--
ALTER TABLE `points`
  MODIFY `points_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `points_details`
--
ALTER TABLE `points_details`
  MODIFY `pd_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `receiving`
--
ALTER TABLE `receiving`
  MODIFY `receiving_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `receiving_details`
--
ALTER TABLE `receiving_details`
  MODIFY `receiving_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sales_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `salesline`
--
ALTER TABLE `salesline`
  MODIFY `salesline_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `stockadjustmentdetails`
--
ALTER TABLE `stockadjustmentdetails`
  MODIFY `sad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `supplierreturn`
--
ALTER TABLE `supplierreturn`
  MODIFY `supplier_return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplierreturndetails`
--
ALTER TABLE `supplierreturndetails`
  MODIFY `return_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adjustmentline`
--
ALTER TABLE `adjustmentline`
  ADD CONSTRAINT `adjustmentline_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `adjustmentline_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `adjustmentline_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `auditlog`
--
ALTER TABLE `auditlog`
  ADD CONSTRAINT `auditlog_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON UPDATE CASCADE;

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `category_ibfk_2` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `customer_type` (`type_id`),
  ADD CONSTRAINT `customer_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `customerreturn`
--
ALTER TABLE `customerreturn`
  ADD CONSTRAINT `customerreturn_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customerreturn_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customerreturn_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `customerreturndetails`
--
ALTER TABLE `customerreturndetails`
  ADD CONSTRAINT `customerreturndetails_ibfk_1` FOREIGN KEY (`customer_return_id`) REFERENCES `customerreturn` (`customer_return_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customerreturndetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `membership_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `points`
--
ALTER TABLE `points`
  ADD CONSTRAINT `points_ibfk_1` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`sales_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `points_ibfk_2` FOREIGN KEY (`membership_id`) REFERENCES `membership` (`membership_id`) ON DELETE CASCADE;

--
-- Constraints for table `points_details`
--
ALTER TABLE `points_details`
  ADD CONSTRAINT `points_details_ibfk_1` FOREIGN KEY (`points_id`) REFERENCES `points` (`points_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `points_details_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `points_details_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_ibfk_3` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_ibfk_4` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `receiving`
--
ALTER TABLE `receiving`
  ADD CONSTRAINT `receiving_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `receiving_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `receiving_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `receiving_details`
--
ALTER TABLE `receiving_details`
  ADD CONSTRAINT `receiving_details_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `receiving` (`receiving_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receiving_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receiving_details_ibfk_3` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `receiving_details_ibfk_4` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `salesline`
--
ALTER TABLE `salesline`
  ADD CONSTRAINT `salesline_ibfk_1` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`sales_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salesline_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `stockadjustmentdetails`
--
ALTER TABLE `stockadjustmentdetails`
  ADD CONSTRAINT `stockadjustmentdetails_ibfk_1` FOREIGN KEY (`adjustment_id`) REFERENCES `adjustmentline` (`adjustment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stockadjustmentdetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `supplier`
--
ALTER TABLE `supplier`
  ADD CONSTRAINT `supplier_ibfk_1` FOREIGN KEY (`createdbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_ibfk_2` FOREIGN KEY (`updatedbyid`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `supplierreturn`
--
ALTER TABLE `supplierreturn`
  ADD CONSTRAINT `supplierreturn_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE;

--
-- Constraints for table `supplierreturndetails`
--
ALTER TABLE `supplierreturndetails`
  ADD CONSTRAINT `supplierreturndetails_ibfk_1` FOREIGN KEY (`supplier_return_id`) REFERENCES `supplierreturn` (`supplier_return_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplierreturndetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
