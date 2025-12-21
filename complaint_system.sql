-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 08:08 PM
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
-- Database: `complaint_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `designation` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `email`, `password`, `designation`, `department`) VALUES
(201, 'Admin Head', 'admin.head@mcccts.gov', 'admin123', 'Chief Administrator', 'Central Mgmt'),
(202, 'Security Admin', 'admin.sec@mcccts.gov', 'admin123', 'Security Analyst', 'Enforcement'),
(203, 'Technical Admin', 'admin.tech@mcccts.gov', 'admin123', 'System Manager', 'IT');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notification`
--

CREATE TABLE `admin_notification` (
  `admin_notification_id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `date_time` datetime NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notification`
--

INSERT INTO `admin_notification` (`admin_notification_id`, `admin_id`, `message`, `date_time`, `status`) VALUES
(1, 201, 'You received a new complaint: Complaint #3 (Garbage Collection). Filed by Anil Kumar.', '2025-03-25 08:30:00', 'read'),
(2, 201, 'Worker \"Deepak Engineer\" has completed the work for Complaint #1 (Road Damage).', '2025-03-05 15:45:00', 'read'),
(3, 201, 'Worker \"Ganesh Roadman\" has completed the work for Complaint #6 (Drain Blockage).', '2025-04-22 10:00:00', 'unread'),
(4, 202, 'You received a new complaint: Complaint #4 (Noise Complaint). Filed by Priya Sharma.', '2025-04-01 09:30:00', 'read'),
(5, 203, 'You received a new complaint: Complaint #7 (System Error). Filed by Vijay Singh.', '2025-05-01 07:00:00', 'unread'),
(6, 201, 'New Complaint Filed: #8 (Road Damage)', '2025-12-01 11:58:56', 'unread'),
(7, 202, 'New Complaint Filed: #8 (Road Damage)', '2025-12-01 11:58:56', 'unread'),
(8, 203, 'New Complaint Filed: #8 (Road Damage)', '2025-12-01 11:58:56', 'unread'),
(9, 201, 'New Complaint Filed: #9 (Garbage Collection)', '2025-12-13 22:04:07', 'unread'),
(10, 202, 'New Complaint Filed: #9 (Garbage Collection)', '2025-12-13 22:04:07', 'unread'),
(11, 203, 'New Complaint Filed: #9 (Garbage Collection)', '2025-12-13 22:04:07', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `citizen`
--

CREATE TABLE `citizen` (
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `date_registered` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `citizen`
--

INSERT INTO `citizen` (`citizen_id`, `name`, `email`, `password`, `phone_no`, `address`, `date_registered`) VALUES
(101, 'Anil Kumar', 'anil.k@mail.com', 'citizen123', '9876512340', 'Sector 14, Main Road, City A', '2025-01-15'),
(102, 'Priya Sharma', 'priya.s@mail.com', 'citizen123', '9998887770', 'Flat 5B, Green Towers, City B', '2025-02-20'),
(103, 'Vijay Singh', 'vijay.s@mail.com', 'citizen123', '9000011111', 'Old Madras Road, Near Temple', '2025-03-10'),
(105, '1231221', 'a@gmail.com', 'ssretry', 'abcdd', 'tergef', '2025-12-01'),
(107, 'abc', 'abc@gmail.com', 'ajqiw', '124', '177ns', '2025-12-01');

-- --------------------------------------------------------

--
-- Table structure for table `citizen_notification`
--

CREATE TABLE `citizen_notification` (
  `citizen_notification_id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `complaint_id` int(10) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `date_time` datetime NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `citizen_notification`
--

INSERT INTO `citizen_notification` (`citizen_notification_id`, `citizen_id`, `complaint_id`, `message`, `date_time`, `status`) VALUES
(1, 101, 1, 'Your complaint \"Road Damage\" (#1) has been assigned to worker \"Deepak Engineer\".', '2025-03-01 10:00:00', 'read'),
(2, 101, 1, 'Your complaint \"Road Damage\" (#1) has been marked as resolved.', '2025-03-05 15:30:00', 'read'),
(3, 101, 6, 'Your complaint \"Drain Blockage\" (#6) has been assigned to worker \"Ganesh Roadman\".', '2025-04-20 12:00:00', 'unread'),
(4, 102, 2, 'Your complaint \"Street Light Out\" (#2) has been assigned to worker \"Manish Light Tech\".', '2025-03-10 11:00:00', 'read'),
(5, 102, 4, 'Your complaint \"Noise Complaint\" (#4) has been rejected. Details provided by Admin Head.', '2025-04-01 10:30:00', 'read'),
(6, 103, 5, 'Your complaint \"Water Leakage\" (#5) has been assigned to worker \"Sanjay Plumber\".', '2025-04-15 16:30:00', 'unread'),
(7, 101, 8, 'Your complaint \'Road Damage\' (#8) has been successfully submitted.', '2025-12-01 11:58:56', 'unread'),
(8, 101, 8, 'Update: Your Complaint #8 has been assigned to a worker.', '2025-12-01 12:02:00', 'unread'),
(9, 103, 5, 'Success! Your Complaint #5 has been marked as Resolved.', '2025-12-01 12:03:49', 'unread'),
(10, 103, 5, 'Status update for complaint \'Water Leakage\': Resolved', '2025-12-01 12:03:49', 'unread'),
(11, 102, 9, 'Your complaint \'Garbage Collection\' (#9) has been successfully submitted.', '2025-12-13 22:04:07', 'read'),
(12, 102, 9, 'Update: Your Complaint #9 has been assigned to a worker.', '2025-12-13 22:06:28', 'read'),
(13, 102, 9, 'Status update for complaint \'Garbage Collection\': Rejected', '2025-12-13 22:08:12', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `complaint_id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `worker_id` int(10) UNSIGNED DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('pending','in_progress','resolved','closed','rejected') DEFAULT 'pending',
  `location` varchar(255) DEFAULT NULL,
  `filed_date` date NOT NULL,
  `resolved_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint`
--

INSERT INTO `complaint` (`complaint_id`, `citizen_id`, `admin_id`, `worker_id`, `category`, `description`, `severity`, `status`, `location`, `filed_date`, `resolved_date`) VALUES
(1, 101, 201, 302, 'Road Damage', 'Large pothole near main junction.', 'high', 'resolved', 'Near City A Main Market', '2025-03-01', '2025-03-05'),
(2, 102, 201, 306, 'Street Light Out', 'Two street lights non-functional on Green Towers road.', 'medium', 'in_progress', 'Green Towers Service Road', '2025-03-10', NULL),
(3, 101, NULL, NULL, 'Garbage Collection', 'Trash bin overflowing, not collected for 3 days.', 'low', 'pending', 'Sector 14 residential street', '2025-03-25', NULL),
(4, 102, 202, NULL, 'Noise Complaint', 'Loud music after 10 PM, not a municipal issue.', 'low', 'rejected', 'Flat 3C, Blue Apartments', '2025-04-01', NULL),
(5, 103, 201, 309, 'Water Leakage', 'Major water pipeline burst in front of the house.', 'critical', 'resolved', 'Old Madras Road Intersection', '2025-04-15', '2025-12-01'),
(6, 101, 201, 303, 'Drain Blockage', 'Main drainage line is fully blocked.', 'high', 'closed', 'Sector 14 drainage outlet', '2025-04-20', '2025-04-22'),
(7, 103, 201, 305, 'System Error', 'Unable to upload image proof during filing.', 'high', 'in_progress', 'Online Submission', '2025-05-01', NULL),
(8, 101, 201, 303, 'Road Damage', 'hole', 'medium', 'in_progress', 'abcd', '2025-12-01', NULL),
(9, 102, 201, 303, 'Garbage Collection', 'There is one huge garbage in main room of 304 named tinku jiya. pls collect it', 'critical', 'rejected', 'North town t 28', '2025-12-13', NULL);

--
-- Triggers `complaint`
--
DELIMITER $$
CREATE TRIGGER `after_complaint_insert` AFTER INSERT ON `complaint` FOR EACH ROW BEGIN
    -- System ke saare Admins ko notify karo
    INSERT INTO admin_notification (admin_id, message, status, date_time)
    SELECT 
        admin_id, 
        CONCAT('New Complaint Filed: #', NEW.complaint_id, ' (', NEW.category, ')'), 
        'unread', 
        CURRENT_TIMESTAMP 
    FROM admin;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_complaint_update` AFTER UPDATE ON `complaint` FOR EACH ROW BEGIN
    
    -- SCENARIO A: Worker Assign hua (Ya Change hua)
    IF NEW.worker_id IS NOT NULL AND (OLD.worker_id IS NULL OR OLD.worker_id != NEW.worker_id) THEN
        
        -- 1. Notify WORKER
        INSERT INTO worker_notification (worker_id, complaint_id, message, status, date_time)
        VALUES (
            NEW.worker_id,
            NEW.complaint_id,
            CONCAT('You have been assigned a new work: Complaint #', NEW.complaint_id, '. Please check details.'),
            'unread',
            CURRENT_TIMESTAMP
        );

        -- 2. Notify CITIZEN (Added complaint_id here)
        INSERT INTO citizen_notification (citizen_id, complaint_id, message, status, date_time)
        VALUES (
            NEW.citizen_id,
            NEW.complaint_id,
            CONCAT('Update: Your Complaint #', NEW.complaint_id, ' has been assigned to a worker.'),
            'unread',
            CURRENT_TIMESTAMP
        );
        
    END IF;

    -- SCENARIO B: Complaint Resolve ho gayi (Status change to 'resolved')
    IF NEW.status = 'resolved' AND OLD.status != 'resolved' THEN
        
        -- Notify CITIZEN only (Added complaint_id here too)
        INSERT INTO citizen_notification (citizen_id, complaint_id, message, status, date_time)
        VALUES (
            NEW.citizen_id,
            NEW.complaint_id,
            CONCAT('Success! Your Complaint #', NEW.complaint_id, ' has been marked as Resolved.'),
            'unread',
            CURRENT_TIMESTAMP
        );
        
    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `worker`
--

CREATE TABLE `worker` (
  `worker_id` int(10) UNSIGNED NOT NULL,
  `supervisor_worker_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `availability_status` enum('available','busy','offline') DEFAULT 'available',
  `department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `worker`
--

INSERT INTO `worker` (`worker_id`, `supervisor_worker_id`, `name`, `email`, `password`, `phone_no`, `availability_status`, `department`) VALUES
(301, NULL, 'Rajesh Civil Head', 'rajesh.h@cms.gov', 'worker123', '8000000001', 'available', 'Civil Works'),
(302, 301, 'Deepak Engineer', 'deepak.e@cms.gov', 'worker123', '8000000002', 'busy', 'Civil Works'),
(303, 301, 'Ganesh Roadman', 'ganesh.r@cms.gov', 'worker123', '8000000003', 'available', 'Civil Works'),
(304, 301, 'Kavita Mason', 'kavita.m@cms.gov', 'worker123', '8000000004', 'offline', 'Civil Works'),
(305, NULL, 'Shreya Electric Head', 'shreya.h@cms.gov', 'worker123', '8000000005', 'available', 'Electrical'),
(306, 305, 'Manish Light Tech', 'manish.t@cms.gov', 'worker123', '8000000006', 'available', 'Electrical'),
(307, 305, 'Pooja Wire Tech', 'pooja.t@cms.gov', 'worker123', '8000000007', 'busy', 'Electrical'),
(308, NULL, 'Vijay Water Head', 'vijay.h@cms.gov', 'worker123', '8000000008', 'available', 'Water Supply'),
(309, 308, 'Sanjay Plumber', 'sanjay.p@cms.gov', 'worker123', '8000000009', 'available', 'Water Supply'),
(310, 308, 'Ritu Pipeline Eng', 'ritu.e@cms.gov', 'worker123', '8000000010', 'available', 'Water Supply');

-- --------------------------------------------------------

--
-- Table structure for table `worker_notification`
--

CREATE TABLE `worker_notification` (
  `worker_notification_id` int(10) UNSIGNED NOT NULL,
  `worker_id` int(10) UNSIGNED NOT NULL,
  `complaint_id` int(10) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `date_time` datetime NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `worker_notification`
--

INSERT INTO `worker_notification` (`worker_notification_id`, `worker_id`, `complaint_id`, `message`, `date_time`, `status`) VALUES
(1, 302, 1, 'You have been assigned a new work: Complaint #1 (Road Damage). Severity: high.', '2025-03-01 09:45:00', 'read'),
(2, 306, 2, 'You have been assigned a new work: Complaint #2 (Street Light Out). Severity: medium.', '2025-03-10 10:45:00', 'unread'),
(3, 309, 5, 'You have been assigned a new work: Complaint #5 (Water Leakage). Severity: critical.', '2025-04-15 16:15:00', 'read'),
(4, 303, 6, 'You have been assigned a new work: Complaint #6 (Drain Blockage). Severity: high.', '2025-04-20 11:45:00', 'read'),
(5, 303, 8, 'You have been assigned a new work: Complaint #8. Please check details.', '2025-12-01 12:02:00', 'unread'),
(6, 303, 9, 'You have been assigned a new work: Complaint #9. Please check details.', '2025-12-13 22:06:28', 'unread');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_notification`
--
ALTER TABLE `admin_notification`
  ADD PRIMARY KEY (`admin_notification_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `citizen`
--
ALTER TABLE `citizen`
  ADD PRIMARY KEY (`citizen_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `citizen_notification`
--
ALTER TABLE `citizen_notification`
  ADD PRIMARY KEY (`citizen_notification_id`),
  ADD KEY `citizen_id` (`citizen_id`),
  ADD KEY `complaint_id` (`complaint_id`);

--
-- Indexes for table `complaint`
--
ALTER TABLE `complaint`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `citizen_id` (`citizen_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- Indexes for table `worker`
--
ALTER TABLE `worker`
  ADD PRIMARY KEY (`worker_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `supervisor_worker_id` (`supervisor_worker_id`);

--
-- Indexes for table `worker_notification`
--
ALTER TABLE `worker_notification`
  ADD PRIMARY KEY (`worker_notification_id`),
  ADD KEY `worker_id` (`worker_id`),
  ADD KEY `complaint_id` (`complaint_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `admin_notification`
--
ALTER TABLE `admin_notification`
  MODIFY `admin_notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `citizen`
--
ALTER TABLE `citizen`
  MODIFY `citizen_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `citizen_notification`
--
ALTER TABLE `citizen_notification`
  MODIFY `citizen_notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `complaint`
--
ALTER TABLE `complaint`
  MODIFY `complaint_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `worker`
--
ALTER TABLE `worker`
  MODIFY `worker_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=311;

--
-- AUTO_INCREMENT for table `worker_notification`
--
ALTER TABLE `worker_notification`
  MODIFY `worker_notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_notification`
--
ALTER TABLE `admin_notification`
  ADD CONSTRAINT `admin_notification_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `citizen_notification`
--
ALTER TABLE `citizen_notification`
  ADD CONSTRAINT `citizen_notification_ibfk_1` FOREIGN KEY (`citizen_id`) REFERENCES `citizen` (`citizen_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `citizen_notification_ibfk_2` FOREIGN KEY (`complaint_id`) REFERENCES `complaint` (`complaint_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `complaint`
--
ALTER TABLE `complaint`
  ADD CONSTRAINT `complaint_ibfk_1` FOREIGN KEY (`citizen_id`) REFERENCES `citizen` (`citizen_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `complaint_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `complaint_ibfk_3` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`worker_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `worker`
--
ALTER TABLE `worker`
  ADD CONSTRAINT `worker_ibfk_1` FOREIGN KEY (`supervisor_worker_id`) REFERENCES `worker` (`worker_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `worker_notification`
--
ALTER TABLE `worker_notification`
  ADD CONSTRAINT `worker_notification_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`worker_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `worker_notification_ibfk_2` FOREIGN KEY (`complaint_id`) REFERENCES `complaint` (`complaint_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
