-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2022 at 02:38 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `new_aluve_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `add_ons`
--

CREATE TABLE `add_ons` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `add_ons`
--

INSERT INTO `add_ons` (`id`, `name`, `price`) VALUES
(1, 'Laundry', '180'),
(2, 'Hunters Dry', '20');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_rooms`
--

CREATE TABLE `blocked_rooms` (
  `id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `linked_resa_id` int(11) DEFAULT NULL,
  `comment` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cleaning`
--

CREATE TABLE `cleaning` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `cleaner` int(11) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest`
--

CREATE TABLE `guest` (
  `id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_image` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT 'Not Verified',
  `phone_number` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `comments` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guest`
--

INSERT INTO `guest` (`id`, `name`, `id_image`, `phone_number`, `email`, `state`, `comments`, `id_number`) VALUES
(1, 'benzo', 'Not Verified', '+27837917430', '', 'Active', NULL, NULL),
(2, 'werer', 'Not Verified', '12121212', '', 'Active', NULL, NULL),
(3, 'werer', 'Not Verified', '12121212', '', 'Active', NULL, NULL),
(4, 'werer', 'Not Verified', '12121212', '', 'Active', NULL, NULL),
(5, 'qwqwqw', 'Not Verified', '12121212', '', 'Active', NULL, NULL),
(6, 'werer', 'Not Verified', '12121212', '', 'Active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_template`
--

CREATE TABLE `message_template` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_variables`
--

CREATE TABLE `message_variables` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_variables`
--

INSERT INTO `message_variables` (`id`, `name`) VALUES
(1, 'guest_name'),
(2, 'check_in'),
(3, 'check_out'),
(4, 'room_name');

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `additional_info` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `received_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `check_in_status` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT 'not checked in',
  `cleanliness_score` int(11) DEFAULT NULL,
  `checked_in_time` datetime DEFAULT NULL,
  `check_in_time` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '14:00',
  `check_out_time` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '10:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `guest_id`, `room_id`, `status`, `check_in`, `check_out`, `additional_info`, `received_on`, `updated_on`, `uid`, `origin`, `origin_url`, `check_in_status`, `cleanliness_score`, `checked_in_time`, `check_in_time`, `check_out_time`) VALUES
(1, 1, 1, 1, '2022-07-13', '2022-07-15', '0837917430', '2022-07-13 09:19:41', '2022-07-13 09:19:41', '62ce720d42510', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(2, 1, 1, 1, '2022-07-15', '2022-07-16', '0837917430', '2022-07-13 10:51:42', '2022-07-13 10:51:42', '62ce879e6a643', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(3, 2, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 10:52:58', '2022-07-13 10:52:58', '62ce87ea244b6', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(4, 3, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 10:53:03', '2022-07-13 10:53:03', '62ce87ef45e8f', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(5, 4, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 10:54:13', '2022-07-13 10:54:13', '62ce8835e002c', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(6, 5, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 10:58:01', '2022-07-13 10:58:01', '62ce8919ced43', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(7, 6, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 11:21:40', '2022-07-13 11:21:40', '62ce8ea4f12d3', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(8, 2, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 11:23:41', '2022-07-13 11:23:41', '62ce8f1db8549', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(9, 2, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 11:23:54', '2022-07-13 11:23:54', '62ce8f2a30547', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00'),
(10, 2, 1, 2, '2022-07-13', '2022-07-15', '12121212', '2022-07-13 11:25:38', '2022-07-13 11:25:38', '62ce8f92645a3', 'website', 'aluvegh.co.za', 'not checked in', 0, NULL, '14:00', '10:00');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_add_ons`
--

CREATE TABLE `reservation_add_ons` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `add_on_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation_notes`
--

CREATE TABLE `reservation_notes` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation_status`
--

CREATE TABLE `reservation_status` (
  `id` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservation_status`
--

INSERT INTO `reservation_status` (`id`, `name`) VALUES
(1, 'confirmed'),
(2, 'pending'),
(3, 'cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `status` int(11) DEFAULT NULL,
  `bed` int(11) DEFAULT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `sleeps` int(11) DEFAULT NULL,
  `linked_room` int(11) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `stairs` tinyint(1) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `status`, `bed`, `name`, `price`, `sleeps`, `linked_room`, `size`, `stairs`, `description`) VALUES
(1, 1, 1, 'Bachelor', '400', 2, NULL, 40, 0, 'Bachelor room');

-- --------------------------------------------------------

--
-- Table structure for table `room_bed_size`
--

CREATE TABLE `room_bed_size` (
  `id` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_bed_size`
--

INSERT INTO `room_bed_size` (`id`, `name`) VALUES
(1, 'King'),
(2, 'Queen'),
(3, 'Double'),
(4, 'Single');

-- --------------------------------------------------------

--
-- Table structure for table `room_images`
--

CREATE TABLE `room_images` (
  `id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_status`
--

CREATE TABLE `room_status` (
  `id` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_status`
--

INSERT INTO `room_status` (`id`, `name`) VALUES
(1, 'live'),
(2, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_messages`
--

CREATE TABLE `schedule_messages` (
  `id` int(11) NOT NULL,
  `message_template` int(11) DEFAULT NULL,
  `message_schedule` int(11) DEFAULT NULL,
  `rooms` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_times`
--

CREATE TABLE `schedule_times` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `days` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedule_times`
--

INSERT INTO `schedule_times` (`id`, `name`, `days`) VALUES
(1, 'Day of check-in', 0),
(2, 'Day before check-in', 1),
(3, 'Week before check-in', 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_ons`
--
ALTER TABLE `add_ons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blocked_rooms`
--
ALTER TABLE `blocked_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `cleaning`
--
ALTER TABLE `cleaning`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cleaning_reservation` (`reservation_id`),
  ADD KEY `cleaning_cleaner` (`cleaner`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest`
--
ALTER TABLE `guest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_template`
--
ALTER TABLE `message_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_variables`
--
ALTER TABLE `message_variables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_reservation` (`reservation_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `reservations_ibfk_3` (`status`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `reservation_add_ons`
--
ALTER TABLE `reservation_add_ons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_add_on_add_on` (`add_on_id`),
  ADD KEY `reservation_add_on_reservation` (`reservation_id`);

--
-- Indexes for table `reservation_notes`
--
ALTER TABLE `reservation_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_notes` (`reservation_id`);

--
-- Indexes for table `reservation_status`
--
ALTER TABLE `reservation_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rooms_ibfk_2` (`status`),
  ADD KEY `rooms_ibfk_1` (`bed`);

--
-- Indexes for table `room_bed_size`
--
ALTER TABLE `room_bed_size`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_images`
--
ALTER TABLE `room_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id_room` (`room_id`);

--
-- Indexes for table `room_status`
--
ALTER TABLE `room_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_messages`
--
ALTER TABLE `schedule_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_message_times` (`message_schedule`),
  ADD KEY `schedule_message_template` (`message_template`);

--
-- Indexes for table `schedule_times`
--
ALTER TABLE `schedule_times`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `add_ons`
--
ALTER TABLE `add_ons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blocked_rooms`
--
ALTER TABLE `blocked_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cleaning`
--
ALTER TABLE `cleaning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest`
--
ALTER TABLE `guest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `message_template`
--
ALTER TABLE `message_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_variables`
--
ALTER TABLE `message_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reservation_add_ons`
--
ALTER TABLE `reservation_add_ons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reservation_notes`
--
ALTER TABLE `reservation_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation_status`
--
ALTER TABLE `reservation_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `room_bed_size`
--
ALTER TABLE `room_bed_size`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `room_images`
--
ALTER TABLE `room_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_status`
--
ALTER TABLE `room_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `schedule_messages`
--
ALTER TABLE `schedule_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_times`
--
ALTER TABLE `schedule_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blocked_rooms`
--
ALTER TABLE `blocked_rooms`
  ADD CONSTRAINT `FK_CBE05B6A54177093` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `cleaning`
--
ALTER TABLE `cleaning`
  ADD CONSTRAINT `FK_3F6C5CF96E8447A4` FOREIGN KEY (`cleaner`) REFERENCES `employee` (`id`),
  ADD CONSTRAINT `FK_3F6C5CF9B83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `FK_65D29B32B83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `FK_4DA23954177093` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `FK_4DA2397B00651C` FOREIGN KEY (`status`) REFERENCES `reservation_status` (`id`),
  ADD CONSTRAINT `FK_4DA2399A4AA658` FOREIGN KEY (`guest_id`) REFERENCES `guest` (`id`);

--
-- Constraints for table `reservation_add_ons`
--
ALTER TABLE `reservation_add_ons`
  ADD CONSTRAINT `FK_CA784A2D220A8152` FOREIGN KEY (`add_on_id`) REFERENCES `add_ons` (`id`),
  ADD CONSTRAINT `FK_CA784A2DB83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);

--
-- Constraints for table `reservation_notes`
--
ALTER TABLE `reservation_notes`
  ADD CONSTRAINT `FK_4264762BB83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `FK_7CA11A967B00651C` FOREIGN KEY (`status`) REFERENCES `room_status` (`id`),
  ADD CONSTRAINT `FK_7CA11A96E647FCFF` FOREIGN KEY (`bed`) REFERENCES `room_bed_size` (`id`);

--
-- Constraints for table `room_images`
--
ALTER TABLE `room_images`
  ADD CONSTRAINT `FK_A15178AB54177093` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `schedule_messages`
--
ALTER TABLE `schedule_messages`
  ADD CONSTRAINT `FK_67E9F7A0531ED5EA` FOREIGN KEY (`message_schedule`) REFERENCES `schedule_times` (`id`),
  ADD CONSTRAINT `FK_67E9F7A09E46DB92` FOREIGN KEY (`message_template`) REFERENCES `message_template` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
