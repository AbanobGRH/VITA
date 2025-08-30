// Client-side TTS fallback for VITA
// This script handles TTS when server-side TTS fails

class VitaTTS {
    constructor() {
        this.synthesis = window.speechSynthesis;
        this.isSupported = 'speechSynthesis' in window;
        this.voices = [];
        this.loadVoices();
    }

    loadVoices() {
        if (this.isSupported) {
            this.voices = this.synthesis.getVoices();
            
            // Chrome loads voices asynchronously
            if (this.voices.length === 0) {
                this.synthesis.addEventListener('voiceschanged', () => {
                    this.voices = this.synthesis.getVoices();
                });
            }
        }
    }

    speak(text, options = {}) {
        if (!this.isSupported) {
            console.warn('Speech synthesis not supported in this browser');
            return false;
        }

        // Cancel any ongoing speech
        this.synthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        
        // Set voice preferences
        const preferredVoice = this.voices.find(voice => 
            voice.lang.startsWith('en') && voice.name.includes('Female')
        ) || this.voices.find(voice => voice.lang.startsWith('en'));
        
        if (preferredVoice) {
            utterance.voice = preferredVoice;
        }

        // Configure speech parameters
        utterance.rate = options.rate || 0.9;
        utterance.pitch = options.pitch || 1.0;
        utterance.volume = options.volume || 0.8;

        // Add event listeners
        utterance.onstart = () => {
            console.log('TTS started:', text);
        };

        utterance.onend = () => {
            console.log('TTS completed:', text);
        };

        utterance.onerror = (event) => {
            console.error('TTS error:', event.error);
        };

        this.synthesis.speak(utterance);
        return true;
    }

    // Method to handle medication reminders
    speakMedication(medicationName) {
        const message = `Time to take your ${medicationName}`;
        return this.speak(message);
    }

    // Method to check for TTS data files and speak them
    async checkAndSpeakTTSFiles() {
        try {
            // Check for any .json TTS files that need to be spoken
            const response = await fetch('/api/audio/check_tts.php');
            const ttsData = await response.json();
            
            if (ttsData.files && ttsData.files.length > 0) {
                for (const file of ttsData.files) {
                    this.speak(file.text);
                    
                    // Mark file as spoken
                    await fetch('/api/audio/mark_spoken.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({file: file.name})
                    });
                }
            }
        } catch (error) {
            console.error('Error checking TTS files:', error);
        }
    }
}

// Initialize TTS when page loads
let vitaTTS;
document.addEventListener('DOMContentLoaded', function() {
    vitaTTS = new VitaTTS();
    
    // Check for pending TTS every 30 seconds
    setInterval(() => {
        vitaTTS.checkAndSpeakTTSFiles();
    }, 30000);
    
    // Add TTS buttons to medication items
    addTTSButtonsToMedications();
});

function addTTSButtonsToMedications() {
    const medicationItems = document.querySelectorAll('.medication-item, .medication-card');
    
    medicationItems.forEach(item => {
        const medicationName = item.querySelector('h3, .medication-name, [data-medication-name]');
        if (medicationName && !item.querySelector('.tts-button')) {
            const ttsButton = document.createElement('button');
            ttsButton.className = 'tts-button';
            ttsButton.innerHTML = '🔊';
            ttsButton.title = 'Speak medication name';
            ttsButton.style.cssText = `
                background: #4CAF50;
                color: white;
                border: none;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                cursor: pointer;
                margin-left: 10px;
                font-size: 14px;
            `;
            
            ttsButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const name = medicationName.textContent.trim();
                vitaTTS.speakMedication(name);
            });
            
            medicationName.appendChild(ttsButton);
        }
    });
}

// Test function for TTS
function testTTS() {
    if (vitaTTS) {
        vitaTTS.speak('This is a test of the text to speech system');
    } else {
        alert('TTS not initialized');
    }
}
