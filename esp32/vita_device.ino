#include <time.h>
#include <WiFi.h>
#include <WebServer.h>
#include <DNSServer.h>
#include <EEPROM.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <MAX30100lib.h>
#include <MPU6050.h>
#include <DFRobotDFPlayerMini.h>
#include <FS.h>
#include <SPIFFS.h>
#include <SD.h>

WebServer server(80);
DNSServer dnsServer;
const byte DNS_PORT = 53;

struct WiFiCredentials {
  char ssid[32];
  char password[64];
};

WiFiCredentials wifiNetwork; 
bool wifiSaved = false;

// Server configuration
const char* serverURL = "http://vita.future-x.dev/api/endpoint.php";
const char* audioURL = "https://vita.future-x.dev/api/medication_audio.php";
const char* apiKey = "vita_api_key_2024";
const char* userId = "550e8400-e29b-41d4-a716-446655440000";
const char* deviceId = "ESP32_VITA_001";

// Hardware pins (3.3V compatible)
#define GPS_RX_PIN 16
#define GPS_TX_PIN 17
#define BATTERY_PIN A0

// Hardware pins (5V compatible)
#define MP3_RX_PIN 18
#define MP3_TX_PIN 19
// Sensor objects
// For ESP32, use HardwareSerial for GPS and MP3 if needed:
HardwareSerial gpsSerial(1); // RX/TX pins can be set in setup()
HardwareSerial mp3Serial(2);
MAX30100 heartSensor;
MPU6050 mpu;
DFRobotDFPlayerMini mp3Player;

// Data structures
struct HealthData {
  int heartRate;
  int spo2;
  int glucoseLevel;
  unsigned long timestamp;
};

struct LocationData {
  float latitude;
  float longitude;
  float accuracy;
  float speed;
  unsigned long timestamp;
};

struct AccelerationData {
  float x, y, z;
  float magnitude;
  unsigned long timestamp;
};

// Global variables
time_t deviceTime = 0;
unsigned long lastTimeSync = 0;
const unsigned long TIME_SYNC_INTERVAL = 3600000; // 1 hour
void syncTimeFromServer() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = String(serverURL) + "?action=get_time";
    http.begin(url);
    int httpCode = http.GET();
    if (httpCode == 200) {
      String payload = http.getString();
      DynamicJsonDocument doc(256);
      DeserializationError err = deserializeJson(doc, payload);
      if (!err && doc["timestamp"]) {
        deviceTime = doc["timestamp"].as<time_t>();
        Serial.printf("Time synced: %ld\n", deviceTime);
      }
    }
    http.end();
  }
}
HealthData currentHealth;
LocationData currentLocation;
const int IMU_BUFFER_SIZE = 180;
AccelerationData accelHistory[IMU_BUFFER_SIZE];
int accelIndex = 0;
unsigned long lastHealthReading = 0;
unsigned long lastLocationReading = 0;
unsigned long lastMedicationCheck = 0;
unsigned long lastDataSend = 0;
bool fallDetected = false;
bool wifiConfigMode = false;

// Timing constants
const unsigned long HEALTH_INTERVAL = 3600000;    // 1 hour
const unsigned long LOCATION_INTERVAL = 3600000;  // 1 hour
const unsigned long MEDICATION_CHECK_INTERVAL = 1800000; // 30 minutes
const unsigned long DATA_SEND_INTERVAL = 3600000;  // 1 hour

void setup() {
  Serial.begin(115200);
  
  // Initialize EEPROM for WiFi storage
  EEPROM.begin(512);
  // Initialize SPIFFS for file storage
  if (!SPIFFS.begin(true)) {
    Serial.println("SPIFFS initialization failed");
  }
  // Load saved WiFi network
  loadWiFiCredentials();
  // Try to connect to saved network
  if (!connectToSavedNetwork()) {
    // Start WiFi configuration portal
    startWiFiPortal();
  } else {
    // Initialize sensors after WiFi connection
    initializeSensors();
  }
}

