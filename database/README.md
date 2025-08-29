# VITA Health Database Setup

## MariaDB/phpMyAdmin Installation

### Option 1: Using phpMyAdmin Interface

1. **Access phpMyAdmin** in your browser (usually `http://localhost/phpmyadmin`)
2. **Import the SQL file**:
   - Click on "Import" tab
   - Choose file: `database/vita_health_mariadb.sql`
   - Click "Go" to execute

### Option 2: Using Command Line

```bash
# Login to MariaDB
mysql -u root -p

# Create database and import
source /path/to/vita_health_mariadb.sql
```

### Option 3: Using MySQL Command Line

```bash
# Import directly
mysql -u root -p < database/vita_health_mariadb.sql
```

## Database Configuration

Update your `api/config.php` file with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vita_health');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

## Features

- **MariaDB 10.x Compatible**: Uses MariaDB-specific syntax
- **UTF8MB4 Character Set**: Full Unicode support including emojis
- **JSON Data Types**: For flexible data storage
- **Foreign Key Constraints**: Data integrity
- **Indexes**: Optimized for performance
- **Sample Data**: Pre-loaded test user and data

## Tables Created

1. `users` - User profiles with meal preferences
2. `health_readings` - Heart rate, SpO2, glucose data
3. `locations` - GPS tracking data
4. `medications` - Medication information
5. `medication_reminders` - Scheduled reminders
6. `alerts` - System notifications
7. `family_members` - Family access control
8. `safe_zones` - Location safety zones
9. `fall_events` - Fall detection data

## Sample User

- **Email**: margaret.thompson@email.com
- **ID**: 550e8400-e29b-41d4-a716-446655440000
- **Name**: Margaret Thompson
- **Age**: 72 years old

## Testing

After setup, you can:
1. Access the web interface
2. Use the test data page (`test-data.php`) to generate sample data
3. View data across all pages with auto-refresh

## Troubleshooting

### Common Issues:

1. **UUID() Function Error**:
   - Ensure MariaDB version 10.7+ or MySQL 8.0+
   - Alternative: Use `UNHEX(REPLACE(UUID(),'-',''))` for older versions

2. **JSON Column Error**:
   - Requires MariaDB 10.2+ or MySQL 5.7+
   - Alternative: Use TEXT columns for older versions

3. **Permission Denied**:
   - Check database user permissions
   - Ensure user has CREATE, INSERT, SELECT privileges

### Version Compatibility:

- **MariaDB**: 10.2 or higher recommended
- **MySQL**: 5.7 or higher recommended
- **PHP**: 7.4 or higher