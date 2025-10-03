-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 04:11 PM
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
-- Database: `brsms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(939, 7, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 04:53:26'),
(940, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 04:53:42'),
(941, 11, 'Request Rejected', 'Rejected request ID: 85', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 04:54:39'),
(942, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:10:40'),
(943, 23, '2FA Enabled', 'User enabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:10:45'),
(944, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:10:46'),
(945, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:11:25'),
(946, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:11:28'),
(947, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 11:00:18'),
(948, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 11:00:21'),
(949, 7, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 11:16:05'),
(950, 7, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 11:16:11'),
(952, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-17 02:27:28'),
(953, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:33:11'),
(954, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:35:49'),
(955, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:36:16'),
(956, 23, '2FA Disabled', 'User disabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:39:13'),
(957, 23, 'Category Added', 'Added category: dsd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:51:22'),
(958, 23, 'Category Deleted', 'Deleted category: dsd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:51:24'),
(959, 23, 'Resource Added', 'Added: dsads', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:51:53'),
(960, 23, 'Resource Edited', 'Edited: dsads', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:52:07'),
(961, 23, 'Resource Deleted', 'Deleted: dsads', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 04:52:13'),
(962, 23, 'Bulk Item Status', 'Updated 5 items (JUSTINE POGI) to \'Under Maintenance\'. IDs: 168, 169, 170, 171, 172.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:18:48'),
(963, 23, 'Bulk Item Status', 'Updated 5 items (JUSTINE POGI) to \'Borrowed\'. IDs: 168, 169, 170, 171, 172.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:19:21'),
(964, 23, 'Request Completed', 'Completed request ID: 84', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:19:39'),
(965, 23, 'Bulk Item Status', 'Updated 2 items (JUSTINE POGI) to \'Available\'. IDs: 171, 172.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:19:49'),
(966, 23, 'Resource Added', 'Added: CHAIRS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:43:59'),
(967, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:44:16'),
(968, 11, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:45:02'),
(969, 23, 'Request Approved', 'Approved request ID: 87', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:45:17'),
(970, 23, 'Request Borrowed', 'Request ID: 87 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:45:59'),
(971, 23, 'Request Completed', 'Completed request ID: 87', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:46:13'),
(972, 11, 'Resource Edited', 'Edited: JUSTINE POGI', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:46:33'),
(973, 23, 'Request Success', 'Requested 30 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:46:59'),
(974, 11, 'Request Approved', 'Approved request ID: 88', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:47:08'),
(975, 34, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:47:33'),
(976, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 13:16:12'),
(977, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 13:21:25'),
(978, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 13:33:40'),
(979, 11, 'Request Borrowed', 'Request ID: 88 for \'JUSTINE POGI\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 13:33:51'),
(980, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 13:45:54'),
(981, 23, 'Request Success', 'Requested 20 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 13:46:23'),
(982, 11, 'Request Approved', 'Approved request ID: 89', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 13:46:32'),
(983, 11, 'Request Completed', 'Completed request ID: 88', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:03:58'),
(984, 11, 'Item Status Update', 'Item ID: 174 (ADV HONDA) from \'Available\' to \'Lost\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:13:14'),
(985, 11, 'Item Status Update', 'Item ID: 174 (ADV HONDA) from \'Lost\' to \'Lost\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:13:34'),
(986, 11, 'Item Status Update', 'Item ID: 174 (ADV HONDA) from \'Lost\' to \'Available\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:13:40'),
(987, 11, 'Request Borrowed', 'Request ID: 89 for \'JUSTINE POGI\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:14:24'),
(988, 11, 'Request Completed', 'Completed request ID: 89', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:14:36'),
(989, 23, 'Item Status Update', 'Item ID: 168 (JUSTINE POGI) from \'Available\' to \'Lost\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:16:02'),
(990, 11, 'Request Success', 'Requested 100 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:18:03'),
(991, 23, 'Request Approved', 'Approved request ID: 90', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:18:07'),
(992, 23, 'Request Borrowed', 'Request ID: 90 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:18:26'),
(993, 23, 'Request Completed', 'Completed request ID: 90', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:19:17'),
(994, 23, 'Request Success', 'Requested 1 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:33:26'),
(995, 23, 'Request Cancelled', 'Requester cancelled request ID: 91', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:33:31'),
(996, 23, 'Request Success', 'Requested 5 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:34:39'),
(997, 23, 'Request Success', 'Requested 1 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:34:53'),
(998, 23, 'Request Cancelled', 'Requester cancelled request ID: 93', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:35:14'),
(999, 11, 'Request Approved', 'Approved request ID: 92', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:35:26'),
(1000, 11, 'Request Borrowed', 'Request ID: 92 for \'JUSTINE POGI\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:38:33'),
(1001, 11, 'Request Completed', 'Completed request ID: 92', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:38:47'),
(1002, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 15:11:47'),
(1003, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 15:12:41'),
(1004, 23, 'Request Success', 'Requested 7 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 15:12:46'),
(1005, 11, 'Request Approved', 'Approved request ID: 94', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 15:12:59'),
(1006, 23, 'Request Success', 'Requested 1 of ADV HONDA. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 15:14:30'),
(1007, 23, 'Request Failed', 'Requested 1 of ADV HONDA. Status: Failed. Your total requested quantity for this resource on Sep 23, 2025 (including existing requests) would exceed the total available quantity of 1.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 15:14:46'),
(1008, 11, 'Request Rejected', 'Rejected request ID: 95', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 15:14:54'),
(1009, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:03:29'),
(1010, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:08:21'),
(1011, 11, 'Request Success', 'Requested 95 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:09:08'),
(1012, 11, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:09:13'),
(1013, 34, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:09:24'),
(1014, 23, 'Request Approved', 'Approved request ID: 96', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:09:44'),
(1015, 34, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:09:57'),
(1016, 23, 'Request Rejected', 'Rejected request ID: 97', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:10:11'),
(1017, 34, 'Request Success', 'Requested 95 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:10:31'),
(1018, 23, 'Request Approved', 'Approved request ID: 98', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:10:39'),
(1019, 23, 'Request Borrowed', 'Request ID: 96 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:10:54'),
(1020, 23, 'Request Completed', 'Completed request ID: 96', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:11:15'),
(1021, 23, 'Request Cancelled', 'Requester cancelled request ID: 98', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:39:51'),
(1022, 34, 'Request Success', 'Requested 90 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:40:17'),
(1023, 34, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:40:22'),
(1024, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:40:31'),
(1025, 11, 'Request Success', 'Requested 90 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:40:50'),
(1026, 23, 'Request Approved', 'Approved request ID: 99', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:40:58'),
(1027, 23, 'Request Borrowed', 'Request ID: 99 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:41:17'),
(1028, 23, 'Request Approved', 'Approved request ID: 100', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:41:19'),
(1029, 23, 'Request Completed', 'Completed request ID: 99', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:41:43'),
(1030, 23, 'Request Quantity Updated', 'Request ID 100 for \'CHAIRS\' quantity updated to 85.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:01:17'),
(1031, 23, 'Request Quantity Updated', 'Request ID 100 for \'CHAIRS\' quantity updated to 95.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:01:38'),
(1032, 23, 'Updated Request Quantity', 'Request ID: 100 quantity updated to 85.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:19:34'),
(1033, 23, 'Updated Request Quantity', 'Request ID: 100 quantity updated to 100.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:19:57'),
(1034, 23, 'Updated Request Quantity', 'Request ID: 100 quantity updated to 85.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:20:18'),
(1035, 23, 'Request Borrowed', 'Request ID: 100 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:20:42'),
(1036, 11, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:21:07'),
(1037, 34, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:21:19'),
(1038, 34, 'Request Success', 'Requested 85 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:21:45'),
(1039, 23, 'Request Completed', 'Completed request ID: 100', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:22:05'),
(1040, 23, 'Request Approved', 'Approved request ID: 101', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:22:34'),
(1041, 23, 'Updated Request Quantity', 'Request ID: 101 quantity updated to 83.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:23:08'),
(1042, 23, 'Request Borrowed', 'Request ID: 101 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:23:26'),
(1043, 34, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:27:06'),
(1044, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:27:17'),
(1045, 11, 'Request Success', 'Requested 83 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:27:41'),
(1046, 23, 'Request Approved', 'Approved request ID: 102', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:27:49'),
(1047, 23, 'Request Completed', 'Completed request ID: 101', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:29:45'),
(1048, 23, 'Updated Request Quantity', 'Request ID: 102 quantity updated to 80.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:29:56'),
(1049, 23, 'Request Borrowed', 'Request ID: 102 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:36:29'),
(1050, 11, 'Request Success', 'Requested 80 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:40:56'),
(1051, 23, 'Request Approved', 'Approved request ID: 103', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:41:16'),
(1052, 23, 'Request Cancelled', 'Requester cancelled request ID: 103', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:42:01'),
(1053, 23, 'Request Completed', 'Completed request ID: 102', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:44:05'),
(1054, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 11:38:51'),
(1055, 23, 'Request Success', 'Requested 1 of ADV HONDA. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:26:56'),
(1056, 34, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:27:26'),
(1057, 34, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:27:34'),
(1058, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:27:44'),
(1059, 11, 'Request Approved', 'Approved request ID: 104', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:27:53'),
(1060, 23, 'Request Success', 'Requested 1 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:39:37'),
(1061, 11, 'Request Approved', 'Approved request ID: 105', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:39:50'),
(1062, 11, 'Request Borrowed', 'Request ID: 105 for \'JUSTINE POGI\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:42:21'),
(1063, 23, 'Request Success', 'Requested 1 of ADV HONDA. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 12:59:54'),
(1064, 11, 'Request Approved', 'Approved request ID: 106', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 13:00:05'),
(1065, 11, 'Request Borrowed', 'Request ID: 106 for \'ADV HONDA\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 13:00:23'),
(1066, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 13:57:36'),
(1067, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:01:47'),
(1068, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:03:12'),
(1069, 11, 'Request Success', 'Requested 80 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:03:38'),
(1070, 23, 'Request Approved', 'Approved request ID: 107', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:03:46'),
(1071, 11, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:04:11'),
(1072, 23, 'Request Approved', 'Approved request ID: 108', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:04:19'),
(1073, 23, 'Request Cancelled', 'Requester cancelled request ID: 108', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:04:36'),
(1074, 11, 'Request Success', 'Requested 80 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:05:05'),
(1075, 23, 'Request Approved', 'Approved request ID: 109', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:05:10'),
(1076, 23, 'Request Borrowed', 'Request ID: 107 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:05:54'),
(1077, 23, 'Request Completed', 'Completed request ID: 107', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:06:04'),
(1078, 23, 'Updated Request Quantity', 'Request ID: 109 quantity updated to 78.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:06:14'),
(1079, 23, 'Request Borrowed', 'Request ID: 109 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:06:19'),
(1080, 23, 'Request Completed', 'Completed request ID: 109', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:06:29'),
(1081, 23, 'Item Status Update', 'Item ID: 185 (CHAIRS) from \'Lost\' to \'Available\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:06:41'),
(1082, 23, 'Item Status Update', 'Item ID: 186 (CHAIRS) from \'Lost\' to \'Available\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:06:45'),
(1083, 23, 'Bulk Item Status', 'Updated 2 items (CHAIRS) to \'Lost\'. IDs: 202, 204.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:06:56'),
(1084, 23, 'Request Success', 'Requested 1 of ADV HONDA. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:39:26'),
(1085, 11, 'Request Approved', 'Approved request ID: 110', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:39:35'),
(1086, 11, 'Request Cancelled', 'Requester cancelled request ID: 110', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:41:17'),
(1087, 11, 'Request Cancelled', 'Requester cancelled request ID: 94', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 03:41:19'),
(1088, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 06:31:06'),
(1089, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 06:31:10'),
(1090, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 06:31:30'),
(1091, 34, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:27:44'),
(1092, 34, 'Request Success', 'Requested 4 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:28:03'),
(1093, 23, 'Request Approved', 'Approved request ID: 111', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:28:09'),
(1094, 23, 'Request Borrowed', 'Request ID: 111 for \'JUSTINE POGI\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:28:24'),
(1095, 23, 'Request Completed', 'Completed request ID: 111', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:28:40'),
(1096, 34, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:29:36'),
(1097, 23, 'Request Approved', 'Approved request ID: 112', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:29:51'),
(1098, 23, 'Request Borrowed', 'Request ID: 112 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:30:02'),
(1099, 23, 'Request Completed', 'Completed request ID: 112', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:33:15'),
(1100, 34, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:35:44'),
(1101, 23, 'Request Approved', 'Approved request ID: 113', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:35:56'),
(1102, 23, 'Request Borrowed', 'Request ID: 113 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:36:22'),
(1103, 23, 'Request Completed', 'Completed request ID: 113', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 07:44:09'),
(1104, 23, 'Item Status Update', 'Item ID: 168 (JUSTINE POGI) from \'Lost\' to \'Available\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:52:36'),
(1105, 23, 'Bulk Item Status', 'Updated 20 items (CHAIRS) to \'Available\'. IDs: 187, 188, 189, 190, 191....', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:52:48'),
(1106, 34, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:53:05'),
(1107, 34, 'Bulk Item Status', 'Updated 5 items (TABLES) to \'Available\'. IDs: 121, 122, 123, 124, 125.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:53:16'),
(1108, 34, 'Bulk Item Status', 'Updated 2 items (TABLES) to \'Available\'. IDs: 119, 120.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:53:27'),
(1109, 34, 'Item Status Update', 'Item ID: 1 (TENTS) from \'Borrowed\' to \'Available\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:53:34'),
(1110, 34, 'Bulk Item Status', 'Updated 5 items (Flashlights) to \'Available\'. IDs: 14, 15, 16, 17, 18.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:54:19'),
(1111, 34, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:55:12'),
(1112, 23, 'Request Rejected', 'Rejected request ID: 114', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:55:25'),
(1113, 34, 'Request Success', 'Requested 100 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:55:40'),
(1114, 23, 'Request Approved', 'Approved request ID: 115', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:55:45'),
(1115, 23, 'Request Borrowed', 'Request ID: 115 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:57:25'),
(1116, 23, 'Request Completed', 'Completed request ID: 115', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 08:58:01'),
(1117, 23, 'Item Status Update', 'Item ID: 168 (JUSTINE POGI) from \'Available\' to \'Lost\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 09:03:27'),
(1118, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 15:49:07'),
(1119, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:08:32'),
(1120, 11, 'Request Success', 'Requested 1 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:08:56'),
(1121, 23, 'Request Approved', 'Approved request ID: 116', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:11:02'),
(1122, 23, 'Request Borrowed', 'Request ID: 116 for \'JUSTINE POGI\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:11:34'),
(1123, 23, 'Request Completed', 'Completed request ID: 116', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:12:50'),
(1124, 11, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:14:41'),
(1125, 23, 'Request Approved', 'Approved request ID: 117', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:14:46'),
(1126, 23, 'Request Borrowed', 'Request ID: 117 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:15:00'),
(1127, 23, 'Request Completed', 'Completed request ID: 117', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:15:59'),
(1128, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:20:20'),
(1129, 34, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:20:43'),
(1130, 34, 'Request Success', 'Requested 100 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:21:06'),
(1131, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:21:26'),
(1132, 23, 'Request Approved', 'Approved request ID: 118', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:21:38'),
(1133, 11, 'Request Success', 'Requested 1 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:22:08'),
(1134, 23, 'Request Approved', 'Approved request ID: 119', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:22:16'),
(1135, 23, 'Request Borrowed', 'Request ID: 118 for \'CHAIRS\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:22:38'),
(1136, 23, 'Request Cancelled', 'Requester cancelled request ID: 119', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:22:43'),
(1137, 11, 'Request Success', 'Requested 100 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:23:08'),
(1138, 23, 'Request Approved', 'Approved request ID: 120', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:23:13'),
(1139, 23, 'Request Completed', 'Completed request ID: 118', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:24:02'),
(1140, 23, 'Updated Request Quantity', 'Request ID: 120 quantity updated to 95.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:24:16'),
(1141, 23, 'Request Cancelled', 'Requester cancelled request ID: 120', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 16:24:38'),
(1142, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 03:06:58'),
(1143, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 03:07:09'),
(1144, 11, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 03:07:18'),
(1145, 11, 'Request Success', 'Requested 95 of CHAIRS. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 03:07:49'),
(1146, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 03:08:07'),
(1147, 23, 'Request Approved', 'Approved request ID: 121', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 03:08:11'),
(1148, 23, 'Request Cancelled', 'Requester cancelled request ID: 121', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:16:29'),
(1149, 23, 'Request Success', 'Requested 7 of JUSTINE POGI. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:17:02'),
(1150, 11, 'Request Approved', 'Approved request ID: 122', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:17:07'),
(1151, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:25:16'),
(1152, 23, 'Request Success', 'Requested 1 of ADV HONDA. Status: Success. Request submitted successfully and is pending approval.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:25:41'),
(1153, 11, 'Request Approved', 'Approved request ID: 123', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:25:51'),
(1154, 11, 'Item Status Update', 'Item ID: 174 (ADV HONDA) from \'Borrowed\' to \'Available\'.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:30:54'),
(1155, 11, 'Request Borrowed', 'Request ID: 123 for \'ADV HONDA\' marked as Borrowed.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:31:08'),
(1156, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:05:30'),
(1157, 23, 'Password Changed', 'User changed their password.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:10:14'),
(1158, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:10:18'),
(1159, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:10:29'),
(1160, 23, '2FA Enabled', 'User enabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:22:53'),
(1161, 23, '2FA Disabled', 'User disabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:23:01'),
(1162, 23, 'Profile Updated', 'User updated their profile information (Full Name: \'Dan Derek Bumotad 2\', Username: \'bumotadd@gmail.com\').', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:23:10');
INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1163, 23, 'Profile Updated', 'User updated their profile information (Full Name: \'Dan Derek Bumotad\', Username: \'bumotadd@gmail.com\').', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:23:14'),
(1164, 23, '2FA Enabled', 'User enabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:30:11'),
(1165, 23, '2FA Disabled', 'User disabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:30:14'),
(1166, 23, 'Password Changed', 'User changed their password.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:30:48'),
(1167, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:30:54'),
(1168, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:31:04'),
(1169, 23, 'Password Changed', 'User changed their password.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:33:55'),
(1170, 23, '2FA Enabled', 'User enabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:34:03'),
(1171, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:34:06'),
(1172, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:34:54'),
(1173, 23, '2FA Disabled', 'User disabled Two-Factor Authentication.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:36:06'),
(1174, 23, 'User Logout', 'User logged out of the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 14:03:43'),
(1175, 23, 'User Login', 'User logged into the system.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 14:09:27');

-- --------------------------------------------------------

--
-- Table structure for table `barangays`
--

CREATE TABLE `barangays` (
  `brgy_id` int(11) NOT NULL,
  `brgy_name` varchar(100) NOT NULL,
  `brgy_phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangays`
--

INSERT INTO `barangays` (`brgy_id`, `brgy_name`, `brgy_phone_number`) VALUES
(1, 'BUGO', '+639706106825'),
(2, 'PUERTO', '+639913144290'),
(3, 'AGUSAN', NULL),
(4, 'BALUARTE', NULL),
(5, 'POBLACION', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `req_id` int(11) NOT NULL,
  `req_user_id` int(11) NOT NULL,
  `req_brgy_id` int(11) NOT NULL,
  `res_id` int(11) NOT NULL,
  `res_brgy_id` int(11) NOT NULL,
  `req_quantity` int(11) NOT NULL,
  `req_date` date NOT NULL,
  `return_date` date NOT NULL,
  `req_status` enum('Pending','Approved','Borrowed','Rejected','Completed','Cancelled') DEFAULT NULL,
  `borrow_timestamp` datetime DEFAULT NULL,
  `return_timestamp` datetime DEFAULT NULL,
  `req_purpose` text NOT NULL,
  `req_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `req_contact_number` varchar(15) NOT NULL,
  `returned_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`req_id`, `req_user_id`, `req_brgy_id`, `res_id`, `res_brgy_id`, `req_quantity`, `req_date`, `return_date`, `req_status`, `borrow_timestamp`, `return_timestamp`, `req_purpose`, `req_timestamp`, `req_contact_number`, `returned_quantity`) VALUES
(114, 34, 3, 18, 1, 1, '2025-09-26', '2025-09-26', 'Rejected', NULL, NULL, 'FFDSFSDFSDFDFSDSDS', '2025-09-26 08:55:11', '09069332775', 0),
(115, 34, 3, 18, 1, 100, '2025-09-26', '2025-09-26', 'Completed', '2025-09-26 16:57:25', '2025-09-26 16:58:01', 'SADSADSAASSADSADSADSAD', '2025-09-26 08:55:40', '09069332775', 0),
(116, 11, 2, 14, 1, 1, '2025-09-27', '2025-09-27', 'Completed', '2025-09-27 00:11:34', '2025-09-27 00:12:50', 'dsaadsasdsadsadsadasasddassdadsaasdad', '2025-09-26 16:08:55', '09069332775', 0),
(117, 11, 2, 18, 1, 1, '2025-09-27', '2025-09-30', 'Completed', '2025-09-27 00:15:00', '2025-09-27 00:15:59', 'saadssaasdaddadassdsa', '2025-09-26 16:14:41', '09069332775', 0),
(118, 34, 3, 18, 1, 100, '2025-09-27', '2025-09-27', 'Completed', '2025-09-27 00:22:38', '2025-09-27 00:24:02', 'sadsadssasasdasaddsa', '2025-09-26 16:21:06', '09069332775', 0),
(119, 11, 2, 18, 1, 1, '2025-09-28', '2025-09-29', 'Cancelled', NULL, NULL, 'sadsadsdsadsadsa', '2025-09-26 16:22:08', '09069332775', 0),
(120, 11, 2, 18, 1, 95, '2025-09-28', '2025-09-30', 'Cancelled', NULL, NULL, 'adasdasdsasaddsadsa', '2025-09-26 16:23:08', '09069332775', 0),
(121, 11, 2, 18, 1, 95, '2025-09-27', '2025-09-28', 'Cancelled', NULL, NULL, 'dasdasdsadsadsadsaasd', '2025-09-27 03:07:49', '09069333277', 0),
(122, 23, 1, 8, 2, 7, '2025-09-27', '2025-09-27', 'Approved', NULL, NULL, 'dsasdaadssadsadadsad', '2025-09-27 04:17:02', '09069332775', 0),
(123, 23, 1, 16, 2, 1, '2025-09-27', '2025-09-27', 'Borrowed', '2025-09-27 12:31:08', NULL, 'dsadsadsadasadsadsa', '2025-09-27 04:25:41', '09069332775', 0);

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `res_id` int(11) NOT NULL,
  `res_photo` varchar(255) DEFAULT NULL,
  `res_name` varchar(100) DEFAULT NULL,
  `res_description` text DEFAULT NULL,
  `res_category_id` int(11) DEFAULT NULL,
  `res_quantity` int(11) DEFAULT NULL,
  `brgy_id` int(11) DEFAULT NULL,
  `is_bulk` tinyint(1) DEFAULT 0,
  `res_status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`res_id`, `res_photo`, `res_name`, `res_description`, `res_category_id`, `res_quantity`, `brgy_id`, `is_bulk`, `res_status`) VALUES