void loop() {
  if (wifiConfigMode) {
    dnsServer.processNextRequest();
    server.handleClient();
    return;
  }
  unsigned long currentMillis = millis();
  // Sync time from server every hour
  if (currentMillis - lastTimeSync >= TIME_SYNC_INTERVAL) {
    syncTimeFromServer();
    lastTimeSync = currentMillis;
  }
  // Read sensors and send data every hour
  if (currentMillis - lastHealthReading >= HEALTH_INTERVAL) {
    readHealthSensors(currentMillis);
    lastHealthReading = currentMillis;
  }
  if (currentMillis - lastLocationReading >= LOCATION_INTERVAL) {
    readLocationSensor(currentMillis);
    lastLocationReading = currentMillis;
  }
  readAccelerometer(currentMillis);
  checkFallDetection();
  if (currentMillis - lastDataSend >= DATA_SEND_INTERVAL) {
    sendDataToServer();
    lastDataSend = currentMillis;
  }
  if (currentMillis - lastMedicationCheck >= MEDICATION_CHECK_INTERVAL) {
    checkMedicationReminders();
    lastMedicationCheck = currentMillis;
  }
  // IMU readings are buffered and analyzed for outliers every 180 readings (15 mins)
  delay(1000);
}

void startWiFiPortal() {
  wifiConfigMode = true;
  // Create AP
  WiFi.mode(WIFI_AP);
  WiFi.softAP("VITA-Setup", "12345678");
  // Start DNS server
  dnsServer.start(DNS_PORT, "*", WiFi.softAPIP());
  // Setup web server routes
  server.on("/", handleRoot);
  server.on("/scan", handleScan);
  server.on("/save", handleSave);
  server.on("/reset", handleReset);
  server.on("/saved", handleSaved);
  server.onNotFound(handleRoot);
  server.begin();
  Serial.println("WiFi Portal Started");
  Serial.print("Connect to: VITA-Setup");
  Serial.print("Password: 12345678");
  Serial.print("Open: http://192.168.4.1");
  
}

