-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 04:20 PM
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
-- Database: `college_mgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `space_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `cancel_message` varchar(255) DEFAULT NULL,
  `student_notified` tinyint(1) DEFAULT 0,
  `status` enum('booked','cancelled','completed') DEFAULT 'booked',
  `cancelled_at` datetime DEFAULT NULL,
  `table_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `space_id`, `booking_date`, `start_time`, `end_time`, `reason`, `cancel_message`, `student_notified`, `status`, `cancelled_at`, `table_number`) VALUES
(11, 4, 1, '2026-02-28', '10:00:00', '11:00:00', 'Studying', NULL, 0, 'booked', NULL, NULL),
(12, 4, 44, '2026-02-27', '14:00:00', '15:00:00', 'discussion\r\n', NULL, 0, 'booked', NULL, NULL),
(17, 3, 52, '2026-03-06', '15:00:00', '16:00:00', 'Cafeteria Booking', NULL, 0, 'booked', NULL, NULL),
(18, 2, 54, '2026-03-06', '15:00:00', '16:00:00', 'Cafeteria Booking', NULL, 0, 'booked', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `inquiry_id` int(11) NOT NULL,
  `committee_id` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `status` enum('pending','answered','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `seen` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `seen`, `created_at`) VALUES
(1, 1, 'Your booking for CR - 302 on 2026-02-27 (12:00 - 13:00) was cancelled by the admin.\nReason: Maintainence', 1, '2026-02-27 04:26:20'),
(2, 1, 'Your booking for CR - 102 on 2026-02-28 (10:00 - 11:00) was cancelled by the admin.\nReason: Maintanence scheduled', 1, '2026-02-27 08:23:24');

-- --------------------------------------------------------

--
-- Table structure for table `spaces`
--

CREATE TABLE `spaces` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `availability` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spaces`
--

