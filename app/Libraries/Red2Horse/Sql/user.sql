-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3307
-- Thời gian đã tạo: Th9 28, 2020 lúc 05:44 PM
-- Phiên bản máy phục vụ: 10.4.13-MariaDB
-- Phiên bản PHP: 7.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ci`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `username` varchar(32) NOT NULL DEFAULT 'unknown',
  `email` varchar(128) NOT NULL DEFAULT 'unknown',
  `password` varchar(64) NOT NULL DEFAULT 'unknown',
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'inactive',
  `selector` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `last_login` varchar(64) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `session_id` varchar(40) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `deleted_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

--
-- Đang đổ dữ liệu cho bảng `user`
--

INSERT INTO `user` (`id`, `group_id`, `username`, `email`, `password`, `status`, `selector`, `token`, `last_login`, `last_activity`, `session_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'administrator', 'webmaster@local.host', '$2y$10$vITbJQyusQXmr3ePgbHlG.roFKgUr0Bklra8VW.oHNuN4w/MOK1um', 'active', 'cf8c65cd58237af0', '695a64dbe84c5fd328c7e3989f06f62a2b20494c002d7309c8c267c4c2ab6f11', '::1', '2020-09-28 20:56:43', 'rsgoldeo81d0jhndevacl5lnnfis2snf', NULL, NULL, NULL),
(2, 2, 'tester', 'tester@local.host', '$2y$10$WCe3MOWEyxOiFPlwkyBOCuSGJxciOcyWSp8q8uFCfPQxYPmjdykHu', 'active', NULL, NULL, '::1', '2020-09-26 05:03:26', 'hg2hlvfru50ch6oekupio369d2qb2i7d', NULL, NULL, NULL),
(3, 3, 'member', 'member@local.host', '$2y$10$O1RBHXGvTUb6dzos5MJZM.KNjIB28oxh5hP.8cDCkyT6tYJFQHuU2', 'active', 'c533e56e3ee3d8ca', '8d0bb7a6e239b380186bceeec2de947ff3ed8cfd2ff66382afaa72039487e88c', '::1', '2020-09-27 00:20:48', 'j6ed0ft0pa2p33qbb935ahv0cmq8qrgq', NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
