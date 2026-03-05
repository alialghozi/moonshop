-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2025 at 08:06 PM
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
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `buyer_id`, `product_id`, `quantity`, `created_at`) VALUES
(20, 8, 6, 1, '2024-12-27 21:02:22'),
(21, 8, 5, 9, '2024-12-27 21:07:31'),
(29, 10, 5, 1, '2024-12-28 17:32:13'),
(30, 10, 9, 1, '2025-01-15 03:00:38');

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `total_sales` decimal(10,2) DEFAULT NULL,
  `commission` decimal(10,2) DEFAULT NULL,
  `income_after_commission` decimal(10,2) DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `income` decimal(10,2) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`id`, `seller_id`, `total_sales`, `commission`, `income_after_commission`, `period_start`, `period_end`, `total_price`, `tax_amount`, `income`, `order_id`, `product_id`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, 14.00, 0.70, 0.70, 3, 6),
(2, NULL, NULL, NULL, NULL, NULL, NULL, 45.00, 2.25, 2.25, 3, 5),
(3, NULL, NULL, NULL, NULL, NULL, NULL, 10.00, 0.50, 0.50, 3, 7),
(4, NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 0.10, 0.10, 5, 8),
(5, NULL, NULL, NULL, NULL, NULL, NULL, 4.00, 0.20, 0.20, 7, 8),
(6, NULL, NULL, NULL, NULL, NULL, NULL, 50.00, 2.50, 2.50, 8, 7),
(7, NULL, NULL, NULL, NULL, NULL, NULL, 4.00, 0.20, 0.20, 9, 8),
(8, NULL, NULL, NULL, NULL, NULL, NULL, 8.00, 0.40, 0.40, 10, 8),
(9, NULL, NULL, NULL, NULL, NULL, NULL, 4.00, 0.20, 0.20, 11, 6),
(10, NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 0.10, 0.10, 12, 6),
(11, NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 0.10, 0.10, 13, 6),
(12, NULL, NULL, NULL, NULL, NULL, NULL, 5.00, 0.25, 0.25, 14, 5),
(13, NULL, NULL, NULL, NULL, NULL, NULL, 4.00, 0.20, 0.20, 15, 8);

