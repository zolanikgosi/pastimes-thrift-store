-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 08, 2024 at 02:55 PM
-- Server version: 5.7.40
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clothingstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

DROP TABLE IF EXISTS `tbladmin`;
CREATE TABLE IF NOT EXISTS `tbladmin` (
  `adminID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`adminID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`adminID`, `name`, `username`, `password`, `email`) VALUES
(1, 'Keaton Makuva', 'kmakuva', '$2y$10$hMmo16R.YtXbE5nQMA.zCe3IZOE4CgAUra1scvI3r86p4v9cNIR1a', 'keaton.makuva@gmail.com'),
(2, 'David White', 'dwhite', '$2y$10$.e/.I3Mo.LGCEbwO1wSvfeRPpAnPc9QplSW5rKu9bHvRFkC03krTW', 'dwhite@bing.com'),
(3, 'Olivia Black', 'oblack', '$2y$10$UWEo6HYB7E1FcvNg0EPpqepyIgOUw//83bmc66I1se7ZngwcerTHa', 'oblack@well.com'),
(4, 'James Blue', 'jblue', '$2y$10$TQUz.bt3xTJJ.UHPZM2.OuNFDnfI5wSFPmhaSbKoeMcmxbV0r3AP2', 'jblue@example.com'),
(5, 'Sophia Red', 'sred', '$2y$10$f44YvULKB2s5GmkFk4qEgeUrE65LUa3cHaAphy88Wa1pCT/N6N0rG', 'sred@dibbs.com');

-- --------------------------------------------------------

--
-- Table structure for table `tblaorder`
--

