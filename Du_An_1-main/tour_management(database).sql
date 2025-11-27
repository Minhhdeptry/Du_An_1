-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 25, 2025 at 07:40 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tour_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint NOT NULL,
  `booking_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `tour_schedule_id` bigint DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT '0.00',
  `status` enum('PENDING','CONFIRMED','PAID','COMPLETED','CANCELED') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `user_id`, `tour_schedule_id`, `contact_name`, `contact_phone`, `contact_email`, `total_amount`, `status`, `created_at`) VALUES
(5, 'B1763354207', NULL, 2, 'Nguyễn Văn Minh', '0373179123', 'minhdz@gmail.com', 3000000.00, 'PENDING', '2025-11-17 04:36:47'),
(9, 'B1763700965', NULL, 1, 'Nguyễn Văn A', '0123456789', 'anv@gmail.com', 2500000.00, 'PENDING', '2025-11-21 04:56:05'),
(10, 'B1763701303', NULL, 3, 'Nguyễn Văn B', '0987654321', 'bnv@gmail.com', 3000000.00, 'PENDING', '2025-11-21 05:01:43'),
(25, 'B1763725617', NULL, 3, 'Nguyễn Văn A', '0123456789', 'anv@gmail.com', 3000000.00, 'PENDING', '2025-11-21 11:46:57'),
(29, 'B1763985412744', NULL, 5, 'Nguyễn Văn C', '0234567891', 'cnv@gmail.com', 3000000.00, 'PENDING', '2025-11-24 11:56:52');

-- --------------------------------------------------------

--
-- Table structure for table `booking_item`
--

CREATE TABLE `booking_item` (
  `id` bigint NOT NULL,
  `booking_id` bigint NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` int DEFAULT '1',
  `unit_price` decimal(12,2) DEFAULT '0.00',
  `total_price` decimal(12,2) GENERATED ALWAYS AS ((`qty` * `unit_price`)) STORED,
  `type` enum('SERVICE','ROOM','PERSON') COLLATE utf8mb4_unicode_ci DEFAULT 'PERSON'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint NOT NULL,
  `booking_id` bigint DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT '0.00',
  `method` enum('CASH','TRANSFER','CARD','ONLINE') COLLATE utf8mb4_unicode_ci DEFAULT 'CASH',
  `status` enum('PENDING','SUCCESS','FAILED','REFUNDED') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDING',
  `transaction_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint NOT NULL,
  `tour_id` bigint DEFAULT NULL,
  `schedule_id` bigint DEFAULT NULL,
  `total_revenue` decimal(12,2) DEFAULT '0.00',
  `total_cost` decimal(12,2) DEFAULT '0.00',
  `profit` decimal(12,2) GENERATED ALWAYS AS ((`total_revenue` - `total_cost`)) STORED,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `id` bigint NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` bigint NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_desc` text COLLATE utf8mb4_unicode_ci,
  `full_desc` text COLLATE utf8mb4_unicode_ci,
  `base_price` decimal(12,2) DEFAULT '0.00',
  `duration_days` int DEFAULT '0',
  `category_id` bigint DEFAULT NULL,
  `policy` text COLLATE utf8mb4_unicode_ci,
  `supplier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tours`
--

INSERT INTO `tours` (`id`, `code`, `title`, `short_desc`, `full_desc`, `base_price`, `duration_days`, `category_id`, `policy`, `supplier`, `image_url`, `is_active`, `created_at`) VALUES
(1, 'T001', 'Hà Nội - Hạ Long (2N1Đ)', 'Khám phá vịnh Hạ Long', 'Chi tiết lịch trình...', 2500000.00, 2, 1, NULL, NULL, 'assets/images/halong.jpg', 1, '2025-11-09 08:40:11'),
(2, 'T002', 'Sài Gòn - Cần Thơ (3N2Đ)', 'Du lịch miền Tây', 'Chi tiết lịch trình...', 3000000.00, 3, 1, NULL, NULL, 'assets/images/mientay.jpg', 1, '2025-11-09 08:40:11'),
(4, 'T003', 'Du lịch Hàn Quốc (7N6Đ)', 'Du lịch hàn quốc', 'Chi tiết lịch trình...', 3000000.00, 7, 2, NULL, NULL, 'assets/images/hanquoc.jpg', 1, '2025-11-24 05:04:55');

-- --------------------------------------------------------

--
-- Table structure for table `tour_category`
--

CREATE TABLE `tour_category` (
  `id` bigint NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tour_category`
