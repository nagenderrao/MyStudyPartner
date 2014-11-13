-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 166.62.8.79
-- Generation Time: Dec 25, 2013 at 07:54 AM
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
-- Table structure for table `tbl_page_post_comments`
--

CREATE TABLE `tbl_page_post_comments` (
  `id` bigint(1) NOT NULL auto_increment,
  `comment_id` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_id` varchar(255) NOT NULL,
  `message` blob NOT NULL,
  `created_time` datetime NOT NULL,
  `like_count` int(11) NOT NULL,
  `user_likes` int(11) NOT NULL,
  `page_post_id` bigint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5418 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_search_by_page_info`
--

CREATE TABLE `tbl_search_by_page_info` (
  `id` bigint(1) NOT NULL auto_increment,
  `page_id` varchar(255) NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_category` varchar(80) NOT NULL,
  `search_type` enum('page') NOT NULL default 'page',
  `keyword_id` bigint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1134 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_search_by_page_post`
--

CREATE TABLE `tbl_search_by_page_post` (
  `id` bigint(1) NOT NULL auto_increment,
  `post_id` varchar(255) NOT NULL,
  `post_from_category` varchar(255) NOT NULL,
  `post_from_name` varchar(255) NOT NULL,
  `post_from_id` varchar(255) NOT NULL,
  `post_message` blob NOT NULL,
  `post_picture` text NOT NULL,
  `post_link` text NOT NULL,
  `post_name` text NOT NULL,
  `post_caption` varchar(255) NOT NULL,
  `post_type` varchar(255) NOT NULL,
  `post_description` blob NOT NULL,
  `post_created_time` datetime NOT NULL,
  `post_updated_time` datetime NOT NULL,
  `search_type` enum('post') NOT NULL,
  `keyword_id` bigint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=554 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_search_by_user_info`
--

CREATE TABLE `tbl_search_by_user_info` (
  `id` bigint(1) NOT NULL auto_increment,
  `profile_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `search_type` enum('user') NOT NULL default 'user',
  `keyword_id` bigint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1147 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_search_by_user_post`
--

CREATE TABLE `tbl_search_by_user_post` (
  `id` bigint(1) NOT NULL auto_increment,
  `post_id` varchar(255) NOT NULL,
  `post_from_category` varchar(255) NOT NULL,
  `post_from_name` varchar(255) NOT NULL,
  `post_from_id` varchar(255) NOT NULL,
  `post_message` blob NOT NULL,
  `post_picture` text NOT NULL,
  `post_link` text NOT NULL,
  `post_name` text NOT NULL,
  `post_caption` varchar(255) NOT NULL,
  `post_type` varchar(255) NOT NULL,
  `post_description` blob NOT NULL,
  `post_created_time` datetime NOT NULL,
  `post_updated_time` datetime NOT NULL,
  `search_type` enum('post') NOT NULL,
  `keyword_id` bigint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1134 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_page_info`
--

CREATE TABLE `tbl_user_page_info` (
  `page_id` varchar(255) NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_category` varchar(80) NOT NULL,
  `page_access_token` text NOT NULL,
  PRIMARY KEY  (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_post_comments`
--

CREATE TABLE `tbl_user_post_comments` (
  `id` bigint(1) NOT NULL auto_increment,
  `comment_id` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_id` varchar(255) NOT NULL,
  `message` blob NOT NULL,
  `created_time` datetime NOT NULL,
  `like_count` int(11) NOT NULL,
  `user_likes` int(11) NOT NULL,
  `user_post_id` bigint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6490 ;
