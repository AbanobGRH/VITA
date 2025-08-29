<?php
require_once 'config.php';

validateApiKey();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'fall_event') {
    handleFallEvent($input);
} else {
    switch ($method) {
        case 'POST':
            handleSensorData($input);
            break;
        case 'GET':
            handleGetRequests();
            break;
        default:
            sendError('Method not allowed', 405);
    }
}
// Handle direct fall event POST from ESP32
function handleFallEvent($data) {
    $pdo = getDBConnection();
    $requiredFields = ['user_id', 'device_id', 'acceleration_data', 'api_key'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            sendError("Missing required field: $field");
        }
    }
    // Validate API key
    if ($data['api_key'] !== API_KEY) {
        sendError('Invalid API key', 401);
    }
    $userId = $data['user_id'];
    $deviceId = $data['device_id'];
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    $confidence = $data['confidence_level'] ?? 0.95;
    $accelData = $data['acceleration_data'];
    try {
        $pdo->beginTransaction();
    // IMU readings are no longer saved to DB
        // Save fall event
        $stmt = $pdo->prepare("INSERT INTO fall_events (user_id, latitude, longitude, acceleration_data, confidence_level) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $latitude,
            $longitude,
            json_encode($accelData),
            $confidence
        ]);
        // Create alert
        createAlert($pdo, $userId, 'fall_detected', 'Fall detected by device', 'high', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'confidence' => $confidence
        ]);
        $pdo->commit();
        sendResponse(['status' => 'success', 'message' => 'Fall event saved']);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendError('Failed to save fall event: ' . $e->getMessage(), 500);
    }
}

function handleSensorData($data) {
    $pdo = getDBConnection();
    
    // Validate required fields
    $requiredFields = ['user_id', 'device_id'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            sendError("Missing required field: $field");
        }
    }
    
    $userId = $data['user_id'];
    $deviceId = $data['device_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Insert health readings only if values are present and non-zero
        $hr = isset($data['heart_rate']) ? intval($data['heart_rate']) : null;
        $spo2 = isset($data['spo2']) ? intval($data['spo2']) : null;
        $glucose = isset($data['glucose_level']) ? intval($data['glucose_level']) : null;
        if (($hr !== null && $hr > 0) || ($spo2 !== null && $spo2 > 0) || ($glucose !== null && $glucose > 0)) {
            $stmt = $pdo->prepare("
                INSERT INTO health_readings (user_id, heart_rate, spo2, glucose_level, device_id, reading_timestamp) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                ($hr !== null && $hr > 0) ? $hr : null,
                ($spo2 !== null && $spo2 > 0) ? $spo2 : null,
                ($glucose !== null && $glucose > 0) ? $glucose : null,
                $deviceId
            ]);
        }

        
        // Insert location data if provided
        if (isset($data['latitude']) && isset($data['longitude'])) {
            $stmt = $pdo->prepare("
                INSERT INTO locations (user_id, latitude, longitude, accuracy, speed, location_timestamp) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $data['latitude'],
                $data['longitude'],
                $data['accuracy'] ?? null,
                $data['speed'] ?? null
            ]);
            
            // Check if user is in safe zone
            checkSafeZone($pdo, $userId, $data['latitude'], $data['longitude']);
        }
        
        // Handle fall detection
        if (isset($data['fall_detected']) && $data['fall_detected'] === true) {
            $stmt = $pdo->prepare("
                INSERT INTO fall_events (user_id, latitude, longitude, acceleration_data, confidence_level) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                json_encode($data['acceleration_data'] ?? []),
                $data['confidence_level'] ?? 0.8
            ]);
            
            // Create fall alert
            createAlert($pdo, $userId, 'fall_detected', 'Potential fall detected', 'high', [
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'confidence' => $data['confidence_level'] ?? 0.8
            ]);
        }
        
        // Send data to AI analytics
        if (isset($data['heart_rate']) || isset($data['spo2']) || isset($data['glucose_level'])) {
            sendToAIAnalytics($data);
        }
        
        $pdo->commit();
        sendResponse(['status' => 'success', 'message' => 'Data received and processed']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        sendError('Failed to process data: ' . $e->getMessage(), 500);
    }
}

function handleGetRequests() {
    $action = $_GET['action'] ?? '';
    $userId = $_GET['user_id'] ?? '';
    
    if (!$userId) {
        sendError('User ID required');
    }
    
    switch ($action) {
        case 'medication_reminders':
            getMedicationReminders($userId);
            break;
        case 'health_data':
            getHealthData($userId);
            break;
        case 'alerts':
            getAlerts($userId);
            break;
        case 'get_time':
            sendResponse(['timestamp' => time()]);
            break;
        default:
            sendError('Invalid action');
    }
}

function getMedicationReminders($userId) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT mr.*, m.name, m.dosage, m.instructions 
        FROM medication_reminders mr 
        JOIN medications m ON mr.medication_id = m.id 
        WHERE mr.user_id = ? 
        AND mr.reminder_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 10 MINUTE)
        AND mr.is_taken = FALSE
        ORDER BY mr.reminder_time ASC
    ");
    $stmt->execute([$userId]);
    $reminders = $stmt->fetchAll();
    
    sendResponse(['reminders' => $reminders]);
}

function getHealthData($userId) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM health_readings 
        WHERE user_id = ? 
        ORDER BY reading_timestamp DESC 
        LIMIT 100
    ");
    $stmt->execute([$userId]);
    $readings = $stmt->fetchAll();
    
    sendResponse(['health_data' => $readings]);
}

function getAlerts($userId) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT * FROM alerts 
        WHERE user_id = ? 
        AND is_read = FALSE 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $alerts = $stmt->fetchAll();
    
    sendResponse(['alerts' => $alerts]);
}

function checkSafeZone($pdo, $userId, $lat, $lng) {
    $stmt = $pdo->prepare("
        SELECT * FROM safe_zones 
        WHERE user_id = ? AND is_active = TRUE
    ");
    $stmt->execute([$userId]);
    $zones = $stmt->fetchAll();
    
    $inSafeZone = false;
    foreach ($zones as $zone) {
        $distance = calculateDistance($lat, $lng, $zone['latitude'], $zone['longitude']);
        if ($distance <= $zone['radius']) {
            $inSafeZone = true;
            break;
        }
    }
    
    if (!$inSafeZone) {
        createAlert($pdo, $userId, 'geofence_exit', 'User has left all safe zones', 'medium', [
            'latitude' => $lat,
            'longitude' => $lng
        ]);
    }
}

function createAlert($pdo, $userId, $type, $message, $severity = 'medium', $metadata = []) {
    $stmt = $pdo->prepare("
        INSERT INTO alerts (user_id, alert_type, message, severity, metadata) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $type, $message, $severity, json_encode($metadata)]);
}

function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $earthRadius = 6371000; // meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

function sendToAIAnalytics($data) {
    $aiData = [
        'user_id' => $data['user_id'],
        'timestamp' => date('c'),
        'vitals' => [
            'heart_rate' => $data['heart_rate'] ?? null,
            'spo2' => $data['spo2'] ?? null,
            'glucose_level' => $data['glucose_level'] ?? null
        ]
    ];
    $ch = curl_init('https://ai.hackclub.com/api/analyze');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aiData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-Key: ' . API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200 && $response) {
        $aiResponse = json_decode($response, true);
        if (isset($aiResponse['alerts'])) {
            $pdo = getDBConnection();
            foreach ($aiResponse['alerts'] as $alert) {
                createAlert($pdo, $data['user_id'], 'ai_analysis', $alert['message'], $alert['severity'] ?? 'medium', $alert);
            }
        }
    }
}
?>