-- Conference Paper Submission System Database Schema
-- Created for Master's Thesis Project

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `conference_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `conference_db`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('admin','author','reviewer') NOT NULL DEFAULT 'author',
  `institution` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `expertise` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `papers`
CREATE TABLE `papers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `abstract` text NOT NULL,
  `keywords` varchar(500) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `co_authors` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `status` enum('submitted','under_review','accepted','rejected','revision_required') NOT NULL DEFAULT 'submitted',
  `submission_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `decision_date` timestamp NULL DEFAULT NULL,
  `decision_by` int(11) DEFAULT NULL,
  `decision_comments` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `conference_track` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  KEY `decision_by` (`decision_by`),
  KEY `submission_date` (`submission_date`),
  FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`decision_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `reviews`
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paper_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `overall_rating` int(1) NOT NULL CHECK (`overall_rating` BETWEEN 1 AND 10),
  `technical_quality` int(1) NOT NULL CHECK (`technical_quality` BETWEEN 1 AND 5),
  `novelty` int(1) NOT NULL CHECK (`novelty` BETWEEN 1 AND 5),
  `significance` int(1) NOT NULL CHECK (`significance` BETWEEN 1 AND 5),
  `clarity` int(1) NOT NULL CHECK (`clarity` BETWEEN 1 AND 5),
  `recommendation` enum('accept','minor_revision','major_revision','reject') NOT NULL,
  `detailed_comments` text NOT NULL,
  `confidential_comments` text DEFAULT NULL,
  `review_status` enum('assigned','in_progress','completed') NOT NULL DEFAULT 'assigned',
  `assigned_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitted_date` timestamp NULL DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `paper_id` (`paper_id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `review_status` (`review_status`),
  KEY `deadline` (`deadline`),
  UNIQUE KEY `unique_review` (`paper_id`, `reviewer_id`),
  FOREIGN KEY (`paper_id`) REFERENCES `papers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `reviewer_assignments`
CREATE TABLE `reviewer_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paper_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deadline` date DEFAULT NULL,
  `status` enum('pending','accepted','declined','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `paper_id` (`paper_id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `status` (`status`),
  UNIQUE KEY `unique_assignment` (`paper_id`, `reviewer_id`),
  FOREIGN KEY (`paper_id`) REFERENCES `papers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `conference_settings`
CREATE TABLE `conference_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `user_sessions`
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `activity_log`
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `resource_type` (`resource_type`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Insert default admin user (password: password)
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `role`, `institution`, `department`, `country`) VALUES
('admin@conference.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'Conference Organization', 'Administration', 'Greece');

-- Insert sample author (password: password)
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `role`, `institution`, `department`, `country`, `expertise`) VALUES
('author@conference.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Author', 'author', 'University of Athens', 'Computer Science', 'Greece', 'Machine Learning, Data Mining');

-- Insert sample reviewer (password: password)
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `role`, `institution`, `department`, `country`, `expertise`) VALUES
('reviewer@conference.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Reviewer', 'reviewer', 'Technical University of Crete', 'Computer Science', 'Greece', 'Artificial Intelligence, Natural Language Processing');

-- Insert conference settings
INSERT INTO `conference_settings` (`setting_key`, `setting_value`, `description`) VALUES
('conference_name', 'International Conference on Computer Science 2025', 'Name of the conference'),
('submission_deadline', '2025-03-15', 'Paper submission deadline'),
('review_deadline', '2025-04-30', 'Review submission deadline'),
('notification_date', '2025-05-15', 'Author notification date'),
('conference_date', '2025-07-10', 'Conference start date'),
('max_file_size', '10485760', 'Maximum file size in bytes (10MB)'),
('allowed_extensions', 'pdf,doc,docx', 'Allowed file extensions for paper submission'),
('reviews_per_paper', '3', 'Number of reviews required per paper'),
('enable_registration', '1', 'Allow new user registrations');

COMMIT;

-- Create indexes for better performance
CREATE INDEX idx_papers_status_date ON papers(status, submission_date);
CREATE INDEX idx_reviews_rating ON reviews(overall_rating);
CREATE INDEX idx_users_role_active ON users(role, is_active);
CREATE INDEX idx_activity_log_date ON activity_log(created_at);

-- Create views for common queries
CREATE VIEW paper_statistics AS
SELECT 
    status,
    COUNT(*) as count,
    AVG(CASE WHEN status = 'under_review' THEN DATEDIFF(NOW(), submission_date) END) as avg_review_days
FROM papers 
WHERE is_active = 1 
GROUP BY status;

CREATE VIEW reviewer_workload AS
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.email,
    COUNT(ra.id) as assigned_papers,
    COUNT(r.id) as completed_reviews,
    AVG(r.overall_rating) as avg_rating
FROM users u
LEFT JOIN reviewer_assignments ra ON u.id = ra.reviewer_id
LEFT JOIN reviews r ON u.id = r.reviewer_id AND r.review_status = 'completed'
WHERE u.role = 'reviewer' AND u.is_active = 1
GROUP BY u.id;