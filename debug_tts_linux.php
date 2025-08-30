<?php
// Debug TTS issues on Linux server
require_once 'api/config.php';

echo "<h1>TTS Debug - Linux Server</h1>";

// Test if espeak is available
echo "<h2>1. Check espeak availability</h2>";
exec("which espeak 2>/dev/null", $espeak_path, $espeak_return);
echo "Espeak path check - Return code: {$espeak_return}<br>";
if ($espeak_return === 0) {
    echo "Espeak found at: " . implode('<br>', $espeak_path) . "<br>";
} else {
    echo "Espeak NOT found<br>";
}

// Test espeak version
exec("espeak --version 2>/dev/null", $version_output, $version_return);
echo "Espeak version check - Return code: {$version_return}<br>";
echo "Version output: " . implode('<br>', $version_output) . "<br><br>";

// Test if ffmpeg is available
echo "<h2>2. Check ffmpeg availability</h2>";
exec("which ffmpeg 2>/dev/null", $ffmpeg_path, $ffmpeg_return);
echo "FFmpeg path check - Return code: {$ffmpeg_return}<br>";
if ($ffmpeg_return === 0) {
    echo "FFmpeg found at: " . implode('<br>', $ffmpeg_path) . "<br>";
} else {
    echo "FFmpeg NOT found<br>";
}

// Test basic espeak command
echo "<h2>3. Test basic espeak command</h2>";
$audioDir = __DIR__ . '/api/audio/';
$testWav = $audioDir . 'test_basic.wav';
$command = "espeak -s 150 'Hello World' -w " . escapeshellarg($testWav) . " 2>&1";
echo "Command: " . htmlspecialchars($command) . "<br>";
exec($command, $output, $return_var);
echo "Return code: {$return_var}<br>";
echo "Output: " . implode('<br>', $output) . "<br>";
echo "WAV file created: " . (file_exists($testWav) ? 'YES' : 'NO') . "<br>";
if (file_exists($testWav)) {
    echo "WAV file size: " . filesize($testWav) . " bytes<br>";
}

// Alternative TTS methods
echo "<h2>4. Alternative TTS Solutions</h2>";

// Method 1: Use festival if available
exec("which festival 2>/dev/null", $festival_path, $festival_return);
echo "Festival TTS - Return code: {$festival_return}<br>";
if ($festival_return === 0) {
    echo "Festival found at: " . implode('<br>', $festival_path) . "<br>";
}

// Method 2: Use flite if available
exec("which flite 2>/dev/null", $flite_path, $flite_return);
echo "Flite TTS - Return code: {$flite_return}<br>";
if ($flite_return === 0) {
    echo "Flite found at: " . implode('<br>', $flite_path) . "<br>";
}

// Method 3: Use Google TTS API (requires internet)
echo "<h2>5. Simplified TTS Function</h2>";

function generateSimpleTTS($text, $filepath) {
    $audioDir = dirname($filepath);
    $basename = basename($filepath, '.mp3');
    $wavPath = $audioDir . '/' . $basename . '.wav';
    
    // Try multiple TTS engines
    $commands = [
        // espeak with different parameters
        "espeak -s 150 -a 200 " . escapeshellarg($text) . " -w " . escapeshellarg($wavPath),
        "espeak " . escapeshellarg($text) . " -w " . escapeshellarg($wavPath),
        // festival
        "echo " . escapeshellarg($text) . " | festival --tts --otype wav > " . escapeshellarg($wavPath),
        // flite
        "flite -t " . escapeshellarg($text) . " -o " . escapeshellarg($wavPath),
    ];
    
    foreach ($commands as $i => $command) {
        echo "Trying method " . ($i + 1) . ": " . htmlspecialchars($command) . "<br>";
        exec($command . " 2>&1", $output, $return_var);
        echo "Return code: {$return_var}<br>";
        echo "Output: " . implode(' ', $output) . "<br>";
        
        if (file_exists($wavPath) && filesize($wavPath) > 0) {
            echo "✓ WAV file created successfully!<br>";
            
            // Convert to MP3 if ffmpeg is available
            if ($return_var === 0) {
                $mp3_command = "ffmpeg -y -i " . escapeshellarg($wavPath) . " " . escapeshellarg($filepath) . " 2>&1";
                exec($mp3_command, $mp3_output, $mp3_return);
                if ($mp3_return === 0 && file_exists($filepath)) {
                    unlink($wavPath);
                    echo "✓ MP3 file created successfully!<br>";
                    return true;
                } else {
                    // Keep WAV file if MP3 conversion fails
                    echo "MP3 conversion failed, keeping WAV file<br>";
                    return true;
                }
            }
        } else {
            echo "✗ Method failed<br>";
        }
        echo "<br>";
    }
    
    return false;
}

// Test the simplified function
$testText = "Test medication reminder";
$testFile = $audioDir . 'test_simple.mp3';
echo "Testing simplified TTS function...<br>";
$result = generateSimpleTTS($testText, $testFile);

if ($result) {
    echo "<h2>Success! TTS file created</h2>";
    if (file_exists($testFile)) {
        echo "<audio controls><source src='api/audio/test_simple.mp3' type='audio/mpeg'></audio><br>";
    } else {
        // Check for WAV file
        $wavFile = str_replace('.mp3', '.wav', $testFile);
        if (file_exists($wavFile)) {
            echo "<audio controls><source src='api/audio/test_simple.wav' type='audio/wav'></audio><br>";
        }
    }
} else {
    echo "<h2>All TTS methods failed</h2>";
    echo "Consider installing: sudo apt-get install espeak espeak-data libespeak-dev ffmpeg<br>";
}
?>
