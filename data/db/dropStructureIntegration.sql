#  This file is part of the Simple demo web-project with REST Full API for Mobile.
#
#  This project is no longer maintained.
#  The project is written in Zend Framework 3 Release.
#
#  @link https://github.com/scorpion3dd
#  @copyright Copyright (c) 2016-2021 Denis Puzik <scorpion3dd@gmail.com>

SET NAMES 'utf8';
USE zf3_demo_integration;

DROP TABLE `role_hierarchy`;
DROP TABLE `role_permission`;
DROP TABLE `user_role`;
DROP TABLE `permission`;
DROP TABLE `role`;
DROP TABLE `user`;
DROP TABLE `user_log`;
DROP TABLE `user_role_log`;
DROP TABLE `user_archives`;
DROP FUNCTION IF EXISTS `randomInt`;
DROP PROCEDURE IF EXISTS `setUsersAccesses`;
DROP PROCEDURE IF EXISTS `setUsersNotAccesses`;
DROP PROCEDURE IF EXISTS `setUsersArchives`;
DROP PROCEDURE IF EXISTS `moveUsersArchives`;
