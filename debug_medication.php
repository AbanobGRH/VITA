<?php
// Debug script for medication addition
session_start();
require_once 'api/config.php';

echo "<h1>Medication Addition Debug</h1>";

// Get user data
$userId = '550e8400-e29b-41d4-a716-446655440000';

try {
    $pdo = getDBConnection();
    echo "✓ Database connection successful<br><br>";
    
    // Test if we can generate a UUID
    $testId = generateUUID();
    echo "Generated UUID: {$testId}<br>";
    echo "UUID Length: " . strlen($testId) . "<br><br>";
    
    // Check if medications table exists and structure
    echo "<h2>Database Table Check</h2>";
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'medications'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✓ medications table exists<br>";
        
        // Check columns
        $stmt = $pdo->prepare("DESCRIBE medications");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        echo "Table structure:<br>";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}<br>";
        }
    } else {
        echo "✗ medications table does not exist<br>";
    }
    
    echo "<br><h2>Test Medication Insert (without TTS)</h2>";
    
    // Test data
    $name = 'Test Medication ' . date('H:i:s');
    $dosage = '10mg';
    $frequency = 'Daily';
    $times = '09:00';
    $condition_for = 'Test Condition';
    $instructions = 'Test instructions';
    $audio_filename = "tts_" . md5($userId . $name) . ".mp3";
    
    echo "Test data:<br>";
    echo "- Name: {$name}<br>";
    echo "- Dosage: {$dosage}<br>";
    echo "- Frequency: {$frequency}<br>";
    echo "- Times: {$times}<br>";
    echo "- Condition: {$condition_for}<br>";
    echo "- Instructions: {$instructions}<br>";
    echo "- Audio filename: {$audio_filename}<br><br>";
    
    // Try insert without TTS
    try {
        $id = generateUUID();
        echo "Attempting insert with ID: {$id}<br>";
        
        $sql = "INSERT INTO medications (id, user_id, name, dosage, frequency, times, condition_for, instructions, is_active, audio_file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id, $userId, $name, $dosage, $frequency, $times, $condition_for, $instructions, $audio_filename]);
        
        if ($result) {
            echo "✓ Insert successful!<br>";
            echo "Affected rows: " . $stmt->rowCount() . "<br>";
            
            // Verify the insert
            $stmt = $pdo->prepare("SELECT * FROM medications WHERE id = ?");
            $stmt->execute([$id]);
            $inserted = $stmt->fetch();
            
            if ($inserted) {
                echo "✓ Record verified in database:<br>";
                echo "- ID: {$inserted['id']}<br>";
                echo "- Name: {$inserted['name']}<br>";
                echo "- User ID: {$inserted['user_id']}<br>";
            } else {
                echo "✗ Record not found after insert<br>";
            }
            
            // Clean up test data
            $stmt = $pdo->prepare("DELETE FROM medications WHERE id = ?");
            $stmt->execute([$id]);
            echo "✓ Test data cleaned up<br>";
        } else {
            echo "✗ Insert failed but no exception thrown<br>";
        }
        
    } catch (PDOException $e) {
        echo "✗ Database error: " . $e->getMessage() . "<br>";
        echo "Error code: " . $e->getCode() . "<br>";
        echo "SQL State: " . $e->errorInfo[0] . "<br>";
    }
    
    echo "<br><h2>Test TTS Function</h2>";
    
    // Test TTS function separately
    $testAudioPath = __DIR__ . "/api/audio/test_tts.mp3";
    echo "Testing TTS generation...<br>";
    echo "Audio path: {$testAudioPath}<br>";
    
    // Create audio directory if it doesn't exist
    $audioDir = dirname($testAudioPath);
    if (!is_dir($audioDir)) {
        if (mkdir($audioDir, 0755, true)) {
            echo "✓ Created audio directory<br>";
        } else {
            echo "✗ Failed to create audio directory<br>";
        }
    } else {
        echo "✓ Audio directory exists<br>";
    }
    
    // Simple TTS test function
    function test_tts($text, $filepath) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "powershell -Command \"Add-Type -AssemblyName System.Speech; \$speak = New-Object System.Speech.Synthesis.SpeechSynthesizer; \$speak.SetOutputToWaveFile('$filepath'); \$speak.Speak('$text'); \$speak.Dispose()\"";
            exec($command, $output, $return_var);
            return ['success' => file_exists($filepath), 'output' => $output, 'return_code' => $return_var];
        } else {
            return ['success' => false, 'output' => ['TTS not implemented for non-Windows'], 'return_code' => 1];
        }
    }
    
    $ttsResult = test_tts("Test medication", $testAudioPath);
    echo "TTS Result:<br>";
    echo "- Success: " . ($ttsResult['success'] ? 'Yes' : 'No') . "<br>";
    echo "- Return code: {$ttsResult['return_code']}<br>";
    echo "- Output: " . implode('<br>', $ttsResult['output']) . "<br>";
    
    if (file_exists($testAudioPath)) {
        echo "✓ TTS file created successfully<br>";
        unlink($testAudioPath); // Clean up
    } else {
        echo "✗ TTS file not created<br>";
    }
    
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><h2>Recommendations</h2>";
echo "1. Check if the form data is being submitted correctly<br>";
echo "2. Verify all required fields are filled<br>";
echo "3. Check if TTS generation is causing delays or failures<br>";
echo "4. Review error logs for any hidden issues<br>";
?>

<form method="POST" action="" style="margin-top: 20px; padding: 20px; border: 1px solid #ccc;">
    <h3>Quick Test Form</h3>
    <input type="text" name="name" placeholder="Medication Name" required><br><br>
    <input type="text" name="dosage" placeholder="Dosage" required><br><br>
    <input type="text" name="frequency" placeholder="Frequency" required><br><br>
    <input type="text" name="times" placeholder="Times" required><br><br>
    <input type="text" name="condition_for" placeholder="Condition" required><br><br>
    <textarea name="instructions" placeholder="Instructions"></textarea><br><br>
    <button type="submit" name="test_add">Test Add Medication</button>
</form>

<?php
// Handle test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_add'])) {
    echo "<h2>Test Form Submission Result</h2>";
    
    $name = trim($_POST['name'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');
    $times = trim($_POST['times'] ?? '');
    $condition_for = trim($_POST['condition_for'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    
    echo "Received data:<br>";
    echo "- Name: '{$name}'<br>";
    echo "- Dosage: '{$dosage}'<br>";
    echo "- Frequency: '{$frequency}'<br>";
    echo "- Times: '{$times}'<br>";
    echo "- Condition: '{$condition_for}'<br>";
    echo "- Instructions: '{$instructions}'<br>";
    
    if ($name && $dosage && $frequency && $times && $condition_for) {
        echo "✓ All required fields filled<br>";
        
        try {
            $id = generateUUID();
            $audio_filename = "tts_" . md5($userId . $name) . ".mp3";
            
            $stmt = $pdo->prepare("INSERT INTO medications (id, user_id, name, dosage, frequency, times, condition_for, instructions, is_active, audio_file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, ?)");
            $result = $stmt->execute([$id, $userId, $name, $dosage, $frequency, $times, $condition_for, $instructions, $audio_filename]);
            
            if ($result) {
                echo "✓ Medication added successfully!<br>";
                echo "ID: {$id}<br>";
            }
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "✗ Missing required fields<br>";
    }
}
?>
