<?php
require_once 'config.php';

validateApiKey();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['esp_next'])) {
            getNextReminderForESP();
        } else {
            getMedicationAudioFiles();
        }
        break;
    default:
        sendError('Method not allowed', 405);
}

// Get next medication reminder for ESP32 device
function getNextReminderForESP() {
    $userId = $_GET['user_id'] ?? '';
    if (!$userId) sendError('User ID required');
    
    $pdo = getDBConnection();
    
    // Get the next medication with audio file
    $stmt = $pdo->prepare("
        SELECT id, name, dosage, audio_file_path, times 
        FROM medications 
        WHERE user_id = ? AND is_active = 1 AND audio_file_path IS NOT NULL 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $medication = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($medication && $medication['audio_file_path']) {
        $audioPath = AUDIO_UPLOAD_PATH . $medication['audio_file_path'];
        
        if (file_exists($audioPath)) {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/audio/' . $medication['audio_file_path'];
            sendResponse([
                'medication_id' => $medication['id'],
                'medication' => $medication['name'],
                'dosage' => $medication['dosage'],
                'times' => $medication['times'],
                'audio_url' => $url,
                'filename' => $medication['audio_file_path']
            ]);
        } else {
            sendError('Audio file not found');
        }
    } else {
        sendResponse([
            'medication_id' => null,
            'medication' => null,
            'audio_url' => null
        ]);
    }
}

function getMedicationAudioFiles() {
    $userId = $_GET['user_id'] ?? '';
    
    if (!$userId) {
        sendError('User ID required');
    }
    
    $pdo = getDBConnection();
    
    // Get all medications with audio files for the user
    $stmt = $pdo->prepare("
        SELECT id, name, dosage, frequency, times, audio_file_path, created_at
        FROM medications 
        WHERE user_id = ? 
        AND is_active = 1
        AND audio_file_path IS NOT NULL
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'medications' => [],
        'current_time' => date('Y-m-d H:i:s')
    ];
    
    foreach ($medications as $medication) {
        $audioPath = AUDIO_UPLOAD_PATH . $medication['audio_file_path'];
        
        if (file_exists($audioPath)) {
            $response['medications'][] = [
                'id' => $medication['id'],
                'name' => $medication['name'],
                'dosage' => $medication['dosage'],
                'frequency' => $medication['frequency'],
                'times' => $medication['times'],
                'filename' => $medication['audio_file_path'],
                'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/api/audio/' . $medication['audio_file_path'],
                'created_at' => $medication['created_at']
            ];
        }
    }
    
    sendResponse($response);
}

// Serve audio files
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = AUDIO_UPLOAD_PATH . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: inline; filename="' . $filename . '"');
        readfile($filepath);
        exit();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
    }
}
?>