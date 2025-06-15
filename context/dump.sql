/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.11-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: telemedi
-- ------------------------------------------------------
-- Server version	10.11.11-MariaDB-0ubuntu0.24.04.2

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
-- Table structure for table `agent_activity_log`
--

DROP TABLE IF EXISTS `agent_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agent_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `activity_start_datetime` datetime NOT NULL,
  `activity_end_datetime` datetime NOT NULL,
  `was_successful` tinyint(1) NOT NULL,
  `activity_reference_id` varchar(255) DEFAULT NULL COMMENT 'e.g., Call ID from telephony system',
  PRIMARY KEY (`id`),
  KEY `idx_agent_activity_log_agent_datetime` (`agent_id`,`activity_start_datetime`),
  KEY `idx_agent_activity_log_queue_datetime` (`queue_id`,`activity_start_datetime`),
  CONSTRAINT `fk_agent_activity_log_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_agent_activity_log_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_activity_log`
--

LOCK TABLES `agent_activity_log` WRITE;
/*!40000 ALTER TABLE `agent_activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `agent_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agent_availability_exceptions`
--

DROP TABLE IF EXISTS `agent_availability_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agent_availability_exceptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `unavailable_datetime_start` datetime DEFAULT NULL,
  `unavialable_datetime_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_agent_availability_exceptions_agent_dates` (`agent_id`),
  CONSTRAINT `fk_agent_availability_exceptions_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_availability_exceptions`
--

LOCK TABLES `agent_availability_exceptions` WRITE;
/*!40000 ALTER TABLE `agent_availability_exceptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `agent_availability_exceptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agent_skills`
--

DROP TABLE IF EXISTS `agent_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agent_skills` (
  `agent_id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  PRIMARY KEY (`agent_id`,`queue_id`),
  KEY `fk_agent_skills_queue` (`queue_id`),
  CONSTRAINT `fk_agent_skills_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_agent_skills_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_skills`
--

LOCK TABLES `agent_skills` WRITE;
/*!40000 ALTER TABLE `agent_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `agent_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agents`
--

DROP TABLE IF EXISTS `agents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `default_availability_pattern` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Standard weekly availability pattern (e.g., {"Mon": ["08:00-16:00"], "Tue": ["09:00-17:00"]})' CHECK (json_valid(`default_availability_pattern`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agents`
--

LOCK TABLES `agents` WRITE;
/*!40000 ALTER TABLE `agents` DISABLE KEYS */;
/*!40000 ALTER TABLE `agents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue_load_trends`
--

DROP TABLE IF EXISTS `queue_load_trends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_load_trends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `quarter` int(11) NOT NULL COMMENT 'Quarter number (1-4)',
  `calculation_date` date NOT NULL COMMENT 'Date (e.g., first day of month/quarter) when data was calculated/aggregated',
  `metric_name` varchar(255) NOT NULL COMMENT 'e.g., ''AverageHourlyCallVolume''',
  `metric_value` varchar(255) NOT NULL COMMENT 'Calculated metric value (VARCHAR for flexibility)',
  `additional_description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_queue_load_trends_queue_year_quarter` (`queue_id`,`year`,`quarter`),
  CONSTRAINT `fk_queue_load_trends_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue_load_trends`
--

LOCK TABLES `queue_load_trends` WRITE;
/*!40000 ALTER TABLE `queue_load_trends` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_load_trends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queues`
--

DROP TABLE IF EXISTS `queues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `queues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_name` varchar(255) NOT NULL,
  `priority` int(11) DEFAULT NULL COMMENT 'Queue priority (e.g., 1-highest, 2-medium, 3-low)',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_name` (`queue_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queues`
--

LOCK TABLES `queues` WRITE;
/*!40000 ALTER TABLE `queues` DISABLE KEYS */;
/*!40000 ALTER TABLE `queues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `time_slot_start` time NOT NULL,
  `time_slot_end` time NOT NULL,
  `entry_status` varchar(50) NOT NULL COMMENT 'e.g., ''ProposedBySystem'', ''ConfirmedByManager''',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_schedules_agent_date_slot` (`agent_id`,`schedule_date`,`time_slot_start`),
  KEY `fk_schedules_queue` (`queue_id`),
  KEY `idx_schedules_date_slot_queue` (`schedule_date`,`time_slot_start`,`queue_id`),
  CONSTRAINT `fk_schedules_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_schedules_queue` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-05  5:00:40
