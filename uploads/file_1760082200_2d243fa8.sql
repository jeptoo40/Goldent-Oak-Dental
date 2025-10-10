-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2025 at 06:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sunleaf-tech`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('Stock OK','Reorder','Low Stock') DEFAULT 'Stock OK',
  `revenue` decimal(12,2) DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT 0.0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `brand_name` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `main_image_url` varchar(255) DEFAULT NULL,
  `thumbnail_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`thumbnail_urls`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `status`, `revenue`, `price`, `quantity`, `rating`, `created_at`, `updated_at`, `brand_name`, `meta_description`, `main_image_url`, `thumbnail_urls`) VALUES
(26, 'Musonye', 'BYello', 'Battery', '', 0.00, 0.13, 23, 0.0, '2025-07-24 22:04:19', '2025-07-24 22:04:19', NULL, NULL, 'products/6882addc8cb14_51F7FbKK6PL.jpg', '[\"products\\/6882ade2181e1_51F7FbKK6PL.jpg\"]'),
(27, 'Musonye', 'BYello', 'Battery', '', 0.00, 0.13, 23, 0.0, '2025-07-24 22:04:19', '2025-07-24 22:04:19', NULL, NULL, 'products/6882addc8cb14_51F7FbKK6PL.jpg', '[\"products\\/6882ade2181e1_51F7FbKK6PL.jpg\"]'),
(28, 'Lithium 20KwH', 'Black', 'Lithium Battery', '', 0.00, 44000.00, 30, 0.0, '2025-07-29 09:15:43', '2025-07-29 09:15:43', NULL, NULL, 'products/6888913cc26e7_14-2.jpg', '[]'),
(29, 'Lithium 20KwH', 'Black', 'Lithium Battery', '', 0.00, 44000.00, 30, 0.0, '2025-07-29 09:15:44', '2025-07-29 09:15:44', NULL, NULL, 'products/6888913cc26e7_14-2.jpg', '[]'),
(30, 'Lithium 10KwH', 'white', 'Battery', '', 0.00, 80000.00, 12, 0.0, '2025-07-29 09:16:56', '2025-07-29 09:16:56', NULL, NULL, 'products/6888918712580_Power-Wall-mounted-Lithium-Battery-Pack.webp', '[]'),
(31, 'Lithium 10KwH', 'white', 'Battery', '', 0.00, 80000.00, 12, 0.0, '2025-07-29 09:16:56', '2025-07-29 09:16:56', NULL, NULL, 'products/6888918712580_Power-Wall-mounted-Lithium-Battery-Pack.webp', '[]'),
(32, 'Solar Hybrid Inverter', 'White', 'Inverters', '', 0.00, 25000.00, 17, 0.0, '2025-07-29 09:18:12', '2025-07-29 09:18:12', NULL, NULL, 'products/688891d331b01_1000142187-1.png', '[]'),
(33, 'Solar Hybrid Inverter', 'White', 'Inverters', '', 0.00, 25000.00, 17, 0.0, '2025-07-29 09:18:12', '2025-07-29 09:18:12', NULL, NULL, 'products/688891d331b01_1000142187-1.png', '[]'),
(34, 'Random P', 'Black', 'None', '', 0.00, 22000.00, 16, 0.0, '2025-08-05 04:02:50', '2025-08-05 04:02:50', NULL, NULL, 'products/689182677ef0e_1s.PNG', '[]'),
(35, 'Random P', 'Black', 'None', '', 0.00, 22000.00, 16, 0.0, '2025-08-05 04:02:50', '2025-08-05 04:02:50', NULL, NULL, 'products/689182677ef0e_1s.PNG', '[]');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `quote_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `quote_number`, `customer_name`, `customer_email`, `file_path`, `created_at`) VALUES
(1, 'Q-1753956778', 'Paul Johnson', 'pauljohnson@gmai.com', 'quotes/quote_1.pdf', '2025-07-31 10:12:58'),
(2, 'Q-1753986708', 'Carson Ben', 'carson@gmail.com', '', '2025-07-31 18:31:48'),
(3, 'Q-1754004179', 'Elan', 'elanwalton@gmail.com', 'quotes/quote_3.pdf', '2025-07-31 23:22:59'),
(4, 'Q-1754004966', 'James Maina', 'elanwalker865@gmail.com', 'quotes/quote_4.pdf', '2025-07-31 23:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `quote_items`
--

CREATE TABLE `quote_items` (
  `id` int(11) NOT NULL,
  `quote_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quote_items`
--

INSERT INTO `quote_items` (`id`, `quote_id`, `description`, `quantity`, `price`) VALUES
(1, 3, 'Copper Cable ', 20, 130.00),
(2, 4, 'EarthRod', 2, 1800.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `second_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(50) DEFAULT 'customer',
  `status` varchar(20) DEFAULT 'Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `second_name`, `email`, `phone`, `password`, `created_at`, `role`, `status`) VALUES
(1, 'Elan', 'musonye', 'elanwalton@gmail.com', NULL, '$2y$10$b.Z5tmZmXsSZ4tSZrVGLM.8Un3tPqhlvxrEvRcK4Me3njIihBybB6', '2025-04-23 08:28:00', 'admin', 'Inactive'),
(3, 'elan', 'walker', 'elanwalker865@gmail.com', NULL, '$2y$10$o4HGFcWl.4klzTvHf8ZrNeiybLGNwITZlU7Q.vSE6x.u2/gHpwFnW', '2025-04-23 19:24:05', 'CUSTOMER', 'Inactive'),
(4, 'Elan', 'musonye', 'elan@gmail.com', NULL, '$2y$10$h8669WI8Eu0.o8sO6oiGKu3VWly95hYJm2iBefEYZcr5LKOMZTXSO', '2025-05-07 09:04:37', 'CUSTOMER', 'Inactive'),
(5, 'Elan', 'walton', 'elanwalton@yahoo.com', NULL, '$2y$10$iEyTWP9ERfB5WMkph10vVOMy/OIKaA1hmKpJsf54oqDMD7aWmVTAe', '2025-05-13 06:20:37', 'customer', 'Inactive'),
(6, 't4trtg', 'gfg', 'elanwalker8645@gmail.com', NULL, '$2y$10$LT6LOZlgIJk1VwuzWje2peTn.R6Fi0uUft7o1hUcz76YFK3WeSbim', '2025-07-03 18:32:41', 'customer', 'Inactive');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quote_items`
--
ALTER TABLE `quote_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quote_id` (`quote_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quote_items`
--
ALTER TABLE `quote_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `quote_items`
--
ALTER TABLE `quote_items`
  ADD CONSTRAINT `quote_items_ibfk_1` FOREIGN KEY (`quote_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
