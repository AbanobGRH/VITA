-- Fix medications table ID column
-- Run this in phpMyAdmin or your MariaDB client

-- First, check current table structure
DESCRIBE medications;

-- If the id column is not varchar(36), fix it
-- Option 1: If table is empty, drop and recreate
DROP TABLE IF EXISTS medications;

-- Recreate with correct structure
CREATE TABLE `medications` (
    `id` varchar(36) NOT NULL, 
    `user_id` varchar(36) NOT NULL,
    `name` varchar(255) NOT NULL,
    `dosage` varchar(100) NOT NULL,
    `frequency` varchar(100) NOT NULL,
    `times` longtext NOT NULL, 
    `condition_for` varchar(255) DEFAULT NULL,
    `instructions` text DEFAULT NULL,
    `audio_file_path` varchar(500) DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify the fix
DESCRIBE medications;
