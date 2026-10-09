-- Migration: Certificates, Awards and Badges Table
-- Run this in phpMyAdmin if not already auto-created

CREATE TABLE IF NOT EXISTS `certificates` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `issuer` varchar(255) NOT NULL,
  `issue_date` varchar(50) DEFAULT NULL,
  `credential_id` varchar(100) DEFAULT NULL,
  `credential_url` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('certificate','award','badge') NOT NULL DEFAULT 'certificate',
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample initial entry
INSERT INTO `certificates` (`title`, `issuer`, `issue_date`, `credential_id`, `credential_url`, `category`, `description`, `sort_order`, `is_published`) 
VALUES 
('Full Stack Web Development Certification', 'freeCodeCamp', '2024', 'FCC-FSW-2024', 'https://freecodecamp.org', 'certificate', 'Demonstrated proficiency in modern full stack web development technologies.', 1, 1),
('Outstanding Project Award', 'City University Peshawar', '2024', 'CUSIT-AW-01', '', 'award', 'Recognized for building innovative AI and software engineering solutions.', 2, 1);
