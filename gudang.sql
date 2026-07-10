-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 10, 2026 at 05:40 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gudang`
--

-- --------------------------------------------------------

--
-- Table structure for table `asset_value_snapshots`
--

CREATE TABLE `asset_value_snapshots` (
  `id` bigint UNSIGNED NOT NULL,
  `snapshot_date` date NOT NULL,
  `warehouse_id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `avg_cost` decimal(15,4) NOT NULL,
  `asset_value` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_value_snapshots`
--

INSERT INTO `asset_value_snapshots` (`id`, `snapshot_date`, `warehouse_id`, `raw_material_id`, `qty`, `avg_cost`, `asset_value`, `created_at`) VALUES
(1, '2026-06-30', 1, 1, 120.0000, 53333.3333, 6400000.00, NULL),
(2, '2026-06-30', 1, 2, 70.0000, 1250000.0000, 87500000.00, NULL),
(3, '2026-06-30', 1, 3, 70.0000, 50000.0000, 3500000.00, NULL),
(4, '2026-06-30', 3, 3, 30.0000, 50000.0000, 1500000.00, NULL),
(5, '2026-06-30', 1, 4, 100.0000, 50000.0000, 5000000.00, NULL),
(6, '2026-06-30', 1, 5, 80.0000, 50000.0000, 4000000.00, NULL),
(7, '2026-06-30', 1, 6, 115.0000, 50000.0000, 5750000.00, NULL),
(8, '2026-06-30', 1, 7, 100.0000, 50000.0000, 5000000.00, NULL),
(9, '2026-06-30', 1, 8, 200.0000, 25000.0000, 5000000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-livewire-rate-limiter:1223b98e20b19479a9f740eb1141a1d82f9edda1', 'i:2;', 1783670840),
('laravel-cache-livewire-rate-limiter:1223b98e20b19479a9f740eb1141a1d82f9edda1:timer', 'i:1783670840;', 1783670840),
('laravel-cache-livewire-rate-limiter:949d30ea22f107bd43d44ad537056dcc17429d84', 'i:1;', 1783479149),
('laravel-cache-livewire-rate-limiter:949d30ea22f107bd43d44ad537056dcc17429d84:timer', 'i:1783479148;', 1783479149),
('laravel-cache-livewire-rate-limiter:9e9a2fcac32d30a065a3024200c3ab4f0d34fdc9', 'i:1;', 1783479266),
('laravel-cache-livewire-rate-limiter:9e9a2fcac32d30a065a3024200c3ab4f0d34fdc9:timer', 'i:1783479266;', 1783479266),
('laravel-cache-livewire-rate-limiter:c3597438b6db825a186eebdb1716460c2f52269e', 'i:1;', 1783479324),
('laravel-cache-livewire-rate-limiter:c3597438b6db825a186eebdb1716460c2f52269e:timer', 'i:1783479324;', 1783479324),
('laravel-cache-livewire-rate-limiter:f12dddcaf626baf4722743ae006637a37ed1ffb0', 'i:1;', 1783488778),
('laravel-cache-livewire-rate-limiter:f12dddcaf626baf4722743ae006637a37ed1ffb0:timer', 'i:1783488778;', 1783488778),
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:126:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:23:\"view_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:27:\"view_any_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:25:\"create_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:25:\"update_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:26:\"restore_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:30:\"restore_any_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:28:\"replicate_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:26:\"reorder_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:25:\"delete_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:29:\"delete_any_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:31:\"force_delete_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:35:\"force_delete_any_material::category\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:18:\"view_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:22:\"view_any_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:20:\"create_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:20:\"update_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:21:\"restore_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:25:\"restore_any_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:23:\"replicate_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:21:\"reorder_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:20:\"delete_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:24:\"delete_any_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:26:\"force_delete_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:30:\"force_delete_any_raw::material\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:9:\"view_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:11:\"create_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:11:\"update_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:11:\"delete_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:15:\"delete_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:27:\"view_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:31:\"view_any_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:29:\"create_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:29:\"update_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:30:\"restore_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:34:\"restore_any_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:32:\"replicate_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:30:\"reorder_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:29:\"delete_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:33:\"delete_any_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:35:\"force_delete_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:39:\"force_delete_any_stock::in::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:18:\"view_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:22:\"view_any_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:20:\"create_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:20:\"update_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:21:\"restore_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:25:\"restore_any_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:23:\"replicate_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:21:\"reorder_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:20:\"delete_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:24:\"delete_any_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:26:\"force_delete_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:30:\"force_delete_any_stock::opname\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:28:\"view_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:32:\"view_any_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:30:\"create_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:30:\"update_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:31:\"restore_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:35:\"restore_any_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:33:\"replicate_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:31:\"reorder_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:30:\"delete_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:34:\"delete_any_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:36:\"force_delete_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:40:\"force_delete_any_stock::out::transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:20:\"view_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:24:\"view_any_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:22:\"create_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:22:\"update_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:23:\"restore_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:27:\"restore_any_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:25:\"replicate_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:23:\"reorder_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:22:\"delete_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:26:\"delete_any_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:28:\"force_delete_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:32:\"force_delete_any_stock::transfer\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:13:\"view_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:17:\"view_any_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:15:\"create_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:15:\"update_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:16:\"restore_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:20:\"restore_any_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:18:\"replicate_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:16:\"reorder_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:15:\"delete_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:19:\"delete_any_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:21:\"force_delete_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:25:\"force_delete_any_supplier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:9:\"view_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:13:\"view_any_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:11:\"create_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:11:\"update_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:12:\"restore_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:16:\"restore_any_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:14:\"replicate_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:12:\"reorder_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:11:\"delete_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:15:\"delete_any_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:17:\"force_delete_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:21:\"force_delete_any_unit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:14:\"view_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:18:\"view_any_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:16:\"create_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:16:\"update_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:17:\"restore_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:21:\"restore_any_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:19:\"replicate_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:17:\"reorder_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:16:\"delete_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:20:\"delete_any_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:22:\"force_delete_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:26:\"force_delete_any_warehouse\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:24:\"page_CriticalStockReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:28:\"page_CurrentAssetValueReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:31:\"page_HistoricalAssetValueReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:20:\"page_StockCardReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:25:\"page_StockInSummaryReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:119;a:4:{s:1:\"a\";i:120;s:1:\"b\";s:22:\"page_StockOpnameReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:26:\"page_StockOutSummaryReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:121;a:4:{s:1:\"a\";i:122;s:1:\"b\";s:19:\"page_TransferReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:30:\"page_WarehouseComparisonReport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:123;a:4:{s:1:\"a\";i:124;s:1:\"b\";s:28:\"widget_TotalAssetValueWidget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:30:\"widget_CriticalStockListWidget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:125;a:4:{s:1:\"a\";i:126;s:1:\"b\";s:31:\"widget_StockMovementChartWidget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"superadmin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}', 1783695061);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_number_counters`
--

