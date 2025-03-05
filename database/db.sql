-- Create the database
-- Updated by Eli
CREATE DATABASE inventorysystem;
USE inventorysystem;

-- Admin Table
CREATE TABLE Admin (
    admin_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phonenumber VARCHAR(20) NOT NULL,
    role VARCHAR(255) NOT NULL
);

-- Category Table
CREATE TABLE Category (
    category_id VARCHAR(25) PRIMARY KEY NOT NULL UNIQUE,
    category_name VARCHAR(225) NOT NULL UNIQUE,
    description TEXT NULL,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- Supplier Table
CREATE TABLE Supplier (
    supplier_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    supplier_name VARCHAR(255) NOT NULL,
    contact_info VARCHAR(255) NULL,
    address VARCHAR(255) NULL,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- Customer_Type Table
CREATE TABLE Customer_Type (
    type_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    type_name VARCHAR(250) NOT NULL
);

-- Customer Table
CREATE TABLE Customer (
    customer_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name VARCHAR(250) NULL,
    contact VARCHAR(20) NULL,
    address VARCHAR(250) NULL,
    is_member BOOLEAN NOT NULL,
    type_id INT,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES Customer_Type(type_id),
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- Product Table
CREATE TABLE Product (
    product_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    product_name VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    unitofmeasurement VARCHAR(50) NOT NULL,
    category_id VARCHAR(25),
    supplier_id INT,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES Category(category_id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES Supplier(supplier_id) ON DELETE SET NULL,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- Membership Table
CREATE TABLE Membership (
    membership_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    customer_id INT,
    status VARCHAR(50) NOT NULL DEFAULT 'Active',
    date_repairs DATE NOT NULL,
    date_renewal DATE NULL,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- Sales Table
CREATE TABLE Sales (
    sales_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    customer_id INT,
    sale_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE SET NULL,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- SalesLine Table
CREATE TABLE SalesLine (
    salesline_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    sales_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price > 0),
    subtotal_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sales_id) REFERENCES Sales(sales_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id) ON DELETE RESTRICT
);
-- Points Table
CREATE TABLE Points (
    points_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    membership_id INT,
    sales_id INT,
    total_purchase DECIMAL(10,2) NOT NULL,
    points_amount INT NOT NULL,
    FOREIGN KEY (sales_id) REFERENCES Sales(sales_id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES Membership(membership_id) ON DELETE CASCADE
);

-- Points_Details Table
CREATE TABLE Points_Details (
    pd_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    points_id INT,
    total_points INT NOT NULL,
    redeemable_date DATE NOT NULL,
    redeemed_amount INT NOT NULL,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (points_id) REFERENCES Points(points_id) ON DELETE CASCADE,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);



-- Payments Table
CREATE TABLE Payments (
    payment_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    customer_id INT,
    sales_id INT,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('Cash', 'Credit', 'GCash') NOT NULL,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE SET NULL,
    FOREIGN KEY (sales_id) REFERENCES Sales(sales_id) ON DELETE SET NULL,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- Receiving Table
CREATE TABLE Receiving (
    receiving_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    order_id INT,
    supplier_id INT,
    receiving_date DATE NOT NULL,
    total_quantity INT NOT NULL,
    total_cost DECIMAL(10,2) NULL,
    status ENUM('Received', 'Pending', 'Cancelled') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (supplier_id) REFERENCES Supplier(supplier_id) ON DELETE SET NULL
);

-- Receiving_Details Table
CREATE TABLE Receiving_Details (
    receiving_detail_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    receiving_id INT,
    product_id INT,
    quantity_furnished INT NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    subtotal_cost DECIMAL(10,2) NOT NULL,
    `condition` ENUM('Good', 'Damaged') NOT NULL DEFAULT 'Good',
    createdbyid INT,
    createdate DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATE,
    FOREIGN KEY (receiving_id) REFERENCES Receiving(receiving_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- Inventory Table
CREATE TABLE Inventory (
    inventory_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    product_id INT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL,
    total_value DECIMAL(10,2) NULL,
    received_date DATE NULL,
    last_restock_date DATE NULL,
    damage_stock INT NULL,
    createdbyid INT,
    createdate DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- SupplierReturn Table
CREATE TABLE SupplierReturn (
    supplier_return_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    supplier_id INT,
    return_reason VARCHAR(255) NOT NULL,
    return_date DATE NOT NULL DEFAULT CURRENT_DATE,
    refund_status ENUM('Refunded', 'Replaced', 'Pending') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (supplier_id) REFERENCES Supplier(supplier_id) ON DELETE CASCADE
);

-- SupplierReturnDetails Table
CREATE TABLE SupplierReturnDetails (
    return_detail_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    supplier_return_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_returned INT NOT NULL CHECK (quantity_returned > 0),
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price > 0),
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (supplier_return_id) REFERENCES SupplierReturn(supplier_return_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id) ON DELETE RESTRICT
);

-- AdjustmentLine Table
CREATE TABLE AdjustmentLine (
    adjustment_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    product_id INT NOT NULL,
    adjustment_type ENUM('Damaged item', 'Expired item') NOT NULL,
    adjustment_date DATE NOT NULL DEFAULT CURRENT_DATE,
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    total_adjusted_quantity INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES Product(product_id) ON DELETE RESTRICT,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- StockAdjustmentDetails Table
CREATE TABLE StockAdjustmentDetails (
    sad_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    adjustment_id INT NOT NULL,
    product_id INT NOT NULL,
    adjusted_quantity INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    date_adjusted DATE NOT NULL,
    FOREIGN KEY (adjustment_id) REFERENCES AdjustmentLine(adjustment_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id) ON DELETE RESTRICT
);

-- CustomerReturn Table
CREATE TABLE CustomerReturn (
    customer_return_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    return_reason VARCHAR(255) NOT NULL,
    return_date DATE NOT NULL DEFAULT CURRENT_DATE,
    refund_status ENUM('Refunded', 'Replaced', 'Pending') NOT NULL DEFAULT 'Pending',
    total_amount DECIMAL(10,2) NOT NULL CHECK (total_amount >= 0),
    createdbyid INT,
    createdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedbyid INT,
    updatedate DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (createdbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (updatedbyid) REFERENCES Admin(admin_id) ON DELETE SET NULL
);

-- CustomerReturnDetails Table
CREATE TABLE CustomerReturnDetails (
    return_detail_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    customer_return_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_returned INT NOT NULL CHECK (quantity_returned > 0),
    unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price > 0),
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (customer_return_id) REFERENCES CustomerReturn(customer_return_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id) ON DELETE RESTRICT
);

-- Audit_Log Table
CREATE TABLE Audit_Log (
    auditlog_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    login_datetime DATETIME NOT NULL,
    admin_id INT,
    description VARCHAR(255) NOT NULL,
    status ENUM('Success', 'Failed', 'Pending', 'Cancelled') NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);