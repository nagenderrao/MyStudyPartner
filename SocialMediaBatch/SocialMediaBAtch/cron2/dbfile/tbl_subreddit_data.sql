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
-- Table structure for table `tbl_subreddit_data`
--

CREATE TABLE `tbl_subreddit_data` (
  `id` bigint(1) NOT NULL auto_increment,
  `keyword_id` bigint(1) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `subreddit` varchar(255) NOT NULL,
  `selftext` text NOT NULL,
  `likes` int(11) NOT NULL,
  `author` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `subreddit_id` varchar(255) NOT NULL,
  `permalink` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `url` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `num_comments` int(11) NOT NULL,
  `create_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=210 ;
