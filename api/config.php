<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'adminn_db1');
define('DB_USER', 'adminn_db1'); // Change to your MariaDB username
define('DB_PASS', '&p@@a3Dk$amY@NSY#W'); // Change to your MariaDB password

// API configuration
define('API_KEY', 'vita_api_key_2024');
define('AUDIO_UPLOAD_PATH', __DIR__ . '/audio/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Content-Type: application/json');

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

// Create audio directory if it doesn't exist
if (!file_exists(AUDIO_UPLOAD_PATH)) {
    mkdir(AUDIO_UPLOAD_PATH, 0755, true);
}
?>