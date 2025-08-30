-- VITA Health Platform Database Schema for MariaDB/phpMyAdmin
-- Compatible with MariaDB 10.x and phpMyAdmin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database


-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` varchar(36) NOT NULL, 
    `email` varchar(255) NOT NULL,
    `full_name` varchar(255) NOT NULL,
    `date_of_birth` date DEFAULT NULL,
    `age` int(11) DEFAULT NULL,
    `marital_status` enum('single','married','divorced','widowed') DEFAULT NULL,
    `height` decimal(5,2) DEFAULT NULL, -- in centimeters
    `does_sports` tinyint(1) DEFAULT 0, -- 0 = no, 1 = yes
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `blood_type` varchar(10) DEFAULT NULL,
    `medical_conditions` longtext DEFAULT NULL, 
    `allergies` longtext DEFAULT NULL, 
    `emergency_contact_name` varchar(255) DEFAULT NULL,
    `emergency_contact_phone` varchar(20) DEFAULT NULL,
    `primary_physician` varchar(255) DEFAULT NULL,
    `insurance_info` text DEFAULT NULL,
    `breakfast_time` time DEFAULT '08:00:00',
    `lunch_time` time DEFAULT '12:00:00',
    `dinner_time` time DEFAULT '18:00:00',
    `snack_times` longtext DEFAULT NULL, 
    `dietary_restrictions` longtext DEFAULT NULL, 
    `preferred_meal_size` enum('small','medium','large') DEFAULT 'medium',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Health readings table (removed blood pressure as requested)
CREATE TABLE IF NOT EXISTS `health_readings` (
    `id` varchar(36) NOT NULL, 
    `user_id` varchar(36) NOT NULL,
    `heart_rate` int(11) DEFAULT NULL,
    `spo2` int(11) DEFAULT NULL,
    `glucose_level` int(11) DEFAULT NULL,
    `reading_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
    `device_id` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_health_readings_user_timestamp` (`user_id`, `reading_timestamp` DESC),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- IMU readings table
CREATE TABLE IF NOT EXISTS `imu_readings` (
    `id` varchar(36) NOT NULL, 
    `user_id` varchar(36) NOT NULL,
    `device_id` varchar(100) DEFAULT NULL,
    `imu_x` float DEFAULT NULL,
    `imu_y` float DEFAULT NULL,
    `imu_z` float DEFAULT NULL,
    `magnitude` float DEFAULT NULL,
    `imu_timestamp` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_imu_user_timestamp` (`user_id`, `imu_timestamp` DESC),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Locations table
CREATE TABLE IF NOT EXISTS `locations` (
    `id` varchar(36) NOT NULL, 
    `user_id` varchar(36) NOT NULL,
    `latitude` decimal(10,8) DEFAULT NULL,
    `longitude` decimal(11,8) DEFAULT NULL,
    `accuracy` decimal(5,2) DEFAULT NULL,
    `speed` decimal(5,2) DEFAULT NULL,
    `location_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
    `is_safe_zone` tinyint(1) DEFAULT 0,
    `zone_name` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_locations_user_timestamp` (`user_id`, `location_timestamp` DESC),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medications table
CREATE TABLE IF NOT EXISTS `medications` (
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

-- Alerts table
CREATE TABLE IF NOT EXISTS `alerts` (
    `id` varchar(36) NOT NULL, 
    `user_id` varchar(36) NOT NULL,
    `alert_type` varchar(100) NOT NULL,
    `message` text NOT NULL,
    `severity` enum('low','medium','high') DEFAULT 'medium',
    `is_read` tinyint(1) DEFAULT 0,
    `metadata` longtext DEFAULT NULL, 
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `read_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_alerts_user_created` (`user_id`, `created_at` DESC),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Safe zones table
CREATE TABLE IF NOT EXISTS `safe_zones` (
    `id` varchar(36) NOT NULL, 
    `user_id` varchar(36) NOT NULL,
    `name` varchar(255) NOT NULL,
    `latitude` decimal(10,8) NOT NULL,
    `longitude` decimal(11,8) NOT NULL,
    `radius` int(11) DEFAULT 50,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fall_events` (
    `id` varchar(36) NOT NULL, 
    `user_id` varchar(36) NOT NULL,
    `latitude` decimal(10,8) DEFAULT NULL,
    `longitude` decimal(11,8) DEFAULT NULL,
    `acceleration_data` longtext DEFAULT NULL, 
    `confidence_level` decimal(3,2) DEFAULT NULL,
    `is_confirmed` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `confirmed_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


