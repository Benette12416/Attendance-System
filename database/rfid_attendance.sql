-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2026 at 12:59 PM
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
-- Database: `rfid_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `fullname`, `created_at`) VALUES
(1, 'admin', '$2y$10$uPJSsFW1K4Cs1Y1dKDVgxefxrEBhx1HHvGv2EAmTvBJG6L6sJwwMe', 'Administrator', '2026-07-17 11:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_logs`
--

CREATE TABLE `admin_login_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `status` varchar(50) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rfid_uid` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `rfid_uid`, `date`, `time_in`, `time_out`, `status`, `created_at`) VALUES
(26, 19, '8D:92:C9:06', '2026-07-23', '18:43:40', '18:55:00', 'Present', '2026-07-23 10:43:40'),
(27, 20, '69:35:F4:DE', '2026-07-23', '18:45:11', NULL, 'Present', '2026-07-23 10:45:11'),
(28, 21, '47:95:66:06', '2026-07-23', '18:45:23', NULL, 'Present', '2026-07-23 10:45:23'),
(29, 22, '58:57:3D:FF', '2026-07-23', '18:49:14', NULL, 'Present', '2026-07-23 10:49:14'),
(30, 25, '68:4D:86:FF', '2026-07-23', '18:54:39', NULL, 'Present', '2026-07-23 10:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `uid` varchar(20) NOT NULL,
  `scan_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `student_id`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(27, 15, NULL, 'Official Attendance Notification: Time Out', '', 'Success', '2026-07-18 16:53:22'),
(28, 7, NULL, 'Official Attendance Notification: Time In', '', 'Success', '2026-07-18 17:10:31'),
(29, 7, NULL, 'Official Attendance Notification: Time Out', '', 'Success', '2026-07-18 17:11:12'),
(30, 16, NULL, 'Official Attendance Notification: Time In', '', 'Success', '2026-07-18 17:19:02'),
(31, 16, NULL, 'Official Attendance Notification: Time Out', '', 'Success', '2026-07-18 17:20:30'),
(32, 13, NULL, 'Official Attendance Notification: Time In', '', 'Success', '2026-07-18 17:49:59'),
(33, 13, NULL, 'Official Attendance Notification: Time Out', '', 'Success', '2026-07-18 17:50:07'),
(34, 17, NULL, 'Official Attendance Notification: Time In', '', 'Success', '2026-07-18 17:50:28'),
(35, 17, NULL, 'Official Attendance Notification: Time Out', '', 'Success', '2026-07-18 17:50:35'),
(36, 17, NULL, 'Official Attendance Notification: Time Out', '', 'Success', '2026-07-18 17:55:52'),
(37, 17, NULL, 'Official Attendance Notification: Time Out', '', 'Success', '2026-07-18 17:56:07'),
(38, 16, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-22 14:28:09'),
(39, 16, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 09:59:34'),
(40, 19, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:22:14'),
(41, 19, NULL, 'Official Attendance Notification: TIME OUT', '', 'Success', '2026-07-23 10:24:34'),
(42, 20, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:28:46'),
(43, 20, NULL, 'Official Attendance Notification: TIME OUT', '', 'Success', '2026-07-23 10:30:58'),
(44, 21, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:34:54'),
(45, 21, NULL, 'Official Attendance Notification: TIME OUT', '', 'Success', '2026-07-23 10:36:18'),
(46, 19, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:43:43'),
(47, 20, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:45:14'),
(48, 21, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:45:26'),
(49, 22, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:49:17'),
(50, 25, NULL, 'Official Attendance Notification: TIME IN', '', 'Success', '2026-07-23 10:54:43'),
(51, 19, NULL, 'Official Attendance Notification: TIME OUT', '', 'Success', '2026-07-23 10:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `school_name` varchar(150) DEFAULT 'My School',
  `late_time` time DEFAULT '08:00:00',
  `sms_provider` varchar(50) DEFAULT 'semaphore',
  `sms_base_url` varchar(255) DEFAULT NULL,
  `sms_api_key` varchar(255) DEFAULT '',
  `sms_sender_name` varchar(50) DEFAULT '',
  `sms_enabled` tinyint(1) DEFAULT 0,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `ip_whitelist` text DEFAULT NULL,
  `brevo_api_key` varchar(255) DEFAULT NULL,
  `email_from_address` varchar(255) DEFAULT '',
  `email_from_name` varchar(255) DEFAULT '',
  `time_in_cutoff` time DEFAULT '08:00:00',
  `time_out_cutoff` time DEFAULT '17:00:00',
  `active_date` date DEFAULT NULL,
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `log_retention_days` int(11) DEFAULT 30,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_user` varchar(255) DEFAULT NULL,
  `smtp_pass` varchar(255) DEFAULT NULL,
  `absent_alert_enabled` tinyint(1) DEFAULT 0,
  `absent_alert_time` time DEFAULT '09:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `school_name`, `late_time`, `sms_provider`, `sms_base_url`, `sms_api_key`, `sms_sender_name`, `sms_enabled`, `email_enabled`, `ip_whitelist`, `brevo_api_key`, `email_from_address`, `email_from_name`, `time_in_cutoff`, `time_out_cutoff`, `active_date`, `maintenance_mode`, `log_retention_days`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `absent_alert_enabled`, `absent_alert_time`) VALUES
(1, 'ACCESS COMPUTER COLLEGE', '11:00:00', 'semaphore', 'y4k3y9.api.infobip.com', 'b0e7b60cd8aa7d9552fdc2447c74a848-1b473212-89d4-4b01-8141-c637788b17fc', 'ACCESS COMPUTER', 1, 1, '112.203.209.10', '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', 'benette.arocha1@gmail.com', 'School Attendance', '08:00:00', '17:00:00', NULL, 1, 30, NULL, 587, NULL, NULL, 0, '09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'Sent',
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year` varchar(20) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `rfid_uid` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `fullname`, `course`, `year`, `section`, `phone`, `email`, `rfid_uid`, `photo`, `created_at`) VALUES
(19, 'A124C0180', 'Benette Arocha', 'BSIT', '3', 'BSITM72', '09917437113', 'benette.arocha1@gmail.com', '8D:92:C9:06', NULL, '2026-07-23 09:55:01'),
(20, 'A124C01801', 'Brent Tomayao', 'BSIT', '3', 'BSITM72', '09917437112', 'mj31tomayao@gmail.com', '69:35:F4:DE', NULL, '2026-07-23 10:25:26'),
(21, 'A124C0073', 'Gean Benedicto', 'BSIT', '3', 'BSITM72', '09917437111', 'georgeandrewdbenedicto@gmail.com', '47:95:66:06', NULL, '2026-07-23 10:33:47'),
(22, 'A124C0098', 'Jemuel Celestial', 'BSIT', '3', 'BSITM72', '09917437114', 'jemcelestialj1@gmail.com', '58:57:3D:FF', NULL, '2026-07-23 10:47:07'),
(25, 'A124C0154', 'Loren Luberio', 'BSIT', '3', 'BSITM72', '09917437115', 'luberioloren@gmail.com', '68:4D:86:FF', NULL, '2026-07-23 10:53:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uid` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'Student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_day` (`student_id`,`date`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `sms_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
