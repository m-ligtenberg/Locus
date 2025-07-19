/**
 * TravelBot v3.0 - Spraakcommando's (Web Speech API)
 * Ondersteunt spraakherkenning in verschillende talen
 */

class SpeechManager {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.isSupported = false;
        this.currentLanguage = 'nl-NL';
        this.onCommandCallback = null;
        this.onErrorCallback = null;
        this.onStatusCallback = null;
        
        this.init();
    }
    
    init() {
        // Check for Speech Recognition support
        this.checkSupport();
        
        if (this.isSupported) {
            this.setupRecognition();
        }
    }
    
    checkSupport() {
        // Check for various implementations of Speech Recognition
        window.SpeechRecognition = window.SpeechRecognition || 
                                  window.webkitSpeechRecognition || 
                                  window.mozSpeechRecognition || 
                                  window.msSpeechRecognition;
        
        this.isSupported = !!window.SpeechRecognition;
        
        if (!this.isSupported) {
            console.warn('Speech Recognition niet ondersteund in deze browser');
            this.showFallbackMessage();
        }
        
        return this.isSupported;
    }
    
    setupRecognition() {
        this.recognition = new window.SpeechRecognition();
        
        // Configure recognition settings
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.maxAlternatives = 3;
        this.recognition.lang = this.currentLanguage;
        
        // Event handlers
        this.recognition.onstart = () => {
            this.isListening = true;
            this.updateUI('listening');
            this.showStatus('Luisteren naar commando\'s...');
            console.log('Spraakherkenning gestart');
        };
        
        this.recognition.onend = () => {
            this.isListening = false;
            this.updateUI('idle');
            this.showStatus('Spraakherkenning gestopt');
            console.log('Spraakherkenning gestopt');
        };
        
        this.recognition.onresult = (event) => {
            this.handleResults(event);
        };
        
        this.recognition.onerror = (event) => {
            this.handleError(event);
        };
        
        this.recognition.onnomatch = () => {
            this.showStatus('Geen commando herkend, probeer opnieuw');
            if (this.onErrorCallback) {
                this.onErrorCallback('no_match', 'Geen geldig commando herkend');
            }
        };
    }
    
    handleResults(event) {
        const results = event.results;
        const lastResult = results[results.length - 1];
        
        if (lastResult.isFinal) {
            const transcript = lastResult[0].transcript.toLowerCase().trim();
            const confidence = lastResult[0].confidence;
            
            console.log('Herkend commando:', transcript, 'Betrouwbaarheid:', confidence);
            
            // Show what was recognized
            this.showStatus(`Herkend: "${transcript}"`);
            
            // Process the command
            this.processCommand(transcript, confidence);
            
            // Alternative results for debugging
            if (results.length > 1) {
                const alternatives = Array.from(results[results.length - 1])
                    .map(result => `"${result.transcript}" (${(result.confidence * 100).toFixed(1)}%)`)
                    .join(', ');
                console.log('Alternatieve resultaten:', alternatives);
            }
        }
    }
    
    processCommand(transcript, confidence) {
        // Define command patterns with multi-language support
        const commands = this.getCommandPatterns();
        
        let matchedCommand = null;
        let matchedPattern = null;
        
        // Try to match against known patterns
        for (const [pattern, command] of Object.entries(commands)) {
            const regex = new RegExp(pattern, 'i');
            if (regex.test(transcript)) {
                matchedCommand = command;
                matchedPattern = pattern;
                break;
            }
        }
        
        if (matchedCommand) {
            console.log('Commando gevonden:', matchedCommand.action);
            this.executeCommand(matchedCommand, transcript);
        } else {
            // Try fuzzy matching for common mistakes
            const fuzzyMatch = this.fuzzyMatchCommand(transcript, commands);
            if (fuzzyMatch) {
                this.executeCommand(fuzzyMatch, transcript);
            } else {
                this.showStatus('Onbekend commando. Zeg "help" voor beschikbare commando\'s.');
                if (this.onErrorCallback) {
                    this.onErrorCallback('unknown_command', transcript);
                }
            }
        }
    }
    
    getCommandPatterns() {
        const currentLang = this.getCurrentLanguageCode();
        
        const patterns = {
            'nl': {
                '(waar ben ik|locatie|positie)': {
                    action: 'get_location',
                    description: 'Vraag huidige locatie'
                },
                '(wat zegt de|vraag de) (belg|amsterdammer|brabander|jordanees)': {
                    action: 'ask_persona',
                    description: 'Vraag persona om reactie'
                },
                '(zeg iets|wees) (cynisch|sarcastisch|droog)': {
                    action: 'cynical_response',
                    description: 'Vraag cynische opmerking'
                },
                '(nieuwe? (opmerking|commentaar)|nieuwe reactie)': {
                    action: 'new_comment',
                    description: 'Genereer nieuwe opmerking'
                },
                '(help|hulp|wat kan je|commando\'s)': {
                    action: 'help',
                    description: 'Toon beschikbare commando\'s'
                },
                '(stop|stil|pauze)': {
                    action: 'stop_listening',
                    description: 'Stop spraakherkenning'
                },
                '(luider|harder|volume omhoog)': {
                    action: 'volume_up',
                    description: 'Verhoog volume'
                },
                '(zachter|stiller|volume omlaag)': {
                    action: 'volume_down',
                    description: 'Verlaag volume'
                }
            },
            'en': {
                '(where am i|location|position)': {
                    action: 'get_location',
                    description: 'Get current location'
                },
                '(what does the|ask the) (belgian|amsterdammer|brabander|jordanees)': {
                    action: 'ask_persona',
                    description: 'Ask persona for response'
                },
                '(say something|be) (cynical|sarcastic|dry)': {
                    action: 'cynical_response',
                    description: 'Request cynical comment'
                },
                '(new (comment|remark)|new response)': {
                    action: 'new_comment',
                    description: 'Generate new comment'
                },
                '(help|what can you do|commands)': {
                    action: 'help',
                    description: 'Show available commands'
                },
                '(stop|quiet|pause)': {
                    action: 'stop_listening',
                    description: 'Stop speech recognition'
                }
            }
        };
        
        return patterns[currentLang] || patterns['nl'];
    }
    
    fuzzyMatchCommand(transcript, commands) {
        // Simple fuzzy matching based on keyword presence
        const words = transcript.split(' ');
        
        for (const [pattern, command] of Object.entries(commands)) {
            const patternWords = pattern.toLowerCase().replace(/[()|\[\]]/g, ' ').split(/\s+/);
            let matches = 0;
            
            for (const word of words) {
                if (patternWords.some(pw => pw.includes(word) || word.includes(pw))) {
                    matches++;
                }
            }
            
            // If more than half the words match, consider it a match
            if (matches >= Math.max(1, Math.floor(words.length / 2))) {
                console.log('Fuzzy match gevonden:', command.action);
                return command;
            }
        }
        
        return null;
    }
    
    executeCommand(command, originalTranscript) {
        if (this.onCommandCallback) {
            this.onCommandCallback(command.action, {
                transcript: originalTranscript,
                command: command
            });
        }
        
        // Built-in command handling
        switch (command.action) {
            case 'help':
                this.showHelp();
                break;
            case 'stop_listening':
                this.stop();
                break;
            case 'volume_up':
                this.adjustVolume(0.1);
                break;
            case 'volume_down':
                this.adjustVolume(-0.1);
                break;
            default:
                // Pass to main app for handling
                break;
        }
    }
    
    handleError(event) {
        console.error('Spraakherkenning fout:', event.error);
        
        const errorMessages = {
            'no-speech': 'Geen spraak gedetecteerd. Probeer opnieuw.',
            'audio-capture': 'Microfoon toegang geweigerd.',
            'not-allowed': 'Microfoon permissie geweigerd.',
            'network': 'Netwerkfout bij spraakherkenning.',
            'service-not-allowed': 'Spraakservice niet toegestaan.',
            'bad-grammar': 'Grammar probleem bij spraakherkenning.',
            'language-not-supported': 'Taal niet ondersteund voor spraakherkenning.'
        };
        
        const message = errorMessages[event.error] || `Spraakfout: ${event.error}`;
        this.showStatus(message);
        
        if (this.onErrorCallback) {
            this.onErrorCallback(event.error, message);
        }
        
        this.isListening = false;
        this.updateUI('error');
    }
    
    start() {
        if (!this.isSupported) {
            this.showStatus('Spraakherkenning niet ondersteund');
            return false;
        }
        
        if (this.isListening) {
            console.log('Spraakherkenning al actief');
            return false;
        }
        
        try {
            this.recognition.start();
            return true;
        } catch (error) {
            console.error('Kan spraakherkenning niet starten:', error);
            this.showStatus('Kan spraakherkenning niet starten');
            return false;
        }
    }
    
    stop() {
        if (this.recognition && this.isListening) {
            this.recognition.stop();
        }
    }
    
    toggle() {
        if (this.isListening) {
            this.stop();
        } else {
            this.start();
        }
    }
    
    setLanguage(language) {
        this.currentLanguage = language;
        if (this.recognition) {
            this.recognition.lang = language;
        }
    }
    
    getCurrentLanguageCode() {
        return this.currentLanguage.substring(0, 2);
    }
    
    // Callback setters
    onCommand(callback) {
        this.onCommandCallback = callback;
    }
    
    onError(callback) {
        this.onErrorCallback = callback;
    }
    
    onStatus(callback) {
        this.onStatusCallback = callback;
    }
    
    // UI Methods
    updateUI(state) {
        const voiceBtn = document.getElementById('voice-toggle');
        const voiceInterface = document.getElementById('voice-interface');
        const voiceAnimation = document.getElementById('voice-animation');
        const voiceStatus = document.getElementById('voice-status');
        
        if (voiceBtn) {
            switch (state) {
                case 'listening':
                    voiceBtn.textContent = '🛑';
                    voiceBtn.title = 'Stop luisteren';
                    voiceBtn.classList.add('listening');
                    break;
                case 'idle':
                case 'error':
                default:
                    voiceBtn.textContent = '🎤';
                    voiceBtn.title = 'Start spraakcommando\'s';
                    voiceBtn.classList.remove('listening');
                    break;
            }
        }
        
        if (voiceInterface) {
            if (state === 'listening') {
                voiceInterface.classList.remove('hidden');
            } else {
                voiceInterface.classList.add('hidden');
            }
        }
        
        if (voiceAnimation) {
            if (state === 'listening') {
                voiceAnimation.classList.add('listening');
            } else {
                voiceAnimation.classList.remove('listening');
            }
        }
        
        // Update status bar
        if (voiceStatus) {
            const statusText = this.isListening ? 
                translator?.translate('voice_on') || '🎤 Spraak: Aan' : 
                translator?.translate('voice_off') || '🎤 Spraak: Uit';
            voiceStatus.textContent = statusText;
        }
    }
    
    showStatus(message) {
        const feedbackElement = document.getElementById('voice-feedback');
        if (feedbackElement) {
            feedbackElement.textContent = message;
        }
        
        if (this.onStatusCallback) {
            this.onStatusCallback(message);
        }
        
        // Auto-hide status after 3 seconds
        setTimeout(() => {
            if (feedbackElement && feedbackElement.textContent === message) {
                feedbackElement.textContent = translator?.translate('listening') || 'Klaar voor commando\'s...';
            }
        }, 3000);
    }
    
    showHelp() {
        const commands = this.getCommandPatterns();
        const helpText = Object.entries(commands)
            .map(([pattern, cmd]) => `"${pattern.replace(/[()|\[\]]/g, '')}" - ${cmd.description}`)
            .join('\n');
        
        this.showStatus('Beschikbare commando\'s:\n' + helpText);
        
        // Also speak the help if TTS is available
        if (window.ttsManager) {
            const helpMessage = translator?.translate('voice_commands.help') || 
                              'Ik kan je vertellen waar je bent, een nieuwe opmerking geven, of iets cynisch zeggen.';
            window.ttsManager.speak(helpMessage);
        }
    }
    
    showFallbackMessage() {
        const fallbackElement = document.getElementById('voice-interface');
        if (fallbackElement) {
            fallbackElement.innerHTML = `
                <div class="voice-fallback">
                    <h4>🚫 Spraakherkenning niet beschikbaar</h4>
                    <p>Je browser ondersteunt geen spraakherkenning, of je bent niet verbonden via HTTPS.</p>
                    <p>Gebruik de chat-interface om met je reisgenoot te communiceren.</p>
                </div>
            `;
        }
    }
    
    adjustVolume(delta) {
        if (window.ttsManager) {
            window.ttsManager.adjustVolume(delta);
            this.showStatus(`Volume aangepast: ${delta > 0 ? 'omhoog' : 'omlaag'}`);
        }
    }
    
    // Test method for development
    simulateCommand(commandText) {
        if (this.onCommandCallback) {
            this.processCommand(commandText.toLowerCase(), 1.0);
        }
    }
    
    // Get supported languages for current browser
    getSupportedLanguages() {
        // Common language codes supported by most browsers
        return [
            { code: 'nl-NL', name: 'Nederlands' },
            { code: 'en-US', name: 'English (US)' },
            { code: 'en-GB', name: 'English (UK)' },
            { code: 'de-DE', name: 'Deutsch' },
            { code: 'fr-FR', name: 'Français' },
            { code: 'es-ES', name: 'Español' },
            { code: 'it-IT', name: 'Italiano' }
        ];
    }
}

// Initialize when DOM is loaded
let speechManager;
document.addEventListener('DOMContentLoaded', () => {
    speechManager = new SpeechManager();
    
    // Setup voice toggle button
    const voiceToggle = document.getElementById('voice-toggle');
    if (voiceToggle) {
        voiceToggle.addEventListener('click', () => {
            speechManager.toggle();
        });
    }
});

// Export for other modules
window.SpeechManager = SpeechManager;
