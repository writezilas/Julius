-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: u773742080_autobidder
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
  CONSTRAINT `allocate_share_histories_user_share_id_foreign` FOREIGN KEY (`user_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75990 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allocate_share_histories`
--

LOCK TABLES `allocate_share_histories` WRITE;
/*!40000 ALTER TABLE `allocate_share_histories` DISABLE KEYS */;
INSERT INTO `allocate_share_histories` VALUES (75986,76047,100000,1,'2025-09-21 16:15:42','2025-09-21 16:15:42'),(75987,76052,100,9,'2025-09-25 10:29:32','2025-09-25 10:29:32'),(75988,76055,50000,1,'2025-09-26 06:23:55','2025-09-26 06:23:55'),(75989,76056,50000,1,'2025-09-26 06:24:10','2025-09-26 06:24:10');
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
  `id` bigint(20) unsigned NOT NULL,
  `key` varchar(191) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_settings`
--

LOCK TABLES `general_settings` WRITE;
/*!40000 ALTER TABLE `general_settings` DISABLE KEYS */;
INSERT INTO `general_settings` VALUES (1,'min_trading_price','500','2025-08-27 06:33:00','2025-09-18 14:10:04'),(2,'max_trading_price','200000','2025-08-27 06:33:00','2025-09-21 15:54:19'),(3,'reffaral_bonus','100','2025-08-27 06:33:39','2025-09-26 06:26:27'),(4,'bought_time','1','2025-08-27 06:33:39','2025-09-21 03:35:04'),(5,'app_timezone','Africa/Nairobi','2025-08-27 06:33:39','2025-09-18 12:04:25');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,8,76052,9,0.00,100.00,100.00,'pending',NULL,'2025-09-25 10:29:32','2025-09-25 10:29:32');
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
) ENGINE=InnoDB AUTO_INCREMENT=542 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (457,9,'App\\Models\\UserShare',76034,'share','Share bought successfully.','3000','2025-09-21 04:26:43','2025-09-21 04:26:43'),(458,9,'App\\Models\\UserShare',76035,'share','Share bought successfully.','2000','2025-09-21 04:26:59','2025-09-21 04:26:59'),(459,8,'App\\Models\\UserSharePayment',61,'payment','You received a payment from maddyPower','3000','2025-09-21 04:27:20','2025-09-21 04:27:20'),(460,9,'App\\Models\\UserSharePayment',61,'payment','You made a payment for Danny','3000','2025-09-21 04:27:20','2025-09-21 04:27:20'),(461,8,'App\\Models\\UserSharePayment',61,'payment','You confirmed a payment from maddyPower','3000','2025-09-21 04:44:39','2025-09-21 04:44:39'),(462,9,'App\\Models\\UserSharePayment',61,'payment','Your payment is confirmed by Danny','3000','2025-09-21 04:44:39','2025-09-21 04:44:39'),(463,9,'App\\Models\\UserShare',76036,'share','Share bought successfully.','6000','2025-09-21 04:49:39','2025-09-21 04:49:39'),(464,9,'App\\Models\\UserShare',76037,'share','Share bought successfully.','1000','2025-09-21 04:49:51','2025-09-21 04:49:51'),(465,8,'App\\Models\\UserSharePayment',62,'payment','You received a payment from maddyPower','1000','2025-09-21 04:50:12','2025-09-21 04:50:12'),(466,9,'App\\Models\\UserSharePayment',62,'payment','You made a payment for Danny','1000','2025-09-21 04:50:12','2025-09-21 04:50:12'),(467,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-21 05:15:27','2025-09-21 05:15:27'),(468,9,'App\\Models\\UserShare',76038,'share','Share bought successfully.','6000','2025-09-21 05:22:28','2025-09-21 05:22:28'),(469,9,'App\\Models\\UserShare',76039,'share','Share bought successfully.','4000','2025-09-21 05:22:36','2025-09-21 05:22:36'),(470,8,'App\\Models\\UserSharePayment',63,'payment','You received a payment from maddyPower','6000','2025-09-21 05:22:57','2025-09-21 05:22:57'),(471,9,'App\\Models\\UserSharePayment',63,'payment','You made a payment for Danny','6000','2025-09-21 05:22:57','2025-09-21 05:22:57'),(472,8,'App\\Models\\UserSharePayment',62,'payment','You confirmed a payment from maddyPower','1000','2025-09-21 05:24:16','2025-09-21 05:24:16'),(473,9,'App\\Models\\UserSharePayment',62,'payment','Your payment is confirmed by Danny','1000','2025-09-21 05:24:16','2025-09-21 05:24:16'),(474,8,'App\\Models\\UserSharePayment',63,'payment','You confirmed a payment from maddyPower','6000','2025-09-21 05:25:54','2025-09-21 05:25:54'),(475,9,'App\\Models\\UserSharePayment',63,'payment','Your payment is confirmed by Danny','6000','2025-09-21 05:25:54','2025-09-21 05:25:54'),(476,14,'App\\Models\\User',14,'login','Login Successfully.','0','2025-09-21 06:04:22','2025-09-21 06:04:22'),(477,14,'App\\Models\\UserShare',76040,'share','Share bought successfully.','10000','2025-09-21 06:04:44','2025-09-21 06:04:44'),(478,8,'App\\Models\\UserSharePayment',64,'payment','You received a payment from johana33','10000','2025-09-21 06:04:54','2025-09-21 06:04:54'),(479,14,'App\\Models\\UserSharePayment',64,'payment','You made a payment for Danny','10000','2025-09-21 06:04:54','2025-09-21 06:04:54'),(480,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-09-21 06:05:14','2025-09-21 06:05:14'),(481,8,'App\\Models\\UserSharePayment',64,'payment','You confirmed a payment from johana33','10000','2025-09-21 06:05:31','2025-09-21 06:05:31'),(482,14,'App\\Models\\UserSharePayment',64,'payment','Your payment is confirmed by Danny','10000','2025-09-21 06:05:31','2025-09-21 06:05:31'),(483,14,'App\\Models\\UserShare',76041,'share','Share bought successfully.','5000','2025-09-21 06:07:50','2025-09-21 06:07:50'),(484,14,'App\\Models\\UserShare',76042,'share','Share bought successfully.','3000','2025-09-21 06:07:59','2025-09-21 06:07:59'),(485,8,'App\\Models\\UserSharePayment',65,'payment','You received a payment from johana33','5000','2025-09-21 06:08:13','2025-09-21 06:08:13'),(486,14,'App\\Models\\UserSharePayment',65,'payment','You made a payment for Danny','5000','2025-09-21 06:08:13','2025-09-21 06:08:13'),(487,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-09-21 06:11:41','2025-09-21 06:11:41'),(488,14,'App\\Models\\UserShare',76043,'share','Share bought successfully.','85000','2025-09-21 06:16:25','2025-09-21 06:16:25'),(489,8,'App\\Models\\UserSharePayment',66,'payment','You received a payment from johana33','75000','2025-09-21 06:16:38','2025-09-21 06:16:38'),(490,14,'App\\Models\\UserSharePayment',66,'payment','You made a payment for Danny','75000','2025-09-21 06:16:38','2025-09-21 06:16:38'),(491,8,'App\\Models\\UserSharePayment',66,'payment','You confirmed a payment from johana33','75000','2025-09-21 06:17:18','2025-09-21 06:17:18'),(492,14,'App\\Models\\UserSharePayment',66,'payment','Your payment is confirmed by Danny','75000','2025-09-21 06:17:18','2025-09-21 06:17:18'),(493,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-21 06:19:41','2025-09-21 06:19:41'),(494,14,'App\\Models\\User',14,'login','Login Successfully.','0','2025-09-21 15:46:40','2025-09-21 15:46:40'),(495,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-21 15:50:13','2025-09-21 15:50:13'),(496,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-09-21 15:51:10','2025-09-21 15:51:10'),(497,14,'App\\Models\\UserShare',76045,'share','Share bought successfully.','110000','2025-09-21 15:54:41','2025-09-21 15:54:41'),(498,14,'App\\Models\\UserShare',76046,'share','Share bought successfully.','110000','2025-09-21 15:56:34','2025-09-21 15:56:34'),(499,9,'App\\Models\\UserSharePayment',67,'payment','You received a payment from johana33','110000','2025-09-21 15:56:44','2025-09-21 15:56:44'),(500,14,'App\\Models\\UserSharePayment',67,'payment','You made a payment for maddyPower','110000','2025-09-21 15:56:44','2025-09-21 15:56:44'),(501,14,'App\\Models\\UserShare',76048,'share','Share bought successfully.','110000','2025-09-21 16:17:25','2025-09-21 16:17:25'),(502,14,'App\\Models\\UserShare',76049,'share','Share bought successfully.','110000','2025-09-21 16:23:25','2025-09-21 16:23:25'),(503,9,'App\\Models\\UserSharePayment',68,'payment','You received a payment from johana33','110000','2025-09-21 16:23:34','2025-09-21 16:23:34'),(504,14,'App\\Models\\UserSharePayment',68,'payment','You made a payment for maddyPower','110000','2025-09-21 16:23:34','2025-09-21 16:23:34'),(505,9,'App\\Models\\UserSharePayment',68,'payment','You confirmed a payment from johana33','110000','2025-09-21 16:55:12','2025-09-21 16:55:12'),(506,14,'App\\Models\\UserSharePayment',68,'payment','Your payment is confirmed by maddyPower','110000','2025-09-21 16:55:12','2025-09-21 16:55:12'),(507,9,'App\\Models\\UserShare',76050,'share','Share bought successfully.','40000','2025-09-21 17:11:35','2025-09-21 17:11:35'),(508,14,'App\\Models\\UserSharePayment',69,'payment','You received a payment from maddyPower','40000','2025-09-21 17:11:45','2025-09-21 17:11:45'),(509,9,'App\\Models\\UserSharePayment',69,'payment','You made a payment for johana33','40000','2025-09-21 17:11:45','2025-09-21 17:11:45'),(510,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-22 03:44:34','2025-09-22 03:44:34'),(511,14,'App\\Models\\User',14,'login','Login Successfully.','0','2025-09-22 03:47:28','2025-09-22 03:47:28'),(512,14,'App\\Models\\UserSharePayment',69,'payment','You confirmed a payment from maddyPower','40000','2025-09-22 03:58:36','2025-09-22 03:58:36'),(513,9,'App\\Models\\UserSharePayment',69,'payment','Your payment is confirmed by johana33','40000','2025-09-22 03:58:36','2025-09-22 03:58:36'),(514,9,'App\\Models\\UserShare',76051,'share','Share bought successfully.','81000','2025-09-22 04:00:50','2025-09-22 04:00:50'),(515,14,'App\\Models\\UserSharePayment',70,'payment','You received a payment from maddyPower','81000','2025-09-22 04:01:02','2025-09-22 04:01:02'),(516,9,'App\\Models\\UserSharePayment',70,'payment','You made a payment for johana33','81000','2025-09-22 04:01:02','2025-09-22 04:01:02'),(517,14,'App\\Models\\UserSharePayment',70,'payment','You confirmed a payment from maddyPower','81000','2025-09-22 04:20:31','2025-09-22 04:20:31'),(518,9,'App\\Models\\UserSharePayment',70,'payment','Your payment is confirmed by johana33','81000','2025-09-22 04:20:31','2025-09-22 04:20:31'),(519,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-09-22 05:18:33','2025-09-22 05:18:33'),(520,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-09-22 07:16:07','2025-09-22 07:16:07'),(521,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-25 09:52:26','2025-09-25 09:52:26'),(522,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-09-25 09:54:58','2025-09-25 09:54:58'),(523,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-09-25 10:13:30','2025-09-25 10:13:30'),(524,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-25 10:19:52','2025-09-25 10:19:52'),(525,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-09-25 10:28:47','2025-09-25 10:28:47'),(526,9,'App\\Models\\User',9,'referral_setup','RETROACTIVE: Referral bonus created for referrer Danny. Referred user will earn KSH 100 when referrer sells bonus shares.','100','2025-09-25 10:29:32','2025-09-25 10:29:32'),(527,8,'App\\Models\\User',8,'referral_bonus_received','RETROACTIVE: Received 100 referral bonus shares for referring maddyPower. Shares are ready to sell.','100','2025-09-25 10:29:32','2025-09-25 10:29:32'),(528,8,'App\\Models\\UserShare',76053,'share','Share bought successfully.','121000','2025-09-25 11:29:08','2025-09-25 11:29:08'),(529,9,'App\\Models\\UserSharePayment',71,'payment','You received a payment from Danny','40000','2025-09-25 11:29:22','2025-09-25 11:29:22'),(530,8,'App\\Models\\UserSharePayment',71,'payment','You made a payment for maddyPower','40000','2025-09-25 11:29:22','2025-09-25 11:29:22'),(531,9,'App\\Models\\UserSharePayment',72,'payment','You received a payment from Danny','81000','2025-09-25 11:29:30','2025-09-25 11:29:30'),(532,8,'App\\Models\\UserSharePayment',72,'payment','You made a payment for maddyPower','81000','2025-09-25 11:29:30','2025-09-25 11:29:30'),(533,9,'App\\Models\\UserSharePayment',71,'payment','You confirmed a payment from Danny','40000','2025-09-25 11:30:19','2025-09-25 11:30:19'),(534,8,'App\\Models\\UserSharePayment',71,'payment','Your payment is confirmed by maddyPower','40000','2025-09-25 11:30:19','2025-09-25 11:30:19'),(535,9,'App\\Models\\UserSharePayment',72,'payment','You confirmed a payment from Danny','81000','2025-09-25 11:30:58','2025-09-25 11:30:58'),(536,8,'App\\Models\\UserSharePayment',72,'payment','Your payment is confirmed by maddyPower','81000','2025-09-25 11:30:58','2025-09-25 11:30:58'),(537,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-25 12:20:44','2025-09-25 12:20:44'),(538,8,'App\\Models\\User',8,'login','Login Successfully.','0','2025-09-26 04:43:17','2025-09-26 04:43:17'),(539,1,'App\\Models\\User',1,'login','Login Successfully.','0','2025-09-26 05:05:58','2025-09-26 05:05:58'),(540,14,'App\\Models\\User',14,'login','Login Successfully.','0','2025-09-26 06:01:16','2025-09-26 06:01:16'),(541,9,'App\\Models\\User',9,'login','Login Successfully.','0','2025-09-26 06:31:36','2025-09-26 06:31:36');
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
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `markets`
--

LOCK TABLES `markets` WRITE;
/*!40000 ALTER TABLE `markets` DISABLE KEYS */;
INSERT INTO `markets` VALUES (1,'08:30:00','09:00:00',0,'2025-09-18 12:07:59','2025-09-26 06:01:29'),(2,'13:30:00','14:00:00',0,'2025-09-18 15:12:43','2025-09-26 06:22:44'),(3,'19:30:00','20:00:00',0,'2025-09-18 15:13:04','2025-09-26 06:01:32');
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
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2022_10_24_105424_create_permission_tables',1),(6,'2023_06_01_085240_create_add_module_name_column_on_permissions_table',1),(7,'2023_06_12_182346_create_policies_table',1),(8,'2023_06_18_105100_create_announcements_table',1),(9,'2023_06_21_183026_create_trades_table',1),(10,'2023_06_22_161631_create_trade_periods_table',1),(11,'2023_06_23_052825_create_add_image_and_video_url_column_on_announcements_table',1),(12,'2023_06_30_172833_create_user_shares_table',1),(13,'2023_06_30_174445_create_user_share_payments_table',1),(14,'2023_07_06_180656_create_add_sold_quantity_column_on_user_shares_table',1),(15,'2023_07_11_182433_create_user_share_pairs_table',1),(16,'2023_07_15_132401_create_add_is_ready_to_sell_column_on_user_shares_table',1),(17,'2023_07_20_014915_create_notifications_table',1),(18,'2023_07_23_205429_create_add_is_paid_column_on_user_share_pairs',1),(19,'2023_07_30_142139_create_add_balance_column_on_users_table',1),(20,'2023_08_10_005453_create_allocate_share_histories_table',1),(21,'2023_08_10_010620_create_add_get_from_column_on_user_shares_table',1),(22,'2023_08_10_023417_create_user_profit_histories_table',1),(23,'2023_08_23_003934_create_add_profit_share_column_on_user_shares_table',1),(24,'2023_08_28_214957_create_add_matured_at_column_on_user_shares_table',1),(25,'2023_09_12_105526_create_logs_table',1),(26,'2025_08_23_193532_add_suspension_until_to_users_table',1),(27,'2025_08_24_000001_add_sold_status_to_user_shares_table',1),(28,'2025_08_24_000002_create_user_payment_failures_table',1),(29,'2025_08_24_000003_add_timer_pause_fields_to_user_shares_table',1),(30,'2025_08_24_021220_create_conversations_table',1),(31,'2025_08_24_021237_create_messages_table',1),(32,'2025_08_24_021252_create_message_reads_table',1),(33,'2025_08_24_022658_create_chat_settings_table',1),(34,'2025_08_24_114039_add_payment_deadline_minutes_to_user_shares_table',1),(35,'2025_08_26_100000_add_suspension_level_to_user_payment_failures_table',1),(36,'2025_08_26_100001_add_suspension_fields_to_user_shares_table',1),(37,'2025_08_26_155429_add_missing_columns_to_users_table',1),(38,'2025_08_26_160056_create_general_settings_table',1),(39,'2025_08_26_165646_create_markets_table',1),(41,'2025_08_27_085332_create_invoices_table',2),(42,'2025_08_27_104421_update_user_status_enum_to_standardized_values',2),(43,'2025_08_27_104845_update_existing_user_statuses_to_standardized_values',3),(44,'2025_08_27_080600_fix_share_pairing_consistency',4),(45,'2025_08_27_171918_add_by_admin_column_to_user_share_payments_table',5),(46,'2025_08_27_172302_fix_user_share_payments_foreign_key_constraint',6),(47,'2025_08_27_164924_add_decline_attempts_to_user_share_pairs_table',7),(48,'2025_08_28_180017_add_suspension_reason_to_users_table',8),(49,'2025_08_30_115000_fix_allocate_share_histories_foreign_key',9),(50,'2025_09_01_140627_create_supports_table',10),(51,'2025_09_19_094451_add_is_active_to_markets_table',11),(52,'2025_09_21_044553_fix_user_share_pairs_column_types',12),(53,'2025_08_27_082000_add_state_consistency_constraints',13),(54,'2025_08_27_083000_fix_historical_state_inconsistencies',14),(55,'2025_08_27_130000_fix_user_shares_status_constraints',14),(56,'2025_09_18_000001_add_sale_maturity_timer_fields',14),(57,'2025_09_19_061500_add_selling_phase_timer_fields_to_user_shares_table',14),(58,'2025_09_21_051144_fix_sold_shares_maturation_logic',15),(59,'2025_09_21_052353_fix_incomplete_bought_to_sold_transitions',16),(60,'2025_09_21_052952_fix_premature_matured_at_setting',17),(61,'2025_09_21_053436_fix_premature_is_ready_to_sell_flag',18),(62,'2025_09_21_054032_fix_admin_allocated_shares_selling_started_at',19),(63,'2025_09_21_054938_force_all_running_shares_to_mature_immediately',20),(64,'2025_09_21_164921_fix_ready_to_sell_constraint',21),(65,'2025_09_21_042200_make_all_running_shares_available',22),(66,'2025_09_25_104900_add_floated_to_market_at_to_user_shares_table',22),(67,'2025_09_26_050000_drop_floated_to_market_at_from_user_shares_table',23),(68,'2025_09_26_090938_add_referral_bonus_at_registration_to_users_table',24),(69,'2025_09_26_092415_backfill_referral_bonus_at_registration_for_existing_users',25);
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
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(1,'App\\Models\\User',16);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policies`
--

LOCK TABLES `policies` WRITE;
/*!40000 ALTER TABLE `policies` DISABLE KEYS */;
INSERT INTO `policies` VALUES (1,'How it works','how-it-work','How To Bid (Buy Shares)','','How Do We Benefit From All These??','How Do We Benefit From All These??','2025-08-27 06:19:34','2025-08-27 06:19:34'),(2,'Privacy Policy','privacy-policy','Privacy Policy','<p>This Privacy Policy describes how your personal information is collected, used, and shared when you use our platform.</p><p>We are committed to protecting your privacy and ensuring the security of your personal data.</p><p>For more details about our privacy practices, please contact our support team.</p>','Data Protection','<p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>','2025-09-17 14:28:04','2025-09-17 14:28:04'),(3,'Terms and Conditions','terms-and-conditions','Terms and Conditions','<p>By accessing and using our platform, you agree to be bound by these Terms and Conditions.</p><p>These terms govern your use of our services and outline your rights and responsibilities.</p><p>Please read these terms carefully before using our platform.</p>','User Responsibilities','<p>Users are responsible for maintaining the confidentiality of their account information and for all activities that occur under their account.</p>','2025-09-17 14:28:40','2025-09-17 14:28:40'),(4,'Confidentiality Policy','confidentiality-policy','Confidentiality Policy','<p>We are committed to maintaining the confidentiality of your personal and financial information.</p><p>This policy outlines how we protect your sensitive data and maintain strict confidentiality standards.</p><p>Your trust is important to us, and we take our responsibility to protect your information seriously.</p>','Information Security','<p>We employ industry-standard security measures and protocols to ensure your information remains confidential and secure at all times.</p>','2025-09-17 14:29:04','2025-09-17 14:29:04');
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
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(24,2),(24,5),(25,1),(25,2),(25,5),(26,1),(26,2),(26,5),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Admin','web','2025-08-27 06:30:03','2025-08-27 06:30:03'),(2,'KYC Update','web','2025-09-17 11:18:49','2025-09-17 11:18:49'),(5,'KYC','web','2025-09-17 12:51:45','2025-09-17 12:51:45');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supports`
--

DROP TABLE IF EXISTS `supports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(191) NOT NULL,
  `last_name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `number` varchar(191) NOT NULL,
  `username` varchar(191) DEFAULT NULL,
  `message` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supports_user_id_foreign` (`user_id`),
  CONSTRAINT `supports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supports`
--

LOCK TABLES `supports` WRITE;
/*!40000 ALTER TABLE `supports` DISABLE KEYS */;
INSERT INTO `supports` VALUES (1,14,'Maddy','Power','julitex2011@yahoo.com','25688688666',NULL,'Hello',0,'2025-09-16 19:19:36','2025-09-16 19:19:36');
/*!40000 ALTER TABLE `supports` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trade_periods`
--

LOCK TABLES `trade_periods` WRITE;
/*!40000 ALTER TABLE `trade_periods` DISABLE KEYS */;
INSERT INTO `trade_periods` VALUES (1,3,30,1,NULL,'2025-08-27 06:32:03','2025-08-27 06:32:03'),(2,6,60,1,NULL,'2025-08-27 06:32:15','2025-08-27 06:32:15'),(3,9,90,1,NULL,'2025-08-27 06:32:28','2025-08-27 06:32:28'),(4,1,10,1,NULL,'2025-08-30 11:45:42','2025-08-30 11:48:00');
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
INSERT INTO `trades` VALUES (1,'Safaricom Shares','safaricom-shares',190000,1.00,0.00,1,NULL,'2025-08-27 06:31:03','2025-09-15 12:23:20'),(2,'Airtel Shares','airtel-shares',0,1.00,0.00,1,NULL,'2025-08-27 06:31:15','2025-08-27 06:31:15'),(3,'KCB Shares','kcb-shares',0,1.00,0.00,1,NULL,'2025-08-27 06:31:24','2025-08-27 06:31:24'),(4,'Equity Bank Shares','equity-bank-shares',0,1.00,0.00,1,NULL,'2025-08-27 06:31:36','2025-08-27 06:31:36');
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_payment_failures`
--

LOCK TABLES `user_payment_failures` WRITE;
/*!40000 ALTER TABLE `user_payment_failures` DISABLE KEYS */;
INSERT INTO `user_payment_failures` VALUES (23,14,0,0,NULL,NULL,NULL,NULL,NULL,'2025-09-21 16:18:39','2025-09-21 16:23:34');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `paired_user_share_id` bigint(20) unsigned DEFAULT NULL,
  `share` int(11) NOT NULL DEFAULT 0,
  `is_paid` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Here 0 = unpaid, 1 = paid',
  `decline_attempts` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Number of times payment has been declined for this pair',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_share_pair` (`user_share_id`,`paired_user_share_id`),
  KEY `user_share_pairs_user_id_foreign` (`user_id`),
  KEY `idx_payment_status_created` (`is_paid`,`created_at`),
  KEY `idx_user_share_payment` (`user_share_id`,`is_paid`),
  KEY `idx_paired_share_payment` (`paired_user_share_id`,`is_paid`),
  CONSTRAINT `user_share_pairs_paired_user_share_id_foreign` FOREIGN KEY (`paired_user_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_share_pairs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_share_pairs_user_share_id_foreign` FOREIGN KEY (`user_share_id`) REFERENCES `user_shares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_payment_status` CHECK (`is_paid` in (0,1,2)),
  CONSTRAINT `chk_share_quantity` CHECK (`share` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_share_pairs`
--

LOCK TABLES `user_share_pairs` WRITE;
/*!40000 ALTER TABLE `user_share_pairs` DISABLE KEYS */;
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
  CONSTRAINT `user_share_payments_user_share_pair_id_foreign` FOREIGN KEY (`user_share_pair_id`) REFERENCES `user_share_pairs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_payment_status_enum` CHECK (`status` in ('pending','paid','conformed','failed')),
  CONSTRAINT `chk_payment_amount` CHECK (`amount` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_share_payments`
--

LOCK TABLES `user_share_payments` WRITE;
/*!40000 ALTER TABLE `user_share_payments` DISABLE KEYS */;
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
  `selling_started_at` datetime DEFAULT NULL COMMENT 'When the investment period started (separate from buying phase)',
  `selling_timer_paused` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Investment maturity timer pause state (separate from payment timer)',
  `selling_timer_paused_at` timestamp NULL DEFAULT NULL COMMENT 'When investment maturity timer was paused',
  `selling_paused_duration_seconds` int(11) NOT NULL DEFAULT 0 COMMENT 'Total seconds investment timer has been paused',
  `payment_timer_paused` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Payment deadline timer pause state (for bought shares only)',
  `payment_timer_paused_at` timestamp NULL DEFAULT NULL COMMENT 'When payment deadline timer was paused',
  `payment_paused_duration_seconds` int(11) NOT NULL DEFAULT 0 COMMENT 'Total seconds payment timer has been paused',
  `timer_paused` tinyint(1) NOT NULL DEFAULT 0,
  `timer_paused_at` timestamp NULL DEFAULT NULL,
  `paused_duration_seconds` int(11) NOT NULL DEFAULT 0,
  `maturity_timer_paused` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether the sale maturity timer is paused (admin intervention only)',
  `maturity_timer_paused_at` timestamp NULL DEFAULT NULL COMMENT 'When the sale maturity timer was paused',
  `maturity_paused_duration_seconds` int(11) NOT NULL DEFAULT 0 COMMENT 'Total seconds the maturity timer has been paused',
  `maturity_pause_reason` varchar(191) DEFAULT NULL COMMENT 'Reason why maturity timer was paused',
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
  KEY `idx_status_created` (`status`,`created_at`),
  KEY `idx_status_timer` (`status`,`timer_paused`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_trade_status` (`trade_id`,`status`),
  KEY `idx_maturity_timer_status` (`status`,`is_ready_to_sell`,`maturity_timer_paused`),
  CONSTRAINT `user_shares_trade_id_foreign` FOREIGN KEY (`trade_id`) REFERENCES `trades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_shares_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_quantities` CHECK (`total_share_count` >= 0 and `hold_quantity` >= 0 and `sold_quantity` >= 0),
  CONSTRAINT `chk_user_share_status_fixed` CHECK (`status` in ('pending','pairing','paired','partially_paired','completed','failed','sold','suspended','running','active')),
  CONSTRAINT `chk_ready_to_sell_logic` CHECK (`is_ready_to_sell` = 0 or `is_ready_to_sell` = 1 and `status` in ('completed','failed','sold'))
) ENGINE=InnoDB AUTO_INCREMENT=76057 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_shares`
--

LOCK TABLES `user_shares` WRITE;
/*!40000 ALTER TABLE `user_shares` DISABLE KEYS */;
INSERT INTO `user_shares` VALUES (76055,1,8,'AB-17588678356',50000.00,0.00,1,50000,50000,0,0,'completed',NULL,'allocated-by-admin',1,0,'2025-09-26 09:23:55','2025-09-26 09:28:55','2025-09-26 09:23:55',0,NULL,0,0,NULL,0,0,NULL,0,0,NULL,0,NULL,60,NULL,'2025-09-26 06:23:55','2025-09-26 06:28:55',5000),(76056,1,14,'AB-17588678508',50000.00,0.00,1,50000,50000,0,0,'completed',NULL,'allocated-by-admin',1,0,'2025-09-26 09:24:10','2025-09-26 09:28:55','2025-09-26 09:24:10',0,NULL,0,0,NULL,0,0,NULL,0,0,NULL,0,NULL,60,NULL,'2025-09-26 06:24:10','2025-09-26 06:28:55',5000);
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
  `referral_bonus_at_registration` int(11) DEFAULT NULL COMMENT 'Stores the referral bonus amount that was active when this user registered',
  `mode` varchar(100) NOT NULL DEFAULT 'light',
  `block_until` timestamp NULL DEFAULT NULL,
  `suspension_until` timestamp NULL DEFAULT NULL,
  `suspension_reason` enum('manual','automatic','payment_failure') DEFAULT 'manual' COMMENT 'Reason for suspension: manual (by admin), automatic (system), payment_failure (payment issues)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Auto Bidder',1,'admin@autobidder.com','superadmin','03400000000',NULL,'2025-08-27 06:30:03','$2y$10$wJmn2RIr9k.64D0doRNl6OOsl.NufcD8BCK/htqxl0jBl820KmVzS','','1',NULL,NULL,NULL,'2025-08-27 06:19:33','2025-08-28 15:21:15',0.00,'active',0,NULL,'light',NULL,NULL,NULL),(8,'Daniel Wafula',2,'dw915499@gmail.com','Danny','0795001587',NULL,'2025-08-27 06:20:13','$2y$10$EJkZZi8m8gXd8j8MMB21Me.BgY0zGGUf/dQImFgXXLTvnMjJIfxA2','','1','{\"mpesa_no\":\"0795001587\",\"mpesa_name\":\"Daniel Wafula\",\"mpesa_till_no\":\"\",\"mpesa_till_name\":\"\"}',NULL,NULL,'2025-08-27 06:19:43','2025-09-16 16:41:07',3000.00,'active',0,NULL,'light',NULL,NULL,NULL),(9,'Maddy Power',2,'Richardbrandson2015@gmail.com','maddyPower','254715172652','Danny','2025-08-27 06:41:53','$2y$10$EJkZZi8m8gXd8j8MMB21Me.BgY0zGGUf/dQImFgXXLTvnMjJIfxA2','images/1755789287.jpg','1','{\"mpesa_no\":\"0715172652\",\"mpesa_name\":\"Julius Njoroge\",\"mpesa_till_no\":\"5149535\",\"mpesa_till_name\":\"Blimpies Tasty Fries\"}',NULL,NULL,'2025-08-27 06:27:32','2025-09-26 06:25:37',3000.00,'active',100,100,'light',NULL,NULL,NULL),(14,'Johanna',2,'julitex2011@yahoo.com','johana33','254347578254',NULL,'2025-09-16 16:30:46','$2y$10$U7C2c.CVxeMEFxOsAg7Tb.2H3zf0hnqhuTDNIFMtqWsVZI3nw8MPG','assets/images/users/default.jpg','1','{\"mpesa_no\":\"7272737\",\"mpesa_name\":\"Johana\",\"mpesa_till_no\":null,\"mpesa_till_name\":null}',NULL,'MnSxhsez3jexYusYtt3wfAJPgAL4OfI3GUddTuvltDHC8NvJwK3R0gXChjUs','2025-09-16 16:30:27','2025-09-16 16:30:46',0.00,'active',0,NULL,'light',NULL,NULL,'manual'),(15,'Malkio Wachio',2,'juliusgachure@yahoo.com','Malkio','275845755454','johana33','2025-09-16 17:40:27','$2y$10$vDIF9lrjl32UagQl09P2oeAdq5iV0dsxDjSR0YdSvPn5ZhpK3Vpoa','assets/images/users/default.jpg','1','{\"mpesa_no\":\"627273\",\"mpesa_name\":\"Malkio\",\"mpesa_till_no\":null,\"mpesa_till_name\":null}',NULL,NULL,'2025-09-16 17:40:14','2025-09-16 17:40:27',0.00,'active',0,NULL,'light',NULL,NULL,'manual'),(16,'Peter Kuria',1,'gachure33@gmail.com','peter','02547888',NULL,NULL,'$2y$10$T1LuYj.ycDRGcBDNmVf9RuCOFzp/jMBUNPHuaz9NNrl.IodhjtByW','assets/images/users/avatar-1.jpg','1',NULL,NULL,NULL,'2025-09-17 11:20:10','2025-09-17 11:20:10',0.00,'active',0,NULL,'light',NULL,NULL,'manual');
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

-- Dump completed on 2025-09-26  9:31:46
