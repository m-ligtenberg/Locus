/**
 * TravelBot v3.0 - Text-to-Speech Manager
 * Ondersteunt Web Speech API TTS met meertalige stemmen
 */

class TTSManager {
    constructor() {
        this.isSupported = false;
        this.voices = [];
        this.currentVoice = null;
        this.volume = 0.8;
        this.rate = 1.0;
        this.pitch = 1.0;
        this.language = 'nl-NL';
        this.isSpeaking = false;
        this.queue = [];
        this.preferredVoices = {};
        
        this.init();
    }
    
    init() {
        this.checkSupport();
        if (this.isSupported) {
            this.loadVoices();
            this.setupEventListeners();
            this.loadSettings();
        }
    }
    
    checkSupport() {
        this.isSupported = 'speechSynthesis' in window;
        
        if (!this.isSupported) {
            console.warn('Text-to-Speech niet ondersteund in deze browser');
            this.showFallbackMessage();
        }
        
        return this.isSupported;
    }
    
    loadVoices() {
        if (!this.isSupported) return;
        
        const updateVoices = () => {
            this.voices = speechSynthesis.getVoices();
            this.categorizeVoices();
            this.selectBestVoice();
            console.log('Beschikbare stemmen geladen:', this.voices.length);
        };
        
        // Load voices immediately if available
        updateVoices();
        
        // Also listen for the voiceschanged event (async loading)
        speechSynthesis.onvoiceschanged = updateVoices;
    }
    
    categorizeVoices() {
        this.preferredVoices = {
            'nl-NL': [],
            'nl-BE': [],
            'en-US': [],
            'en-GB': [],
            'de-DE': [],
            'fr-FR': []
        };
        
        this.voices.forEach(voice => {
            const lang = voice.lang;
            if (this.preferredVoices.hasOwnProperty(lang)) {
                this.preferredVoices[lang].push(voice);
            }
        });
        
        // Sort voices by quality (prefer non-default, then local)
        Object.keys(this.preferredVoices).forEach(lang => {
            this.preferredVoices[lang].sort((a, b) => {
                if (a.localService && !b.localService) return -1;
                if (!a.localService && b.localService) return 1;
                if (!a.default && b.default) return -1;
                if (a.default && !b.default) return 1;
                return 0;
            });
        });
    }
    
    selectBestVoice() {
        const availableVoices = this.preferredVoices[this.language] || [];
        
        if (availableVoices.length > 0) {
            this.currentVoice = availableVoices[0];
        } else {
            // Fallback to any Dutch voice
            const dutchVoice = this.voices.find(voice => voice.lang.startsWith('nl'));
            if (dutchVoice) {
                this.currentVoice = dutchVoice;
            } else {
                // Final fallback to first available voice
                this.currentVoice = this.voices[0] || null;
            }
        }
        
        if (this.currentVoice) {
            console.log('Geselecteerde stem:', this.currentVoice.name, this.currentVoice.lang);
        }
    }
    