(1, '68ba74ee6d9b6.jpg', 'TENTS', 'NA', NULL, 10, 3, 1, 'Available'),
(3, '68ba75b1279ba.jpg', 'Flashlights', 'na', NULL, 5, 3, 1, 'Available'),
(4, '68ba75d6b9fb8.png', 'CHAIRS', 'NA', NULL, 100, 3, 1, 'Available'),
(5, '68ba76e9d6c3f.jpg', 'TABLES', 'NA', NULL, 10, 3, 1, 'Available'),
(6, '68baab4ad03f5.jpg', 'Ambulance', 'na', NULL, 3, 3, 1, 'Available'),
(7, '68baab676eea3.jpg', 'FIRST AID KIT', 'NA', NULL, 5, 3, 1, 'Available'),
(8, '68bacc207677c.png', 'JUSTINE POGI', 'NA', 3, 30, 2, 1, 'Available'),
(14, '68bfcf0da4d7c.png', 'JUSTINE POGI', 'na', 13, 5, 1, 1, 'Available'),
(16, '68c8da92baf38.jpg', 'ADV HONDA', 'SARAP TANGENA', 3, 1, 2, 1, 'Available'),
(18, '68d23398823bc.png', 'CHAIRS', 'MONOBLOCKS', 13, 100, 1, 1, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `resource_categories`
--

CREATE TABLE `resource_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `brgy_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resource_categories`
--

INSERT INTO `resource_categories` (`category_id`, `category_name`, `brgy_id`, `created_at`) VALUES
(3, 'HELL', 2, '2025-09-05 11:40:01'),
(13, 'BARANGAY EQUIPMENTS', 1, '2025-09-16 02:39:39');

-- --------------------------------------------------------

--
-- Table structure for table `resource_items`
--

CREATE TABLE `resource_items` (
  `item_id` int(11) NOT NULL,
  `res_id` int(11) NOT NULL,
  `item_status` enum('Available','Borrowed','Under Maintenance','Lost') NOT NULL DEFAULT 'Available',
  `current_req_id` int(11) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `serial_number` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resource_items`
--

INSERT INTO `resource_items` (`item_id`, `res_id`, `item_status`, `current_req_id`, `qr_code`, `last_updated`, `serial_number`) VALUES
(1, 1, 'Available', NULL, 'qr_item_1.png', '2025-09-26 08:53:34', 'TENTS-001-0001'),
(2, 1, 'Available', NULL, 'qr_item_2.png', '2025-09-14 12:10:40', 'TENTS-001-0002'),
(3, 1, 'Available', NULL, 'qr_item_3.png', '2025-09-14 12:10:40', 'TENTS-001-0003'),
(4, 1, 'Available', NULL, 'qr_item_4.png', '2025-09-14 12:10:40', 'TENTS-001-0004'),
(5, 1, 'Available', NULL, 'qr_item_5.png', '2025-09-14 12:10:40', 'TENTS-001-0005'),
(6, 1, 'Available', NULL, 'qr_item_6.png', '2025-09-14 12:10:40', 'TENTS-001-0006'),
(7, 1, 'Available', NULL, 'qr_item_7.png', '2025-09-14 12:10:40', 'TENTS-001-0007'),
(8, 1, 'Available', NULL, 'qr_item_8.png', '2025-09-14 12:10:40', 'TENTS-001-0008'),
(9, 1, 'Available', NULL, 'qr_item_9.png', '2025-09-14 12:10:40', 'TENTS-001-0009'),
(10, 1, 'Available', NULL, 'qr_item_10.png', '2025-09-14 12:10:40', 'TENTS-001-0010'),
(14, 3, 'Available', NULL, 'qr_item_14.png', '2025-09-26 08:54:19', 'FLASH-003-0014'),
(15, 3, 'Available', NULL, 'qr_item_15.png', '2025-09-26 08:54:19', 'FLASH-003-0015'),
(16, 3, 'Available', NULL, 'qr_item_16.png', '2025-09-26 08:54:19', 'FLASH-003-0016'),
(17, 3, 'Available', NULL, 'qr_item_17.png', '2025-09-26 08:54:19', 'FLASH-003-0017'),
(18, 3, 'Available', NULL, 'qr_item_18.png', '2025-09-26 08:54:19', 'FLASH-003-0018'),
(19, 4, 'Available', NULL, 'qr_item_19.png', '2025-09-16 03:46:22', 'CHAIR-004-0019'),
(20, 4, 'Available', NULL, 'qr_item_20.png', '2025-09-16 03:46:22', 'CHAIR-004-0020'),
(21, 4, 'Available', NULL, 'qr_item_21.png', '2025-09-16 03:46:22', 'CHAIR-004-0021'),
(22, 4, 'Available', NULL, 'qr_item_22.png', '2025-09-16 03:46:22', 'CHAIR-004-0022'),
(23, 4, 'Available', NULL, 'qr_item_23.png', '2025-09-16 03:46:22', 'CHAIR-004-0023'),
(24, 4, 'Available', NULL, 'qr_item_24.png', '2025-09-16 03:46:22', 'CHAIR-004-0024'),
(25, 4, 'Available', NULL, 'qr_item_25.png', '2025-09-16 03:46:22', 'CHAIR-004-0025'),
(26, 4, 'Available', NULL, 'qr_item_26.png', '2025-09-16 03:46:22', 'CHAIR-004-0026'),
(27, 4, 'Available', NULL, 'qr_item_27.png', '2025-09-16 03:46:22', 'CHAIR-004-0027'),
(28, 4, 'Available', NULL, 'qr_item_28.png', '2025-09-16 03:46:22', 'CHAIR-004-0028'),
(29, 4, 'Available', NULL, 'qr_item_29.png', '2025-09-16 03:46:22', 'CHAIR-004-0029'),
(30, 4, 'Available', NULL, 'qr_item_30.png', '2025-09-16 03:46:22', 'CHAIR-004-0030'),
(31, 4, 'Available', NULL, 'qr_item_31.png', '2025-09-16 03:46:22', 'CHAIR-004-0031'),
(32, 4, 'Available', NULL, 'qr_item_32.png', '2025-09-16 03:46:22', 'CHAIR-004-0032'),
(33, 4, 'Available', NULL, 'qr_item_33.png', '2025-09-16 03:46:22', 'CHAIR-004-0033'),
(34, 4, 'Available', NULL, 'qr_item_34.png', '2025-09-16 03:46:22', 'CHAIR-004-0034'),
(35, 4, 'Available', NULL, 'qr_item_35.png', '2025-09-16 03:46:22', 'CHAIR-004-0035'),
(36, 4, 'Available', NULL, 'qr_item_36.png', '2025-09-16 03:46:22', 'CHAIR-004-0036'),
(37, 4, 'Available', NULL, 'qr_item_37.png', '2025-09-16 03:46:22', 'CHAIR-004-0037'),
(38, 4, 'Available', NULL, 'qr_item_38.png', '2025-09-16 03:46:22', 'CHAIR-004-0038'),
(39, 4, 'Available', NULL, 'qr_item_39.png', '2025-09-16 03:46:22', 'CHAIR-004-0039'),
(40, 4, 'Available', NULL, 'qr_item_40.png', '2025-09-16 03:46:22', 'CHAIR-004-0040'),
(41, 4, 'Available', NULL, 'qr_item_41.png', '2025-09-16 03:46:22', 'CHAIR-004-0041'),
(42, 4, 'Available', NULL, 'qr_item_42.png', '2025-09-16 03:46:22', 'CHAIR-004-0042'),
(43, 4, 'Available', NULL, 'qr_item_43.png', '2025-09-16 03:46:22', 'CHAIR-004-0043'),
(44, 4, 'Available', NULL, 'qr_item_44.png', '2025-09-16 03:46:22', 'CHAIR-004-0044'),
(45, 4, 'Available', NULL, 'qr_item_45.png', '2025-09-16 03:46:22', 'CHAIR-004-0045'),
(46, 4, 'Available', NULL, 'qr_item_46.png', '2025-09-16 03:46:22', 'CHAIR-004-0046'),
(47, 4, 'Available', NULL, 'qr_item_47.png', '2025-09-16 03:46:22', 'CHAIR-004-0047'),
(48, 4, 'Available', NULL, 'qr_item_48.png', '2025-09-16 03:46:22', 'CHAIR-004-0048'),
(49, 4, 'Available', NULL, 'qr_item_49.png', '2025-09-16 03:46:22', 'CHAIR-004-0049'),
(50, 4, 'Available', NULL, 'qr_item_50.png', '2025-09-16 03:46:22', 'CHAIR-004-0050'),
(51, 4, 'Available', NULL, 'qr_item_51.png', '2025-09-16 03:46:22', 'CHAIR-004-0051'),
(52, 4, 'Available', NULL, 'qr_item_52.png', '2025-09-16 03:46:22', 'CHAIR-004-0052'),
(53, 4, 'Available', NULL, 'qr_item_53.png', '2025-09-16 03:46:22', 'CHAIR-004-0053'),
(54, 4, 'Available', NULL, 'qr_item_54.png', '2025-09-16 03:46:22', 'CHAIR-004-0054'),
(55, 4, 'Available', NULL, 'qr_item_55.png', '2025-09-16 03:46:22', 'CHAIR-004-0055'),
(56, 4, 'Available', NULL, 'qr_item_56.png', '2025-09-16 03:46:22', 'CHAIR-004-0056'),
(57, 4, 'Available', NULL, 'qr_item_57.png', '2025-09-16 03:46:22', 'CHAIR-004-0057'),
(58, 4, 'Available', NULL, 'qr_item_58.png', '2025-09-16 03:46:22', 'CHAIR-004-0058'),
(59, 4, 'Available', NULL, 'qr_item_59.png', '2025-09-16 03:46:22', 'CHAIR-004-0059'),
(60, 4, 'Available', NULL, 'qr_item_60.png', '2025-09-16 03:46:22', 'CHAIR-004-0060'),
(61, 4, 'Available', NULL, 'qr_item_61.png', '2025-09-16 03:46:22', 'CHAIR-004-0061'),
(62, 4, 'Available', NULL, 'qr_item_62.png', '2025-09-16 03:46:22', 'CHAIR-004-0062'),
(63, 4, 'Available', NULL, 'qr_item_63.png', '2025-09-16 03:46:22', 'CHAIR-004-0063'),
(64, 4, 'Available', NULL, 'qr_item_64.png', '2025-09-16 03:46:22', 'CHAIR-004-0064'),
(65, 4, 'Available', NULL, 'qr_item_65.png', '2025-09-16 03:46:22', 'CHAIR-004-0065'),
(66, 4, 'Available', NULL, 'qr_item_66.png', '2025-09-16 03:46:22', 'CHAIR-004-0066'),
(67, 4, 'Available', NULL, 'qr_item_67.png', '2025-09-16 03:46:22', 'CHAIR-004-0067'),
(68, 4, 'Available', NULL, 'qr_item_68.png', '2025-09-16 03:46:22', 'CHAIR-004-0068'),
(69, 4, 'Available', NULL, 'qr_item_69.png', '2025-09-16 03:46:22', 'CHAIR-004-0069'),
(70, 4, 'Available', NULL, 'qr_item_70.png', '2025-09-16 03:46:22', 'CHAIR-004-0070'),
(71, 4, 'Available', NULL, 'qr_item_71.png', '2025-09-16 03:46:22', 'CHAIR-004-0071'),
(72, 4, 'Available', NULL, 'qr_item_72.png', '2025-09-16 03:46:22', 'CHAIR-004-0072'),
(73, 4, 'Available', NULL, 'qr_item_73.png', '2025-09-16 03:46:22', 'CHAIR-004-0073'),
(74, 4, 'Available', NULL, 'qr_item_74.png', '2025-09-16 03:46:22', 'CHAIR-004-0074'),
(75, 4, 'Available', NULL, 'qr_item_75.png', '2025-09-16 03:46:22', 'CHAIR-004-0075'),
(76, 4, 'Available', NULL, 'qr_item_76.png', '2025-09-16 03:46:22', 'CHAIR-004-0076'),
(77, 4, 'Available', NULL, 'qr_item_77.png', '2025-09-16 03:46:22', 'CHAIR-004-0077'),
(78, 4, 'Available', NULL, 'qr_item_78.png', '2025-09-16 03:46:22', 'CHAIR-004-0078'),
(79, 4, 'Available', NULL, 'qr_item_79.png', '2025-09-16 03:46:22', 'CHAIR-004-0079'),
(80, 4, 'Available', NULL, 'qr_item_80.png', '2025-09-16 03:46:22', 'CHAIR-004-0080'),
(81, 4, 'Available', NULL, 'qr_item_81.png', '2025-09-16 03:46:22', 'CHAIR-004-0081'),
(82, 4, 'Available', NULL, 'qr_item_82.png', '2025-09-16 03:46:22', 'CHAIR-004-0082'),
(83, 4, 'Available', NULL, 'qr_item_83.png', '2025-09-16 03:46:22', 'CHAIR-004-0083'),
(84, 4, 'Available', NULL, 'qr_item_84.png', '2025-09-16 03:46:22', 'CHAIR-004-0084'),
(85, 4, 'Available', NULL, 'qr_item_85.png', '2025-09-16 03:46:22', 'CHAIR-004-0085'),
(86, 4, 'Available', NULL, 'qr_item_86.png', '2025-09-16 03:46:22', 'CHAIR-004-0086'),
(87, 4, 'Available', NULL, 'qr_item_87.png', '2025-09-16 03:46:22', 'CHAIR-004-0087'),
(88, 4, 'Available', NULL, 'qr_item_88.png', '2025-09-16 03:46:22', 'CHAIR-004-0088'),
(89, 4, 'Available', NULL, 'qr_item_89.png', '2025-09-16 03:46:22', 'CHAIR-004-0089'),
(90, 4, 'Available', NULL, 'qr_item_90.png', '2025-09-16 03:46:22', 'CHAIR-004-0090'),
(91, 4, 'Available', NULL, 'qr_item_91.png', '2025-09-16 03:46:22', 'CHAIR-004-0091'),
(92, 4, 'Available', NULL, 'qr_item_92.png', '2025-09-16 03:46:22', 'CHAIR-004-0092'),
(93, 4, 'Available', NULL, 'qr_item_93.png', '2025-09-16 03:46:22', 'CHAIR-004-0093'),
(94, 4, 'Available', NULL, 'qr_item_94.png', '2025-09-16 03:46:22', 'CHAIR-004-0094'),
(95, 4, 'Available', NULL, 'qr_item_95.png', '2025-09-16 03:46:22', 'CHAIR-004-0095'),
(96, 4, 'Available', NULL, 'qr_item_96.png', '2025-09-16 03:46:22', 'CHAIR-004-0096'),
(97, 4, 'Available', NULL, 'qr_item_97.png', '2025-09-16 03:46:22', 'CHAIR-004-0097'),
(98, 4, 'Available', NULL, 'qr_item_98.png', '2025-09-16 03:46:22', 'CHAIR-004-0098'),
(99, 4, 'Available', NULL, 'qr_item_99.png', '2025-09-16 03:46:22', 'CHAIR-004-0099'),
(100, 4, 'Available', NULL, 'qr_item_100.png', '2025-09-16 03:46:22', 'CHAIR-004-0100'),
(101, 4, 'Available', NULL, 'qr_item_101.png', '2025-09-16 03:46:22', 'CHAIR-004-0101'),
(102, 4, 'Available', NULL, 'qr_item_102.png', '2025-09-16 03:46:22', 'CHAIR-004-0102'),
(103, 4, 'Available', NULL, 'qr_item_103.png', '2025-09-16 03:46:22', 'CHAIR-004-0103'),
(104, 4, 'Available', NULL, 'qr_item_104.png', '2025-09-16 03:46:22', 'CHAIR-004-0104'),
(105, 4, 'Available', NULL, 'qr_item_105.png', '2025-09-16 03:46:22', 'CHAIR-004-0105'),
(106, 4, 'Available', NULL, 'qr_item_106.png', '2025-09-16 03:46:22', 'CHAIR-004-0106'),
(107, 4, 'Available', NULL, 'qr_item_107.png', '2025-09-16 03:46:22', 'CHAIR-004-0107'),
(108, 4, 'Available', NULL, 'qr_item_108.png', '2025-09-16 03:46:22', 'CHAIR-004-0108'),
(109, 4, 'Available', NULL, 'qr_item_109.png', '2025-09-16 03:46:22', 'CHAIR-004-0109'),
(110, 4, 'Available', NULL, 'qr_item_110.png', '2025-09-16 03:46:22', 'CHAIR-004-0110'),
(111, 4, 'Available', NULL, 'qr_item_111.png', '2025-09-16 03:46:22', 'CHAIR-004-0111'),
(112, 4, 'Available', NULL, 'qr_item_112.png', '2025-09-16 03:46:22', 'CHAIR-004-0112'),
(113, 4, 'Available', NULL, 'qr_item_113.png', '2025-09-16 03:46:22', 'CHAIR-004-0113'),
(114, 4, 'Available', NULL, 'qr_item_114.png', '2025-09-16 03:46:22', 'CHAIR-004-0114'),
(115, 4, 'Available', NULL, 'qr_item_115.png', '2025-09-16 03:46:22', 'CHAIR-004-0115'),
(116, 4, 'Available', NULL, 'qr_item_116.png', '2025-09-16 03:46:22', 'CHAIR-004-0116'),
(117, 4, 'Available', NULL, 'qr_item_117.png', '2025-09-16 03:46:22', 'CHAIR-004-0117'),
(118, 4, 'Available', NULL, 'qr_item_118.png', '2025-09-16 03:46:22', 'CHAIR-004-0118'),
(119, 5, 'Available', NULL, 'qr_item_119.png', '2025-09-26 08:53:27', 'TABLE-005-0119'),
(120, 5, 'Available', NULL, 'qr_item_120.png', '2025-09-26 08:53:27', 'TABLE-005-0120'),
(121, 5, 'Available', NULL, 'qr_item_121.png', '2025-09-26 08:53:16', 'TABLE-005-0121'),
(122, 5, 'Available', NULL, 'qr_item_122.png', '2025-09-26 08:53:16', 'TABLE-005-0122'),
(123, 5, 'Available', NULL, 'qr_item_123.png', '2025-09-26 08:53:16', 'TABLE-005-0123'),
(124, 5, 'Available', NULL, 'qr_item_124.png', '2025-09-26 08:53:16', 'TABLE-005-0124'),
(125, 5, 'Available', NULL, 'qr_item_125.png', '2025-09-26 08:53:16', 'TABLE-005-0125'),
(126, 5, 'Available', NULL, 'qr_item_126.png', '2025-09-10 02:21:19', 'TABLE-005-0126'),
(127, 5, 'Available', NULL, 'qr_item_127.png', '2025-09-10 02:06:42', 'TABLE-005-0127'),
(128, 5, 'Available', NULL, 'qr_item_128.png', '2025-09-10 02:06:42', 'TABLE-005-0128'),
(129, 6, 'Available', NULL, 'qr_item_129.png', '2025-09-05 09:20:11', 'AMBUL-006-0129'),
(130, 6, 'Available', NULL, 'qr_item_130.png', '2025-09-05 09:20:11', 'AMBUL-006-0130'),
(131, 6, 'Available', NULL, 'qr_item_131.png', '2025-09-05 09:20:11', 'AMBUL-006-0131'),
(132, 7, 'Available', NULL, 'qr_item_132.png', '2025-09-15 08:15:00', 'FIRST-007-0132'),
(133, 7, 'Available', NULL, 'qr_item_133.png', '2025-09-15 08:15:00', 'FIRST-007-0133'),
(134, 7, 'Available', NULL, 'qr_item_134.png', '2025-09-15 08:15:00', 'FIRST-007-0134'),
(135, 7, 'Available', NULL, 'qr_item_135.png', '2025-09-15 08:15:00', 'FIRST-007-0135'),
(136, 7, 'Available', NULL, 'qr_item_136.png', '2025-09-15 08:15:00', 'FIRST-007-0136'),
(137, 8, 'Under Maintenance', NULL, 'qr_item_137.png', '2025-09-23 14:03:58', 'JUSTI-008-0137'),
(138, 8, 'Lost', NULL, 'qr_item_138.png', '2025-09-23 14:03:58', 'JUSTI-008-0138'),
(139, 8, 'Lost', NULL, 'qr_item_139.png', '2025-09-23 14:03:58', 'JUSTI-008-0139'),
(140, 8, 'Lost', NULL, 'qr_item_140.png', '2025-09-23 14:14:36', 'JUSTI-008-0140'),
(141, 8, 'Lost', NULL, 'qr_item_141.png', '2025-09-23 14:14:36', 'JUSTI-008-0141'),
(142, 8, 'Lost', NULL, 'qr_item_142.png', '2025-09-23 14:14:36', 'JUSTI-008-0142'),
(143, 8, 'Lost', NULL, 'qr_item_143.png', '2025-09-23 14:14:36', 'JUSTI-008-0143'),
(144, 8, 'Lost', NULL, 'qr_item_144.png', '2025-09-23 14:14:36', 'JUSTI-008-0144'),
(145, 8, 'Lost', NULL, 'qr_item_145.png', '2025-09-23 14:14:36', 'JUSTI-008-0145'),
(146, 8, 'Lost', NULL, 'qr_item_146.png', '2025-09-23 14:14:36', 'JUSTI-008-0146'),
(163, 8, 'Lost', NULL, 'qr_item_163.png', '2025-09-23 14:14:36', 'JUSTI-008-0163'),
(164, 8, 'Lost', NULL, 'qr_item_164.png', '2025-09-23 14:14:36', 'JUSTI-008-0164'),
(165, 8, 'Lost', NULL, 'qr_item_165.png', '2025-09-23 14:14:36', 'JUSTI-008-0165'),
(166, 8, 'Lost', NULL, 'qr_item_166.png', '2025-09-23 14:14:36', 'JUSTI-008-0166'),
(167, 8, 'Lost', NULL, 'qr_item_167.png', '2025-09-23 14:14:36', 'JUSTI-008-0167'),
(168, 14, 'Lost', NULL, 'qr_item_168.png', '2025-09-26 09:03:27', 'JUSTI-014-0168'),
(169, 14, 'Available', NULL, 'qr_item_169.png', '2025-09-26 07:28:40', 'JUSTI-014-0169'),
(170, 14, 'Available', NULL, 'qr_item_170.png', '2025-09-26 07:28:40', 'JUSTI-014-0170'),
(171, 14, 'Available', NULL, 'qr_item_171.png', '2025-09-26 07:28:40', 'JUSTI-014-0171'),
(172, 14, 'Available', NULL, 'qr_item_172.png', '2025-09-26 16:12:50', 'JUSTI-014-0172'),
(174, 16, 'Borrowed', 123, 'qr_item_174.png', '2025-09-27 04:31:08', 'ADVHO-016-0174'),
(185, 18, 'Lost', NULL, 'qr_item_185.png', '2025-09-26 16:24:02', 'CHAIR-018-0185'),
(186, 18, 'Lost', NULL, 'qr_item_186.png', '2025-09-26 16:24:02', 'CHAIR-018-0186'),
(187, 18, 'Lost', NULL, 'qr_item_187.png', '2025-09-26 16:24:02', 'CHAIR-018-0187'),
(188, 18, 'Lost', NULL, 'qr_item_188.png', '2025-09-26 16:24:02', 'CHAIR-018-0188'),
(189, 18, 'Under Maintenance', NULL, 'qr_item_189.png', '2025-09-26 16:24:02', 'CHAIR-018-0189'),
(190, 18, 'Available', NULL, 'qr_item_190.png', '2025-09-26 16:24:02', 'CHAIR-018-0190'),
(191, 18, 'Available', NULL, 'qr_item_191.png', '2025-09-26 16:24:02', 'CHAIR-018-0191'),
(192, 18, 'Available', NULL, 'qr_item_192.png', '2025-09-26 16:24:02', 'CHAIR-018-0192'),
(193, 18, 'Available', NULL, 'qr_item_193.png', '2025-09-26 16:24:02', 'CHAIR-018-0193'),
(194, 18, 'Available', NULL, 'qr_item_194.png', '2025-09-26 16:24:02', 'CHAIR-018-0194'),
(195, 18, 'Available', NULL, 'qr_item_195.png', '2025-09-26 16:24:02', 'CHAIR-018-0195'),
(196, 18, 'Available', NULL, 'qr_item_196.png', '2025-09-26 16:24:02', 'CHAIR-018-0196'),
(197, 18, 'Available', NULL, 'qr_item_197.png', '2025-09-26 16:24:02', 'CHAIR-018-0197'),
(198, 18, 'Available', NULL, 'qr_item_198.png', '2025-09-26 16:24:02', 'CHAIR-018-0198'),
(199, 18, 'Available', NULL, 'qr_item_199.png', '2025-09-26 16:24:02', 'CHAIR-018-0199'),
(200, 18, 'Available', NULL, 'qr_item_200.png', '2025-09-26 16:24:02', 'CHAIR-018-0200'),
(201, 18, 'Available', NULL, 'qr_item_201.png', '2025-09-26 16:24:02', 'CHAIR-018-0201'),
(202, 18, 'Available', NULL, 'qr_item_202.png', '2025-09-26 16:24:02', 'CHAIR-018-0202'),
(203, 18, 'Available', NULL, 'qr_item_203.png', '2025-09-26 16:24:02', 'CHAIR-018-0203'),
(204, 18, 'Available', NULL, 'qr_item_204.png', '2025-09-26 16:24:02', 'CHAIR-018-0204'),
(205, 18, 'Available', NULL, 'qr_item_205.png', '2025-09-26 16:24:02', 'CHAIR-018-0205'),
(206, 18, 'Available', NULL, 'qr_item_206.png', '2025-09-26 16:24:02', 'CHAIR-018-0206'),
(207, 18, 'Available', NULL, 'qr_item_207.png', '2025-09-26 16:24:02', 'CHAIR-018-0207'),
(208, 18, 'Available', NULL, 'qr_item_208.png', '2025-09-26 16:24:02', 'CHAIR-018-0208'),
(209, 18, 'Available', NULL, 'qr_item_209.png', '2025-09-26 16:24:02', 'CHAIR-018-0209'),
(210, 18, 'Available', NULL, 'qr_item_210.png', '2025-09-26 16:24:02', 'CHAIR-018-0210'),
(211, 18, 'Available', NULL, 'qr_item_211.png', '2025-09-26 16:24:02', 'CHAIR-018-0211'),
(212, 18, 'Available', NULL, 'qr_item_212.png', '2025-09-26 16:24:02', 'CHAIR-018-0212'),
(213, 18, 'Available', NULL, 'qr_item_213.png', '2025-09-26 16:24:02', 'CHAIR-018-0213'),
(214, 18, 'Available', NULL, 'qr_item_214.png', '2025-09-26 16:24:02', 'CHAIR-018-0214'),
(215, 18, 'Available', NULL, 'qr_item_215.png', '2025-09-26 16:24:02', 'CHAIR-018-0215'),
(216, 18, 'Available', NULL, 'qr_item_216.png', '2025-09-26 16:24:02', 'CHAIR-018-0216'),
(217, 18, 'Available', NULL, 'qr_item_217.png', '2025-09-26 16:24:02', 'CHAIR-018-0217'),
(218, 18, 'Available', NULL, 'qr_item_218.png', '2025-09-26 16:24:02', 'CHAIR-018-0218'),
(219, 18, 'Available', NULL, 'qr_item_219.png', '2025-09-26 16:24:02', 'CHAIR-018-0219'),
(220, 18, 'Available', NULL, 'qr_item_220.png', '2025-09-26 16:24:02', 'CHAIR-018-0220'),
(221, 18, 'Available', NULL, 'qr_item_221.png', '2025-09-26 16:24:02', 'CHAIR-018-0221'),
(222, 18, 'Available', NULL, 'qr_item_222.png', '2025-09-26 16:24:02', 'CHAIR-018-0222'),
(223, 18, 'Available', NULL, 'qr_item_223.png', '2025-09-26 16:24:02', 'CHAIR-018-0223'),
(224, 18, 'Available', NULL, 'qr_item_224.png', '2025-09-26 16:24:02', 'CHAIR-018-0224'),
(225, 18, 'Available', NULL, 'qr_item_225.png', '2025-09-26 16:24:02', 'CHAIR-018-0225'),
(226, 18, 'Available', NULL, 'qr_item_226.png', '2025-09-26 16:24:02', 'CHAIR-018-0226'),
(227, 18, 'Available', NULL, 'qr_item_227.png', '2025-09-26 16:24:02', 'CHAIR-018-0227'),
(228, 18, 'Available', NULL, 'qr_item_228.png', '2025-09-26 16:24:02', 'CHAIR-018-0228'),
(229, 18, 'Available', NULL, 'qr_item_229.png', '2025-09-26 16:24:02', 'CHAIR-018-0229'),
(230, 18, 'Available', NULL, 'qr_item_230.png', '2025-09-26 16:24:02', 'CHAIR-018-0230'),
(231, 18, 'Available', NULL, 'qr_item_231.png', '2025-09-26 16:24:02', 'CHAIR-018-0231'),
(232, 18, 'Available', NULL, 'qr_item_232.png', '2025-09-26 16:24:02', 'CHAIR-018-0232'),
(233, 18, 'Available', NULL, 'qr_item_233.png', '2025-09-26 16:24:02', 'CHAIR-018-0233'),
(234, 18, 'Available', NULL, 'qr_item_234.png', '2025-09-26 16:24:02', 'CHAIR-018-0234'),
(235, 18, 'Available', NULL, 'qr_item_235.png', '2025-09-26 16:24:02', 'CHAIR-018-0235'),
(236, 18, 'Available', NULL, 'qr_item_236.png', '2025-09-26 16:24:02', 'CHAIR-018-0236'),
(237, 18, 'Available', NULL, 'qr_item_237.png', '2025-09-26 16:24:02', 'CHAIR-018-0237'),
(238, 18, 'Available', NULL, 'qr_item_238.png', '2025-09-26 16:24:02', 'CHAIR-018-0238'),
(239, 18, 'Available', NULL, 'qr_item_239.png', '2025-09-26 16:24:02', 'CHAIR-018-0239'),
(240, 18, 'Available', NULL, 'qr_item_240.png', '2025-09-26 16:24:02', 'CHAIR-018-0240'),
(241, 18, 'Available', NULL, 'qr_item_241.png', '2025-09-26 16:24:02', 'CHAIR-018-0241'),
(242, 18, 'Available', NULL, 'qr_item_242.png', '2025-09-26 16:24:02', 'CHAIR-018-0242'),
(243, 18, 'Available', NULL, 'qr_item_243.png', '2025-09-26 16:24:02', 'CHAIR-018-0243'),
(244, 18, 'Available', NULL, 'qr_item_244.png', '2025-09-26 16:24:02', 'CHAIR-018-0244'),
(245, 18, 'Available', NULL, 'qr_item_245.png', '2025-09-26 16:24:02', 'CHAIR-018-0245'),
(246, 18, 'Available', NULL, 'qr_item_246.png', '2025-09-26 16:24:02', 'CHAIR-018-0246'),
(247, 18, 'Available', NULL, 'qr_item_247.png', '2025-09-26 16:24:02', 'CHAIR-018-0247'),
(248, 18, 'Available', NULL, 'qr_item_248.png', '2025-09-26 16:24:02', 'CHAIR-018-0248'),
(249, 18, 'Available', NULL, 'qr_item_249.png', '2025-09-26 16:24:02', 'CHAIR-018-0249'),
(250, 18, 'Available', NULL, 'qr_item_250.png', '2025-09-26 16:24:02', 'CHAIR-018-0250'),
(251, 18, 'Available', NULL, 'qr_item_251.png', '2025-09-26 16:24:02', 'CHAIR-018-0251'),
(252, 18, 'Available', NULL, 'qr_item_252.png', '2025-09-26 16:24:02', 'CHAIR-018-0252'),
(253, 18, 'Available', NULL, 'qr_item_253.png', '2025-09-26 16:24:02', 'CHAIR-018-0253'),
(254, 18, 'Available', NULL, 'qr_item_254.png', '2025-09-26 16:24:02', 'CHAIR-018-0254'),
(255, 18, 'Available', NULL, 'qr_item_255.png', '2025-09-26 16:24:02', 'CHAIR-018-0255'),
(256, 18, 'Available', NULL, 'qr_item_256.png', '2025-09-26 16:24:02', 'CHAIR-018-0256'),
(257, 18, 'Available', NULL, 'qr_item_257.png', '2025-09-26 16:24:02', 'CHAIR-018-0257'),
(258, 18, 'Available', NULL, 'qr_item_258.png', '2025-09-26 16:24:02', 'CHAIR-018-0258'),
(259, 18, 'Available', NULL, 'qr_item_259.png', '2025-09-26 16:24:02', 'CHAIR-018-0259'),
(260, 18, 'Available', NULL, 'qr_item_260.png', '2025-09-26 16:24:02', 'CHAIR-018-0260'),
(261, 18, 'Available', NULL, 'qr_item_261.png', '2025-09-26 16:24:02', 'CHAIR-018-0261'),
(262, 18, 'Available', NULL, 'qr_item_262.png', '2025-09-26 16:24:02', 'CHAIR-018-0262'),
(263, 18, 'Available', NULL, 'qr_item_263.png', '2025-09-26 16:24:02', 'CHAIR-018-0263'),
(264, 18, 'Available', NULL, 'qr_item_264.png', '2025-09-26 16:24:02', 'CHAIR-018-0264'),
(265, 18, 'Available', NULL, 'qr_item_265.png', '2025-09-26 16:24:02', 'CHAIR-018-0265'),
(266, 18, 'Available', NULL, 'qr_item_266.png', '2025-09-26 16:24:02', 'CHAIR-018-0266'),
(267, 18, 'Available', NULL, 'qr_item_267.png', '2025-09-26 16:24:02', 'CHAIR-018-0267'),
(268, 18, 'Available', NULL, 'qr_item_268.png', '2025-09-26 16:24:02', 'CHAIR-018-0268'),
(269, 18, 'Available', NULL, 'qr_item_269.png', '2025-09-26 16:24:02', 'CHAIR-018-0269'),
(270, 18, 'Available', NULL, 'qr_item_270.png', '2025-09-26 16:24:02', 'CHAIR-018-0270'),
(271, 18, 'Available', NULL, 'qr_item_271.png', '2025-09-26 16:24:02', 'CHAIR-018-0271'),
(272, 18, 'Available', NULL, 'qr_item_272.png', '2025-09-26 16:24:02', 'CHAIR-018-0272'),
(273, 18, 'Available', NULL, 'qr_item_273.png', '2025-09-26 16:24:02', 'CHAIR-018-0273'),
(274, 18, 'Available', NULL, 'qr_item_274.png', '2025-09-26 16:24:02', 'CHAIR-018-0274'),
(275, 18, 'Available', NULL, 'qr_item_275.png', '2025-09-26 16:24:02', 'CHAIR-018-0275'),
(276, 18, 'Available', NULL, 'qr_item_276.png', '2025-09-26 16:24:02', 'CHAIR-018-0276'),
(277, 18, 'Available', NULL, 'qr_item_277.png', '2025-09-26 16:24:02', 'CHAIR-018-0277'),
(278, 18, 'Available', NULL, 'qr_item_278.png', '2025-09-26 16:24:02', 'CHAIR-018-0278'),
(279, 18, 'Available', NULL, 'qr_item_279.png', '2025-09-26 16:24:02', 'CHAIR-018-0279'),
(280, 18, 'Available', NULL, 'qr_item_280.png', '2025-09-26 16:24:02', 'CHAIR-018-0280'),
(281, 18, 'Available', NULL, 'qr_item_281.png', '2025-09-26 16:24:02', 'CHAIR-018-0281'),
(282, 18, 'Available', NULL, 'qr_item_282.png', '2025-09-26 16:24:02', 'CHAIR-018-0282'),
(283, 18, 'Available', NULL, 'qr_item_283.png', '2025-09-26 16:24:02', 'CHAIR-018-0283'),
(284, 18, 'Available', NULL, 'qr_item_284.png', '2025-09-26 16:24:02', 'CHAIR-018-0284'),
(285, 8, 'Lost', NULL, 'qr_item_285.png', '2025-09-23 14:14:36', 'JUSTI-008-0285'),
(286, 8, 'Lost', NULL, 'qr_item_286.png', '2025-09-23 14:14:36', 'JUSTI-008-0286'),
(287, 8, 'Lost', NULL, 'qr_item_287.png', '2025-09-23 14:14:36', 'JUSTI-008-0287'),
(288, 8, 'Lost', NULL, 'qr_item_288.png', '2025-09-23 14:14:36', 'JUSTI-008-0288'),
(289, 8, 'Lost', NULL, 'qr_item_289.png', '2025-09-23 14:14:36', 'JUSTI-008-0289'),
(290, 8, 'Lost', NULL, 'qr_item_290.png', '2025-09-23 14:14:36', 'JUSTI-008-0290'),
(291, 8, 'Lost', NULL, 'qr_item_291.png', '2025-09-23 14:14:36', 'JUSTI-008-0291'),
(292, 8, 'Lost', NULL, 'qr_item_292.png', '2025-09-23 14:14:36', 'JUSTI-008-0292'),
(293, 8, 'Borrowed', NULL, 'qr_item_293.png', '2025-09-25 12:42:21', 'JUSTI-008-0293'),
(294, 8, 'Available', NULL, 'qr_item_294.png', '2025-09-23 14:38:47', 'JUSTI-008-0294'),
(295, 8, 'Available', NULL, 'qr_item_295.png', '2025-09-23 14:38:47', 'JUSTI-008-0295'),
(296, 8, 'Available', NULL, 'qr_item_296.png', '2025-09-23 14:38:47', 'JUSTI-008-0296'),
(297, 8, 'Available', NULL, 'qr_item_297.png', '2025-09-23 14:03:58', 'JUSTI-008-0297'),
(298, 8, 'Available', NULL, 'qr_item_298.png', '2025-09-23 14:03:58', 'JUSTI-008-0298'),
(299, 8, 'Available', NULL, 'qr_item_299.png', '2025-09-23 14:38:47', 'JUSTI-008-0299');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `req_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `return_condition` longtext NOT NULL,
  `brgy_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `req_id`, `return_date`, `return_condition`, `brgy_id`) VALUES
