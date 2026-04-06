-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 04:20 AM
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
(18, 2, 54, '2026-03-06', '15:00:00', '16:00:00', 'Cafeteria Booking', NULL, 0, 'booked', NULL, NULL),
(19, 9, 44, '2026-03-13', '13:00:00', '14:00:00', 'Core Discussion', NULL, 0, 'booked', NULL, NULL),
(20, 11, 23, '2026-03-13', '14:00:00', '15:00:00', 'Studying', NULL, 0, 'booked', NULL, NULL),
(21, 11, 54, '2026-03-13', '12:00:00', '13:00:00', 'Cafeteria Booking', NULL, 0, 'booked', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
