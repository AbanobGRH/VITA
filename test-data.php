<?php
session_start();
require_once 'api/config.php';

// Handle AJAX requests for adding test data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $userId = '550e8400-e29b-41d4-a716-446655440000';
    $deviceId = 'ESP32_VITA_001';
    $pdo = getDBConnection();
    
    try {
        switch ($_POST['action']) {
            case 'normal_data':
                // Insert normal health readings
                $stmt = $pdo->prepare("
                    INSERT INTO health_readings (user_id, heart_rate, spo2, glucose_level, device_id, reading_timestamp) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $userId,
                    rand(65, 85), // Normal heart rate
                    rand(95, 100), // Normal SpO2
                    rand(80, 120), // Normal glucose
                    $deviceId
                ]);
                
                // Insert normal location
                $stmt = $pdo->prepare("
                    INSERT INTO locations (user_id, latitude, longitude, accuracy, speed, location_timestamp) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $userId,
                    39.7392 + (rand(-100, 100) / 10000), // Small variation around home
                    -104.9903 + (rand(-100, 100) / 10000),
                    rand(3, 8),
                    rand(0, 5)
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Normal data added successfully']);
                break;
                
            case 'zero_data':
                // Insert zero/error readings
                $stmt = $pdo->prepare("
                    INSERT INTO health_readings (user_id, heart_rate, spo2, glucose_level, device_id, reading_timestamp) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $userId,
                    0, // Zero heart rate
                    0, // Zero SpO2
                    0, // Zero glucose
                    $deviceId
                ]);
                
                // Create alert for sensor malfunction
                $stmt = $pdo->prepare("
                    INSERT INTO alerts (user_id, alert_type, message, severity, metadata) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    'sensor_error',
                    'Sensor readings showing zero values - possible device malfunction',
                    'high',
                    json_encode(['heart_rate' => 0, 'spo2' => 0, 'glucose' => 0])
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Zero data and sensor error alert added']);
                break;
                
            case 'fall_alert':
                // Insert fall event
                $stmt = $pdo->prepare("
                    INSERT INTO fall_events (user_id, latitude, longitude, acceleration_data, confidence_level) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    39.7392,
                    -104.9903,
                    json_encode([
                        'x' => 15.2,
                        'y' => -8.7,
                        'z' => 22.1,
                        'magnitude' => 28.5
                    ]),
                    0.92
                ]);
                
                // Create fall alert
                $stmt = $pdo->prepare("
                    INSERT INTO alerts (user_id, alert_type, message, severity, metadata) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    'fall_detected',
                    'FALL DETECTED - Immediate attention required',
                    'high',
                    json_encode([
                        'confidence' => 0.92,
                        'location' => '39.7392, -104.9903',
                        'timestamp' => date('c')
                    ])
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Fall alert created successfully']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Data Generator - VITA</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span class="logo-text">VITA</span>
                </div>
                <button class="sidebar-close" id="sidebarClose">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                    </svg>
                    Dashboard
                </a></li>
                <li><a href="health-metrics.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    Health Metrics
                </a></li>
                <li><a href="location.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Location
                </a></li>
                <li><a href="medication.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4.5 16.5c-1.5 1.5-1.5 3.5 0 5s3.5 1.5 5 0l12-12c1.5-1.5 1.5-3.5 0-5s-3.5-1.5-5 0l-12 12z"></path>
                        <path d="M15 7l3 3"></path>
                    </svg>
                    Medication
                </a></li>
                <li><a href="alerts.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Alerts
                </a></li>
                <li><a href="profile.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profile
                </a></li>
                
                <li><a href="test-data.php" class="nav-link active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2z"></path>
                        <path d="M21 11h-4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2z"></path>
                        <path d="M15 2H9a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"></path>
                    </svg>
                    Test Data
                </a></li>
            </ul>
        </nav>

        <!-- Mobile Header -->
        <header class="mobile-header">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="mobile-logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span class="logo-text">VITA</span>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Test Data Generator</h1>
                <p>Generate test data for development and testing purposes</p>
            </div>

            <!-- Test Data Controls -->
            <div class="card">
                <div class="card-header">
                    <h2>Data Generation Controls</h2>
                    <div class="status-indicator online">
                        <div class="status-dot"></div>
                        <span>Ready</span>
                    </div>
                </div>
                
                <div class="test-controls">
                    <div class="control-section">
                        <h3>Health Data Simulation</h3>
                        <p>Generate different types of health readings for testing</p>
                        
                        <div class="test-buttons">
                            <button class="test-btn normal" onclick="sendTestData('normal_data')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <div>
                                    <span class="btn-title">Normal Data</span>
                                    <span class="btn-desc">HR: 65-85, SpO2: 95-100%, Glucose: 80-120</span>
                                </div>
                            </button>
                            
                            <button class="test-btn error" onclick="sendTestData('zero_data')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                    <line x1="12" y1="9" x2="12" y2="13"></line>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                                <div>
                                    <span class="btn-title">Zero/Error Data</span>
                                    <span class="btn-desc">All readings = 0 (sensor malfunction)</span>
                                </div>
                            </button>
                            
                            <button class="test-btn emergency" onclick="sendTestData('fall_alert')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                                </svg>
                                <div>
                                    <span class="btn-title">Fall Alert</span>
                                    <span class="btn-desc">Trigger emergency fall detection</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    
                    <div class="control-section">
                        <h3>Data Preview</h3>
                        <div class="data-preview" id="dataPreview">
                            <p>Click a button above to generate test data</p>
                        </div>
                    </div>
                    
                    <div class="control-section">
                        <h3>Auto-Refresh Status</h3>
                        <p>All pages now auto-refresh data every 5 seconds</p>
                        <div class="refresh-status">
                            <div class="status-item">
                                <span>Dashboard</span>
                                <span class="status-active">✓ Active</span>
                            </div>
                            <div class="status-item">
                                <span>Health Metrics</span>
                                <span class="status-active">✓ Active</span>
                            </div>
                            <div class="status-item">
                                <span>Alerts</span>
                                <span class="status-active">✓ Active</span>
                            </div>
                            <div class="status-item">
                                <span>Medication</span>
                                <span class="status-active">✓ Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Log -->
            <div class="card">
                <div class="card-header">
                    <h2>Activity Log</h2>
                    <button class="clear-log-btn" onclick="clearLog()">Clear Log</button>
                </div>
                
                <div class="activity-log" id="activityLog">
                    <div class="log-item">
                        <span class="log-time"><?= date('H:i:s') ?></span>
                        <span class="log-message">Test data generator initialized</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="assets/js/main.js"></script>
    <script>
        function sendTestData(action) {
            const button = event.target.closest('.test-btn');
            const originalContent = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = `
                <svg class="loading-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                <div>
                    <span class="btn-title">Sending...</span>
                    <span class="btn-desc">Please wait</span>
                </div>
            `;
            
            fetch('test-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                // Restore button
                button.disabled = false;
                button.innerHTML = originalContent;
                
                // Show result
                if (data.success) {
                    showNotification(data.message, 'success');
                    addToLog(data.message, 'success');
                    updateDataPreview(action, data);
                } else {
                    showNotification(data.message, 'error');
                    addToLog(data.message, 'error');
                }
            })
            .catch(error => {
                button.disabled = false;
                button.innerHTML = originalContent;
                showNotification('Network error: ' + error.message, 'error');
                addToLog('Network error: ' + error.message, 'error');
            });
        }
        
        function updateDataPreview(action, data) {
            const preview = document.getElementById('dataPreview');
            const now = new Date().toLocaleTimeString();
            
            let content = '';
            switch(action) {
                case 'normal_data':
                    content = `
                        <div class="preview-item success">
                            <h4>Normal Health Data Generated</h4>
                            <p>Heart Rate: 65-85 bpm | SpO2: 95-100% | Glucose: 80-120 mg/dL</p>
                            <small>Generated at ${now}</small>
                        </div>
                    `;
                    break;
                case 'zero_data':
                    content = `
                        <div class="preview-item error">
                            <h4>Error Data Generated</h4>
                            <p>All readings set to 0 (sensor malfunction simulation)</p>
                            <small>Generated at ${now}</small>
                        </div>
                    `;
                    break;
                case 'fall_alert':
                    content = `
                        <div class="preview-item emergency">
                            <h4>Fall Alert Triggered</h4>
                            <p>Emergency fall detection with 92% confidence</p>
                            <small>Generated at ${now}</small>
                        </div>
                    `;
                    break;
            }
            
            preview.innerHTML = content;
        }
        
        function addToLog(message, type) {
            const log = document.getElementById('activityLog');
            const now = new Date().toLocaleTimeString();
            
            const logItem = document.createElement('div');
            logItem.className = `log-item ${type}`;
            logItem.innerHTML = `
                <span class="log-time">${now}</span>
                <span class="log-message">${message}</span>
            `;
            
            log.insertBefore(logItem, log.firstChild);
            
            // Keep only last 10 items
            while (log.children.length > 10) {
                log.removeChild(log.lastChild);
            }
        }
        
        function clearLog() {
            const log = document.getElementById('activityLog');
            log.innerHTML = `
                <div class="log-item">
                    <span class="log-time">${new Date().toLocaleTimeString()}</span>
                    <span class="log-message">Activity log cleared</span>
                </div>
            `;
        }
        
        function showNotification(message, type) {
            if (window.VitaApp) {
                window.VitaApp.showNotification(message, type);
            }
        }
    </script>
    
    <style>
        .test-controls {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .control-section {
            padding: 1.5rem;
            border: 1px solid var(--vita-grey-light);
            border-radius: 1rem;
            background: var(--vita-white);
        }
        
        .control-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .control-section p {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .test-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .test-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            border: 2px solid;
            border-radius: 1rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
        }
        
        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft-lg);
        }
        
        .test-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .test-btn.normal {
            border-color: var(--vita-mint);
            color: var(--vita-mint-dark);
        }
        
        .test-btn.normal:hover {
            background: rgba(126, 214, 165, 0.05);
        }
        
        .test-btn.error {
            border-color: var(--orange-500);
            color: var(--orange-500);
        }
        
        .test-btn.error:hover {
            background: var(--orange-50);
        }
        
        .test-btn.emergency {
            border-color: var(--vita-coral);
            color: var(--vita-coral);
        }
        
        .test-btn.emergency:hover {
            background: rgba(231, 76, 60, 0.05);
        }
        
        .test-btn svg {
            width: 2rem;
            height: 2rem;
            flex-shrink: 0;
        }
        
        .btn-title {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .btn-desc {
            display: block;
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .data-preview {
            min-height: 4rem;
            padding: 1rem;
            background: var(--vita-grey-light);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .preview-item {
            padding: 1rem;
            border-radius: 0.5rem;
            border-left: 4px solid;
        }
        
        .preview-item.success {
            background: rgba(126, 214, 165, 0.1);
            border-left-color: var(--vita-mint);
        }
        
        .preview-item.error {
            background: var(--orange-50);
            border-left-color: var(--orange-500);
        }
        
        .preview-item.emergency {
            background: rgba(231, 76, 60, 0.1);
            border-left-color: var(--vita-coral);
        }
        
        .preview-item h4 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .preview-item p {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .preview-item small {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        
        .refresh-status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--vita-grey-light);
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }
        
        .status-active {
            color: var(--vita-mint-dark);
            font-weight: 500;
        }
        
        .activity-log {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .log-item {
            display: flex;
            gap: 1rem;
            padding: 0.75rem;
            border-bottom: 1px solid var(--vita-grey-light);
            font-size: 0.875rem;
        }
        
        .log-item:last-child {
            border-bottom: none;
        }
        
        .log-item.success {
            background: rgba(126, 214, 165, 0.05);
        }
        
        .log-item.error {
            background: rgba(231, 76, 60, 0.05);
        }
        
        .log-time {
            font-family: monospace;
            color: #6b7280;
            min-width: 4rem;
        }
        
        .log-message {
            flex: 1;
        }
        
        .clear-log-btn {
            font-size: 0.875rem;
            color: #6b7280;
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .clear-log-btn:hover {
            color: var(--vita-coral);
        }
        
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .test-buttons {
                grid-template-columns: 1fr;
            }
            
            .refresh-status {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>