--

INSERT INTO `tour_category` (`id`, `code`, `name`, `note`, `is_active`) VALUES
(1, 'TN', 'Tour trong nước', NULL, 1),
(2, 'QT', 'Tour quốc tế', NULL, 1),
(3, 'REQ', 'Tour theo yêu cầu', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tour_logs`
--

CREATE TABLE `tour_logs` (
  `id` bigint NOT NULL,
  `schedule_id` bigint DEFAULT NULL,
  `booking_id` bigint DEFAULT NULL,
  `author_id` bigint DEFAULT NULL,
  `entry_type` enum('NOTE','INCIDENT','CHECKIN','REQUEST') COLLATE utf8mb4_unicode_ci DEFAULT 'NOTE',
  `content` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tour_schedule`
--

CREATE TABLE `tour_schedule` (
  `id` bigint NOT NULL,
  `tour_id` bigint NOT NULL,
  `depart_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `seats_total` int DEFAULT '0',
  `seats_available` int DEFAULT '0',
  `price_override` decimal(12,2) DEFAULT NULL,
  `status` enum('OPEN','CLOSED','CANCELED','FINISHED') COLLATE utf8mb4_unicode_ci DEFAULT 'OPEN',
  `note` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tour_schedule`
--

INSERT INTO `tour_schedule` (`id`, `tour_id`, `depart_date`, `return_date`, `seats_total`, `seats_available`, `price_override`, `status`, `note`) VALUES
(1, 1, '2025-12-01', '2025-12-03', 20, 20, NULL, 'OPEN', NULL),
(2, 1, '2025-12-04', '2025-12-06', 30, 30, NULL, 'OPEN', NULL),
(3, 2, '2025-12-05', '2025-12-07', 25, 25, NULL, 'OPEN', NULL),
(4, 2, '2025-12-08', '2025-12-10', 35, 35, NULL, 'OPEN', NULL),
(5, 4, '2025-12-10', '2025-12-16', 30, 30, NULL, 'OPEN', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('ADMIN','CUSTOMER','HDV') COLLATE utf8mb4_unicode_ci DEFAULT 'CUSTOMER',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2b$10$Kii2ljR/gbAKvGXxiOzNSO2Nkmtofmcwev9odSgatmMMaXEqMpS3e', 'Administrator', 'admin@example.com', NULL, 'ADMIN', 1, '2025-11-12 04:18:19', '2025-11-12 04:18:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tour_schedule_id` (`tour_schedule_id`);

--
-- Indexes for table `booking_item`
--
ALTER TABLE `booking_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tour_id` (`tour_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tour_category`
--
ALTER TABLE `tour_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `tour_logs`
--
ALTER TABLE `tour_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `tour_schedule`
--
ALTER TABLE `tour_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `booking_item`
--
ALTER TABLE `booking_item`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tour_category`
--
ALTER TABLE `tour_category`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tour_logs`
--
ALTER TABLE `tour_logs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tour_schedule`
--
ALTER TABLE `tour_schedule`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tour_schedule_id`) REFERENCES `tour_schedule` (`id`);

--
-- Constraints for table `booking_item`
--
ALTER TABLE `booking_item`
  ADD CONSTRAINT `booking_item_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `tour_schedule` (`id`);

--
-- Constraints for table `staffs`
--
ALTER TABLE `staffs`
  ADD CONSTRAINT `staffs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tours`
--
ALTER TABLE `tours`
  ADD CONSTRAINT `tours_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `tour_category` (`id`);

--
-- Constraints for table `tour_logs`
--
ALTER TABLE `tour_logs`
  ADD CONSTRAINT `tour_logs_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `tour_schedule` (`id`),
  ADD CONSTRAINT `tour_logs_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `tour_logs_ibfk_3` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tour_schedule`
--
ALTER TABLE `tour_schedule`
  ADD CONSTRAINT `tour_schedule_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