INSERT INTO `spaces` (`id`, `name`, `type`, `capacity`, `availability`) VALUES
(1, 'CR - 101', 'Classroom', 60, 1),
(2, 'CR - 102', 'Classroom', 60, 1),
(3, 'CR - 103', 'Classroom', 60, 1),
(4, 'CR - 104', 'Classroom', 60, 1),
(5, 'CR - 105', 'Classroom', 60, 1),
(6, 'CL - 101', 'Lab', 40, 1),
(7, 'CL - 102', 'Lab', 40, 1),
(8, 'CR - 201', 'Classroom', 60, 1),
(9, 'CR - 202', 'Classroom', 60, 1),
(10, 'CR - 203', 'Classroom', 60, 1),
(11, 'CR - 204', 'Classroom', 60, 1),
(12, 'CR - 205', 'Classroom', 60, 1),
(13, 'CL - 201', 'Lab', 40, 1),
(14, 'CL - 202', 'Lab', 40, 1),
(15, 'CR - 301', 'Classroom', 60, 1),
(16, 'CR - 302', 'Classroom', 60, 1),
(17, 'CR - 303', 'Classroom', 60, 1),
(18, 'CR - 304', 'Classroom', 60, 1),
(19, 'CR - 305', 'Classroom', 60, 1),
(20, 'CL - 301', 'Lab', 40, 1),
(21, 'CL - 302', 'Lab', 40, 1),
(22, 'CR - 401', 'Classroom', 60, 1),
(23, 'CR - 402', 'Classroom', 60, 1),
(24, 'CR - 403', 'Classroom', 60, 1),
(25, 'CR - 404', 'Classroom', 60, 1),
(26, 'CR - 405', 'Classroom', 60, 1),
(27, 'CL - 401', 'Lab', 40, 1),
(28, 'CL - 402', 'Lab', 40, 1),
(29, 'CR - 501', 'Classroom', 60, 1),
(30, 'CR - 502', 'Classroom', 60, 1),
(31, 'CR - 503', 'Classroom', 60, 1),
(32, 'CR - 504', 'Classroom', 60, 1),
(33, 'CR - 505', 'Classroom', 60, 1),
(34, 'CL - 501', 'Lab', 40, 1),
(35, 'CL - 502', 'Lab', 40, 1),
(36, 'CR - 601', 'Classroom', 60, 1),
(37, 'CR - 602', 'Classroom', 60, 1),
(38, 'CR - 603', 'Classroom', 60, 1),
(39, 'CR - 604', 'Classroom', 60, 1),
(40, 'CR - 605', 'Classroom', 60, 1),
(41, 'CL - 601', 'Lab', 40, 1),
(42, 'CL - 602', 'Lab', 40, 1),
(43, 'Library Pod 1', 'Library', 6, 1),
(44, 'Library Pod 2', 'Library', 6, 1),
(45, 'Library Pod 3', 'Library', 6, 1),
(46, 'Library Pod 4', 'Library', 6, 1),
(47, 'Carrom Table 1', 'Recreation', 4, 1),
(48, 'Carrom Table 2', 'Recreation', 4, 1),
(49, 'Chess Table 1', 'Recreation', 2, 1),
(50, 'Chess Table 2', 'Recreation', 2, 1),
(51, 'Cafeteria AC', 'Cafeteria', 80, 1),
(52, 'Cafeteria Table 1', 'Cafeteria', 4, 1),
(53, 'Cafeteria Table 2', 'Cafeteria', 4, 1),
(54, 'Cafeteria Table 3', 'Cafeteria', 4, 1),
(55, 'Cafeteria Table 4', 'Cafeteria', 4, 1),
(56, 'Cafeteria Table 5', 'Cafeteria', 4, 1),
(57, 'Cafeteria Table 6', 'Cafeteria', 4, 1),
(58, 'Cafeteria Table 7', 'Cafeteria', 4, 1),
(59, 'Cafeteria Table 8', 'Cafeteria', 4, 1),
(60, 'Cafeteria Table 9', 'Cafeteria', 4, 1),
(61, 'Cafeteria Table 10', 'Cafeteria', 4, 1),
(62, 'Cafeteria Table 11', 'Cafeteria', 4, 1),
(63, 'Cafeteria Table 12', 'Cafeteria', 4, 1),
(64, 'Cafeteria Table 13', 'Cafeteria', 4, 1),
(65, 'Cafeteria Table 14', 'Cafeteria', 4, 1),
(66, 'Cafeteria Table 15', 'Cafeteria', 4, 1),
(67, 'Cafeteria Table 16', 'Cafeteria', 4, 1),
(68, 'Cafeteria Table 17', 'Cafeteria', 4, 1),
(69, 'Cafeteria Table 18', 'Cafeteria', 4, 1),
(70, 'Cafeteria Table 19', 'Cafeteria', 4, 1),
(71, 'Cafeteria Table 20', 'Cafeteria', 4, 1),
(72, 'Cafeteria Table 21', 'Cafeteria', 4, 1),
(73, 'Cafeteria Table 22', 'Cafeteria', 4, 1),
(74, 'Cafeteria Table 23', 'Cafeteria', 4, 1),
(75, 'Cafeteria Table 24', 'Cafeteria', 4, 1),
(76, 'Cafeteria Table 25', 'Cafeteria', 4, 1),
(77, 'Cafeteria Table 26', 'Cafeteria', 4, 1),
(78, 'Cafeteria Table 27', 'Cafeteria', 4, 1),
(79, 'Cafeteria Table 28', 'Cafeteria', 4, 1),
(80, 'Cafeteria Table 29', 'Cafeteria', 4, 1),
(81, 'Cafeteria Table 30', 'Cafeteria', 4, 1),
(82, 'Automation Lab', 'Lab', 40, 1),
(83, 'AR/VR Lab', 'Lab', 30, 1),
(84, 'Hardware Lab 1', 'Lab', 35, 1),
(85, 'Hardware Lab 2', 'Lab', 35, 1),
(86, 'Pneumatic Lab', 'Lab', 30, 1),
(87, 'Sensor IoT Lab', 'Lab', 30, 1),
(88, 'Hydraulics Lab', 'Lab', 30, 1),
(89, 'Physics Lab', 'Lab', 40, 1),
(90, 'DE Lab', 'Lab', 35, 1),
(91, 'ES Lab', 'Lab', 35, 1),
(92, 'Advanced Communication Lab', 'Lab', 30, 1),
(93, 'Basic Communication Lab', 'Lab', 30, 1),
(94, 'AI Lab', 'Lab', 30, 1),
(95, 'Robotics Lab', 'Lab', 30, 1),
(96, 'Additive Manufacturing Lab', 'Lab', 25, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','faculty','committee','admin') NOT NULL,
  `committee_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `committee_id`) VALUES
(1, 'Vihaan', 'Patole', 'vihaanmpatole@gmail.com', '$2y$10$fR8yeAjDLHKj/EjhhfyBMO64NNYUpIwv8dqtZEXqn8v8DrIW2i7kK', 'student', NULL),
(2, 'Preet', 'Shah', 'pshah@gmail.com', '$2y$10$imMhx1t/AyhH69y/IJHaPeqaqSooGKhpHucjAUln6seOlTiQ0tdv.', 'admin', NULL),
(3, 'Dhruv', 'Mane', 'dhruvmane@gmail.com', '$2y$10$mD0IoMki9xq/L/wgQdCki..O/Rrx.uk0YphycQ2NmoZNiXF2jVbga', 'student', NULL),
(4, 'sohham', 'soundalkar', 'sohham@gmail.com', '$2y$10$x3YJv4ZABf1KS4G/p4z.te3r4RN4jVdwTUPvZKyhpzdIL/QL.1Tfi', 'student', NULL),
(5, 'sohham', 'sound', 'sound@gmail.com', '$2y$10$enf9yBG6YG3Fdm.7PXQpue/L7H9itY9v5gBi5KwkxklY0b0v9j6CC', 'committee', NULL),
(6, 'Sofia', 'Francis', 'sfrancis@gmail.com', '$2y$10$k1Ql6q.r9Bo8yA95J9DkgOvpYADJK1oMCPLelYvCEcEPTiLHIrVp.', 'faculty', NULL),
(7, 'Hrishikesh', 'Kunde', 'hk@gmail.com', '$2y$10$2C4rgQ8pDYtz0LdpppOFyuDO/oiaVeyhhOl.q7Fk3dhzM30R6VeK2', 'committee', 'IETE'),
(8, 'Kylian', 'Mbappe', 'kmbappe@gmail.com', '$2y$10$uAdeJa32uAAP2HhuxyT18etedu7y2/q20kZ7n2.um73AuvCazzucC', 'committee', 'MSC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`inquiry_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `spaces`
--
ALTER TABLE `spaces`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `spaces`
--
ALTER TABLE `spaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
