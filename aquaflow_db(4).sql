-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 10:43 AM
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
-- Database: `aquaflow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action_event` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action_event`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'USER_LOGIN', 'User signed in.', '::1', '2026-10-01 16:51:54'),
(2, 1, 'USER_LOGOUT', 'User signed out.', '::1', '2026-10-01 16:51:59'),
(3, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 03:58:32'),
(4, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 1.', '127.0.0.1', '2026-10-11 03:59:47'),
(5, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 2.', '127.0.0.1', '2026-10-11 04:00:41'),
(6, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 3.', '127.0.0.1', '2026-10-11 04:00:49'),
(7, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 4.', '127.0.0.1', '2026-10-11 04:00:53'),
(8, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 5.', '127.0.0.1', '2026-10-11 04:00:59'),
(9, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 1.', '127.0.0.1', '2026-10-11 04:01:47'),
(10, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 1.', '127.0.0.1', '2026-10-11 04:02:12'),
(11, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 1.', '127.0.0.1', '2026-10-11 04:02:39'),
(12, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 1.', '127.0.0.1', '2026-10-11 04:02:52'),
(13, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 1.', '127.0.0.1', '2026-10-11 04:22:09'),
(14, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 1.', '127.0.0.1', '2026-10-11 04:22:25'),
(15, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 1.', '127.0.0.1', '2026-10-11 04:23:33'),
(16, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 1.', '127.0.0.1', '2026-10-11 04:23:46'),
(17, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 1.', '127.0.0.1', '2026-10-11 04:40:17'),
(18, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 2.', '127.0.0.1', '2026-10-11 04:40:21'),
(19, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 3.', '127.0.0.1', '2026-10-11 04:40:25'),
(20, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 2.', '127.0.0.1', '2026-10-11 04:40:41'),
(21, 1, 'INVENTORY_ADJUSTED', 'Adjusted EMPTY stock for size ID 3.', '127.0.0.1', '2026-10-11 04:40:49'),
(22, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 2.', '127.0.0.1', '2026-10-11 04:41:00'),
(23, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 3.', '127.0.0.1', '2026-10-11 04:41:12'),
(24, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 4.', '127.0.0.1', '2026-10-11 04:41:19'),
(25, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 5.', '127.0.0.1', '2026-10-11 04:41:25'),
(26, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 05:17:47'),
(27, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 05:17:50'),
(28, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 05:18:00'),
(29, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 05:18:09'),
(30, 1, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-11 05:22:49'),
(31, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 05:22:52'),
(32, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 05:32:00'),
(33, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 05:32:02'),
(34, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 05:52:37'),
(35, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 05:52:39'),
(36, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 05:55:05'),
(37, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 05:55:07'),
(38, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:38:40'),
(39, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:45:00'),
(40, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:45:03'),
(41, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:46:53'),
(42, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:46:55'),
(43, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:47:53'),
(44, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:47:55'),
(45, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:50:35'),
(46, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:50:37'),
(47, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:51:32'),
(48, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:51:35'),
(49, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:52:41'),
(50, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:52:44'),
(51, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:53:00'),
(52, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:53:02'),
(53, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 06:54:09'),
(54, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 06:54:12'),
(55, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 07:00:01'),
(56, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 07:00:03'),
(57, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 07:08:14'),
(58, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 07:08:16'),
(59, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 07:08:44'),
(60, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 07:08:46'),
(61, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 07:09:50'),
(62, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 07:09:57'),
(63, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 09:00:58'),
(64, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 09:01:00'),
(65, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 09:05:16'),
(66, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 09:05:19'),
(67, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 09:06:18'),
(68, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 09:06:21'),
(69, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 09:06:46'),
(70, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 09:06:48'),
(71, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 09:07:04'),
(72, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 09:07:06'),
(73, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:15:53'),
(74, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 13:18:00'),
(75, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:18:05'),
(76, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 13:19:12'),
(77, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:19:17'),
(78, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 13:20:40'),
(79, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:20:42'),
(80, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 13:21:38'),
(81, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:22:40'),
(82, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 13:23:43'),
(83, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:23:45'),
(84, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 13:23:55'),
(85, NULL, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:24:00'),
(86, NULL, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-11 13:24:02'),
(87, 1, 'USER_LOGIN', 'User signed in.', '::1', '2026-10-11 13:26:09'),
(88, 1, 'USER_LOGOUT', 'Administrator signed out.', '::1', '2026-10-11 13:26:34'),
(89, 1, 'USER_LOGIN', 'User signed in.', '::1', '2026-10-11 13:26:36'),
(90, 1, 'USER_LOGOUT', 'Administrator signed out.', '::1', '2026-10-11 13:33:24'),
(91, 1, 'USER_LOGIN', 'User signed in.', '::1', '2026-10-11 13:33:25'),
(92, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 13:42:25'),
(93, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 14:36:06'),
(94, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 14:36:08'),
(95, NULL, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-11 15:06:50'),
(96, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 15:06:52'),
(97, 1, 'BATCH_CREATED', 'Processed filling batch AF-20261011172901-DF73.', '127.0.0.1', '2026-10-11 15:29:01'),
(98, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 15:40:23'),
(99, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 15:50:22'),
(100, 4, 'ORDER_PLACED', 'Placed order AF-20261011180445-0BEE44.', '127.0.0.1', '2026-10-11 16:04:45'),
(101, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-11 16:05:15'),
(102, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 16:05:18'),
(103, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-11 16:12:09'),
(104, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 16:12:14'),
(105, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-11 16:14:36'),
(106, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-11 16:14:40'),
(107, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-11 16:29:25'),
(108, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 04:26:29'),
(109, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 05:26:39'),
(110, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 05:26:41'),
(111, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 05:34:47'),
(112, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 05:34:50'),
(113, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 05:49:42'),
(114, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 05:49:44'),
(115, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 05:51:00'),
(116, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 05:51:03'),
(117, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 06:20:02'),
(118, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 06:20:05'),
(119, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 06:20:57'),
(120, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 06:20:59'),
(121, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 06:22:48'),
(122, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 06:22:53'),
(123, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 06:23:05'),
(124, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 06:23:08'),
(125, 1, 'PAYMENT_UPDATED', 'Updated payment ID 1 to PAID.', '127.0.0.1', '2026-10-12 06:54:29'),
(126, 1, 'ORDER_STATUS_UPDATED', 'Updated order ID 1 from PENDING to DELIVERED.', '127.0.0.1', '2026-10-12 06:54:43'),
(127, 1, 'USER_UPDATED', 'Updated user account ID 2.', '127.0.0.1', '2026-10-12 06:55:12'),
(128, 1, 'USER_UPDATED', 'Updated user account ID 2.', '127.0.0.1', '2026-10-12 06:55:21'),
(129, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 1.', '127.0.0.1', '2026-10-12 06:57:27'),
(130, 1, 'INVENTORY_ADJUSTED', 'Adjusted FILLED stock for size ID 1.', '127.0.0.1', '2026-10-12 07:09:04'),
(131, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 07:09:37'),
(132, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 07:09:40'),
(133, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 08:04:29'),
(134, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:04:32'),
(135, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 08:05:06'),
(136, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:05:09'),
(137, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 08:05:20'),
(138, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:05:23'),
(139, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 08:17:46'),
(140, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:17:50'),
(141, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 08:18:02'),
(142, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:24:45'),
(143, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 08:24:57'),
(144, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:25:00'),
(145, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 08:25:29'),
(146, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:25:31'),
(147, 1, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 08:25:36'),
(148, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:27:05'),
(149, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 08:34:40'),
(150, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:34:45'),
(151, 4, 'USER_LOGOUT', 'User signed out.', '127.0.0.1', '2026-10-12 08:36:28'),
(152, 1, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:36:30'),
(153, 1, 'USER_LOGOUT', 'Administrator signed out.', '127.0.0.1', '2026-10-12 08:36:36'),
(154, 4, 'USER_LOGIN', 'User signed in.', '127.0.0.1', '2026-10-12 08:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `bottle_sizes`
--

CREATE TABLE `bottle_sizes` (
  `id` int(10) UNSIGNED NOT NULL,
  `size_name` varchar(20) NOT NULL,
  `volume_liters` decimal(5,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_reusable` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bottle_sizes`
--

INSERT INTO `bottle_sizes` (`id`, `size_name`, `volume_liters`, `description`, `is_reusable`, `is_active`, `created_at`) VALUES
(1, '500ml', 0.50, '500ml drinking water bottle', 0, 1, '2026-09-30 15:56:08'),
(2, '1L', 1.00, '1 liter drinking water bottle', 0, 1, '2026-09-30 15:56:08'),
(3, '1.5L', 1.50, '1.5 liter drinking water bottle', 0, 1, '2026-09-30 15:56:08'),
(4, '5L', 5.00, '5 liter reusable water container', 1, 1, '2026-09-30 15:56:08'),
(5, '19L', 19.00, '19 liter reusable dispenser jar', 1, 1, '2026-09-30 15:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_address` text NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `delivery_address`, `city`, `postal_code`, `created_at`) VALUES
(2, 4, 'Kandy', 'Kandy', '2200', '2026-10-11 15:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `empty_bottle_inventory`
--

CREATE TABLE `empty_bottle_inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bottle_size_id` int(10) UNSIGNED NOT NULL,
  `quantity_available` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `min_stock_threshold` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `empty_bottle_inventory`
--

INSERT INTO `empty_bottle_inventory` (`id`, `bottle_size_id`, `quantity_available`, `min_stock_threshold`, `last_updated`) VALUES
(1, 1, 400, 100, '2026-10-11 15:29:01'),
(2, 2, 500, 100, '2026-10-11 04:40:41'),
(3, 3, 500, 100, '2026-10-11 04:40:49'),
(4, 4, 500, 50, '2026-10-11 04:00:53'),
(5, 5, 500, 50, '2026-10-11 04:00:59');

-- --------------------------------------------------------

--
-- Table structure for table `filled_bottle_inventory`
--

CREATE TABLE `filled_bottle_inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bottle_size_id` int(10) UNSIGNED NOT NULL,
  `quantity_available` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `min_stock_threshold` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `filled_bottle_inventory`
--

INSERT INTO `filled_bottle_inventory` (`id`, `bottle_size_id`, `quantity_available`, `min_stock_threshold`, `last_updated`) VALUES
(1, 1, 600, 100, '2026-10-12 07:09:04'),
(2, 2, 500, 100, '2026-10-11 04:41:00'),
(3, 3, 500, 100, '2026-10-11 04:41:12'),
(4, 4, 500, 50, '2026-10-11 04:41:19'),
(5, 5, 500, 50, '2026-10-11 04:41:25');

-- --------------------------------------------------------

--
-- Table structure for table `filling_batches`
--

CREATE TABLE `filling_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_code` varchar(50) NOT NULL,
  `bottle_size_id` int(10) UNSIGNED NOT NULL,
  `quantity_filled` int(10) UNSIGNED NOT NULL,
  `quantity_damaged` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `filling_date` date NOT NULL,
  `processed_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `filling_batches`
--

INSERT INTO `filling_batches` (`id`, `batch_code`, `bottle_size_id`, `quantity_filled`, `quantity_damaged`, `filling_date`, `processed_by_user_id`, `remarks`, `created_at`) VALUES
(1, 'AF-20261011172901-DF73', 1, 100, 0, '2026-10-11', 1, NULL, '2026-10-11 15:29:01');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_type` enum('EMPTY','FILLED') NOT NULL,
  `bottle_size_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `transaction_type` enum('STOCK_IN','STOCK_OUT','FILLING_DEDUCTION','FILLING_ADDITION','ORDER_DISPATCH','RETURN','ADJUSTMENT') NOT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `processed_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `inventory_type`, `bottle_size_id`, `quantity`, `transaction_type`, `reference_id`, `processed_by_user_id`, `remarks`, `created_at`) VALUES
