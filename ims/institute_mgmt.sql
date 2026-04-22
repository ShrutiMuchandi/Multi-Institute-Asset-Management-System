-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 11:00 PM
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
-- Database: `institute_mgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `module` varchar(30) DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `inst_id`, `user_id`, `action`, `module`, `record_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-19 00:20:54'),
(2, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-19 00:30:23'),
(3, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-19 00:30:32'),
(4, 0, 1, 'CREATE_INSTITUTE', 'admin', 1, '0', '::1', '2026-03-19 00:48:31'),
(5, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-19 00:49:17'),
(6, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-19 00:58:13'),
(7, 0, 1, 'CREATE_INSTITUTE', 'admin', 2, '0', '::1', '2026-03-19 01:00:24'),
(8, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-19 01:01:25'),
(9, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-19 01:06:39'),
(10, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-19 01:07:49'),
(11, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-19 01:39:11'),
(12, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-19 01:39:19'),
(13, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 20:16:34'),
(14, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 20:19:41'),
(15, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-23 20:20:08'),
(16, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-23 20:54:34'),
(17, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 20:54:44'),
(18, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 20:56:34'),
(19, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-23 20:56:49'),
(20, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-23 20:57:38'),
(21, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-23 21:07:36'),
(22, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-23 21:07:42'),
(23, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 21:08:09'),
(24, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 21:09:20'),
(25, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 21:09:43'),
(26, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 21:09:58'),
(27, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-23 21:10:15'),
(28, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-23 21:10:20'),
(29, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 21:10:29'),
(30, 0, 1, 'CREATE_USER', 'admin', 0, '0', '::1', '2026-03-23 21:14:03'),
(31, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 21:14:28'),
(32, 1, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-23 21:14:41'),
(33, 1, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-23 21:14:58'),
(34, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-23 21:16:10'),
(35, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-23 21:16:20'),
(36, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 21:16:31'),
(37, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 21:21:51'),
(38, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 21:27:31'),
(39, 0, 1, 'CREATE_INSTITUTE', 'admin', 1, '0', '::1', '2026-03-23 21:37:08'),
(40, 0, 1, 'CREATE_USER', 'admin', 0, '0', '::1', '2026-03-23 21:38:31'),
(41, 0, 1, 'CREATE_INSTITUTE', 'admin', 2, '0', '::1', '2026-03-23 21:40:41'),
(42, 0, 1, 'CREATE_USER', 'admin', 0, '0', '::1', '2026-03-23 21:41:47'),
(43, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 21:42:46'),
(44, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-23 21:42:59'),
(45, 2, 5, 'ADD_PROPOSAL', 'proposals', 1, '0', '::1', '2026-03-23 21:45:31'),
(46, 2, 5, 'APPROVE_PROPOSAL', 'proposals', 1, '0', '::1', '2026-03-23 21:47:25'),
(47, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-23 21:47:38'),
(48, 2, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-23 21:48:12'),
(49, 2, 4, 'ADD_BILL', 'bills', 1, '0', '::1', '2026-03-23 23:19:29'),
(50, 2, 4, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-23 23:22:48'),
(51, 2, 4, 'ADD_MOVEMENT', 'movements', 1, '0', '::1', '2026-03-23 23:26:48'),
(52, 2, 4, 'ADD_MOVEMENT', 'movements', 2, '0', '::1', '2026-03-23 23:38:06'),
(53, 2, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-23 23:39:21'),
(54, 1, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-23 23:39:43'),
(55, 1, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-23 23:40:14'),
(56, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-23 23:40:31'),
(57, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-23 23:40:49'),
(58, 2, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-23 23:41:11'),
(59, 2, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-23 23:45:00'),
(60, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-23 23:45:11'),
(61, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-23 23:48:32'),
(62, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-23 23:48:51'),
(63, 2, 5, 'RETURN_MOVEMENT', 'movements', 2, '0', '::1', '2026-03-23 23:56:37'),
(64, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-24 23:26:13'),
(65, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-25 21:09:17'),
(66, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-25 21:13:42'),
(67, 2, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-25 21:13:57'),
(68, 2, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-25 21:14:02'),
(69, 2, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-25 21:14:39'),
(70, 2, 4, 'ADD_PROPOSAL', 'proposals', 2, '0', '::1', '2026-03-25 21:15:46'),
(71, 2, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-25 21:21:46'),
(72, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-25 21:22:36'),
(73, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-25 21:23:02'),
(74, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-25 21:23:11'),
(75, 0, 1, 'CREATE_USER', 'admin', 0, '0', '::1', '2026-03-25 21:25:13'),
(76, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-25 21:25:20'),
(77, 2, 6, 'LOGIN', 'auth', 6, '0', '::1', '2026-03-25 21:25:35'),
(78, 2, 6, 'ADD_PROPOSAL', 'proposals', 3, '0', '::1', '2026-03-25 21:26:59'),
(79, 2, 6, 'LOGOUT', 'auth', 6, '0', '::1', '2026-03-25 21:27:03'),
(80, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-25 21:27:12'),
(81, 2, 5, 'APPROVE_PROPOSAL', 'proposals', 3, '0', '::1', '2026-03-25 21:28:17'),
(82, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-25 21:29:38'),
(83, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-25 21:29:48'),
(84, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-25 23:13:16'),
(85, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-25 23:13:27'),
(86, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-25 23:13:57'),
(87, 1, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-25 23:14:21'),
(88, 1, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-25 23:20:48'),
(89, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-25 23:21:09'),
(90, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-25 23:21:25'),
(91, 2, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-25 23:21:42'),
(92, 2, 4, 'ADD_BILL', 'bills', 2, '0', '::1', '2026-03-25 23:27:09'),
(93, 2, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-26 00:27:59'),
(94, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-26 00:28:08'),
(95, 0, 1, 'CREATE_INSTITUTE', 'admin', 3, '0', '::1', '2026-03-26 00:37:28'),
(96, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-26 00:49:30'),
(97, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-26 00:49:39'),
(98, 0, 1, 'CREATE_INSTITUTE', 'admin', 1, '0', '::1', '2026-03-26 00:58:59'),
(99, 0, 1, 'CREATE_INSTITUTE', 'admin', 2, '0', '::1', '2026-03-26 01:00:04'),
(100, 0, 1, 'CREATE_INSTITUTE', 'admin', 3, '0', '::1', '2026-03-26 01:01:32'),
(101, 0, 1, 'CREATE_USER', 'admin', 0, '0', '::1', '2026-03-26 01:02:55'),
(102, 0, 1, 'CREATE_USER', 'admin', 0, '0', '::1', '2026-03-26 01:03:42'),
(103, 0, 1, 'CREATE_USER', 'admin', 0, '0', '::1', '2026-03-26 01:04:18'),
(104, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-26 01:05:00'),
(105, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-26 01:05:14'),
(106, 2, 3, 'ADD_PROPOSAL', 'proposals', 1, '0', '::1', '2026-03-26 01:08:58'),
(107, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-26 01:09:57'),
(108, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-26 01:11:10'),
(109, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-26 01:11:59'),
(110, 2, 5, 'ADD_PROPOSAL', 'proposals', 1, '0', '::1', '2026-03-26 01:12:55'),
(111, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-26 01:16:04'),
(112, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-26 01:16:36'),
(113, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-26 01:16:45'),
(114, 2, 5, 'ADD_PROPOSAL', 'proposals', 2, '0', '::1', '2026-03-26 01:19:51'),
(115, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-26 01:20:15'),
(116, 3, 6, 'LOGIN', 'auth', 6, '0', '::1', '2026-03-26 01:20:40'),
(117, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-26 01:20:59'),
(118, 3, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-26 01:21:16'),
(119, 3, 6, 'ADD_PROPOSAL', 'proposals', 3, '0', '::1', '2026-03-26 01:23:47'),
(120, 3, 6, 'ADD_PROPOSAL', 'proposals', 4, '0', '::1', '2026-03-26 01:26:12'),
(121, 3, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-26 01:26:59'),
(122, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-26 01:27:09'),
(123, 3, 6, 'LOGOUT', 'auth', 6, '0', '::1', '2026-03-26 01:27:14'),
(124, 1, 7, 'LOGIN', 'auth', 7, '0', '::1', '2026-03-26 01:27:41'),
(125, 1, 7, 'ADD_PROPOSAL', 'proposals', 5, '0', '::1', '2026-03-26 01:28:50'),
(126, 1, 7, 'ADD_PROPOSAL', 'proposals', 6, '0', '::1', '2026-03-26 01:32:03'),
(127, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-26 01:33:07'),
(128, 1, 7, 'LOGOUT', 'auth', 7, '0', '::1', '2026-03-26 01:33:10'),
(129, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-26 01:33:16'),
(130, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-26 19:11:09'),
(131, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 5, '0', '::1', '2026-03-26 19:21:39'),
(132, 1, 7, 'LOGIN', 'auth', 7, '0', '::1', '2026-03-26 19:22:21'),
(133, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-26 19:23:09'),
(134, 1, 2, 'ADD_PROPOSAL', 'proposals', 7, '0', '::1', '2026-03-26 19:24:13'),
(135, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-26 20:03:18'),
(136, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-26 20:03:27'),
(137, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-26 20:03:56'),
(138, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-26 20:04:08'),
(139, 1, 2, 'ADD_BILL', 'bills', 1, '0', '::1', '2026-03-26 20:20:12'),
(140, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-26 20:27:19'),
(141, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-26 20:27:38'),
(142, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-26 20:28:54'),
(143, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-26 20:29:04'),
(144, 1, 2, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-26 20:46:23'),
(145, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-26 20:48:42'),
(146, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-26 20:48:52'),
(147, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 2, '0', '::1', '2026-03-26 20:49:53'),
(148, 1, 7, 'LOGOUT', 'auth', 7, '0', '::1', '2026-03-26 20:50:30'),
(149, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-26 20:51:08'),
(150, 2, 3, 'ADD_BILL', 'bills', 2, '0', '::1', '2026-03-26 20:54:41'),
(151, 2, 3, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-26 21:03:25'),
(152, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 1, '0', '::1', '2026-03-26 21:41:13'),
(153, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-26 23:36:32'),
(154, 3, 6, 'LOGIN', 'auth', 6, '0', '::1', '2026-03-26 23:36:43'),
(155, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-27 00:10:13'),
(156, 3, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-27 00:10:23'),
(157, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-27 00:11:00'),
(158, 3, 6, 'ADD_PROPOSAL', 'proposals', 8, '0', '::1', '2026-03-27 00:21:06'),
(159, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 8, '0', '::1', '2026-03-27 00:26:38'),
(160, 3, 4, 'ADD_BILL', 'bills', 3, '0', '::1', '2026-03-27 01:03:49'),
(161, 3, 4, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-27 01:08:12'),
(162, 0, 1, 'REJECT_PROPOSAL', 'proposals', 6, '0', '::1', '2026-03-27 01:10:04'),
(163, 0, 1, 'CONDEMN_ITEM', 'scrap', 1, '0', '::1', '2026-03-27 01:13:21'),
(164, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-27 19:00:30'),
(165, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-27 19:20:43'),
(166, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-03-27 19:29:09'),
(167, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-27 19:29:47'),
(168, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-27 19:30:35'),
(169, 2, 3, 'ADD_BILL', 'bills', 4, '0', '::1', '2026-03-27 19:34:41'),
(170, 2, 3, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-27 19:37:00'),
(171, 0, 1, 'CREATE_INSTITUTE', 'admin', 4, '0', '::1', '2026-03-27 20:37:36'),
(172, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-27 20:39:03'),
(173, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-27 20:39:36'),
(174, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-27 20:39:46'),
(175, 4, 8, 'LOGIN', 'auth', 8, '0', '::1', '2026-03-27 20:39:57'),
(176, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-27 20:40:17'),
(177, 4, 9, 'LOGIN', 'auth', 9, '0', '::1', '2026-03-27 20:40:28'),
(178, 0, 1, 'CREATE_INSTITUTE', 'admin', 5, '0', '::1', '2026-03-27 20:48:00'),
(179, 4, 8, 'LOGOUT', 'auth', 8, '0', '::1', '2026-03-27 20:48:05'),
(180, 5, 10, 'LOGIN', 'auth', 10, '0', '::1', '2026-03-27 20:48:29'),
(181, 0, 1, 'EDIT_USER', 'admin', 10, '0', '::1', '2026-03-27 21:05:02'),
(182, 0, 1, 'EDIT_USER', 'admin', 10, '0', '::1', '2026-03-27 21:06:03'),
(183, 0, 1, 'EDIT_USER', 'admin', 10, '0', '::1', '2026-03-27 21:11:16'),
(184, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-28 18:44:28'),
(185, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-28 20:24:04'),
(186, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-28 20:24:17'),
(187, 2, 5, 'ADD_PROPOSAL', 'proposals', 9, '0', '::1', '2026-03-28 20:26:24'),
(188, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-28 20:27:17'),
(189, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 9, '0', '::1', '2026-03-28 20:28:09'),
(190, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-28 20:28:44'),
(191, 2, 3, 'ADD_QUOTATION', 'quotations', 9, '0', '::1', '2026-03-28 20:30:30'),
(192, 2, 3, 'ADD_QUOTATION', 'quotations', 9, '0', '::1', '2026-03-28 20:31:33'),
(193, 2, 3, 'ADD_QUOTATION', 'quotations', 9, '0', '::1', '2026-03-28 20:32:10'),
(194, 2, 5, 'SELECT_VENDOR', 'quotations', 2, '0', '::1', '2026-03-28 20:33:17'),
(195, 2, 3, 'RAISE_PO', 'quotations', 9, '0', '::1', '2026-03-28 20:34:07'),
(196, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-28 20:39:04'),
(197, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-28 20:49:08'),
(198, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-28 20:59:46'),
(199, 3, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-28 20:59:59'),
(200, 3, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-03-28 21:00:23'),
(201, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-28 21:00:34'),
(202, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-28 21:08:12'),
(203, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-28 21:09:21'),
(204, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-28 21:22:28'),
(205, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-28 21:22:47'),
(206, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-28 21:23:04'),
(207, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-28 21:24:53'),
(208, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-28 21:25:07'),
(209, 2, 3, 'CONDEMN_ITEM', 'scrap', 1, '0', '::1', '2026-03-28 21:26:08'),
(210, 2, 3, 'ADD_PROPOSAL', 'proposals', 10, '0', '::1', '2026-03-28 21:30:04'),
(211, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 10, '0', '::1', '2026-03-28 21:33:10'),
(212, 2, 3, 'ADD_QUOTATION', 'quotations', 10, '0', '::1', '2026-03-28 21:35:59'),
(213, 2, 3, 'ADD_QUOTATION', 'quotations', 10, '0', '::1', '2026-03-28 21:36:50'),
(214, 2, 5, 'SELECT_VENDOR', 'quotations', 4, '0', '::1', '2026-03-28 21:37:43'),
(215, 2, 3, 'RAISE_PO', 'quotations', 10, '0', '::1', '2026-03-28 21:40:09'),
(216, 2, 3, 'ADD_BILL', 'bills', 5, '0', '::1', '2026-03-28 21:41:52'),
(217, 2, 3, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-28 21:44:04'),
(218, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-03-31 19:24:25'),
(219, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 7, '0', '::1', '2026-03-31 19:26:21'),
(220, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-31 19:27:27'),
(221, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-31 19:27:34'),
(222, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-31 19:27:46'),
(223, 1, 2, 'ADD_QUOTATION', 'quotations', 7, '0', '::1', '2026-03-31 19:28:54'),
(224, 0, 1, 'SELECT_VENDOR', 'quotations', 6, '0', '::1', '2026-03-31 19:29:09'),
(225, 1, 2, 'RAISE_PO', 'quotations', 7, '0', '::1', '2026-03-31 19:29:41'),
(226, 1, 2, 'ADD_BILL', 'bills', 6, '0', '::1', '2026-03-31 19:32:10'),
(227, 1, 2, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-31 19:33:35'),
(228, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 4, '0', '::1', '2026-03-31 19:56:13'),
(229, 1, 7, 'LOGIN', 'auth', 7, '0', '::1', '2026-03-31 20:01:08'),
(230, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-31 20:03:21'),
(231, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-03-31 20:03:30'),
(232, 1, 7, 'LOGOUT', 'auth', 7, '0', '::1', '2026-03-31 20:03:52'),
(233, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-03-31 20:04:02'),
(234, 2, 3, 'ADD_BILL', 'bills', 7, '0', '::1', '2026-03-31 20:11:23'),
(235, 2, 3, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-31 20:12:39'),
(236, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-03-31 20:16:46'),
(237, 1, 2, 'LOGIN', 'auth', 2, '0', '::1', '2026-03-31 20:16:59'),
(238, 1, 2, 'LOGOUT', 'auth', 2, '0', '::1', '2026-03-31 20:17:31'),
(239, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-03-31 20:17:34'),
(240, 1, 7, 'LOGIN', 'auth', 7, '0', '::1', '2026-03-31 20:17:46'),
(241, 1, 7, 'LOGOUT', 'auth', 7, '0', '::1', '2026-03-31 20:17:48'),
(242, 3, 6, 'LOGIN', 'auth', 6, '0', '::1', '2026-03-31 20:18:10'),
(243, 3, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-03-31 20:18:41'),
(244, 3, 4, 'ADD_QUOTATION', 'quotations', 4, '0', '::1', '2026-03-31 20:30:43'),
(245, 3, 4, 'ADD_QUOTATION', 'quotations', 4, '0', '::1', '2026-03-31 20:31:37'),
(246, 3, 6, 'SELECT_VENDOR', 'quotations', 7, '0', '::1', '2026-03-31 20:34:59'),
(247, 3, 4, 'RAISE_PO', 'quotations', 4, '0', '::1', '2026-03-31 20:41:33'),
(248, 3, 4, 'ADD_BILL', 'bills', 8, '0', '::1', '2026-03-31 20:50:21'),
(249, 3, 4, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-03-31 20:52:09'),
(250, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-04-01 19:09:59'),
(251, 0, 1, 'UPLOAD_SIGNATURE', 'profile', 1, '0', '::1', '2026-04-01 19:18:07'),
(252, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-04-01 20:36:36'),
(253, 2, 3, 'ADD_MOVEMENT', 'movements', 1, '0', '::1', '2026-04-01 23:06:32'),
(254, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-04-01 23:16:09'),
(255, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-04-01 23:25:16'),
(256, 3, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-04-01 23:25:25'),
(257, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-04-01 23:31:31'),
(258, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-04-01 23:31:35'),
(259, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-04-01 23:31:53'),
(260, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-04-01 23:32:31'),
(261, 3, 4, 'LOGOUT', 'auth', 4, '0', '::1', '2026-04-01 23:36:49'),
(262, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-04-01 23:37:06'),
(263, 2, 3, 'RETURN_MOVEMENT', 'movements', 1, '0', '::1', '2026-04-01 23:41:41'),
(264, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-04-02 00:13:01'),
(265, 0, 1, 'UPLOAD_SIGNATURE', 'profile', 1, '0', '::1', '2026-04-02 00:13:14'),
(266, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-04-02 00:42:42'),
(267, 3, 4, 'LOGIN', 'auth', 4, '0', '::1', '2026-04-02 00:43:02'),
(268, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-04-02 19:17:00'),
(269, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-04-02 19:29:38'),
(270, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-04-02 20:07:02'),
(271, 0, 1, 'CONDEMN_ITEM', 'scrap', 2, '0', '::1', '2026-04-02 20:18:43'),
(272, 0, 1, 'LOGOUT', 'auth', 1, '0', '::1', '2026-04-02 20:24:33'),
(273, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-04-02 20:24:52'),
(274, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-04-02 21:29:43'),
(275, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-04-02 21:33:37'),
(276, 2, 5, 'ADD_PROPOSAL', 'proposals', 11, '0', '::1', '2026-04-02 21:36:43'),
(277, 0, 1, 'APPROVE_PROPOSAL', 'proposals', 11, '0', '::1', '2026-04-02 21:37:18'),
(278, 2, 3, 'ADD_QUOTATION', 'quotations', 11, '0', '::1', '2026-04-02 21:39:45'),
(279, 2, 3, 'ADD_QUOTATION', 'quotations', 11, '0', '::1', '2026-04-02 21:40:15'),
(280, 2, 5, 'SELECT_VENDOR', 'quotations', 10, '0', '::1', '2026-04-02 21:41:07'),
(281, 2, 3, 'RAISE_PO', 'quotations', 11, '0', '::1', '2026-04-02 21:42:41'),
(282, 2, 3, 'ADD_BILL', 'bills', 9, '0', '::1', '2026-04-02 21:45:14'),
(283, 2, 3, 'ADD_STOCK', 'stock', 0, '0', '::1', '2026-04-02 21:47:14'),
(284, 2, 3, 'ADD_MOVEMENT', 'movements', 2, '0', '::1', '2026-04-02 21:53:01'),
(285, 2, 3, 'RETURN_MOVEMENT', 'movements', 2, '0', '::1', '2026-04-02 21:54:28'),
(286, 0, 1, 'LOGIN', 'auth', 1, '0', '::1', '2026-04-06 18:54:29'),
(287, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-04-06 18:54:51'),
(288, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-04-06 18:55:08'),
(289, 2, 3, 'CHANGE_PASSWORD', 'profile', 3, '0', '::1', '2026-04-06 18:56:19'),
(290, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-04-06 18:56:23'),
(291, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-04-06 18:56:41'),
(292, 2, 3, 'CHANGE_PASSWORD', 'profile', 3, '0', '::1', '2026-04-06 18:57:09'),
(293, 2, 5, 'LOGOUT', 'auth', 5, '0', '::1', '2026-04-06 20:07:37'),
(294, 2, 5, 'LOGIN', 'auth', 5, '0', '::1', '2026-04-06 20:07:49'),
(295, 2, 3, 'LOGOUT', 'auth', 3, '0', '::1', '2026-04-06 20:21:06'),
(296, 2, 3, 'LOGIN', 'auth', 3, '0', '::1', '2026-04-06 20:21:26');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED NOT NULL,
  `fy_id` tinyint(3) UNSIGNED NOT NULL,
  `bill_no` varchar(40) NOT NULL,
  `internal_ref` varchar(25) NOT NULL,
  `bill_date` date NOT NULL,
  `receipt_date` date DEFAULT NULL,
  `vendor_name` varchar(150) NOT NULL,
  `vendor_gstin` varchar(20) DEFAULT NULL,
  `vendor_address` text DEFAULT NULL,
  `description` text NOT NULL,
  `cat_id` smallint(5) UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `gst_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) GENERATED ALWAYS AS (`amount` + `gst_amount`) STORED,
  `pay_mode` enum('Cash','Cheque','NEFT/RTGS','DD','UPI','Credit Card','Other') DEFAULT 'Cheque',
  `pay_ref_no` varchar(60) DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `proposal_id` int(10) UNSIGNED DEFAULT NULL,
  `entered_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_by` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `inst_id`, `fy_id`, `bill_no`, `internal_ref`, `bill_date`, `receipt_date`, `vendor_name`, `vendor_gstin`, `vendor_address`, `description`, `cat_id`, `amount`, `gst_amount`, `pay_mode`, `pay_ref_no`, `pay_date`, `proposal_id`, `entered_by`, `verified_by`, `remarks`, `created_at`) VALUES
(1, 1, 3, 'INV/2026/001245', 'BILL-SSEMS-2026-001', '2026-03-27', '2026-03-27', 'Shree Ganesh', '29ABCDE1234F1Z5', '', 'Microscope', 3, 5000.00, 500.00, 'Cheque', 'CHQ123456', '2026-03-27', 5, 2, 'Chairman', '', '2026-03-26 20:20:12'),
(2, 2, 3, 'INV/2026/001233', 'BILL-RNSP-2026-001', '2026-03-28', '2026-03-28', 'Ram Furnitures', '29OKCDE1234F1O9', '', 'Chairs', 2, 10000.00, 1000.00, 'Cash', '', '2026-03-28', 2, 3, 'Chairman', '', '2026-03-26 20:54:41'),
(3, 3, 3, 'INV/2026/007833', 'BILL-SGBIT-2026-001', '2026-03-30', '2026-03-30', 'Gada Electronics', '29OKCDE4532F187', '', 'Printer', 1, 5000.00, 100.00, 'Cash', '', '2026-04-01', 8, 4, 'Chairman', '', '2026-03-27 01:03:49'),
(4, 2, 3, 'INV/2026/007765', 'BILL-RNSP-2026-002', '2026-03-28', '2026-03-28', 'Gada Electronics', '29OKCDE6522F180', '', 'Computers', 1, 500000.00, 1000.00, 'Cheque', 'CHQ19528', '2026-04-01', 1, 3, 'Chairman', '', '2026-03-27 19:34:41'),
(5, 2, 3, 'INV/2026/007789', 'BILL-RNSP-2026-003', '2026-03-29', '2026-03-29', 'Gada Electronics', '29OKCDE6522F180', '', 'Pendrives', 1, 999.00, 20.00, 'Cash', '', '2026-04-01', 10, 3, 'Trust', '', '2026-03-28 21:41:52'),
(6, 1, 3, 'INV/2026/456219', 'BILL-SSEMS-2026-002', '2026-03-31', '2026-03-31', 'Damro', '29OKCDE8743F187', '', 'Tables', 2, 3500.00, 0.00, 'Cash', '', '2026-04-01', 7, 2, 'Trust', '', '2026-03-31 19:32:10'),
(7, 2, 3, 'INV/2026/458729', 'BILL-RNSP-2026-004', '2026-04-01', '2026-04-01', 'Gada Electronics', '29OKCDE8743F678', '', 'CPU', 1, 2599.00, 400.00, 'Cash', '', '2026-04-01', 9, 3, 'Trust', '', '2026-03-31 20:11:23'),
(8, 3, 3, 'INV/2026/974353', 'BILL-SGBIT-2026-002', '2026-04-01', '2026-04-01', 'Gandharva Electricals', '29OKCDE5629F042', '', 'Fan', 8, 1400.00, 100.00, 'Cash', '', '2026-04-01', 4, 4, 'Trust', '', '2026-03-31 20:50:21'),
(9, 2, 3, 'INV/2026/87657845', 'BILL-RNSP-2026-005', '2026-04-03', '2026-04-03', 'Jeswal', '56uuuu777uuuu', '', 'Tables', 2, 1500.00, 50.00, 'Cheque', 'CHEQ-1234', '2026-04-03', 11, 3, 'Principal', '', '2026-04-02 21:45:14');

-- --------------------------------------------------------

--
-- Table structure for table `financial_years`
--

CREATE TABLE `financial_years` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `fy_label` varchar(10) NOT NULL,
  `start_dt` date NOT NULL,
  `end_dt` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_years`
--

INSERT INTO `financial_years` (`id`, `fy_label`, `start_dt`, `end_dt`, `is_active`) VALUES
(1, '2023-24', '2023-04-01', '2024-03-31', 0),
(2, '2024-25', '2024-04-01', '2025-03-31', 0),
(3, '2025-26', '2025-04-01', '2026-03-31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `institutes`
--

CREATE TABLE `institutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `type_id` tinyint(3) UNSIGNED NOT NULL,
  `inst_code` varchar(12) NOT NULL,
  `inst_name` varchar(120) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  `district` varchar(60) DEFAULT NULL,
  `state` varchar(60) DEFAULT 'Karnataka',
  `pincode` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `principal` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institutes`
--

INSERT INTO `institutes` (`id`, `type_id`, `inst_code`, `inst_name`, `address`, `city`, `district`, `state`, `pincode`, `phone`, `email`, `principal`, `is_active`, `created_at`) VALUES
(1, 1, 'SSEMS', 'Shri Siddharameshwar English Medium School', 'Shivabasava Nagar, Belguam', 'Belgaum', 'Belgaum', 'Karnataka', '590016', '1234567890', 'ssems@gmail.com', 'Mrs. Lata H.', 1, '2026-03-26 00:58:59'),
(2, 5, 'RNSP', 'R. N. Shetty Polytechnic, Belgaum', 'Shivabasava nagar,  Belgaum', 'Belgaum', 'Belgaum', 'Karnataka', '590016', '+918231602068', 'rnsp@gmail.com', 'Mr. Girish P.', 1, '2026-03-26 01:00:03'),
(3, 2, 'SGBIT', 'S. G. Balekundri Institution of Technology, Belgaum', 'Shivabasava nagar,  Belgaum', 'Belgaum', 'Belgaum', 'Karnataka', '590016', '8975843657', 'sgbit@gamil.com', 'Mr. B. R. Patagundi', 1, '2026-03-26 01:01:31'),
(4, 3, 'SSPU', 'Shri Siddharameshwar PU College', 'Shivabasava Nagar Belgaum', 'Belguam', 'Belguam', 'Karnataka', '590016', '1234567890', 'sspu@gmail.com', 'Mrs. Jaya  Patil', 1, '2026-03-27 20:37:36');

-- --------------------------------------------------------

--
-- Table structure for table `institute_types`
--

CREATE TABLE `institute_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `type_name` varchar(60) NOT NULL,
  `type_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institute_types`
--

INSERT INTO `institute_types` (`id`, `type_name`, `type_code`) VALUES
(1, 'School', 'SCH'),
(2, 'Degree College', 'COL'),
(3, 'Pre University College', 'PUC'),
(4, 'ITI', 'ITI'),
(5, 'Polytechnic', 'PLY'),
(6, 'Other', 'OTH');

-- --------------------------------------------------------

--
-- Table structure for table `item_categories`
--

CREATE TABLE `item_categories` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `cat_name` varchar(80) NOT NULL,
  `cat_code` varchar(6) NOT NULL,
  `type` enum('asset','consumable','both') DEFAULT 'both'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item_categories`
--

INSERT INTO `item_categories` (`id`, `cat_name`, `cat_code`, `type`) VALUES
(1, 'IT / Electronics', 'IT', 'asset'),
(2, 'Furniture', 'FU', 'asset'),
(3, 'Laboratory Equipment', 'LA', 'asset'),
(4, 'Library Books', 'LB', 'asset'),
(5, 'Sports Equipment', 'SP', 'asset'),
(6, 'Office Equipment', 'OE', 'asset'),
(7, 'Vehicle', 'VH', 'asset'),
(8, 'Electrical Fittings', 'EL', 'asset'),
(9, 'Civil / Infrastructure', 'CI', 'asset'),
(10, 'Stationery', 'ST', 'consumable'),
(11, 'Consumables / Chemicals', 'CO', 'consumable'),
(12, 'Tea / Refreshments', 'TR', 'consumable'),
(13, 'Maintenance', 'MN', 'consumable'),
(14, 'Other', 'OT', 'both');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED NOT NULL,
  `location_name` varchar(120) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `inst_id`, `location_name`, `created_at`) VALUES
(1, 2, 'Staff Room (G4)', '2026-04-01 23:05:36'),
(3, 2, 'Class no. 6', '2026-04-02 21:50:40');

-- --------------------------------------------------------

--
-- Table structure for table `movements`
--

CREATE TABLE `movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED NOT NULL,
  `ref_no` varchar(25) NOT NULL,
  `move_date` date NOT NULL,
  `move_type` enum('Issue','Transfer','Return','Send for Repair','Return from Repair','Temporary Issue') DEFAULT 'Issue',
  `stock_id` int(10) UNSIGNED NOT NULL,
  `qty_moved` decimal(10,2) DEFAULT 1.00,
  `from_loc` varchar(120) DEFAULT NULL,
  `to_loc` varchar(120) DEFAULT NULL,
  `issued_to` varchar(150) DEFAULT NULL,
  `issued_by` varchar(100) DEFAULT NULL,
  `expected_return` date DEFAULT NULL,
  `actual_return` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Issued',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movements`
--

INSERT INTO `movements` (`id`, `inst_id`, `ref_no`, `move_date`, `move_type`, `stock_id`, `qty_moved`, `from_loc`, `to_loc`, `issued_to`, `issued_by`, `expected_return`, `actual_return`, `remarks`, `status`, `created_by`, `created_at`) VALUES
(1, 2, 'MOV-RNSP-2026-001', '2026-04-02', 'Transfer', 5, 1.00, 'Staff Room (G4)', 'SGBIT', 'Mr. Mantesh, Instructor', 'HOD', '2026-04-10', '2026-04-01', '', 'Returned', 3, '2026-04-01 23:06:32'),
(2, 2, 'MOV-RNSP-2026-002', '2026-04-03', 'Issue', 2, 1.00, 'Class no. 6', 'SSPU', 'Mrs. jaya Patil, Principal', 'Ram, Pean', '2026-04-15', '2026-04-02', '', 'Returned', 3, '2026-04-02 21:53:01');

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED NOT NULL,
  `fy_id` tinyint(3) UNSIGNED NOT NULL,
  `proposal_no` varchar(25) NOT NULL,
  `proposal_date` date NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `item_name` varchar(200) NOT NULL,
  `cat_id` smallint(5) UNSIGNED DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT 'Nos',
  `est_amount` decimal(12,2) NOT NULL,
  `justification` text DEFAULT NULL,
  `raised_by` varchar(100) DEFAULT NULL,
  `designation` varchar(80) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Purchased') DEFAULT 'Pending',
  `quotation_status` enum('Not Started','Quotes Collected','Vendor Selected','PO Raised') DEFAULT 'Not Started',
  `approved_by` varchar(100) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `approval_note` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `inst_id`, `fy_id`, `proposal_no`, `proposal_date`, `department`, `item_name`, `cat_id`, `quantity`, `unit`, `est_amount`, `justification`, `raised_by`, `designation`, `status`, `quotation_status`, `approved_by`, `approved_date`, `approval_note`, `created_by`, `created_at`) VALUES
(1, 2, 3, 'PROP-RNSP-2026-001', '2026-03-26', 'CS', 'Computers', 1, 5.00, 'Nos', 500000.00, 'Required for CS lab', 'HOD, CS', 'HOD', 'Purchased', 'Not Started', 'Super Administrator', '2026-03-26', '', 5, '2026-03-26 01:12:55'),
(2, 2, 3, 'PROP-RNSP-2026-002', '2026-03-26', 'EC', 'Chairs', 2, 5.00, 'Nos', 10000.00, 'For EC Staff Room', 'HOD, EC', 'HOD', 'Purchased', 'Not Started', 'Super Administrator', '2026-03-26', '', 5, '2026-03-26 01:19:51'),
(3, 3, 3, 'PROP-SGBIT-2026-001', '2026-03-26', 'Library', 'Books', 4, 10.00, 'Nos', 500.00, 'Required as Syllabus Changed', 'Librarian', 'HOD', 'Pending', 'Not Started', NULL, NULL, NULL, 6, '2026-03-26 01:23:47'),
(4, 3, 3, 'PROP-SGBIT-2026-002', '2026-03-26', 'ME', 'Fan', 8, 1.00, 'Nos', 2000.00, 'Required', 'HOD, ME', 'HOD', 'Purchased', 'PO Raised', 'Super Administrator', '2026-03-31', '', 6, '2026-03-26 01:26:12'),
(5, 1, 3, 'PROP-SSEMS-2026-001', '2026-03-26', 'Laboratory', 'Microscope', 3, 1.00, 'Nos', 5000.00, 'Required for experiment purpose', 'Lab Teacher', 'Teacher', 'Purchased', 'Not Started', 'Super Administrator', '2026-03-26', '', 7, '2026-03-26 01:28:50'),
(6, 1, 3, 'PROP-SSEMS-2026-002', '2026-03-26', 'Primary Staff Room', 'Pens Set', 10, 10.00, 'Set', 100.00, 'Required', 'Teacher', 'Teacher', 'Rejected', 'Not Started', 'Super Administrator', '2026-03-26', '', 7, '2026-03-26 01:32:03'),
(7, 1, 3, 'PROP-SSEMS-2026-003', '2026-03-27', 'Office', 'Table', 2, 1.00, 'Nos', 4000.00, 'for placing computers', 'Office Staff', 'Office Superintendent', 'Purchased', 'PO Raised', 'Super Administrator', '2026-03-31', '', 2, '2026-03-26 19:24:13'),
(8, 3, 3, 'PROP-SGBIT-2026-003', '2026-03-30', 'ME', 'Printer', 1, 1.00, 'Nos', 5000.00, 'Required', 'HOD, ME', 'HOD', 'Purchased', 'Not Started', 'Super Administrator', '2026-03-26', 'approved', 6, '2026-03-27 00:21:06'),
(9, 2, 3, 'PROP-RNSP-2026-003', '2026-03-29', 'EE', 'CPU', 1, 1.00, 'Nos', 3000.00, '', 'HOD, EE', 'HOD', 'Purchased', 'PO Raised', 'Super Administrator', '2026-03-28', '', 5, '2026-03-28 20:26:24'),
(10, 2, 3, 'PROP-RNSP-2026-004', '2026-03-29', 'CS', 'Pendrives', 1, 2.00, 'Nos', 1000.00, '', 'hod', 'HOD', 'Purchased', 'PO Raised', 'Super Administrator', '2026-03-28', '', 3, '2026-03-28 21:30:04'),
(11, 2, 3, 'PROP-RNSP-2026-005', '2026-04-03', 'Library', 'Tables', 2, 2.00, 'Nos', 2000.00, '', 'Nelima', 'HOD', 'Purchased', 'PO Raised', 'Super Administrator', '2026-04-02', '', 5, '2026-04-02 21:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED NOT NULL,
  `proposal_id` int(10) UNSIGNED NOT NULL,
  `vendor_name` varchar(150) NOT NULL,
  `vendor_contact` varchar(100) DEFAULT NULL,
  `vendor_address` text DEFAULT NULL,
  `vendor_gstin` varchar(20) DEFAULT NULL,
  `quoted_amount` decimal(12,2) NOT NULL,
  `delivery_days` int(11) DEFAULT 0,
  `validity_days` int(11) DEFAULT 30,
  `remarks` text DEFAULT NULL,
  `is_selected` tinyint(1) DEFAULT 0,
  `selected_by` varchar(100) DEFAULT NULL,
  `selected_at` datetime DEFAULT NULL,
  `added_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `inst_id`, `proposal_id`, `vendor_name`, `vendor_contact`, `vendor_address`, `vendor_gstin`, `quoted_amount`, `delivery_days`, `validity_days`, `remarks`, `is_selected`, `selected_by`, `selected_at`, `added_by`, `created_at`) VALUES
(1, 2, 9, 'Raj Electronics', '1223343545', 'Shahu Nagar', '29OKCDE1234F1O5', 2800.00, 3, 30, '', 0, NULL, NULL, 3, '2026-03-28 20:30:30'),
(2, 2, 9, 'Gada Electronics', '576876887656568', 'Patel nagar', '29OKCDE4532F187', 2999.00, 2, 15, '', 1, 'Mr. Girish P.', '2026-03-28 13:33:17', 3, '2026-03-28 20:31:33'),
(3, 2, 9, 'Savi Electronics', '356748567835678543', 'Ram Nagar', '29OKCDE6782F187', 3000.00, 4, 30, '', 0, NULL, NULL, 3, '2026-03-28 20:32:10'),
(4, 2, 10, 'Gada Electronics', '1223343545', 'Shahu Nagar', '29OKCDE1234F1O5', 999.00, 3, 30, '', 1, 'Mr. Girish P.', '2026-03-28 14:37:43', 3, '2026-03-28 21:35:59'),
(5, 2, 10, 'Sheva Electronics', '675756767', 'Patel nagar', '29OKCDE1234F1O5', 1000.00, 3, 30, '', 0, NULL, NULL, 3, '2026-03-28 21:36:50'),
(6, 1, 7, 'Damro', '67885677', 'Ajam Nagar', '29OKCDE8743F187', 3500.00, 2, 30, '', 1, 'Super Administrator', '2026-03-31 12:29:09', 2, '2026-03-31 19:28:54'),
(7, 3, 4, 'Gandharva Electricals', '75767546546', 'kirloskar road', '29OKCDE6546F189', 1500.00, 2, 30, '', 1, 'Mr. B. R. Patagundi', '2026-03-31 13:34:59', 4, '2026-03-31 20:30:43'),
(8, 3, 4, 'Havel Fans', '576735454', 'Saraf Nagar', '29OKCDE9834F742', 1800.00, 1, 30, '', 0, NULL, NULL, 4, '2026-03-31 20:31:37'),
(9, 2, 11, 'Heeralal', '565756767', 'Shivabasava Nagar', '56uuuu777uuuu', 1900.00, 3, 30, '', 0, NULL, NULL, 3, '2026-04-02 21:39:45'),
(10, 2, 11, 'Jeswal', 'r556567', 'raj nagar', '56uuu6565757', 1500.00, 45, 30, '', 1, 'Mr. Girish P.', '2026-04-02 14:41:07', 3, '2026-04-02 21:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `scrap`
--

CREATE TABLE `scrap` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED NOT NULL,
  `scrap_no` varchar(25) NOT NULL,
  `scrap_date` date NOT NULL,
  `stock_id` int(10) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `condemned_by` varchar(100) DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `disposal` enum('Auction','Write Off','Donation','Return to Supplier','Govt Disposal','Destroyed','Other') DEFAULT 'Write Off',
  `realised_value` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scrap`
--

INSERT INTO `scrap` (`id`, `inst_id`, `scrap_no`, `scrap_date`, `stock_id`, `reason`, `condemned_by`, `approved_by`, `disposal`, `realised_value`, `remarks`, `created_by`, `created_at`) VALUES
(1, 2, 'SCR-RNSP-2026-001', '2026-03-29', 6, 'Broken', 'Peon', '0', 'Write Off', 500.00, '', 3, '2026-03-28 21:26:08'),
(2, 1, 'SCR-SSEMS-2026-001', '2026-04-03', 5, 'broken', 'Mohan, Instructor', 'Principal', 'Donation', 0.00, '', 1, '2026-04-02 20:18:43');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED NOT NULL,
  `fy_id` tinyint(3) UNSIGNED NOT NULL,
  `asset_tag` varchar(30) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `cat_id` smallint(5) UNSIGNED DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT 1.00,
  `unit` varchar(20) DEFAULT 'Nos',
  `bill_id` int(10) UNSIGNED DEFAULT NULL,
  `receipt_date` date NOT NULL,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `location` varchar(120) DEFAULT NULL,
  `dept` varchar(100) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `make_model` varchar(150) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Active','In Use','Under Repair','Condemned') DEFAULT 'Active',
  `added_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `inst_id`, `fy_id`, `asset_tag`, `item_name`, `cat_id`, `quantity`, `unit`, `bill_id`, `receipt_date`, `unit_cost`, `location`, `dept`, `supplier`, `make_model`, `serial_no`, `warranty_end`, `remarks`, `status`, `added_by`, `created_at`) VALUES
(1, 1, 3, 'SSEMS-LA-2026-001', 'Microscope', 3, 1.00, 'Nos', 1, '2026-03-27', 5500.00, 'Laboratory', 'High School Block', 'Shree Ganesh', 'LT-Compound Microscope X200', 'MIC2026-001', '2027-03-27', 'Item received and verified, in working condition', 'Active', 2, '2026-03-26 20:46:23'),
(2, 2, 3, 'RNSP-FU-2026-001', 'Chairs', 2, 1.00, 'Nos', 2, '2026-03-28', 2200.00, 'Class no. 6', 'EC', 'Ram Furnitures', 'PlyWood', '', '2028-03-28', 'Item received and checked, in Good condition', 'Active', 3, '2026-03-26 21:03:25'),
(3, 2, 3, 'RNSP-FU-2026-002', 'Chairs', 2, 1.00, 'Nos', 2, '2026-03-28', 2200.00, 'EC Staff Room', 'EC', 'Ram Furnitures', 'PlyWood', '', '2028-03-28', 'Item received and checked, in Good condition', 'Active', 3, '2026-03-26 21:03:25'),
(4, 2, 3, 'RNSP-FU-2026-003', 'Chairs', 2, 1.00, 'Nos', 2, '2026-03-28', 2200.00, 'EC Staff Room', 'EC', 'Ram Furnitures', 'PlyWood', '', '2028-03-28', 'Item received and checked, in Good condition', 'Active', 3, '2026-03-26 21:03:25'),
(5, 2, 3, 'RNSP-FU-2026-004', 'Chairs', 2, 1.00, 'Nos', 2, '2026-03-28', 2200.00, 'Staff Room (G4)', 'EC', 'Ram Furnitures', 'PlyWood', '', '2028-03-28', 'Item received and checked, in Good condition', 'Condemned', 3, '2026-03-26 21:03:25'),
(6, 2, 3, 'RNSP-FU-2026-005', 'Chairs', 2, 1.00, 'Nos', 2, '2026-03-28', 2200.00, 'EC Staff Room', 'EC', 'Ram Furnitures', 'PlyWood', '', '2028-03-28', 'Item received and checked, in Good condition', 'Condemned', 3, '2026-03-26 21:03:25'),
(7, 3, 3, 'SGBIT-IT-2026-001', 'Printer', 1, 1.00, 'Nos', 3, '2026-03-30', 5100.00, 'Machine lab 1', 'ME', 'HP', 'Laser Jet Pro MFP 4101dw', 'MIC2026-764', '2030-03-30', 'working', 'Active', 4, '2026-03-27 01:08:12'),
(8, 2, 3, 'RNSP-IT-2026-001', 'Computers', 1, 1.00, 'Nos', 4, '2026-03-28', 10000.00, 'CS lab 4', 'CS', 'HP / Gada Electronics', 'Apex 452', '', '2029-10-27', '', 'Active', 3, '2026-03-27 19:37:00'),
(9, 2, 3, 'RNSP-IT-2026-002', 'Computers', 1, 1.00, 'Nos', 4, '2026-03-28', 10000.00, 'CS lab 4', 'CS', 'HP / Gada Electronics', 'Apex 452', '', '2029-10-27', '', 'Active', 3, '2026-03-27 19:37:00'),
(10, 2, 3, 'RNSP-IT-2026-003', 'Computers', 1, 1.00, 'Nos', 4, '2026-03-28', 10000.00, 'CS lab 4', 'CS', 'HP / Gada Electronics', 'Apex 452', '', '2029-10-27', '', 'Active', 3, '2026-03-27 19:37:00'),
(11, 2, 3, 'RNSP-IT-2026-004', 'Computers', 1, 1.00, 'Nos', 4, '2026-03-28', 10000.00, 'CS lab 4', 'CS', 'HP / Gada Electronics', 'Apex 452', '', '2029-10-27', '', 'Active', 3, '2026-03-27 19:37:00'),
(12, 2, 3, 'RNSP-IT-2026-005', 'Computers', 1, 1.00, 'Nos', 4, '2026-03-28', 10000.00, 'CS lab 4', 'CS', 'HP / Gada Electronics', 'Apex 452', '', '2029-10-27', '', 'Active', 3, '2026-03-27 19:37:00'),
(13, 2, 3, 'RNSP-IT-2026-006', 'pendrive', 1, 1.00, 'Nos', 5, '2026-03-29', 509.50, 'HOD Room', 'CS', 'HP', 'optel 6784', '', '2028-06-28', '', 'Active', 3, '2026-03-28 21:44:04'),
(14, 2, 3, 'RNSP-IT-2026-007', 'pendrive', 1, 1.00, 'Nos', 5, '2026-03-29', 509.50, 'HOD Room', 'CS', 'HP', 'optel 6784', '', '2028-06-28', '', 'Active', 3, '2026-03-28 21:44:04'),
(15, 1, 3, 'SSEMS-FU-2026-001', 'Table', 2, 1.00, 'Nos', 6, '2026-04-01', 3500.00, 'Office room', 'office', '', '', '', '2030-06-04', '', 'Active', 2, '2026-03-31 19:33:35'),
(16, 2, 3, 'RNSP-IT-2026-008', 'CPU', 1, 1.00, 'Nos', 7, '2026-04-01', 2999.00, 'EC dept room 08', 'EC', 'Dell', 'optel 6i789k', 'MIC2026-987654', '2029-03-31', '', 'Active', 3, '2026-03-31 20:12:39'),
(17, 3, 3, 'SGBIT-EL-2026-001', 'Fan', 8, 1.00, 'Nos', 8, '2026-04-01', 1500.00, 'Staff Room 03', 'ME', 'Crompton', '345 base', 'MIC2026-8343785', '2028-03-31', '', 'Active', 4, '2026-03-31 20:52:09'),
(18, 2, 3, 'RNSP-FU-2026-006', 'Tables', 2, 1.00, 'Nos', 9, '2026-04-03', 775.00, 'Office 3', 'Office', '', '', '', '2027-02-03', '', 'Active', 3, '2026-04-02 21:47:14'),
(19, 2, 3, 'RNSP-FU-2026-007', 'Tables', 2, 1.00, 'Nos', 9, '2026-04-03', 775.00, 'Office 3', 'Office', '', '', '', '2027-02-03', '', 'Active', 3, '2026-04-02 21:47:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `inst_id` int(10) UNSIGNED DEFAULT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','principal','office_staff','teacher','auditor') DEFAULT 'office_staff',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `signature` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `inst_id`, `username`, `password`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `signature`) VALUES
(1, NULL, 'superadmin', '$2y$10$c2Pjlw7SKGwAsSm2Fl1LLeozubbvdC5vtIYh4gawGUV6aNBK1CtAC', 'Super Administrator', NULL, 'superadmin', 1, '2026-04-06 11:54:29', '2026-03-18 23:55:46', 'sig_1_1775088794.jpg'),
(2, 1, 'ssems_office', '$2y$10$TwkoUryW7G3C2LUmnH6nqe8imDF9CsuSXDSk/CxD9ccK1PS/vwPti', 'Office Staff', 'ssems@gmail.com', 'office_staff', 1, '2026-03-31 13:16:59', '2026-03-26 00:58:59', NULL),
(3, 2, 'rnsp_office', '$2y$10$t5uofcD3/cjJDiwqRJBhsugweer3L24l4orvx3FkrCdrrvC3Ce9hC', 'Office Staff', 'rnsp@gmail.com', 'office_staff', 1, '2026-04-06 13:21:26', '2026-03-26 01:00:04', NULL),
(4, 3, 'sgbit_office', '$2y$10$4ao8AHB069z/f.5hHLUlXOY9lLXveHGZ7vnakzQgGkPMc.NhtYy4u', 'Office Staff', 'sgbit@gamil.com', 'office_staff', 1, '2026-04-01 17:43:02', '2026-03-26 01:01:32', NULL),
(5, 2, 'rnsp_principal', '$2y$10$51J250ux6RsIsvAcwHdi9OwqUf93WxERC6pEg34NG.UkxgxlvAR7e', 'Mr. Girish P.', 'rnsp@gmail.com', 'principal', 1, '2026-04-06 13:07:49', '2026-03-26 01:02:55', NULL),
(6, 3, 'sgbit_principal', '$2y$10$njYg7..C5sro61AJKBFlFOafCXhs.VRz5BTnxmjUh.IvqE0VRa.WG', 'Mr. B. R. Patagundi', 'sgbit@gamil.com', 'principal', 1, '2026-03-31 13:18:10', '2026-03-26 01:03:42', NULL),
(7, 1, 'ssems_principal', '$2y$10$v9Ol13AbxtPhnMnaodIMw.8WlenemjwdQuoqEj2F95Q1AczxidWAe', 'Mrs. Lata H.', 'ssems@gmail.com', 'principal', 1, '2026-03-31 13:17:46', '2026-03-26 01:04:18', NULL),
(8, 4, 'sspu_office', '$2y$10$InyDtLaVPlHEMnbbOv.iQO4psc29vljW3FFUkNlOaIoHl8ubCLfVq', 'Office Staff', 'sspu@gmail.com', 'office_staff', 1, '2026-03-27 13:39:57', '2026-03-27 20:37:36', NULL),
(9, 4, 'sspu_principal', '$2y$10$InyDtLaVPlHEMnbbOv.iQO4psc29vljW3FFUkNlOaIoHl8ubCLfVq', 'Mrs. Jaya  Patil', 'sspu@gmail.com', 'principal', 1, '2026-03-27 13:40:28', '2026-03-27 20:37:36', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_bill_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_bill_summary` (
`inst_name` varchar(120)
,`fy_label` varchar(10)
,`category` varchar(80)
,`bill_count` bigint(21)
,`total_spent` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stock_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_stock_summary` (
`inst_name` varchar(120)
,`category` varchar(80)
,`status` enum('Active','In Use','Under Repair','Condemned')
,`item_count` bigint(21)
,`total_value` decimal(44,4)
);

-- --------------------------------------------------------

--
-- Structure for view `v_bill_summary`
--
DROP TABLE IF EXISTS `v_bill_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bill_summary`  AS SELECT `i`.`inst_name` AS `inst_name`, `fy`.`fy_label` AS `fy_label`, `ic`.`cat_name` AS `category`, count(0) AS `bill_count`, sum(`b`.`total_amount`) AS `total_spent` FROM (((`bills` `b` join `institutes` `i` on(`b`.`inst_id` = `i`.`id`)) join `financial_years` `fy` on(`b`.`fy_id` = `fy`.`id`)) left join `item_categories` `ic` on(`b`.`cat_id` = `ic`.`id`)) GROUP BY `i`.`id`, `fy`.`id`, `ic`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `v_stock_summary`
--
DROP TABLE IF EXISTS `v_stock_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock_summary`  AS SELECT `i`.`inst_name` AS `inst_name`, `ic`.`cat_name` AS `category`, `s`.`status` AS `status`, count(0) AS `item_count`, sum(`s`.`unit_cost` * `s`.`quantity`) AS `total_value` FROM ((`stock` `s` join `institutes` `i` on(`s`.`inst_id` = `i`.`id`)) join `item_categories` `ic` on(`s`.`cat_id` = `ic`.`id`)) GROUP BY `i`.`id`, `ic`.`id`, `s`.`status` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inst` (`inst_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `internal_ref` (`internal_ref`),
  ADD KEY `inst_id` (`inst_id`),
  ADD KEY `fy_id` (`fy_id`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `proposal_id` (`proposal_id`),
  ADD KEY `entered_by` (`entered_by`);

--
-- Indexes for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `institutes`
--
ALTER TABLE `institutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inst_code` (`inst_code`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `institute_types`
--
ALTER TABLE `institute_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_code` (`type_code`);

--
-- Indexes for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inst_id` (`inst_id`);

--
-- Indexes for table `movements`
--
ALTER TABLE `movements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ref_no` (`ref_no`),
  ADD KEY `inst_id` (`inst_id`),
  ADD KEY `stock_id` (`stock_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `proposal_no` (`proposal_no`),
  ADD KEY `inst_id` (`inst_id`),
  ADD KEY `fy_id` (`fy_id`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inst_id` (`inst_id`),
  ADD KEY `proposal_id` (`proposal_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `scrap`
--
ALTER TABLE `scrap`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scrap_no` (`scrap_no`),
  ADD KEY `inst_id` (`inst_id`),
  ADD KEY `stock_id` (`stock_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_tag` (`asset_tag`),
  ADD KEY `inst_id` (`inst_id`),
  ADD KEY `fy_id` (`fy_id`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `inst_id` (`inst_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=297;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `financial_years`
--
ALTER TABLE `financial_years`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `institutes`
--
ALTER TABLE `institutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `institute_types`
--
ALTER TABLE `institute_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `movements`
--
ALTER TABLE `movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `scrap`
--
ALTER TABLE `scrap`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`),
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`fy_id`) REFERENCES `financial_years` (`id`),
  ADD CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`cat_id`) REFERENCES `item_categories` (`id`),
  ADD CONSTRAINT `bills_ibfk_4` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bills_ibfk_5` FOREIGN KEY (`entered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `institutes`
--
ALTER TABLE `institutes`
  ADD CONSTRAINT `institutes_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `institute_types` (`id`);

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movements`
--
ALTER TABLE `movements`
  ADD CONSTRAINT `movements_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`),
  ADD CONSTRAINT `movements_ibfk_2` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`),
  ADD CONSTRAINT `movements_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`),
  ADD CONSTRAINT `proposals_ibfk_2` FOREIGN KEY (`fy_id`) REFERENCES `financial_years` (`id`),
  ADD CONSTRAINT `proposals_ibfk_3` FOREIGN KEY (`cat_id`) REFERENCES `item_categories` (`id`),
  ADD CONSTRAINT `proposals_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`),
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotations_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `scrap`
--
ALTER TABLE `scrap`
  ADD CONSTRAINT `scrap_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`),
  ADD CONSTRAINT `scrap_ibfk_2` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`),
  ADD CONSTRAINT `scrap_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`),
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`fy_id`) REFERENCES `financial_years` (`id`),
  ADD CONSTRAINT `stock_ibfk_3` FOREIGN KEY (`cat_id`) REFERENCES `item_categories` (`id`),
  ADD CONSTRAINT `stock_ibfk_4` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_ibfk_5` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`inst_id`) REFERENCES `institutes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
