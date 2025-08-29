<?php
session_start();
require_once 'api/config.php';

// Function to calculate distance between two points (Haversine formula)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Earth radius in meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

// Get user data
$userId = '550e8400-e29b-41d4-a716-446655440000';
$pdo = getDBConnection();

// Get latest location from database
$stmt = $pdo->prepare("SELECT * FROM locations WHERE user_id = ? ORDER BY location_timestamp DESC LIMIT 1");
$stmt->execute([$userId]);
$latestLocation = $stmt->fetch();

// If no location data exists, use default location
if (!$latestLocation) {
    $latestLocation = [
        'latitude' => 39.7392,
        'longitude' => -104.9903,
        'accuracy' => 3.0,
        'location_timestamp' => date('Y-m-d H:i:s'),
        'is_safe_zone' => false,
        'zone_name' => null
    ];
}

// Get safe zones
$stmt = $pdo->prepare("SELECT * FROM safe_zones WHERE user_id = ? AND is_active = TRUE ORDER BY name");
$stmt->execute([$userId]);
$safeZones = $stmt->fetchAll();

// Check if current location is in any safe zone
$currentSafeZone = null;
foreach ($safeZones as $zone) {
    $distance = calculateDistance(
        $latestLocation['latitude'], 
        $latestLocation['longitude'],
        $zone['latitude'], 
        $zone['longitude']
    );
    
    if ($distance <= $zone['radius']) {
        $currentSafeZone = $zone;
        $latestLocation['is_safe_zone'] = true;
        $latestLocation['zone_name'] = $zone['name'];
        break;
    }
}

