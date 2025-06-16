-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2025 at 08:21 AM
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
-- Database: `projekt`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`) VALUES
(1, 'Informatyka'),
(2, 'Pedagogika'),
(3, 'Ekonomika'),
(4, 'Europeistyka');

-- --------------------------------------------------------

--
-- Table structure for table `course_years`
--

CREATE TABLE `course_years` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `year_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_years`
--

INSERT INTO `course_years` (`id`, `course_id`, `year_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 2, 1),
(5, 2, 2),
(6, 2, 3),
(7, 3, 1),
(8, 3, 2),
(9, 3, 3),
(10, 4, 1),
(11, 4, 2),
(12, 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `submission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `grade`, `comment`, `submission_id`) VALUES
(6, 4.00, 'TEST', 11),
(7, 3.00, 'gsdgfs', 12),
(8, 4.00, 'dfafasfa', 13),
(9, 5.00, '123', 14),
(10, 2.00, 'pnh', 15),
(11, 4.00, 'asda', 18);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `course_year_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `course_year_id`, `name`) VALUES
(1, 1, 'Grupa 1'),
(2, 1, 'Grupa 2'),
(3, 2, 'Grupa 1'),
(4, 4, 'Grupa 1'),
(5, 7, 'Grupa 1'),
(6, 10, 'Grupa 1'),
(8, 5, 'Grupa 1'),
(9, 8, 'Grupa 1'),
(10, 11, 'Grupa 1'),
(11, 3, 'Grupa 1'),
(12, 6, 'Grupa 1'),
(13, 9, 'Grupa 1'),
(14, 12, 'Grupa 1'),
(16, 4, 'Grupa 2'),
(17, 7, 'Grupa 2'),
(18, 10, 'Grupa 2'),
(19, 2, 'Grupa 2'),
(20, 5, 'Grupa 2'),
(21, 8, 'Grupa 2'),
(22, 11, 'Grupa 2'),
(23, 3, 'Grupa 2'),
(24, 6, 'Grupa 2'),
(25, 9, 'Grupa 2'),
(26, 12, 'Grupa 2');

-- --------------------------------------------------------

--
-- Table structure for table `lectures`
--

CREATE TABLE `lectures` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `course_year_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lectures`
--

INSERT INTO `lectures` (`id`, `name`, `teacher_id`, `course_year_id`) VALUES
(41, 'Matematyka Dyskretna', 1, 1),
(42, 'Matematyka Dyskretna', 2, 1),
(43, 'Podstawy Programowania', 1, 2),
(44, 'Psychologia Rozwojowa', 3, 4),
(45, 'Zarządzanie Zasobami Ludzkimi', 2, 7),
(46, 'Historia Europy', 4, 10),
(47, 'Ekonomia Polityczna', 2, 8),
(48, 'Metody Nauczania', 3, 5),
(49, 'Psychologia Rozwojowa', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `text_answer` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `assignment_id`, `file_path`, `text_answer`) VALUES
(11, 1, '', 'nvcnvn'),
(12, 12, NULL, 'gfdsgsfsdfs'),
(13, 16, 'uploads/1745497403_header.php', '12345csggs'),
(14, 13, NULL, '123'),
(15, 14, 'uploads/1748511916_firma.pdf', 'rar'),
(16, 18, NULL, 'gfdgfd452'),
(18, 22, 'uploads/1749823090_18191098.pdf', 'csgd');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `lecture_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('homework','project') NOT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `group_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `lecture_id`, `name`, `description`, `type`, `due_date`, `created_at`, `group_id`, `image_path`) VALUES
(1, 41, 'Zadanie 1', 'Przeczytaj rozdziały 1-3 podręcznika', 'homework', '2025-02-15', '2025-03-19 22:45:40', 1, 'uploads/task_image_67dbd6bbe1638.png'),
(2, 42, 'Zadanie 2', 'Napisz esej na temat teorii zarządzania', 'project', '2025-02-20', '2025-03-19 22:45:40', 2, NULL),
(3, 43, 'Zadanie 3', 'Rozwiązuj zadania z matematyki dyskretnej', 'homework', '2025-02-25', '2025-03-19 22:45:40', 3, NULL),
(4, 44, 'Zadanie 4', 'Przygotuj prezentację na temat psychologii rozwojowej', 'project', '2025-03-01', '2025-03-19 22:45:40', 4, NULL),
(17, 41, 'test', 'test', 'homework', '2025-03-31', '2025-03-20 09:52:39', 1, 'uploads/task_image_67dbe5671eded.png'),
(18, 41, 'test2', 'gfdhdh', 'project', '2025-04-25', '2025-04-15 13:46:20', 2, NULL),
(19, 41, 'asfas', 'dgzndgsfg', 'project', '2025-04-24', '2025-04-15 13:47:11', 1, NULL),
(20, 41, 'test123', 'test123', 'homework', '2025-04-30', '2025-04-24 12:21:01', 1, 'uploads/task_image_680a2cadc148d.png'),
(21, 41, 'sdgsdgsdg', 'sdgdsgsdg', 'homework', '2025-06-13', '2025-06-12 12:34:16', 1, NULL),
(23, 49, 'test', 'test123', 'homework', '2025-06-27', '2025-06-13 13:54:52', 1, 'uploads/task_file_684c2dacd3e90.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_date` datetime NOT NULL DEFAULT current_timestamp(),
  `submission_date` datetime DEFAULT NULL,
  `status` enum('pending','submitted','graded') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `task_id`, `student_id`, `assigned_date`, `submission_date`, `status`) VALUES
