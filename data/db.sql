/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Version : 50614
 Source Host           : localhost
 Source Database       : blog

 Target Server Version : 50614
 File Encoding         : utf-8

 Date: 04/17/2014 12:10:38 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `post`
-- ----------------------------
DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(64) NOT NULL,
  `written_on` datetime NOT NULL,
  `title` varchar(64) NOT NULL,
  `preview` text NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `body` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
