-- ===============================
-- Campus Space Booking System
-- Database Schema + Sample Data
-- ===============================

-- Drop existing database (optional if re-importing)
DROP DATABASE IF EXISTS campus_booking;

-- Create new database
CREATE DATABASE campus_booking;
USE campus_booking;

-- ===============================
-- Users Table
-- ===============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- Spaces Table
-- ===============================
CREATE TABLE spaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('classroom', 'canteen', 'game_room', 'library') NOT NULL,
    capacity INT NOT NULL,
    availability BOOLEAN DEFAULT TRUE
);

-- ===============================
-- Bookings Table
-- ===============================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    space_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('booked', 'cancelled', 'completed') DEFAULT 'booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE CASCADE
);

-- ===============================
-- Sample Users
-- Passwords are plain for demo only; use password_hash() in signup.php
-- ===============================
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@campus.com', 'admin123', 'admin'),
('John Doe', 'john@student.com', 'john123', 'student'),
('Jane Smith', 'jane@student.com', 'jane123', 'student');

-- ===============================
-- Sample Spaces
-- ===============================
INSERT INTO spaces (name, type, capacity, availability) VALUES
('Classroom A1', 'classroom', 40, TRUE),
('Classroom B2', 'classroom', 50, TRUE),
('Library Reading Room', 'library', 20, TRUE),
('Canteen Table 5', 'canteen', 6, TRUE),
('Canteen Table 6', 'canteen', 6, TRUE),
('Game Room 1', 'game_room', 8, TRUE),
('Game Room 2', 'game_room', 10, TRUE);

-- ===============================
-- Sample Bookings
-- ===============================
INSERT INTO bookings (user_id, space_id, booking_date, start_time, end_time, status)
VALUES
(2, 1, '2025-10-31', '09:00:00', '11:00:00', 'booked'),
(3, 4, '2025-10-31', '12:00:00', '13:00:00', 'booked');