void handleRoot() {
  String html = R"(
<!DOCTYPE html>
<html>
<head>
    <title>VITA WiFi Setup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial; margin: 20px; background: #f0f0f0; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        h1 { color: #4A90E2; text-align: center; }
        .network { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4A90E2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; margin: 5px 0; }
        button:hover { background: #3A7BC8; }
        .scan-btn { background: #7ED6A5; }
        .reset-btn { background: #E74C3C; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏥 VITA WiFi Setup</h1>
        <p>Configure WiFi networks for your VITA health device</p>
        
        <button class="scan-btn" onclick="scanNetworks()">🔍 Scan Networks</button>
        
        <div id="networks"></div>
        
        <h3>Add Network Manually</h3>
        <form id="wifiForm">
            <input type="text" id="ssid" placeholder="WiFi Network Name" required>
            <input type="password" id="password" placeholder="WiFi Password" required>
            <button type="submit">💾 Save Network</button>
        </form>
        
        <div id="savedNetworks">
            <h3>Saved Networks</h3>
            <div id="networkList"></div>
        </div>
        
        <button class="reset-btn" onclick="resetDevice()">🔄 Reset All Settings</button>
        
        <div id="status"></div>
    </div>

    <script>
        function scanNetworks() {
            document.getElementById('status').innerHTML = '<div class="status">Scanning networks...</div>';
            fetch('/scan')
                .then(response => response.json())
                .then(data => {
                    let html = '<h3>Available Networks</h3>';
                    data.networks.forEach(network => {
                        html += `<div class="network">
                            <strong>${network.ssid}</strong> (${network.rssi} dBm)
                            <button onclick="selectNetwork('${network.ssid}')" style="float: right;">Select</button>
                        </div>`;
                    });
                    document.getElementById('networks').innerHTML = html;
                    document.getElementById('status').innerHTML = '';
                });
        }
        
        function selectNetwork(ssid) {
            document.getElementById('ssid').value = ssid;
        }
        
        document.getElementById('wifiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const ssid = document.getElementById('ssid').value;
            const password = document.getElementById('password').value;
            
            document.getElementById('status').innerHTML = '<div class="status">Saving network...</div>';
            
            fetch('/save', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ssid=${encodeURIComponent(ssid)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('status').innerHTML = `<div class="status success">${data}</div>`;
                document.getElementById('wifiForm').reset();
                loadSavedNetworks();
            })
            .catch(error => {
                document.getElementById('status').innerHTML = `<div class="status error">Error: ${error}</div>`;
            });
        });
        
        function loadSavedNetworks() {
            document.getElementById('status').innerHTML = '<div class="status">Loading saved network...</div>';
            fetch('/saved')
                .then(response => response.json())
                .then(data => {
                    let html = '<h3>Saved Network</h3>';
                    if (data.ssid) {
                        html += `<div class="network">
                            <strong>${data.ssid}</strong>
                        </div>`;
                    } else {
                        html += `<div class="network">
                            <em>No saved network</em>
                        </div>`;
                    }
                    document.getElementById('networkList').innerHTML = html;
                    document.getElementById('status').innerHTML = '';
                });
        }
        
        function resetDevice() {
            if (confirm('Reset all WiFi settings? Device will restart.')) {
                fetch('/reset').then(() => {
                    document.getElementById('status').innerHTML = '<div class="status">Device resetting...</div>';
                });
            }
        }
        
        // Load saved networks on page load
        loadSavedNetworks();
    </script>
</body>
</html>
  )";
  
  server.send(200, "text/html", html);
}

void handleScan() {
  int n = WiFi.scanNetworks();
  String json = "{\"networks\":[";
  
  for (int i = 0; i < n; i++) {
    if (i > 0) json += ",";
    json += "{\"ssid\":\"" + WiFi.SSID(i) + "\",\"rssi\":" + String(WiFi.RSSI(i)) + "}";
  }
  
  json += "]}";
  server.send(200, "application/json", json);
}

void handleSave() {
  String ssid = server.arg("ssid");
  String password = server.arg("password");
  if (ssid.length() > 0) {
    // Save to memory (only 1 network)
    ssid.toCharArray(wifiNetwork.ssid, 32);
    password.toCharArray(wifiNetwork.password, 64);
    wifiSaved = true;
    // Save to EEPROM
    saveWiFiCredentials();
    // Try to connect
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid.c_str(), password.c_str());
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
      delay(500);
      attempts++;
    }
    if (WiFi.status() == WL_CONNECTED) {
      server.send(200, "text/plain", "Network saved and connected! Device will restart in normal mode.");
      delay(2000);
      wifiConfigMode = false;
      initializeSensors();
    } else {
      server.send(200, "text/plain", "Network saved but connection failed. Please check credentials.");
    }
  } else {
    server.send(400, "text/plain", "Invalid network data.");
  }
}

void handleReset() {
  for (int i = 0; i < 512; i++) {
    EEPROM.write(i, 0);
  }
  EEPROM.commit();
  wifiSaved = false;
  memset(&wifiNetwork, 0, sizeof(wifiNetwork));
  server.send(200, "text/plain", "Settings reset. Device restarting...");
  delay(1000);
  ESP.restart();
}

void handleSaved() {
  String json = "{";
  if (wifiSaved) {
    json += "\"ssid\":\"" + String(wifiNetwork.ssid) + "\"";
  } else {
    json += "\"ssid\":null";
  }
  json += "}";
  server.send(200, "application/json", json);
}

