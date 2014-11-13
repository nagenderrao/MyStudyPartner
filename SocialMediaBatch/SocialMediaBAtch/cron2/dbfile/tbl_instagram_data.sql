-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 166.62.8.79
-- Generation Time: Dec 25, 2013 at 07:53 AM
-- Server version: 5.0.96
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wpBlogRss`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_instagram_data`
--

CREATE TABLE `tbl_instagram_data` (
  `auto_id` bigint(20) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `website` text NOT NULL,
  `profile_picture` text NOT NULL,
  `full_name` text NOT NULL,
  `id` bigint(20) NOT NULL,
  `keyword_id` bigint(20) NOT NULL,
  `inserttime` datetime NOT NULL,
  PRIMARY KEY  (`auto_id`),
  KEY `keyword_id` (`keyword_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=901 ;
