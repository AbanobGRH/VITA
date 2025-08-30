-- Fix for medications table ID column issue
-- Run this in phpMyAdmin or your MariaDB client

-- Check current table structure
DESCRIBE medications;

-- If the ID column is not the right size, modify it
ALTER TABLE medications MODIFY COLUMN id VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

-- Verify the change
DESCRIBE medications;
