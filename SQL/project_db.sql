-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 13, 2025 at 12:45 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(4, 'Household'),
(3, 'Kids'),
(1, 'Men'),
(2, 'Women');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `phone`, `email`, `password`, `registration_date`) VALUES
(1, 'Mahesh', '8105940855', 'maheshhegde332@gmail.com', '$2y$10$KzlGP2bc82WR12gUMgurdepnA1NPrI.7xq/kX8ZVMl2s451poNOpK', '2025-03-28 18:26:46'),
(4, 'Manjunath', '9483665830', 'hegdem420@gmail.com', '$2y$10$1TJud1EYIhcHV7ZG30QOEudnLfbkr/L55jdxjhtPdjIebE0UgmrWm', '2025-04-08 19:31:38'),
(5, 'newuser', '9876543211', 'new@gmail.com', '$2y$10$os4cVRySeLoY7oMWdtCuRO5MH2/5qEgwejXXvuCnbdc5g9NCaP/1y', '2025-04-11 19:45:13'),
(6, 'hegde', '9945467612', 'hegde@gmail.com', '$2y$10$I21lnlugA8X54tgpRnUrmuiT2E8JxpVHhstxvtHbPtDpHF8ha2vh2', '2025-04-14 20:42:58'),
(8, 'Bhushan', '8892416843', 'bhushankalgar@gmail.com', '$2y$10$jU/WVspKE0bUQhnsf/ngUOecZRp0l2tzOjPKzOZQ7m8zlGp7Ux19C', '2025-04-24 12:49:35'),
(9, 'vikas', '8431470674', 'vikas@gmail.com', '$2y$10$5IpoKbfm4ohttGzl4d/9ZeLQNTkab7Im7mxeQaLeeY.m4Un/kEvPC', '2025-05-11 21:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `employee_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` enum('Washer','Ironing','Delivery','Admin') NOT NULL,
  `email` varchar(55) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `rating` int NOT NULL,
  `comments` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `order_id`, `customer_id`, `rating`, `comments`, `created_at`) VALUES
(2, 32, 1, 4, 'good but need improvement', '2025-05-11 22:36:36'),
(3, 31, 1, 5, 'nice service ', '2025-05-12 15:13:38'),
(4, 35, 1, 4, 'good', '2025-05-12 15:19:49'),
(5, 33, 6, 4, 'nice service liked it', '2025-05-13 00:24:27');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_responses`
--

DROP TABLE IF EXISTS `feedback_responses`;
CREATE TABLE IF NOT EXISTS `feedback_responses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `feedback_id` int NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_by` int NOT NULL,
  `sent_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback_responses`
--

INSERT INTO `feedback_responses` (`id`, `feedback_id`, `subject`, `message`, `sent_by`, `sent_at`) VALUES
(1, 4, 'Regarding Your Feedback', 'nice to have reivew', 1, '2025-05-12 23:27:46'),
(2, 2, 'Regarding Your Feedback', 'pls send now \n', 1, '2025-05-12 23:30:48'),
(3, 2, 'Regarding Your Feedback', 'testing', 1, '2025-05-12 23:32:23'),
(4, 2, 'Regarding Your Feedback', 'last testing of the day ', 1, '2025-05-12 23:34:22'),
(5, 4, 'Regarding Your Feedback', 'hii bro', 1, '2025-05-12 23:42:41'),
(6, 4, 'Regarding Your Feedback', 'hii', 1, '2025-05-12 23:45:20'),
(7, 4, 'Regarding Your Feedback', 'hii 2', 1, '2025-05-12 23:50:11'),
(8, 2, 'Regarding Your Feedback', '22 testing ', 1, '2025-05-12 23:52:25'),
(9, 3, 'Regarding Your Feedback', 'another', 1, '2025-05-12 23:53:25'),
(10, 4, 'Regarding Your Feedback', 'not now', 1, '2025-05-12 23:55:32'),
(11, 3, 'Regarding Your Feedback', 'ok sir', 1, '2025-05-13 00:12:23'),
(12, 3, 'Regarding Your Feedback', 'last test', 1, '2025-05-13 00:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `quantity` int NOT NULL,
  `unit` varchar(50) NOT NULL,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `total_price` int NOT NULL,
  `pickup_date` date DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `pickup_address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','processing','completed','out_for_delivery') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `total_price`, `pickup_date`, `pickup_time`, `pickup_address`, `created_at`, `status`) VALUES
