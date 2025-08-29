<?php
// sendtoai.php: Collects user info and readings for a period, then POSTs to aitest.php
session_start();
require_once 'api/config.php';

// Get user id and period from GET or POST
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? '550e8400-e29b-41d4-a716-446655440000';
$period = $_GET['period'] ?? $_POST['period'] ?? 'week';

$pdo = getDBConnection();

// Fetch user profile
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Build SQL filter for the selected period
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

// Compose prompt
$periodLabel = [
    'today' => 'today',
    'yesterday' => 'yesterday',
    'last2days' => 'the last 2 days',
    'week' => 'the last week',
    'month' => 'the last month'
][$period] ?? $period;

$profile = '';
if ($user) {
    $profile .= "Patient Profile:\n";
    foreach ($user as $k => $v) {
        $profile .= ucfirst(str_replace('_', ' ', $k)) . ': ' . (is_null($v) ? 'N/A' : $v) . "\n";
    }
}
$prompt = $profile . "\nAnalyze the following health data for this patient for $periodLabel.\n";
foreach ($allReadings as $r) {
    $prompt .= "Time: {$r['reading_timestamp']}, HR: {$r['heart_rate']} bpm, SpO2: {$r['spo2']}%, Glucose: {$r['glucose_level']} mg/dL\n";
}

// Send to aitest.php via POST
echo '<form id="fwdform" method="post" action="aitest.php">';
echo '<input type="hidden" name="period" value="' . htmlspecialchars($period) . '">';
echo '<input type="hidden" name="prompt" value="' . htmlspecialchars($prompt) . '">';
echo '</form>';
echo '<script>document.getElementById("fwdform").submit();</script>';
exit;
