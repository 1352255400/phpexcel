/*
Navicat MySQL Data Transfer

Source Server         : 本机
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : wenhua

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2018-07-25 10:24:01
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `demo`
-- ----------------------------
DROP TABLE IF EXISTS `demo`;
CREATE TABLE `demo` (
  `demo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `age` tinyint(4) DEFAULT NULL,
  `sex` tinyint(1) DEFAULT NULL,
  `time_add` int(10) DEFAULT NULL,
  `time_update` int(10) DEFAULT NULL,
  PRIMARY KEY (`demo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of demo
-- ----------------------------
INSERT INTO `demo` VALUES ('1', '姓名:1', '20', '1', '123123131', '123123131');
