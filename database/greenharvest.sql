-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 04:26 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `greenharvest`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `crop_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `customer_id`, `crop_id`, `quantity`, `created_at`) VALUES
(10, 13, 3, 50, '2025-07-21 14:24:55');

-- --------------------------------------------------------

--
-- Table structure for table `crops`
--

CREATE TABLE `crops` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `season` varchar(50) DEFAULT NULL,
  `price_per_kg` decimal(10,2) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `insurance_status` enum('insured','not_insured') DEFAULT NULL,
  `certificate_available` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crops`
--

INSERT INTO `crops` (`id`, `farmer_id`, `name`, `description`, `image`, `season`, `price_per_kg`, `video`, `insurance_status`, `certificate_available`) VALUES
(1, 1, 'corn', '2+ Million Crop Farming Royalty.', NULL, 'summer ', 100.00, 'uploads/videos/WhatsApp Video 2025-03-02 at 1.33.18 PM.mp4', 'insured', 1),
(2, 1, 'pentuens ', 'pentuens 100% real', NULL, 'summer ', 1200.00, 'uploads/videos/WhatsApp_Video_2025-03-02_at_1.33.18_PM.mp4', 'insured', 1),
(3, 10, '123', '123', NULL, 'summer ', 12.00, NULL, 'insured', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `otp` int(11) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `pass_key` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `password`, `city`, `otp`, `is_verified`, `pass_key`) VALUES
(1, 'daku', 'kukadiyavarshil1@gmail.com', '$2y$10$hdSAFAgQswSJ6ea9uZOsrOa2.bG4Pn1o1Vbk8IIdf.OkfT6w0jaQ6', 'Amreli', NULL, 0, NULL),
(7, 'bapu', 'kukadiyavarshil11@gmail.com', '$2y$10$geeS.JrqIxT0GDPMFMCC5e6zMDb7HcKCo.7VPHuF/yrsdHGME8UCe', 'Amreli', NULL, 1, NULL),
(13, 'varshil', 'kukadiyavarshil@gmail.com', '$2y$10$qOpPADOVPSqaqpx9vcgu9eUDEWWIoAvZLrhdttWEDJTOy9l2RjDFi', 'kanpar', NULL, 1, NULL),
(14, 'Bapu', 'bapu@gmail.com', '$2y$10$ndu2HP0R3xMOZTQ6KUQh2uxzAyiFjhge8wxxQZH4TMbiUcoLsaEnK', 'Bapu', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `certification_status` enum('pending','verified','rejected') DEFAULT NULL,
  `certification_doc` varchar(255) DEFAULT NULL,
  `insurance_doc` varchar(255) DEFAULT NULL,
  `crop_certification` varchar(255) NOT NULL,
  `otp` varchar(6) DEFAULT '0',
  `is_verified` tinyint(1) DEFAULT 0,
  `pass_key` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `name`, `email`, `password`, `location`, `bio`, `certification_status`, `certification_doc`, `insurance_doc`, `crop_certification`, `otp`, `is_verified`, `pass_key`) VALUES