void saveWiFiCredentials() {
  // Save wifiSaved flag
  EEPROM.write(0, wifiSaved ? 1 : 0);
  // Save SSID
  for (int j = 0; j < 32; j++) {
    EEPROM.write(1 + j, wifiNetwork.ssid[j]);
  }
  // Save password
  for (int j = 0; j < 64; j++) {
    EEPROM.write(33 + j, wifiNetwork.password[j]);
  }
  EEPROM.commit();
}

void loadWiFiCredentials() {
  wifiSaved = EEPROM.read(0) == 1;
  if (wifiSaved) {
    for (int j = 0; j < 32; j++) {
      wifiNetwork.ssid[j] = EEPROM.read(1 + j);
    }
    for (int j = 0; j < 64; j++) {
      wifiNetwork.password[j] = EEPROM.read(33 + j);
    }
  } else {
    memset(&wifiNetwork, 0, sizeof(wifiNetwork));
  }
}

bool connectToSavedNetwork() {
  if (!wifiSaved) return false;
  WiFi.mode(WIFI_STA);
  Serial.printf("Trying to connect to: %s\n", wifiNetwork.ssid);
  WiFi.begin(wifiNetwork.ssid, wifiNetwork.password);
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected!");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
    
    return true;
  }
  return false;
}

void initializeSensors() {
  // Initialize sensors after WiFi connection
  Wire.begin();
  
  if (!heartSensor.begin()) {
    Serial.println("MAX30100 initialization failed");
  }
  
  if (!mpu.begin()) {
    Serial.println("MPU6050 initialization failed");
  } else {
    mpu.setAccelerometerRange(MPU6050_RANGE_8_G);
    mpu.setGyroRange(MPU6050_RANGE_500_DEG);
    mpu.setFilterBandwidth(MPU6050_BAND_21_HZ);
  }
  
  // Initialize MP3 player
  if (!mp3Player.begin(mp3Serial)) {
    Serial.println("MP3 player initialization failed");
  } else {
    mp3Player.volume(20);
  }
  // Initialize SD card for MP3 playback
  if (!SD.begin()) {
    Serial.println("SD card initialization failed");
  } else {
    Serial.println("SD card initialized");
  }
  
  // Initialize data structures
  memset(&currentHealth, 0, sizeof(currentHealth));
  memset(&currentLocation, 0, sizeof(currentLocation));
  memset(accelHistory, 0, sizeof(accelHistory));
  
  Serial.println("VITA Device initialized and connected");
}

void readHealthSensors(unsigned long currentTime) {
  heartSensor.update();
  if (heartSensor.getRawValues(&currentHealth.heartRate, &currentHealth.spo2)) {
    if (currentHealth.heartRate > 50 && currentHealth.heartRate < 200 &&
        currentHealth.spo2 > 80 && currentHealth.spo2 <= 100) {
      // Calculate glucose level using the provided equation
      float bpm = currentHealth.heartRate;
      float spo2 = currentHealth.spo2;
      float glucose = 16714.61 + 0.47 * bpm - 351.045 * spo2 + 1.85 * (spo2 * spo2);
      currentHealth.glucoseLevel = (int)glucose;
      // Use deviceTime if available, else millis
      if (deviceTime > 0) {
        currentHealth.timestamp = deviceTime + (millis() / 1000);
      } else {
        currentHealth.timestamp = millis();
      }
      Serial.printf("Health: HR=%d, SpO2=%d, Glucose=%d\n", currentHealth.heartRate, currentHealth.spo2, currentHealth.glucoseLevel);
    }
  }
}

void readLocationSensor(unsigned long currentTime) {
  if (currentTime - lastLocationReading >= LOCATION_INTERVAL) {
    if (gpsSerial.available()) {
      String gpsData = gpsSerial.readString();
      if (parseGPSData(gpsData)) {
        currentLocation.timestamp = currentTime;
        Serial.printf("Location: Lat=%.6f, Lng=%.6f, Speed=%.2f\n", 
                     currentLocation.latitude, currentLocation.longitude, currentLocation.speed);
      }
    }
    lastLocationReading = currentTime;
  }
}