DROP TABLE IF EXISTS `tblaorder`;
CREATE TABLE IF NOT EXISTS `tblaorder` (
  `orderID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `orderDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `orderStatus` varchar(50) DEFAULT NULL,
  `totalAmount` decimal(10,2) NOT NULL,
  `shippingAddress` varchar(255) NOT NULL,
  PRIMARY KEY (`orderID`),
  KEY `userID` (`userID`),
  KEY `adminID` (`adminID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblaorder`
--

INSERT INTO `tblaorder` (`orderID`, `userID`, `adminID`, `orderDate`, `orderStatus`, `totalAmount`, `shippingAddress`) VALUES
(1, 2, 3, '2024-10-09 14:25:00', 'Shipped', '280.00', '456 Birch St Forest Hill'),
(2, 3, 2, '2024-10-10 07:50:00', 'Pending', '450.75', '789 Cedar St Mountain View'),
(3, 1, 3, '2024-10-11 10:15:00', 'Delivered', '180.25', '123 Walnut St Hilltop'),
(4, 3, 3, '2024-10-13 08:55:00', 'Canceled', '500.50', '789 Maple St Forest Hill'),
(5, 1, 1, '2024-10-14 11:20:00', 'Pending', '425.00', '123 Birch St Springfield');

-- --------------------------------------------------------

--
-- Table structure for table `tblclothes`
--

DROP TABLE IF EXISTS `tblclothes`;
CREATE TABLE IF NOT EXISTS `tblclothes` (
  `clothes_id` int(11) NOT NULL AUTO_INCREMENT,
  `clothes_name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(50) NOT NULL,
  `color` varchar(50) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`clothes_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblclothes`
--

INSERT INTO `tblclothes` (`clothes_id`, `clothes_name`, `description`, `price`, `size`, `color`, `stock_quantity`, `image_path`, `category`) VALUES
(1, 'Womens Shorts', 'A stylish short', '170.00', 'S', 'blue', 8, '_images/womens_shorts.jpg', 'Women'),
(2, 'Cargo Pants', 'Winter pants', '170.00', 'M', 'green', 8, '_images/cargo_pants.jpg', 'Women'),
(3, 'Red shirt ', 'summer clothing ', '350.00', 'M', 'red', 17, '_images/shirt.jpg', 'Men'),
(4, 'blue shirt ', 'summer clothing ', '350.00', 'M', 'blue', 17, '_images/tshirt.jpg', 'Men'),
(5, 'blue shirt ', 'summer clothing ', '350.00', 'M', 'blue', 17, '_images/blew.jpg', 'Men'),
(6, 'Womens Shorts', 'A stylish short', '170.00', 'S', 'blue', 8, '_images/monkey.jpg', 'Women');

-- --------------------------------------------------------

--
-- Table structure for table `tblseller`
--

DROP TABLE IF EXISTS `tblseller`;
CREATE TABLE IF NOT EXISTS `tblseller` (
  `sellerID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`sellerID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblseller`
--

INSERT INTO `tblseller` (`sellerID`, `name`, `username`, `password`, `email`, `status`) VALUES
(1, 'Victoria Ross', 'vross', '$2y$10$P3jTRUOh56N.CbIfE5JmauXxWbMDbE/NbStN6yEAszRvw6A7Wo79a', 'vross@domain.com', 'verified'),
(2, 'Ethan Brown', 'ebrown', '$2y$10$UD1kIeXhwWl5I26mfApLGOeGSCnOWfPi2GxprQzxsl5SEPhaG4jpe', 'ebrown@webmail.com', 'pending'),
(3, 'Ava Davis', 'adavis', '$2y$10$sR0ftIZ4s6sdi35Bve17kuvV1XkgvVsdDb38JHt/WCClZdujtIv4i', 'adavis@domain.com', 'pending'),
(4, 'Isabella White', 'iwhite', '$2y$10$JI4Ff8vPXdu1VwBsMls/XuXPSFHZ3sfmgkjaaYZa/3jRRYWNm7hqy', 'iwhite@domain.com', 'rejected'),
(5, 'Amelia Harris', 'aharris', '$2y$10$QULr1ZB01VOaLiVkJnWb6ectzgXn.gptCOaV.3Cwa4HHE5Q3HTDA.', 'aharris@mail.com', 'verified');

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

DROP TABLE IF EXISTS `tbluser`;
CREATE TABLE IF NOT EXISTS `tbluser` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`userID`, `name`, `username`, `password`, `email`, `status`) VALUES
(1, 'Kgosi Makuva', 'kgosi', '$2y$10$CsxqawqOqzqUximd/0l0.ur1r/xRKMwbQhhpJu1L4BA8hT6ZA284m', 'kgosimakuva@gmail.com', 'pending'),
(2, 'David Johnson', 'davidj', '$2y$10$8gd98exNHvYvIyeZkuR8Se3mq1QYYX698d/JmwTjZrtVXXgN3lstG', 'davidj@outlook.com', 'pending'),
(3, 'Emily Garcia', 'emilyg', '$2y$10$atMhLECoMxDrkwvnH7qThuUN1Be.L7fLCRGkYb1bPHlOz5DmrLm12', 'emilyg@gmail.com', 'pending'),
(4, 'Christine Lee', 'chrisl', '$2y$10$btAQy2mgfSGj5TQJ.GOGGe3rv96mGfskze1F6yL0ALlavY4FS/JU6', 'chrisl@live.com', 'pending'),
(5, 'Jack Martin', 'jackcam', '$2y$10$PgfMHkIOWaVV5gFJruF1vuHTb7sslpjXOjUQ7I2ooq1DAznFnY222', 'jackm@hotmail.com', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_item`
--

DROP TABLE IF EXISTS `tbl_item`;
CREATE TABLE IF NOT EXISTS `tbl_item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category` enum('Men','Women','Babies') NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_item`
--

INSERT INTO `tbl_item` (`item_id`, `item_name`, `description`, `price`, `size`, `color`, `stock_quantity`, `image_path`, `category`) VALUES
(1, 'Womens Shorts', 'A stylish short', '170.00', 'S', 'blue', 8, '_images/womens_shorts.jpg', 'Women'),
(2, 'Cargo Pants', 'Winter pants', '170.00', 'M', 'green', 8, '_images/cargo_pants.jpg', 'Women'),
(3, 'Red shirt ', 'summer clothing ', '350.00', 'M', 'red', 17, '_images/shirt.jpg', 'Men'),
(4, 'blue shirt ', 'summer clothing ', '350.00', 'M', 'blue', 17, '_images/tshirt.jpg', 'Men'),
(5, 'blue shirt ', 'summer clothing ', '350.00', 'M', 'blue', 17, '_images/blew.jpg', 'Men'),
(6, 'Womens Shorts', 'A stylish short', '170.00', 'S', 'blue', 8, '_images/monkey.jpg', 'Women');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