CREATE TABLE `document_number_counters` (
  `id` bigint UNSIGNED NOT NULL,
  `document_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_number` int UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_number_counters`
--

INSERT INTO `document_number_counters` (`id`, `document_type`, `period`, `last_number`, `updated_at`) VALUES
(1, 'stock_in', '202607', 7, '2026-07-06 19:21:04'),
(2, 'stock_out', '202607', 2, '2026-07-06 19:19:51'),
(3, 'stock_transfer', '202607', 1, '2026-07-06 19:06:32'),
(5, 'stock_opname', '202607', 2, '2026-07-06 19:17:42');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_categories`
--

CREATE TABLE `material_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_categories`
--

INSERT INTO `material_categories` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Kain', 'KIN', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(2, 'Benang', 'BNG', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(3, 'Kancing', 'KNC', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(4, 'Resleting', 'RSL', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(5, 'Karet', 'KRT', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(6, 'Label', 'LBL', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(7, 'Kemasan', 'KMS', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(8, 'Bahan Pelapis', 'LPS', '2026-07-06 18:34:02', '2026-07-06 18:34:02');

-- --------------------------------------------------------

--
-- Table structure for table `material_stocks`
--

CREATE TABLE `material_stocks` (
  `id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `warehouse_id` bigint UNSIGNED NOT NULL,
  `min_stock` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `current_stock` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `current_avg_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `current_asset_value` decimal(18,2) NOT NULL DEFAULT '0.00',
  `last_notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_stocks`
--

INSERT INTO `material_stocks` (`id`, `raw_material_id`, `warehouse_id`, `min_stock`, `current_stock`, `current_avg_cost`, `current_asset_value`, `last_notified_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0.0000, 120.0000, 53333.3333, 6400000.00, NULL, '2026-07-06 18:55:45', '2026-07-06 18:55:45'),
(2, 2, 1, 0.0000, 70.0000, 1250000.0000, 87500000.00, NULL, '2026-07-06 18:57:03', '2026-07-06 18:57:03'),
(3, 3, 1, 0.0000, 70.0000, 50000.0000, 3500000.00, NULL, '2026-07-06 19:06:32', '2026-07-06 19:06:32'),
(4, 3, 3, 0.0000, 30.0000, 50000.0000, 1500000.00, NULL, '2026-07-06 19:06:32', '2026-07-06 19:06:32'),
(5, 4, 1, 0.0000, 100.0000, 50000.0000, 5000000.00, NULL, '2026-07-06 19:07:37', '2026-07-06 19:07:37'),
(6, 5, 1, 0.0000, 80.0000, 50000.0000, 4000000.00, NULL, '2026-07-06 19:17:42', '2026-07-06 19:17:42'),
(7, 6, 1, 20.0000, 115.0000, 50000.0000, 5750000.00, NULL, '2026-07-06 19:19:51', '2026-07-06 19:19:51'),
(8, 7, 1, 0.0000, 100.0000, 50000.0000, 999999.00, NULL, '2026-07-06 19:21:04', '2026-07-06 19:21:04'),
(9, 8, 1, 0.0000, 200.0000, 25000.0000, 5000000.00, NULL, '2026-07-06 19:21:04', '2026-07-06 19:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_06_135041_create_notifications_table', 1),
(5, '2026_07_06_135143_create_permission_tables', 1),
(6, '2026_07_06_135902_create_material_categories_table', 1),
(7, '2026_07_06_135903_create_suppliers_table', 1),
(8, '2026_07_06_135903_create_units_table', 1),
(9, '2026_07_06_135903_create_warehouses_table', 1),
(10, '2026_07_06_135904_create_raw_materials_table', 1),
(11, '2026_07_06_135904_create_unit_conversions_table', 1),
(12, '2026_07_06_140509_create_material_stocks_table', 1),
(13, '2026_07_06_140510_create_stock_ledgers_table', 1),
(14, '2026_07_06_140511_create_document_number_counters_table', 1),
(15, '2026_07_06_141802_create_stock_in_transactions_table', 1),
(16, '2026_07_06_141803_create_stock_in_details_table', 1),
(17, '2026_07_06_141805_create_stock_out_transactions_table', 1),
(18, '2026_07_06_141806_create_stock_out_details_table', 1),
(19, '2026_07_06_150052_create_stock_transfers_table', 1),
(20, '2026_07_06_150053_create_stock_transfer_details_table', 1),
(21, '2026_07_07_000851_create_stock_opnames_table', 1),
(22, '2026_07_07_000852_create_stock_opname_details_table', 1),
(23, '2026_07_07_000853_add_locked_by_opname_fk_to_warehouses', 1),
(24, '2026_07_07_012637_create_asset_value_snapshots_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(2, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 5),
(1, 'App\\Models\\User', 7);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(2, 'view_any_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(3, 'create_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(4, 'update_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(5, 'restore_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(6, 'restore_any_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(7, 'replicate_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(8, 'reorder_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(9, 'delete_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(10, 'delete_any_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(11, 'force_delete_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(12, 'force_delete_any_material::category', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(13, 'view_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(14, 'view_any_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(15, 'create_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(16, 'update_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(17, 'restore_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(18, 'restore_any_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(19, 'replicate_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(20, 'reorder_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(21, 'delete_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(22, 'delete_any_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(23, 'force_delete_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(24, 'force_delete_any_raw::material', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(25, 'view_role', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(26, 'view_any_role', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(27, 'create_role', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(28, 'update_role', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(29, 'delete_role', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(30, 'delete_any_role', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(31, 'view_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(32, 'view_any_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(33, 'create_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(34, 'update_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(35, 'restore_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(36, 'restore_any_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(37, 'replicate_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(38, 'reorder_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(39, 'delete_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(40, 'delete_any_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(41, 'force_delete_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(42, 'force_delete_any_stock::in::transaction', 'web', '2026-07-06 19:46:32', '2026-07-06 19:46:32'),
(43, 'view_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(44, 'view_any_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(45, 'create_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(46, 'update_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(47, 'restore_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(48, 'restore_any_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(49, 'replicate_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(50, 'reorder_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(51, 'delete_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(52, 'delete_any_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(53, 'force_delete_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(54, 'force_delete_any_stock::opname', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(55, 'view_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(56, 'view_any_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(57, 'create_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(58, 'update_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(59, 'restore_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(60, 'restore_any_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(61, 'replicate_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(62, 'reorder_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(63, 'delete_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(64, 'delete_any_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(65, 'force_delete_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(66, 'force_delete_any_stock::out::transaction', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(67, 'view_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(68, 'view_any_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(69, 'create_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(70, 'update_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(71, 'restore_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(72, 'restore_any_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(73, 'replicate_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(74, 'reorder_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(75, 'delete_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(76, 'delete_any_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(77, 'force_delete_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(78, 'force_delete_any_stock::transfer', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(79, 'view_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(80, 'view_any_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(81, 'create_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(82, 'update_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(83, 'restore_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(84, 'restore_any_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(85, 'replicate_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(86, 'reorder_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(87, 'delete_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(88, 'delete_any_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(89, 'force_delete_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(90, 'force_delete_any_supplier', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(91, 'view_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(92, 'view_any_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(93, 'create_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(94, 'update_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(95, 'restore_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(96, 'restore_any_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(97, 'replicate_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(98, 'reorder_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(99, 'delete_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(100, 'delete_any_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(101, 'force_delete_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(102, 'force_delete_any_unit', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(103, 'view_warehouse', 'web', '2026-07-06 19:46:33', '2026-07-06 19:46:33'),
(104, 'view_any_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(105, 'create_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(106, 'update_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(107, 'restore_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(108, 'restore_any_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(109, 'replicate_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(110, 'reorder_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(111, 'delete_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(112, 'delete_any_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(113, 'force_delete_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(114, 'force_delete_any_warehouse', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(115, 'page_CriticalStockReport', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(116, 'page_CurrentAssetValueReport', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(117, 'page_HistoricalAssetValueReport', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(118, 'page_StockCardReport', 'web', '2026-07-06 19:46:34', '2026-07-06 19:46:34'),
(119, 'page_StockInSummaryReport', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35'),
(120, 'page_StockOpnameReport', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35'),
(121, 'page_StockOutSummaryReport', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35'),
(122, 'page_TransferReport', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35'),
(123, 'page_WarehouseComparisonReport', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35'),
(124, 'widget_TotalAssetValueWidget', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35'),
(125, 'widget_CriticalStockListWidget', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35'),
(126, 'widget_StockMovementChartWidget', 'web', '2026-07-06 19:46:35', '2026-07-06 19:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `raw_materials`
--

CREATE TABLE `raw_materials` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_category_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `raw_materials`
--

INSERT INTO `raw_materials` (`id`, `code`, `name`, `material_category_id`, `unit_id`, `image`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'CHECK-001', 'Check Test Item', 1, 1, NULL, 1, NULL, '2026-07-06 18:55:45', '2026-07-06 18:55:45'),
(2, 'CHECK-002', 'Check Stock In/Out', 1, 1, NULL, 1, NULL, '2026-07-06 18:57:03', '2026-07-06 18:57:03'),
(3, 'CHECK-003', 'Check Transfer', 1, 1, NULL, 1, NULL, '2026-07-06 19:06:32', '2026-07-06 19:06:32'),
(4, 'CHECK-005', 'Check Opname', 1, 1, NULL, 1, NULL, '2026-07-06 19:07:37', '2026-07-06 19:07:37'),
(5, 'CHECK-OP5', 'Check Opname Final', 1, 1, NULL, 1, NULL, '2026-07-06 19:17:42', '2026-07-06 19:17:42'),
(6, 'CHECK-NOTIF', 'Check Notification', 1, 1, NULL, 1, NULL, '2026-07-06 19:19:51', '2026-07-06 19:19:51'),
(7, 'RPT-001', 'Report Item 1', 1, 1, NULL, 1, NULL, '2026-07-06 19:21:04', '2026-07-06 19:21:04'),
(8, 'RPT-002', 'Report Item 2', 1, 1, NULL, 1, NULL, '2026-07-06 19:21:04', '2026-07-06 19:21:04'),
(9, 'FDEL-TEST', 'ForceDelete Test', 1, 1, NULL, 1, NULL, '2026-07-06 19:22:31', '2026-07-06 19:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-07-06 19:19:50', '2026-07-06 19:19:50'),
(2, 'superadmin', 'web', '2026-07-06 19:49:53', '2026-07-06 19:49:53'),
(3, 'admin', 'web', '2026-07-06 19:49:53', '2026-07-06 19:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(126, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(56, 2),
(57, 2),
(58, 2),
(59, 2),
(60, 2),
(61, 2),
(62, 2),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 2),
(70, 2),
(71, 2),
(72, 2),
(73, 2),
(74, 2),
(75, 2),
(76, 2),
(77, 2),
(78, 2),
(79, 2),
(80, 2),
(81, 2),
(82, 2),
(83, 2),
(84, 2),
(85, 2),
(86, 2),
(87, 2),
(88, 2),
(89, 2),
(90, 2),
(91, 2),
(92, 2),
(93, 2),
(94, 2),
(95, 2),
(96, 2),
(97, 2),
(98, 2),
(99, 2),
(100, 2),
(101, 2),
(102, 2),
(103, 2),
(104, 2),
(105, 2),
(106, 2),
(107, 2),
(108, 2),
(109, 2),
(110, 2),
(111, 2),
(112, 2),
(113, 2),
(114, 2),
(115, 2),
(116, 2),
(117, 2),
(118, 2),
(119, 2),
(120, 2),
(121, 2),
(122, 2),
(123, 2),
(124, 2),
(125, 2),
(126, 2),
(1, 3),
(2, 3),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(9, 3),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(21, 3),
(31, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(36, 3),
(39, 3),
(43, 3),
(44, 3),
(45, 3),
(46, 3),
(47, 3),
(48, 3),
(51, 3),
(55, 3),
(56, 3),
(57, 3),
(58, 3),
(59, 3),
(60, 3),
(63, 3),
(67, 3),
(68, 3),
(69, 3),
(70, 3),
(71, 3),
(72, 3),
(75, 3),
(79, 3),
(80, 3),
(81, 3),
(82, 3),
(83, 3),
(84, 3),
(87, 3),
(91, 3),
(92, 3),
(93, 3),
(94, 3),
(95, 3),
(96, 3),
(99, 3),
(103, 3),
(104, 3),
(105, 3),
(106, 3),
(107, 3),
(108, 3),
(111, 3),
(115, 3),
(116, 3),
(117, 3),
(118, 3),
(119, 3),
(120, 3),
(121, 3),
(122, 3),
(123, 3),
(124, 3),
(125, 3),
(126, 3);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('cwVvFBpFm2zHvFwtHIVyy8AT5EcisoatHd1uPA38', NULL, '192.168.110.196', 'WhatsApp/2.2625.101 W', 'eyJfdG9rZW4iOiJ4RUNZT1lrdm9neGRodTB6cm9zek5HemtJZk5qUUQ3U0lJbWlnUzNWIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2JyZWFrLW1pbmlzdGVycy1wb2lzb24tZHVyYWJsZS50cnljbG91ZGZsYXJlLmNvbSIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783670728),
('FQs4iW2qELepMrGKKcQCinXpV1T18PmTwdoN3H51', 1, '192.168.110.196', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJhOFZ4OWxldnVCVWJ1UFVXUXV0bTNhaUxJamJYanFBam5JVDQzcnpKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2JyZWFrLW1pbmlzdGVycy1wb2lzb24tZHVyYWJsZS50cnljbG91ZGZsYXJlLmNvbVwvYWRtaW4iLCJyb3V0ZSI6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInVybCI6W10sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6IjVhOTZhY2IyMDQzNjE2MWJhZWExNmQ5N2VlMzJkODgxNjI3YzJjYmQyOTNjNjE4ZDEyZGZmN2IzOWNlZjRlZWMifQ==', 1783675820),
('pKxycGzNjbwjKeKQy7YLvp6AaYM9tcUeFiPZA4kn', 1, '192.168.110.196', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJPTjNPOHhpQXlDUzU4NE5XRGdKRm03SzlRQWwyVWRERUxEN0pjS3llIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2JyZWFrLW1pbmlzdGVycy1wb2lzb24tZHVyYWJsZS50cnljbG91ZGZsYXJlLmNvbVwvYWRtaW4iLCJyb3V0ZSI6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInVybCI6W10sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6IjVhOTZhY2IyMDQzNjE2MWJhZWExNmQ5N2VlMzJkODgxNjI3YzJjYmQyOTNjNjE4ZDEyZGZmN2IzOWNlZjRlZWMifQ==', 1783670982),
('xyffqzJdeZ59mHVo24XYvEfHWQlegpEFq5miU2vQ', NULL, '192.168.110.196', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 'eyJfdG9rZW4iOiJNQ05KY2FKRG1NU2F3WE9abVRndURFbGN2a0Z5MEdGNmpkNVhXUE1qIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2JyZWFrLW1pbmlzdGVycy1wb2lzb24tZHVyYWJsZS50cnljbG91ZGZsYXJlLmNvbSIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783670764),
('ZLDeEB9I6Xmw1VlwRgjl9RNSnKC0cR8RCpSLEkNB', 1, '192.168.110.196', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJNTTFWb3NhYnVKbXpIajJ2VDVvaHAzS0pDN3IweUYzMk8wTkpNY3RHIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2JyZWFrLW1pbmlzdGVycy1wb2lzb24tZHVyYWJsZS50cnljbG91ZGZsYXJlLmNvbVwvYWRtaW4iLCJyb3V0ZSI6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInVybCI6W10sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6IjVhOTZhY2IyMDQzNjE2MWJhZWExNmQ5N2VlMzJkODgxNjI3YzJjYmQyOTNjNjE4ZDEyZGZmN2IzOWNlZjRlZWMifQ==', 1783676233);

-- --------------------------------------------------------

--
-- Table structure for table `stock_in_details`
--

CREATE TABLE `stock_in_details` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_in_transaction_id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `qty_base` decimal(15,4) NOT NULL,
  `unit_price` decimal(15,4) NOT NULL,
  `subtotal` decimal(18,2) NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_in_details`
--

INSERT INTO `stock_in_details` (`id`, `stock_in_transaction_id`, `raw_material_id`, `unit_id`, `qty`, `qty_base`, `unit_price`, `subtotal`, `notes`) VALUES
(1, 1, 2, 2, 4.0000, 100.0000, 1250000.0000, 5000000.00, NULL),
(2, 2, 3, 1, 100.0000, 100.0000, 50000.0000, 5000000.00, NULL),
(3, 3, 4, 1, 100.0000, 100.0000, 50000.0000, 5000000.00, NULL),
(4, 4, 5, 1, 100.0000, 100.0000, 50000.0000, 5000000.00, NULL),
(5, 5, 6, 1, 100.0000, 100.0000, 50000.0000, 5000000.00, NULL),
(6, 6, 7, 1, 100.0000, 100.0000, 50000.0000, 5000000.00, NULL),
(7, 6, 8, 1, 200.0000, 200.0000, 25000.0000, 5000000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_in_transactions`
--

CREATE TABLE `stock_in_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `transaction_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `warehouse_id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('purchase','production_return','adjustment_add') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_in_transactions`
--

INSERT INTO `stock_in_transactions` (`id`, `transaction_number`, `transaction_date`, `warehouse_id`, `supplier_id`, `type`, `reference_number`, `attachment`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'SIN-2026070002', '2026-07-15', 1, NULL, 'purchase', 'INV-001', NULL, 'Test', 1, '2026-07-06 18:57:03', '2026-07-06 18:57:03'),
(2, 'SIN-2026070003', '2026-07-15', 1, NULL, 'purchase', NULL, NULL, NULL, 2, '2026-07-06 19:06:32', '2026-07-06 19:06:32'),
(3, 'SIN-2026070004', '2026-07-15', 1, NULL, 'purchase', NULL, NULL, NULL, 3, '2026-07-06 19:07:37', '2026-07-06 19:07:37'),
(4, 'SIN-2026070005', '2026-07-15', 1, NULL, 'purchase', NULL, NULL, NULL, 4, '2026-07-06 19:17:42', '2026-07-06 19:17:42'),
(5, 'SIN-2026070006', '2026-07-07', 1, NULL, 'purchase', NULL, NULL, NULL, 5, '2026-07-06 19:19:51', '2026-07-06 19:19:51'),
(6, 'SIN-2026070007', '2026-07-01', 1, NULL, 'purchase', NULL, NULL, NULL, 6, '2026-07-06 19:21:04', '2026-07-06 19:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `stock_ledgers`
--

CREATE TABLE `stock_ledgers` (
  `id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `warehouse_id` bigint UNSIGNED NOT NULL,
  `transaction_date` date NOT NULL,
  `direction` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` bigint UNSIGNED NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `unit_cost` decimal(15,4) NOT NULL,
  `running_qty_balance` decimal(15,4) NOT NULL,
  `running_avg_cost` decimal(15,4) NOT NULL,
  `running_asset_value` decimal(18,2) NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_ledgers`
--

INSERT INTO `stock_ledgers` (`id`, `raw_material_id`, `warehouse_id`, `transaction_date`, `direction`, `source_type`, `source_id`, `qty`, `unit_cost`, `running_qty_balance`, `running_avg_cost`, `running_asset_value`, `notes`, `created_at`) VALUES
(1, 1, 1, '2026-07-07', 'in', 'test', 1, 100.0000, 50000.0000, 100.0000, 50000.0000, 5000000.00, NULL, '2026-07-06 18:55:45'),
(2, 1, 1, '2026-07-07', 'in', 'test', 2, 50.0000, 60000.0000, 150.0000, 53333.3333, 8000000.00, NULL, '2026-07-06 18:55:45'),
(3, 1, 1, '2026-07-07', 'out', 'test', 3, 30.0000, 53333.3333, 120.0000, 53333.3333, 6400000.00, NULL, '2026-07-06 18:55:45'),
(4, 2, 1, '2026-07-15', 'in', 'stock_in', 1, 100.0000, 1250000.0000, 100.0000, 1250000.0000, 125000000.00, NULL, '2026-07-06 18:57:03'),
(5, 2, 1, '2026-07-16', 'out', 'stock_out', 1, 30.0000, 1250000.0000, 70.0000, 1250000.0000, 87500000.00, NULL, '2026-07-06 18:57:03'),
(6, 3, 1, '2026-07-15', 'in', 'stock_in', 2, 100.0000, 50000.0000, 100.0000, 50000.0000, 5000000.00, NULL, '2026-07-06 19:06:32'),
(7, 3, 1, '2026-07-16', 'out', 'transfer_out', 1, 30.0000, 50000.0000, 70.0000, 50000.0000, 3500000.00, NULL, '2026-07-06 19:06:32'),
(8, 3, 3, '2026-07-16', 'in', 'transfer_in', 1, 30.0000, 50000.0000, 30.0000, 50000.0000, 1500000.00, NULL, '2026-07-06 19:06:32'),
(9, 4, 1, '2026-07-15', 'in', 'stock_in', 3, 100.0000, 50000.0000, 100.0000, 50000.0000, 5000000.00, NULL, '2026-07-06 19:07:37'),
(10, 5, 1, '2026-07-15', 'in', 'stock_in', 4, 100.0000, 50000.0000, 100.0000, 50000.0000, 5000000.00, NULL, '2026-07-06 19:17:42'),
(11, 5, 1, '2026-07-20', 'out', 'opname_adjustment', 1, 20.0000, 50000.0000, 80.0000, 50000.0000, 4000000.00, 'Stock opname adjustment: OPN-2026070001', '2026-07-06 19:17:42'),
(12, 6, 1, '2026-07-07', 'out', 'stock_out', 2, 85.0000, 50000.0000, 15.0000, 50000.0000, 750000.00, NULL, '2026-07-06 19:19:51'),
(13, 6, 1, '2026-07-07', 'in', 'stock_in', 5, 100.0000, 50000.0000, 115.0000, 50000.0000, 5750000.00, NULL, '2026-07-06 19:19:51'),
(14, 7, 1, '2026-07-01', 'in', 'stock_in', 6, 100.0000, 50000.0000, 100.0000, 50000.0000, 5000000.00, NULL, '2026-07-06 19:21:04'),
(15, 8, 1, '2026-07-01', 'in', 'stock_in', 6, 200.0000, 25000.0000, 200.0000, 25000.0000, 5000000.00, NULL, '2026-07-06 19:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `stock_opnames`
--

CREATE TABLE `stock_opnames` (
  `id` bigint UNSIGNED NOT NULL,
  `opname_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opname_date` date NOT NULL,
  `warehouse_id` bigint UNSIGNED NOT NULL,
  `status` enum('counting','finalized','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_at` timestamp NOT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_opnames`
--

INSERT INTO `stock_opnames` (`id`, `opname_number`, `opname_date`, `warehouse_id`, `status`, `started_at`, `finalized_at`, `cancelled_at`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'OPN-2026070001', '2026-07-20', 1, 'finalized', '2026-07-06 19:17:42', '2026-07-06 19:17:42', NULL, 'Check', 4, '2026-07-06 19:17:42', '2026-07-06 19:17:42'),
(2, 'OPN-2026070002', '2026-07-21', 1, 'cancelled', '2026-07-06 19:17:42', NULL, '2026-07-06 19:17:42', 'Cancel', 4, '2026-07-06 19:17:42', '2026-07-06 19:17:42'),
(3, 'OPN-AUTO-001', '2026-07-05', 1, 'cancelled', '2026-07-05 18:17:42', NULL, '2026-07-06 19:17:42', NULL, 4, '2026-07-06 19:17:42', '2026-07-06 19:17:42');

-- --------------------------------------------------------

--
-- Table structure for table `stock_opname_details`
--

CREATE TABLE `stock_opname_details` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_opname_id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `system_qty` decimal(15,4) NOT NULL,
  `physical_qty_unit_id` bigint UNSIGNED NOT NULL,
  `physical_qty` decimal(15,4) NOT NULL,
  `physical_qty_base` decimal(15,4) NOT NULL,
  `difference_qty` decimal(15,4) NOT NULL,
  `avg_cost_at_opname` decimal(15,4) NOT NULL,
  `difference_value` decimal(18,2) NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_opname_details`
--

INSERT INTO `stock_opname_details` (`id`, `stock_opname_id`, `raw_material_id`, `system_qty`, `physical_qty_unit_id`, `physical_qty`, `physical_qty_base`, `difference_qty`, `avg_cost_at_opname`, `difference_value`, `notes`) VALUES
(1, 1, 5, 100.0000, 1, 80.0000, 80.0000, -20.0000, 50000.0000, -1000000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_out_details`
--

CREATE TABLE `stock_out_details` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_out_transaction_id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `qty_base` decimal(15,4) NOT NULL,
  `cost_at_issue` decimal(15,4) NOT NULL,
  `subtotal_hpp` decimal(18,2) NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_out_details`
--

INSERT INTO `stock_out_details` (`id`, `stock_out_transaction_id`, `raw_material_id`, `unit_id`, `qty`, `qty_base`, `cost_at_issue`, `subtotal_hpp`, `notes`) VALUES
(1, 1, 2, 1, 30.0000, 30.0000, 1250000.0000, 37500000.00, NULL),
(2, 2, 6, 1, 85.0000, 85.0000, 50000.0000, 4250000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_out_transactions`
--

CREATE TABLE `stock_out_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `transaction_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `warehouse_id` bigint UNSIGNED NOT NULL,
  `type` enum('production_usage','supplier_return','adjustment_reduce','damaged_lost') COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_out_transactions`
--

INSERT INTO `stock_out_transactions` (`id`, `transaction_number`, `transaction_date`, `warehouse_id`, `type`, `destination`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'SOUT-2026070001', '2026-07-16', 1, 'production_usage', 'Line Produksi A', NULL, 1, '2026-07-06 18:57:03', '2026-07-06 18:57:03'),
(2, 'SOUT-2026070002', '2026-07-07', 1, 'production_usage', 'Test', NULL, 5, '2026-07-06 19:19:51', '2026-07-06 19:19:51');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` bigint UNSIGNED NOT NULL,
  `transfer_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transfer_date` date NOT NULL,
  `from_warehouse_id` bigint UNSIGNED NOT NULL,
  `to_warehouse_id` bigint UNSIGNED NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfers`
--

INSERT INTO `stock_transfers` (`id`, `transfer_number`, `transfer_date`, `from_warehouse_id`, `to_warehouse_id`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'TRF-2026070001', '2026-07-16', 1, 3, 'Check transfer', 2, '2026-07-06 19:06:32', '2026-07-06 19:06:32');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_details`
--

CREATE TABLE `stock_transfer_details` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_transfer_id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `qty` decimal(15,4) NOT NULL,
  `qty_base` decimal(15,4) NOT NULL,
  `cost_at_transfer` decimal(15,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfer_details`
--

INSERT INTO `stock_transfer_details` (`id`, `stock_transfer_id`, `raw_material_id`, `unit_id`, `qty`, `qty_base`, `cost_at_transfer`) VALUES
(1, 1, 3, 1, 30.0000, 30.0000, 50000.0000);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Test Supplier', NULL, NULL, 1, '2026-07-06 19:22:31', '2026-07-06 19:22:31', '2026-07-06 19:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `symbol`, `created_at`, `updated_at`) VALUES
(1, 'Meter', 'm', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(2, 'Roll', 'rol', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(3, 'Pieces', 'pcs', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(4, 'Kilogram', 'kg', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(5, 'Dus', 'dus', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(6, 'Lembar', 'lbr', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(7, 'Yard', 'yd', '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(8, 'Centimeter', 'cm', '2026-07-06 18:34:02', '2026-07-06 18:34:02');

-- --------------------------------------------------------

--
-- Table structure for table `unit_conversions`
--

CREATE TABLE `unit_conversions` (
  `id` bigint UNSIGNED NOT NULL,
  `raw_material_id` bigint UNSIGNED NOT NULL,
  `from_unit_id` bigint UNSIGNED NOT NULL,
  `to_unit_id` bigint UNSIGNED NOT NULL,
  `conversion_factor` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_conversions`
--

INSERT INTO `unit_conversions` (`id`, `raw_material_id`, `from_unit_id`, `to_unit_id`, `conversion_factor`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 25.0000, '2026-07-06 18:55:45', '2026-07-06 18:55:45'),
(2, 2, 2, 1, 25.0000, '2026-07-06 18:57:03', '2026-07-06 18:57:03'),
(3, 9, 2, 1, 25.0000, '2026-07-06 19:22:31', '2026-07-06 19:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Mrs. Karli Kiehn', 'fkoss@example.org', '2026-07-06 18:57:03', '$2y$12$ccRn0IULrtKBHVghhEjIGuXupdkud7ZqBA8pmZ5MuCstNjvQy3vKW', 'XuuP7xYgjkAU1HbngZw4yfyt4IATcR5yKsoFCfMptgWwEVIfzQeHpbjtdzPD', '2026-07-06 18:57:03', '2026-07-06 18:57:03'),
(2, 'Gia Bahringer', 'kasandra.boyle@example.com', '2026-07-06 19:06:31', '$2y$12$32OEaTfGfxgDbrAzvD.Yo.vMOLG2CuCSl9lGUEVps38Pv3vPNyLbe', 'zlHQn0RZJoyukUJVLv3vF4Zo89GNtKds6YWZXnDHKUqaDdhr2ruBToXGwMF9', '2026-07-06 19:06:32', '2026-07-06 19:06:32'),
(3, 'Carrie Lubowitz', 'jaclyn15@example.com', '2026-07-06 19:07:37', '$2y$12$8gCA/dtglZh3bmwX.9kNAOEhCYmXCGBUA2npdx55bvlzBSocmGfA2', 'Wt8fjl9RhJDG1VDKfsSuqeuyPHuxWO4PlkRthgEMpcZ4BR6HabhiCGUS632r', '2026-07-06 19:07:37', '2026-07-06 19:07:37'),
(4, 'Vincenza Mohr', 'djohns@example.com', '2026-07-06 19:17:41', '$2y$12$7PU5XeeKsvi/NvHh9O24KOeYKkGugTH.zA/ys7hXIkHJNB9Nt1sK.', 'aUsjVLU2iv', '2026-07-06 19:17:42', '2026-07-06 19:17:42'),
(5, 'Mr. Alexis Fritsch DDS', 'boyer.cordia@example.net', '2026-07-06 19:19:50', '$2y$12$P55Eb3/OZKUMYZkVLIjLf.2YZbwHoOBW9G5XUQ5GsTF3ajoG4XDXq', '6gzfkSGxLbMxcOMLeSxytP6zg3SxA9GR6M8SdEhtj6MukJQlMF6GLckxCrWd', '2026-07-06 19:19:50', '2026-07-06 19:19:50'),
(6, 'Mr. Austyn Luettgen Sr.', 'mayert.yoshiko@example.com', '2026-07-06 19:21:03', '$2y$12$Znv5.67N.MignFX8iPtdeOlPtEnGe2F7p.Q7zRH/Ee666shFOs0n.', '1JfwwzALNy', '2026-07-06 19:21:04', '2026-07-06 19:21:04'),
(7, 'Super Admin', 'admin@gudang.test', NULL, '$2y$12$ABll5wfqwtiFS3B9bUxc2uDVC8QKFOE560y4UM3cXInAcu820ejyC', NULL, '2026-07-07 05:48:33', '2026-07-07 05:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `locked_by_opname_id` bigint UNSIGNED DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `code`, `location`, `is_active`, `is_locked`, `locked_by_opname_id`, `locked_at`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Gudang Pusat', 'GDG-PST', 'Jl. Industri No. 1, Jakarta', 1, 0, NULL, NULL, NULL, '2026-07-06 18:34:02', '2026-07-06 19:17:42'),
(2, 'Gudang Cabang Blitar', 'GDG-BLR', 'Jl. Raya Blitar No. 25, Blitar', 1, 0, NULL, NULL, NULL, '2026-07-06 18:34:02', '2026-07-06 18:34:02'),
(3, 'Gudang Cabang Bandung', 'GDG-BDG', 'Jl. Cilaki No. 50, Bandung', 1, 0, NULL, NULL, NULL, '2026-07-06 18:34:02', '2026-07-06 18:34:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asset_value_snapshots`
--
ALTER TABLE `asset_value_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_value_snapshots_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `idx_snapshot_date_wh` (`snapshot_date`,`warehouse_id`),
  ADD KEY `idx_snapshot_wh_item_date` (`warehouse_id`,`raw_material_id`,`snapshot_date`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `document_number_counters`
--
ALTER TABLE `document_number_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_document_counter_type_period` (`document_type`,`period`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_categories`
--
ALTER TABLE `material_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_categories_code_unique` (`code`);

--
-- Indexes for table `material_stocks`
--
ALTER TABLE `material_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_material_stocks_item_warehouse` (`raw_material_id`,`warehouse_id`),
  ADD KEY `material_stocks_warehouse_id_foreign` (`warehouse_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `raw_materials_code_unique` (`code`),
  ADD KEY `raw_materials_material_category_id_foreign` (`material_category_id`),
  ADD KEY `raw_materials_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stock_in_details`
--
ALTER TABLE `stock_in_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_in_details_stock_in_transaction_id_foreign` (`stock_in_transaction_id`),
  ADD KEY `stock_in_details_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `stock_in_details_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `stock_in_transactions`
--
ALTER TABLE `stock_in_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_in_transactions_transaction_number_unique` (`transaction_number`),
  ADD KEY `stock_in_transactions_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `stock_in_transactions_supplier_id_foreign` (`supplier_id`),
  ADD KEY `stock_in_transactions_created_by_foreign` (`created_by`);

--
-- Indexes for table `stock_ledgers`
--
ALTER TABLE `stock_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_ledgers_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `idx_ledger_item_warehouse_date` (`raw_material_id`,`warehouse_id`,`transaction_date`),
  ADD KEY `idx_ledger_source` (`source_type`,`source_id`);

--
-- Indexes for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_opnames_opname_number_unique` (`opname_number`),
  ADD KEY `stock_opnames_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `stock_opnames_created_by_foreign` (`created_by`);

--
-- Indexes for table `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_opname_details_stock_opname_id_foreign` (`stock_opname_id`),
  ADD KEY `stock_opname_details_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `stock_opname_details_physical_qty_unit_id_foreign` (`physical_qty_unit_id`);

--
-- Indexes for table `stock_out_details`
--
ALTER TABLE `stock_out_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_out_details_stock_out_transaction_id_foreign` (`stock_out_transaction_id`),
  ADD KEY `stock_out_details_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `stock_out_details_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `stock_out_transactions`
--
ALTER TABLE `stock_out_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_out_transactions_transaction_number_unique` (`transaction_number`),
  ADD KEY `stock_out_transactions_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `stock_out_transactions_created_by_foreign` (`created_by`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_transfers_transfer_number_unique` (`transfer_number`),
  ADD KEY `stock_transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  ADD KEY `stock_transfers_to_warehouse_id_foreign` (`to_warehouse_id`),
  ADD KEY `stock_transfers_created_by_foreign` (`created_by`);

--
-- Indexes for table `stock_transfer_details`
--
ALTER TABLE `stock_transfer_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transfer_details_stock_transfer_id_foreign` (`stock_transfer_id`),
  ADD KEY `stock_transfer_details_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `stock_transfer_details_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unit_conversions`
--
ALTER TABLE `unit_conversions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_unit_conversions` (`raw_material_id`,`from_unit_id`,`to_unit_id`),
  ADD KEY `unit_conversions_from_unit_id_foreign` (`from_unit_id`),
  ADD KEY `unit_conversions_to_unit_id_foreign` (`to_unit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouses_code_unique` (`code`),
  ADD KEY `warehouses_locked_by_opname_id_foreign` (`locked_by_opname_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asset_value_snapshots`
--
ALTER TABLE `asset_value_snapshots`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `document_number_counters`
--
ALTER TABLE `document_number_counters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_categories`
--
ALTER TABLE `material_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `material_stocks`
--
ALTER TABLE `material_stocks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `raw_materials`
--
ALTER TABLE `raw_materials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_in_details`
--
ALTER TABLE `stock_in_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stock_in_transactions`
--
ALTER TABLE `stock_in_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock_ledgers`
--
ALTER TABLE `stock_ledgers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_out_details`
--
ALTER TABLE `stock_out_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_out_transactions`
--
ALTER TABLE `stock_out_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_transfer_details`
--
ALTER TABLE `stock_transfer_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `unit_conversions`
--
ALTER TABLE `unit_conversions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `asset_value_snapshots`
--
ALTER TABLE `asset_value_snapshots`
  ADD CONSTRAINT `asset_value_snapshots_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `asset_value_snapshots_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `material_stocks`
--
ALTER TABLE `material_stocks`
  ADD CONSTRAINT `material_stocks_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `material_stocks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD CONSTRAINT `raw_materials_material_category_id_foreign` FOREIGN KEY (`material_category_id`) REFERENCES `material_categories` (`id`),
  ADD CONSTRAINT `raw_materials_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_in_details`
--
ALTER TABLE `stock_in_details`
  ADD CONSTRAINT `stock_in_details_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `stock_in_details_stock_in_transaction_id_foreign` FOREIGN KEY (`stock_in_transaction_id`) REFERENCES `stock_in_transactions` (`id`),
  ADD CONSTRAINT `stock_in_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `stock_in_transactions`
--
ALTER TABLE `stock_in_transactions`
  ADD CONSTRAINT `stock_in_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_in_transactions_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `stock_in_transactions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_ledgers`
--
ALTER TABLE `stock_ledgers`
  ADD CONSTRAINT `stock_ledgers_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `stock_ledgers_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_opnames`
--
ALTER TABLE `stock_opnames`
  ADD CONSTRAINT `stock_opnames_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_opnames_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_opname_details`
--
ALTER TABLE `stock_opname_details`
  ADD CONSTRAINT `stock_opname_details_physical_qty_unit_id_foreign` FOREIGN KEY (`physical_qty_unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `stock_opname_details_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `stock_opname_details_stock_opname_id_foreign` FOREIGN KEY (`stock_opname_id`) REFERENCES `stock_opnames` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_out_details`
--
ALTER TABLE `stock_out_details`
  ADD CONSTRAINT `stock_out_details_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `stock_out_details_stock_out_transaction_id_foreign` FOREIGN KEY (`stock_out_transaction_id`) REFERENCES `stock_out_transactions` (`id`),
  ADD CONSTRAINT `stock_out_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `stock_out_transactions`
--
ALTER TABLE `stock_out_transactions`
  ADD CONSTRAINT `stock_out_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_out_transactions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stock_transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_transfer_details`
--
ALTER TABLE `stock_transfer_details`
  ADD CONSTRAINT `stock_transfer_details_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `stock_transfer_details_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`),
  ADD CONSTRAINT `stock_transfer_details_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `unit_conversions`
--
ALTER TABLE `unit_conversions`
  ADD CONSTRAINT `unit_conversions_from_unit_id_foreign` FOREIGN KEY (`from_unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `unit_conversions_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  ADD CONSTRAINT `unit_conversions_to_unit_id_foreign` FOREIGN KEY (`to_unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_locked_by_opname_id_foreign` FOREIGN KEY (`locked_by_opname_id`) REFERENCES `stock_opnames` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