bool parseGPSData(String data) {
  if (data.indexOf("$GPRMC") != -1) {
    int commaIndex[12];
    int commaCount = 0;
    
    for (int i = 0; i < data.length() && commaCount < 12; i++) {
      if (data.charAt(i) == ',') {
        commaIndex[commaCount++] = i;
      }
    }
    
    if (commaCount >= 9) {
      String latStr = data.substring(commaIndex[2] + 1, commaIndex[3]);
      String latDir = data.substring(commaIndex[3] + 1, commaIndex[4]);
      String lngStr = data.substring(commaIndex[4] + 1, commaIndex[5]);
      String lngDir = data.substring(commaIndex[5] + 1, commaIndex[6]);
      String speedStr = data.substring(commaIndex[6] + 1, commaIndex[7]);
      
      if (latStr.length() > 0 && lngStr.length() > 0) {
        currentLocation.latitude = convertDMSToDD(latStr, latDir);
        currentLocation.longitude = convertDMSToDD(lngStr, lngDir);
        currentLocation.speed = speedStr.toFloat() * 1.852;
        currentLocation.accuracy = 3.0;
        return true;
      }
    }
  }
  return false;
}

float convertDMSToDD(String dms, String direction) {
  if (dms.length() < 4) return 0.0;
  
  float degrees = dms.substring(0, 2).toFloat();
  float minutes = dms.substring(2).toFloat();
  float dd = degrees + (minutes / 60.0);
  
  if (direction == "S" || direction == "W") {
    dd = -dd;
  }
  
  return dd;
}

void readAccelerometer(unsigned long currentTime) {
  sensors_event_t a, g, temp;
  mpu.getEvent(&a, &g, &temp);

  AccelerationData& current = accelHistory[accelIndex];
  current.x = a.acceleration.x;
  current.y = a.acceleration.y;
  current.z = a.acceleration.z;
  current.magnitude = sqrt(current.x * current.x + current.y * current.y + current.z * current.z);
  current.timestamp = currentTime;

  accelIndex++;
  if (accelIndex >= IMU_BUFFER_SIZE) {
    // Outlier detection using IQR (Interquartile Range)
    float magnitudes[IMU_BUFFER_SIZE];
    int valid = 0;
    for (int i = 0; i < IMU_BUFFER_SIZE; i++) {
      if (accelHistory[i].timestamp > 0) {
        magnitudes[valid++] = accelHistory[i].magnitude;
      }
    }
    if (valid > 0) {
      // Sort magnitudes
      for (int i = 0; i < valid - 1; i++) {
        for (int j = i + 1; j < valid; j++) {
          if (magnitudes[i] > magnitudes[j]) {
            float temp = magnitudes[i];
            magnitudes[i] = magnitudes[j];
            magnitudes[j] = temp;
          }
        }
      }
      // Calculate Q1, Q3
      int q1_idx = valid / 4;
      int q3_idx = (3 * valid) / 4;
      float Q1 = magnitudes[q1_idx];
      float Q3 = magnitudes[q3_idx];
      float IQR = Q3 - Q1;
      float outlier_threshold = Q3 + 1.5 * IQR;
      bool outlierFound = false;
      for (int i = 0; i < valid; i++) {
        if (magnitudes[i] > outlier_threshold) {
          outlierFound = true;
          break;
        }
      }
      if (outlierFound) {
        Serial.println("IMU Outlier detected (IQR): possible fall event");
        sendFallAlertToDB();
      }
    }
    // Reset buffer
    memset(accelHistory, 0, sizeof(accelHistory));
    accelIndex = 0;
  }
}
void sendFallAlertToDB() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String dbEndpoint = String(serverURL) + "?action=fall_event";
    http.begin(dbEndpoint);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-Key", apiKey);
    DynamicJsonDocument doc(4096);
    doc["user_id"] = userId;
    doc["device_id"] = deviceId;
    doc["timestamp"] = millis();
    if (currentLocation.timestamp > 0) {
      doc["latitude"] = currentLocation.latitude;
      doc["longitude"] = currentLocation.longitude;
    }
    JsonArray accelData = doc.createNestedArray("acceleration_data");
    for (int i = 0; i < IMU_BUFFER_SIZE; i++) {
      if (accelHistory[i].timestamp > 0) {
        JsonObject accel = accelData.createNestedObject();
        accel["x"] = accelHistory[i].x;
        accel["y"] = accelHistory[i].y;
        accel["z"] = accelHistory[i].z;
        accel["magnitude"] = accelHistory[i].magnitude;
        accel["timestamp"] = accelHistory[i].timestamp;
      }
    }
    doc["confidence_level"] = 0.95;
    String jsonString;
    serializeJson(doc, jsonString);
    int httpResponseCode = http.POST(jsonString);
    Serial.printf("Fall alert to DB sent: %d\n", httpResponseCode);
    http.end();
  }
}