(53, 115, '2025-09-26', '[{\"item_id\":185,\"serial_number\":\"CHAIR-018-0185\",\"condition\":\"Minor Scratches\",\"new_status\":\"Available\"},{\"item_id\":186,\"serial_number\":\"CHAIR-018-0186\",\"condition\":\"Minor Scratches\",\"new_status\":\"Available\"},{\"item_id\":187,\"serial_number\":\"CHAIR-018-0187\",\"condition\":\"Minor Scratches\",\"new_status\":\"Available\"},{\"item_id\":188,\"serial_number\":\"CHAIR-018-0188\",\"condition\":\"Minor Scratches\",\"new_status\":\"Available\"},{\"item_id\":189,\"serial_number\":\"CHAIR-018-0189\",\"condition\":\"Minor Scratches\",\"new_status\":\"Available\"},{\"item_id\":190,\"serial_number\":\"CHAIR-018-0190\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":191,\"serial_number\":\"CHAIR-018-0191\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":192,\"serial_number\":\"CHAIR-018-0192\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":193,\"serial_number\":\"CHAIR-018-0193\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":194,\"serial_number\":\"CHAIR-018-0194\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":195,\"serial_number\":\"CHAIR-018-0195\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":196,\"serial_number\":\"CHAIR-018-0196\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":197,\"serial_number\":\"CHAIR-018-0197\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":198,\"serial_number\":\"CHAIR-018-0198\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":199,\"serial_number\":\"CHAIR-018-0199\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":200,\"serial_number\":\"CHAIR-018-0200\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":201,\"serial_number\":\"CHAIR-018-0201\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":202,\"serial_number\":\"CHAIR-018-0202\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":203,\"serial_number\":\"CHAIR-018-0203\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":204,\"serial_number\":\"CHAIR-018-0204\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":205,\"serial_number\":\"CHAIR-018-0205\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":206,\"serial_number\":\"CHAIR-018-0206\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":207,\"serial_number\":\"CHAIR-018-0207\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":208,\"serial_number\":\"CHAIR-018-0208\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":209,\"serial_number\":\"CHAIR-018-0209\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":210,\"serial_number\":\"CHAIR-018-0210\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":211,\"serial_number\":\"CHAIR-018-0211\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":212,\"serial_number\":\"CHAIR-018-0212\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":213,\"serial_number\":\"CHAIR-018-0213\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":214,\"serial_number\":\"CHAIR-018-0214\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":215,\"serial_number\":\"CHAIR-018-0215\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":216,\"serial_number\":\"CHAIR-018-0216\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":217,\"serial_number\":\"CHAIR-018-0217\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":218,\"serial_number\":\"CHAIR-018-0218\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":219,\"serial_number\":\"CHAIR-018-0219\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":220,\"serial_number\":\"CHAIR-018-0220\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":221,\"serial_number\":\"CHAIR-018-0221\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":222,\"serial_number\":\"CHAIR-018-0222\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":223,\"serial_number\":\"CHAIR-018-0223\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":224,\"serial_number\":\"CHAIR-018-0224\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":225,\"serial_number\":\"CHAIR-018-0225\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":226,\"serial_number\":\"CHAIR-018-0226\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":227,\"serial_number\":\"CHAIR-018-0227\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":228,\"serial_number\":\"CHAIR-018-0228\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":229,\"serial_number\":\"CHAIR-018-0229\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":230,\"serial_number\":\"CHAIR-018-0230\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":231,\"serial_number\":\"CHAIR-018-0231\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":232,\"serial_number\":\"CHAIR-018-0232\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":233,\"serial_number\":\"CHAIR-018-0233\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":234,\"serial_number\":\"CHAIR-018-0234\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":235,\"serial_number\":\"CHAIR-018-0235\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":236,\"serial_number\":\"CHAIR-018-0236\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":237,\"serial_number\":\"CHAIR-018-0237\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":238,\"serial_number\":\"CHAIR-018-0238\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":239,\"serial_number\":\"CHAIR-018-0239\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":240,\"serial_number\":\"CHAIR-018-0240\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":241,\"serial_number\":\"CHAIR-018-0241\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":242,\"serial_number\":\"CHAIR-018-0242\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":243,\"serial_number\":\"CHAIR-018-0243\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":244,\"serial_number\":\"CHAIR-018-0244\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":245,\"serial_number\":\"CHAIR-018-0245\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":246,\"serial_number\":\"CHAIR-018-0246\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":247,\"serial_number\":\"CHAIR-018-0247\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":248,\"serial_number\":\"CHAIR-018-0248\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":249,\"serial_number\":\"CHAIR-018-0249\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":250,\"serial_number\":\"CHAIR-018-0250\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":251,\"serial_number\":\"CHAIR-018-0251\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":252,\"serial_number\":\"CHAIR-018-0252\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":253,\"serial_number\":\"CHAIR-018-0253\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":254,\"serial_number\":\"CHAIR-018-0254\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":255,\"serial_number\":\"CHAIR-018-0255\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":256,\"serial_number\":\"CHAIR-018-0256\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":257,\"serial_number\":\"CHAIR-018-0257\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":258,\"serial_number\":\"CHAIR-018-0258\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":259,\"serial_number\":\"CHAIR-018-0259\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":260,\"serial_number\":\"CHAIR-018-0260\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":261,\"serial_number\":\"CHAIR-018-0261\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":262,\"serial_number\":\"CHAIR-018-0262\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":263,\"serial_number\":\"CHAIR-018-0263\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":264,\"serial_number\":\"CHAIR-018-0264\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":265,\"serial_number\":\"CHAIR-018-0265\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":266,\"serial_number\":\"CHAIR-018-0266\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":267,\"serial_number\":\"CHAIR-018-0267\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":268,\"serial_number\":\"CHAIR-018-0268\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":269,\"serial_number\":\"CHAIR-018-0269\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":270,\"serial_number\":\"CHAIR-018-0270\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":271,\"serial_number\":\"CHAIR-018-0271\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":272,\"serial_number\":\"CHAIR-018-0272\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":273,\"serial_number\":\"CHAIR-018-0273\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":274,\"serial_number\":\"CHAIR-018-0274\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":275,\"serial_number\":\"CHAIR-018-0275\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":276,\"serial_number\":\"CHAIR-018-0276\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":277,\"serial_number\":\"CHAIR-018-0277\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":278,\"serial_number\":\"CHAIR-018-0278\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":279,\"serial_number\":\"CHAIR-018-0279\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":280,\"serial_number\":\"CHAIR-018-0280\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":281,\"serial_number\":\"CHAIR-018-0281\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":282,\"serial_number\":\"CHAIR-018-0282\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":283,\"serial_number\":\"CHAIR-018-0283\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":284,\"serial_number\":\"CHAIR-018-0284\",\"condition\":\"Good\",\"new_status\":\"Available\"}]', 1),
(54, 116, '2025-09-26', '[{\"item_id\":172,\"serial_number\":\"JUSTI-014-0172\",\"condition\":\"Good\",\"new_status\":\"Available\"}]', 1),
(55, 117, '2025-09-26', '[{\"item_id\":185,\"serial_number\":\"CHAIR-018-0185\",\"condition\":\"Good\",\"new_status\":\"Available\"}]', 1),
(56, 118, '2025-09-26', '[{\"item_id\":185,\"serial_number\":\"CHAIR-018-0185\",\"condition\":\"Lost\",\"new_status\":\"Lost\"},{\"item_id\":186,\"serial_number\":\"CHAIR-018-0186\",\"condition\":\"Lost\",\"new_status\":\"Lost\"},{\"item_id\":187,\"serial_number\":\"CHAIR-018-0187\",\"condition\":\"Lost\",\"new_status\":\"Lost\"},{\"item_id\":188,\"serial_number\":\"CHAIR-018-0188\",\"condition\":\"Lost\",\"new_status\":\"Lost\"},{\"item_id\":189,\"serial_number\":\"CHAIR-018-0189\",\"condition\":\"Damaged\",\"new_status\":\"Under Maintenance\"},{\"item_id\":190,\"serial_number\":\"CHAIR-018-0190\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":191,\"serial_number\":\"CHAIR-018-0191\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":192,\"serial_number\":\"CHAIR-018-0192\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":193,\"serial_number\":\"CHAIR-018-0193\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":194,\"serial_number\":\"CHAIR-018-0194\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":195,\"serial_number\":\"CHAIR-018-0195\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":196,\"serial_number\":\"CHAIR-018-0196\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":197,\"serial_number\":\"CHAIR-018-0197\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":198,\"serial_number\":\"CHAIR-018-0198\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":199,\"serial_number\":\"CHAIR-018-0199\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":200,\"serial_number\":\"CHAIR-018-0200\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":201,\"serial_number\":\"CHAIR-018-0201\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":202,\"serial_number\":\"CHAIR-018-0202\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":203,\"serial_number\":\"CHAIR-018-0203\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":204,\"serial_number\":\"CHAIR-018-0204\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":205,\"serial_number\":\"CHAIR-018-0205\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":206,\"serial_number\":\"CHAIR-018-0206\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":207,\"serial_number\":\"CHAIR-018-0207\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":208,\"serial_number\":\"CHAIR-018-0208\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":209,\"serial_number\":\"CHAIR-018-0209\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":210,\"serial_number\":\"CHAIR-018-0210\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":211,\"serial_number\":\"CHAIR-018-0211\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":212,\"serial_number\":\"CHAIR-018-0212\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":213,\"serial_number\":\"CHAIR-018-0213\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":214,\"serial_number\":\"CHAIR-018-0214\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":215,\"serial_number\":\"CHAIR-018-0215\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":216,\"serial_number\":\"CHAIR-018-0216\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":217,\"serial_number\":\"CHAIR-018-0217\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":218,\"serial_number\":\"CHAIR-018-0218\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":219,\"serial_number\":\"CHAIR-018-0219\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":220,\"serial_number\":\"CHAIR-018-0220\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":221,\"serial_number\":\"CHAIR-018-0221\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":222,\"serial_number\":\"CHAIR-018-0222\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":223,\"serial_number\":\"CHAIR-018-0223\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":224,\"serial_number\":\"CHAIR-018-0224\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":225,\"serial_number\":\"CHAIR-018-0225\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":226,\"serial_number\":\"CHAIR-018-0226\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":227,\"serial_number\":\"CHAIR-018-0227\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":228,\"serial_number\":\"CHAIR-018-0228\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":229,\"serial_number\":\"CHAIR-018-0229\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":230,\"serial_number\":\"CHAIR-018-0230\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":231,\"serial_number\":\"CHAIR-018-0231\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":232,\"serial_number\":\"CHAIR-018-0232\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":233,\"serial_number\":\"CHAIR-018-0233\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":234,\"serial_number\":\"CHAIR-018-0234\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":235,\"serial_number\":\"CHAIR-018-0235\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":236,\"serial_number\":\"CHAIR-018-0236\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":237,\"serial_number\":\"CHAIR-018-0237\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":238,\"serial_number\":\"CHAIR-018-0238\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":239,\"serial_number\":\"CHAIR-018-0239\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":240,\"serial_number\":\"CHAIR-018-0240\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":241,\"serial_number\":\"CHAIR-018-0241\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":242,\"serial_number\":\"CHAIR-018-0242\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":243,\"serial_number\":\"CHAIR-018-0243\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":244,\"serial_number\":\"CHAIR-018-0244\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":245,\"serial_number\":\"CHAIR-018-0245\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":246,\"serial_number\":\"CHAIR-018-0246\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":247,\"serial_number\":\"CHAIR-018-0247\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":248,\"serial_number\":\"CHAIR-018-0248\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":249,\"serial_number\":\"CHAIR-018-0249\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":250,\"serial_number\":\"CHAIR-018-0250\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":251,\"serial_number\":\"CHAIR-018-0251\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":252,\"serial_number\":\"CHAIR-018-0252\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":253,\"serial_number\":\"CHAIR-018-0253\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":254,\"serial_number\":\"CHAIR-018-0254\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":255,\"serial_number\":\"CHAIR-018-0255\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":256,\"serial_number\":\"CHAIR-018-0256\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":257,\"serial_number\":\"CHAIR-018-0257\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":258,\"serial_number\":\"CHAIR-018-0258\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":259,\"serial_number\":\"CHAIR-018-0259\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":260,\"serial_number\":\"CHAIR-018-0260\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":261,\"serial_number\":\"CHAIR-018-0261\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":262,\"serial_number\":\"CHAIR-018-0262\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":263,\"serial_number\":\"CHAIR-018-0263\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":264,\"serial_number\":\"CHAIR-018-0264\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":265,\"serial_number\":\"CHAIR-018-0265\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":266,\"serial_number\":\"CHAIR-018-0266\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":267,\"serial_number\":\"CHAIR-018-0267\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":268,\"serial_number\":\"CHAIR-018-0268\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":269,\"serial_number\":\"CHAIR-018-0269\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":270,\"serial_number\":\"CHAIR-018-0270\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":271,\"serial_number\":\"CHAIR-018-0271\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":272,\"serial_number\":\"CHAIR-018-0272\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":273,\"serial_number\":\"CHAIR-018-0273\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":274,\"serial_number\":\"CHAIR-018-0274\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":275,\"serial_number\":\"CHAIR-018-0275\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":276,\"serial_number\":\"CHAIR-018-0276\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":277,\"serial_number\":\"CHAIR-018-0277\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":278,\"serial_number\":\"CHAIR-018-0278\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":279,\"serial_number\":\"CHAIR-018-0279\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":280,\"serial_number\":\"CHAIR-018-0280\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":281,\"serial_number\":\"CHAIR-018-0281\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":282,\"serial_number\":\"CHAIR-018-0282\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":283,\"serial_number\":\"CHAIR-018-0283\",\"condition\":\"Good\",\"new_status\":\"Available\"},{\"item_id\":284,\"serial_number\":\"CHAIR-018-0284\",\"condition\":\"Good\",\"new_status\":\"Available\"}]', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_full_name` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('barangay_official','secretary','captain','superadmin') NOT NULL,
  `brgy_id` int(11) DEFAULT NULL,
  `user_photo` varchar(255) DEFAULT 'uploads/default_profile.png',
  `brgy_name` varchar(255) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `twofa_secret` varchar(255) DEFAULT NULL,
  `twofa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_full_name`, `username`, `password`, `role`, `brgy_id`, `user_photo`, `brgy_name`, `otp`, `otp_expires_at`, `twofa_secret`, `twofa_enabled`, `two_factor_enabled`) VALUES
(7, 'Super Admin', 'barangaysms@gmail.com', '$2y$10$aQLZRUU0C2qP6Khz7Huvu.lJQqinN9CiITzJ4NeN92RntUDh/6aEm', 'superadmin', NULL, 'uploads/default_profile.png', NULL, NULL, NULL, NULL, 0, 0),
(11, 'Benjoe Cabonegro', 'benjoeraboy@gmail.com', '$2y$10$Mwd6CD/bpNjjJsNTx2wjNuojMFUnW1yKJykdN.erq2F5A7KIxWBza', 'captain', 2, 'uploads/user_photos/68ac530ca0017_puerto.jpg', 'PUERTO', NULL, NULL, NULL, 0, 0),
(23, 'Dan Derek Bumotad', 'bumotadd@gmail.com', '$2y$10$60HRvBFjSXliwMgzXS2.6O69BYJDbe9xkY/9TwNzTwm8P10lipEPK', 'captain', 1, 'uploads/profile_pictures/profile_68c7e3b7a60a0.png', 'BUGO', NULL, NULL, NULL, 0, 0),
(34, 'Alexis Buscado', 'alexisbuscado@gmail.com', '$2y$10$RcURzf9PsXn2.v4T1p79mOS69yMJuW.7D/IvC2Lv0itGd8rv2actC', 'captain', 3, 'uploads/user_photos/68ac5343972e8_agusan.jpg', 'AGUSAN', NULL, NULL, NULL, 0, 0),
(36, 'John Rommel Butanas', 'johnrommelbutanas14@gmail.com', '$2y$10$AN57C3yp8QJJAK4OU00reuYoYMkfh449l.h/AX0YLGyxbTrzOR3ZG', 'secretary', 5, 'uploads/user_photos/68bcf7c147e99_BRSMS-removebg-preview.jpg', 'POBLACION', NULL, NULL, NULL, 0, 0),
(42, 'Jheyd Hailey Tagapulot', 'jheydhailey@gmail.com', '$2y$10$nkVXIs1r/8BhRYd3CTDo8OvZInaUOGCP67JAAXWiIoPo5hQBMFJi.', 'secretary', 4, 'uploads/user_photos/68bcf7d0f2f5a_ChatGPT Image Aug 30, 2025, 12_13_20 PM.png', 'BALUARTE', NULL, NULL, NULL, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `barangays`
--
ALTER TABLE `barangays`
  ADD PRIMARY KEY (`brgy_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`req_id`),
  ADD KEY `req_brgy_id` (`req_brgy_id`),
  ADD KEY `res_brgy_id` (`res_brgy_id`),
  ADD KEY `requests_ibfk_2` (`res_id`),
  ADD KEY `requests_ibfk_1` (`req_user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`res_id`),
  ADD KEY `fk_resource_category` (`res_category_id`);

--
-- Indexes for table `resource_categories`
--
ALTER TABLE `resource_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `UQ_category_name_brgy_id` (`category_name`,`brgy_id`),
  ADD UNIQUE KEY `category_name` (`category_name`,`brgy_id`),
  ADD KEY `brgy_id` (`brgy_id`);

--
-- Indexes for table `resource_items`
--
ALTER TABLE `resource_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `res_id` (`res_id`),
  ADD KEY `fk_current_req_id` (`current_req_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `brgy_id` (`brgy_id`),
  ADD KEY `fk_returns_req` (`req_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_brgy` (`brgy_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1176;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `res_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `resource_categories`
--
ALTER TABLE `resource_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `resource_items`
--
ALTER TABLE `resource_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=300;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`req_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`res_id`) REFERENCES `resources` (`res_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`req_brgy_id`) REFERENCES `barangays` (`brgy_id`),
  ADD CONSTRAINT `requests_ibfk_4` FOREIGN KEY (`res_brgy_id`) REFERENCES `barangays` (`brgy_id`);

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `fk_resource_category` FOREIGN KEY (`res_category_id`) REFERENCES `resource_categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `resource_categories`
--
ALTER TABLE `resource_categories`
  ADD CONSTRAINT `resource_categories_ibfk_1` FOREIGN KEY (`brgy_id`) REFERENCES `users` (`brgy_id`) ON DELETE CASCADE;

--
-- Constraints for table `resource_items`
--
ALTER TABLE `resource_items`
  ADD CONSTRAINT `fk_current_req_id` FOREIGN KEY (`current_req_id`) REFERENCES `requests` (`req_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resource_items_ibfk_1` FOREIGN KEY (`res_id`) REFERENCES `resources` (`res_id`) ON DELETE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `fk_returns_req` FOREIGN KEY (`req_id`) REFERENCES `requests` (`req_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`brgy_id`) REFERENCES `barangays` (`brgy_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_brgy` FOREIGN KEY (`brgy_id`) REFERENCES `barangays` (`brgy_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
