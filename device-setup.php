
<?php
session_start();
require_once 'api/config.php';

$userId = '550e8400-e29b-41d4-a716-446655440000';
$pdo = getDBConnection();

// Ensure devices table exists (create if not)
$pdo->exec("CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    serial_number VARCHAR(32) NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_device (user_id, serial_number)
)");

// Handle new device form submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['serial_number'])) {
    $serial = trim($_POST['serial_number']);
    if (!preg_match('/^VITAMD\\d{10}$/', $serial)) {
        $error = 'Serial number is incorrect.';
    } else {
        // Check if already registered for this user
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = ? AND serial_number = ?");
        $stmt->execute([$userId, $serial]);
        if ($stmt->fetch()) {
            $error = 'This device is already registered to your account.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO devices (user_id, serial_number) VALUES (?, ?)");
            if ($stmt->execute([$userId, $serial])) {
                $message = 'Device with serial number ' . htmlspecialchars($serial) . ' added successfully.';
            } else {
                $error = 'Failed to add device. Please try again.';
            }
        }
    }
}

// Get current device id for user (most recently registered)
$stmt = $pdo->prepare("SELECT serial_number FROM devices WHERE user_id = ? ORDER BY registered_at DESC LIMIT 1");
$stmt->execute([$userId]);
$row = $stmt->fetch();
$currentDeviceId = $row ? htmlspecialchars($row['serial_number']) : 'No device registered';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Setup - VITA</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <li><a href="device-setup.php" class="nav-link active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Device Setup
                </a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content" style="max-width: 500px; margin: 0 auto;">
            <h1 style="margin-bottom: 2rem;">Device Setup</h1>
            <div class="card">
                <h2>Current Device ID</h2>
                <p class="device-id-value">
                    <?= $currentDeviceId ?>
                </p>
            </div>
            <div class="card" style="margin-top: 2em;">
                <h2>Add New Device</h2>
                <form method="post" action="" style="margin-top: 1em;">
                    <label for="serial_number" style="font-weight: 500;">Device Serial Number:</label><br>
                    <input type="text" id="serial_number" name="serial_number" required class="input-full" placeholder="VITAMD1234567890">
                    <button type="submit" class="setup-btn" style="margin-top: 1em;">Add Device</button>
                </form>
                <?php if ($message): ?>
                    <div class="success-message">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert" style="margin-top: 1em; color: red;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
            </div>
</html>

<style>
    .device-id-value {
        font-size: 1.2em;
        color: #333;
        font-weight: bold;
        margin-top: 1em;
        margin-bottom: 1em;
    }
    .input-full {
        width: 100%;
        padding: 0.5em;
        margin: 1em 0;
        border: 1px solid #d6d9df;
        border-radius: 0.5em;
        font-size: 1em;
        box-sizing: border-box;
    }
    .setup-btn {
        background: var(--vita-blue);
        color: #fff;
        border: none;
        border-radius: 0.75em;
        padding: 0.75em 1.5em;
        font-size: 1em;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .setup-btn:hover {
        background: var(--vita-blue-dark);
    }
    .alert {
        background: #ffeaea;
        border: 1px solid #e74c3c;
        color: #e74c3c;
        border-radius: 0.5em;
        padding: 0.75em 1em;
        font-size: 0.95em;
    }
</style>
        </main>
    </div>
</body>
    <script>
        // Auto-refresh data every 5 seconds
        setInterval(function() {
            location.reload();
        }, 5000);
    </script>
</html>