(1, 1, 5, '2025-03-20 00:46:14', '2025-04-15 18:17:23', 'graded'),
(2, 2, 6, '2025-03-20 00:46:14', NULL, 'pending'),
(3, 3, 7, '2025-03-20 00:46:14', NULL, 'pending'),
(4, 4, 8, '2025-03-20 00:46:14', NULL, 'pending'),
(12, 17, 5, '2025-03-20 11:52:39', '2025-04-15 18:35:27', 'graded'),
(13, 17, 6, '2025-03-20 11:52:39', '2025-04-24 15:29:48', 'graded'),
(14, 19, 5, '2025-04-15 16:47:11', '2025-05-29 12:45:16', 'graded'),
(15, 19, 6, '2025-04-15 16:47:11', NULL, 'pending'),
(16, 20, 5, '2025-04-24 15:21:01', '2025-04-24 15:23:23', 'graded'),
(17, 20, 6, '2025-04-24 15:21:01', NULL, 'pending'),
(18, 21, 5, '2025-06-12 15:34:16', '2025-06-12 15:34:38', 'submitted'),
(19, 21, 6, '2025-06-12 15:34:16', NULL, 'pending'),
(22, 23, 5, '2025-06-13 16:54:52', '2025-06-13 16:58:10', 'graded'),
(23, 23, 6, '2025-06-13 16:54:52', NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `first_name`, `last_name`, `group_id`) VALUES
(1, 'adam.zielinski', '$2y$12$616bROhaTNwLS.MWTuvj8enGp9dosleL3ebParHJ2IYGOog.VgVUS', 'teacher', 'Adam', 'Zieliński', NULL),
(2, 'ewa.kowalczyk', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'teacher', 'Ewa', 'Kowalczyk', NULL),
(3, 'marek.kowal', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'teacher', 'Marek', 'Kowal', NULL),
(4, 'joanna.krol', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'teacher', 'Joanna', 'Król', NULL),
(5, 'jan.kowalski', '$2y$12$616bROhaTNwLS.MWTuvj8enGp9dosleL3ebParHJ2IYGOog.VgVUS', 'student', 'Jan', 'Kowalski', 1),
(6, 'anna.nowak', '$2y$12$616bROhaTNwLS.MWTuvj8enGp9dosleL3ebParHJ2IYGOog.VgVUS', 'student', 'Anna', 'Nowak', 1),
(7, 'michal.wis', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Michał', 'Wiśniewski', 3),
(8, 'kasia.lask', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Katarzyna', 'Laskowska', 4),
(9, 'piotr.adamski', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Piotr', 'Adamski', 5),
(10, 'agnieszka.kubiak', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Agnieszka', 'Kubiak', 6),
(11, 'tomasz.zajac', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Tomasz', 'Zając', 8),
(12, 'magda.wojcik', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Magda', 'Wójcik', 16),
(13, 'marek.szymanski', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Marek', 'Szymański', 9),
(14, 'ewa.borkowska', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Ewa', 'Borkowska', 17),
(15, 'lukasz.kowalczyk', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Łukasz', 'Kowalczyk', 12),
(16, 'natalia.nowicka', '$2y$12$/ql4SRHg94F09bd5T0FNIe/plrdDOpn1h.mPvbk5BQTHt6Htg0mb2', 'student', 'Natalia', 'Nowicka', 24),
(17, 'edgar.makovski', '$2y$12$Di.EsV2UCH05fxBjdvMHWeoRC79xsIFXyCgFTS3m05VdJ1c0B8azm', 'admin', 'Edgar', 'Makovski', NULL),
(18, 'albert.nowicki', '$2y$10$QL1YohLIiXfc4QmBZgbT4OBRY4adAgJXG7BpBasFMFxqho9jrtKbW', 'teacher', 'Albert', 'Nowicki', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `years`
--

CREATE TABLE `years` (
  `id` int(11) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `years`
--

INSERT INTO `years` (`id`, `year`) VALUES
(1, 1),
(2, 2),
(3, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_years`
--
ALTER TABLE `course_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_course_year` (`course_id`,`year_id`),
  ADD KEY `year_id` (`year_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_year_id` (`course_year_id`);

--
-- Indexes for table `lectures`
--
ALTER TABLE `lectures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `course_year_id` (`course_year_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecture_id` (`lecture_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `years`
--
ALTER TABLE `years`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_years`
--
ALTER TABLE `course_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `lectures`
--
ALTER TABLE `lectures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `years`
--
ALTER TABLE `years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_years`
--
ALTER TABLE `course_years`
  ADD CONSTRAINT `course_years_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `course_years_ibfk_2` FOREIGN KEY (`year_id`) REFERENCES `years` (`id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`);

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_2` FOREIGN KEY (`course_year_id`) REFERENCES `course_years` (`id`);

--
-- Constraints for table `lectures`
--
ALTER TABLE `lectures`
  ADD CONSTRAINT `lectures_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `lectures_ibfk_2` FOREIGN KEY (`course_year_id`) REFERENCES `course_years` (`id`);

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `task_assignments` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Constraints for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD CONSTRAINT `task_assignments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_assignments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
