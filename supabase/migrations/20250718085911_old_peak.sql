-- Insert a health reading for the test user
INSERT IGNORE INTO `health_readings` (
    `id`, `user_id`, `heart_rate`, `spo2`, `glucose_level`, `reading_timestamp`, `device_id`, `created_at`
) VALUES (
    'hr-1', '550e8400-e29b-41d4-a716-446655440000', 72, 98, 110, NOW(), 'dev-1', NOW()
);

-- Insert an IMU reading for the test user
INSERT IGNORE INTO `imu_readings` (
    `id`, `user_id`, `device_id`, `imu_x`, `imu_y`, `imu_z`, `magnitude`, `imu_timestamp`, `created_at`
) VALUES (
    'imu-1', '550e8400-e29b-41d4-a716-446655440000', 'dev-1', 0.12, -0.05, 0.98, 0.99, NOW(), NOW()
);

-- Insert an alert for the test user
INSERT IGNORE INTO `alerts` (
    `id`, `user_id`, `alert_type`, `message`, `severity`, `is_read`, `metadata`, `created_at`, `read_at`
) VALUES (
    'alert-1', '550e8400-e29b-41d4-a716-446655440000', 'geofence_exit', 'User has left all safe zones', 'medium', 0, '{"latitude":39.7392,"longitude":-104.9903}', NOW(), NULL
);

-- Insert a fall event for the test user
INSERT IGNORE INTO `fall_events` (
    `id`, `user_id`, `latitude`, `longitude`, `acceleration_data`, `confidence_level`, `is_confirmed`, `created_at`, `confirmed_at`
) VALUES (
    'fall-1', '550e8400-e29b-41d4-a716-446655440000', 39.7392, -104.9903, '[{"x":0.12,"y":-0.05,"z":0.98}]', 0.92, 0, NOW(), NULL
);

-- --------------------------------------------------
-- Dummy Data for Testing
-- --------------------------------------------------

-- Insert a test user
INSERT IGNORE INTO `users` (
    `id`, `email`, `full_name`, `date_of_birth`, `age`, `marital_status`, `height`, `does_sports`, `phone`, `address`, `blood_type`, `medical_conditions`, `allergies`, `emergency_contact_name`, `emergency_contact_phone`, `primary_physician`, `insurance_info`, `breakfast_time`, `lunch_time`, `dinner_time`, `snack_times`, `dietary_restrictions`, `preferred_meal_size`, `created_at`, `updated_at`
) VALUES (
    '550e8400-e29b-41d4-a716-446655440000', 'test@example.com', 'John Doe', '1980-01-01', 45, 'single', 175.5, 1, '1234567890', '123 Oak Street, Springfield', 'O+', '[]', '[]', 'Jane Doe', '0987654321', 'Dr. Smith', 'ACME Insurance', '08:00:00', '12:00:00', '18:00:00', '[]', '[]', 'medium', NOW(), NOW()
);

-- Insert a safe zone for the test user
INSERT IGNORE INTO `safe_zones` (
    `id`, `user_id`, `name`, `latitude`, `longitude`, `radius`, `is_active`, `created_at`
) VALUES (
    'zone-1', '550e8400-e29b-41d4-a716-446655440000', 'Home', 39.7392, -104.9903, 50, 1, NOW()
);

-- Insert location history for the test user
INSERT IGNORE INTO `locations` (
    `id`, `user_id`, `latitude`, `longitude`, `accuracy`, `speed`, `location_timestamp`, `is_safe_zone`, `zone_name`, `created_at`
) VALUES
    ('loc-1', '550e8400-e29b-41d4-a716-446655440000', 39.7392, -104.9903, 3.0, 0.0, DATE_SUB(NOW(), INTERVAL 5 MINUTE), 1, 'Home', NOW()),
    ('loc-2', '550e8400-e29b-41d4-a716-446655440000', 39.7390, -104.9905, 4.0, 0.0, DATE_SUB(NOW(), INTERVAL 15 MINUTE), 1, 'Home', NOW());

-- Insert a medication for the test user
INSERT IGNORE INTO `medications` (
    `id`, `user_id`, `name`, `dosage`, `frequency`, `times`, `condition_for`, `instructions`, `is_active`, `created_at`, `updated_at`
) VALUES (
    'med-1', '550e8400-e29b-41d4-a716-446655440000', 'Aspirin', '100mg', 'daily', '["08:00:00"]', 'Heart Health', 'Take with water', 1, NOW(), NOW()
);
