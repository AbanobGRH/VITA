
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'api/config.php';

// Get user data
$userId = '550e8400-e29b-41d4-a716-446655440000';
$pdo = getDBConnection();

// Handle delete medication POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_medication'])) {
    $delete_id = trim($_POST['delete_id'] ?? '');
    if ($delete_id) {
        $stmt = $pdo->prepare("DELETE FROM medications WHERE id = ? AND user_id = ?");
        $stmt->execute([$delete_id, $userId]);
        header('Location: medication.php');
        exit;
    }
}
// Handle add medication POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medication'])) {
    $name = trim($_POST['name'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');
    $times = trim($_POST['times'] ?? '');
    $condition_for = trim($_POST['condition_for'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    // Generate UUID for id
    function generate_uuid() {
        // Generate a version 4 UUID
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    if ($name && $dosage && $frequency && $times && $condition_for) {
        $id = generate_uuid();
        // Generate TTS audio for medication name
        $audio_filename = "tts_" . md5($userId . $name) . ".mp3";
        $audio_filepath = __DIR__ . "/api/audio/" . $audio_filename;
        generate_medication_tts($name, $audio_filepath);
        $stmt = $pdo->prepare("INSERT INTO medications (id, user_id, name, dosage, frequency, times, condition_for, instructions, is_active, audio_file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, ?)");
        $stmt->execute([$id, $userId, $name, $dosage, $frequency, $times, $condition_for, $instructions, $audio_filename]);
        header('Location: medication.php');
        exit;
    } else {
        $add_error = 'Please fill in all required fields.';
    }
}

// Handle edit medication POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_medication'])) {
    $edit_id = intval($_POST['edit_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');
    $times = trim($_POST['times'] ?? '');
    $condition_for = trim($_POST['condition_for'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    if ($edit_id && $name && $dosage && $frequency && $times && $condition_for) {
        // Generate TTS audio for medication name
        $audio_filename = "tts_" . md5($userId . $name) . ".mp3";
        $audio_filepath = __DIR__ . "/api/audio/" . $audio_filename;
        generate_medication_tts($name, $audio_filepath);
        $stmt = $pdo->prepare("UPDATE medications SET name=?, dosage=?, frequency=?, times=?, condition_for=?, instructions=?, audio_file_path=? WHERE id=? AND user_id=?");
        $stmt->execute([$name, $dosage, $frequency, $times, $condition_for, $instructions, $audio_filename, $edit_id, $userId]);
        header('Location: medication.php');
        exit;
    } else {
        $edit_error = 'Please fill in all required fields.';
    }
}

// Get medications
$stmt = $pdo->prepare("SELECT * FROM medications WHERE user_id = ? AND is_active = TRUE ORDER BY name");
$stmt->execute([$userId]);
$medications = $stmt->fetchAll();

// TTS generation function
function generate_medication_tts($text, $filepath) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = "powershell -Command \"Add-Type -AssemblyName System.Speech; $speak = New-Object System.Speech.Synthesis.SpeechSynthesizer; $speak.SetOutputToWaveFile('$filepath'); $speak.Speak('$text'); $speak.Dispose()\"";
        exec($command);
    } else {
        $wavPath = str_replace('.mp3', '.wav', $filepath);
        $command = "espeak -s 150 -v en+f3 -w " . escapeshellarg($wavPath) . " " . escapeshellarg($text);
        exec($command);
        if (file_exists($wavPath)) {
            exec("ffmpeg -y -i " . escapeshellarg($wavPath) . " " . escapeshellarg($filepath));
            unlink($wavPath);
        }
    }
    return file_exists($filepath);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication Manager - VITA</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Enhanced Modal Styling */
        .modal-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal-popup.active {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
        }
        
        .modal-popup-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }
        
        .modal-popup-content {
            position: relative;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-popup.active .modal-popup-content {
            transform: scale(1);
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .modal-close:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        /* Enhanced Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-group .input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #fafafa;
            box-sizing: border-box;
        }
        
        .form-group .input:focus {
            outline: none;
            border-color: var(--vita-blue);
            background: white;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }
        
        .form-group textarea.input {
            resize: vertical;
            min-height: 80px;
        }
        
        /* Enhanced Button Styling */
        .med-action-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .med-action-btn.add {
            background: linear-gradient(135deg, var(--vita-blue), #3b82f6);
            color: white;
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }
        
        .med-action-btn.add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
        }
        
        .med-action-btn.edit {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .med-action-btn.edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .med-action-btn.delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .med-action-btn.delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        /* Error Message Styling */
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        /* Form Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-grid .form-group {
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-popup-content {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
        
        /* Enhanced Medication Item Styling */
        .medication-item {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        
        .medication-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: var(--vita-blue);
        }
        
        .medication-icon {
            background: linear-gradient(135deg, var(--vita-blue), #3b82f6);
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }
        
        .medication-icon svg {
            color: white !important;
        }
        
        .medication-condition {
            background: linear-gradient(135deg, rgba(74,144,226,0.1), rgba(59,130,246,0.1));
            border: 1px solid rgba(74,144,226,0.2);
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
                <li><a href="location.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Location
                </a></li>
                <li><a href="medication.php" class="nav-link active">
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
                <h1>Medication Manager</h1>
                <p>Track medications, schedules, and adherence</p>
            </div>


            <!-- Medication List and Adherence -->
            <div class="dashboard-grid">
                <!-- Active Medications -->
                <div class="card">
                    <div class="card-header">
                        <h2>Active Medications</h2>
                        <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4.5 16.5c-1.5 1.5-1.5 3.5 0 5s3.5 1.5 5 0l12-12c1.5-1.5 1.5-3.5 0-5s-3.5-1.5-5 0l-12 12z"></path>
                            <path d="M15 7l3 3"></path>
                        </svg>
                    <button id="addMedicationBtn" class="med-action-btn add" style="float:right; margin-top:-2.2em; margin-right:0.5em;">+ Add Medication</button>
                    </div>
                    
                    <div class="medications-list" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <?php foreach ($medications as $medication): ?>
                            <div class="medication-item" style="display: flex; align-items: center; background: var(--vita-grey-light); border-radius: 1rem; padding: 1.25rem 1.5rem; box-shadow: var(--shadow-soft); border: 1px solid var(--vita-grey-light); gap: 1.25rem;">
                                <div class="medication-icon <?= strtolower(str_replace(' ', '-', $medication['condition_for'])) ?>" style="background: var(--vita-white); border-radius: 1rem; padding: 0.75rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-soft);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 2rem; height: 2rem; color: var(--vita-blue);">
                                        <path d="M4.5 16.5c-1.5 1.5-1.5 3.5 0 5s3.5 1.5 5 0l12-12c1.5-1.5 1.5-3.5 0-5s-3.5-1.5-5 0l-12 12z"></path>
                                        <path d="M15 7l3 3"></path>
                                    </svg>
                                </div>
                                <div class="medication-details" style="flex:1; display:flex; flex-direction:column; gap:0.25rem;">
                                    <h3 style="font-size:1.1rem; font-weight:600; color:#1f2937; margin-bottom:0;"><?= htmlspecialchars($medication['name']) ?></h3>
                                    <div style="color:#6b7280; font-size:0.95rem; margin-bottom:0;"><?= htmlspecialchars($medication['dosage']) ?> <span style="color:#b8bcc4;">•</span> <?= htmlspecialchars($medication['frequency']) ?></div>
                                    <span class="medication-condition" style="font-size:0.9rem; color:var(--vita-blue); background:rgba(74,144,226,0.08); border-radius:0.5rem; padding:0.15em 0.7em; display:inline-block; width:fit-content; margin-top:0.2em;"><?= htmlspecialchars($medication['condition_for']) ?></span>
                                </div>
                                <div class="medication-actions" style="margin-left:1rem; display:flex; gap:0.5em;">
                                    <button class="med-action-btn edit" style="padding:0.5em 1.2em;" 
                                        data-id="<?= $medication['id'] ?>"
                                        data-name="<?= htmlspecialchars($medication['name'], ENT_QUOTES) ?>"
                                        data-dosage="<?= htmlspecialchars($medication['dosage'], ENT_QUOTES) ?>"
                                        data-frequency="<?= htmlspecialchars($medication['frequency'], ENT_QUOTES) ?>"
                                        data-times="<?= htmlspecialchars($medication['times'], ENT_QUOTES) ?>"
                                        data-condition_for="<?= htmlspecialchars($medication['condition_for'], ENT_QUOTES) ?>"
                                        data-instructions="<?= htmlspecialchars($medication['instructions'], ENT_QUOTES) ?>"
                                    >Edit</button>
                                    <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete this medication?');" style="display:inline;">
                                        <input type="hidden" name="delete_medication" value="1">
                                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars($medication['id'], ENT_QUOTES) ?>">
                                        <button type="submit" class="med-action-btn delete" style="color:#e74c3c; padding:0.5em 1.2em;">Delete</button>
                                    </form>
                                </div>
    <!-- Edit Medication Popup -->
    <div id="editMedicationModal" class="modal-popup" role="dialog" aria-modal="true" aria-labelledby="editMedicationTitle" tabindex="-1">
        <div class="modal-popup-backdrop"></div>
        <div class="modal-popup-content" role="document">
            <button id="closeEditMedicationModal" class="modal-close" aria-label="Close">&times;</button>
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width: 28px; height: 28px;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </div>
                <h2 id="editMedicationTitle" style="margin: 0; color: #1f2937; font-size: 1.5rem; font-weight: 700;">Edit Medication</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0; font-size: 0.95rem;">Update the medication details below</p>
            </div>
            
            <?php if (!empty($edit_error)): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= htmlspecialchars($edit_error) ?>
                </div>
            <?php endif; ?>
            
            <form id="editMedicationForm" method="post" action="">
                <input type="hidden" name="edit_medication" value="1">
                <input type="hidden" name="edit_id" id="edit_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: #10b981;">
                                <path d="M4.5 16.5c-1.5 1.5-1.5 3.5 0 5s3.5 1.5 5 0l12-12c1.5-1.5 1.5-3.5 0-5s-3.5-1.5-5 0l-12 12z"></path>
                            </svg>
                            Medication Name
                        </label>
                        <input type="text" name="name" id="edit_name" class="input" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: #10b981;">
                                <path d="M20 6L9 17l-5-5"></path>
                            </svg>
                            Dosage
                        </label>
                        <input type="text" name="dosage" id="edit_dosage" class="input" required>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: #10b981;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            Frequency
                        </label>
                        <input type="text" name="frequency" id="edit_frequency" class="input" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: #10b981;">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
                            </svg>
                            Times
                        </label>
                        <input type="text" name="times" id="edit_times" class="input" placeholder="08:00, 20:00" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: #10b981;">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        Condition
                    </label>
                    <input type="text" name="condition_for" id="edit_condition_for" class="input" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: #10b981;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10,9 9,9 8,9"></polyline>
                        </svg>
                        Special Instructions (Optional)
                    </label>
                    <textarea name="instructions" id="edit_instructions" class="input" rows="3"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeEditPopup()" style="flex: 1; padding: 0.75rem; border: 2px solid #e5e7eb; background: white; color: #6b7280; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                        Cancel
                    </button>
                    <button type="submit" class="med-action-btn edit" style="flex: 2;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($medications)): ?>
                            <div style="padding: 1em; color: #888; background: var(--vita-grey-light); border-radius: 1rem; text-align:center;">No active medications found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Refill Reminders -->
            
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Add Medication Popup -->
    <div id="addMedicationModal" class="modal-popup" role="dialog" aria-modal="true" aria-labelledby="addMedicationTitle" tabindex="-1">
        <div class="modal-popup-backdrop"></div>
        <div class="modal-popup-content" role="document">
            <button id="closeAddMedicationModal" class="modal-close" aria-label="Close">&times;</button>
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: linear-gradient(135deg, var(--vita-blue), #3b82f6); border-radius: 50%; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width: 28px; height: 28px;">
                        <path d="M4.5 16.5c-1.5 1.5-1.5 3.5 0 5s3.5 1.5 5 0l12-12c1.5-1.5 1.5-3.5 0-5s-3.5-1.5-5 0l-12 12z"></path>
                        <path d="M15 7l3 3"></path>
                    </svg>
                </div>
                <h2 id="addMedicationTitle" style="margin: 0; color: #1f2937; font-size: 1.5rem; font-weight: 700;">Add New Medication</h2>
                <p style="color: #6b7280; margin: 0.5rem 0 0 0; font-size: 0.95rem;">Fill in the details below to add a new medication to your schedule</p>
            </div>
            
            <?php if (!empty($add_error)): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?= htmlspecialchars($add_error) ?>
                </div>
            <?php endif; ?>
            
            <form id="addMedicationForm" method="post" action="">
                <input type="hidden" name="add_medication" value="1">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: var(--vita-blue);">
                                <path d="M4.5 16.5c-1.5 1.5-1.5 3.5 0 5s3.5 1.5 5 0l12-12c1.5-1.5 1.5-3.5 0-5s-3.5-1.5-5 0l-12 12z"></path>
                            </svg>
                            Medication Name
                        </label>
                        <input type="text" name="name" class="input" placeholder="e.g. Aspirin" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: var(--vita-blue);">
                                <path d="M20 6L9 17l-5-5"></path>
                            </svg>
                            Dosage
                        </label>
                        <input type="text" name="dosage" class="input" placeholder="e.g. 100mg" required>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: var(--vita-blue);">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            Frequency
                        </label>
                        <input type="text" name="frequency" class="input" placeholder="e.g. Twice daily" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: var(--vita-blue);">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
                            </svg>
                            Times
                        </label>
                        <input type="text" name="times" class="input" placeholder="08:00, 20:00" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: var(--vita-blue);">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        Condition
                    </label>
                    <input type="text" name="condition_for" class="input" placeholder="e.g. Blood pressure" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem; color: var(--vita-blue);">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10,9 9,9 8,9"></polyline>
                        </svg>
                        Special Instructions (Optional)
                    </label>
                    <textarea name="instructions" class="input" rows="3" placeholder="Take with food, avoid alcohol, etc."></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closePopup()" style="flex: 1; padding: 0.75rem; border: 2px solid #e5e7eb; background: white; color: #6b7280; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                        Cancel
                    </button>
                    <button type="submit" class="med-action-btn add" style="flex: 2;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Medication
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/medication.js"></script>
    <script>
    // Popup logic for Add Medication
    const addMedicationModal = document.getElementById('addMedicationModal');
    const addMedicationBtn = document.getElementById('addMedicationBtn');
    const closeAddMedicationModal = document.getElementById('closeAddMedicationModal');
    const modalBackdrop = addMedicationModal.querySelector('.modal-popup-backdrop');
    function openPopup() {
        addMedicationModal.classList.add('active');
        addMedicationModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            addMedicationModal.querySelector('.modal-popup-content').focus();
        }, 100);
    }
    function closePopup() {
        addMedicationModal.classList.remove('active');
        addMedicationModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
    addMedicationBtn.onclick = openPopup;
    closeAddMedicationModal.onclick = closePopup;
    modalBackdrop.onclick = closePopup;
    document.addEventListener('keydown', function(e) {
        if (addMedicationModal.classList.contains('active') && e.key === 'Escape') closePopup();
    });

    // Popup logic for Edit Medication
    const editMedicationModal = document.getElementById('editMedicationModal');
    const closeEditMedicationModal = document.getElementById('closeEditMedicationModal');
    const editModalBackdrop = editMedicationModal.querySelector('.modal-popup-backdrop');
    const editForm = document.getElementById('editMedicationForm');
    function openEditPopup() {
        editMedicationModal.classList.add('active');
        editMedicationModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            editMedicationModal.querySelector('.modal-popup-content').focus();
        }, 100);
    }
    function closeEditPopup() {
        editMedicationModal.classList.remove('active');
        editMedicationModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
    closeEditMedicationModal.onclick = closeEditPopup;
    editModalBackdrop.onclick = closeEditPopup;
    document.addEventListener('keydown', function(e) {
        if (editMedicationModal.classList.contains('active') && e.key === 'Escape') closeEditPopup();
    });
    // Attach to all edit buttons
    document.querySelectorAll('.med-action-btn.edit').forEach(function(btn) {
        btn.onclick = function(e) {
            e.preventDefault();
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_dosage').value = btn.getAttribute('data-dosage');
            document.getElementById('edit_frequency').value = btn.getAttribute('data-frequency');
            document.getElementById('edit_times').value = btn.getAttribute('data-times');
            document.getElementById('edit_condition_for').value = btn.getAttribute('data-condition_for');
            document.getElementById('edit_instructions').value = btn.getAttribute('data-instructions');
            openEditPopup();
        };
    });
    </script>
    <script>
        // Auto-refresh data every 5 seconds
        setInterval(function() {
            location.reload();
        }, 5000);
    </script>
</body>
</html>