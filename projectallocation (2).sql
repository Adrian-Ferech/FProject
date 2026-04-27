-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 12:42 AM
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
-- Database: `projectallocation`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `Admin_ID` smallint(6) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`Admin_ID`, `Name`, `Email`, `password`) VALUES
(1, 'admin', 'admin@test.com', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `allocation`
--

CREATE TABLE `allocation` (
  `Allocation_ID` smallint(6) NOT NULL,
  `Student_ID` smallint(6) NOT NULL,
  `Project_ID` smallint(6) NOT NULL,
  `Lecturer_ID` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allocation`
--

INSERT INTO `allocation` (`Allocation_ID`, `Student_ID`, `Project_ID`, `Lecturer_ID`) VALUES
(18, 2, 5, 2),
(19, 1, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `Lecturer_ID` smallint(6) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`Lecturer_ID`, `Name`, `Email`, `Department`, `password`) VALUES
(1, 'test', 'test@gmail.com', 'CS', 'test'),
(2, 'tester', 'tester@gmail.com', 'Computer Science', 'tester');

-- --------------------------------------------------------

--
-- Table structure for table `preferences`
--

CREATE TABLE `preferences` (
  `Preference_ID` smallint(6) NOT NULL,
  `Student_ID` smallint(6) NOT NULL,
  `Project_ID` smallint(6) NOT NULL,
  `preference_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `preferences`
--

INSERT INTO `preferences` (`Preference_ID`, `Student_ID`, `Project_ID`, `preference_order`) VALUES
(45, 1, 4, 1),
(46, 1, 5, 2),
(47, 1, 17, 3),
(53, 2, 4, 1),
(54, 2, 5, 2),
(55, 2, 23, 3);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `Project_ID` smallint(6) NOT NULL,
  `Lecturer_ID` smallint(6) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Prerequisite` enum('NONE','CS3MAS','CS3OS','CS3SA') DEFAULT 'NONE',
  `Status` tinyint(1) DEFAULT 1,
  `capacity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`Project_ID`, `Lecturer_ID`, `Title`, `Description`, `Prerequisite`, `Status`, `capacity`) VALUES
(4, 1, 'Web-Based Library System', 'Develop a full-stack web application for managing university library resources.', 'CS3MAS', 1, 1),
(5, 2, 'Machine Learning for Fraud Detection', 'Build a predictive model to detect fraudulent financial transactions.', 'CS3OS', 1, 1),
(6, 1, 'Mobile Fitness Tracker App', 'Create an Android/iOS app to track workouts and health metrics.', '', 1, 1),
(17, 1, 'AI text to speech support', 'This will do something...', 'CS3MAS', 1, 1),
(23, 1, 'The best project', 'yes', 'CS3SA', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `project_tags`
--

CREATE TABLE `project_tags` (
  `Project_ID` smallint(6) NOT NULL,
  `Tag_ID` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_tags`
--

INSERT INTO `project_tags` (`Project_ID`, `Tag_ID`) VALUES
(23, 1),
(23, 5);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `Student_ID` smallint(6) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Course` varchar(100) DEFAULT NULL,
  `Year` tinyint(4) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`Student_ID`, `Name`, `Email`, `Course`, `Year`, `password`, `grade`) VALUES
(1, 'test', 'test@gmail.com', 'CS3MAS', 3, 'test', 80.00),
(2, 'tester', 'tester@gmail.com', 'CS3SA', 3, 'tester', 79.00);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `Tag_ID` smallint(6) NOT NULL,
  `Tag_Name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`Tag_ID`, `Tag_Name`) VALUES
(4, 'AI'),
(5, 'Cybersecurity'),
(8, 'Data Science'),
(1, 'Mathematics'),
(7, 'Mobile Development'),
(3, 'Nature'),
(2, 'Science'),
(6, 'Web Development');

-- --------------------------------------------------------

--
-- Table structure for table `tag_combinations`
--

CREATE TABLE `tag_combinations` (
  `Combination_ID` int(11) NOT NULL,
  `Tag1_ID` smallint(6) NOT NULL,
  `Tag2_ID` smallint(6) NOT NULL,
  `Combination_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tag_combinations`
--

INSERT INTO `tag_combinations` (`Combination_ID`, `Tag1_ID`, `Tag2_ID`, `Combination_Name`) VALUES
(12, 1, 5, 'Mathematics + Cybersecurity');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`Admin_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `allocation`
--
ALTER TABLE `allocation`
  ADD PRIMARY KEY (`Allocation_ID`),
  ADD UNIQUE KEY `uq_student_allocation` (`Student_ID`),
  ADD KEY `fk_alloc_project` (`Project_ID`),
  ADD KEY `fk_alloc_lecturer` (`Lecturer_ID`);

--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`Lecturer_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `preferences`
--
ALTER TABLE `preferences`
  ADD PRIMARY KEY (`Preference_ID`),
  ADD UNIQUE KEY `uq_student_preference_order` (`Student_ID`,`preference_order`),
  ADD UNIQUE KEY `uq_student_project_preference` (`Student_ID`,`Project_ID`),
  ADD KEY `fk_pref_project` (`Project_ID`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`Project_ID`),
  ADD KEY `fk_project_lecturer` (`Lecturer_ID`);

--
-- Indexes for table `project_tags`
--
ALTER TABLE `project_tags`
  ADD PRIMARY KEY (`Project_ID`,`Tag_ID`),
  ADD KEY `fk_pt_tag` (`Tag_ID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`Student_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`Tag_ID`),
  ADD UNIQUE KEY `uq_tag_name` (`Tag_Name`);

--
-- Indexes for table `tag_combinations`
--
ALTER TABLE `tag_combinations`
  ADD PRIMARY KEY (`Combination_ID`),
  ADD UNIQUE KEY `uq_tag_pair` (`Tag1_ID`,`Tag2_ID`),
  ADD KEY `fk_tc_tag2` (`Tag2_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `Admin_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `allocation`
--
ALTER TABLE `allocation`
  MODIFY `Allocation_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `Lecturer_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `preferences`
--
ALTER TABLE `preferences`
  MODIFY `Preference_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `Project_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `Student_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `Tag_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tag_combinations`
--
ALTER TABLE `tag_combinations`
  MODIFY `Combination_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocation`
--
ALTER TABLE `allocation`
  ADD CONSTRAINT `fk_alloc_lecturer` FOREIGN KEY (`Lecturer_ID`) REFERENCES `lecturers` (`Lecturer_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alloc_project` FOREIGN KEY (`Project_ID`) REFERENCES `projects` (`Project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alloc_student` FOREIGN KEY (`Student_ID`) REFERENCES `students` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `preferences`
--
ALTER TABLE `preferences`
  ADD CONSTRAINT `fk_pref_project` FOREIGN KEY (`Project_ID`) REFERENCES `projects` (`Project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pref_student` FOREIGN KEY (`Student_ID`) REFERENCES `students` (`Student_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_project_lecturer` FOREIGN KEY (`Lecturer_ID`) REFERENCES `lecturers` (`Lecturer_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_tags`
--
ALTER TABLE `project_tags`
  ADD CONSTRAINT `fk_pt_project` FOREIGN KEY (`Project_ID`) REFERENCES `projects` (`Project_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pt_tag` FOREIGN KEY (`Tag_ID`) REFERENCES `tags` (`Tag_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tag_combinations`
--
ALTER TABLE `tag_combinations`
  ADD CONSTRAINT `fk_tc_tag1` FOREIGN KEY (`Tag1_ID`) REFERENCES `tags` (`Tag_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tc_tag2` FOREIGN KEY (`Tag2_ID`) REFERENCES `tags` (`Tag_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
