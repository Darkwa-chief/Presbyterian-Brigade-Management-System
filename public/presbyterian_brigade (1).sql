-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2026 at 05:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `presbyterian_brigade`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `brigader_id` int(11) NOT NULL,
  `status` enum('present','absent','excused','late') NOT NULL DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `marked_by` int(11) DEFAULT NULL,
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `event_id`, `brigader_id`, `status`, `notes`, `marked_by`, `marked_at`) VALUES
(4, 5, 12, 'present', '', 1, '2026-06-29 05:22:24'),
(5, 5, 15, 'present', '', 1, '2026-06-29 05:22:24'),
(6, 5, 11, 'present', '', 1, '2026-06-29 05:22:24'),
(7, 5, 4, 'present', '', 1, '2026-06-29 05:22:24'),
(8, 5, 14, 'present', '', 1, '2026-06-29 05:22:24'),
(9, 5, 9, 'present', '', 1, '2026-06-29 05:22:24'),
(10, 5, 10, 'present', '', 1, '2026-06-29 05:22:24'),
(11, 5, 6, 'present', '', 1, '2026-06-29 05:22:24'),
(12, 5, 7, 'present', '', 1, '2026-06-29 05:22:24'),
(13, 5, 2, 'present', '', 1, '2026-06-29 05:22:24'),
(14, 5, 5, 'present', '', 1, '2026-06-29 05:22:24'),
(15, 5, 1, 'present', '', 1, '2026-06-29 05:22:24'),
(16, 5, 13, 'present', '', 1, '2026-06-29 05:22:24'),
(17, 5, 3, 'present', '', 1, '2026-06-29 05:22:24'),
(18, 5, 8, 'present', '', 1, '2026-06-29 05:22:24'),
(19, 5, 17, 'present', '', 1, '2026-06-28 17:22:24'),
(20, 5, 16, 'present', '', 1, '2026-06-28 17:22:24'),
(21, 6, 18, 'present', '', 1, '2026-07-12 21:20:06'),
(22, 6, 12, 'absent', '', 1, '2026-07-12 21:20:06'),
(23, 6, 15, 'absent', '', 1, '2026-07-13 20:49:34'),
(24, 6, 11, 'absent', '', 1, '2026-07-12 21:20:06'),
(25, 6, 4, 'present', '', 1, '2026-07-12 21:20:06'),
(26, 6, 14, 'absent', '', 1, '2026-07-12 21:20:06'),
(27, 6, 9, 'absent', '', 1, '2026-07-13 20:49:34'),
(28, 6, 10, 'absent', '', 1, '2026-07-12 21:20:06'),
(29, 6, 6, 'absent', '', 1, '2026-07-13 20:49:34'),
(30, 6, 7, 'absent', '', 1, '2026-07-12 21:20:06'),
(31, 6, 23, 'present', '', 1, '2026-07-12 21:20:06'),
(32, 6, 22, 'present', '', 1, '2026-07-12 21:20:06'),
(33, 6, 2, 'absent', '', 1, '2026-07-12 21:20:06'),
(34, 6, 5, 'absent', '', 1, '2026-07-12 21:20:06'),
(35, 6, 24, 'present', '', 1, '2026-07-12 21:20:06'),
(36, 6, 19, 'present', '', 1, '2026-07-12 21:20:06'),
(37, 6, 1, 'present', '', 1, '2026-07-12 21:20:06'),
(38, 6, 17, 'absent', '', 1, '2026-07-12 21:20:06'),
(39, 6, 21, 'present', '', 1, '2026-07-12 21:20:06'),
(40, 6, 16, 'absent', '', 1, '2026-07-12 21:20:06'),
(41, 6, 20, 'absent', '', 1, '2026-07-12 21:20:06'),
(42, 6, 13, 'absent', '', 1, '2026-07-12 21:20:06'),
(43, 6, 3, 'absent', '', 1, '2026-07-12 21:20:06'),
(44, 6, 8, 'present', '', 1, '2026-07-13 20:49:34'),
(45, 6, 25, 'present', '', 1, '2026-07-13 08:49:34'),
(46, 6, 27, 'present', '', 1, '2026-07-13 08:49:34'),
(47, 6, 26, 'present', '', 1, '2026-07-13 08:49:34'),
(48, 6, 33, 'present', '', 1, '2026-07-13 08:49:34'),
(49, 6, 29, 'present', '', 1, '2026-07-13 08:49:34'),
(50, 6, 28, 'present', '', 1, '2026-07-13 08:49:34'),
(51, 6, 30, 'present', '', 1, '2026-07-13 08:49:34'),
(52, 6, 32, 'present', '', 1, '2026-07-13 08:49:34'),
(53, 6, 31, 'present', '', 1, '2026-07-13 08:49:34'),
(54, 7, 18, 'present', '', 1, '2026-07-20 09:17:27'),
(55, 7, 12, 'absent', '', 1, '2026-07-20 09:17:27'),
(56, 7, 11, 'present', '', 1, '2026-07-20 09:17:27'),
(57, 7, 4, 'present', '', 1, '2026-07-20 09:17:27'),
(58, 7, 14, 'present', '', 1, '2026-07-20 09:17:27'),
(59, 7, 10, 'present', '', 1, '2026-07-20 09:17:27'),
(60, 7, 7, 'present', '', 1, '2026-07-20 09:17:27'),
(61, 7, 23, 'absent', '', 1, '2026-07-20 09:17:27'),
(62, 7, 22, 'absent', '', 1, '2026-07-20 09:17:27'),
(63, 7, 2, 'present', '', 1, '2026-07-20 09:17:27'),
(64, 7, 5, 'present', '', 1, '2026-07-20 09:17:27'),
(65, 7, 24, 'present', '', 1, '2026-07-20 09:17:27'),
(66, 7, 19, 'present', '', 1, '2026-07-20 09:17:27'),
(67, 7, 1, 'present', '', 1, '2026-07-20 09:17:27'),
(68, 7, 17, 'absent', '', 1, '2026-07-20 09:17:27'),
(69, 7, 21, 'present', '', 1, '2026-07-20 09:17:27'),
(70, 7, 16, 'absent', '', 1, '2026-07-20 09:17:27'),
(71, 7, 20, 'present', '', 1, '2026-07-20 09:17:27'),
(72, 7, 13, 'present', '', 1, '2026-07-20 09:17:27'),
(73, 7, 3, 'present', '', 1, '2026-07-20 09:17:27'),
(74, 7, 25, 'absent', '', 1, '2026-07-20 09:16:24'),
(75, 7, 15, 'absent', '', 1, '2026-07-20 09:16:24'),
(76, 7, 27, 'present', '', 1, '2026-07-20 09:16:24'),
(77, 7, 9, 'present', '', 1, '2026-07-20 09:16:24'),
(78, 7, 6, 'absent', '', 1, '2026-07-20 09:16:24'),
(79, 7, 26, 'absent', '', 1, '2026-07-20 09:16:24'),
(80, 7, 33, 'present', '', 1, '2026-07-20 09:16:24'),
(81, 7, 29, 'present', '', 1, '2026-07-20 09:16:24'),
(82, 7, 28, 'absent', '', 1, '2026-07-20 09:16:24'),
(83, 7, 30, 'present', '', 1, '2026-07-20 09:16:24'),
(84, 7, 32, 'present', '', 1, '2026-07-20 09:16:24'),
(85, 7, 31, 'present', '', 1, '2026-07-20 09:16:24'),
(86, 7, 8, 'absent', '', 1, '2026-07-20 09:16:24'),
(87, 7, 39, 'present', '', 1, '2026-07-20 09:16:24'),
(88, 7, 40, 'present', '', 1, '2026-07-20 09:16:24'),
(89, 7, 37, 'present', '', 1, '2026-07-20 09:17:27'),
(90, 7, 35, 'present', '', 1, '2026-07-20 09:17:27'),
(91, 7, 38, 'present', '', 1, '2026-07-20 09:17:27'),
(92, 7, 36, 'present', '', 1, '2026-07-20 09:17:27'),
(93, 7, 34, 'present', '', 1, '2026-07-20 09:17:27'),
(94, 8, 37, 'absent', '', 1, '2026-08-09 21:07:07'),
(95, 8, 18, 'present', '', 1, '2026-08-09 21:07:07'),
(96, 8, 35, 'absent', '', 1, '2026-08-09 21:07:07'),
(97, 8, 12, 'absent', '', 1, '2026-08-09 21:07:07'),
(98, 8, 11, 'absent', '', 1, '2026-08-09 21:07:07'),
(99, 8, 4, 'present', '', 1, '2026-08-09 21:07:07'),
(100, 8, 14, 'absent', '', 1, '2026-08-09 21:07:07'),
(101, 8, 10, 'absent', '', 1, '2026-08-09 21:07:07'),
(102, 8, 7, 'absent', '', 1, '2026-08-09 21:07:07'),
(103, 8, 23, 'absent', '', 1, '2026-08-09 21:07:07'),
(104, 8, 22, 'absent', '', 1, '2026-08-09 21:07:07'),
(105, 8, 38, 'absent', '', 1, '2026-08-09 21:07:07'),
(106, 8, 36, 'absent', '', 1, '2026-08-09 21:07:07'),
(107, 8, 2, 'absent', '', 1, '2026-08-09 21:07:07'),
(108, 8, 5, 'present', '', 1, '2026-08-09 21:07:07'),
(109, 8, 24, 'present', '', 1, '2026-08-09 21:07:07'),
(110, 8, 19, 'absent', '', 1, '2026-08-09 21:07:07'),
(111, 8, 1, 'present', '', 1, '2026-08-09 21:07:07'),
(112, 8, 17, 'absent', '', 1, '2026-08-09 21:07:07'),
(113, 8, 21, 'present', '', 1, '2026-08-09 21:07:07'),
(114, 8, 16, 'absent', '', 1, '2026-08-09 21:07:07'),
(115, 8, 20, 'present', '', 1, '2026-08-09 21:07:07'),
(116, 8, 13, 'absent', '', 1, '2026-08-09 21:07:07'),
(117, 8, 3, 'absent', '', 1, '2026-08-09 21:07:07'),
(118, 8, 34, 'present', '', 1, '2026-08-09 21:07:07'),
(119, 8, 39, 'absent', '', 1, '2026-08-09 21:12:26'),
(120, 8, 25, 'present', '', 1, '2026-08-09 21:12:26'),
(121, 8, 15, 'absent', '', 1, '2026-08-09 21:12:26'),
(122, 8, 27, 'absent', '', 1, '2026-08-09 21:12:26'),
(123, 8, 40, 'absent', '', 1, '2026-08-09 21:12:26'),
(124, 8, 9, 'absent', '', 1, '2026-08-09 21:12:26'),
(125, 8, 6, 'absent', '', 1, '2026-08-09 21:12:26'),
(126, 8, 26, 'absent', '', 1, '2026-08-09 21:12:26'),
(127, 8, 33, 'present', '', 1, '2026-08-09 21:12:26'),
(128, 8, 29, 'absent', '', 1, '2026-08-09 21:12:26'),
(129, 8, 28, 'absent', '', 1, '2026-08-09 21:12:26'),
(130, 8, 30, 'absent', '', 1, '2026-08-09 21:12:26'),
(131, 8, 32, 'present', '', 1, '2026-08-09 21:12:26'),
(132, 8, 31, 'absent', '', 1, '2026-08-09 21:12:26'),
(133, 8, 8, 'absent', '', 1, '2026-08-09 21:12:26'),
(134, 9, 37, 'present', '', 1, '2026-09-20 19:46:24'),
(135, 9, 39, 'absent', '', 1, '2026-09-20 19:46:24'),
(136, 9, 18, 'absent', '', 1, '2026-09-20 19:46:24'),
(137, 9, 35, 'absent', '', 1, '2026-09-20 19:46:24'),
(138, 9, 12, 'absent', '', 1, '2026-09-20 19:46:24'),
(139, 9, 25, 'present', '', 1, '2026-09-20 19:46:24'),
(140, 9, 15, 'absent', '', 1, '2026-09-20 19:46:24'),
(141, 9, 11, 'absent', '', 1, '2026-09-20 19:46:24'),
(142, 9, 4, 'present', '', 1, '2026-09-20 19:46:24'),
(143, 9, 27, 'absent', '', 1, '2026-09-20 19:46:24'),
(144, 9, 40, 'absent', '', 1, '2026-09-20 19:46:24'),
(145, 9, 46, 'present', '', 1, '2026-09-20 19:46:24'),
(146, 9, 47, 'present', '', 1, '2026-09-20 19:46:24'),
(147, 9, 48, 'present', '', 1, '2026-09-20 19:46:24'),
(148, 9, 42, 'absent', '', 1, '2026-09-20 19:46:24'),
(149, 9, 41, 'absent', '', 1, '2026-09-20 19:46:24'),
(150, 9, 14, 'absent', '', 1, '2026-09-20 19:46:24'),
(151, 9, 9, 'absent', '', 1, '2026-09-20 19:46:24'),
(152, 9, 10, 'absent', '', 1, '2026-09-20 19:46:24'),
(153, 9, 6, 'absent', '', 1, '2026-09-20 19:46:24'),
(154, 9, 7, 'absent', '', 1, '2026-09-20 19:46:24'),
(155, 9, 26, 'absent', '', 1, '2026-09-20 19:46:24'),
(156, 9, 43, 'absent', '', 1, '2026-09-20 19:46:24'),
(157, 9, 23, 'absent', '', 1, '2026-09-20 19:46:24'),
(158, 9, 22, 'absent', '', 1, '2026-09-20 19:46:24'),
(159, 9, 33, 'present', '', 1, '2026-09-20 19:46:24'),
(160, 9, 38, 'absent', '', 1, '2026-09-20 19:46:24'),
(161, 9, 36, 'absent', '', 1, '2026-09-20 19:46:24'),
(162, 9, 44, 'absent', '', 1, '2026-09-20 19:46:24'),
(163, 9, 2, 'present', '', 1, '2026-09-20 19:46:24'),
(164, 9, 5, 'absent', '', 1, '2026-09-20 19:46:24'),
(165, 9, 24, 'absent', '', 1, '2026-09-20 19:46:24'),
(166, 9, 19, 'present', '', 1, '2026-09-20 19:46:24'),
(167, 9, 1, 'present', '', 1, '2026-09-20 19:46:24'),
(168, 9, 17, 'absent', '', 1, '2026-09-20 19:46:24'),
(169, 9, 45, 'absent', '', 1, '2026-09-20 19:46:24'),
(170, 9, 29, 'present', '', 1, '2026-09-20 19:46:24'),
(171, 9, 28, 'absent', '', 1, '2026-09-20 19:46:24'),
(172, 9, 30, 'absent', '', 1, '2026-09-20 19:46:24'),
(173, 9, 21, 'absent', '', 1, '2026-09-20 19:46:24'),
(174, 9, 32, 'present', '', 1, '2026-09-20 19:46:24'),
(175, 9, 16, 'absent', '', 1, '2026-09-20 19:46:24'),
(176, 9, 20, 'present', '', 1, '2026-09-20 19:46:24'),
(177, 9, 13, 'absent', '', 1, '2026-09-20 19:46:24'),
(178, 9, 31, 'absent', '', 1, '2026-09-20 19:46:24'),
(179, 9, 3, 'present', '', 1, '2026-09-20 19:46:24'),
(180, 9, 8, 'absent', '', 1, '2026-09-20 19:46:24'),
(181, 9, 34, 'absent', '', 1, '2026-09-20 19:46:24'),
(182, 10, 37, 'absent', '', 1, '2026-09-20 19:41:10'),
(183, 10, 18, 'excused', '', 1, '2026-09-20 19:41:10'),
(184, 10, 35, 'present', '', 1, '2026-09-20 19:41:10'),
(185, 10, 12, 'absent', '', 1, '2026-09-20 19:41:10'),
(186, 10, 11, 'absent', '', 1, '2026-09-20 19:41:10'),
(187, 10, 4, 'present', '', 1, '2026-09-20 19:41:10'),
(188, 10, 46, 'present', '', 1, '2026-09-20 19:41:10'),
(189, 10, 47, 'present', '', 1, '2026-09-20 19:41:10'),
(190, 10, 48, 'present', '', 1, '2026-09-20 19:33:38'),
(191, 10, 14, 'present', '', 1, '2026-09-20 19:41:10'),
(192, 10, 10, 'absent', '', 1, '2026-09-20 19:41:10'),
(193, 10, 7, 'absent', '', 1, '2026-09-20 19:41:10'),
(194, 10, 23, 'absent', '', 1, '2026-09-20 19:41:10'),
(195, 10, 22, 'absent', '', 1, '2026-09-20 19:41:10'),
(196, 10, 38, 'present', '', 1, '2026-09-20 19:41:10'),
(197, 10, 36, 'present', '', 1, '2026-09-20 19:41:10'),
(198, 10, 2, 'present', '', 1, '2026-09-20 19:41:10'),
(199, 10, 5, 'absent', '', 1, '2026-09-20 19:41:10'),
(200, 10, 24, 'absent', '', 1, '2026-09-20 19:41:10'),
(201, 10, 19, 'present', '', 1, '2026-09-20 19:41:10'),
(202, 10, 1, 'present', '', 1, '2026-09-20 19:41:10'),
(203, 10, 17, 'absent', '', 1, '2026-09-20 19:41:10'),
(204, 10, 21, 'absent', '', 1, '2026-09-20 19:41:10'),
(205, 10, 16, 'absent', '', 1, '2026-09-20 19:41:10'),
(206, 10, 20, 'absent', '', 1, '2026-09-20 19:41:10'),
(207, 10, 13, 'absent', '', 1, '2026-09-20 19:41:10'),
(208, 10, 3, 'absent', '', 1, '2026-09-20 19:41:10'),
(209, 10, 34, 'absent', '', 1, '2026-09-20 19:41:10'),
(210, 10, 39, 'absent', '', 1, '2026-09-20 19:35:35'),
(211, 10, 25, 'absent', '', 1, '2026-09-20 19:35:35'),
(212, 10, 15, 'absent', '', 1, '2026-09-20 19:35:35'),
(213, 10, 27, 'present', '', 1, '2026-09-20 19:35:35'),
(214, 10, 40, 'present', '', 1, '2026-09-20 19:35:35'),
(215, 10, 42, 'present', '', 1, '2026-09-20 19:35:35'),
(216, 10, 41, 'present', '', 1, '2026-09-20 19:35:35'),
(217, 10, 9, 'present', '', 1, '2026-09-20 19:35:35'),
(218, 10, 6, 'absent', '', 1, '2026-09-20 19:35:35'),
(219, 10, 26, 'absent', '', 1, '2026-09-20 19:35:35'),
(220, 10, 43, 'absent', '', 1, '2026-09-20 19:35:35'),
(221, 10, 33, 'present', '', 1, '2026-09-20 19:35:35'),
(222, 10, 44, 'absent', '', 1, '2026-09-20 19:35:35'),
(223, 10, 45, 'present', '', 1, '2026-09-20 19:35:35'),
(224, 10, 29, 'absent', '', 1, '2026-09-20 19:35:35'),
(225, 10, 28, 'absent', '', 1, '2026-09-20 19:35:35'),
(226, 10, 30, 'absent', '', 1, '2026-09-20 19:35:35'),
(227, 10, 32, 'present', '', 1, '2026-09-20 19:35:35'),
(228, 10, 31, 'present', '', 1, '2026-09-20 19:35:35'),
(229, 10, 8, 'absent', '', 1, '2026-09-20 19:35:35');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(286, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 08:49:40'),
(288, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-21 13:57:44'),
(289, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-24 21:23:59'),
(290, 1, 'create_brigader', 'brigaders', 44, NULL, '{\"first_name\":\"Gertrude\",\"last_name\":\"Boateng Efia  Serwaa\",\"date_of_birth\":\"2002-09-06\",\"gender\":\"female\",\"email\":\"\",\"phone\":\"0543265906 \\/ 0544582103\",\"address\":\"Korle Bu\",\"rank_id\":null,\"unit\":\"Officer\",\"join_date\":\"2026-08-24\",\"status\":\"active\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-24 21:29:10'),
(291, 1, 'create_brigader', 'brigaders', 45, NULL, '{\"first_name\":\"Samuella Mamle Seryoe\",\"last_name\":\"Konotey\",\"date_of_birth\":\"2007-03-16\",\"gender\":\"female\",\"email\":\"\",\"phone\":\"0256454183\",\"address\":\"Mamprobi\",\"rank_id\":null,\"unit\":\"Senior\",\"join_date\":\"2026-08-24\",\"status\":\"active\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-24 21:33:54'),
(292, 1, 'update_brigader', 'brigaders', 44, NULL, '{\"first_name\":\"Gertrude\",\"last_name\":\"Boateng Efia  Serwaa\",\"date_of_birth\":\"2002-09-06\",\"gender\":\"female\",\"email\":\"\",\"phone\":\"0543265906 \\/ 0544582\",\"address\":\"Korle Bu\",\"rank_id\":null,\"unit\":\"Officer\",\"join_date\":\"2026-08-24\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-24 22:29:23'),
(293, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-30 20:55:57'),
(294, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 09:48:16'),
(295, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 17:19:15'),
(296, 1, 'create_brigader', 'brigaders', 46, NULL, '{\"first_name\":\"Kofi Asante Mireku\",\"last_name\":\"Ampadu\",\"date_of_birth\":\"2017-10-20\",\"gender\":\"male\",\"email\":\"\",\"phone\":\"0272434913\",\"address\":\"Opposite Korle bu police station\",\"rank_id\":null,\"unit\":\"Junior\",\"join_date\":\"2026-09-13\",\"status\":\"active\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 17:23:54'),
(297, 1, 'create_brigader', 'brigaders', 47, NULL, '{\"first_name\":\"Kwame Amoateng Mireku\",\"last_name\":\"Ampadu\",\"date_of_birth\":\"2020-10-10\",\"gender\":\"male\",\"email\":\"\",\"phone\":\"0272434913\",\"address\":\"Opposite Korle bu police station\",\"rank_id\":null,\"unit\":\"Junior\",\"join_date\":\"2026-09-13\",\"status\":\"active\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 17:25:28'),
(298, 1, 'create_brigader', 'brigaders', 48, NULL, '{\"first_name\":\"Yaa Obrempomaa Mireku\",\"last_name\":\"Ampadu\",\"date_of_birth\":\"2024-04-18\",\"gender\":\"male\",\"email\":\"\",\"phone\":\"0272434913\",\"address\":\"Opposite Korle bu\",\"rank_id\":null,\"unit\":\"Junior\",\"join_date\":\"2026-09-13\",\"status\":\"active\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 17:27:20'),
(299, 1, 'create_event', 'events', 9, NULL, '{\"event_name\":\"Sunday Meeting\",\"event_type\":\"meeting\",\"event_date\":\"2026-08-13\",\"event_time\":null,\"location\":\"Children Service\",\"description\":\"\",\"status\":\"ongoing\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 17:29:18'),
(300, 1, 'update_event', 'events', 9, NULL, '{\"event_name\":\"Sunday Meeting\",\"event_type\":\"meeting\",\"event_date\":\"2026-09-13\",\"event_time\":null,\"location\":\"Children Service\",\"description\":\"\",\"status\":\"ongoing\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 17:30:50'),
(301, 1, 'mark_attendance', 'attendance', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 17:36:23'),
(302, 1, 'update_event_status', 'events', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 20:26:43'),
(303, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 20:28:38'),
(304, 11, 'login', 'users', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 20:28:52'),
(305, 11, 'logout', 'users', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 20:41:40'),
(306, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 20:41:48'),
(307, 1, 'create_recital', 'recitals', 2, NULL, '{\"title\":\"METHOD\",\"content\":\"The combination of religion and discipline, religious instructions at bible class and discipline at company parade.\",\"category\":\"catechism\",\"reference\":\"\",\"difficulty_level\":\"beginner\",\"visibility\":\"all\",\"is_active\":1,\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-13 20:47:00'),
(308, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:27:33'),
(309, 1, 'create_event', 'events', 10, NULL, '{\"event_name\":\"Sunday Meeting and Drills\",\"event_type\":\"meeting\",\"event_date\":\"2026-09-20\",\"event_time\":\"15:40\",\"location\":\"Children Service\",\"description\":\"Meeting and Drills training\",\"status\":\"ongoing\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:29:33'),
(310, 1, 'mark_attendance', 'attendance', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:33:39'),
(311, 1, 'mark_attendance', 'attendance', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:35:35'),
(312, 1, 'update_brigader', 'brigaders', 48, NULL, '{\"first_name\":\"Yaa Obrempomaa Mireku\",\"last_name\":\"Ampadu\",\"date_of_birth\":\"2024-04-18\",\"gender\":\"female\",\"email\":\"\",\"phone\":\"0272434913\",\"address\":\"Opposite Korle bu\",\"rank_id\":null,\"unit\":\"Junior\",\"join_date\":\"2026-09-13\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:36:07'),
(313, 1, 'update_brigader', 'brigaders', 14, NULL, '{\"first_name\":\"Joel\",\"last_name\":\"Anim Boateng\",\"date_of_birth\":\"2013-08-22\",\"gender\":\"male\",\"email\":\"\",\"phone\":\"0244767300\",\"address\":\"Last Stop\",\"rank_id\":null,\"unit\":\"Junior\",\"join_date\":\"2026-06-28\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:37:05'),
(314, 1, 'mark_attendance', 'attendance', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:37:49'),
(315, 1, 'complete_event', 'events', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:38:32'),
(316, 1, 'mark_attendance', 'attendance', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:41:10'),
(317, 1, 'create_user', 'users', 17, NULL, '{\"username\":\"Florence Ampadu\",\"email\":\"florence@gmail.com\",\"full_name\":\"Florence Ampadu\",\"role\":\"officer\",\"status\":\"active\",\"password\":\"0000\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:43:51'),
(318, 1, 'update_user', 'users', 16, NULL, '{\"email\":\"kingsley@gmail.com\",\"full_name\":\"Kingsley Afam\",\"role\":\"officer\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:44:19'),
(319, 1, 'mark_attendance', 'attendance', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:46:24'),
(320, 1, 'create_event', 'events', 11, NULL, '{\"event_name\":\"Visit To St Francis of Assisi\",\"event_type\":\"other\",\"event_date\":\"2026-09-27\",\"event_time\":\"08:00\",\"location\":\"St Francis - Mamprobi\",\"description\":\"\",\"status\":\"scheduled\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-20 19:59:00');

-- --------------------------------------------------------

--
-- Table structure for table `brigaders`
--

CREATE TABLE `brigaders` (
  `id` int(11) NOT NULL,
  `brigade_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `passport_photo` varchar(255) DEFAULT NULL,
  `rank_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `join_date` date NOT NULL,
  `status` enum('active','inactive','transferred') NOT NULL DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brigaders`
--

INSERT INTO `brigaders` (`id`, `brigade_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `email`, `phone`, `address`, `passport_photo`, `rank_id`, `unit`, `join_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BRG20260001', 'Ishmael Darkwa', 'Gyimah', '2006-05-10', 'male', 'ishmaelgyimah13@gmail.com', '0546996407', 'Accra , Korle-Gonno\r\nAccra', 'uploads/passports/passport_1_1770885571.jpg', NULL, 'Senior Boy', '2026-02-12', 'active', 1, '2026-02-12 08:39:31', '2026-07-20 09:20:06'),
(2, 'BRG20260002', 'Curtis', 'Buabeng', '2017-06-21', 'male', '', '0593373634', 'Accra, Mamprobi', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 16:53:18', '2026-06-28 16:53:18'),
(3, 'BRG20260003', 'Abraham', 'Yeboah', '2012-11-20', 'male', '', '0504371284', 'Mamprobi, Girls School', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 16:54:55', '2026-06-28 16:54:55'),
(4, 'BRG20260004', 'Nathaniel', 'Amoah', '2015-05-14', 'male', 'ishmaelgyimah13@gmail.com', '0546996407', 'Mamprobi', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 16:55:49', '2026-06-28 16:55:49'),
(5, 'BRG20260005', 'Jason-Fred', 'Dzissah', '2011-05-05', 'male', '', '0548904041', 'Korle-bu, Quaters', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 16:57:39', '2026-06-28 16:57:39'),
(6, 'BRG20260006', 'Akua', 'Asante', '2017-09-27', 'female', '', '0543035747', '', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 16:59:31', '2026-06-28 16:59:31'),
(7, 'BRG20260007', 'Kojo', 'Asante', '2015-09-21', 'male', '', '0543035747', '', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:00:19', '2026-06-28 17:00:19'),
(8, 'BRG20260008', 'Comfort', 'Yeboah', '2014-02-09', 'female', '', '0538131122', '', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:01:11', '2026-06-28 17:01:11'),
(9, 'BRG20260009', 'Praiselyn', 'Anim Boateng', '2015-09-03', 'female', '', '0244767300', '', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:02:23', '2026-07-20 09:06:20'),
(10, 'BRG20260010', 'David', 'Antwi', '2013-06-15', 'male', '', '0548904041', '', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:03:40', '2026-06-28 17:03:40'),
(11, 'BRG20260011', 'Richmond', 'Amartey', '2016-01-07', 'male', '', '0546996407', 'Mamprobi, Sempe', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:05:01', '2026-06-28 17:05:01'),
(12, 'BRG20260012', 'Emmanuel', 'Allotey', '2013-02-10', 'male', '', '0555881618', 'Mamprobi-Sempe', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:06:03', '2026-06-28 17:06:03'),
(13, 'BRG20260013', 'Shane', 'Sackey', '2014-06-27', 'male', '', '0538923983', 'Mamprobi,Girls School', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:07:25', '2026-06-28 17:07:25'),
(14, 'BRG20260014', 'Joel', 'Anim Boateng', '2013-08-22', 'male', '', '0244767300', 'Last Stop', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:08:41', '2026-09-20 19:37:05'),
(15, 'BRG20260015', 'Esther', 'Amana', '2010-02-24', 'female', '', '0246990828', 'Awotse - Kojo', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:10:05', '2026-06-28 17:10:05'),
(16, 'BRG20260016', 'Jaden Kwabena', 'Ofori Safo', '2017-02-14', 'male', '', '0277833596', 'Mamprobi , Djarto Street', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:18:50', '2026-06-28 17:18:50'),
(17, 'BRG20260017', 'Franklin Zeus', 'Kojo Safo Junior', '2015-04-13', 'male', '', '05083915', 'Mamprobi , Djarto Street', NULL, NULL, 'Junior', '2026-06-28', 'active', 1, '2026-06-28 17:20:31', '2026-06-28 17:20:31'),
(18, 'BRG20260018', 'Clement', 'Afam', '2006-04-29', 'male', '', '0543626244', 'Mamprobi', 'uploads/passports/passport_18_1783890076.jpeg', NULL, 'Senior Boy', '2026-07-12', 'active', 1, '2026-07-12 21:01:16', '2026-07-20 09:09:56'),
(19, 'BRG20260019', 'George', 'Ferguson', '2000-01-01', 'male', '', '0268659722', 'Mamprobi', NULL, NULL, 'Senior boy', '2026-07-12', 'active', 1, '2026-07-12 21:06:10', '2026-07-12 21:06:10'),
(20, 'BRG20260020', 'Emmanuel Chris Oduro', 'Quarshie', '2015-12-25', 'male', '', '0551442230', 'Mamprobi', NULL, NULL, 'Junior', '2026-07-12', 'active', 1, '2026-07-12 21:08:11', '2026-07-12 21:08:11'),
(21, 'BRG20260021', 'Ephraim', 'Mensah', '2015-06-16', 'male', '', '0547155016', 'Camara', NULL, NULL, 'Junior', '2026-07-12', 'active', 1, '2026-07-12 21:09:56', '2026-07-12 21:09:56'),
(22, 'BRG20260022', 'Ohene', 'Bekoe', '2013-02-02', 'male', '', '0541603361', 'Accra', NULL, NULL, 'Junior', '2026-07-12', 'active', 1, '2026-07-12 21:11:26', '2026-07-12 21:11:26'),
(23, 'BRG20260023', 'Manuel', 'Awuah', '2014-06-22', 'male', '', '0541224904', 'Accra', NULL, NULL, 'Junior', '2026-07-12', 'active', 1, '2026-07-12 21:13:08', '2026-07-12 21:13:08'),
(24, 'BRG20260024', 'Emmanuel', 'Ebo Mensah', '2026-01-01', 'male', '', '0552853147', 'Accra', NULL, NULL, 'Senior boy', '2026-07-12', 'active', 1, '2026-07-12 21:16:29', '2026-08-09 21:03:48'),
(25, 'BRG20260025', 'Michelle', 'Allotey', '2000-01-01', 'female', '', '0557266710', 'Korle - Gonno', NULL, NULL, 'Junior', '2026-07-13', 'active', 1, '2026-07-13 08:31:20', '2026-07-13 08:31:20'),
(26, 'BRG20260026', 'Jochebed', 'Asiedu', '2000-01-01', 'female', '', '0541603361', 'Accra', NULL, NULL, 'Junior', '2026-07-13', 'active', 1, '2026-07-13 08:33:05', '2026-07-13 08:33:05'),
(27, 'BRG20260027', 'Nathalia', 'Amoussou', '2012-03-14', 'female', '', '0557371737', 'Accra', NULL, NULL, 'Junior', '2026-07-13', 'active', 1, '2026-07-13 08:36:18', '2026-07-20 09:05:36'),
(28, 'BRG20260028', 'Emmanuella', 'Lisa', '2000-01-01', 'female', '', '0000000000', 'Accra', NULL, NULL, 'Junior', '2026-07-13', 'active', 1, '2026-07-13 08:37:33', '2026-07-13 08:37:33'),
(29, 'BRG20260029', 'Mary', 'Laryea', '2014-05-12', 'female', '', '0547147732', 'Accra', NULL, NULL, 'Junior', '2026-07-13', 'active', 1, '2026-07-13 08:38:24', '2026-07-20 09:07:25'),
(30, 'BRG20260030', 'Eunice', 'Martey', '2000-01-01', 'female', '', '0000000000', 'Accra', NULL, NULL, 'Junior', '2026-07-13', 'active', 1, '2026-07-13 08:39:24', '2026-07-13 08:39:24'),
(31, 'BRG20260031', 'Kayla', 'Tagoe', '2015-10-22', 'female', '', '0244939434', 'Accra', NULL, NULL, 'Junior', '2026-07-13', 'active', 1, '2026-07-13 08:40:29', '2026-07-20 09:09:32'),
(32, 'BRG20260032', 'Quinster', 'Mensah', '2000-01-01', 'female', '', '0509402916', 'Accra', NULL, NULL, 'Officer', '2026-07-13', 'active', 1, '2026-07-13 08:43:08', '2026-07-13 08:43:08'),
(33, 'BRG20260033', 'Ellen Frimpomaa', 'Boadu', '2007-07-08', 'female', 'ishmaelgyimah13@gmail.com', '0537632297', 'Accra , Korle-Gonno', NULL, NULL, 'Senior', '2026-07-13', 'active', 1, '2026-07-13 08:47:57', '2026-08-09 21:00:37'),
(34, 'BRG20260034', 'Nana Kwame', 'Yeboah Agyemang', '2015-05-26', 'male', '', '0591560400', '', NULL, NULL, 'Junior', '2026-07-20', 'active', 1, '2026-07-20 06:50:17', '2026-07-20 06:50:17'),
(35, 'BRG20260035', 'Joel Senanu', 'Agbeyenawo', '2016-01-06', 'male', '', '0555316534', '', NULL, NULL, 'Junior', '2026-07-20', 'active', 1, '2026-07-20 06:52:32', '2026-07-20 06:52:32'),
(36, 'BRG20260036', 'Samuel', 'Boateng', '2014-06-19', 'male', '', '0243072504', '', NULL, NULL, 'Junior Session', '2026-07-20', 'active', 1, '2026-07-20 06:59:55', '2026-07-20 06:59:55'),
(37, 'BRG20260037', 'Melvin Osei', 'Adjei', '2013-12-29', 'male', '', '0205966744', '', NULL, NULL, 'Junior Session', '2026-07-20', 'active', 1, '2026-07-20 07:02:13', '2026-07-20 07:02:13'),
(38, 'BRG20260038', 'Jenkins', 'Boateng', '2011-04-21', 'male', '', '0243072504', '', NULL, NULL, 'Junior Session', '2026-07-20', 'active', 1, '2026-07-20 07:03:23', '2026-07-20 07:03:23'),
(39, 'BRG20260039', 'Mimi Osei', 'Adjei', '2016-02-18', 'female', '', '0205966744', '', NULL, NULL, 'Junior', '2026-07-20', 'active', 1, '2026-07-20 09:04:33', '2026-07-20 09:04:33'),
(40, 'BRG20260040', 'Navida', 'Amoussou', '2014-04-29', 'female', '', '0557371737', '', NULL, NULL, 'Junior', '2026-07-20', 'active', 1, '2026-07-20 09:13:45', '2026-07-20 09:13:45'),
(41, 'BRG20260041', 'Tracy', 'Amponsah', '2001-12-29', 'female', '', '0277211892 / 0534920', 'Korle Gonno', NULL, NULL, 'Senior', '2026-08-20', 'active', 1, '2026-08-20 13:45:32', '2026-08-20 13:45:32'),
(42, 'BRG20260042', 'Akosua Agyapomaa', 'Amponsah', '2015-08-30', 'female', 'ishmaelgyimah13@gmail.com', '0277211892 / 0534920', 'Accra , Korle-Gonno', NULL, NULL, 'Junior', '2026-08-20', 'active', 1, '2026-08-20 13:47:55', '2026-08-20 13:47:55'),
(43, 'BRG20260043', 'Baidu Stephanie', 'Atuabea', '2005-12-13', 'female', '', '', 'Accra , Tuesday Market', NULL, NULL, 'Officer', '2026-08-21', 'active', 1, '2026-08-21 06:38:25', '2026-08-21 06:38:25'),
(44, 'BRG20260044', 'Gertrude', 'Boateng Efia  Serwaa', '2002-09-06', 'female', '', '0543265906 / 0544582', 'Korle Bu', 'uploads/passports/passport_44_1787606950.jpeg', NULL, 'Officer', '2026-08-24', 'active', 1, '2026-08-24 21:29:10', '2026-08-24 21:29:10'),
(45, 'BRG20260045', 'Samuella Mamle Seryoe', 'Konotey', '2007-03-16', 'female', '', '0256454183', 'Mamprobi', NULL, NULL, 'Senior', '2026-08-24', 'active', 1, '2026-08-24 21:33:54', '2026-08-24 21:33:54'),
(46, 'BRG20260046', 'Kofi Asante Mireku', 'Ampadu', '2017-10-20', 'male', '', '0272434913', 'Opposite Korle bu police station', NULL, NULL, 'Junior', '2026-09-13', 'active', 1, '2026-09-13 17:23:54', '2026-09-13 17:23:54'),
(47, 'BRG20260047', 'Kwame Amoateng Mireku', 'Ampadu', '2020-10-10', 'male', '', '0272434913', 'Opposite Korle bu police station', NULL, NULL, 'Junior', '2026-09-13', 'active', 1, '2026-09-13 17:25:28', '2026-09-13 17:25:28'),
(48, 'BRG20260048', 'Yaa Obrempomaa Mireku', 'Ampadu', '2024-04-18', 'female', '', '0272434913', 'Opposite Korle bu', NULL, NULL, 'Junior', '2026-09-13', 'active', 1, '2026-09-13 17:27:20', '2026-09-20 19:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_type` enum('meeting','training','service','competition','other') NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `event_type`, `event_date`, `event_time`, `location`, `description`, `created_by`, `status`, `created_at`, `updated_at`) VALUES
(5, 'Sunday Meeting And Band Rehearsal', 'meeting', '2026-06-28', '15:00:00', 'Children Service', 'Sunday Meeting And Band Rehearsal', 1, 'completed', '2026-06-28 17:13:19', '2026-07-12 20:49:27'),
(6, 'Sunday Meeting', 'meeting', '2026-07-12', NULL, 'Children Service', 'Meeting and Drills training', 1, 'completed', '2026-07-12 20:51:03', '2026-07-12 20:51:03'),
(7, 'Sunday Meeting (Quiz )', 'meeting', '2026-07-19', NULL, 'Children Service', '', 1, 'completed', '2026-07-19 17:03:45', '2026-07-20 09:28:18'),
(8, 'Sunday Meeting', 'meeting', '2026-09-08', NULL, 'Children Service', '', 1, 'completed', '2026-08-09 21:01:43', '2026-08-20 08:56:13'),
(9, 'Sunday Meeting', 'meeting', '2026-09-13', NULL, 'Children Service', '', 1, 'completed', '2026-09-13 17:29:18', '2026-09-13 20:26:43'),
(10, 'Sunday Meeting and Drills', 'meeting', '2026-09-20', '15:40:00', 'Children Service', 'Meeting and Drills training', 1, 'completed', '2026-09-20 19:29:33', '2026-09-20 19:38:32'),
(11, 'Visit To St Francis of Assisi', 'other', '2026-09-27', '08:00:00', 'St Francis - Mamprobi', '', 1, 'scheduled', '2026-09-20 19:59:00', '2026-09-20 19:59:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('general','event','promotion','urgent') NOT NULL DEFAULT 'general',
  `target_role` enum('all','admin','officer','member') NOT NULL DEFAULT 'all',
  `target_unit` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `brigader_id` int(11) NOT NULL,
  `from_rank_id` int(11) DEFAULT NULL,
  `to_rank_id` int(11) NOT NULL,
  `promotion_date` date NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `id` int(11) NOT NULL,
  `rank_name` varchar(50) NOT NULL,
  `rank_level` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recitals`
--

CREATE TABLE `recitals` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `category` enum('scripture','creed','catechism','hymn','prayer','other') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `difficulty_level` enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `visibility` enum('all','officers','specific_ranks') NOT NULL DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recitals`
--

INSERT INTO `recitals` (`id`, `title`, `content`, `category`, `reference`, `difficulty_level`, `visibility`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'THE OBJECT OF THE BOYS BRIGADE', 'The advancement of Christ kingdom among boys and the promotion of habit of obedience, reverence, discipline, self-respect, and all that turns towards a good Christian manliness.', 'catechism', 'JOHN 11: 35', 'beginner', 'officers', 1, 1, '2026-02-13 19:12:31', '2026-02-13 19:15:58'),
(2, 'METHOD', 'The combination of religion and discipline, religious instructions at bible class and discipline at company parade.', 'catechism', '', 'beginner', 'all', 1, 1, '2026-09-13 20:47:00', '2026-09-13 20:47:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','officer','member') NOT NULL DEFAULT 'member',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@presbyterianbrigade.org', 'System Administrator', 'admin', 'active', '2026-02-09 08:49:17', '2026-02-09 08:49:17'),
(10, 'Frank Boamah', '$2y$10$wZvpFmhRAXVbtukzZrY.L.vMX1sadeazaonKWcw8bUIBqmbJwV3ru', 'frankboamah@gmail.com', 'Frank Boamah', 'officer', 'active', '2026-07-12 21:22:59', '2026-07-12 21:22:59'),
(11, 'Quinster Mensah', '$2y$10$4jITamriLyJqmUG9QovQaOT.C0JdQgUgJABa/OFCVhgLlTo8qdswq', 'quinstermeansh@gmail.com', 'Quinster Mensah', 'officer', 'active', '2026-07-12 21:24:03', '2026-07-12 21:24:03'),
(12, 'Ransford Oko Quarshie', '$2y$10$qh6P6vnq.rwnKqFIVIAlz.lSIo4aSqnCw0Qg8P7gHoFoE0KYhgpgG', 'ransford@gmail.com', 'Ransford Oko Quarshie', 'officer', 'active', '2026-07-12 21:25:22', '2026-07-12 21:25:22'),
(13, 'Kenneth Tetteh', '$2y$10$muOg1.5fc/aCMPnHXhH0x.r7dJTTKy86FfQC0yND.4AYz3MdKK7gC', 'kenneth@gmail.com', 'Kenneth Tetteh', 'officer', 'active', '2026-07-12 21:26:26', '2026-07-12 21:26:26'),
(14, 'Samuel Nyeduala Addo', '$2y$10$OdE7XwcVhupdCTnXIizR7.tR6fkEnqbtJK0x.6zBsdEYSInXFSLq6', 'papa@gmail.com', 'Samuel Nyeduala Addo', 'officer', 'active', '2026-07-12 21:27:22', '2026-07-12 21:27:22'),
(15, 'Charles Otto', '$2y$10$AYw1sSYtJ1FS3N19p2bJb.1w1dQnATLip0fR4q3G2pQvdbDrHMf2e', 'Charles@gmail.com', 'Charles Otto', 'officer', 'active', '2026-07-12 21:28:19', '2026-07-12 21:28:19'),
(16, 'Kingsley Afam', '$2y$10$aTcNsFk4jT7hWrWYO4O1h.dEIz.YcwQ.ynjxzwrnRcyH.UV5d3bbS', 'kingsley@gmail.com', 'Kingsley Afam', 'officer', 'active', '2026-08-09 21:09:21', '2026-09-20 19:44:19'),
(17, 'Florence Ampadu', '$2y$10$tcXAIqOmk5PxkaX68XKO.eG9A3gzmJAr47lxRWARK2JwzgWLYRhMW', 'florence@gmail.com', 'Florence Ampadu', 'officer', 'active', '2026-09-20 19:43:51', '2026-09-20 19:43:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`event_id`,`brigader_id`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `idx_event` (`event_id`),
  ADD KEY `idx_brigader` (`brigader_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `brigaders`
--
ALTER TABLE `brigaders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brigade_id` (`brigade_id`),
  ADD KEY `rank_id` (`rank_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_brigade_id` (`brigade_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_unit` (`unit`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_date` (`event_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_read` (`notification_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_rank_id` (`from_rank_id`),
  ADD KEY `to_rank_id` (`to_rank_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_brigader` (`brigader_id`),
  ADD KEY `idx_date` (`promotion_date`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rank_name` (`rank_name`),
  ADD UNIQUE KEY `rank_level` (`rank_level`),
  ADD KEY `idx_level` (`rank_level`);

--
-- Indexes for table `recitals`
--
ALTER TABLE `recitals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

--
-- AUTO_INCREMENT for table `brigaders`
--
ALTER TABLE `brigaders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `recitals`
--
ALTER TABLE `recitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`brigader_id`) REFERENCES `brigaders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `brigaders`
--
ALTER TABLE `brigaders`
  ADD CONSTRAINT `brigaders_ibfk_1` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `brigaders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_reads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`brigader_id`) REFERENCES `brigaders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotions_ibfk_2` FOREIGN KEY (`from_rank_id`) REFERENCES `ranks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotions_ibfk_3` FOREIGN KEY (`to_rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotions_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `recitals`
--
ALTER TABLE `recitals`
  ADD CONSTRAINT `recitals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
