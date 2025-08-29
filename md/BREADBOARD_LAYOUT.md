# VITA ESP32 Breadboard Layout Guide

## 🍞 **Breadboard Wiring Layout**

### **Components Placement on Breadboard:**

```
     A  B  C  D  E     F  G  H  I  J
   ┌─────────────────────────────────┐
 1 │ +  +  +  +  +     +  +  +  +  + │ ← 3.3V Rail
 2 │ -  -  -  -  -     -  -  -  -  - │ ← GND Rail
   ├─────────────────────────────────┤
 3 │                                 │
 4 │     [ESP32 DevKit v1]           │
 5 │                                 │
...│                                 │
15 │                                 │
   ├─────────────────────────────────┤
16 │ [NEO7]  [MAX30100]  [MPU6050]   │
17 │  GPS      Heart       IMU       │
18 │                                 │
19 │        [MP3 TF 16P]             │
20 │         Audio                   │
   ├─────────────────────────────────┤
21 │ +  +  +  +  +     +  +  +  +  + │ ← 3.3V Rail
22 │ -  -  -  -  -     -  -  -  -  - │ ← GND Rail
   └─────────────────────────────────┘
```

### **Step-by-Step Wiring:**

#### **Step 1: Power Rails**
```
Connect TP4056 OUT+ → ESP32 VIN
Connect TP4056 OUT- → ESP32 GND
Connect ESP32 3.3V → Top positive rail (row 1)
Connect ESP32 GND → Top negative rail (row 2)
Connect ESP32 GND → Bottom negative rail (row 22)
```

#### **Step 2: NEO7 GPS Module**
```
Place GPS at rows 16-18, columns A-D
GPS VCC → Row 1 (3.3V rail)
GPS GND → Row 2 (GND rail)
GPS TX → ESP32 Pin 16 (GPIO16)
GPS RX → ESP32 Pin 17 (GPIO17)
```

#### **Step 3: MAX30100 Heart Rate Sensor**
```
Place MAX30100 at rows 16-18, columns E-F
MAX30100 VIN → Row 1 (3.3V rail)
MAX30100 GND → Row 2 (GND rail)
MAX30100 SDA → ESP32 Pin 21 (GPIO21)
MAX30100 SCL → ESP32 Pin 22 (GPIO22)
```

#### **Step 4: MPU6050 IMU**
```
Place MPU6050 at rows 16-18, columns G-I
MPU6050 VCC → Row 1 (3.3V rail)
MPU6050 GND → Row 2 (GND rail)
MPU6050 SDA → ESP32 Pin 21 (GPIO21) [shared with MAX30100]
MPU6050 SCL → ESP32 Pin 22 (GPIO22) [shared with MAX30100]
```

#### **Step 5: MP3 TF 16P Audio Module**
```
Place MP3 module at rows 19-20, columns D-G
MP3 VCC → Row 1 (3.3V rail)
MP3 GND → Row 2 (GND rail)
MP3 RX → ESP32 Pin 25 (GPIO25)
MP3 TX → ESP32 Pin 26 (GPIO26)
```

#### **Step 6: Additional Components**
```
Status LED:
- LED Anode → ESP32 Pin 2 (GPIO2)
- LED Cathode → 220Ω resistor → GND rail

Speaker:
- Speaker (+) → ESP32 Pin 27 (GPIO27)
- Speaker (-) → GND rail

Battery Monitor (Voltage Divider):
- 10kΩ resistor: Battery (+) to breadboard row
- 10kΩ resistor: Same row to GND
- Junction → ESP32 Pin A0 (GPIO36)
```

### **Jumper Wire Connections:**

#### **Red Wires (3.3V Power):**
1. TP4056 OUT+ → ESP32 VIN
2. ESP32 3.3V → Breadboard top rail (+)
3. Top rail (+) → NEO7 VCC
4. Top rail (+) → MAX30100 VIN
5. Top rail (+) → MPU6050 VCC
6. Top rail (+) → MP3 TF 16P VCC

#### **Black Wires (Ground):**
1. TP4056 OUT- → ESP32 GND
2. ESP32 GND → Breadboard top rail (-)
3. ESP32 GND → Breadboard bottom rail (-)
4. Top rail (-) → NEO7 GND
5. Top rail (-) → MAX30100 GND
6. Top rail (-) → MPU6050 GND
7. Top rail (-) → MP3 TF 16P GND
8. Bottom rail (-) → Speaker (-)

#### **Signal Wires:**
1. **Yellow:** ESP32 GPIO16 → NEO7 TX
2. **Orange:** ESP32 GPIO17 → NEO7 RX
3. **Blue:** ESP32 GPIO21 → MAX30100 SDA & MPU6050 SDA
4. **Green:** ESP32 GPIO22 → MAX30100 SCL & MPU6050 SCL
5. **Purple:** ESP32 GPIO25 → MP3 TF 16P TX
6. **Gray:** ESP32 GPIO26 → MP3 TF 16P RX
7. **White:** ESP32 GPIO27 → Speaker (+)
8. **Brown:** ESP32 GPIO2 → LED Anode (via resistor)
9. **Pink:** ESP32 GPIO36 → Voltage divider junction (from TP4056 BAT+)

### **Final Breadboard Layout:**
```
Power Rails: TP4056 → ESP32 → 3.3V and GND distributed
Left Side: GPS module
Center: Heart rate and IMU sensors (I2C bus)
Right Side: MP3 audio module
External: Speaker, LED, TP4056 charging module
```

This layout keeps components organized, minimizes wire crossing, and maintains clean power distribution throughout the breadboard.