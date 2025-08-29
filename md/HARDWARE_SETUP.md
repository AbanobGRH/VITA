# VITA ESP32 Hardware Setup Guide

## 🔌 Power Requirements & Connections

### **Power Supply (3.3V System with TP4056 Charging)**
All components work on **3.3V** from ESP32:

```
Power Distribution:
3.7V Li-Po Battery ↔ TP4056 Charging Module ↔ ESP32 VIN
ESP32 (3.3V Output) → All Sensors
├── NEO7 GPS Module (3.3V)
├── MAX30100 Heart Rate Sensor (3.3V) 
├── MPU6050 IMU (3.3V)
├── MP3 TF 16P Module (3.3V)
└── Speaker (via amplifier if needed)

Charging: USB-C/Micro-USB → TP4056 → Battery + ESP32
```

## 📋 Required Libraries

### **Arduino IDE Setup:**

1. **Install ESP32 Board Package:**
   - File → Preferences → Additional Board Manager URLs
   - Add: `https://dl.espressif.com/dl/package_esp32_index.json`
   - Tools → Board → Boards Manager → Search "ESP32" → Install

2. **Install Required Libraries:**
   ```
   Library Manager (Ctrl+Shift+I):
   ├── WiFi (Built-in)
   ├── WebServer (Built-in) 
   ├── DNSServer (Built-in)
   ├── EEPROM (Built-in)
   ├── HTTPClient (Built-in)
   ├── ArduinoJson by Benoit Blanchon
   ├── SoftwareSerial (Built-in)
   ├── Wire (Built-in)
   ├── MAX30100lib by OXullo
   ├── MPU6050 by Electronic Cats
   ├── DFRobotDFPlayerMini by DFRobot
   ├── FS (Built-in)
   └── SPIFFS (Built-in)
   ```

## 🔧 Hardware Connections

### **Detailed Wiring Diagram:**

```
ESP32 DevKit v1 Pinout:
┌─────────────────────────────────────────┐
│                ESP32                    │
├─────────────────────────────────────────┤
│ 3.3V ──┬── NEO7 GPS (VCC)              │
│        ├── MAX30100 (VIN)               │
│        ├── MPU6050 (VCC)                │
│        └── MP3 TF 16P (VCC)             │
│                                         │
│ GND ───┬── All Sensors (GND)            │
│        └── Speaker (-)                  │
│                                         │
│ Pin 16 ──── NEO7 GPS (TX)               │
│ Pin 17 ──── NEO7 GPS (RX)               │
│                                         │
│ Pin 21 ──┬── MAX30100 (SDA)             │
│          └── MPU6050 (SDA)              │
│                                         │
│ Pin 22 ──┬── MAX30100 (SCL)             │
│          └── MPU6050 (SCL)              │
│                                         │
│ Pin 25 ──── MP3 TF 16P (RX)             │
│ Pin 26 ──── MP3 TF 16P (TX)             │
│                                         │
│ Pin 27 ──── Speaker (+) or Amplifier    │
│                                         │
│ Pin A0 ──── Battery Monitor (Voltage)   │
│                                         │
│ Pin 2 ───── Status LED                  │
│                                         │
│ VIN ────── 3.7V Li-Po Battery (+)       │
│ GND ────── 3.7V Li-Po Battery (-)       │
└─────────────────────────────────────────┘
```

### **Component Specifications:**

| Component | Voltage | Current | Interface |
|-----------|---------|---------|-----------|
| ESP32 | 3.3V | 240mA | Main MCU |
| NEO7 GPS | 3.3V | 45mA | UART |
| MAX30100 | 3.3V | 50mA | I2C |
| MPU6050 | 3.3V | 3.9mA | I2C |
| MP3 TF 16P | 3.3V | 20mA | UART |
| Speaker | 3.3V | 100mA | Analog |

**Total Current: ~460mA (peak)**

## 🔋 Battery & Charging

### **Recommended Setup:**
- **Battery:** 3.7V Li-Po 2000mAh+ 
- **Charging:** TP4056 USB-C charging module
- **Runtime:** ~4-6 hours continuous operation

### **Battery Monitor Circuit:**
```
Battery (+) ──┬── ESP32 VIN
              │
              └── Voltage Divider ──── ESP32 A0
                  (10kΩ + 10kΩ)
```

## 📱 First Time Setup Process

### **WiFi Configuration Portal:**

1. **Power on ESP32** (no saved WiFi networks)
2. **Device creates AP:** `VITA-Setup` (Password: `12345678`)
3. **Connect phone/laptop** to VITA-Setup network
4. **Open browser:** `http://192.168.4.1`
5. **Web interface appears** with WiFi setup options
6. **Scan networks** or manually enter WiFi credentials
7. **Save up to 5 networks** for automatic connection
8. **Device connects** and starts normal operation

### **Status LED Indicators:**
- **Blinking rapidly:** WiFi setup mode
- **Solid ON:** Connected and operating
- **Slow blink:** Trying to connect
- **OFF:** No power or error

## 🚀 Upload Instructions

### **Step-by-Step Upload:**

1. **Connect ESP32** to computer via USB
2. **Open Arduino IDE**
3. **Select Board:** Tools → Board → ESP32 Dev Module
4. **Select Port:** Tools → Port → (Your ESP32 port)
5. **Set Upload Speed:** 921600
6. **Flash Size:** 4MB
7. **Partition Scheme:** Default 4MB with spiffs
8. **Copy code** into Arduino IDE
9. **Click Upload** (Ctrl+U)

### **Upload Settings:**
```
Board: ESP32 Dev Module
Upload Speed: 921600
CPU Frequency: 240MHz
Flash Frequency: 80MHz
Flash Mode: QIO
Flash Size: 4MB (32Mb)
Partition Scheme: Default 4MB with spiffs (1.2MB APP/1.5MB SPIFFS)
Core Debug Level: None
```

## 🔧 Troubleshooting

### **Common Issues:**

1. **Upload Failed:**
   - Hold BOOT button while uploading
   - Check USB cable and drivers
   - Try lower upload speed (115200)

2. **WiFi Not Connecting:**
   - Reset device (hold BOOT + EN, release EN first)
   - Check WiFi credentials in portal
   - Ensure 2.4GHz network (not 5GHz)

3. **Sensors Not Working:**
   - Check 3.3V power supply
   - Verify I2C connections (SDA/SCL)
   - Check serial connections for GPS/MP3

4. **Audio Issues:**
   - Verify MP3 module connections
   - Check SD card in MP3 module
   - Test speaker connections

## 📊 System Features

### **WiFi Portal Features:**
- **Network Scanning:** Automatic detection of available networks
- **Multiple Networks:** Store up to 5 WiFi credentials
- **Auto-Connect:** Tries saved networks in order
- **Manual Entry:** Add hidden networks manually
- **Reset Function:** Clear all settings and restart
- **Mobile Friendly:** Responsive web interface

### **Health Monitoring:**
- **Heart Rate:** Continuous monitoring via MAX30100
- **SpO2:** Blood oxygen level tracking
- **GPS Location:** Real-time position tracking
- **Fall Detection:** IMU-based fall detection algorithm
- **Battery Monitoring:** Power level reporting

### **Smart Features:**
- **Medication Reminders:** Downloads and plays MP3 audio
- **Emergency Alerts:** Automatic fall detection alerts
- **Data Sync:** Real-time server communication
- **Offline Storage:** Local data buffering when offline

The system is now ready for deployment with complete WiFi setup portal and hardware integration!