--
-- Triggers `income`
--
DELIMITER $$
CREATE TRIGGER `set_income_on_insert` BEFORE INSERT ON `income` FOR EACH ROW BEGIN
    SET NEW.income = NEW.total_price * 0.05;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `set_income_on_update` BEFORE UPDATE ON `income` FOR EACH ROW BEGIN
    SET NEW.income = NEW.total_price * 0.05;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `status` enum('Pending','Processing','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_price` decimal(10,2) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postcode` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `product_id`, `quantity`, `status`, `created_at`, `total_price`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `postcode`, `payment_method`, `total`) VALUES
(1, 10, NULL, NULL, 'Cancelled', '2024-12-27 16:22:38', 0.00, 'ali', 'alghozi', 'ai@gamil.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'credit_card', 69.00),
(3, 10, NULL, NULL, 'Cancelled', '2024-12-27 16:31:01', 0.00, 'ali', 'alghozi', 'ai@gamil.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'credit_card', 69.00),
(4, 10, NULL, NULL, 'Cancelled', '2024-12-27 16:36:05', 0.00, 'ali', 'alghozi', 'ai@gamil.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'credit_card', 0.00),
(5, 10, NULL, NULL, 'Cancelled', '2024-12-27 16:57:00', 0.00, 'ali', 'alghozi', 'ai@gamil.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'cash_on_delivery', 2.00),
(6, 15, NULL, NULL, 'Cancelled', '2024-12-27 17:42:46', 0.00, 'ali', 'alghozi', 'ai@gmail.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'bank_transfer', 0.00),
(7, 15, NULL, NULL, 'Cancelled', '2024-12-27 17:59:46', 0.00, 'ali', 'alghozi', 'ai@gmail.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'bank_transfer', 4.00),
(8, 15, NULL, NULL, 'Cancelled', '2024-12-27 18:25:55', 0.00, 'ali', 'alghozi', 'alghozi@gmail.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'credit_card', 50.00),
(9, 15, NULL, NULL, 'Cancelled', '2024-12-27 18:42:34', 0.00, 'ali', 'alghozi', 'alghozi@gmail.com', '3343434', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'cash_on_delivery', 4.00),
(10, 2, NULL, NULL, 'Cancelled', '2024-12-27 20:03:01', 0.00, 'ali', 'alghozi', 'man@gmail.com', '3455435', 'fsffs fsfsd sfsdf', 'sfsfsd', '2323', 'bank_transfer', 8.00),
(11, 16, NULL, NULL, 'Pending', '2024-12-27 20:07:05', 0.00, 'ali', 'alghozi', 'alghozi@gmail.com', '3455435', 'dhds', 'sfsfsd', '2323', 'cash_on_delivery', 4.00),
(12, 8, NULL, NULL, 'Pending', '2024-12-27 20:14:37', 0.00, 'dfsgh', 'fsdf', 'dddd@gmail.com', '35465675645', 'sdfghfdg', 'gdfgfds', 'lk', 'credit_card', 2.00),
(13, 10, NULL, NULL, 'Pending', '2024-12-28 08:38:37', 0.00, 'www', 'vxc', 'ai@gmail.com', '353535', 'fsffs fsfsd sfsdf', 'ter', 'ytre', 'credit_card', 2.00),
(14, 10, NULL, NULL, 'Pending', '2024-12-28 10:42:33', 0.00, 'dff', 'sdf', 'fdsflkjdsf@gmail.com', '45453', 'fddgfds', 'erere', 'fdfd', 'bank_transfer', 5.00),
(15, 15, NULL, NULL, 'Cancelled', '2024-12-28 11:24:53', 0.00, 'hfd', 'dff', 'dddd@gmail.com', 'fgfg', 'gfgf', 'fgfg', 'fgfg', 'bank_transfer', 4.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `total_price`, `tax_amount`) VALUES
(1, 3, 6, 7, 2.00, 14.00, 0.70),
(2, 3, 5, 9, 5.00, 45.00, 2.25),
(3, 3, 7, 1, 10.00, 10.00, 0.50),
(4, 5, 8, 1, 2.00, 2.00, 0.10),
(5, 7, 8, 2, 2.00, 4.00, 0.20),
(6, 8, 7, 5, 10.00, 50.00, 2.50),
(7, 9, 8, 2, 2.00, 4.00, 0.20),
(8, 10, 8, 4, 2.00, 8.00, 0.40),
(9, 11, 6, 2, 2.00, 4.00, 0.20),
(10, 12, 6, 1, 2.00, 2.00, 0.10),
(11, 13, 6, 1, 2.00, 2.00, 0.10),
(12, 14, 5, 1, 5.00, 5.00, 0.25),
(13, 15, 8, 2, 2.00, 4.00, 0.20);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `seller_name` varchar(255) DEFAULT NULL,
  `shop_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `eco_rating` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seller_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT 'placeholder.jpg',
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `shop_id`, `seller_name`, `shop_name`, `name`, `description`, `price`, `eco_rating`, `quantity`, `status`, `image`, `created_at`, `seller_id`, `image_url`, `stock`) VALUES
(5, 5, 'd s', 'ss', 'TEA', 'very nice', 5.00, 4, 9, 'approved', 'uploads/6767e3ef593a1_software-development.png', '2024-12-22 09:55:03', 16, 'placeholder.jpg', 0),
(6, 5, 'd s', 'ss', 'apple', 'good', 2.00, 4, 0, 'approved', 'uploads/67698ebb35e76_globalization (1).png', '2024-12-22 10:41:55', 16, 'placeholder.jpg', 0),
(7, 5, 'd s', 'ss', 'book', 'good good', 10.00, 3, 0, 'approved', 'uploads/67698f970ace4_hacker.png', '2024-12-23 16:28:07', 16, 'placeholder.jpg', 5),
(8, 4, 'ali alghozi', 'ali', 'earth', 'nice', 2.00, 5, 27, 'approved', 'uploads/677154ede6689_worldwide (1).png', '2024-12-26 09:33:39', 15, 'placeholder.jpg', 4),
(9, 4, 'ali alghozi', 'ali', 'pen', 'nice', 3.00, 4, 7, 'approved', 'uploads/67705cd7d9de2_w2500.jpg', '2024-12-28 20:17:27', 15, 'placeholder.jpg', 0),
(10, 4, 'ali alghozi', 'ali', 'boll', 'fff', 3.00, 5, 2, 'approved', 'uploads/67705ea272a86_Fresh-Red-Delicious-Apple-Each_7320e63a-de46-4a16-9b8c-526e15219a12_3.e557c1ad9973e1f76f512b34950243a3.jpg', '2024-12-28 20:25:06', 15, 'placeholder.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sale_amount` decimal(10,2) NOT NULL,
  `sale_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `shop_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shop_description` text DEFAULT NULL,
  `shop_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`id`, `seller_id`, `shop_name`, `status`, `created_at`, `shop_description`, `shop_location`) VALUES
(1, 8, 'coffee shop ', 'pending', '2024-12-21 21:42:08', 'good', NULL),
(4, 15, 'ali', 'pending', '2024-12-22 05:51:29', 'ali', 'Alor Setar, 05200 Alor Setar,'),
(5, 16, 'ss', 'verified', '2024-12-22 06:00:48', 'ss', 'Alor Setar, 05200 Alor Setar,');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `role` enum('buyer','seller','owner') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `status` enum('pending','approved') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `address`, `phone_number`, `role`, `created_at`, `first_name`, `last_name`, `status`) VALUES
(2, 'alialialghuzi@gmail.com', '$2y$10$5FAYamgPSGFpIb5Xoc7W4ucoAXB.fgtpHJpJrnWdQq1QJXQmQe6GO', NULL, NULL, 'owner', '2024-12-21 17:11:41', 'ali', 'alghozi', 'approved'),
(8, 'alia@gmail.com', '$2y$10$4lSfpHGOxAyJAUnGsPqQt.AsCiqnKUwPIgVLrlrI5EshmGif6vO0S', 'Jln Tun Razak, Bandar Alor Setar, 05200 Alor Setar,', '0196176860', 'buyer', '2024-12-21 18:57:02', 'ali', 'alghozi', 'approved'),
(10, 'ai@gmail.com', '$2y$10$1GlI2iP7dOQjliVAddTYc.jimlVv14vY/HYtGQWLcy9nBwgNKrw2.', 'Jln Tun Razak, Bandar Alor Setar, 05200 Alor Setar,', '0196176860', 'buyer', '2024-12-21 20:52:45', 'ali', 'a', NULL),
(15, 'zi@gmail.com', '$2y$10$1BDDl6peUgl90xllsBC6X.lNKUBMNbq4UVaB0Kennz1YMHDQ3ucJ6', 'Jln Tun Razak, Bandar Alor Setar, 05200 Alor Setar,', '0196176860', 'seller', '2024-12-22 05:51:29', 'ali', 'alghozi', 'approved'),
(16, 'ss@gfgfgfg.com', '$2y$10$f8pnVG8IAzKK7IbefAsTxOGGTW5q5nHP8ED84pjOvYXxKJQqOEOPu', 'Jln Tun Razak, Bandar Alor Setar, 05200 Alor Setar,', '0196176860', 'seller', '2024-12-22 06:00:48', 'd', 's', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `fk_order` (`order_id`),
  ADD KEY `fk_product` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seller_id` (`seller_id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `income_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `fk_seller_id` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shops_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
