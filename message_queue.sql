/*
MySQL - 10.11.11-MariaDB
*********************************************************************
*/

/*Table structure for table `message_queue` */

CREATE TABLE `message_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_type` enum('email','sms','push') NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `bcc` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `status` enum('queued','processing','sent','failed') DEFAULT 'queued',
  `queue_date` datetime DEFAULT current_timestamp(),
  `sent_date` datetime DEFAULT NULL,
  `result` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `created_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `priority` (`priority`),
  KEY `recipient` (`recipient`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

