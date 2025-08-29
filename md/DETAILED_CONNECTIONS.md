# VITA ESP32 Detailed Pin Connections

##  **Complete Pin-to-Pin Wiring Guide**

### **ESP32 DevKit v1 to Components:**

---

##  **NEO7 GPS Module**
```
ESP32 Pin 16 (GPIO16/RX2) ←→ NEO7 GPS TX Pin
ESP32 Pin 17 (GPIO17/TX2) ←→ NEO7 GPS RX Pin
ESP32 3.3V ←→ NEO7 GPS VCC Pin
ESP32 GND ←→ NEO7 GPS GND Pin
```

---

## **MAX30100 Heart Rate & SpO2 Sensor**
```
ESP32 Pin 21 (GPIO21/SDA) ←→ MAX30100 SDA Pin
ESP32 Pin 22 (GPIO22/SCL) ←→ MAX30100 SCL Pin
ESP32 3.3V ←→ MAX30100 VIN Pin
ESP32 GND ←→ MAX30100 GND Pin
```

---

##  **MPU6050 IMU (Accelerometer/Gyroscope)**
```
ESP32 Pin 21 (GPIO21/SDA) ←→ MPU6050 SDA Pin
ESP32 Pin 22 (GPIO22/SCL) ←→ MPU6050 SCL Pin
ESP32 3.3V ←→ MPU6050 VCC Pin
ESP32 GND ←→ MPU6050 GND Pin
```
*Note: MAX30100 and MPU6050 share the same I2C bus (pins 21 & 22)*

---

## **MP3 TF 16P Audio Module**
```
ESP32 Pin 25 (GPIO25) ←→ MP3 TF 16P RX Pin
ESP32 Pin 26 (GPIO26) ←→ MP3 TF 16P TX Pin
ESP32 3.3V ←→ MP3 TF 16P VCC Pin
ESP32 GND ←→ MP3 TF 16P GND Pin
```

---

## 🔊 **Speaker Connection**
```
ESP32 Pin 27 (GPIO27) ←→ Speaker Positive (+)
ESP32 GND ←→ Speaker Negative (-)
```
*Note: For better audio quality, use a small amplifier between ESP32 and speaker*

---

## **Battery Monitoring**
```
ESP32 Pin A0 (ADC0/GPIO36) ←→ Battery Voltage Divider Output
TP4056 BAT+ ←→ Voltage Divider Input
ESP32 GND ←→ Voltage Divider Ground
```

**Voltage Divider Circuit:**
```
TP4056 BAT+ ──[10kΩ]──┬──[10kΩ]── GND
                      │
                      └── ESP32 A0
```

---

##  **TP4056 Charging Module**
```
USB Input ←→ TP4056 USB+ and USB-
TP4056 BAT+ ←→ 3.7V Li-Po Battery (+)
TP4056 BAT- ←→ 3.7V Li-Po Battery (-)
TP4056 OUT+ ←→ ESP32 VIN Pin
TP4056 OUT- ←→ ESP32 GND Pin
```

---

##  **Status LED**
```
ESP32 Pin 2 (GPIO2) ←→ LED Positive (Anode)
LED Negative (Cathode) ←→ 220Ω Resistor ←→ ESP32 GND
```

---

##  **Power Connections**
```
TP4056 OUT+ ←→ ESP32 VIN Pin
TP4056 OUT- ←→ ESP32 GND Pin
ESP32 3.3V Output ←→ All Sensor VCC Pins
ESP32 GND ←→ All Sensor GND Pins
```

---

##  **Complete Wiring Summary Table**

| ESP32 Pin | Function | Connected To | Component |
|-----------|----------|--------------|-----------|
| VIN | Power Input | TP4056 OUT+ | TP4056 Module |
| GND | Ground | TP4056 OUT-, All GNDs | Common Ground |
| 3.3V | Power Output | All VCC pins | All Sensors |
| GPIO2 | Digital Out | LED + (via resistor) | Status LED |
| GPIO16 | UART2 RX | NEO7 GPS TX | GPS Module |
| GPIO17 | UART2 TX | NEO7 GPS RX | GPS Module |
| GPIO21 | I2C SDA | MAX30100 SDA, MPU6050 SDA | Heart Rate + IMU |
| GPIO22 | I2C SCL | MAX30100 SCL, MPU6050 SCL | Heart Rate + IMU |
| GPIO25 | UART (RX) | MP3 TF 16P TX | Audio Module |
| GPIO26 | UART (TX) | MP3 TF 16P RX | Audio Module |
| GPIO27 | PWM/DAC | Speaker (+) | Audio Output |
| GPIO36 (A0) | ADC | Voltage Divider from TP4056 BAT+ | Battery Monitor |

---

##  **Power Distribution Diagram**
```
USB Charger (5V)
        │
    TP4056 Charging Module
        │
3.7V Li-Po Battery ←→ TP4056 BAT+/BAT-
        │
    TP4056 OUT+/OUT-
        │
        ├── ESP32 VIN (Power Input)
        │
ESP32 3.3V Regulator Output
        │
        ├── NEO7 GPS (VCC)
        ├── MAX30100 (VIN)
        ├── MPU6050 (VCC)
        ├── MP3 TF 16P (VCC)
        └── Status LED (via GPIO2)

Common Ground (GND)
        │
        ├── TP4056 OUT-
        ├── ESP32 GND
        ├── All Sensor GND pins
        ├── Speaker (-)
        └── LED Cathode (via resistor)
```

---

## 🔧 **Important Notes:**

### **I2C Bus Sharing:**
- MAX30100 and MPU6050 both use I2C
- They share pins 21 (SDA) and 22 (SCL)
- Each has different I2C addresses, so no conflicts

### **UART Usage:**
- GPS uses Hardware Serial2 (pins 16, 17)
- MP3 module uses SoftwareSerial (pins 25, 26)
- USB Serial (pins 1, 3) remains free for programming

### **Power Considerations:**
- Total current draw: ~460mA peak
- ESP32 3.3V regulator can handle up to 600mA
- All components are 3.3V compatible
- No level shifters needed

### **Audio Output:**
- Pin 27 can drive small speakers directly
- For better quality, add a small amplifier
- MP3 module has built-in DAC for audio processing

### **Battery Monitoring:**
- Voltage divider reduces 3.7V to ~1.85V for ADC
- ESP32 ADC reads 0-3.3V safely
- Code calculates actual battery voltage

This wiring configuration ensures all components work together on the 3.3V system with proper communication protocols and power distribution.