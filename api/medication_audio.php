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

// Removed POST TTS generation. TTS is now handled in medication.php when saving medication.
// For ESP: Get next medication reminder time and audio file
function getNextReminderForESP() {
    $userId = $_GET['user_id'] ?? '';
    if (!$userId) sendError('User ID required');
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT mr.*, m.name FROM medication_reminders mr JOIN medications m ON mr.medication_id = m.id WHERE mr.user_id = ? AND mr.reminder_time > NOW() AND mr.is_taken = FALSE ORDER BY mr.reminder_time ASC LIMIT 1");
    $stmt->execute([$userId]);
    $reminder = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($reminder) {
        $filename = "tts_" . md5($userId . $reminder['name']) . ".mp3";
        $filepath = AUDIO_UPLOAD_PATH . $filename;
        if (!file_exists($filepath)) {
            generateTTSAudio($reminder['name'], $filepath);
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/audio/' . $filename;
        sendResponse([
            'reminder_time' => $reminder['reminder_time'],
            'medication' => $reminder['name'],
            'audio_url' => $url,
            'filename' => $filename
        ]);
    } else {
        sendResponse(['reminder_time' => null, 'medication' => null, 'audio_url' => null]);
    }
}

function getMedicationAudioFiles() {
    $userId = $_GET['user_id'] ?? '';
    $lastCheck = $_GET['last_check'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));
    
    if (!$userId) {
        sendError('User ID required');
    }
    
    $pdo = getDBConnection();
    
    // Get new audio files since last check
    $stmt = $pdo->prepare("
        SELECT mr.*, m.name, m.dosage 
        FROM medication_reminders mr 
        JOIN medications m ON mr.medication_id = m.id 
        WHERE mr.user_id = ? 
        AND mr.audio_file_path IS NOT NULL
        AND mr.created_at > ?
        AND mr.is_taken = FALSE
        ORDER BY mr.reminder_time ASC
    ");
    $stmt->execute([$userId, $lastCheck]);
    $newFiles = $stmt->fetchAll();
    
    $response = [
        'new_files' => [],
        'current_time' => date('Y-m-d H:i:s')
    ];
    
    foreach ($newFiles as $file) {
        if (file_exists(AUDIO_UPLOAD_PATH . $file['audio_file_path'])) {
            $response['new_files'][] = [
                'reminder_id' => $file['id'],
                'filename' => $file['audio_file_path'],
                'reminder_time' => $file['reminder_time'],
                'medication' => $file['name'],
                'dosage' => $file['dosage'],
                'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/api/audio/' . $file['audio_file_path']
            ];
        }
    }
    
    sendResponse($response);
}

function generateTTSAudio($text, $filepath) {
    // For Windows, use SAPI
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = "powershell -Command \"Add-Type -AssemblyName System.Speech; $speak = New-Object System.Speech.Synthesis.SpeechSynthesizer; $speak.SetOutputToWaveFile('$filepath'); $speak.Speak('$text'); $speak.Dispose()\"";
        exec($command, $output, $returnCode);
        return file_exists($filepath);
    } else {
        // Linux: espeak to WAV, then ffmpeg to MP3
        $wavPath = str_replace('.mp3', '.wav', $filepath);
        $command = "espeak -s 150 -v en+f3 -w " . escapeshellarg($wavPath) . " " . escapeshellarg($text);
        exec($command, $output, $returnCode);
        if ($returnCode === 0 && file_exists($wavPath)) {
            exec("ffmpeg -y -i " . escapeshellarg($wavPath) . " " . escapeshellarg($filepath), $output2, $returnCode2);
            if ($returnCode2 === 0 && file_exists($filepath)) {
                unlink($wavPath);
                return true;
            }
        }
        return file_exists($filepath);
    }
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