(24, 'FILLED', 1, 100, 'FILLING_ADDITION', 1, 1, 'Produced by batch AF-20261011172901-DF73', '2026-10-11 15:29:01'),
(25, 'FILLED', 1, 600, 'STOCK_IN', NULL, 1, 'Manual stock adjustment', '2026-10-12 06:57:27'),
(26, 'FILLED', 1, 600, 'STOCK_OUT', NULL, 1, 'Manual stock adjustment', '2026-10-12 07:09:04');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `order_status` enum('PENDING','CONFIRMED','PREPARING','DISPATCHED','DELIVERED','CANCELLED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `payment_status` enum('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `delivery_address` text NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `subtotal`, `delivery_fee`, `total_amount`, `order_status`, `payment_status`, `delivery_address`, `contact_phone`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'AF-20261011180445-0BEE44', 2, 175000.00, 0.00, 175000.00, 'DELIVERED', 'PAID', 'Kandy', '123456789', NULL, '2026-10-11 16:04:45', '2026-10-12 06:54:43');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `line_total`) VALUES
(1, 1, 1, 100, 70.00, 7000.00),
(2, 1, 2, 100, 120.00, 12000.00),
(3, 1, 3, 100, 160.00, 16000.00),
(4, 1, 4, 100, 450.00, 45000.00),
(5, 1, 5, 100, 950.00, 95000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method` enum('COD','BANK_TRANSFER','ONLINE_CARD') NOT NULL DEFAULT 'COD',
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `recorded_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount_paid`, `payment_status`, `transaction_reference`, `recorded_by_user_id`, `paid_at`, `created_at`) VALUES
(1, 1, 'COD', 175000.00, 'PAID', NULL, 4, '2026-10-12 08:54:29', '2026-10-11 16:04:45');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bottle_size_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `sales_unit` enum('SINGLE','PACK','CRATE') NOT NULL DEFAULT 'SINGLE',
  `units_per_pack` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `bottle_size_id`, `product_name`, `selling_price`, `sales_unit`, `units_per_pack`, `image_url`, `is_active`, `created_at`) VALUES
(1, 1, '500ml Drinking Water Bottle', 2100.00, 'PACK', 30, NULL, 1, '2026-09-30 15:56:08'),
(2, 2, '1L Drinking Water Bottle', 2880.00, 'PACK', 24, NULL, 1, '2026-09-30 15:56:08'),
(3, 3, '1.5L Drinking Water Bottle', 3840.00, 'PACK', 24, NULL, 1, '2026-09-30 15:56:08'),
(4, 4, '5L Mineral Water Container', 1800.00, 'PACK', 4, NULL, 1, '2026-09-30 15:56:08'),
(5, 5, '19L Dispenser Jar', 950.00, 'SINGLE', 1, NULL, 1, '2026-09-30 15:56:08'),
(6, 1, '500ml Drinking Water Bottle', 70.00, 'SINGLE', 1, NULL, 1, '2026-10-12 05:48:09'),
(7, 2, '1L Drinking Water Bottle', 120.00, 'SINGLE', 1, NULL, 1, '2026-10-12 05:48:09'),
(8, 3, '1.5L Drinking Water Bottle', 160.00, 'SINGLE', 1, NULL, 1, '2026-10-12 05:48:10'),
(9, 4, '5L Mineral Water Container', 450.00, 'SINGLE', 1, NULL, 1, '2026-10-12 05:48:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('ADMIN','STORE_MANAGER','CUSTOMER') NOT NULL DEFAULT 'CUSTOMER',
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@aquaflow.com', '$2y$10$VNKYJzBHR13nn4vKGU2Nn.z9wM.qY2R6cq535Yp/yHY0jYFmXMhyW', 'System Administrator', NULL, 'ADMIN', 'ACTIVE', '2026-09-30 15:56:08', '2026-10-12 08:23:18'),
(4, 'senadheera', 'kashika@gmail.com', '$2y$10$AOwXREv9qljUjuJwSHX4z.NrT2TuFqNUN1SfKh1vRzDrHhorSLKle', 'Kashika Senadheera', '123456789', 'CUSTOMER', 'ACTIVE', '2026-10-11 15:49:44', '2026-10-11 15:49:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_user` (`user_id`),
  ADD KEY `idx_activity_event` (`action_event`);

--
-- Indexes for table `bottle_sizes`
--
ALTER TABLE `bottle_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_bottle_size_name` (`size_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_user` (`user_id`);

--
-- Indexes for table `empty_bottle_inventory`
--
ALTER TABLE `empty_bottle_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empty_inventory_size` (`bottle_size_id`);

--
-- Indexes for table `filled_bottle_inventory`
--
ALTER TABLE `filled_bottle_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_filled_inventory_size` (`bottle_size_id`);

--
-- Indexes for table `filling_batches`
--
ALTER TABLE `filling_batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_batch_code` (`batch_code`),
  ADD KEY `idx_batch_size` (`bottle_size_id`),
  ADD KEY `idx_batch_user` (`processed_by_user_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction_type` (`inventory_type`,`transaction_type`),
  ADD KEY `idx_transaction_size` (`bottle_size_id`),
  ADD KEY `idx_transaction_user` (`processed_by_user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_order_number` (`order_number`),
  ADD KEY `idx_order_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_item_order` (`order_id`),
  ADD KEY `idx_order_item_product` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_order` (`order_id`),
  ADD KEY `idx_payment_user` (`recorded_by_user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_size` (`bottle_size_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `bottle_sizes`
--
ALTER TABLE `bottle_sizes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `empty_bottle_inventory`
--
ALTER TABLE `empty_bottle_inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `filled_bottle_inventory`
--
ALTER TABLE `filled_bottle_inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `filling_batches`
--
ALTER TABLE `filling_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `empty_bottle_inventory`
--
ALTER TABLE `empty_bottle_inventory`
  ADD CONSTRAINT `fk_empty_inventory_size` FOREIGN KEY (`bottle_size_id`) REFERENCES `bottle_sizes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `filled_bottle_inventory`
--
ALTER TABLE `filled_bottle_inventory`
  ADD CONSTRAINT `fk_filled_inventory_size` FOREIGN KEY (`bottle_size_id`) REFERENCES `bottle_sizes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `filling_batches`
--
ALTER TABLE `filling_batches`
  ADD CONSTRAINT `fk_batch_size` FOREIGN KEY (`bottle_size_id`) REFERENCES `bottle_sizes` (`id`),
  ADD CONSTRAINT `fk_batch_user` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `fk_transaction_size` FOREIGN KEY (`bottle_size_id`) REFERENCES `bottle_sizes` (`id`),
  ADD CONSTRAINT `fk_transaction_user` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_size` FOREIGN KEY (`bottle_size_id`) REFERENCES `bottle_sizes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
