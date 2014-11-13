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
-- Table structure for table `tbl_twitter_data`
--

CREATE TABLE `tbl_twitter_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keywordId` int(11) unsigned NOT NULL,
  `screenName` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `tweet` longtext NOT NULL,
  `tweetdate` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=751 ;