(29, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 45, '2025-05-09', '23:30:00', 'dharwad', '2025-05-07 19:19:16', 'pending'),
(30, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 50, '2025-05-11', '11:20:00', 'hello', '2025-05-07 19:24:06', 'completed'),
(31, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 50, '2025-05-09', '22:47:00', 'ahfkahfklsa', '2025-05-08 17:14:19', 'completed'),
(32, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 120, '2025-05-12', '11:11:00', 'dvf d bf hg nvv d v', '2025-05-11 15:10:44', 'pending'),
(33, 6, 'hegde', 'hegde@gmail.com', '9945467612', 110, '2025-05-14', '11:11:00', '11affaf', '2025-05-11 20:25:45', 'processing'),
(34, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 50, '2025-05-21', '11:11:00', 'asfaffff', '2025-05-11 22:25:52', 'pending'),
(35, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 90, '2025-05-14', '23:11:00', 'hukkerikar nagar ,ys colony dharwad', '2025-05-12 15:19:30', 'pending'),
(36, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 80, '2025-05-14', '12:15:00', 'djarajc', '2025-05-12 18:43:31', 'pending'),
(37, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 40, '2025-05-15', '00:19:00', 'mahesh hegde', '2025-05-12 18:45:59', 'pending'),
(38, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 50, '2025-05-16', '08:08:00', 'dhaha', '2025-05-13 00:38:38', 'pending'),
(39, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 50, '2025-05-15', '00:34:00', 'dharwad', '2025-05-13 00:43:02', 'pending'),
(40, 1, 'Mahesh', 'maheshhegde332@gmail.com', '8105940855', 140, '2025-05-21', '00:03:00', 'anywhere', '2025-05-13 00:43:54', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_assignment`
--

DROP TABLE IF EXISTS `order_assignment`;
CREATE TABLE IF NOT EXISTS `order_assignment` (
  `assigment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `employee_id` int DEFAULT NULL,
  `task` varchar(250) NOT NULL,
  `status` enum('Pending','Out for Delivery','Delivered') DEFAULT 'Pending',
  PRIMARY KEY (`assigment_id`),
  KEY `order_id` (`order_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `comments` text,
  `service_type` varchar(20) DEFAULT 'wash_iron',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_name`, `quantity`, `price`, `comments`, `service_type`) VALUES
(1, 29, 'Shirt Half Premium Pack', 1, 45.00, 'white shirt dont mix it others', 'wash_iron'),
(2, 30, 'Trousers Flat Front Premium Pk', 1, 50.00, 'nothing', 'wash_iron'),
(3, 31, 'Trousers Flat Front Premium Pk', 1, 50.00, 'white peticote dont not make scracth', 'wash_iron'),
(4, 32, 'Shirt Half Premium Pack', 1, 30.00, 'white shirt donot mix with others', 'wash_fold'),
(5, 32, 'Trousers Flat Front Premium Pk', 1, 40.00, NULL, 'wash_fold'),
(6, 32, 'Salwar Kameez Premium Pack', 1, 50.00, NULL, 'wash_fold'),
(7, 33, 'Shirt Half Premium Pack', 1, 60.00, NULL, 'dry_clean'),
(8, 33, 'Trousers Flat Front Premium Pk', 1, 50.00, NULL, 'wash_iron'),
(9, 34, 'Trousers Flat Front Premium Pk', 1, 50.00, NULL, 'wash_iron'),
(10, 35, 'Shirt Half Premium Pack', 2, 45.00, 'do not use brush', 'wash_iron'),
(11, 36, 'Salwar Kameez Premium Pack', 1, 80.00, 'wegw', 'wash_iron'),
(12, 37, 'Trousers Flat Front Premium Pk', 1, 40.00, 'dont wash with brush', 'wash_fold'),
(13, 38, 'Trousers Flat Front Premium Pk', 1, 50.00, 'no item sracth', 'wash_iron'),
(14, 39, 'Trousers Flat Front Premium Pk', 1, 50.00, NULL, 'wash_iron'),
(15, 40, 'Jeans Flat Front Premium Pack', 1, 55.00, NULL, 'wash_iron'),
(16, 40, 'Shirt Half Premium Pack', 1, 45.00, NULL, 'wash_iron'),
(17, 40, 'T-Shirt Half Premium Pack', 1, 40.00, NULL, 'wash_iron');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_method` enum('Cash','Card','upi') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `amount`, `payment_date`, `payment_method`) VALUES
(1, 29, 45.00, '2025-05-07 19:19:16', 'Cash'),
(2, 30, 50.00, '2025-05-07 19:24:06', 'Cash'),
(3, 31, 50.00, '2025-05-08 17:14:19', 'Cash'),
(4, 32, 120.00, '2025-05-11 15:10:44', 'Cash'),
(5, 33, 110.00, '2025-05-11 20:25:45', 'Cash'),
(6, 34, 50.00, '2025-05-11 22:25:52', 'Cash'),
(7, 35, 90.00, '2025-05-12 15:19:30', 'Cash'),
(8, 36, 80.00, '2025-05-12 18:43:31', 'Cash'),
(9, 37, 40.00, '2025-05-12 18:45:59', 'Cash'),
(10, 38, 50.00, '2025-05-13 00:38:38', 'Cash'),
(11, 39, 50.00, '2025-05-13 00:43:02', 'Cash'),
(12, 40, 140.00, '2025-05-13 00:43:54', 'Cash');

-- --------------------------------------------------------

--
-- Table structure for table `price_list`
--

DROP TABLE IF EXISTS `price_list`;
CREATE TABLE IF NOT EXISTS `price_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price_wash_iron` decimal(10,2) DEFAULT NULL,
  `price_wash_fold` decimal(10,2) DEFAULT NULL,
  `price_steam_iron` decimal(10,2) DEFAULT NULL,
  `price_dry_clean` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `price_list`
--

INSERT INTO `price_list` (`id`, `category_id`, `name`, `price_wash_iron`, `price_wash_fold`, `price_steam_iron`, `price_dry_clean`, `image_url`) VALUES
(1, 1, 'Shirt Half Premium Pack', 45.00, 30.00, 15.00, 60.00, 'image\\2109 T-shirt half Premium .svg'),
(2, 1, 'Trousers Flat Front Premium Pk', 50.00, 40.00, 20.00, 80.00, 'image\\2120 Trouser Crease Standard .svg'),
(3, 1, 'T-Shirt Half Premium Pack', 40.00, 30.00, 15.00, 60.00, 'image\\2109 T-shirt half Premium .svg'),
(4, 1, 'Jeans Flat Front Premium Pack', 55.00, 40.00, 20.00, 80.00, 'image\\2109 T-shirt half Premium .svg'),
(5, 1, 'Long Coat Hanger Pack', 150.00, 100.00, 50.00, 200.00, 'image\\2109 T-shirt half Premium .svg'),
(6, 1, 'Sherwani Plain Hanger Pack', 150.00, 100.00, 30.00, 200.00, 'image\\2109 T-shirt half Premium .svg'),
(7, 1, 'Ethnic Jacket Hanger Pack', 120.00, 80.00, 30.00, 150.00, 'image\\2109 T-shirt half Premium .svg'),
(8, 1, 'Sherwani Hanger Pack', 175.00, 140.00, 50.00, 250.00, 'image\\2109 T-shirt half Premium .svg'),
(9, 1, 'Jacket Hanger Pack', 150.00, 100.00, 40.00, 200.00, 'image\\2109 T-shirt half Premium .svg'),
(10, 1, 'Jeans pant premium', 45.00, 35.00, 15.00, 60.00, 'image\\2109 T-shirt half Premium .svg'),
(11, 2, 'Saree Regular Pack', 100.00, 70.00, 30.00, 150.00, 'image\\2109 T-shirt half Premium .svg'),
(12, 2, 'Salwar Kameez Premium Pack', 80.00, 50.00, 30.00, 120.00, 'image\\2109 T-shirt half Premium .svg'),
(13, 2, 'Leggings Standard Pack', 40.00, 25.00, 15.00, 80.00, 'image\\2109 T-shirt half Premium .svg'),
(14, 2, 'Top Premium Pack', 45.00, 30.00, 15.00, 80.00, 'image\\2109 T-shirt half Premium .svg'),
(15, 2, 'Blouse Standard Pack', 35.00, 25.00, 15.00, 60.00, 'image\\2109 T-shirt half Premium .svg'),
(16, 2, 'Dupatta Soft Wash Pack', 30.00, 20.00, 12.00, 60.00, 'image\\2109 T-shirt half Premium .svg'),
(17, 2, 'Gown Dry Clean Pack', 150.00, 100.00, 40.00, 200.00, 'image\\2109 T-shirt half Premium .svg'),
(18, 3, 'Kids Shirt Standard Pack', 30.00, 20.00, 10.00, 60.00, 'image\\2109 T-shirt half Premium .svg'),
(19, 3, 'Kids Jeans Premium Pack', 40.00, 30.00, 15.00, 80.00, 'image\\2109 T-shirt half Premium .svg'),
(20, 3, 'Kids Dress Fancy Pack', 50.00, 35.00, 15.00, 80.00, 'image\\2109 T-shirt half Premium .svg'),
(21, 3, 'Kids Sweater Woolen Pack', 60.00, 45.00, 20.00, 90.00, 'image\\2109 T-shirt half Premium .svg'),
(22, 3, 'Kids Shorts Summer Pack', 25.00, 20.00, 10.00, 50.00, 'image\\2109 T-shirt half Premium .svg'),
(23, 3, 'Kids Jacket Winter Pack', 70.00, 50.00, 20.00, 100.00, 'image\\2109 T-shirt half Premium .svg'),
(24, 2, 'Leggins ladeis', 40.00, 30.00, 15.00, 50.00, 'image\\2109 T-shirt half Premium .svg'),
(25, 2, 'Kurti Premium Pack', 50.00, 30.00, 15.00, 80.00, 'image\\2109 T-shirt half Premium .svg'),
(26, 4, 'Curtains Standard Clean (per panel)', 150.00, 100.00, 50.00, 250.00, 'image\\2109 T-shirt half Premium .svg'),
(27, 4, 'Blanket Winter Wash', 200.00, 150.00, 80.00, 300.00, 'image\\2109 T-shirt half Premium .svg'),
(28, 4, 'Carpet Floor Clean (per sq. ft)', 30.00, 20.00, 10.00, 50.00, 'image\\2109 T-shirt half Premium .svg'),
(29, 4, 'Sofa Cover Clean (per seat)', 100.00, 70.00, 30.00, 150.00, 'image\\2109 T-shirt half Premium .svg'),
(30, 4, 'Pillow Cover Standard Wash', 30.00, 20.00, 10.00, 50.00, 'image\\2109 T-shirt half Premium .svg'),
(32, 4, 'Single Bedsheet Premium Wash', 80.00, 50.00, 30.00, 120.00, 'image\\2109 T-shirt half Premium .svg'),
(33, 4, 'Double Bedsheet Premium Wash', 120.00, 90.00, 50.00, 180.00, 'image\\2109 T-shirt half Premium .svg');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price_per_unit` decimal(10,2) NOT NULL,
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `description`, `price_per_unit`) VALUES
(1, 'wash_fold', 'Daily wear. Occasion wear. Clothes for just anytime and anywhere. If it\'s about laundry, it\'s about time you stopped doing it yourself. Leave it to us.', 100.00),
(2, 'Wash_and_iron', 'Daily wear. Occasion wear. Clothes for just anytime and anywhere. If it\'s about laundry, it\'s about time you\r\n            stopped doing it yourself. Leave it to us.', 150.00),
(3, 'Steam_iron', 'Daily wear. Occasion wear. Clothes for just anytime and anywhere. If it\'s about laundry, it\'s about time you\r\n            stopped doing it yourself. Leave it to us.', 80.00),
(4, 'Dry_cleaning', 'Daily wear. Occasion wear. Clothes for just anytime and anywhere. If it\'s about laundry, it\'s about time you\r\n            stopped doing it yourself. Leave it to us.', 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `business_name` varchar(255) DEFAULT NULL,
  `business_email` varchar(255) DEFAULT NULL,
  `business_phone` varchar(50) DEFAULT NULL,
  `business_address` text,
  `currency` varchar(10) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `opening_hours` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `business_name`, `business_email`, `business_phone`, `business_address`, `currency`, `tax_rate`, `opening_hours`, `created_at`, `updated_at`) VALUES
(1, 'Laundry techs', 'laundrytechs@gmail.com', '9844444494', 'Prassanna colony, Basaveshwar Nagar, Hubbali', 'indian rs', 18.00, '6AM-9PM', '2025-04-17 20:11:10', '2025-04-17 20:11:10');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `price_list`
--
ALTER TABLE `price_list`
  ADD CONSTRAINT `price_list_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
