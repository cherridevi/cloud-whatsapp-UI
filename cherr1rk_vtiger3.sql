-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 23, 2024 at 06:54 AM
-- Server version: 5.7.23-23
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cherr1rk_vtiger3`
--

-- --------------------------------------------------------

--
-- Table structure for table `alt_menu`
--

CREATE TABLE `alt_menu` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alt_otpValiddation`
--

CREATE TABLE `alt_otpValiddation` (
  `id` int(11) NOT NULL,
  `ticket_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `otp` int(11) NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alt_userchat`
--

CREATE TABLE `alt_userchat` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `qsk_id` int(11) NOT NULL,
  `answer` text COLLATE utf8_unicode_ci NOT NULL,
  `updated_date` datetime NOT NULL,
  `created_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alt_users`
--

CREATE TABLE `alt_users` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `last_seen`
--

CREATE TABLE `last_seen` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` bigint(20) NOT NULL,
  `sender` int(11) NOT NULL,
  `recvId` int(11) NOT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recvIsGroup` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `email` varchar(32) NOT NULL,
  `user_type` varchar(32) DEFAULT NULL,
  `number` varchar(32) NOT NULL DEFAULT '+12345678910',
  `pic` varchar(32) NOT NULL DEFAULT '1.jpg',
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alt_menu`
--
ALTER TABLE `alt_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alt_otpValiddation`
--
ALTER TABLE `alt_otpValiddation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alt_userchat`
--
ALTER TABLE `alt_userchat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alt_users`
--
ALTER TABLE `alt_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `last_seen`
--
ALTER TABLE `last_seen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alt_menu`
--
ALTER TABLE `alt_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alt_otpValiddation`
--
ALTER TABLE `alt_otpValiddation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alt_userchat`
--
ALTER TABLE `alt_userchat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alt_users`
--
ALTER TABLE `alt_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `last_seen`
--
ALTER TABLE `last_seen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
