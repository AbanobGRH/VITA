# TP4056 Charging Module Integration

## 🔋 **TP4056 Module Pinout & Connections**

### **TP4056 Module Pins:**
```
┌─────────────────────────┐
│        TP4056           │
├─────────────────────────┤
│ IN+  │ USB Power Input  │ ← USB 5V+
│ IN-  │ USB Ground       │ ← USB GND
│ BAT+ │ Battery Positive │ ← Li-Po Battery (+)
│ BAT- │ Battery Negative │ ← Li-Po Battery (-)
│ OUT+ │ Load Positive    │ → ESP32 VIN
│ OUT- │ Load Negative    │ → ESP32 GND
└─────────────────────────┘
```

## 🔌 **Complete Wiring with TP4056:**

### **Power Chain:**
```
USB Charger (5V) → TP4056 → 3.7V Li-Po Battery → ESP32 → 3.3V Sensors
```

### **Detailed Connections:**

#### **1. USB Charging Input:**
- **USB 5V+** ←→ **TP4056 IN+**
- **USB GND** ←→ **TP4056 IN-**

#### **2. Battery Connection:**
- **TP4056 BAT+** ←→ **3.7V Li-Po Battery (+)**
- **TP4056 BAT-** ←→ **3.7V Li-Po Battery (-)**

#### **3. ESP32 Power:**
- **TP4056 OUT+** ←→ **ESP32 VIN**
- **TP4056 OUT-** ←→ **ESP32 GND**

#### **4. Battery Monitoring:**
```
TP4056 BAT+ ──[10kΩ]──┬──[10kΩ]── GND
                      │
                      └── ESP32 GPIO36 (A0)
```

## 🚨 **TP4056 LED Indicators:**
- **Red LED ON**: Charging in progress
- **Blue LED ON**: Charging complete
- **No LEDs**: No USB power connected

## ⚡ **Power Management Features:**
- **Overcharge Protection**: Stops at 4.2V
- **Over-discharge Protection**: Cuts off at 2.5V
- **Short Circuit Protection**: Built-in safety
- **Thermal Protection**: Prevents overheating

## 🔧 **Assembly Order:**
1. **Connect TP4056** to battery first
2. **Connect TP4056 OUT** to ESP32 VIN/GND
3. **Add voltage divider** from TP4056 BAT+ to ESP32 A0
4. **Connect all sensors** to ESP32 3.3V rail
5. **Test charging** with USB before final assembly

## 📊 **Battery Monitoring Code:**
```cpp
// Read battery voltage through TP4056
int batteryRaw = analogRead(A0);
float batteryVoltage = (batteryRaw / 4095.0) * 3.3 * 2; // Voltage divider
int batteryPercent = map(batteryVoltage * 100, 320, 420, 0, 100);
```

This setup provides safe charging, power management, and accurate battery monitoring for your VITA device!