// Get location history (last 24 hours)
$stmt = $pdo->prepare("SELECT * FROM locations WHERE user_id = ? AND location_timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY location_timestamp DESC LIMIT 10");
$stmt->execute([$userId]);
$locationHistory = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Tracking - VITA</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
    <style>
        .map-container {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .location-card {
            margin-bottom: 20px;
        }
        .location-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .location-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                <li><a href="location.php" class="nav-link active">
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
                <h1>Location Tracking</h1>
                <p>Real-time location monitoring and safety zones</p>
            </div>

            <!-- Current Location -->
            <div class="card location-card">
                <div class="card-header">
                    <h2>Current Location</h2>
                    <div class="status-indicator online">
                        <div class="status-dot"></div>
                        <span>Live</span>
                    </div>
                </div>
                
                <div class="location-content">
                    <!-- Real Map -->
                    <div id="map" class="map-container"></div>

                    <!-- Location Details -->
                    <div class="location-details">
                        <div class="current-location-info">
                            <h3>Current Position</h3>
                            <div class="location-stats">
                                <div class="stat-item">
                                    <p class="stat-label">Coordinates</p>
                                    <p class="stat-value">
                                        <?= number_format($latestLocation['latitude'], 6) ?>, <?= number_format($latestLocation['longitude'], 6) ?>
                                    </p>
                                </div>
                                <div class="stat-item">
                                    <p class="stat-label">Accuracy</p>
                                    <p class="stat-value">
                                        ±<?= $latestLocation['accuracy'] ?> meters
                                    </p>
                                </div>
                                <?php if ($latestLocation['speed']): ?>
                                <div class="stat-item">
                                    <p class="stat-label">Speed</p>
                                    <p class="stat-value">
                                        <?= number_format($latestLocation['speed'], 1) ?> km/h
                                    </p>
                                </div>
                                <?php endif; ?>
                                <div class="stat-item">
                                    <p class="stat-label">Last Update</p>
                                    <p class="stat-value">
                                        <?= date('M j, Y g:i A', strtotime($latestLocation['location_timestamp'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="safety-status">
                           
                        </div>
                </div>
            </div>

            <!-- Safe Zones and Location History -->
            <div class="dashboard-grid">
                <!-- Safe Zones -->
                
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Location History -->
                <div class="card">
                    <div class="card-header">
                        <h2>Location History</h2>
                        <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
                        </svg>
                    </div>
                    
                    <div class="history-list">
                        <?php if (empty($locationHistory)): ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
                                </svg>
                                <p>No recent location history</p>
                                <p class="empty-subtitle">Location data will appear here as it's collected</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($locationHistory as $location): ?>
                                <div class="history-item <?= $location['is_safe_zone'] ? 'safe' : 'neutral' ?>">
                                    <div class="history-status"></div>
                                    <div class="history-info">
                                        <div class="history-header">
                                            <span class="history-location">
                                                <?= $location['zone_name'] ? htmlspecialchars($location['zone_name']) : 'Unknown Location' ?>
                                            </span>
                                            <span class="history-time">
                                                <?= date('g:i A', strtotime($location['location_timestamp'])) ?>
                                            </span>
                                        </div>
                                        <p class="history-details">
                                            <?= number_format($location['latitude'], 4) ?>, <?= number_format($location['longitude'], 4) ?>
                                            • Accuracy: ±<?= $location['accuracy'] ?>m
                                            <?php if ($location['speed']): ?>
                                                • Speed: <?= number_format($location['speed'], 1) ?> km/h
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/location.js"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([<?= $latestLocation['latitude'] ?>, <?= $latestLocation['longitude'] ?>], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add current location marker
        const currentLocationIcon = L.divIcon({
            className: 'current-location-marker',
            html: '<div style="background: #4CAF50; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
        
        const currentLocationMarker = L.marker([<?= $latestLocation['latitude'] ?>, <?= $latestLocation['longitude'] ?>], {
            icon: currentLocationIcon
        }).addTo(map);
        
        currentLocationMarker.bindPopup(`
            <strong>Current Location</strong><br>
            Coordinates: <?= number_format($latestLocation['latitude'], 6) ?>, <?= number_format($latestLocation['longitude'], 6) ?><br>
            Accuracy: ±<?= $latestLocation['accuracy'] ?>m<br>
            Last Update: <?= date('M j, Y g:i A', strtotime($latestLocation['location_timestamp'])) ?>
        `);
        
        // Add accuracy circle
        L.circle([<?= $latestLocation['latitude'] ?>, <?= $latestLocation['longitude'] ?>], {
            color: '#4CAF50',
            fillColor: '#4CAF50',
            fillOpacity: 0.1,
            radius: <?= $latestLocation['accuracy'] ?>
        }).addTo(map);
        
        // Add safe zones to map
        <?php foreach ($safeZones as $zone): ?>
        const safeZoneCircle_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zone['id']) ?> = L.circle([<?= $zone['latitude'] ?>, <?= $zone['longitude'] ?>], {
            color: '#2196F3',
            fillColor: '#2196F3',
            fillOpacity: 0.2,
            radius: <?= $zone['radius'] ?>
        }).addTo(map);
        
        const safeZoneMarker_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zone['id']) ?> = L.marker([<?= $zone['latitude'] ?>, <?= $zone['longitude'] ?>], {
            icon: L.divIcon({
                className: 'safe-zone-marker',
                html: '<div style="background: #2196F3; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            })
        }).addTo(map);
        
        safeZoneMarker_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zone['id']) ?>.bindPopup(`
            <strong><?= htmlspecialchars($zone['name']) ?></strong><br>
            Safe Zone - <?= $zone['radius'] ?>m radius<br>
            Coordinates: <?= number_format($zone['latitude'], 6) ?>, <?= number_format($zone['longitude'], 6) ?>
        `);
        <?php endforeach; ?>
        
        // Add location history trail
        <?php if (count($locationHistory) > 1): ?>
        const historyCoordinates = [
            <?php foreach ($locationHistory as $location): ?>
            [<?= $location['latitude'] ?>, <?= $location['longitude'] ?>],
            <?php endforeach; ?>
        ];
        
        const historyPolyline = L.polyline(historyCoordinates, {
            color: '#FF9800',
            weight: 3,
            opacity: 0.7,
            dashArray: '5, 10'
        }).addTo(map);
        <?php endif; ?>
        
        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>