void checkFallDetection() {
  float totalMagnitude = 0;
  float maxMagnitude = 0;
  int validReadings = 0;
  
  for (int i = 0; i < 10; i++) {
    if (accelHistory[i].timestamp > 0) {
      totalMagnitude += accelHistory[i].magnitude;
      maxMagnitude = max(maxMagnitude, accelHistory[i].magnitude);
      validReadings++;
    }
  }
  
  if (validReadings >= 5) {
    float avgMagnitude = totalMagnitude / validReadings;
    if (maxMagnitude > 20.0 && avgMagnitude < 8.0) {
      if (!fallDetected) {
        fallDetected = true;
        Serial.println("FALL DETECTED!");
        sendFallAlert();
        // Instead of blocking delay, set a timer to reset fallDetected
        static unsigned long fallResetTime = 0;
        fallResetTime = millis() + 30000;
      }
    }
  }
  // Reset fallDetected after 30 seconds
  static unsigned long fallResetTime = 0;
  if (fallDetected && fallResetTime > 0 && millis() > fallResetTime) {
    fallDetected = false;
    fallResetTime = 0;
  }
}

void sendDataToServer() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-Key", apiKey);
    
    DynamicJsonDocument doc(1024);
    doc["user_id"] = userId;
    doc["device_id"] = deviceId;
    doc["timestamp"] = millis();
    
    if (currentHealth.timestamp > 0) {
      doc["heart_rate"] = currentHealth.heartRate;
      doc["spo2"] = currentHealth.spo2;
      if (currentHealth.glucoseLevel > 0) {
        doc["glucose_level"] = currentHealth.glucoseLevel;
      }
    }
    
    if (currentLocation.timestamp > 0) {
      doc["latitude"] = currentLocation.latitude;
      doc["longitude"] = currentLocation.longitude;
      doc["accuracy"] = currentLocation.accuracy;
      doc["speed"] = currentLocation.speed;
    }
    
    int batteryLevel = map(analogRead(BATTERY_PIN), 0, 4095, 0, 100);
    doc["battery_level"] = batteryLevel;
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    int httpResponseCode = http.POST(jsonString);
    
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.printf("Server response: %d - %s\n", httpResponseCode, response.c_str());
    } else {
      Serial.printf("HTTP error: %d\n", httpResponseCode);
    }
    
    http.end();
  }
}

