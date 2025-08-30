<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'admins2_db1');
define('DB_USER', 'admins2_db1'); // Change to your MariaDB username
define('DB_PASS', '&p@@a3Dk$amY@NSY#W'); // Change to your MariaDB password

// API configuration
define('API_KEY', 'vita_api_key_2024');
define('AUDIO_UPLOAD_PATH', __DIR__ . '/audio/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }
}

// Validate API key
function validateApiKey() {
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? $_GET['api_key'] ?? '';
    
    if ($apiKey !== API_KEY) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit();
    }
}

// Utility functions
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function sendError($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit();
}

// Generate a proper UUID v4
function generateUUID() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Create audio directory if it doesn't exist
if (!file_exists(AUDIO_UPLOAD_PATH)) {
    mkdir(AUDIO_UPLOAD_PATH, 0755, true);
}
?>