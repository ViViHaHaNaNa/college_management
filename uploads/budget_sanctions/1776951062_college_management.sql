-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 09:32 PM
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
-- Database: `college_management`
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
(23, 1, 22, '2026-04-23', '08:00:00', '09:00:00', 'Study', NULL, 0, 'booked', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `committees`
--

CREATE TABLE `committees` (
  `committee_id` varchar(20) NOT NULL,
  `committee_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `committees`
--

INSERT INTO `committees` (`committee_id`, `committee_password`) VALUES
('IETE-SF', 'comm7'),
('MSC', 'comm7'),
('SPORTS COMMITTEE', 'comm7'),
('STUDENT COUNCIL', 'comm7');

-- --------------------------------------------------------

--
-- Table structure for table `datetime_documents`
--

CREATE TABLE `datetime_documents` (
  `document_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `datetime_documents`
--

INSERT INTO `datetime_documents` (`document_id`, `request_id`, `document_type`, `file_path`) VALUES
(1, 1, 'letterhead', 'uploads/area_usage/1773260139_Letterhead.docx'),
(2, 1, 'display_work', 'uploads/area_usage/1773260139_Design.docx');

-- --------------------------------------------------------

--
-- Table structure for table `datetime_requests`
--

CREATE TABLE `datetime_requests` (
  `request_id` int(11) NOT NULL,
  `committee_id` varchar(50) DEFAULT NULL,
  `type` enum('area_usage','special_event') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` varchar(255) DEFAULT NULL,
  `forwarded_to_admin` tinyint(1) DEFAULT 0,
  `admin_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `datetime_requests`
--

INSERT INTO `datetime_requests` (`request_id`, `committee_id`, `type`, `status`, `created_at`, `rejection_reason`, `forwarded_to_admin`, `admin_status`, `admin_remarks`) VALUES
(1, 'IETE-SF', 'area_usage', 'pending', '2026-03-11 20:15:39', NULL, 1, 'approved', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `general_documents`
--

CREATE TABLE `general_documents` (
  `document_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_documents`
--

INSERT INTO `general_documents` (`document_id`, `request_id`, `document_type`, `file_path`) VALUES
(1, 1, 'email_content', 'uploads/general/mass_email/1773258469_Letterhead.docx'),
(3, 3, 'smartboard_content', 'uploads/general/smart_board/1773258710_Design.docx'),
(4, 4, 'certificate_content', 'uploads/general/certificates/1773258829_certificate.docx'),
(5, 5, 'email_content', 'uploads/general/mass_emailing/1773263765_Letterhead.docx'),
(6, 6, 'email_content', 'uploads/general/mass_emailing/1776665708_Mini Project Report (2).pdf');

-- --------------------------------------------------------

--
-- Table structure for table `general_requests`
--

CREATE TABLE `general_requests` (
  `request_id` int(11) NOT NULL,
  `committee_id` varchar(50) DEFAULT NULL,
  `type` enum('mass_email','smartboard','digital_signature') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` varchar(255) DEFAULT NULL,
  `forwarded_to_admin` tinyint(1) DEFAULT 0,
  `admin_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_requests`
--

INSERT INTO `general_requests` (`request_id`, `committee_id`, `type`, `status`, `created_at`, `rejection_reason`, `forwarded_to_admin`, `admin_status`, `admin_remarks`) VALUES
(1, 'IETE-SF', 'mass_email', 'pending', '2026-03-11 19:47:49', NULL, 0, 'pending', NULL),
(3, 'IETE-SF', 'smartboard', 'pending', '2026-03-11 19:51:50', NULL, 0, 'pending', NULL),
(4, 'IETE-SF', 'digital_signature', 'pending', '2026-03-11 19:53:49', NULL, 1, 'approved', NULL),
(5, 'IETE-SF', 'mass_email', 'pending', '2026-03-11 21:16:05', 'Not valid', 1, 'rejected', NULL),
(6, 'IETE-SF', 'mass_email', 'pending', '2026-04-20 06:15:08', NULL, 0, 'pending', NULL);

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

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`inquiry_id`, `committee_id`, `message`, `reply`, `status`, `created_at`) VALUES
(6, 'IETE-SF', 'Test', 'Cool', 'resolved', '2026-03-11 15:56:17'),
(7, 'IETE-SF', 'Test', NULL, 'pending', '2026-03-11 20:37:05'),
(8, 'IETE-SF', 'Is it possible to meet on the 15th at 4-5', 'Yeah sure', 'resolved', '2026-03-12 04:51:08');

-- --------------------------------------------------------

--
-- Table structure for table `logistical_documents`
--

CREATE TABLE `logistical_documents` (
  `document_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logistical_documents`
--

INSERT INTO `logistical_documents` (`document_id`, `request_id`, `document_type`, `file_path`) VALUES
(1, 1, 'approval_document', 'uploads/logistical/notice_board/1773254295_Letterhead.docx'),
(2, 1, 'display_work', 'uploads/logistical/notice_board/1773254295_Design.docx'),
(3, 2, 'approval_document', 'uploads/logistical/guest_invitation/1773257677_Letterhead.docx'),
(4, 2, 'guest_identity', 'uploads/logistical/guest_invitation/1773257677_Guest_Identity.docx'),
(5, 3, 'arrangement_document', 'uploads/logistical/arrangements/1773257702_ArrangementReqs.docx'),
(7, 5, 'arrangement_document', 'uploads/logistical/arrangement/1773263711_Letterhead.docx'),
(8, 6, 'approval_document', 'uploads/logistical/guest_invitation/1775211733_1773258710_Design.docx'),
(9, 6, 'guest_identity', 'uploads/logistical/guest_invitation/1775211733_EXP9C092FOE.docx');

-- --------------------------------------------------------

--
-- Table structure for table `logistical_requests`
--

CREATE TABLE `logistical_requests` (
  `request_id` int(11) NOT NULL,
  `committee_id` varchar(50) DEFAULT NULL,
  `type` enum('notice_board','guest_invitation','arrangement') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` varchar(255) DEFAULT NULL,
  `forwarded_to_admin` tinyint(1) DEFAULT 0,
  `admin_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logistical_requests`
--

INSERT INTO `logistical_requests` (`request_id`, `committee_id`, `type`, `status`, `created_at`, `rejection_reason`, `forwarded_to_admin`, `admin_status`, `admin_remarks`) VALUES
(1, 'IETE-SF', 'notice_board', 'pending', '2026-03-11 18:38:15', NULL, 0, 'pending', NULL),
(2, 'IETE-SF', 'guest_invitation', 'pending', '2026-03-11 19:34:37', 'Not valid', 1, 'rejected', NULL),
(3, 'IETE-SF', 'arrangement', 'pending', '2026-03-11 19:35:02', NULL, 0, 'pending', NULL),
(5, 'IETE-SF', 'arrangement', 'pending', '2026-03-11 21:15:11', NULL, 0, 'pending', NULL),
(6, 'IETE-SF', 'guest_invitation', 'pending', '2026-04-03 10:22:13', NULL, 1, 'approved', NULL);

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
(2, 1, 'Your booking for CR - 102 on 2026-02-28 (10:00 - 11:00) was cancelled by the admin.\nReason: Maintanence scheduled', 1, '2026-02-27 08:23:24'),
(3, 4, 'Your booking for Library Pod 2 on 2026-02-27 (14:00 - 15:00) was cancelled by the admin.\nReason: s', 0, '2026-04-01 06:34:51'),
(4, 4, 'Your booking for CR - 101 on 2026-02-28 (10:00 - 11:00) was cancelled by the admin.\nReason: s', 0, '2026-04-01 06:34:55'),
(5, 11, 'Your booking for Cafeteria Table 3 on 2026-03-13 (12:00 - 13:00) was cancelled by the admin.\nReason: s', 0, '2026-04-01 06:34:58'),
(6, 11, 'Your booking for CR - 402 on 2026-03-13 (14:00 - 15:00) was cancelled by the admin.\nReason: s', 0, '2026-04-01 06:35:01');

-- --------------------------------------------------------

--
-- Table structure for table `paperwork`
--

CREATE TABLE `paperwork` (
  `paperwork_id` int(11) NOT NULL,
  `committee_id` varchar(20) DEFAULT NULL,
  `type` enum('annual_report','event_approval','budget_sanction','reimbursement') DEFAULT NULL,
  `status` enum('pending','forwarded_to_admin','approved','rejected') NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` varchar(255) DEFAULT NULL,
  `forwarded_to_admin` tinyint(1) DEFAULT 0,
  `admin_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paperwork`
--

INSERT INTO `paperwork` (`paperwork_id`, `committee_id`, `type`, `status`, `uploaded_at`, `rejection_reason`, `forwarded_to_admin`, `admin_status`, `admin_remarks`) VALUES
(1, 'IETE-SF', 'annual_report', 'pending', '2026-03-11 17:12:43', NULL, 0, 'pending', NULL),
(2, 'IETE-SF', 'event_approval', 'pending', '2026-03-11 17:17:51', NULL, 0, 'pending', NULL),
(3, 'IETE-SF', 'event_approval', 'pending', '2026-03-11 17:19:16', NULL, 0, 'pending', NULL),
(4, 'IETE-SF', 'event_approval', 'pending', '2026-03-11 17:22:04', NULL, 1, 'approved', NULL),
(5, 'IETE-SF', 'budget_sanction', 'pending', '2026-04-15 03:49:30', NULL, 0, NULL, NULL),
(6, 'IETE-SF', 'annual_report', 'forwarded_to_admin', '2026-04-01 06:33:07', NULL, 1, 'approved', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `paperwork_documents`
--

CREATE TABLE `paperwork_documents` (
  `document_id` int(11) NOT NULL,
  `paperwork_id` int(11) DEFAULT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paperwork_documents`
--

INSERT INTO `paperwork_documents` (`document_id`, `paperwork_id`, `document_type`, `file_path`) VALUES
(1, 1, 'report', 'uploads/annual_report/1773249163_college_mgmt.sql'),
(8, 4, 'letterhead', 'uploads/event_approval/1773249724_Letterhead.docx'),
(9, 4, 'proposal', 'uploads/event_approval/1773249724_Proposal.docx'),
(10, 4, 'expenses', 'uploads/event_approval/1773249724_Expen ses.xlsx'),
(14, 6, 'report', 'uploads/annual_report/1775025187_Experimement 9_FOE.docx'),
(21, 5, 'previous_report', 'uploads/budget_sanctions/1776224970_C096_CG_Practical 10 1.doc'),
(22, 5, 'proposal', 'uploads/budget_sanctions/1776224970_RTR_CaseStudy.docx');

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
(6, 'Sofia', 'Francis', 'sfrancis@gmail.com', '$2y$10$k1Ql6q.r9Bo8yA95J9DkgOvpYADJK1oMCPLelYvCEcEPTiLHIrVp.', 'faculty', NULL),
(9, 'sohham', 'sound', 'sound@gmail.com', '$2y$10$aQJJnvbfk7/UZ44kPK9QGeCI7mHz//r1R8aFfQja9xYO/ulZRdjY6', 'committee', 'IETE-SF'),
(10, 'Darren', 'Dsliva', 'dsilva@gmail.com', '$2y$10$2JvwY2mCjTOtOgsfsewQ7ev//2OWHILFfqHKknz149VxiSWEf3clu', 'committee', 'MSC'),
(11, 'Shobha', 'Putra', 'shobha@gmail.com', '$2y$10$M0YTVwSGYBjPsMWyhpjPq.Y0ygPtKkY9v/vmSGMA/oFdvJRtHn8.2', 'student', NULL),
(12, 'Abdul', 'Kalam', 'abd@gmail.com', '$2y$10$yq82mxmUYXVjsuvHmOYHMOTHoCkqbuidrhTP75BqOWCBh6OAhpqFq', 'committee', 'SPORTS COMMITTEE');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `committees`
--
ALTER TABLE `committees`
  ADD PRIMARY KEY (`committee_id`);

--
-- Indexes for table `datetime_documents`
--
ALTER TABLE `datetime_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `datetime_requests`
--
ALTER TABLE `datetime_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `general_documents`
--
ALTER TABLE `general_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `general_requests`
--
ALTER TABLE `general_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`inquiry_id`);

--
-- Indexes for table `logistical_documents`
--
ALTER TABLE `logistical_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `logistical_requests`
--
ALTER TABLE `logistical_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paperwork`
--
ALTER TABLE `paperwork`
  ADD PRIMARY KEY (`paperwork_id`);

--
-- Indexes for table `paperwork_documents`
--
ALTER TABLE `paperwork_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `paperwork_id` (`paperwork_id`);

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
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `committee_id` (`committee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `datetime_documents`
--
ALTER TABLE `datetime_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `datetime_requests`
--
ALTER TABLE `datetime_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `general_documents`
--
ALTER TABLE `general_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `general_requests`
--
ALTER TABLE `general_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `logistical_documents`
--
ALTER TABLE `logistical_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `logistical_requests`
--
ALTER TABLE `logistical_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `paperwork`
--
ALTER TABLE `paperwork`
  MODIFY `paperwork_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `paperwork_documents`
--
ALTER TABLE `paperwork_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `spaces`
--
ALTER TABLE `spaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `datetime_documents`
--
ALTER TABLE `datetime_documents`
  ADD CONSTRAINT `datetime_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `datetime_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `general_documents`
--
ALTER TABLE `general_documents`
  ADD CONSTRAINT `general_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `general_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `logistical_documents`
--
ALTER TABLE `logistical_documents`
  ADD CONSTRAINT `logistical_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `logistical_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `paperwork_documents`
--
ALTER TABLE `paperwork_documents`
  ADD CONSTRAINT `paperwork_documents_ibfk_1` FOREIGN KEY (`paperwork_id`) REFERENCES `paperwork` (`paperwork_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
