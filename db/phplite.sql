-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- 主机： 10.100.112.6
-- 生成日期： 2021-10-26 08:42:20
-- 服务器版本： 5.7.26-log
-- PHP 版本： 7.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `phplite`
--

-- --------------------------------------------------------

--
-- 表的结构 `alog`
--

CREATE TABLE `alog` (
  `id` int(4) NOT NULL,
  `log1` varchar(255) DEFAULT NULL,
  `log2` text,
  `log3` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='log';

-- --------------------------------------------------------

--
-- 表的结构 `wx_cache`
--

CREATE TABLE `wx_cache` (
  `id` int(4) NOT NULL,
  `appid` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `type` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `value` varchar(512) NOT NULL DEFAULT '',
  `expires_in` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='weixin token cache' ROW_FORMAT=DYNAMIC;

--
-- 转储表的索引
--

--
-- 表的索引 `alog`
--
ALTER TABLE `alog`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `wx_cache`
--
ALTER TABLE `wx_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appid` (`appid`),
  ADD KEY `type` (`type`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `alog`
--
ALTER TABLE `alog`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wx_cache`
--
ALTER TABLE `wx_cache`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
