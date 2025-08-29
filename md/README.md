# VITA Health Platform - Complete System

A comprehensive elderly care monitoring system with real-time health tracking, AI analytics, and ESP32 device integration.

## Features

### 🏥 Health Monitoring
- **Heart Rate & SpO2**: Real-time monitoring via MAX30100 sensor
- **Blood Glucose**: Tracking and trend analysis
- **AI Analytics**: Automated health analysis via ai.hackclub.com integration
- **Removed**: Blood pressure monitoring (as requested)

### 📱 Web Platform
- **Dashboard**: Real-time health metrics display
- **Medication Management**: Smart reminders with audio alerts
- **Location Tracking**: GPS monitoring with safe zones
- **Family Access**: Multi-user access control
- **Profile Management**: Complete user preferences including meal times

### 🤖 AI Integration
- **Real-time Analysis**: Continuous vital signs analysis
- **Smart Alerts**: AI-powered health recommendations
- **Risk Assessment**: Predictive health monitoring
- **API Integration**: Direct connection to ai.hackclub.com

### 🔧 Hardware Integration (ESP32)
- **NEO7 GPS**: Location tracking and geofencing
- **MAX30100**: Heart rate and SpO2 monitoring
- **IMU**: Fall detection with acceleration analysis
- **MP3 TF 16P**: Audio medication reminders
- **Battery Management**: Power monitoring and optimization

## System Architecture

### Database Schema
- **Users**: Profile information with meal preferences
- **Health Readings**: Heart rate, SpO2, glucose data
- **Locations**: GPS tracking with safe zones
- **Medications**: Smart medication management
- **Alerts**: AI-powered notifications
- **Family Members**: Access control system

### API Endpoints
- **POST /api/endpoint.php**: Receive sensor data from ESP32
- **GET /api/endpoint.php**: Retrieve health data and alerts
- **POST /api/medication_audio.php**: Generate medication reminders
- **GET /api/medication_audio.php**: Download audio files for ESP32

### ESP32 Features
- **Real-time Data**: Sends health and location data every minute
- **Fall Detection**: IMU-based fall detection with server alerts
- **Medication Reminders**: Downloads and plays MP3 reminders
- **Battery Monitoring**: Reports power status
- **WiFi Connectivity**: Robust connection management

## Installation

### 1. Database Setup
```sql
-- Import the schema
mysql -u root -p vita_health < database/schema.sql
```

### 2. PHP Configuration
```php
// Update api/config.php with your database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'vita_health');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. ESP32 Setup
```cpp
// Update WiFi credentials in esp32/vita_device.ino
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverURL = "http://your-server.com/api/endpoint.php";
```

### 4. Hardware Connections
```
ESP32 Connections:
- NEO7 GPS: RX=16, TX=17
- MAX30100: SDA=21, SCL=22
- MPU6050: SDA=21, SCL=22
- MP3 Player: RX=25, TX=26
- Speaker: Pin 27
- Battery Monitor: A0
```

## API Usage

### Send Health Data
```bash
curl -X POST http://your-server.com/api/endpoint.php \
  -H "Content-Type: application/json" \
  -H "X-API-Key: vita_api_key_2024" \
  -d '{
    "user_id": "550e8400-e29b-41d4-a716-446655440000",
    "device_id": "ESP32_VITA_001",
    "heart_rate": 72,
    "spo2": 98,
    "glucose_level": 95,
    "latitude": 39.7392,
    "longitude": -104.9903
  }'
```

### Get Medication Reminders
```bash
curl "http://your-server.com/api/medication_audio.php?action=get_files&user_id=550e8400-e29b-41d4-a716-446655440000&api_key=vita_api_key_2024"
```

## AI Analytics Integration

The system automatically sends health data to ai.hackclub.com for analysis:

```javascript
// Automatic analysis every 5 minutes
const aiAnalytics = new VitaAIAnalytics();
aiAnalytics.startContinuousAnalysis();

// Manual analysis trigger
aiAnalytics.analyzeCurrentVitals();
```

## Fall Detection Algorithm

The ESP32 implements a sophisticated fall detection system:

```cpp
// Detects sudden acceleration spikes followed by stillness
if (maxMagnitude > 20.0 && avgMagnitude < 8.0) {
    fallDetected = true;
    sendFallAlert();
}
```

## Medication Reminder System

1. **Server generates MP3 files** from medication schedules
2. **ESP32 checks every 5 minutes** for new audio files
3. **Downloads and plays** reminders at scheduled times
4. **Tracks adherence** and reports back to server

## Security Features

- **API Key Authentication**: All endpoints require valid API key
- **Row Level Security**: Database access control
- **HTTPS Support**: Secure data transmission
- **Input Validation**: Comprehensive data sanitization

## Monitoring & Alerts

### Health Alerts
- Heart rate anomalies
- SpO2 level warnings
- Glucose level alerts
- AI-generated health insights

### Location Alerts
- Geofence violations
- Fall detection
- Emergency location sharing

### System Alerts
- Device connectivity issues
- Battery level warnings
- Medication adherence tracking

## Development

### File Structure
```
vita-health/
├── api/
│   ├── config.php
│   ├── endpoint.php
│   └── medication_audio.php
├── assets/
│   ├── css/style.css
│   └── js/
│       ├── main.js
│       └── ai-analytics.js
├── database/
│   └── schema.sql
├── esp32/
│   └── vita_device.ino
└── *.html (web pages)
```

### Key Technologies
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+, MySQL 8.0+
- **Hardware**: ESP32, MAX30100, NEO7 GPS, MPU6050
- **AI**: Integration with ai.hackclub.com
- **Audio**: MP3 TF 16P module with TTS generation

## License

MIT License - See LICENSE file for details

## Support

For technical support or questions:
- Check the troubleshooting section in device-setup.html
- Review API documentation in the code comments
- Test hardware connections using the ESP32 serial monitor