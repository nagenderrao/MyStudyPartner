CREATE TABLE `tbl_keywords` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `tbl_facebook_page_post_comments` (
  `id` bigint(1) NOT NULL AUTO_INCREMENT,
  `comment_id` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_id` varchar(255) NOT NULL,
  `message` blob NOT NULL,
  `created_time` datetime NOT NULL,
  `like_count` int(11) NOT NULL,
  `user_likes` int(11) NOT NULL,
  `page_post_id` bigint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `tbl_facebook_search_by_page_info` (
  `id` bigint(1) NOT NULL AUTO_INCREMENT,
  `page_id` varchar(255) NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_category` varchar(80) NOT NULL,
  `search_type` enum('page') NOT NULL DEFAULT 'page',
  `project_id` bigint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



CREATE TABLE `tbl_facebook_search_by_page_post` (
  `id` bigint(1) NOT NULL AUTO_INCREMENT,
  `post_id` varchar(255) NOT NULL,
  `post_from_category` varchar(255) NOT NULL,
  `post_from_name` varchar(255) NOT NULL,
  `post_from_id` varchar(255) NOT NULL,
  `post_message` blob NOT NULL,
  `post_picture` text NOT NULL,
  `post_link` text NOT NULL,
  `post_name` text NOT NULL,
  `post_caption` blob NOT NULL,
  `post_type` varchar(255) NOT NULL,
  `post_description` blob NOT NULL,
  `post_created_time` datetime NOT NULL,
  `post_updated_time` datetime NOT NULL,
  `search_type` enum('post') NOT NULL,
  `project_id` bigint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT= 1 DEFAULT CHARSET=latin1;


CREATE TABLE `tbl_facebook_search_by_user_info` (
  `id` bigint(1) NOT NULL AUTO_INCREMENT,
  `profile_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `search_type` enum('user') NOT NULL DEFAULT 'user',
  `project_id` bigint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `tbl_facebook_search_by_user_post` (
  `id` bigint(1) NOT NULL AUTO_INCREMENT,
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
  `project_id` bigint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `tbl_facebook_user_info` (
  `user_access_token` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `tbl_facebook_user_page_info` (
  `page_id` varchar(255) NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_category` varchar(80) NOT NULL,
  `page_access_token` text NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `tbl_facebook_user_post_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_id` varchar(255) NOT NULL,
  `message` blob NOT NULL,
  `created_time` datetime NOT NULL,
  `like_count` int(11) NOT NULL,
  `user_likes` int(11) NOT NULL,
  `user_post_id` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `tbl_twitter_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) unsigned NOT NULL,
  `screenName` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `tweet` longtext NOT NULL,
  `tweetdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

