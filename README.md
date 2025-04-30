# PHP Message Queue 

A lightweight, database-backed message queue and SMTP mailer system for PHP projects. Supports HTML/plaintext emails and secure delivery using SMTP credentials from an `.env` file.

## Features

- Queue-based email delivery (deferred or immediate)
- Template-based messages with placeholder substitution (e.g., `{{ user_name }}`)
- HTML and plain text email versions
- Secure SMTP support using Symfony Mailer (or swapable transport)
- Logging of delivery results, success/failure, and timestamping

## Use Case Examples

- Password reset and signup confirmations
- Contact form autoresponders
- High-volume transactional notifications
- Systems where message retry/failure logging is required

## Components

- `message_queue` table (MySQL schema included)
- `MessageHelper.php` – queue logic and delivery
- `database.php` - database connection logic
- `init.php` - initialization script
- `env.php` - get variables from an .env file
- `process_messages.php` - optional CLI script to run queue processor

## Installation

1. Create the `message_queue` table (SQL provided below)
2. Add your SMTP credentials in a `.env` file:

   SMTP_HOST=smtp.example.com
   SMTP_PORT=587
   SMTP_USER=youruser@example.com
   SMTP_PASS=yourpassword
   FROM_EMAIL=youruser@example.com
   FROM_NAME="Your App"
   
3. Schedule `process_messages.php` as a cron job for queued delivery

## Example `message_queue` Table Schema
```
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
```

## Credits

Built by Rich Stern (https://www.linkedin.com/in/richard-stern-4244b09/) at ZJS Technology.
