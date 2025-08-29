<?php
session_start();
require_once 'api/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// Get user data
$userId = '550e8400-e29b-41d4-a716-446655440000';
$pdo = getDBConnection();

// Get health readings for the selected period
$period = $_GET['period'] ?? 'week';
$limit = 100;

switch ($period) {
    case 'day':
        $dateFilter = "DATE(reading_timestamp) = CURDATE()";
        break;
    case 'week':
        $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        break;
    case 'month':
        $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
    case 'year':
        $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        break;
    default:
        $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
}

$stmt = $pdo->prepare("
    SELECT * FROM health_readings 
    WHERE user_id = ? AND $dateFilter
    ORDER BY reading_timestamp DESC 
    LIMIT $limit
");
$stmt->execute([$userId]);
$readings = $stmt->fetchAll();

// Get latest reading
$latestReading = $readings[0] ?? null;

// Calculate averages
$avgHeartRate = 0;
$avgSpo2 = 0;
$avgGlucose = 0;

if (!empty($readings)) {
    $totalHR = array_sum(array_column($readings, 'heart_rate'));
    $totalSpo2 = array_sum(array_column($readings, 'spo2'));
    $totalGlucose = array_sum(array_column($readings, 'glucose_level'));
    $count = count($readings);
    
    $avgHeartRate = round($totalHR / $count);
    $avgSpo2 = round($totalSpo2 / $count);
    $avgGlucose = round($totalGlucose / $count);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Metrics - VITA</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <li><a href="health-metrics.php" class="nav-link active">
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
                <h1>Health Metrics</h1>
                <p>Comprehensive health monitoring and trends</p>
                <form id="aiAnalysisForm" method="get" action="sendtoai.php" target="_blank" style="display:flex;align-items:center;gap:1em;">
                    <select name="period" id="aiPeriodSelect" class="ai-analysis-btn" style="padding:0.5em 1em;font-size:1em;">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last2days">Last 2 Days</option>
                        <option value="week" selected>Last Week</option>
                        <option value="month">Last Month</option>
                    </select>
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
                    <button type="submit" class="ai-analysis-btn" style="margin-left:0;">AI Analysis</button>
                </form>
            </div>

            <!-- Metrics Overview -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-content">
                        <div class="metric-info">
                            <p class="metric-label">Heart Rate</p>
                            <p class="metric-value"><?= $latestReading ? $latestReading['heart_rate'] : '0' ?> <span class="metric-unit">bpm</span></p>
                            <p class="metric-average">Avg: <?= $avgHeartRate ?> bpm</p>
                        </div>
                        <div class="metric-icon heart">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-content">
                        <div class="metric-info">
                            <p class="metric-label">Blood Glucose</p>
                            <p class="metric-value"><?= $latestReading ? $latestReading['glucose_level'] : '0' ?> <span class="metric-unit">mg/dL</span></p>
                            <p class="metric-average">Avg: <?= $avgGlucose ?> mg/dL</p>
                        </div>
                        <div class="metric-icon glucose">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10,9 9,9 8,9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-content">
                        <div class="metric-info">
                            <p class="metric-label">Blood Oxygen (SpO2)</p>
                            <p class="metric-value"><?= $latestReading ? $latestReading['spo2'] : '0' ?> <span class="metric-unit">%</span></p>
                            <p class="metric-average">Avg: <?= $avgSpo2 ?>%</p>
                        </div>
                        <div class="metric-icon oxygen">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M7 19a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"></path>
                                <path d="M17 19a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"></path>
                                <path d="M12 19a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"></path>
                                <path d="M7 13a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"></path>
                                <path d="M17 13a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Detailed View -->
            <div class="dashboard-grid">
             
                <!-- Recent Readings -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Readings</h2>
                        <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="readings-list">
                        <?php foreach (array_slice($readings, 0, 5) as $reading): ?>
                            <div class="reading-item">
                                <div class="reading-header">
                                    <span class="reading-time"><?= date('g:i A', strtotime($reading['reading_timestamp'])) ?></span>
                                    <span class="reading-date"><?= date('M j', strtotime($reading['reading_timestamp'])) ?></span>
                                </div>
                                <div class="reading-metrics">
                                    <div class="reading-metric">
                                        <span class="metric-name">HR:</span>
                                        <span class="metric-val"><?= $reading['heart_rate'] ?> bpm</span>
                                    </div>
                                    <div class="reading-metric">
                                        <span class="metric-name">SpO2:</span>
                                        <span class="metric-val"><?= $reading['spo2'] ?>%</span>
                                    </div>
                                    <div class="reading-metric">
                                        <span class="metric-name">Glucose:</span>
                                        <span class="metric-val"><?= $reading['glucose_level'] ?> mg/dL</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

           
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/health-metrics.js"></script>
    <script>
    // No JS needed for AI Analysis form, handled by sendtoai.php

    // If this is a fetch for prompt, return formatted prompt and exit
    <?php if (isset($_GET['period']) && isset($_GET['as']) && $_GET['as'] === 'prompt') : ?>
        <?php
        // Compose a prompt for AI including all user profile fields
        $periodLabel = [
            'today' => 'today',
            'yesterday' => 'yesterday',
            'last2days' => 'the last 2 days',
            'week' => 'the last week',
            'month' => 'the last month'
        ][$_GET['period']] ?? $_GET['period'];
        // Fetch user profile
        $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        $profile = '';
        if ($user) {
            $profile .= "Patient Profile:\n";
            foreach ($user as $k => $v) {
                $profile .= ucfirst(str_replace('_', ' ', $k)) . ': ' . (is_null($v) ? 'N/A' : $v) . "\n";
            }
        }
        // Build SQL filter for the selected period
        $period = $_GET['period'];
        switch ($period) {
            case 'today':
                $dateFilter = "DATE(reading_timestamp) = CURDATE()";
                break;
            case 'yesterday':
                $dateFilter = "DATE(reading_timestamp) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                break;
            case 'last2days':
                $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
                break;
            case 'week':
                $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            default:
                $dateFilter = "reading_timestamp >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        }
        $sql = "SELECT * FROM health_readings WHERE user_id = ? AND $dateFilter ORDER BY reading_timestamp DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $allReadings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $prompt = $profile . "\nAnalyze the following health data for this patient for $periodLabel.\n";
        foreach ($allReadings as $r) {
            $prompt .= "Time: {$r['reading_timestamp']}, HR: {$r['heart_rate']} bpm, SpO2: {$r['spo2']}%, Glucose: {$r['glucose_level']} mg/dL\n";
        }
        // Output only the prompt, no HTML or JS
        header('Content-Type: text/plain; charset=UTF-8');
        echo $prompt;
        exit;
        ?>
    <?php endif; ?>

    // Auto-refresh data every 5 seconds
    setInterval(function() {
        location.reload();
    }, 5000);
    </script>
    <script src="assets/js/ai-analytics.js"></script>
</body>
</html>