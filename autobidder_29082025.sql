-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: 127.0.0.1    Database: u773742080_autobidder
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `allocate_share_histories`
--

DROP TABLE IF EXISTS `allocate_share_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allocate_share_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_share_id` bigint(20) unsigned NOT NULL,
  `shares` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `allocate_share_histories_user_share_id_foreign` (`user_share_id`),
  CONSTRAINT `allocate_share_histories_user_share_id_foreign` FOREIGN KEY (`user_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allocate_share_histories`
--

LOCK TABLES `allocate_share_histories` WRITE;
/*!40000 ALTER TABLE `allocate_share_histories` DISABLE KEYS */;
INSERT INTO `allocate_share_histories` VALUES (1,1,50000,1,'2025-08-27 06:34:47','2025-08-27 06:34:47'),(2,2,50000,1,'2025-08-27 06:34:59','2025-08-27 06:34:59'),(3,3,50000,1,'2025-08-27 06:35:18','2025-08-27 06:35:18'),(4,4,50000,1,'2025-08-27 06:35:29','2025-08-27 06:35:29'),(5,5,50000,1,'2025-08-27 06:35:45','2025-08-27 06:35:45'),(6,6,50000,1,'2025-08-27 06:36:10','2025-08-27 06:36:10'),(7,22,50000,1,'2025-08-27 12:18:25','2025-08-27 12:18:25'),(8,23,50000,1,'2025-08-27 12:18:45','2025-08-27 12:18:45'),(9,24,50000,1,'2025-08-27 12:18:57','2025-08-27 12:18:57'),(10,25,50000,1,'2025-08-27 12:19:13','2025-08-27 12:19:13'),(11,26,50000,1,'2025-08-27 12:19:31','2025-08-27 12:19:31'),(12,27,50000,1,'2025-08-27 12:19:52','2025-08-27 12:19:52'),(13,37,50000,1,'2025-08-28 07:58:30','2025-08-28 07:58:30'),(14,38,30000,1,'2025-08-28 07:58:41','2025-08-28 07:58:41'),(15,39,65000,1,'2025-08-28 10:56:21','2025-08-28 10:56:21');
/*!40000 ALTER TABLE `allocate_share_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `excerpt` text NOT NULL,
  `description` longtext DEFAULT NULL,
  `video_url` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Commence date','Commence date is 2/10/2023. \r\n\r\nThe Auction is open daily from 8:00 AM-11:59 PM','<p>Commence date is 2/10/2023.&nbsp;</p><p><strong>The Auction is open daily from 8:00 AM-11:59 PM</strong></p><p>The minimum trading amount is KSH 1500/= and the maximum is 5000/= Once you are paired with the seller, Kindly Click \'pay now\' before the time elapses. All shares not paid up will be returned to the dashboard.&nbsp;</p><p>&nbsp;</p><p><strong>DO NOT CLICK ‘PAY NOW’ BEFORE SENDING MONEY TO THE PAYEE.&nbsp;</strong></p><p>&nbsp;</p><p>Copy-paste the MPESA <strong>‘Transaction ID’</strong> in the Pop-up. You can Call, or text Payee to Confirm you.</p><p>&nbsp;</p><blockquote><p><strong>NB: Only Send money to the MPESA Numbers displayed in your Account.</strong></p></blockquote><p><strong>We will block buyers who continuously buy shares and fail to pay Up.</strong></p><p>&nbsp;</p><p><strong>TRADING IS FREE </strong>and we <strong>do not </strong>charge Commission or any up-front charges.</p><p>&nbsp;</p><p>Contact Us <a href=\"https://www.autobidder.live/support\">here</a> for Inquiry</p><p>&nbsp;</p><p>Join our WhatsApp channel here:&nbsp;</p><p>Join our Telegram channel here: <a href=\"https://t.me/+0kkvjg6icXM5YTc0\">https://t.me/+0kkvjg6icXM5YTc0</a></p>',NULL,NULL,1,'2025-08-28 13:02:05','2025-08-28 13:02:05');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_settings`
--

DROP TABLE IF EXISTS `chat_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) NOT NULL,
  `value` text NOT NULL,
  `type` varchar(191) NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_settings`
--

LOCK TABLES `chat_settings` WRITE;
/*!40000 ALTER TABLE `chat_settings` DISABLE KEYS */;
INSERT INTO `chat_settings` VALUES (1,'chat_enabled','0','boolean','Enable or disable the chat system','2025-08-27 06:19:35','2025-08-27 06:33:56'),(2,'chat_character_limit','100','integer','Maximum character limit for chat messages','2025-08-27 06:19:35','2025-08-27 06:19:35'),(3,'chat_file_upload_enabled','0','boolean','Allow file uploads in chat','2025-08-27 06:19:35','2025-08-27 06:33:56'),(4,'chat_max_file_size','5120','integer','Maximum file size in KB for chat uploads','2025-08-27 06:19:35','2025-08-27 06:19:35');
/*!40000 ALTER TABLE `chat_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `buyer_share_id` bigint(20) unsigned NOT NULL,
  `seller_share_id` bigint(20) unsigned NOT NULL,
  `user_share_pair_id` bigint(20) unsigned NOT NULL,
  `status` enum('active','ended') NOT NULL DEFAULT 'active',
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversations_user_share_pair_id_unique` (`user_share_pair_id`),
  KEY `conversations_seller_share_id_foreign` (`seller_share_id`),
  KEY `conversations_buyer_share_id_seller_share_id_index` (`buyer_share_id`,`seller_share_id`),
  KEY `conversations_status_index` (`status`),
  CONSTRAINT `conversations_buyer_share_id_foreign` FOREIGN KEY (`buyer_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_seller_share_id_foreign` FOREIGN KEY (`seller_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_user_share_pair_id_foreign` FOREIGN KEY (`user_share_pair_id`) REFERENCES `user_share_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
-- Table structure for table `general_settings`
--

DROP TABLE IF EXISTS `general_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `general_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `general_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_settings`
--

LOCK TABLES `general_settings` WRITE;
/*!40000 ALTER TABLE `general_settings` DISABLE KEYS */;
INSERT INTO `general_settings` VALUES (1,'min_trading_price','1000','2025-08-27 06:33:00','2025-08-27 06:33:00'),(2,'max_trading_price','100000','2025-08-27 06:33:00','2025-08-27 06:33:00'),(3,'reffaral_bonus','0','2025-08-27 06:33:39','2025-08-27 06:33:39'),(4,'bought_time','60','2025-08-27 06:33:39','2025-08-27 07:03:26'),(5,'app_timezone','UTC','2025-08-27 06:33:39','2025-08-27 06:33:39');
/*!40000 ALTER TABLE `general_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `share_id` bigint(20) unsigned NOT NULL,
  `reff_user_id` bigint(20) unsigned DEFAULT NULL,
  `old_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `add_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `new_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(191) NOT NULL DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoices_user_id_index` (`user_id`),
  KEY `invoices_share_id_index` (`share_id`),
  KEY `invoices_reff_user_id_index` (`reff_user_id`),
  CONSTRAINT `invoices_reff_user_id_foreign` FOREIGN KEY (`reff_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_share_id_foreign` FOREIGN KEY (`share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `logable_type` varchar(191) NOT NULL,
  `logable_id` bigint(20) unsigned NOT NULL,
  `type` text NOT NULL COMMENT 'share, referral, payment',
  `remarks` text NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logs_user_id_foreign` (`user_id`),
  KEY `logs_logable_type_logable_id_index` (`logable_type`,`logable_id`),
  CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (5,8,'App\\Models\\User',8,'signup','Signup Successfully.','0','2025-08-27 06:19:43','2025-08-27 06:19:43'),(6,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-27 06:22:17','2025-08-27 06:22:17'),(7,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-27 06:24:43','2025-08-27 06:24:43'),(8,9,'App\\Models\\User',9,'restore','User account restored from backup','0','2025-08-27 06:27:32','2025-08-27 06:27:32'),(9,9,'App\\Models\\User',9,'username_update','Username changed from driftwood to maddyPower','0','2025-08-27 06:28:05','2025-08-27 06:28:05'),(10,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-27 06:37:48','2025-08-27 06:37:48'),(11,8,'App\\Models\\UserShare',7,'share','Share bought successfully.','1000','2025-08-27 06:40:06','2025-08-27 06:40:06'),(12,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 06:41:20','2025-08-27 06:41:20'),(13,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 06:42:18','2025-08-27 06:42:18'),(14,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 06:43:59','2025-08-27 06:43:59'),(15,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-27 06:44:24','2025-08-27 06:44:24'),(16,8,'App\\Models\\UserShare',8,'share','Share bought successfully.','1000','2025-08-27 06:44:46','2025-08-27 06:44:46'),(17,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 06:45:38','2025-08-27 06:45:38'),(18,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-27 06:45:49','2025-08-27 06:45:49'),(19,9,'App\\Models\\UserShare',9,'share','Share bought successfully.','1000','2025-08-27 06:47:20','2025-08-27 06:47:20'),(20,9,'App\\Models\\UserShare',10,'share','Share bought successfully.','5000','2025-08-27 06:52:05','2025-08-27 06:52:05'),(21,9,'App\\Models\\UserShare',11,'share','Share bought successfully.','2500','2025-08-27 07:04:51','2025-08-27 07:04:51'),(22,9,'App\\Models\\UserShare',12,'share','Share bought successfully.','6500','2025-08-27 07:15:34','2025-08-27 07:15:34'),(23,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 08:48:25','2025-08-27 08:48:25'),(24,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-27 09:26:17','2025-08-27 09:26:17'),(25,8,'App\\Models\\UserShare',15,'share','Share bought successfully.','5000','2025-08-27 09:28:39','2025-08-27 09:28:39'),(26,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 09:48:56','2025-08-27 09:48:56'),(27,9,'App\\Models\\UserShare',16,'share','Share bought successfully.','5000','2025-08-27 09:51:09','2025-08-27 09:51:09'),(28,9,'App\\Models\\UserShare',17,'share','Share bought successfully.','7800','2025-08-27 10:00:18','2025-08-27 10:00:18'),(29,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-27 10:03:05','2025-08-27 10:03:05'),(30,8,'App\\Models\\UserSharePayment',1,'payment','You received a payment from maddyPower','7800','2025-08-27 10:31:37','2025-08-27 10:31:37'),(31,9,'App\\Models\\UserSharePayment',1,'payment','You made a payment for Danny','7800','2025-08-27 10:31:37','2025-08-27 10:31:37'),(32,8,'App\\Models\\UserSharePayment',2,'payment','You received a payment from maddyPower','7800','2025-08-27 10:31:45','2025-08-27 10:31:45'),(33,9,'App\\Models\\UserSharePayment',2,'payment','You made a payment for Danny','7800','2025-08-27 10:31:45','2025-08-27 10:31:45'),(34,8,'App\\Models\\UserSharePayment',3,'payment','You received a payment from maddyPower','7800','2025-08-27 10:31:53','2025-08-27 10:31:53'),(35,9,'App\\Models\\UserSharePayment',3,'payment','You made a payment for Danny','7800','2025-08-27 10:31:53','2025-08-27 10:31:53'),(36,8,'App\\Models\\UserSharePayment',4,'payment','You received a payment from maddyPower','7800','2025-08-27 10:31:59','2025-08-27 10:31:59'),(37,9,'App\\Models\\UserSharePayment',4,'payment','You made a payment for Danny','7800','2025-08-27 10:31:59','2025-08-27 10:31:59'),(38,8,'App\\Models\\UserSharePayment',4,'payment','You confirmed a payment from maddyPower','7800','2025-08-27 10:32:43','2025-08-27 10:32:43'),(39,9,'App\\Models\\UserSharePayment',4,'payment','Your payment is confirmed by Danny','7800','2025-08-27 10:32:43','2025-08-27 10:32:43'),(40,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 10:48:48','2025-08-27 10:48:48'),(41,8,'App\\Models\\UserShare',18,'share','Share bought successfully.','10000','2025-08-27 10:49:43','2025-08-27 10:49:43'),(42,9,'App\\Models\\UserShare',19,'share','Share bought successfully.','5067','2025-08-27 10:52:35','2025-08-27 10:52:35'),(43,8,'App\\Models\\UserShare',20,'share','Share bought successfully.','3200','2025-08-27 10:53:30','2025-08-27 10:53:30'),(44,8,'App\\Models\\UserShare',21,'share','Share bought successfully.','3000','2025-08-27 11:02:31','2025-08-27 11:02:31'),(45,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 12:12:17','2025-08-27 12:12:17'),(46,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-27 12:17:58','2025-08-27 12:17:58'),(47,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 12:20:11','2025-08-27 12:20:11'),(48,9,'App\\Models\\UserShare',30,'share','Share bought successfully.','5000','2025-08-27 13:10:13','2025-08-27 13:10:13'),(49,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 13:10:59','2025-08-27 13:10:59'),(50,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-27 13:15:34','2025-08-27 13:15:34'),(51,8,'App\\Models\\UserShare',31,'share','Share bought successfully.','5000','2025-08-27 14:12:04','2025-08-27 14:12:04'),(52,9,'App\\Models\\UserSharePayment',10,'payment','You received a payment from Danny','5000','2025-08-27 14:24:39','2025-08-27 14:24:39'),(53,8,'App\\Models\\UserSharePayment',10,'payment','You made a payment for maddyPower','5000','2025-08-27 14:24:39','2025-08-27 14:24:39'),(54,9,'App\\Models\\UserSharePayment',10,'payment','You confirmed a payment from Danny','5000','2025-08-27 14:25:35','2025-08-27 14:25:35'),(55,8,'App\\Models\\UserSharePayment',10,'payment','Your payment is confirmed by maddyPower','5000','2025-08-27 14:25:35','2025-08-27 14:25:35'),(56,8,'App\\Models\\UserShare',32,'share','Share bought successfully.','6000','2025-08-27 14:35:54','2025-08-27 14:35:54'),(57,9,'App\\Models\\UserSharePayment',11,'payment','You received a payment from Danny','6000','2025-08-27 14:36:51','2025-08-27 14:36:51'),(58,8,'App\\Models\\UserSharePayment',11,'payment','You made a payment for maddyPower','6000','2025-08-27 14:36:51','2025-08-27 14:36:51'),(59,9,'App\\Models\\UserSharePayment',11,'payment','You confirmed a payment from Danny','6000','2025-08-27 14:37:25','2025-08-27 14:37:25'),(60,8,'App\\Models\\UserSharePayment',11,'payment','Your payment is confirmed by maddyPower','6000','2025-08-27 14:37:25','2025-08-27 14:37:25'),(61,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-27 15:51:25','2025-08-27 15:51:25'),(62,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-27 15:51:53','2025-08-27 15:51:53'),(63,9,'App\\Models\\UserShare',33,'share','Share bought successfully.','5000','2025-08-27 15:52:23','2025-08-27 15:52:23'),(64,8,'App\\Models\\UserSharePayment',12,'payment','You received a payment from maddyPower','5000','2025-08-27 15:52:36','2025-08-27 15:52:36'),(65,9,'App\\Models\\UserSharePayment',12,'payment','You made a payment for Danny','5000','2025-08-27 15:52:36','2025-08-27 15:52:36'),(66,8,'App\\Models\\UserSharePayment',12,'payment','You confirmed a payment from maddyPower','5000','2025-08-27 15:53:09','2025-08-27 15:53:09'),(67,9,'App\\Models\\UserSharePayment',12,'payment','Your payment is confirmed by Danny','5000','2025-08-27 15:53:09','2025-08-27 15:53:09'),(68,9,'App\\Models\\UserShare',34,'share','Share bought successfully.','5000','2025-08-27 15:57:51','2025-08-27 15:57:51'),(69,8,'App\\Models\\UserSharePayment',13,'payment','You received a payment from maddyPower','5000','2025-08-27 15:58:16','2025-08-27 15:58:16'),(70,9,'App\\Models\\UserSharePayment',13,'payment','You made a payment for Danny','5000','2025-08-27 15:58:16','2025-08-27 15:58:16'),(71,8,'App\\Models\\UserSharePayment',13,'payment','You confirmed a payment from maddyPower','5000','2025-08-27 16:21:56','2025-08-27 16:21:56'),(72,9,'App\\Models\\UserSharePayment',13,'payment','Your payment is confirmed by Danny','5000','2025-08-27 16:21:56','2025-08-27 16:21:56'),(73,8,'App\\Models\\UserShare',35,'share','Share bought successfully.','36000','2025-08-27 16:27:37','2025-08-27 16:27:37'),(74,9,'App\\Models\\UserSharePayment',14,'payment','You received a payment from Danny','36000','2025-08-27 16:28:14','2025-08-27 16:28:14'),(75,8,'App\\Models\\UserSharePayment',14,'payment','You made a payment for maddyPower','36000','2025-08-27 16:28:14','2025-08-27 16:28:14'),(76,9,'App\\Models\\UserSharePayment',14,'payment','You confirmed a payment from Danny','36000','2025-08-27 16:28:38','2025-08-27 16:28:38'),(77,8,'App\\Models\\UserSharePayment',14,'payment','Your payment is confirmed by maddyPower','36000','2025-08-27 16:28:38','2025-08-27 16:28:38'),(78,8,'App\\Models\\UserShare',36,'share','Share bought successfully.','9000','2025-08-27 16:29:27','2025-08-27 16:29:27'),(79,9,'App\\Models\\UserSharePayment',15,'payment','You received a payment from Danny','9000','2025-08-27 16:29:58','2025-08-27 16:29:58'),(80,8,'App\\Models\\UserSharePayment',15,'payment','You made a payment for maddyPower','9000','2025-08-27 16:29:58','2025-08-27 16:29:58'),(81,9,'App\\Models\\UserSharePayment',15,'payment','You declined a payment from Danny','9000','2025-08-27 16:43:55','2025-08-27 16:43:55'),(82,8,'App\\Models\\UserSharePayment',15,'payment','Your payment was declined by maddyPower','9000','2025-08-27 16:43:55','2025-08-27 16:43:55'),(83,9,'App\\Models\\UserSharePayment',15,'payment','You declined a payment from Danny (second chance given)','9000','2025-08-27 16:54:02','2025-08-27 16:54:02'),(84,8,'App\\Models\\UserSharePayment',15,'payment','Your payment was declined by maddyPower. Please verify and reconfirm your payment.','9000','2025-08-27 16:54:02','2025-08-27 16:54:02'),(85,9,'App\\Models\\UserSharePayment',15,'payment','You confirmed a payment from Danny','9000','2025-08-27 17:10:20','2025-08-27 17:10:20'),(86,8,'App\\Models\\UserSharePayment',15,'payment','Your payment is confirmed by maddyPower','9000','2025-08-27 17:10:20','2025-08-27 17:10:20'),(87,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-28 07:58:03','2025-08-28 07:58:03'),(88,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-28 08:00:15','2025-08-28 08:00:15'),(89,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-28 10:31:28','2025-08-28 10:31:28'),(90,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-28 10:36:28','2025-08-28 10:36:28'),(91,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-28 10:36:59','2025-08-28 10:36:59'),(92,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-28 10:55:31','2025-08-28 10:55:31'),(93,9,'App\\Models\\UserShare',40,'share','Share bought successfully.','5000','2025-08-28 11:04:12','2025-08-28 11:04:12'),(94,8,'App\\Models\\UserSharePayment',16,'payment','You received a payment from maddyPower','5000','2025-08-28 11:05:19','2025-08-28 11:05:19'),(95,9,'App\\Models\\UserSharePayment',16,'payment','You made a payment for Danny','5000','2025-08-28 11:05:19','2025-08-28 11:05:19'),(96,8,'App\\Models\\UserSharePayment',17,'payment','You received a payment from maddyPower','5000','2025-08-28 11:08:17','2025-08-28 11:08:17'),(97,9,'App\\Models\\UserSharePayment',17,'payment','You made a payment for Danny','5000','2025-08-28 11:08:17','2025-08-28 11:08:17'),(98,8,'App\\Models\\UserSharePayment',17,'payment','You confirmed a payment from maddyPower','5000','2025-08-28 11:09:39','2025-08-28 11:09:39'),(99,9,'App\\Models\\UserSharePayment',17,'payment','Your payment is confirmed by Danny','5000','2025-08-28 11:09:39','2025-08-28 11:09:39'),(100,9,'App\\Models\\UserShare',41,'share','Share bought successfully.','4500','2025-08-28 11:16:06','2025-08-28 11:16:06'),(101,8,'App\\Models\\UserSharePayment',18,'payment','You received a payment from maddyPower','4500','2025-08-28 11:16:18','2025-08-28 11:16:18'),(102,9,'App\\Models\\UserSharePayment',18,'payment','You made a payment for Danny','4500','2025-08-28 11:16:18','2025-08-28 11:16:18'),(103,8,'App\\Models\\UserSharePayment',18,'payment','You confirmed a payment from maddyPower','4500','2025-08-28 11:17:03','2025-08-28 11:17:03'),(104,9,'App\\Models\\UserSharePayment',18,'payment','Your payment is confirmed by Danny','4500','2025-08-28 11:17:03','2025-08-28 11:17:03'),(105,8,'App\\Models\\UserShare',42,'share','Share bought successfully.','30000','2025-08-28 11:17:51','2025-08-28 11:17:51'),(106,9,'App\\Models\\UserSharePayment',19,'payment','You received a payment from Danny','30000','2025-08-28 11:18:07','2025-08-28 11:18:07'),(107,8,'App\\Models\\UserSharePayment',19,'payment','You made a payment for maddyPower','30000','2025-08-28 11:18:07','2025-08-28 11:18:07'),(108,9,'App\\Models\\UserSharePayment',19,'payment','You confirmed a payment from Danny','30000','2025-08-28 11:18:39','2025-08-28 11:18:39'),(109,8,'App\\Models\\UserSharePayment',19,'payment','Your payment is confirmed by maddyPower','30000','2025-08-28 11:18:39','2025-08-28 11:18:39'),(110,8,'App\\Models\\UserShare',43,'share','Share bought successfully.','3000','2025-08-28 11:19:11','2025-08-28 11:19:11'),(111,9,'App\\Models\\UserSharePayment',20,'payment','You received a payment from Danny','3000','2025-08-28 11:19:44','2025-08-28 11:19:44'),(112,8,'App\\Models\\UserSharePayment',20,'payment','You made a payment for maddyPower','3000','2025-08-28 11:19:44','2025-08-28 11:19:44'),(113,9,'App\\Models\\UserSharePayment',20,'payment','You confirmed a payment from Danny','3000','2025-08-28 11:20:09','2025-08-28 11:20:09'),(114,8,'App\\Models\\UserSharePayment',20,'payment','Your payment is confirmed by maddyPower','3000','2025-08-28 11:20:09','2025-08-28 11:20:09'),(115,9,'App\\Models\\UserShare',44,'share','Share bought successfully.','1587','2025-08-28 11:30:00','2025-08-28 11:30:00'),(116,9,'App\\Models\\UserShare',45,'share','Share bought successfully.','1913','2025-08-28 12:25:36','2025-08-28 12:25:36'),(117,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-28 12:36:30','2025-08-28 12:36:30'),(118,8,'App\\Models\\UserSharePayment',21,'payment','You received a payment from maddyPower','1913','2025-08-28 12:46:07','2025-08-28 12:46:07'),(119,9,'App\\Models\\UserSharePayment',21,'payment','You made a payment for Danny','1913','2025-08-28 12:46:07','2025-08-28 12:46:07'),(120,8,'App\\Models\\UserSharePayment',22,'payment','You received a payment from maddyPower','1913','2025-08-28 12:48:09','2025-08-28 12:48:09'),(121,9,'App\\Models\\UserSharePayment',22,'payment','You made a payment for Danny','1913','2025-08-28 12:48:09','2025-08-28 12:48:09'),(122,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-28 12:48:47','2025-08-28 12:48:47'),(123,8,'App\\Models\\UserSharePayment',22,'payment','You confirmed a payment from maddyPower','1913','2025-08-28 12:49:02','2025-08-28 12:49:02'),(124,9,'App\\Models\\UserSharePayment',22,'payment','Your payment is confirmed by Danny','1913','2025-08-28 12:49:02','2025-08-28 12:49:02'),(125,8,'App\\Models\\UserShare',46,'share','Share bought successfully.','2000','2025-08-28 12:50:52','2025-08-28 12:50:52'),(126,9,'App\\Models\\UserSharePayment',23,'payment','You received a payment from Danny','2000','2025-08-28 12:58:43','2025-08-28 12:58:43'),(127,8,'App\\Models\\UserSharePayment',23,'payment','You made a payment for maddyPower','2000','2025-08-28 12:58:43','2025-08-28 12:58:43'),(128,9,'App\\Models\\UserSharePayment',23,'payment','You confirmed a payment from Danny','2000','2025-08-28 12:59:22','2025-08-28 12:59:22'),(129,8,'App\\Models\\UserSharePayment',23,'payment','Your payment is confirmed by maddyPower','2000','2025-08-28 12:59:22','2025-08-28 12:59:22'),(130,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-28 13:00:02','2025-08-28 13:00:02'),(131,9,'App\\Models\\UserShare',47,'share','Share bought successfully.','1000','2025-08-28 13:56:04','2025-08-28 13:56:04'),(132,9,'App\\Models\\UserShare',48,'share','Share bought successfully.','4000','2025-08-28 13:58:41','2025-08-28 13:58:41'),(133,8,'App\\Models\\UserShare',49,'share','Share bought successfully.','9000','2025-08-28 14:04:04','2025-08-28 14:04:04'),(134,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-28 14:37:08','2025-08-28 14:37:08'),(135,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-28 15:25:04','2025-08-28 15:25:04'),(136,8,'App\\Models\\User',8,'admin_update','User information updated by admin: Auto Bidder','0','2025-08-28 15:37:59','2025-08-28 15:37:59'),(137,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-08-28 15:39:39','2025-08-28 15:39:39'),(138,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-28 15:40:15','2025-08-28 15:40:15'),(139,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-29 05:54:01','2025-08-29 05:54:01'),(140,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-29 06:11:44','2025-08-29 06:11:44'),(141,9,'App\\Models\\UserShare',3134,'share','Share bought successfully.','6000','2025-08-29 06:12:08','2025-08-29 06:12:08'),(142,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-08-29 06:15:16','2025-08-29 06:15:16'),(143,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-29 07:21:44','2025-08-29 07:21:44'),(144,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-08-29 07:25:08','2025-08-29 07:25:08');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `markets`
--

DROP TABLE IF EXISTS `markets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `markets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `markets`
--

LOCK TABLES `markets` WRITE;
/*!40000 ALTER TABLE `markets` DISABLE KEYS */;
/*!40000 ALTER TABLE `markets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_reads`
--

DROP TABLE IF EXISTS `message_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_reads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_reads_message_id_user_id_unique` (`message_id`,`user_id`),
  KEY `message_reads_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `message_reads_message_id_index` (`message_id`),
  CONSTRAINT `message_reads_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_reads`
--

LOCK TABLES `message_reads` WRITE;
/*!40000 ALTER TABLE `message_reads` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `type` enum('text','file','image') NOT NULL DEFAULT 'text',
  `file_path` varchar(191) DEFAULT NULL,
  `file_name` varchar(191) DEFAULT NULL,
  `is_system_message` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  KEY `messages_sender_id_index` (`sender_id`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2022_10_24_105424_create_permission_tables',1),(6,'2023_06_01_085240_create_add_module_name_column_on_permissions_table',1),(7,'2023_06_12_182346_create_policies_table',1),(8,'2023_06_18_105100_create_announcements_table',1),(9,'2023_06_21_183026_create_trades_table',1),(10,'2023_06_22_161631_create_trade_periods_table',1),(11,'2023_06_23_052825_create_add_image_and_video_url_column_on_announcements_table',1),(12,'2023_06_30_172833_create_user_shares_table',1),(13,'2023_06_30_174445_create_user_share_payments_table',1),(14,'2023_07_06_180656_create_add_sold_quantity_column_on_user_shares_table',1),(15,'2023_07_11_182433_create_user_share_pairs_table',1),(16,'2023_07_15_132401_create_add_is_ready_to_sell_column_on_user_shares_table',1),(17,'2023_07_20_014915_create_notifications_table',1),(18,'2023_07_23_205429_create_add_is_paid_column_on_user_share_pairs',1),(19,'2023_07_30_142139_create_add_balance_column_on_users_table',1),(20,'2023_08_10_005453_create_allocate_share_histories_table',1),(21,'2023_08_10_010620_create_add_get_from_column_on_user_shares_table',1),(22,'2023_08_10_023417_create_user_profit_histories_table',1),(23,'2023_08_23_003934_create_add_profit_share_column_on_user_shares_table',1),(24,'2023_08_28_214957_create_add_matured_at_column_on_user_shares_table',1),(25,'2023_09_12_105526_create_logs_table',1),(26,'2025_08_23_193532_add_suspension_until_to_users_table',1),(27,'2025_08_24_000001_add_sold_status_to_user_shares_table',1),(28,'2025_08_24_000002_create_user_payment_failures_table',1),(29,'2025_08_24_000003_add_timer_pause_fields_to_user_shares_table',1),(30,'2025_08_24_021220_create_conversations_table',1),(31,'2025_08_24_021237_create_messages_table',1),(32,'2025_08_24_021252_create_message_reads_table',1),(33,'2025_08_24_022658_create_chat_settings_table',1),(34,'2025_08_24_114039_add_payment_deadline_minutes_to_user_shares_table',1),(35,'2025_08_26_100000_add_suspension_level_to_user_payment_failures_table',1),(36,'2025_08_26_100001_add_suspension_fields_to_user_shares_table',1),(37,'2025_08_26_155429_add_missing_columns_to_users_table',1),(38,'2025_08_26_160056_create_general_settings_table',1),(39,'2025_08_26_165646_create_markets_table',1),(41,'2025_08_27_085332_create_invoices_table',2),(42,'2025_08_27_104421_update_user_status_enum_to_standardized_values',2),(43,'2025_08_27_104845_update_existing_user_statuses_to_standardized_values',3),(44,'2025_08_27_080600_fix_share_pairing_consistency',4),(45,'2025_08_27_171918_add_by_admin_column_to_user_share_payments_table',5),(46,'2025_08_27_172302_fix_user_share_payments_foreign_key_constraint',6),(47,'2025_08_27_164924_add_decline_attempts_to_user_share_pairs_table',7),(48,'2025_08_28_180017_add_suspension_reason_to_users_table',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(191) NOT NULL,
  `notifiable_type` varchar(191) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('1fac48ad-5954-461e-9057-1b5f18a95686','App\\Notifications\\PaymentApproved','App\\Models\\User',9,'{\"for\":\"payment-received\",\"heading\":\"Your payment has been confirmed\",\"payment_id\":4}','2025-08-28 08:00:35','2025-08-27 10:32:43','2025-08-28 08:00:35'),('3e761aab-2b72-46c9-9d2b-ac584fbac840','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',8,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":4}',NULL,'2025-08-27 10:32:04','2025-08-27 10:32:04'),('3f073c8e-d662-430d-903e-a602700db467','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',9,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":11}','2025-08-28 08:00:35','2025-08-27 14:36:55','2025-08-28 08:00:35'),('40565ebb-4b71-47cb-a9d3-cc3e7a7b81d7','App\\Notifications\\PaymentApproved','App\\Models\\User',9,'{\"for\":\"payment-received\",\"heading\":\"Your payment has been confirmed\",\"payment_id\":12}','2025-08-28 08:00:35','2025-08-27 15:53:09','2025-08-28 08:00:35'),('492e91d4-9258-4585-aac1-2fcca6c4ad90','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',8,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":1}',NULL,'2025-08-27 10:31:44','2025-08-27 10:31:44'),('4c1b5f56-53ad-457b-919f-56b4cf1dced1','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',8,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":3}',NULL,'2025-08-27 10:31:59','2025-08-27 10:31:59'),('63019936-83fb-47bc-aa80-d9ff95b31a02','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',9,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":14}','2025-08-28 08:00:35','2025-08-27 16:28:19','2025-08-28 08:00:35'),('6d678ccd-24fe-48c4-9daf-cbf036a0cf06','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',8,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":2}',NULL,'2025-08-27 10:31:53','2025-08-27 10:31:53'),('7cbdd3c8-e4b0-4d3c-a8b8-2fb490ebabfa','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',8,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":13}',NULL,'2025-08-27 15:58:22','2025-08-27 15:58:22'),('7d24e2fa-e2bb-4588-b2b1-18a975e1b5e5','App\\Notifications\\PaymentApproved','App\\Models\\User',9,'{\"for\":\"payment-received\",\"heading\":\"Your payment has been confirmed\",\"payment_id\":13}','2025-08-28 08:00:35','2025-08-27 16:21:56','2025-08-28 08:00:35'),('830d2461-2472-423a-a19b-f3fbd390025c','App\\Notifications\\PaymentApproved','App\\Models\\User',8,'{\"for\":\"payment-received\",\"heading\":\"Your payment has been confirmed\",\"payment_id\":10}',NULL,'2025-08-27 14:25:35','2025-08-27 14:25:35'),('8dda18e0-c70b-4faf-8d3e-7d103a6ef583','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',8,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":12}',NULL,'2025-08-27 15:52:41','2025-08-27 15:52:41'),('966ccda9-9499-495a-9b8c-f3a52cb4e6c4','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',9,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":10}','2025-08-28 08:00:35','2025-08-27 14:24:45','2025-08-28 08:00:35'),('ad2c632b-5cbc-4b87-a2c0-652d05d0ea0e','App\\Notifications\\PaymentApproved','App\\Models\\User',8,'{\"for\":\"payment-received\",\"heading\":\"Your payment has been confirmed\",\"payment_id\":11}',NULL,'2025-08-27 14:37:25','2025-08-27 14:37:25'),('d9c6934c-f13c-42d1-9381-0ab01773bd70','App\\Notifications\\PaymentSentToSeller','App\\Models\\User',9,'{\"for\":\"payment-sent\",\"heading\":\"You have received a payment\",\"payment_id\":15}','2025-08-28 08:00:35','2025-08-27 16:30:04','2025-08-28 08:00:35'),('ec30882d-ec8a-4c5d-a32c-3e34742c95e2','App\\Notifications\\PaymentApproved','App\\Models\\User',8,'{\"for\":\"payment-received\",\"heading\":\"Your payment has been confirmed\",\"payment_id\":14}',NULL,'2025-08-27 16:28:38','2025-08-27 16:28:38'),('ecf7a344-49d4-4611-8593-c0671db43ef1','App\\Notifications\\PaymentApproved','App\\Models\\User',8,'{\"for\":\"payment-received\",\"heading\":\"Your payment has been confirmed\",\"payment_id\":15}',NULL,'2025-08-27 17:10:20','2025-08-27 17:10:20'),('fa877351-f637-4d68-9f77-5f369b21cba9','App\\Notifications\\PaymentDeclined','App\\Models\\User',8,'{\"type\":\"payment_declined\",\"payment_id\":15,\"user_share_id\":36,\"user_share_pair_id\":22,\"amount\":9000,\"txs_id\":\"ygweshgsd\",\"is_second_chance\":true,\"decline_reason\":null,\"message\":\"Your payment of KSH 9,000.00 has been declined. Please verify your payment details and try again. This is your second chance to confirm the payment.\",\"action_url\":\"http:\\/\\/127.0.0.1:8001\\/bought-shares\\/view\\/36\"}',NULL,'2025-08-27 16:54:02','2025-08-27 16:54:02');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `module_name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view-analytic','dashboard','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(2,'view-share-pending-confirmation','dashboard','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(3,'accept-share-pending-confirmation','dashboard','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(4,'pending-payment-confirmation-index','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(5,'pending-payment-confirmation-view','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(6,'pending-payment-confirmation-approve','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(7,'pending-payment-confirmation-decline','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(8,'role-index','role-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(9,'role-create','role-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(10,'role-edit','role-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(11,'role-update','role-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(12,'role-delete','role-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(13,'permission-edit','role-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(14,'announcement-index','announcement','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(15,'announcement-create','announcement','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(16,'announcement-edit','announcement','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(17,'announcement-update','announcement','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(18,'announcement-delete','announcement','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(19,'staff-index','staff','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(20,'staff-create','staff','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(21,'staff-edit','staff','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(22,'staff-update','staff','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(23,'staff-delete','staff','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(24,'customer-index','customer','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(25,'customer-view','customer','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(26,'customer-update','customer','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(27,'allocate-share-to-user','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(28,'allocate-share-to-user-history','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(29,'allocate-share-to-user-history-delete','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(30,'transfer-share-from-user','share-management','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(31,'send-email','email','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(32,'send-sms','sms','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(33,'support-index','support','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(34,'support-view','support','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(35,'trade-index','trade','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(36,'trade-create','trade','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(37,'trade-edit','trade','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(38,'trade-update','trade','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(39,'trade-delete','trade','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(40,'trade-periods-index','trade-periods','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(41,'trade-periods-create','trade-periods','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(42,'trade-periods-edit','trade-periods','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(43,'trade-periods-update','trade-periods','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(44,'trade-periods-delete','trade-periods','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(45,'how-it-work-page-view','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(46,'how-it-work-page-update','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(47,'term-and-condition-page-view','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(48,'term-and-condition-page-update','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(49,'privacy-policy-page-view','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(50,'privacy-policy-page-update','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(51,'confidentiality-policy-page-view','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(52,'confidentiality-policy-page-update','frontend-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(53,'general-setting-view','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(54,'general-setting-update','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(55,'set-min-max-trading-amount-view','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(56,'set-min-max-trading-amount-update','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(57,'set-income-tax-rate-view','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(58,'set-income-tax-rate-update','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(59,'sms-api-page-view','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(60,'sms-api-page-update','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(61,'email-api-page-view','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(62,'email-api-page-update','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(63,'payments-api-page-view','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(64,'payments-api-page-update','general-setting','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(65,'market-index','market','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(66,'market-create','market','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(67,'market-edit','market','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(68,'market-update','market','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(69,'market-delete','market','web','2025-08-27 06:30:03','2025-08-27 06:30:03');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policies`
--

DROP TABLE IF EXISTS `policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `policies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `heading_one` varchar(191) DEFAULT NULL,
  `content_one` longtext DEFAULT NULL,
  `heading_two` varchar(191) DEFAULT NULL,
  `content_two` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policies`
--

LOCK TABLES `policies` WRITE;
/*!40000 ALTER TABLE `policies` DISABLE KEYS */;
INSERT INTO `policies` VALUES (1,'How it works','how-it-work','How To Bid (Buy Shares)','','How Do We Benefit From All These??','How Do We Benefit From All These??','2025-08-27 06:19:34','2025-08-27 06:19:34');
/*!40000 ALTER TABLE `policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Admin','web','2025-08-27 06:30:03','2025-08-27 06:30:03');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trade_periods`
--

DROP TABLE IF EXISTS `trade_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trade_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `days` int(11) NOT NULL,
  `percentage` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 => Active, 2 => Inactive',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trade_periods`
--

LOCK TABLES `trade_periods` WRITE;
/*!40000 ALTER TABLE `trade_periods` DISABLE KEYS */;
INSERT INTO `trade_periods` VALUES (1,3,30,1,NULL,'2025-08-27 06:32:03','2025-08-27 06:32:03'),(2,6,60,1,NULL,'2025-08-27 06:32:15','2025-08-27 06:32:15'),(3,9,90,1,NULL,'2025-08-27 06:32:28','2025-08-27 06:32:28');
/*!40000 ALTER TABLE `trade_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trades`
--

DROP TABLE IF EXISTS `trades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `quantity` bigint(20) NOT NULL DEFAULT 0,
  `price` double(15,2) NOT NULL DEFAULT 0.00,
  `buying_price` double(15,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 => Active, 2 => Inactive',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trades`
--

LOCK TABLES `trades` WRITE;
/*!40000 ALTER TABLE `trades` DISABLE KEYS */;
INSERT INTO `trades` VALUES (1,'Safaricom Shares','safaricom-shares',0,1.00,0.00,1,NULL,'2025-08-27 06:31:03','2025-08-27 06:31:03'),(2,'Airtel Shares','airtel-shares',0,1.00,0.00,1,NULL,'2025-08-27 06:31:15','2025-08-27 06:31:15'),(3,'KCB Shares','kcb-shares',0,1.00,0.00,1,NULL,'2025-08-27 06:31:24','2025-08-27 06:31:24'),(4,'Equity Bank Shares','equity-bank-shares',0,1.00,0.00,1,NULL,'2025-08-27 06:31:36','2025-08-27 06:31:36');
/*!40000 ALTER TABLE `trades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_payment_failures`
--

DROP TABLE IF EXISTS `user_payment_failures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_payment_failures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `consecutive_failures` int(11) NOT NULL DEFAULT 0,
  `suspension_level` int(11) NOT NULL DEFAULT 0,
  `last_failure_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspension_duration_hours` int(11) DEFAULT NULL,
  `suspension_lifted_at` timestamp NULL DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_payment_failures_user_id_consecutive_failures_index` (`user_id`,`consecutive_failures`),
  KEY `user_payment_failures_user_id_suspension_level_index` (`user_id`,`suspension_level`),
  CONSTRAINT `user_payment_failures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_payment_failures`
--

LOCK TABLES `user_payment_failures` WRITE;
/*!40000 ALTER TABLE `user_payment_failures` DISABLE KEYS */;
INSERT INTO `user_payment_failures` VALUES (2,8,0,1,NULL,'2025-08-28 15:22:48',6,NULL,NULL,'2025-08-28 15:22:48','2025-08-28 15:22:48');
/*!40000 ALTER TABLE `user_payment_failures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profit_histories`
--

DROP TABLE IF EXISTS `user_profit_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_profit_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_share_id` bigint(20) unsigned NOT NULL,
  `shares` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_profit_histories_user_share_id_foreign` (`user_share_id`),
  CONSTRAINT `user_profit_histories_user_share_id_foreign` FOREIGN KEY (`user_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profit_histories`
--

LOCK TABLES `user_profit_histories` WRITE;
/*!40000 ALTER TABLE `user_profit_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_profit_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_share_pairs`
--

DROP TABLE IF EXISTS `user_share_pairs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_share_pairs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `user_share_id` bigint(20) unsigned NOT NULL,
  `paired_user_share_id` int(11) NOT NULL,
  `share` int(11) NOT NULL DEFAULT 0,
  `is_paid` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Here 0 = unpaid, 1 = paid',
  `decline_attempts` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Number of times payment has been declined for this pair',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_share_pair` (`user_share_id`,`paired_user_share_id`),
  KEY `user_share_pairs_user_id_foreign` (`user_id`),
  KEY `idx_payment_status_created` (`is_paid`,`created_at`),
  CONSTRAINT `user_share_pairs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_share_pairs_user_share_id_foreign` FOREIGN KEY (`user_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_share_pairs`
--

LOCK TABLES `user_share_pairs` WRITE;
/*!40000 ALTER TABLE `user_share_pairs` DISABLE KEYS */;
INSERT INTO `user_share_pairs` VALUES (23,9,40,38,5000,1,0,'2025-08-28 11:04:12','2025-08-28 11:09:30'),(24,9,41,38,4500,1,0,'2025-08-28 11:16:06','2025-08-28 11:16:56'),(25,8,42,37,30000,1,0,'2025-08-28 11:17:51','2025-08-28 11:18:32'),(26,8,43,37,3000,1,0,'2025-08-28 11:19:11','2025-08-28 11:20:02'),(27,9,44,38,1587,2,0,'2025-08-28 11:30:00','2025-08-28 12:30:00'),(28,9,45,38,1913,1,0,'2025-08-28 12:25:36','2025-08-28 12:48:55'),(29,8,46,39,2000,1,0,'2025-08-28 12:50:52','2025-08-28 12:59:14'),(30,9,47,38,1000,2,0,'2025-08-28 13:56:04','2025-08-28 14:56:04'),(31,9,48,38,4000,2,0,'2025-08-28 13:58:41','2025-08-28 14:58:42'),(32,8,49,37,9000,2,0,'2025-08-28 14:04:04','2025-08-28 15:04:04'),(33,9,120,38,1000,1,0,'2025-08-28 15:39:11','2025-08-28 15:39:11'),(34,9,3134,38,6000,0,0,'2025-08-29 06:12:08','2025-08-29 06:12:08');
/*!40000 ALTER TABLE `user_share_pairs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_share_payments`
--

DROP TABLE IF EXISTS `user_share_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_share_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_share_id` bigint(20) unsigned NOT NULL,
  `user_share_pair_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `amount` double(15,2) NOT NULL DEFAULT 0.00,
  `name` varchar(191) DEFAULT NULL,
  `number` varchar(191) NOT NULL,
  `received_phone_no` varchar(191) NOT NULL,
  `txs_id` varchar(191) DEFAULT NULL,
  `file` text DEFAULT NULL,
  `status` enum('pending','paid','conformed','failed') NOT NULL DEFAULT 'pending',
  `by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `note_by_sender` text DEFAULT NULL,
  `note_by_receiver` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_share_payments_user_share_id_foreign` (`user_share_id`),
  KEY `user_share_payments_user_share_pair_id_foreign` (`user_share_pair_id`),
  CONSTRAINT `user_share_payments_user_share_id_foreign` FOREIGN KEY (`user_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_share_payments_user_share_pair_id_foreign` FOREIGN KEY (`user_share_pair_id`) REFERENCES `user_share_pairs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_share_payments`
--

LOCK TABLES `user_share_payments` WRITE;
/*!40000 ALTER TABLE `user_share_payments` DISABLE KEYS */;
INSERT INTO `user_share_payments` VALUES (16,40,23,8,9,5000.00,'Julius Njoroge','0715172652','0795001587','qhsjsww',NULL,'paid',0,NULL,NULL,'2025-08-28 11:05:19','2025-08-28 11:05:19'),(17,40,23,8,9,5000.00,'Julius Njoroge','0715172652','0795001587','qhsjsww',NULL,'conformed',0,NULL,'thank you','2025-08-28 11:08:17','2025-08-28 11:09:30'),(18,41,24,8,9,4500.00,'Julius Njoroge','0715172652','0795001587','55356',NULL,'conformed',0,NULL,NULL,'2025-08-28 11:16:18','2025-08-28 11:16:56'),(19,42,25,9,8,30000.00,'Daniel Wafula','0795001587','0715172652','yeyeey',NULL,'conformed',0,NULL,NULL,'2025-08-28 11:18:07','2025-08-28 11:18:32'),(20,43,26,9,8,3000.00,'Daniel Wafula','0795001587','0715172652','fgsajshg',NULL,'conformed',0,NULL,NULL,'2025-08-28 11:19:44','2025-08-28 11:20:02'),(21,45,28,8,9,1913.00,'Daniel Wafula','0795001587','0795001587',NULL,NULL,'paid',0,NULL,NULL,'2025-08-28 12:46:07','2025-08-28 12:46:07'),(22,45,28,8,9,1913.00,'Daniel Wafula','0795001587','0795001587',NULL,NULL,'conformed',0,NULL,NULL,'2025-08-28 12:48:09','2025-08-28 12:48:55'),(23,46,29,9,8,2000.00,'Julius Njoroge','0715172652','0715172652','sdffdd',NULL,'conformed',0,NULL,'wert','2025-08-28 12:58:43','2025-08-28 12:59:14');
/*!40000 ALTER TABLE `user_share_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_shares`
--

DROP TABLE IF EXISTS `user_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_shares` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trade_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `ticket_no` varchar(191) NOT NULL,
  `amount` double(15,2) NOT NULL DEFAULT 0.00,
  `balance` double(15,2) NOT NULL DEFAULT 0.00,
  `period` int(11) NOT NULL DEFAULT 0,
  `share_will_get` int(11) NOT NULL DEFAULT 0,
  `total_share_count` int(11) NOT NULL DEFAULT 0,
  `sold_quantity` int(11) NOT NULL DEFAULT 0,
  `hold_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','pairing','paired','completed','failed','sold') DEFAULT 'pending',
  `status_before_suspension` varchar(191) DEFAULT NULL,
  `get_from` varchar(191) NOT NULL DEFAULT 'purchase',
  `is_ready_to_sell` tinyint(4) NOT NULL DEFAULT 0,
  `is_sold` tinyint(4) NOT NULL DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `matured_at` datetime DEFAULT NULL,
  `timer_paused` tinyint(1) NOT NULL DEFAULT 0,
  `timer_paused_at` timestamp NULL DEFAULT NULL,
  `paused_duration_seconds` int(11) NOT NULL DEFAULT 0,
  `payment_deadline_minutes` int(11) NOT NULL DEFAULT 60 COMMENT 'Payment deadline in minutes stored when share is created (unaffected by admin config changes)',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `profit_share` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_shares_ticket_no_unique` (`ticket_no`),
  KEY `user_shares_trade_id_foreign` (`trade_id`),
  KEY `user_shares_user_id_foreign` (`user_id`),
  KEY `user_shares_status_user_id_index` (`status`,`user_id`),
  KEY `idx_status_start_date` (`status`,`start_date`),
  KEY `idx_status_ready_sell` (`status`,`is_ready_to_sell`),
  CONSTRAINT `user_shares_trade_id_foreign` FOREIGN KEY (`trade_id`) REFERENCES `trades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_shares_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_quantities` CHECK (`total_share_count` >= 0 and `hold_quantity` >= 0 and `sold_quantity` >= 0),
  CONSTRAINT `chk_user_share_status_fixed` CHECK (`status` in ('pending','pairing','paired','partially_paired','completed','failed','sold','suspended','running','active')),
  CONSTRAINT `chk_user_share_status` CHECK (`status` in ('pending','paired','failed','completed','suspended','running','partially_paired','active'))
) ENGINE=InnoDB AUTO_INCREMENT=8221 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_shares`
--

LOCK TABLES `user_shares` WRITE;
/*!40000 ALTER TABLE `user_shares` DISABLE KEYS */;
INSERT INTO `user_shares` VALUES (37,1,9,'AB-17563679106',50000.00,0.00,3,50000,17000,33000,0,'completed',NULL,'allocated-by-admin',1,0,'2025-08-28 10:58:30','2025-08-28 14:01:09',0,NULL,0,60,NULL,'2025-08-28 07:58:30','2025-08-28 15:04:04',0),(38,1,8,'AB-17563679216',30000.00,0.00,3,30000,11587,11413,6000,'completed',NULL,'allocated-by-admin',1,0,'2025-08-28 10:58:41','2025-08-28 14:01:09',0,NULL,0,60,NULL,'2025-08-28 07:58:41','2025-08-29 06:12:08',0),(39,1,9,'AB-17563785815',65000.00,0.00,3,65000,63000,2000,0,'completed',NULL,'allocated-by-admin',1,0,'2025-08-28 13:56:21','2025-08-28 14:01:09',0,NULL,0,60,NULL,'2025-08-28 10:56:21','2025-08-28 12:59:14',0),(40,1,9,'AB-17563790525403',5000.00,0.00,3,5000,5000,0,0,'completed',NULL,'purchase',0,0,'2025-08-28 14:09:30',NULL,1,'2025-08-28 15:22:48',0,60,NULL,'2025-08-28 11:04:12','2025-08-28 15:22:48',0),(41,1,9,'AB-17563797664759',4500.00,0.00,6,4500,4500,0,0,'completed',NULL,'purchase',0,0,'2025-08-28 14:16:56',NULL,1,'2025-08-28 15:22:48',0,60,NULL,'2025-08-28 11:16:06','2025-08-28 15:22:48',0),(42,1,8,'AB-17563798712472',30000.00,0.00,9,30000,30000,0,0,'completed',NULL,'purchase',0,0,'2025-08-28 14:18:32',NULL,1,'2025-08-28 15:22:48',0,60,NULL,'2025-08-28 11:17:51','2025-08-28 15:22:48',0),(43,1,8,'AB-17563799516940',3000.00,0.00,3,3000,3000,0,0,'completed',NULL,'purchase',0,0,'2025-08-28 14:20:02',NULL,1,'2025-08-28 15:22:48',0,60,NULL,'2025-08-28 11:19:11','2025-08-28 15:22:48',0),(44,1,9,'AB-17563806007856',1587.00,0.00,6,1587,0,0,0,'failed',NULL,'purchase',0,0,'2025-08-28 14:30:00',NULL,0,NULL,0,60,NULL,'2025-08-28 11:30:00','2025-08-28 12:30:00',0),(45,1,9,'AB-17563839362927',1913.00,0.00,9,1913,1913,0,0,'completed',NULL,'purchase',0,0,'2025-08-28 15:48:55',NULL,1,'2025-08-28 15:22:48',0,60,NULL,'2025-08-28 12:25:36','2025-08-28 15:22:48',0),(46,1,8,'AB-17563854522626',2000.00,0.00,9,2000,2000,0,0,'completed',NULL,'purchase',0,0,'2025-08-28 15:59:14',NULL,1,'2025-08-28 15:22:48',0,60,NULL,'2025-08-28 12:50:52','2025-08-28 15:22:48',0),(47,1,9,'AB-17563893648752',1000.00,0.00,6,1000,0,0,0,'failed',NULL,'purchase',0,0,'2025-08-28 16:56:04',NULL,0,NULL,0,60,NULL,'2025-08-28 13:56:04','2025-08-28 14:56:04',0),(48,1,9,'AB-17563895215662',4000.00,0.00,3,4000,0,0,0,'failed',NULL,'purchase',0,0,'2025-08-28 16:58:41',NULL,0,NULL,0,60,NULL,'2025-08-28 13:58:41','2025-08-28 14:58:41',0),(49,1,8,'AB-17563898444257',9000.00,0.00,3,9000,0,0,0,'failed',NULL,'purchase',0,0,'2025-08-28 17:04:04',NULL,0,NULL,0,60,NULL,'2025-08-28 14:04:04','2025-08-28 15:04:04',0),(120,1,9,'AB-17563955515',1000.00,0.00,3,1000,1000,0,0,'completed',NULL,'transferred-by-admin',1,0,'2025-08-25 18:39:11',NULL,0,NULL,0,60,NULL,'2025-08-28 15:39:11','2025-08-28 15:39:11',0),(3134,1,9,'AB-17564479284995',6000.00,0.00,3,6000,0,0,0,'paired',NULL,'purchase',0,0,'2025-08-29 09:12:08',NULL,0,NULL,0,60,NULL,'2025-08-29 06:12:08','2025-08-29 06:12:08',0);
/*!40000 ALTER TABLE `user_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 2,
  `email` varchar(191) NOT NULL,
  `username` varchar(191) NOT NULL,
  `phone` varchar(191) NOT NULL,
  `refferal_code` varchar(191) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `avatar` text NOT NULL,
  `business_account_id` enum('1','2') NOT NULL DEFAULT '1' COMMENT '1=mpesa,2=till',
  `business_profile` longtext DEFAULT NULL,
  `trade_id` int(11) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `balance` double(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','suspended','blocked') DEFAULT 'active',
  `ref_amount` int(11) NOT NULL DEFAULT 0,
  `mode` varchar(100) NOT NULL DEFAULT 'light',
  `block_until` timestamp NULL DEFAULT NULL,
  `suspension_until` timestamp NULL DEFAULT NULL,
  `suspension_reason` enum('manual','automatic','payment_failure') DEFAULT 'manual' COMMENT 'Reason for suspension: manual (by admin), automatic (system), payment_failure (payment issues)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Auto Bidder',1,'admin@autobidder.com','superadmin','03400000000',NULL,'2025-08-27 06:30:03','$2y$10$wJmn2RIr9k.64D0doRNl6OOsl.NufcD8BCK/htqxl0jBl820KmVzS','','1',NULL,NULL,NULL,'2025-08-27 06:19:33','2025-08-28 15:21:15',0.00,'active',0,'light',NULL,NULL,NULL),(8,'Daniel Wafula',2,'dw915499@gmail.com','Danny','0795001587','Danny','2025-08-27 06:20:13','$2y$10$EJkZZi8m8gXd8j8MMB21Me.BgY0zGGUf/dQImFgXXLTvnMjJIfxA2','','1','{\"mpesa_no\":\"0795001587\",\"mpesa_name\":\"Daniel Wafula\",\"mpesa_till_no\":\"\",\"mpesa_till_name\":\"\"}',NULL,NULL,'2025-08-27 06:19:43','2025-08-28 15:37:59',0.00,'active',0,'light',NULL,NULL,NULL),(9,'Maddy Power',2,'Richardbrandson2015@gmail.com','maddyPower','254715172652',NULL,'2025-08-27 06:41:53','$2y$10$EJkZZi8m8gXd8j8MMB21Me.BgY0zGGUf/dQImFgXXLTvnMjJIfxA2','images/1755789287.jpg','1','{\"mpesa_no\":\"0715172652\",\"mpesa_name\":\"Julius Njoroge\",\"mpesa_till_no\":\"5149535\",\"mpesa_till_name\":\"Blimpies Tasty Fries\"}',NULL,NULL,'2025-08-27 06:27:32','2025-08-29 06:35:36',0.00,'active',0,'light',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-29 10:54:52
