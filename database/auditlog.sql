-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2025 at 12:04 PM
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
(5, 4, 'Login', 'User logged in from IP 10.0.0.1', '2025-03-14 13:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auditlog`
--
ALTER TABLE `auditlog`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditlog`
--
ALTER TABLE `auditlog`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auditlog`
--
ALTER TABLE `auditlog`
  ADD CONSTRAINT `auditlog_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
