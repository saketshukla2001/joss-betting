-- BetZone full data dump
-- Create database first: CREATE DATABASE betzone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Then import: mysql -u root -p betzone < betzone-dump.sql
-- MySQL dump 10.13  Distrib 9.6.0, for macos26.4 (arm64)
--
-- Host: localhost    Database: betzone
-- ------------------------------------------------------
-- Server version	9.6.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agent_commissions`
--

DROP TABLE IF EXISTS `agent_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agent_commissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `player_id` bigint unsigned NOT NULL,
  `bet_id` bigint unsigned NOT NULL,
  `stake` decimal(14,2) NOT NULL,
  `rate` decimal(6,2) NOT NULL,
  `basis` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'turnover',
  `amount` decimal(14,2) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `payout_reference` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_commissions_agent_id_bet_id_unique` (`agent_id`,`bet_id`),
  KEY `agent_commissions_bet_id_foreign` (`bet_id`),
  KEY `agent_commissions_agent_id_status_index` (`agent_id`,`status`),
  KEY `agent_commissions_player_id_created_at_index` (`player_id`,`created_at`),
  KEY `agent_commissions_status_index` (`status`),
  CONSTRAINT `agent_commissions_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_commissions_bet_id_foreign` FOREIGN KEY (`bet_id`) REFERENCES `bets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_commissions_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_commissions`
--

LOCK TABLES `agent_commissions` WRITE;
/*!40000 ALTER TABLE `agent_commissions` DISABLE KEYS */;
INSERT INTO `agent_commissions` VALUES (1,10,2,12,3000.00,5.00,'turnover',150.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(2,9,2,12,3000.00,3.00,'turnover',90.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(3,8,2,12,3000.00,2.50,'turnover',75.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(4,7,2,12,3000.00,2.50,'turnover',75.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(5,1,2,12,3000.00,2.00,'turnover',60.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(6,10,3,13,2500.00,5.00,'turnover',125.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(7,9,3,13,2500.00,3.00,'turnover',75.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(8,8,3,13,2500.00,2.50,'turnover',62.50,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(9,7,3,13,2500.00,2.50,'turnover',62.50,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(10,1,3,13,2500.00,2.00,'turnover',50.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(11,10,4,14,1000.00,5.00,'turnover',50.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(12,9,4,14,1000.00,3.00,'turnover',30.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(13,8,4,14,1000.00,2.50,'turnover',25.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(14,7,4,14,1000.00,2.50,'turnover',25.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(15,1,4,14,1000.00,2.00,'turnover',20.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(16,11,5,15,500.00,3.00,'net_loss',15.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(17,10,5,15,500.00,5.00,'turnover',25.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(18,9,5,15,500.00,3.00,'turnover',15.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(19,8,5,15,500.00,2.50,'turnover',12.50,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(20,7,5,15,500.00,2.50,'turnover',12.50,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(21,1,5,15,500.00,2.00,'turnover',10.00,'pending',NULL,NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33');
/*!40000 ALTER TABLE `agent_commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bets`
--

DROP TABLE IF EXISTS `bets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bet_code` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `match_id` bigint unsigned NOT NULL,
  `market_id` bigint unsigned NOT NULL,
  `selection_id` bigint unsigned NOT NULL,
  `sport_name` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `league` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_label` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `market_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selection_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `odds` decimal(8,2) NOT NULL,
  `stake` decimal(14,2) NOT NULL,
  `potential_return` decimal(14,2) NOT NULL,
  `status` enum('pending','won','lost','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payout` decimal(14,2) NOT NULL DEFAULT '0.00',
  `settled_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bets_bet_code_unique` (`bet_code`),
  KEY `bets_selection_id_foreign` (`selection_id`),
  KEY `bets_user_id_status_index` (`user_id`,`status`),
  KEY `bets_match_id_status_index` (`match_id`,`status`),
  KEY `bets_market_id_status_index` (`market_id`,`status`),
  KEY `bets_created_at_index` (`created_at`),
  CONSTRAINT `bets_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bets_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bets_selection_id_foreign` FOREIGN KEY (`selection_id`) REFERENCES `selections` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bets`
--

LOCK TABLES `bets` WRITE;
/*!40000 ALTER TABLE `bets` DISABLE KEYS */;
INSERT INTO `bets` VALUES (1,'BZ261114QLABA',2,1,1,1,'Football','Premier League','Arsenal vs Chelsea','Match Winner','Arsenal',2.10,2000.00,4200.00,'pending',0.00,NULL,'127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(2,'BZ261114ZAEIE',2,2,4,8,'Football','La Liga','Real Madrid vs Barcelona','Match Winner','Real Madrid',2.35,1500.00,3525.00,'pending',0.00,NULL,'127.0.0.1','2026-11-12 08:21:32','2026-11-12 08:21:32'),(3,'BZ261114VJNDT',2,7,12,28,'Cricket','ODI Series','India vs Australia','Match Winner','India',1.72,1000.00,1720.00,'pending',0.00,NULL,'127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(4,'BZ261114WOTBZ',2,8,14,32,'Basketball','NBA','Los Angeles Lakers vs Boston Celtics','Match Winner','Los Angeles Lakers',1.75,500.00,875.00,'pending',0.00,NULL,'127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(5,'BZ261114OGTFV',3,1,1,3,'Football','Premier League','Arsenal vs Chelsea','Match Winner','Chelsea',3.25,1000.00,3250.00,'pending',0.00,NULL,'127.0.0.1','2026-11-09 08:21:32','2026-11-09 08:21:32'),(6,'BZ261114XHV1V',3,6,10,25,'Cricket','Indian Premier League','Mumbai Indians vs Chennai Super Kings','Match Winner','Chennai Super Kings',1.90,2000.00,3800.00,'pending',0.00,NULL,'127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(7,'BZ261114AVILZ',3,9,16,36,'Tennis','ATP Finals','Carlos Alcaraz vs Jannik Sinner','Match Winner','Carlos Alcaraz',1.80,750.00,1350.00,'pending',0.00,NULL,'127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(8,'BZ261114VITU5',4,2,4,10,'Football','La Liga','Real Madrid vs Barcelona','Match Winner','Barcelona',2.90,3000.00,8700.00,'pending',0.00,NULL,'127.0.0.1','2026-11-11 08:21:32','2026-11-11 08:21:32'),(9,'BZ2611149MEKA',4,3,6,13,'Football','Serie A','Inter Milan vs Juventus','Match Winner','Inter Milan',1.95,1200.00,2340.00,'pending',0.00,NULL,'127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(10,'BZ261114LDO4N',4,9,16,37,'Tennis','ATP Finals','Carlos Alcaraz vs Jannik Sinner','Match Winner','Jannik Sinner',2.00,800.00,1600.00,'pending',0.00,NULL,'127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(11,'BZ261114C4WLW',5,10,17,38,'Hockey','NHL','Toronto Maple Leafs vs Montreal Canadiens','Match Winner','Toronto Maple Leafs',1.85,500.00,925.00,'pending',0.00,NULL,'127.0.0.1','2026-11-13 08:21:32','2026-11-13 08:21:32'),(12,'BZ261114BC9D2',2,4,8,18,'Football','FA Cup','Liverpool vs Manchester City','Match Winner','Liverpool',2.20,3000.00,6600.00,'won',6600.00,'2026-11-14 08:21:32','127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(13,'BZ2611146HJUS',3,4,8,20,'Football','FA Cup','Liverpool vs Manchester City','Match Winner','Manchester City',3.10,2500.00,7750.00,'lost',0.00,'2026-11-14 08:21:32','127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(14,'BZ261114XSURX',4,4,8,18,'Football','FA Cup','Liverpool vs Manchester City','Match Winner','Liverpool',2.20,1000.00,2200.00,'won',2200.00,'2026-11-14 08:21:32','127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32'),(15,'BZ2611143MZHC',5,4,8,20,'Football','FA Cup','Liverpool vs Manchester City','Match Winner','Manchester City',3.10,500.00,1550.00,'lost',0.00,'2026-11-14 08:21:32','127.0.0.1','2026-11-14 08:21:32','2026-11-14 08:21:32');
/*!40000 ALTER TABLE `bets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('betzone-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1795000548),('betzone-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1795000548;',1795000548);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposit_requests`
--

DROP TABLE IF EXISTS `deposit_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deposit_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('upi','bank','usdt') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upi',
  `transaction_ref` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deposit_requests_user_id_foreign` (`user_id`),
  KEY `deposit_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `deposit_requests_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `deposit_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deposit_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposit_requests`
--

LOCK TABLES `deposit_requests` WRITE;
/*!40000 ALTER TABLE `deposit_requests` DISABLE KEYS */;
INSERT INTO `deposit_requests` VALUES (1,2,10000.00,'upi','UPI-8899221100','approved','Verified against UPI reference.',1,'2026-11-14 08:21:32','2026-11-14 08:21:32','2026-11-14 08:21:32'),(2,3,5000.00,'bank','NEFT-77120045','approved','Bank transfer received.',1,'2026-11-14 08:21:32','2026-11-14 08:21:32','2026-11-14 08:21:32'),(3,2,5000.00,'upi','UPI-5566112233','pending',NULL,NULL,NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(4,4,2000.00,'usdt','TX-0x9f2a','rejected','Transaction hash could not be verified.',1,'2026-11-14 08:21:32','2026-11-14 08:21:32','2026-11-14 08:21:32'),(5,5,7500.00,'bank','IMPS-44556677','pending',NULL,NULL,NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32');
/*!40000 ALTER TABLE `deposit_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_logs`
--

DROP TABLE IF EXISTS `login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_logs_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `login_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_logs`
--

LOCK TABLES `login_logs` WRITE;
/*!40000 ALTER TABLE `login_logs` DISABLE KEYS */;
INSERT INTO `login_logs` VALUES (1,1,'103.21.20.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-12 02:40:00','2026-11-12 02:40:00'),(2,1,'103.21.20.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-11 03:40:00','2026-11-11 03:40:00'),(3,1,'103.21.20.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-10 04:40:00','2026-11-10 04:40:00'),(4,2,'103.21.21.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-10 02:41:00','2026-11-10 02:41:00'),(5,2,'103.21.21.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-09 03:41:00','2026-11-09 03:41:00'),(6,2,'103.21.21.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-08 04:41:00','2026-11-08 04:41:00'),(7,3,'103.21.22.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-08 02:42:00','2026-11-08 02:42:00'),(8,3,'103.21.22.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-07 03:42:00','2026-11-07 03:42:00'),(9,3,'103.21.22.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-06 04:42:00','2026-11-06 04:42:00'),(10,4,'103.21.23.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-06 02:43:00','2026-11-06 02:43:00'),(11,4,'103.21.23.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-05 03:43:00','2026-11-05 03:43:00'),(12,4,'103.21.23.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-04 04:43:00','2026-11-04 04:43:00'),(13,5,'103.21.24.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-04 02:44:00','2026-11-04 02:44:00'),(14,5,'103.21.24.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-03 03:44:00','2026-11-03 03:44:00'),(15,5,'103.21.24.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-02 04:44:00','2026-11-02 04:44:00'),(16,6,'103.21.25.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-02 02:45:00','2026-11-02 02:45:00'),(17,6,'103.21.25.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-11-01 03:45:00','2026-11-01 03:45:00'),(18,6,'103.21.25.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-31 04:45:00','2026-10-31 04:45:00'),(19,7,'103.21.26.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-31 02:46:00','2026-10-31 02:46:00'),(20,7,'103.21.26.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-30 03:46:00','2026-10-30 03:46:00'),(21,7,'103.21.26.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-29 04:46:00','2026-10-29 04:46:00'),(22,8,'103.21.27.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-29 02:47:00','2026-10-29 02:47:00'),(23,8,'103.21.27.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-28 03:47:00','2026-10-28 03:47:00'),(24,8,'103.21.27.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-27 04:47:00','2026-10-27 04:47:00'),(25,9,'103.21.28.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-27 02:48:00','2026-10-27 02:48:00'),(26,9,'103.21.28.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-26 03:48:00','2026-10-26 03:48:00'),(27,9,'103.21.28.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-25 04:48:00','2026-10-25 04:48:00'),(28,10,'103.21.29.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-25 02:49:00','2026-10-25 02:49:00'),(29,10,'103.21.29.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-24 03:49:00','2026-10-24 03:49:00'),(30,10,'103.21.29.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-23 04:49:00','2026-10-23 04:49:00'),(31,11,'103.21.30.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-23 02:50:00','2026-10-23 02:50:00'),(32,11,'103.21.30.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-22 03:50:00','2026-10-22 03:50:00'),(33,11,'103.21.30.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-21 04:50:00','2026-10-21 04:50:00'),(34,12,'103.21.31.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-21 02:51:00','2026-10-21 02:51:00'),(35,12,'103.21.31.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-20 03:51:00','2026-10-20 03:51:00'),(36,12,'103.21.31.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-19 04:51:00','2026-10-19 04:51:00'),(37,13,'103.21.32.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-19 02:52:00','2026-10-19 02:52:00'),(38,13,'103.21.32.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-18 03:52:00','2026-10-18 03:52:00'),(39,13,'103.21.32.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-17 04:52:00','2026-10-17 04:52:00'),(40,14,'103.21.33.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-17 02:53:00','2026-10-17 02:53:00'),(41,14,'103.21.33.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-16 03:53:00','2026-10-16 03:53:00'),(42,14,'103.21.33.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-15 04:53:00','2026-10-15 04:53:00'),(43,15,'103.21.34.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-15 02:54:00','2026-10-15 02:54:00'),(44,15,'103.21.34.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-14 03:54:00','2026-10-14 03:54:00'),(45,15,'103.21.34.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-13 04:54:00','2026-10-13 04:54:00'),(46,16,'103.21.35.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-13 02:55:00','2026-10-13 02:55:00'),(47,16,'103.21.35.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-12 03:55:00','2026-10-12 03:55:00'),(48,16,'103.21.35.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-11 04:55:00','2026-10-11 04:55:00'),(49,17,'103.21.36.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-11 02:56:00','2026-10-11 02:56:00'),(50,17,'103.21.36.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-10 03:56:00','2026-10-10 03:56:00'),(51,17,'103.21.36.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-09 04:56:00','2026-10-09 04:56:00'),(52,18,'103.21.37.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-09 02:57:00','2026-10-09 02:57:00'),(53,18,'103.21.37.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-08 03:57:00','2026-10-08 03:57:00'),(54,18,'103.21.37.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-07 04:57:00','2026-10-07 04:57:00'),(55,19,'103.21.38.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-07 02:58:00','2026-10-07 02:58:00'),(56,19,'103.21.38.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-06 03:58:00','2026-10-06 03:58:00'),(57,19,'103.21.38.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-05 04:58:00','2026-10-05 04:58:00'),(58,20,'103.21.39.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-05 02:59:00','2026-10-05 02:59:00'),(59,20,'103.21.39.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-04 03:59:00','2026-10-04 03:59:00'),(60,20,'103.21.39.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-03 04:59:00','2026-10-03 04:59:00'),(61,21,'103.21.40.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-03 03:00:00','2026-10-03 03:00:00'),(62,21,'103.21.40.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-02 04:00:00','2026-10-02 04:00:00'),(63,21,'103.21.40.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-01 05:00:00','2026-10-01 05:00:00'),(64,22,'103.21.41.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-10-01 03:01:00','2026-10-01 03:01:00'),(65,22,'103.21.41.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-30 04:01:00','2026-09-30 04:01:00'),(66,22,'103.21.41.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-29 05:01:00','2026-09-29 05:01:00'),(67,23,'103.21.42.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-29 03:02:00','2026-09-29 03:02:00'),(68,23,'103.21.42.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-28 04:02:00','2026-09-28 04:02:00'),(69,23,'103.21.42.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-27 05:02:00','2026-09-27 05:02:00'),(70,24,'103.21.43.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-27 03:03:00','2026-09-27 03:03:00'),(71,24,'103.21.43.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-26 04:03:00','2026-09-26 04:03:00'),(72,24,'103.21.43.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-25 05:03:00','2026-09-25 05:03:00'),(73,25,'103.21.44.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-25 03:04:00','2026-09-25 03:04:00'),(74,25,'103.21.44.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-24 04:04:00','2026-09-24 04:04:00'),(75,25,'103.21.44.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-23 05:04:00','2026-09-23 05:04:00'),(76,26,'103.21.45.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-23 03:05:00','2026-09-23 03:05:00'),(77,26,'103.21.45.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-22 04:05:00','2026-09-22 04:05:00'),(78,26,'103.21.45.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-21 05:05:00','2026-09-21 05:05:00'),(79,27,'103.21.46.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-21 03:06:00','2026-09-21 03:06:00'),(80,27,'103.21.46.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-20 04:06:00','2026-09-20 04:06:00'),(81,27,'103.21.46.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-19 05:06:00','2026-09-19 05:06:00'),(82,28,'103.21.47.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-19 03:07:00','2026-09-19 03:07:00'),(83,28,'103.21.47.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-18 04:07:00','2026-09-18 04:07:00'),(84,28,'103.21.47.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-17 05:07:00','2026-09-17 05:07:00'),(85,29,'103.21.48.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-17 03:08:00','2026-09-17 03:08:00'),(86,29,'103.21.48.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-16 04:08:00','2026-09-16 04:08:00'),(87,29,'103.21.48.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-15 05:08:00','2026-09-15 05:08:00'),(88,30,'103.21.49.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-15 03:09:00','2026-09-15 03:09:00'),(89,30,'103.21.49.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-14 04:09:00','2026-09-14 04:09:00'),(90,30,'103.21.49.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-13 05:09:00','2026-09-13 05:09:00'),(91,31,'103.21.50.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-13 03:10:00','2026-09-13 03:10:00'),(92,31,'103.21.50.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-12 04:10:00','2026-09-12 04:10:00'),(93,31,'103.21.50.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-11 05:10:00','2026-09-11 05:10:00'),(94,32,'103.21.51.10','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-11 03:11:00','2026-09-11 03:11:00'),(95,32,'103.21.51.11','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-10 04:11:00','2026-09-10 04:11:00'),(96,32,'103.21.51.12','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/131.0 Safari/537.36','2026-09-09 05:11:00','2026-09-09 05:11:00'),(97,1,'127.0.0.1','curl/8.7.1','2026-11-14 08:21:43','2026-11-14 08:21:43'),(98,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-14 08:23:38','2026-11-14 08:23:38'),(99,1,'127.0.0.1','curl/8.7.1','2026-11-14 08:32:41','2026-11-14 08:32:41'),(100,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:33:30','2026-11-14 08:33:30'),(101,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:34:04','2026-11-14 08:34:04'),(102,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:34:39','2026-11-14 08:34:39'),(103,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:35:31','2026-11-14 08:35:31'),(104,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:36:00','2026-11-14 08:36:00'),(105,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:36:33','2026-11-14 08:36:33'),(106,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:37:08','2026-11-14 08:37:08'),(107,1,'127.0.0.1','curl/8.7.1','2026-11-14 08:54:04','2026-11-14 08:54:04'),(108,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:54:49','2026-11-14 08:54:49'),(109,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:55:00','2026-11-14 08:55:00'),(110,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:55:17','2026-11-14 08:55:17'),(111,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:56:04','2026-11-14 08:56:04'),(112,1,'127.0.0.1','curl/8.7.1','2026-11-14 08:59:40','2026-11-14 08:59:40'),(113,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 08:59:59','2026-11-14 08:59:59'),(114,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 09:01:18','2026-11-14 09:01:18'),(115,1,'127.0.0.1','curl/8.7.1','2026-11-14 09:03:04','2026-11-14 09:03:04'),(116,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 09:03:30','2026-11-14 09:03:30'),(117,1,'127.0.0.1','curl/8.7.1','2026-11-14 09:09:42','2026-11-14 09:09:42'),(118,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 09:10:23','2026-11-14 09:10:23'),(119,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 09:11:06','2026-11-14 09:11:06'),(120,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-14 09:11:36','2026-11-14 09:11:36'),(121,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-17 08:43:40','2026-11-17 08:43:40'),(122,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-17 08:45:50','2026-11-17 08:45:50'),(123,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-17 10:45:02','2026-11-17 10:45:02'),(124,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-17 10:46:24','2026-11-17 10:46:24'),(125,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-18 10:01:40','2026-11-18 10:01:40'),(126,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:06:41','2026-11-18 10:06:41'),(127,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:07:46','2026-11-18 10:07:46'),(128,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:08:53','2026-11-18 10:08:53'),(129,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:08:55','2026-11-18 10:08:55'),(130,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:09:22','2026-11-18 10:09:22'),(131,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:09:24','2026-11-18 10:09:24'),(132,10,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-11-18 10:15:17','2026-11-18 10:15:17'),(133,10,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:21:56','2026-11-18 10:21:56'),(134,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:23:06','2026-11-18 10:23:06'),(135,1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 10:23:35','2026-11-18 10:23:35'),(136,10,'127.0.0.1','curl/8.7.1','2026-11-18 10:23:55','2026-11-18 10:23:55'),(137,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 11:07:58','2026-11-18 11:07:58'),(138,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 11:11:20','2026-11-18 11:11:20'),(139,7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','2026-11-18 11:14:48','2026-11-18 11:14:48');
/*!40000 ALTER TABLE `login_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `markets`
--

DROP TABLE IF EXISTS `markets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `markets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auto_settle` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('open','suspended','settled','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `markets_match_id_status_index` (`match_id`,`status`),
  CONSTRAINT `markets_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `markets`
--

LOCK TABLES `markets` WRITE;
/*!40000 ALTER TABLE `markets` DISABLE KEYS */;
INSERT INTO `markets` VALUES (1,1,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(2,1,'Total Goals — Over/Under 2.5',0,'open',2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(3,1,'Both Teams to Score',0,'open',3,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(4,2,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(5,2,'Total Goals — Over/Under 3.5',0,'open',2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(6,3,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(7,3,'Total Goals — Over/Under 2.5',0,'open',2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(8,4,'Match Winner',1,'settled',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(9,5,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(10,6,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(11,6,'Total Runs — Over/Under 340.5',0,'open',2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(12,7,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(13,7,'Total Runs — Over/Under 340.5',0,'open',2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(14,8,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(15,8,'Total Points — Over/Under 220.5',0,'open',2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(16,9,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(17,10,'Match Winner',1,'open',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(18,11,'Match Winner',1,'void',1,'2026-11-14 08:21:32','2026-11-14 08:21:32');
/*!40000 ALTER TABLE `markets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_limits`
--

DROP TABLE IF EXISTS `match_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_limits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_limits_match_id_user_id_unique` (`match_id`,`user_id`),
  KEY `match_limits_user_id_foreign` (`user_id`),
  CONSTRAINT `match_limits_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_limits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_limits`
--

LOCK TABLES `match_limits` WRITE;
/*!40000 ALTER TABLE `match_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `match_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `matches`
--

DROP TABLE IF EXISTS `matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `matches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sport_id` bigint unsigned NOT NULL,
  `league` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `home_team` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `away_team` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `status` enum('upcoming','live','settled','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upcoming',
  `home_score` tinyint unsigned DEFAULT NULL,
  `away_score` tinyint unsigned DEFAULT NULL,
  `result` enum('home','away','draw') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `matches_sport_id_foreign` (`sport_id`),
  KEY `matches_status_start_time_index` (`status`,`start_time`),
  KEY `matches_featured_index` (`featured`),
  CONSTRAINT `matches_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matches`
--

LOCK TABLES `matches` WRITE;
/*!40000 ALTER TABLE `matches` DISABLE KEYS */;
INSERT INTO `matches` VALUES (1,1,'Premier League','Arsenal','Chelsea','2026-11-14 16:51:32','upcoming',NULL,NULL,NULL,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(2,1,'La Liga','Real Madrid','Barcelona','2026-11-15 21:00:00','upcoming',NULL,NULL,NULL,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(3,1,'Serie A','Inter Milan','Juventus','2026-11-14 13:13:32','live',1,1,NULL,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(4,1,'FA Cup','Liverpool','Manchester City','2026-11-12 13:51:32','settled',NULL,NULL,'home',0,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(5,1,'Bundesliga','Bayern Munich','Borussia Dortmund','2026-11-16 20:30:00','upcoming',NULL,NULL,NULL,0,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(6,2,'Indian Premier League','Mumbai Indians','Chennai Super Kings','2026-11-14 18:51:32','upcoming',NULL,NULL,NULL,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(7,2,'ODI Series','India','Australia','2026-11-14 12:51:32','live',156,142,NULL,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(8,3,'NBA','Los Angeles Lakers','Boston Celtics','2026-11-14 21:51:32','upcoming',NULL,NULL,NULL,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(9,4,'ATP Finals','Carlos Alcaraz','Jannik Sinner','2026-11-15 19:00:00','upcoming',NULL,NULL,NULL,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(10,5,'NHL','Toronto Maple Leafs','Montreal Canadiens','2026-11-14 19:51:32','upcoming',NULL,NULL,NULL,0,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(11,1,'EFL Cup','Newcastle United','Brighton','2026-11-15 01:51:32','cancelled',NULL,NULL,NULL,0,'2026-11-14 08:21:32','2026-11-14 08:21:32');
/*!40000 ALTER TABLE `matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_01_000001_add_betting_fields_to_users_table',1),(5,'2026_01_01_000002_create_sports_table',1),(6,'2026_01_01_000003_create_matches_table',1),(7,'2026_01_01_000004_create_markets_table',1),(8,'2026_01_01_000005_create_selections_table',1),(9,'2026_01_01_000006_create_bets_table',1),(10,'2026_01_01_000007_create_wallet_transactions_table',1),(11,'2026_01_01_000008_create_deposit_requests_table',1),(12,'2026_01_01_000009_create_withdrawal_requests_table',1),(13,'2026_01_01_000010_create_settings_table',1),(14,'2026_01_01_000011_add_agent_fields_to_users_table',1),(15,'2026_01_01_000012_create_agent_commissions_table',1),(16,'2026_01_01_000013_add_hierarchy_profile_fields_to_users_table',1),(17,'2026_01_01_000014_add_reference_and_extra_commission_fields_to_users_table',1),(18,'2026_01_01_000015_add_is_demo_to_wallet_transactions_table',1),(19,'2026_01_01_000016_create_operation_logs_table',1),(20,'2026_01_01_000017_create_login_logs_table',1),(21,'2026_01_01_000018_expand_user_status_values',1),(22,'2026_01_01_000019_create_match_limits_table',2),(23,'2026_01_01_000020_add_panel_permissions_to_users_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation_logs`
--

DROP TABLE IF EXISTS `operation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `operation` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_demo` tinyint(1) NOT NULL DEFAULT '0',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operation_logs_user_id_foreign` (`user_id`),
  KEY `operation_logs_created_at_index` (`created_at`),
  CONSTRAINT `operation_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation_logs`
--

LOCK TABLES `operation_logs` WRITE;
/*!40000 ALTER TABLE `operation_logs` DISABLE KEYS */;
INSERT INTO `operation_logs` VALUES (1,1,'CASINO STATUS UPDATED','LAST CASINO STATUS DISABLE, NEW CASINO STATUS ACTIVE BY [JMD OR,SU88623]',1,'{\"demo\": true}','2026-07-02 10:45:04','2026-07-02 10:45:04'),(2,1,'BETTING STATUS UPDATED','LAST BETTING STATUS DISABLE, NEW BETTING STATUS ACTIVE BY [JMD OR,SU88623]',1,'{\"demo\": true}','2026-07-02 10:45:03','2026-07-02 10:45:03'),(3,1,'CASINO STATUS UPDATED','LAST CASINO STATUS ACTIVE, NEW CASINO STATUS DISABLE BY [JMD OR,SU88623]',1,'{\"demo\": true}','2026-07-02 10:45:02','2026-07-02 10:45:02'),(4,1,'BETTING STATUS UPDATED','LAST BETTING STATUS ACTIVE, NEW BETTING STATUS DISABLE BY [JMD OR,SU88623]',1,'{\"demo\": true}','2026-07-02 10:45:01','2026-07-02 10:45:01'),(5,1,'PASSWORD HAS BEEN CHANGED','LAST PASSWORD ******, NEW PASSWORD IS ****** BY [JMD OR,SU88623]',1,'{\"demo\": true}','2026-06-25 21:42:00','2026-06-25 21:42:00');
/*!40000 ALTER TABLE `operation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `selections`
--

DROP TABLE IF EXISTS `selections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `selections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result_key` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odds` decimal(8,2) NOT NULL DEFAULT '2.00',
  `status` enum('active','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `result` enum('win','lose','void') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `selections_market_id_status_index` (`market_id`,`status`),
  CONSTRAINT `selections_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `markets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `selections`
--

LOCK TABLES `selections` WRITE;
/*!40000 ALTER TABLE `selections` DISABLE KEYS */;
INSERT INTO `selections` VALUES (1,1,'Arsenal','home',6.78,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(2,1,'Draw','draw',2.12,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(3,1,'Chelsea','away',3.90,'active',NULL,3,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(4,2,'Over 2.5','over',1.94,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:24'),(5,2,'Under 2.5','under',1.47,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:39'),(6,3,'Yes','yes',2.05,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:13:54'),(7,3,'No','no',2.77,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:09'),(8,4,'Real Madrid','home',2.54,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(9,4,'Draw','draw',7.24,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:39'),(10,4,'Barcelona','away',5.78,'active',NULL,3,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(11,5,'Over 3.5','over',4.78,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(12,5,'Under 3.5','under',3.03,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:24'),(13,6,'Inter Milan','home',2.47,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:13:39'),(14,6,'Draw','draw',4.22,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(15,6,'Juventus','away',5.37,'active',NULL,3,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(16,7,'Over 2.5','over',4.33,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(17,7,'Under 2.5','under',8.43,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(18,8,'Liverpool','home',2.20,'active','win',1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(19,8,'Draw','draw',3.30,'active','lose',2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(20,8,'Manchester City','away',3.10,'active','lose',3,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(21,9,'Bayern Munich','home',2.85,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(22,9,'Draw','draw',5.74,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:09'),(23,9,'Borussia Dortmund','away',5.67,'active',NULL,3,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(24,10,'Mumbai Indians','home',1.69,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:24'),(25,10,'Chennai Super Kings','away',4.19,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(26,11,'Over 340.5','over',2.79,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(27,11,'Under 340.5','under',5.06,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:39'),(28,12,'India','home',5.65,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:39'),(29,12,'Australia','away',2.38,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(30,13,'Over 340.5','over',2.13,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(31,13,'Under 340.5','under',4.20,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:39'),(32,14,'Los Angeles Lakers','home',3.16,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:54'),(33,14,'Boston Celtics','away',2.37,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(34,15,'Over 220.5','over',6.57,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(35,15,'Under 220.5','under',2.87,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:14:39'),(36,16,'Carlos Alcaraz','home',3.54,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(37,16,'Jannik Sinner','away',1.55,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:15:09'),(38,17,'Toronto Maple Leafs','home',1.51,'active',NULL,1,'2026-11-14 08:21:32','2026-11-18 11:14:39'),(39,17,'Montreal Canadiens','away',1.96,'active',NULL,2,'2026-11-14 08:21:32','2026-11-18 11:15:09');
/*!40000 ALTER TABLE `selections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('3DfQYjwlHBKjalKCFjHy7PT2XcTWa15JTMhcw82a',7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZzB3dlZ2QXFaOFVLdjk4M1VnR2RhdGhqYWl6MEF3MzFQR3FwQlZzWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9oaWVyYXJjaHkvY3JlYXRlIjtzOjU6InJvdXRlIjtzOjIyOiJhZ2VudC5oaWVyYXJjaHkuY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiYTUyMTMxYzkyZWM5MGY0NTM3N2NiNTg0YzMyZWJiZmUwOTlkMTBkZjZmNGVlODJmY2Q4NWFiNWYxYWJmM2M5YyI7fQ==',1795000078),('5QLGgsckMj92w5dONuMh4j21HKuJBBufEIbTqTX4',7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTzlJR3pYR00yTGkwcjBQcTFXU00xSGlxYU1OcUdkZWQxY1FBSzFoNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9oaWVyYXJjaHkvOC9lZGl0IjtzOjU6InJvdXRlIjtzOjIwOiJhZ2VudC5oaWVyYXJjaHkuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjc7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImE1MjEzMWM5MmVjOTBmNDUzNzdjYjU4NGMzMmViYmZlMDk5ZDEwZGY2ZjRlZTgyZmNkODVhYjVmMWFiZjNjOWMiO30=',1795000281),('ALPgY4CB2jx6tMnKDtSYAjV8juqiOKJOgqqtuOrT',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVlNaN0lRM3FSbXAyYTc2Z2JPTmhDVmxKMWZDVXVta2RqUkN6TlE5NyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZG1pbiI7czo1OiJyb3V0ZSI7czoxNToiYWRtaW4uZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZjNhMTRjYjUyNTdiODI3YzY3YWIxOTg3NThiMmU3N2YxYmY2NDFiYzU0NjIzNGM5Y2U3ZGZhZmM0YjdlNzUxOSI7fQ==',1794996432),('cPuZY2tskKzk6H6AyabJU1g8YU6XTnodIE8oNQax',NULL,'127.0.0.1','curl/8.7.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS004UWlDbEtCVDdPMUVwZjEwU0dQQUxrVXVMMnJ6WGw3RnhjVE9oMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794996618),('dwStzRa2DH84pZLR0LJzzcdDomiyngMb0v9Kn1Pl',NULL,'127.0.0.1','curl/8.7.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMldPNmhuSlloWHlwOW9jRWF4VDRhWWFVcVE0dUY5NHdyVWxxZE1VdyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795000130),('IxIm56e7h2Ilw9NbdGjnGwMq4HVMxSKAhRyTZu7h',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSEVyZzBXM0p4U0c5OXFUMXFRdXhmdW9udlVabFBVOUw1U2dpaXJSSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZG1pbi9hY2Nlc3MvMTYiO3M6NToicm91dGUiO3M6MTc6ImFkbWluLmFjY2Vzcy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZjNhMTRjYjUyNTdiODI3YzY3YWIxOTg3NThiMmU3N2YxYmY2NDFiYzU0NjIzNGM5Y2U3ZGZhZmM0YjdlNzUxOSI7fQ==',1794996534),('jGByi4gf8dqOWgd5AOjiamO6fAlPgAxcpqnZ8YU0',7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiblNkUUR3SHNnbmI4YzRwT2JWV0dzeDNFamNRV0c5S1BOWElFNnNlaiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9oaWVyYXJjaHkiO3M6NToicm91dGUiO3M6MjE6ImFnZW50LmhpZXJhcmNoeS5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjc7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImE1MjEzMWM5MmVjOTBmNDUzNzdjYjU4NGMzMmViYmZlMDk5ZDEwZGY2ZjRlZTgyZmNkODVhYjVmMWFiZjNjOWMiO30=',1794996535),('ma9XdLJgHqFscGh7S2jWftON4yRKdOYkWoYzf0Sa',NULL,'127.0.0.1','curl/8.7.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoieTFkOGJlRXhOU0FzNHZ5VzhLNHJJa0VzaEVCVmdmc0htMEY0T3drUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795000331),('nI0q0vuj827GDOyGiH2bsrSWefegg0tsJBbvo8mX',7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoidlV3SzZiek9Bd2wxNmpVTzlhMnVxQU9MaDZpeG80MlBleVdzUThwVyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9oaWVyYXJjaHkvY3JlYXRlIjtzOjU6InJvdXRlIjtzOjIyOiJhZ2VudC5oaWVyYXJjaHkuY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiYTUyMTMxYzkyZWM5MGY0NTM3N2NiNTg0YzMyZWJiZmUwOTlkMTBkZjZmNGVlODJmY2Q4NWFiNWYxYWJmM2M5YyI7fQ==',1794997387),('NOwfUQc97BkepWk9lAhGSiAu5K1UAqb7RLYnQVQY',NULL,'127.0.0.1','curl/8.7.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU1dtOGJiVDhFOUgwSDFXM1F2M2x2dVE1cUYyZmcyUlI0UDlnVFRoSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794997458),('QMckQy249GTcaK3NWVhMDJj4iF10a6LiBSL3534l',10,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNW9vTk9TZjBabkJzR1NERmIySTdBT1pjT0Z0WTU4aUVXQUZBTmxpWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9tYXRjaGVzLzciO3M6NToicm91dGUiO3M6MTg6ImFnZW50Lm1hdGNoZXMuc2hvdyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEwO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiI0OGVjMGNjYjQ5MmRkYThiYWIzYWFiMzQwMjQ3ZjNiZTQwODAwMWVhMGNjMjliYzc5ZGJjMmViODFkY2MzMjA3Ijt9',1794997317),('qoW3YYubxOA3s9ybtJsGOehZAWxXnG4UcQNyAja1',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiS0NjZUhscm9JSTg3ckt2WGRSYlVuanRtZ0FEamJoZXBQWDNHVGxQMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZG1pbiI7czo1OiJyb3V0ZSI7czoxNToiYWRtaW4uZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZjNhMTRjYjUyNTdiODI3YzY3YWIxOTg3NThiMmU3N2YxYmY2NDFiYzU0NjIzNGM5Y2U3ZGZhZmM0YjdlNzUxOSI7fQ==',1794996497),('s1FOQaUJvKnDlKDJJ4gBWKHi827RxF6nb7MbFlul',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQW51bldYNWg2anJJQWVLcGVKSGtwZXpaR0JHckoxcUVGTzFycEk5MiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZG1pbi9hY2Nlc3MvMTYiO3M6NToicm91dGUiO3M6MTc6ImFkbWluLmFjY2Vzcy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZjNhMTRjYjUyNTdiODI3YzY3YWIxOTg3NThiMmU3N2YxYmY2NDFiYzU0NjIzNGM5Y2U3ZGZhZmM0YjdlNzUxOSI7fQ==',1794996563),('SQh4jqkgv44y574sovKPSBcNcKECqslyiHHvAr3L',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUTM2TkE0RW1TWUJ4S0tKRjZjYjEyWmk5d1htamNwY0oyNno5YXdHWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZG1pbi9hY2Nlc3MvMTYiO3M6NToicm91dGUiO3M6MTc6ImFkbWluLmFjY2Vzcy5lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZjNhMTRjYjUyNTdiODI3YzY3YWIxOTg3NThiMmU3N2YxYmY2NDFiYzU0NjIzNGM5Y2U3ZGZhZmM0YjdlNzUxOSI7fQ==',1794997415),('TalUTpyaXjzPWEhqFbzdidbJU9haLQq5GXNso4J0',7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiak9idW44aFV3aVNwUGVGaURiSk9Ca2FrRExhdVZwOVk2VW9sREgzcyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9oaWVyYXJjaHkiO3M6NToicm91dGUiO3M6MjE6ImFnZW50LmhpZXJhcmNoeS5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjc7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImE1MjEzMWM5MmVjOTBmNDUzNzdjYjU4NGMzMmViYmZlMDk5ZDEwZGY2ZjRlZTgyZmNkODVhYjVmMWFiZjNjOWMiO30=',1794996564),('un7LIdLX9quKNQTsuu3Q2q5DrQuWRv8m89P7AHS6',10,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiT1AzNDVSY0k2dDVncGxVemlnbERRckM3clFmZ1FsbHFuOTBYZ1pnUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9oaWVyYXJjaHkvY3JlYXRlIjtzOjU6InJvdXRlIjtzOjIyOiJhZ2VudC5oaWVyYXJjaHkuY3JlYXRlIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjQ4ZWMwY2NiNDkyZGRhOGJhYjNhYWIzNDAyNDdmM2JlNDA4MDAxZWEwY2MyOWJjNzlkYmMyZWI4MWRjYzMyMDciO30=',1795000340),('YqqPTydR43SOElheja1Y8eQ6kWo0QUagKJ61IG1d',7,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/157.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiV3hNcjVTZnAzT05EcHRVMkw0YzhURWdkUEpiZkxpekk3b2F0NFNUZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9oaWVyYXJjaHkvY3JlYXRlIjtzOjU6InJvdXRlIjtzOjIyOiJhZ2VudC5oaWVyYXJjaHkuY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiYTUyMTMxYzkyZWM5MGY0NTM3N2NiNTg0YzMyZWJiZmUwOTlkMTBkZjZmNGVlODJmY2Q4NWFiNWYxYWJmM2M5YyI7fQ==',1795000488),('Z4UnB06WFb0QYUh0mX0Ga106WFA0RYX20DtEXoJg',10,'127.0.0.1','curl/8.7.1','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZXNqOTExc0Z6MURVMEx0Q2I1OE5oQXJ6Z0ZyRTNYUFZCUGM1akdUNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODA5MC9hZ2VudC9hY2NvdW50IjtzOjU6InJvdXRlIjtzOjE5OiJhZ2VudC5hY2NvdW50LmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTA7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjQ4ZWMwY2NiNDkyZGRhOGJhYjNhYWIzNDAyNDdmM2JlNDA4MDAxZWEwY2MyOWJjNzlkYmMyZWI4MWRjYzMyMDciO30=',1794997436);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','BetZone','2026-11-14 08:21:31','2026-11-14 08:21:31'),(2,'tagline','Live sports. Real odds. Instant payouts.','2026-11-14 08:21:31','2026-11-14 08:21:31'),(3,'currency','₹','2026-11-14 08:21:31','2026-11-14 08:21:31'),(4,'min_stake','100','2026-11-14 08:21:31','2026-11-14 08:21:31'),(5,'max_stake','100000','2026-11-14 08:21:31','2026-11-14 08:21:31'),(6,'signup_bonus','500','2026-11-14 08:21:31','2026-11-14 08:21:31'),(7,'support_email','support@betzone.test','2026-11-14 08:21:31','2026-11-14 08:21:31'),(8,'announcement','🏆 Featured markets are live — back your team and win big this week.','2026-11-14 08:21:31','2026-11-14 08:21:31'),(9,'odds_auto_move','1','2026-11-14 08:21:31','2026-11-14 08:21:31'),(10,'min_deposit','500','2026-11-14 08:21:31','2026-11-14 08:21:31'),(11,'min_withdrawal','100','2026-11-14 08:21:31','2026-11-14 08:21:31'),(12,'maintenance_message','','2026-11-14 08:21:31','2026-11-14 08:21:31'),(13,'rules','Bet responsibly. 18+ only. All bets are subject to market settlement and platform rules.','2026-11-14 08:21:31','2026-11-14 08:21:31');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sports`
--

DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 0xF09F8F86,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sports_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sports`
--

LOCK TABLES `sports` WRITE;
/*!40000 ALTER TABLE `sports` DISABLE KEYS */;
INSERT INTO `sports` VALUES (1,'Football','football','⚽',1,1,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(2,'Cricket','cricket','🏏',1,2,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(3,'Basketball','basketball','🏀',1,3,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(4,'Tennis','tennis','🎾',1,4,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(5,'Hockey','hockey','🏑',1,5,'2026-11-14 08:21:32','2026-11-14 08:21:32');
/*!40000 ALTER TABLE `sports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_code` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `share_percent` decimal(6,2) NOT NULL DEFAULT '95.00',
  `company_share_percent` decimal(6,2) NOT NULL DEFAULT '5.00',
  `session_commission_percent` decimal(6,2) NOT NULL DEFAULT '3.00',
  `casino_commission_percent` decimal(6,2) NOT NULL DEFAULT '2.00',
  `matka_commission_percent` decimal(6,2) NOT NULL DEFAULT '2.00',
  `secure_code` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'turnover',
  `commission_rate` decimal(6,2) NOT NULL DEFAULT '0.00',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `panel_permissions` json DEFAULT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `exposure` decimal(14,2) NOT NULL DEFAULT '0.00',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_status_index` (`role`,`status`),
  KEY `users_parent_id_index` (`parent_id`),
  KEY `users_agent_code_index` (`agent_code`),
  KEY `users_secure_code_index` (`secure_code`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'MAU NP','admin','SAD88852',140.00,95.00,5.00,3.00,2.00,2.00,'268856','turnover',2.00,'admin@betzone.test','9876543210','Mau NP',NULL,'$2y$12$/VC47XeynB3sYoP1qViTw.gxxqOTSRZGAbkvu8RYPClL0ahm2wseu','admin','active',NULL,2199109.00,0.00,NULL,'2026-11-18 10:23:35','2026-11-14 08:21:31','2026-11-18 10:23:35'),(2,10,'Demo Player','demo','C9497',0.00,0.00,0.00,0.00,0.00,0.00,'771981','net_loss',0.00,'demo@betzone.test','9812345678',NULL,NULL,'$2y$12$JCIa6lGuUO0OGxQ.N/l/AeGzqEbeNzHguE/BEVnEI3z4YXt/svW.2','client','active',NULL,19600.00,5000.00,NULL,NULL,'2026-03-18 18:30:00','2026-03-18 18:30:00'),(3,10,'Rahul Verma','rahul','C3290',0.00,0.00,0.00,0.00,0.00,0.00,'171236','net_loss',0.00,'rahul@betzone.test','9822334455',NULL,NULL,'$2y$12$phry59D3BOmUrRJbXd91F.tXzvuzlKB04AFPcCKDN101Z/6b.dmly','client','active',NULL,1250.00,3750.00,NULL,NULL,'2026-03-19 18:30:00','2026-03-19 18:30:00'),(4,11,'Priya Nair','priya','C7082',0.00,0.00,0.00,0.00,0.00,0.00,'701581','net_loss',0.00,'priya@betzone.test','9833445566',NULL,NULL,'$2y$12$/Fxqty0DLsdroJDQTj5edOCUDdOuyjSzuXnf43ez8VFyWo9/mB3qa','client','active',NULL,8200.00,5000.00,NULL,NULL,'2026-03-22 18:30:00','2026-03-22 18:30:00'),(5,11,'Vikram Singh','vikram','C5065',0.00,0.00,0.00,0.00,0.00,0.00,'165877','net_loss',0.00,'vikram@betzone.test','9844556677',NULL,NULL,'$2y$12$caWzUBXThgoJaz2RVIcRMOwK6DnnySLFdB9xwwi0EYb0V2AR6sSg.','client','active',NULL,2200.00,500.00,NULL,NULL,'2026-03-23 18:30:00','2026-03-23 18:30:00'),(6,10,'Karan Mehta','karan','C5141',0.00,0.00,0.00,0.00,0.00,0.00,'211524','net_loss',0.00,'karan@betzone.test','9855667788',NULL,NULL,'$2y$12$MwxHq42Nh.bB/GR5c6IsA.5BSXJBfQhRkoiRGtzOUdiYtLaUOgsDm','client','blocked',NULL,1500.00,0.00,NULL,NULL,'2026-03-27 18:30:00','2026-03-27 18:30:00'),(7,1,'Mini Master One','mini1','MINI1001',175.00,90.00,5.00,2.50,2.00,2.00,'510109','turnover',2.50,'mini1@betzone.test',NULL,NULL,NULL,'$2y$12$xKPCz5I49UpjLo1dARzyuO99B65NR51Y0GrCIfN2wqICkYBZ977Zu','mini_master','active',NULL,250000.00,0.00,NULL,'2026-11-18 11:14:48','2026-03-18 18:30:00','2026-11-18 11:14:48'),(8,7,'Master Master One','master1','MAST1001',175.00,85.00,5.00,2.50,2.00,2.00,'443819','turnover',2.50,'master1@betzone.test',NULL,NULL,NULL,'$2y$12$ZSygNTM/AHLmVpK5K1W.xOb5h7b3D6a7kqD34nrOWcdTDOASjlWCq','master_master','active',NULL,150000.00,0.00,NULL,NULL,'2026-03-18 18:30:00','2026-03-18 18:30:00'),(9,8,'Super Master One','super1','SUP1001',210.00,80.00,5.00,2.00,2.00,2.00,'449346','turnover',3.00,'super1@betzone.test',NULL,NULL,NULL,'$2y$12$Oo3wbMFsAEKBnhzuc0a03.5OUxUR9MfXhJxEmM4nfLUGG4hL0uc26','super_master','active',NULL,100000.00,0.00,NULL,NULL,'2026-03-18 18:30:00','2026-03-18 18:30:00'),(10,9,'Rohit Malhotra','agent1','AG1001',350.00,75.00,5.00,2.50,2.00,2.00,'404293','turnover',5.00,'agent1@betzone.test','9866778899',NULL,NULL,'$2y$12$JRbfiDui/1F77q2PX8kM0e45mXT12By6Zqz6FcS2mFNu3gjt0QhOm','agent','active',NULL,50000.00,0.00,NULL,'2026-11-18 10:23:55','2026-03-18 18:30:00','2026-11-18 10:23:55'),(11,10,'Neha Kapoor','agent2','AG1002',15.00,70.00,5.00,2.00,2.00,2.00,'482100','net_loss',3.00,'agent2@betzone.test','9877889900',NULL,NULL,'$2y$12$xB3FlgvlB6Uxc6tao4IET.QE2oLqUs/qLzVD8uPqDvbokkozxtmkC','agent','active',NULL,50000.00,0.00,NULL,NULL,'2026-03-19 18:30:00','2026-03-19 18:30:00'),(12,1,'Mini Admin Two','mini2','MINI1002',0.00,94.00,5.00,3.00,2.00,2.00,'821672','bet_by_bet',2.00,'mini2@betzone.local',NULL,'Mau NP',NULL,'$2y$12$tV7Vtv7RgFJ31wY.ayJta.Lye7za9fq6wGIQqgSiTW5U41Gv/ryWi','mini_master','active',NULL,431700.00,0.00,NULL,NULL,'2026-03-18 18:30:00','2026-03-18 18:30:00'),(13,1,'Mini Admin Three','mini3','MINI1003',0.00,93.00,5.00,3.00,2.00,2.00,'715517','bet_by_bet',2.00,'mini3@betzone.local',NULL,'Mau NP',NULL,'$2y$12$C2sKJwy.OLVW/zvTPUVNH.w3gu0LRxmaLQHWdVqsdgE/6rHZNfjW6','mini_master','active',NULL,-18400.00,0.00,NULL,NULL,'2026-03-19 18:30:00','2026-03-19 18:30:00'),(14,1,'Mini Admin Four','mini4','MINI1004',0.00,92.00,5.00,3.00,2.00,2.00,'141781','bet_by_bet',2.00,'mini4@betzone.local',NULL,'Mau NP',NULL,'$2y$12$YLfakfzoah4G6PJ6q7xlxOlrQKawcq065PlCeBwlZNzSko/kCNWwG','mini_master','active',NULL,211522.00,0.00,NULL,NULL,'2026-03-22 18:30:00','2026-03-22 18:30:00'),(15,1,'Mini Admin Five','mini5','MINI1005',0.00,91.00,5.00,3.00,2.00,2.00,'293631','bet_by_bet',2.00,'mini5@betzone.local',NULL,'Mau NP',NULL,'$2y$12$OWnxcw7pJquVGogfohUuWOEry44Kp1krhJ6Dt9GvenDDjdp6HvA2.','mini_master','inactive',NULL,0.00,0.00,NULL,NULL,'2026-03-27 18:30:00','2026-03-27 18:30:00'),(16,1,'Mini Admin Six','mini6','MINI1006',0.00,90.00,5.00,3.00,2.00,2.00,'613356','bet_by_bet',2.00,'mini6@betzone.local',NULL,'Mau NP',NULL,'$2y$12$18aouTtPuvpk4ABA8eK1Ie97KFzx6CTBlyS2GWdSzrzXLF6C4xBJS','mini_master','active',NULL,20000.00,0.00,NULL,NULL,'2026-04-12 18:30:00','2026-04-12 18:30:00'),(17,12,'Master Two','master2','MAST1002',0.00,80.00,5.00,3.00,2.00,2.00,'456868','bet_by_bet',2.00,'master2@betzone.local',NULL,'Mau NP',NULL,'$2y$12$qoxxYLSm2nOsUsbh.iPitelJR5ZaCHcX5QGXDgHb8BeAkqY0a/fZi','master_master','active',NULL,0.00,0.00,NULL,NULL,'2026-03-20 18:30:00','2026-03-20 18:30:00'),(18,13,'Master Three','master3','MAST1003',0.00,79.00,5.00,3.00,2.00,2.00,'553334','bet_by_bet',2.00,'master3@betzone.local',NULL,'Mau NP',NULL,'$2y$12$mFnRwZZ.FWms/DfyA3gJLOEfU9naKdeuFGMQavda21NPYYE9HsMRG','master_master','active',NULL,-5200.00,0.00,NULL,NULL,'2026-03-22 18:30:00','2026-03-22 18:30:00'),(19,14,'Master Four','master4','MAST1004',0.00,78.00,5.00,3.00,2.00,2.00,'511097','bet_by_bet',2.00,'master4@betzone.local',NULL,'Mau NP',NULL,'$2y$12$zttRGDxPsXXPVZJwqWQgo.EZEtRQE1lEf7ii9rgv.KCTgZeVm1gM.','master_master','active',NULL,0.00,0.00,NULL,NULL,'2026-03-24 18:30:00','2026-03-24 18:30:00'),(20,15,'Master Five','master5','MAST1005',0.00,77.00,5.00,3.00,2.00,2.00,'282066','bet_by_bet',2.00,'master5@betzone.local',NULL,'Mau NP',NULL,'$2y$12$1GrQqYKTMry4auvIVFulxekrPJGs2NecOV3K7giBYgcxZnf5CXTXu','master_master','blocked',NULL,120000.00,0.00,NULL,NULL,'2026-04-01 18:30:00','2026-04-01 18:30:00'),(21,17,'Super Two','super2','SUP1002',0.00,85.00,5.00,3.00,2.00,2.00,'966271','bet_by_bet',2.00,'super2@betzone.local',NULL,'Mau NP',NULL,'$2y$12$46CXN6LWhWXmSeSxmCMIaODtCnjE.XzQtQm42jYLgrZXCGdQ0kONe','super_master','active',NULL,118175.00,0.00,NULL,NULL,'2026-03-18 18:30:00','2026-03-18 18:30:00'),(22,18,'Super Three','super3','SUP1003',0.00,84.00,5.00,3.00,2.00,2.00,'447793','bet_by_bet',2.00,'super3@betzone.local',NULL,'Mau NP',NULL,'$2y$12$Tt316XPLs823.mTju5zWd.JMfPzWs6SSfHiWgzMu8dZUyT2sHRDMW','super_master','active',NULL,-9800.00,0.00,NULL,NULL,'2026-03-20 18:30:00','2026-03-20 18:30:00'),(23,19,'Super Four','super4','SUP1004',0.00,83.00,5.00,3.00,2.00,2.00,'520129','bet_by_bet',2.00,'super4@betzone.local',NULL,'Mau NP',NULL,'$2y$12$4ZO/HbQgM1PQ9JmuRrKcwuCp0eAnM6y0nj1G.Qha.RNT4btd1SEMy','super_master','active',NULL,256.00,0.00,NULL,NULL,'2026-03-23 18:30:00','2026-03-23 18:30:00'),(24,20,'Super Five','super5','SUP1005',0.00,82.00,5.00,3.00,2.00,2.00,'107734','bet_by_bet',2.00,'super5@betzone.local',NULL,'Mau NP',NULL,'$2y$12$2SOOn7lIIb.7V.AEDODQHuupjaR/7h1c2AB5WPMIlNM0uj522UpxG','super_master','inactive',NULL,1740.00,0.00,NULL,NULL,'2026-03-25 18:30:00','2026-03-25 18:30:00'),(25,21,'Agent Three','agent3','AG1003',0.00,70.00,5.00,3.00,2.00,2.00,'721913','bet_by_bet',2.00,'agent3@betzone.local',NULL,'Mau NP',NULL,'$2y$12$5fOY8weCbi2GABup.5NmueDvImCt5fCQQz1GNRFZHwRHMryKCEaUq','agent','active',NULL,298707.00,0.00,NULL,NULL,'2026-03-18 18:30:00','2026-03-18 18:30:00'),(26,22,'Agent Four','agent4','AG1004',0.00,69.00,5.00,3.00,2.00,2.00,'873395','bet_by_bet',2.00,'agent4@betzone.local',NULL,'Mau NP',NULL,'$2y$12$PQqLCZAIpbX6fvqA0GgYyOmxJ6T4CdvZ/kmWJL9bjkuWwz8qgXy.i','agent','active',NULL,-4300.00,0.00,NULL,NULL,'2026-03-20 18:30:00','2026-03-20 18:30:00'),(27,23,'Agent Five','agent5','AG1005',0.00,68.00,5.00,3.00,2.00,2.00,'263697','bet_by_bet',2.00,'agent5@betzone.local',NULL,'Mau NP',NULL,'$2y$12$ffi65xQEESMn9e0kShQe2eudgrQmX7lkC3bCO1l2KngLzFMyLC4H6','agent','active',NULL,20560.00,0.00,NULL,NULL,'2026-03-23 18:30:00','2026-03-23 18:30:00'),(28,10,'Client Six','client6','C6001',0.00,0.00,0.00,0.00,0.00,0.00,'710902','net_loss',0.00,'client6@betzone.local',NULL,'Mau NP',NULL,'$2y$12$pJrO2uWzzRB24Fr7eoYU2u0v6Dz88Z0uHAc05jydSfqVaaaLPNPCe','client','active',NULL,90.00,0.00,NULL,NULL,'2026-03-19 18:30:00','2026-11-18 10:22:32'),(29,11,'Client Seven','client7','C6002',0.00,0.00,0.00,0.00,0.00,0.00,'540832','net_loss',0.00,'client7@betzone.local',NULL,'Mau NP',NULL,'$2y$12$WZyJUlnbUFr8cIbFMN5egOO4lvptNHZMyekrriVUGo20JOpaCZikC','client','active',NULL,-150.00,0.00,NULL,NULL,'2026-03-20 18:30:00','2026-11-18 10:22:32'),(30,25,'Client Eight','client8','C6003',0.00,0.00,0.00,0.00,0.00,0.00,'580542','net_loss',0.00,'client8@betzone.local',NULL,'Mau NP',NULL,'$2y$12$/ceVDhBrf9U/k0xKWjpZROop5.e9kR6.071SbKLG9I74sqWXv02CW','client','active',NULL,0.65,0.00,NULL,NULL,'2026-03-21 18:30:00','2026-11-18 10:22:32'),(31,26,'Client Nine','client9','C6004',0.00,0.00,0.00,0.00,0.00,0.00,'391649','net_loss',0.00,'client9@betzone.local',NULL,'Mau NP',NULL,'$2y$12$PTA97HgBGLvjyZRgV1C5C.hyd5GnIHShfy9CFgJMcRrZ85mUxZrWi','client','inactive',NULL,11.70,0.00,NULL,NULL,'2026-03-23 18:30:00','2026-11-18 10:22:32'),(32,27,'Client Ten','client10','C6005',0.00,0.00,0.00,0.00,0.00,0.00,'724264','net_loss',0.00,'client10@betzone.local',NULL,'Mau NP',NULL,'$2y$12$q9FlZyPKl/c6QGTmMwpxrOyvj2upLB/Oeo7/kK1sQch8uTQgIORAy','client','active',NULL,61.50,0.00,NULL,NULL,'2026-03-24 18:30:00','2026-11-18 10:22:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallet_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` enum('deposit','withdrawal','bet','win','refund','admin_credit','admin_debit','bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `balance_after` decimal(14,2) NOT NULL,
  `status` enum('completed','pending','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `is_demo` tinyint(1) NOT NULL DEFAULT '0',
  `reference_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_transactions_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `wallet_transactions_type_status_index` (`type`,`status`),
  CONSTRAINT `wallet_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_transactions`
--

LOCK TABLES `wallet_transactions` WRITE;
/*!40000 ALTER TABLE `wallet_transactions` DISABLE KEYS */;
INSERT INTO `wallet_transactions` VALUES (1,2,'admin_credit',15000.00,15000.00,'completed',0,NULL,NULL,'Opening demo balance',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(2,3,'admin_credit',3500.00,3500.00,'completed',0,NULL,NULL,'Opening demo balance',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(3,4,'admin_credit',12000.00,12000.00,'completed',0,NULL,NULL,'Opening demo balance',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(4,5,'admin_credit',3200.00,3200.00,'completed',0,NULL,NULL,'Opening demo balance',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(5,6,'admin_credit',1500.00,1500.00,'completed',0,NULL,NULL,'Opening demo balance',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(6,2,'deposit',10000.00,25000.00,'completed',0,'deposit_request',1,'Deposit approved #1 · UPI',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(7,3,'deposit',5000.00,8500.00,'completed',0,'deposit_request',2,'Deposit approved #2 · Bank Transfer',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(8,2,'bet',-2000.00,23000.00,'completed',0,'bet',1,'Bet BZ261114QLABA · Arsenal @ 2.10','{\"match\": \"Arsenal vs Chelsea\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(9,2,'bet',-1500.00,21500.00,'completed',0,'bet',2,'Bet BZ261114ZAEIE · Real Madrid @ 2.35','{\"match\": \"Real Madrid vs Barcelona\", \"market\": \"Match Winner\"}','2026-11-12 08:21:32','2026-11-12 08:21:32'),(10,2,'bet',-1000.00,20500.00,'completed',0,'bet',3,'Bet BZ261114VJNDT · India @ 1.72','{\"match\": \"India vs Australia\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(11,2,'bet',-500.00,20000.00,'completed',0,'bet',4,'Bet BZ261114WOTBZ · Los Angeles Lakers @ 1.75','{\"match\": \"Los Angeles Lakers vs Boston Celtics\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(12,3,'bet',-1000.00,7500.00,'completed',0,'bet',5,'Bet BZ261114OGTFV · Chelsea @ 3.25','{\"match\": \"Arsenal vs Chelsea\", \"market\": \"Match Winner\"}','2026-11-09 08:21:32','2026-11-09 08:21:32'),(13,3,'bet',-2000.00,5500.00,'completed',0,'bet',6,'Bet BZ261114XHV1V · Chennai Super Kings @ 1.90','{\"match\": \"Mumbai Indians vs Chennai Super Kings\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(14,3,'bet',-750.00,4750.00,'completed',0,'bet',7,'Bet BZ261114AVILZ · Carlos Alcaraz @ 1.80','{\"match\": \"Carlos Alcaraz vs Jannik Sinner\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(15,4,'bet',-3000.00,9000.00,'completed',0,'bet',8,'Bet BZ261114VITU5 · Barcelona @ 2.90','{\"match\": \"Real Madrid vs Barcelona\", \"market\": \"Match Winner\"}','2026-11-11 08:21:32','2026-11-11 08:21:32'),(16,4,'bet',-1200.00,7800.00,'completed',0,'bet',9,'Bet BZ2611149MEKA · Inter Milan @ 1.95','{\"match\": \"Inter Milan vs Juventus\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(17,4,'bet',-800.00,7000.00,'completed',0,'bet',10,'Bet BZ261114LDO4N · Jannik Sinner @ 2.00','{\"match\": \"Carlos Alcaraz vs Jannik Sinner\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(18,5,'bet',-500.00,2700.00,'completed',0,'bet',11,'Bet BZ261114C4WLW · Toronto Maple Leafs @ 1.85','{\"match\": \"Toronto Maple Leafs vs Montreal Canadiens\", \"market\": \"Match Winner\"}','2026-11-13 08:21:32','2026-11-13 08:21:32'),(19,2,'bet',-3000.00,17000.00,'completed',0,'bet',12,'Bet BZ261114BC9D2 · Liverpool @ 2.20','{\"match\": \"Liverpool vs Manchester City\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(20,3,'bet',-2500.00,2250.00,'completed',0,'bet',13,'Bet BZ2611146HJUS · Manchester City @ 3.10','{\"match\": \"Liverpool vs Manchester City\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(21,4,'bet',-1000.00,6000.00,'completed',0,'bet',14,'Bet BZ261114XSURX · Liverpool @ 2.20','{\"match\": \"Liverpool vs Manchester City\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(22,5,'bet',-500.00,2200.00,'completed',0,'bet',15,'Bet BZ2611143MZHC · Manchester City @ 3.10','{\"match\": \"Liverpool vs Manchester City\", \"market\": \"Match Winner\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(23,2,'win',6600.00,23600.00,'completed',0,'bet',12,'Bet won BZ261114BC9D2 · Liverpool','{\"odds\": 2.2, \"stake\": 3000}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(24,4,'win',2200.00,8200.00,'completed',0,'bet',14,'Bet won BZ261114XSURX · Liverpool','{\"odds\": 2.2, \"stake\": 1000}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(25,2,'withdrawal',-4000.00,19600.00,'pending',0,'withdrawal_request',1,'Withdrawal requested #1 · UPI','{\"account_name\": \"Demo Player\", \"account_number\": \"9876543210\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(26,3,'withdrawal',-1000.00,1250.00,'completed',0,'withdrawal_request',2,'Withdrawal requested #2 · Bank Transfer','{\"account_name\": \"Rahul Verma\", \"account_number\": \"50100200300400\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(27,4,'withdrawal',-1500.00,6700.00,'rejected',0,'withdrawal_request',3,'Withdrawal requested #3 · UPI','{\"account_name\": \"Priya Nair\", \"account_number\": \"9822334455\"}','2026-11-14 08:21:32','2026-11-14 08:21:32'),(28,4,'refund',1500.00,8200.00,'completed',0,'withdrawal_request',3,'Withdrawal rejected #3 · amount returned',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(29,1,'admin_credit',2199109.00,2199109.00,'completed',0,NULL,NULL,'Opening admin coin balance',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(30,7,'admin_credit',250000.00,250000.00,'completed',0,NULL,NULL,'Opening coin balance',NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(31,8,'admin_credit',150000.00,150000.00,'completed',0,NULL,NULL,'Opening coin balance',NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(32,9,'admin_credit',100000.00,100000.00,'completed',0,NULL,NULL,'Opening coin balance',NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(33,12,'admin_credit',431700.00,431700.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:33','2026-11-14 08:21:33'),(34,14,'admin_credit',211522.00,211522.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:34','2026-11-14 08:21:34'),(35,16,'admin_credit',20000.00,20000.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:34','2026-11-14 08:21:34'),(36,20,'admin_credit',120000.00,120000.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:35','2026-11-14 08:21:35'),(37,21,'admin_credit',118175.00,118175.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:35','2026-11-14 08:21:35'),(38,23,'admin_credit',256.00,256.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:35','2026-11-14 08:21:35'),(39,24,'admin_credit',1740.00,1740.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:35','2026-11-14 08:21:35'),(40,25,'admin_credit',298707.00,298707.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:36','2026-11-14 08:21:36'),(41,27,'admin_credit',20560.00,20560.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:36','2026-11-14 08:21:36'),(42,28,'admin_credit',90.00,90.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:36','2026-11-14 08:21:36'),(43,29,'admin_credit',24.00,24.00,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:36','2026-11-14 08:21:36'),(44,30,'admin_credit',0.65,0.65,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:36','2026-11-14 08:21:36'),(45,31,'admin_credit',11.70,11.70,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:37','2026-11-14 08:21:37'),(46,32,'admin_credit',61.50,61.50,'completed',0,NULL,NULL,'Opening demo chips',NULL,'2026-11-14 08:21:37','2026-11-14 08:21:37'),(47,1,'admin_credit',192000.00,6004030.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO CLIENT8 (C6003) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"client8\"}','2026-11-13 05:37:00','2026-11-13 05:37:00'),(48,1,'admin_debit',-302000.00,2383369.00,'completed',1,'demo_statement',NULL,'COINS DEBITED FROM AGENT5 (AG1005) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"agent5\"}','2026-11-09 14:36:00','2026-11-09 14:36:00'),(49,1,'admin_credit',263000.00,2774452.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO SUPER3 (SUP1003) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"super3\"}','2026-11-08 12:12:00','2026-11-08 12:12:00'),(50,1,'admin_credit',245000.00,4751742.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO MINI4 (MINI1004) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"mini4\"}','2026-11-04 06:35:00','2026-11-04 06:35:00'),(51,1,'admin_debit',-56000.00,5531913.00,'completed',1,'demo_statement',NULL,'COINS DEBITED FROM SUPER2 (SUP1002) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"super2\"}','2026-10-31 03:31:00','2026-10-31 03:31:00'),(52,1,'admin_credit',85000.00,5189392.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO SUPER2 (SUP1002) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"super2\"}','2026-10-28 15:08:00','2026-10-28 15:08:00'),(53,1,'admin_credit',348000.00,5319801.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO AGENT5 (AG1005) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"agent5\"}','2026-10-25 11:52:00','2026-10-25 11:52:00'),(54,1,'admin_debit',-342000.00,5058025.00,'completed',1,'demo_statement',NULL,'COINS DEBITED FROM AGENT1 (AG1001) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"agent1\"}','2026-10-22 14:53:00','2026-10-22 14:53:00'),(55,1,'admin_credit',280000.00,5327460.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO KARAN (C5141) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"karan\"}','2026-10-21 10:41:00','2026-10-21 10:41:00'),(56,1,'admin_credit',264000.00,2989889.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO MASTER1 (MAST1001) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"master1\"}','2026-10-16 15:25:00','2026-10-16 15:25:00'),(57,1,'admin_debit',-372000.00,3252111.00,'completed',1,'demo_statement',NULL,'COINS DEBITED FROM AGENT2 (AG1002) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"agent2\"}','2026-10-15 05:07:00','2026-10-15 05:07:00'),(58,1,'admin_credit',98000.00,4410186.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO AGENT4 (AG1004) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"agent4\"}','2026-10-10 10:57:00','2026-10-10 10:57:00'),(59,1,'admin_credit',51000.00,5850618.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO MINI6 (MINI1006) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"mini6\"}','2026-10-08 12:02:00','2026-10-08 12:02:00'),(60,1,'admin_debit',-194000.00,2258269.00,'completed',1,'demo_statement',NULL,'COINS DEBITED FROM CLIENT8 (C6003) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"client8\"}','2026-10-04 13:43:00','2026-10-04 13:43:00'),(61,1,'admin_credit',64000.00,3767551.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO MINI6 (MINI1006) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"mini6\"}','2026-10-03 12:45:00','2026-10-03 12:45:00'),(62,1,'admin_credit',359000.00,3510650.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO CLIENT6 (C6001) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"client6\"}','2026-09-28 04:35:00','2026-09-28 04:35:00'),(63,1,'admin_debit',-166000.00,3577156.00,'completed',1,'demo_statement',NULL,'COINS DEBITED FROM MASTER1 (MAST1001) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"master1\"}','2026-09-27 04:44:00','2026-09-27 04:44:00'),(64,1,'admin_credit',64000.00,3238459.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO VIKRAM (C5065) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"vikram\"}','2026-09-23 15:23:00','2026-09-23 15:23:00'),(65,1,'admin_credit',330000.00,5497804.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO KARAN (C5141) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"karan\"}','2026-09-20 11:11:00','2026-09-20 11:11:00'),(66,1,'admin_debit',-128000.00,5935541.00,'completed',1,'demo_statement',NULL,'COINS DEBITED FROM VIKRAM (C5065) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"vikram\"}','2026-09-18 09:54:00','2026-09-18 09:54:00'),(67,1,'admin_credit',347000.00,6189032.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO MINI5 (MINI1005) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"mini5\"}','2026-09-13 15:08:00','2026-09-13 15:08:00'),(68,1,'admin_credit',192000.00,2481980.00,'completed',1,'demo_statement',NULL,'COINS CREDITED TO AGENT2 (AG1002) BY MAU NP (SAD88852) [SAD88852,MAU NP]','{\"demo\": true, \"target\": \"agent2\"}','2026-09-10 07:24:00','2026-09-10 07:24:00'),(69,10,'admin_credit',50000.00,50000.00,'completed',0,NULL,NULL,'Opening demo betting chips',NULL,'2026-11-18 10:22:32','2026-11-18 10:22:32'),(70,11,'admin_credit',50000.00,50000.00,'completed',0,NULL,NULL,'Opening demo betting chips',NULL,'2026-11-18 10:22:32','2026-11-18 10:22:32');
/*!40000 ALTER TABLE `wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawal_requests`
--

DROP TABLE IF EXISTS `withdrawal_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `withdrawal_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('upi','bank','usdt') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upi',
  `account_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ifsc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upi_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawal_requests_user_id_foreign` (`user_id`),
  KEY `withdrawal_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `withdrawal_requests_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `withdrawal_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `withdrawal_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawal_requests`
--

LOCK TABLES `withdrawal_requests` WRITE;
/*!40000 ALTER TABLE `withdrawal_requests` DISABLE KEYS */;
INSERT INTO `withdrawal_requests` VALUES (1,2,4000.00,'upi','Demo Player','9876543210',NULL,'demo@upi','pending',NULL,NULL,NULL,'2026-11-14 08:21:32','2026-11-14 08:21:32'),(2,3,1000.00,'bank','Rahul Verma','50100200300400','HDFC0001234',NULL,'approved','Paid via NEFT.',1,'2026-11-14 08:21:32','2026-11-14 08:21:32','2026-11-14 08:21:32'),(3,4,1500.00,'upi','Priya Nair','9822334455',NULL,'priya@upi','rejected','UPI ID name mismatch. Please update KYC.',1,'2026-11-14 08:21:32','2026-11-14 08:21:32','2026-11-14 08:21:32');
/*!40000 ALTER TABLE `withdrawal_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'betzone'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 16:45:18