(1, 'arpit', 'arpit@gmail.com', '$2y$10$hiO6SuhmxE2.FrH4jYwzruxUw6p8dp65YTsVlt5IcDyBTdpZM8Nwq', 'kanpar', 'i am a farmer,i love farmeing.', 'pending', 'IMG_20220904_161153.jpg', 'peakpx.jpg', 'organic', '0', 0, NULL),
(10, 'pratik', 'pratikkavathiya60@gmail.com', '$2y$10$G1i6hY1rRUUBZ2x87qeNjuyvBFyIVP0jpmH1e6Z.CULjWCohSu7JK', 'ujala', 'hello', 'verified', '', NULL, 'non-organic', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `farm_visits`
--

CREATE TABLE `farm_visits` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('requested','approved','rejected') NOT NULL DEFAULT 'requested'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farm_visits`
--

INSERT INTO `farm_visits` (`id`, `farmer_id`, `customer_id`, `date`, `description`, `status`) VALUES
(1, 1, 1, '2025-06-13', 'i see my corn crop.', 'approved'),
(2, 1, 7, '2025-06-18', 'sa', 'approved'),
(3, 1, 13, '2025-06-30', 'hello i want a farm visit', 'requested'),
(4, 1, 14, '2025-07-18', 'Test for me', 'requested');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `customer_id`, `order_id`, `farmer_id`, `rating`, `comment`) VALUES
(1, 1, 1, 1, 5, 'nice i never seen before like this type of corn.'),
(2, 7, 2, 1, 4, 'nice product');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `crop_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','shipped','delivered') DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `advance_payment` decimal(10,2) DEFAULT NULL,
  `farmer_id` int(11) DEFAULT NULL,
  `payment_method` enum('cod','online') DEFAULT 'cod',
  `payment_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `crop_id`, `quantity`, `total_price`, `status`, `order_date`, `advance_payment`, `farmer_id`, `payment_method`, `payment_id`) VALUES
(1, 1, 1, 100, 10000.00, 'delivered', '2025-06-10 10:23:57', NULL, NULL, 'cod', NULL),
(2, 7, 1, 12, 1200.00, 'delivered', '2025-06-14 11:22:23', NULL, NULL, 'cod', NULL),
(43, 13, NULL, NULL, 15600.00, '', '2025-07-21 17:51:06', NULL, NULL, 'online', NULL),
(44, 13, NULL, NULL, 15600.00, '', '2025-07-21 18:01:31', NULL, NULL, 'online', NULL),
(45, 13, NULL, NULL, 15600.00, '', '2025-07-21 18:01:44', NULL, NULL, 'online', NULL),
(46, 13, NULL, NULL, 15600.00, '', '2025-07-21 18:01:55', NULL, NULL, 'online', NULL),
(47, 13, NULL, NULL, 15600.00, '', '2025-07-21 18:02:05', NULL, NULL, 'online', NULL),
(48, 13, NULL, NULL, 15600.00, '', '2025-07-21 18:03:23', NULL, NULL, 'online', NULL),
(49, 13, NULL, NULL, 1200.00, '', '2025-07-21 18:18:44', NULL, NULL, 'online', NULL),
(50, 13, NULL, NULL, 15600.00, '', '2025-07-21 18:21:39', NULL, NULL, 'online', NULL),
(51, 13, NULL, NULL, 1200.00, '', '2025-07-21 18:22:53', NULL, NULL, 'online', NULL),
(52, 13, 3, 100, 1200.00, 'pending', '2025-07-21 18:26:34', NULL, NULL, 'cod', NULL),
(53, 13, NULL, NULL, 15600.00, '', '2025-07-21 18:27:08', NULL, NULL, 'online', NULL),
(54, 13, NULL, NULL, 144.00, '', '2025-07-21 18:30:21', NULL, NULL, 'online', NULL),
(55, 13, 2, 33, 39600.00, 'pending', '2025-07-21 19:54:15', NULL, NULL, 'cod', NULL),
(56, 13, 1, 123, 12300.00, 'pending', '2025-07-21 19:54:15', NULL, NULL, 'cod', NULL),
(57, 13, 3, 12, 144.00, 'pending', '2025-07-21 19:54:29', NULL, NULL, 'cod', NULL),
(58, 13, NULL, NULL, 600.00, '', '2025-07-21 19:55:37', NULL, NULL, 'online', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `crop_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price_per_kg` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `crop_id`, `quantity`, `price_per_kg`) VALUES
(1, 50, 2, 12, 1200.00),
(2, 50, 1, 12, 100.00),
(3, 51, 3, 100, 12.00),
(4, 53, 2, 12, 1200.00),
(5, 53, 1, 12, 100.00),
(6, 54, 3, 12, 12.00),
(7, 58, 3, 50, 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `razorpay_payment_id` varchar(100) NOT NULL,
  `razorpay_order_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('success','failed') DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `razorpay_payment_id`, `razorpay_order_id`, `amount`, `status`, `created_at`, `customer_id`, `order_id`) VALUES
(12, 'pay_QmDXZYqLBQ9Qzp', 'order_QmDX6u6XaDAWZx', 700.00, 'success', '2025-06-27 12:18:03', 13, NULL),
(21, 'pay_QvirvDbqIzAdji', 'order_Qvirn4VGpyIGIW', 1200.00, 'success', '2025-07-21 12:48:44', 13, 49),
(22, 'pay_Qviw0yXYewQeY3', 'order_Qvivu38yuxXCpp', 15600.00, 'success', '2025-07-21 12:51:39', 13, 2),
(23, 'pay_QvixIn9UMis6Qq', 'order_QvixAAhe5EjZt4', 1200.00, 'success', '2025-07-21 12:52:53', 13, 3),
(24, 'pay_Qvj1nIthI0IlIu', 'order_Qvj1aghn7u0Fn7', 15600.00, 'success', '2025-07-21 12:57:08', 13, 5),
(25, 'pay_Qvj5CO6gY2bcrn', 'order_Qvj54J9QkVo5An', 144.00, 'success', '2025-07-21 13:00:21', 13, 6),
(26, 'pay_QvkXEvBfej8tcw', 'order_QvkWw4isVgs0JT', 600.00, 'success', '2025-07-21 14:25:37', 13, 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crops`
--
ALTER TABLE `crops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `farm_visits`
--
ALTER TABLE `farm_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `crop_id` (`crop_id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `crops`
--
ALTER TABLE `crops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `farm_visits`
--
ALTER TABLE `farm_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `crops`
--
ALTER TABLE `crops`
  ADD CONSTRAINT `crops_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`);

--
-- Constraints for table `farm_visits`
--
ALTER TABLE `farm_visits`
  ADD CONSTRAINT `farm_visits_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`),
  ADD CONSTRAINT `farm_visits_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`crop_id`) REFERENCES `crops` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
