-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 01:45 AM
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
-- Database: `itc127-cs2a-2025`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `usertype` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Dumping data for table `tblaccounts`
--

INSERT INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`, `created_at`) VALUES
('admin', '123456', 'ADMINISTRATOR', 'ACTIVE', 'admin', '02/10/2025', '2025-03-21 15:08:07'),
('staff', '123456', 'STAFF', 'ACTIVE', 'admin', '02/10/2025', '2025-03-21 15:08:07'),
('staff2', '123456', 'STAFF', 'ACTIVE', 'admin', '20/04/2025', '2025-04-20 10:39:59'),
('tech', '123456', 'TECHNICAL', 'ACTIVE', 'admin', '02/10/2025', '2025-03-21 15:08:07'),
('tech2', '123456', 'TECHNICAL', 'ACTIVE', 'admin', '03/04/2025', '2025-04-03 01:41:39'),
('User', '123456', 'USER', 'ACTIVE', 'admin', '21/03/2025', '2025-03-21 15:08:07');

-- --------------------------------------------------------

--
-- Table structure for table `tblequipments`
--

CREATE TABLE `tblequipments` (
  `assetnumber` varchar(50) NOT NULL,
  `serialnumber` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `manufacturer` varchar(50) NOT NULL,
  `yearmodel` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Dumping data for table `tblequipments`
--

INSERT INTO `tblequipments` (`assetnumber`, `serialnumber`, `type`, `manufacturer`, `yearmodel`, `description`, `branch`, `department`, `status`, `createdby`, `datecreated`, `created_at`) VALUES
('21-A12345', '321-123', 'Monitor', 'manila', '2015', 'Working', 'AU Legarda/Main', 'Institute of Accountancy', 'ON-REPAIR', 'tech', '2025-04-05', '2025-04-05 00:02:20'),
('21-A32133', '321-141', 'CPU', 'Tondo', '2020', 'Working', 'AU Pasay', 'College of Nursing', 'WORKING', 'tech', '2025-04-05', '2025-04-05 00:03:05'),
('22-B13555', '441-321', 'Keyboard', 'Manila', '2016', 'Working', 'Arellano School of Law', 'School of Psychology', 'RETIRED', 'tech', '2025-04-05', '2025-04-05 00:03:58'),
('23-C32153', '512-213', 'Mouse', 'Manila', '2022', 'Working', 'AU Pasig', 'College of Physical Therapy', 'WORKING', 'tech', '2025-04-05', '2025-04-05 00:04:44'),
('25-C19036', '321-213', 'Projector', 'dell', '2025', 'asdwajdswa', 'AU Pasay', 'College of General Education and Liberal Arts', 'WORKING', 'admin', '2025-04-20', '2025-04-20 10:41:48');

-- --------------------------------------------------------

--
-- Table structure for table `tblogs`
--

CREATE TABLE `tblogs` (
  `datelog` varchar(20) NOT NULL,
  `timelog` varchar(20) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(20) NOT NULL,
  `performedto` varchar(50) NOT NULL,
  `performedby` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Dumping data for table `tblogs`
--

INSERT INTO `tblogs` (`datelog`, `timelog`, `module`, `action`, `performedto`, `performedby`) VALUES
('21/04/2025', '07:24:19am', 'Accounts Management', 'Delete', 'johnmer', 'admin'),
('21/04/2025', '07:26:17am', 'Equipments Management', 'Update', '231-1233', 'admin'),
('21/04/2025', '07:27:23am', 'Equipments Management', 'Update', '231-1233', 'admin'),
('21/04/2025', '07:27:45am', 'Equipments Management', 'Delete', '231-1233', 'admin'),
('21/04/2025', '07:27:48am', 'Equipments Management', 'Delete', '231-123', 'admin'),
('21/04/2025', '07:30:21am', 'Equipments Management', 'Update', '21-A12345', 'admin'),
('21/04/2025', '07:39:55am', 'Equipments Management', 'Update', '25-C19036', 'admin'),
('21/04/2025', '07:42:07am', 'Equipments Management', 'Update', '25-C19036', 'admin'),
('21/04/2025', '07:42:25am', 'Equipments Management', 'Update', '25-C19036', 'admin'),
('21/04/2025', '07:42:35am', 'Equipments Management', 'Update', '25-C19036', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tbltickets`
--

CREATE TABLE `tbltickets` (
  `ticketnumber` varchar(50) NOT NULL,
  `problem` varchar(50) NOT NULL,
  `details` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL,
  `assignedto` varchar(255) DEFAULT NULL,
  `dateassigned` varchar(50) NOT NULL,
  `datecompleted` varchar(50) NOT NULL,
  `approvedby` varchar(50) NOT NULL,
  `dateapproved` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Dumping data for table `tbltickets`
--

INSERT INTO `tbltickets` (`ticketnumber`, `problem`, `details`, `status`, `createdby`, `datecreated`, `assignedto`, `dateassigned`, `datecompleted`, `approvedby`, `dateapproved`) VALUES
('20250420184524', 'Hardware', 'hard is ware', 'CLOSED', 'staff', '2025-04-20 18:45:24', 'tech', '20/04/2025', '20/04/2025', 'admin', '20/04/2025'),
('20250420184535', 'Software', 'soft is ware', 'FOR APPROVAL', 'staff', '2025-04-20 18:45:35', 'tech', '20/04/2025', '20/04/2025', '', ''),
('20250420184546', 'Connection', 'con nec', 'CLOSED', 'staff', '2025-04-20 18:45:46', 'tech', '20/04/2025', '20/04/2025', 'admin', '20/04/2025'),
('20250420184601', 'Hardware', 'awsa', 'ONGOING', 'staff', '2025-04-20 18:46:01', 'tech', '20/04/2025', '', '', ''),
('20250420184607', 'Connection', 'dwasdw', 'PENDING', 'staff', '2025-04-20 18:46:07', '', '', '', '', ''),
('20250420184648', 'Software', 'sadw', 'ONGOING', 'staff2', '2025-04-20 18:46:48', 'tech2', '20/04/2025', '', '', ''),
('20250420184653', 'Hardware', 'dwasd', 'ONGOING', 'staff2', '2025-04-20 18:46:53', 'tech2', '20/04/2025', '', '', ''),
('20250421072809', 'Hardware', 'wad', 'PENDING', 'admin', '2025-04-21 07:28:09', '', '', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `tblequipments`
--
ALTER TABLE `tblequipments`
  ADD PRIMARY KEY (`assetnumber`);

--
-- Indexes for table `tbltickets`
--
ALTER TABLE `tbltickets`
  ADD PRIMARY KEY (`ticketnumber`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