    setupEventListeners() {
        // Handle speaking events
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.isSpeaking) {
                // Pause when tab becomes hidden (optional)
                // speechSynthesis.pause();
            }
        });
    }
    
    speak(text, options = {}) {
        if (!this.isSupported || !text) return false;
        
        // Stop current speech if interrupt is requested
        if (options.interrupt && this.isSpeaking) {
            this.stop();
        }
        
        const utterance = new SpeechSynthesisUtterance(text);
        
        // Apply settings
        utterance.voice = options.voice || this.currentVoice;
        utterance.volume = options.volume !== undefined ? options.volume : this.volume;
        utterance.rate = options.rate !== undefined ? options.rate : this.rate;
        utterance.pitch = options.pitch !== undefined ? options.pitch : this.pitch;
        utterance.lang = options.language || this.language;
        
        // Event handlers
        utterance.onstart = () => {
            this.isSpeaking = true;
            this.updateUI('speaking');
            console.log('TTS gestart:', text.substring(0, 50) + '...');
        };
        
        utterance.onend = () => {
            this.isSpeaking = false;
            this.updateUI('idle');
            this.processQueue();
            console.log('TTS voltooid');
        };
        
        utterance.onerror = (event) => {
            console.error('TTS fout:', event.error);
            this.isSpeaking = false;
            this.updateUI('error');
            this.processQueue();
        };
        
        utterance.onpause = () => {
            console.log('TTS gepauzeerd');
        };
        
        utterance.onresume = () => {
            console.log('TTS hervat');
        };
        
        // Add to queue or speak immediately
        if (options.queue && this.isSpeaking) {
            this.queue.push(utterance);
            console.log('Toegevoegd aan TTS wachtrij');
        } else {
            speechSynthesis.speak(utterance);
        }
        
        return true;
    }
    
    processQueue() {
        if (this.queue.length > 0 && !this.isSpeaking) {
            const nextUtterance = this.queue.shift();
            speechSynthesis.speak(nextUtterance);
        }
    }
    
    stop() {
        if (this.isSupported) {
            speechSynthesis.cancel();
            this.queue = [];
            this.isSpeaking = false;
            this.updateUI('idle');
        }
    }
    
    pause() {
        if (this.isSupported && this.isSpeaking) {
            speechSynthesis.pause();
        }
    }
    
    resume() {
        if (this.isSupported) {
            speechSynthesis.resume();
        }
    }
    
    setLanguage(language) {
        this.language = language;
        this.selectBestVoice();
        this.saveSettings();
    }
    
    setVoice(voiceName) {
        const voice = this.voices.find(v => v.name === voiceName);
        if (voice) {
            this.currentVoice = voice;
            this.language = voice.lang;
            this.saveSettings();
        }
    }
    
    adjustVolume(delta) {
        this.volume = Math.max(0, Math.min(1, this.volume + delta));
        this.saveSettings();
        return this.volume;
    }
    
    setRate(rate) {
        this.rate = Math.max(0.1, Math.min(10, rate));
        this.saveSettings();
    }
    
    setPitch(pitch) {
        this.pitch = Math.max(0, Math.min(2, pitch));
        this.saveSettings();
    }
    
    // Persona-specific voice mapping
    getPersonaVoice(persona) {
        const voiceMap = {
            'amsterdammer': {
                'nl-NL': ['Microsoft David - Dutch (Netherlands)', 'Google Nederlands'],
                'en-US': ['Microsoft Zira - English (United States)', 'Google US English']
            },
            'belg': {
                'nl-BE': ['Microsoft Bart - Dutch (Belgium)'],
                'nl-NL': ['Microsoft Frank - Dutch (Netherlands)'],
                'en-US': ['Microsoft Mark - English (United States)']
            },
            'brabander': {
                'nl-NL': ['Microsoft David - Dutch (Netherlands)'],
                'en-US': ['Microsoft David - English (United States)']
            },
            'jordanees': {
                'nl-NL': ['Microsoft David - Dutch (Netherlands)'],
                'en-US': ['Microsoft Mark - English (United States)']
            }
        };
        
        const personaVoices = voiceMap[persona];
        if (!personaVoices) return this.currentVoice;
        
        const languageVoices = personaVoices[this.language] || personaVoices['nl-NL'];
        if (!languageVoices) return this.currentVoice;
        
        // Find first available voice from preferred list
        for (const voiceName of languageVoices) {
            const voice = this.voices.find(v => v.name.includes(voiceName.split(' - ')[0]));
            if (voice) return voice;
        }
        
        return this.currentVoice;
    }
    
    speakPersonaResponse(text, persona, options = {}) {
        const personaVoice = this.getPersonaVoice(persona);
        return this.speak(text, {
            ...options,
            voice: personaVoice
        });
    }
    
    // Night mode adjustments
    getNightModeSettings() {
        return {
            volume: this.volume * 0.7, // Quieter at night
            rate: this.rate * 0.9,     // Slightly slower
            pitch: this.pitch * 0.95   // Slightly deeper
        };
    }
    
    speakNightMode(text, options = {}) {
        const nightSettings = this.getNightModeSettings();
        return this.speak(text, {
            ...options,
            ...nightSettings
        });
    }
    
    // Settings persistence
    saveSettings() {
        const settings = {
            volume: this.volume,
            rate: this.rate,
            pitch: this.pitch,
            language: this.language,
            voiceName: this.currentVoice?.name
        };
        
        localStorage.setItem('travelbot-tts-settings', JSON.stringify(settings));
    }
    
    loadSettings() {
        const saved = localStorage.getItem('travelbot-tts-settings');
        if (saved) {
            try {
                const settings = JSON.parse(saved);
                this.volume = settings.volume || this.volume;
                this.rate = settings.rate || this.rate;
                this.pitch = settings.pitch || this.pitch;
                this.language = settings.language || this.language;
                
                if (settings.voiceName) {
                    // Try to restore specific voice
                    setTimeout(() => {
                        this.setVoice(settings.voiceName);
                    }, 1000);
                }
            } catch (error) {
                console.warn('Kon TTS instellingen niet laden:', error);
            }
        }
    }
    
    // UI Methods
    updateUI(state) {
        const speakBtn = document.getElementById('speak-btn');
        
        if (speakBtn) {
            switch (state) {
                case 'speaking':
                    speakBtn.textContent = '⏸️';
                    speakBtn.title = 'Pauzeer spraak';
                    speakBtn.classList.add('speaking');
                    break;
                case 'idle':
                default:
                    speakBtn.textContent = '🔊';
                    speakBtn.title = 'Spreek uit';
                    speakBtn.classList.remove('speaking');
                    break;
                case 'error':
                    speakBtn.textContent = '🔇';
                    speakBtn.title = 'Spraak fout';
                    speakBtn.classList.remove('speaking');
                    break;
            }
        }
    }
    
    showFallbackMessage() {
        console.warn('Text-to-Speech niet beschikbaar - gebruik stille modus');
    }
    
    // Voice testing and diagnostics
    testVoice(voiceName = null) {
        const voice = voiceName ? 
            this.voices.find(v => v.name === voiceName) : 
            this.currentVoice;
            
        if (voice) {
            const testText = this.getTestText();
            this.speak(testText, { voice: voice, interrupt: true });
            return true;
        }
        return false;
    }
    
    getTestText() {
        const testTexts = {
            'nl': 'Hallo, ik ben je TravelBot reisgenoot. Ik ga je vergezellen op je reis.',
            'en': 'Hello, I am your TravelBot travel companion. I will accompany you on your journey.',
            'de': 'Hallo, ich bin dein TravelBot Reisebegleiter. Ich werde dich auf deiner Reise begleiten.',
            'fr': 'Bonjour, je suis votre compagnon de voyage TravelBot. Je vais vous accompagner dans votre voyage.'
        };
        
        const langCode = this.language.substring(0, 2);
        return testTexts[langCode] || testTexts['nl'];
    }
    
    // Get available voices grouped by language
    getVoicesByLanguage() {
        const grouped = {};
        
        this.voices.forEach(voice => {
            const lang = voice.lang;
            if (!grouped[lang]) {
                grouped[lang] = [];
            }
            grouped[lang].push({
                name: voice.name,
                language: voice.lang,
                default: voice.default,
                local: voice.localService
            });
        });
        
        return grouped;
    }
    
    // Development/debug methods
    logVoiceInfo() {
        console.log('TTS Status:', {
            isSupported: this.isSupported,
            isSpeaking: this.isSpeaking,
            currentVoice: this.currentVoice?.name,
            language: this.language,
            voiceCount: this.voices.length,
            queueLength: this.queue.length,
            volume: this.volume,
            rate: this.rate,
            pitch: this.pitch
        });
    }
    
    // Route-based speech adjustments
    adjustForMovement(speed) {
        // Adjust rate based on travel speed
        if (speed > 80) {
            // Highway speed - speak faster
            this.setRate(1.2);
        } else if (speed > 30) {
            // City speed - normal rate
            this.setRate(1.0);
        } else {
            // Slow/stopped - speak slower for clarity
            this.setRate(0.8);
        }
    }
}

// Initialize when DOM is loaded
let ttsManager;
document.addEventListener('DOMContentLoaded', () => {
    ttsManager = new TTSManager();
    
    // Setup speak button
    const speakBtn = document.getElementById('speak-btn');
    if (speakBtn) {
        speakBtn.addEventListener('click', () => {
            const responseText = document.getElementById('response-text')?.textContent;
            if (responseText) {
                if (ttsManager.isSpeaking) {
                    ttsManager.stop();
                } else {
                    ttsManager.speak(responseText);
                }
            }
        });
    }
});

// Export for other modules
window.TTSManager = TTSManager;