void sendFallAlert() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-Key", apiKey);
    
    DynamicJsonDocument doc(1024);
    doc["user_id"] = userId;
    doc["device_id"] = deviceId;
    doc["fall_detected"] = true;
    doc["confidence_level"] = 0.85;
    doc["timestamp"] = millis();
    
    if (currentLocation.timestamp > 0) {
      doc["latitude"] = currentLocation.latitude;
      doc["longitude"] = currentLocation.longitude;
    }
    
    JsonArray accelData = doc.createNestedArray("acceleration_data");
    for (int i = 0; i < 10; i++) {
      if (accelHistory[i].timestamp > 0) {
        JsonObject accel = accelData.createNestedObject();
        accel["x"] = accelHistory[i].x;
        accel["y"] = accelHistory[i].y;
        accel["z"] = accelHistory[i].z;
        accel["magnitude"] = accelHistory[i].magnitude;
        accel["timestamp"] = accelHistory[i].timestamp;
      }
    }
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    int httpResponseCode = http.POST(jsonString);
    Serial.printf("Fall alert sent: %d\n", httpResponseCode);
    
    http.end();
  }
}

void checkMedicationReminders() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = String(audioURL) + "?esp_next=1&user_id=" + userId;
    http.begin(url);
    int httpCode = http.GET();
    if (httpCode == 200) {
      String payload = http.getString();
      DynamicJsonDocument doc(512);
      DeserializationError err = deserializeJson(doc, payload);
      if (!err && doc["audio_url"] && doc["audio_url"].as<String>() != "") {
        String audioUrl = doc["audio_url"].as<String>();
        String filename = doc["filename"] | "medication.mp3";
        time_t reminderTime = doc["reminder_time"] | 0;
        if (reminderTime > 0 && deviceTime > 0) {
          time_t now = deviceTime + (millis() / 1000);
          // Download MP3 if within 1 hour of reminder
          if (abs(now - reminderTime) < 3600) {
            if (downloadMP3ToSPIFFS(audioUrl, filename)) {
              Serial.printf("MP3 downloaded for reminder at %ld\n", reminderTime);
            }
          }
          // Play MP3 only if within 1 minute of reminder
          if (abs(now - reminderTime) < 60) {
            playMP3FromSPIFFS(filename);
          } else {
            Serial.printf("Not time yet for playback: now=%ld, reminder=%ld\n", now, reminderTime);
          }
        }
      }
    }
    http.end();
  }
}

bool downloadMP3ToSPIFFS(String url, String filename) {
  // Delete previous mp3 files in SPIFFS before saving new one
  File root = SPIFFS.open("/");
  while (true) {
    File file = root.openNextFile();
    if (!file) break;
    String fname = String(file.name());
    if (fname.endsWith(".mp3")) {
      SPIFFS.remove(fname);
    }
    file.close();
  }
  root.close();

  HTTPClient http;
  http.begin(url);
  int httpCode = http.GET();
  if (httpCode == 200) {
    File file = SPIFFS.open("/" + filename, FILE_WRITE);
    if (!file) return false;
    WiFiClient* stream = http.getStreamPtr();
    uint8_t buf[512];
    int len = http.getSize();
    int total = 0;
    while (http.connected() && (len > 0 || len == -1)) {
      size_t size = stream->available();
      if (size) {
        int c = stream->readBytes(buf, ((size > sizeof(buf)) ? sizeof(buf) : size));
        file.write(buf, c);
        total += c;
        if (len > 0) len -= c;
      }
      delay(1);
    }
    file.close();
    http.end();
    return true;
  }
  http.end();
  return false;
}

void playMP3FromSPIFFS(String filename) {
  // Copy MP3 from SPIFFS to SD card, then play with DFPlayer Mini
  if (!SD.begin()) {
    Serial.println("SD card not initialized for MP3 playback");
    return;
  }
  File mp3File = SPIFFS.open("/" + filename, FILE_READ);
  if (!mp3File) {
    Serial.println("MP3 file not found in SPIFFS");
    return;
  }
  File sdFile = SD.open(filename, FILE_WRITE);
  if (!sdFile) {
    Serial.println("Failed to open SD file for writing");
    mp3File.close();
    return;
  }
  while (mp3File.available()) {
    sdFile.write(mp3File.read());
  }
  mp3File.close();
  sdFile.close();
  if (!mp3Player.playMp3File(filename.c_str())) {
    Serial.println("MP3 playback failed");
  }
}