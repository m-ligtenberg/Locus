/**
 * TravelBot v3.0 - Meertalige ondersteuning
 * Ondersteunt: Nederlands, Engels, Duits, Frans
 */

class Translator {
    constructor() {
        this.currentLanguage = 'nl';
        this.translations = {
            nl: {
                // UI Labels
                choose_persona: "Kies je AI reisgenoot:",
                amsterdammer_desc: "Directe, nuchtere Amsterdam-style",
                belg_desc: "Melancholisch maar grappig",
                brabander_desc: "Gezellige Brabantse humor",
                jordanees_desc: "Amsterdamse volksbuurt charme",
                current_location: "Huidige locatie:",
                welcome: "Welkom! Ik ga je vergezellen op je reis.",
                new_comment: "Nieuw commentaar",
                listening: "Luisteren...",
                try_commands: "Probeer deze commando's:",
                ask_companion: "Vraag iets aan je reisgenoot...",
                loading: "TravelBot laden...",
                share_quote: "Quote delen",
                download_image: "Download Afbeelding",
                copy_link: "Kopieer Link",
                settings: "Instellingen",
                update_interval: "Update interval (minuten):",
                voice_enabled: "Spraak ingeschakeld:",
                auto_night_mode: "Automatische nachtmodus:",
                developer_mode: "Developer modus:",
                
                // Status Messages
                gps_connecting: "📍 GPS verbinden...",
                gps_connected: "📍 GPS verbonden",
                gps_error: "📍 GPS fout",
                voice_on: "🎤 Spraak: Aan",
                voice_off: "🎤 Spraak: Uit",
                route_detecting: "🧭 Route: Detecteren...",
                route_straight: "🧭 Route: Rechtdoor",
                route_turning: "🧭 Route: Bochten",
                route_stopped: "🧭 Route: Stilstand",
                
                // Persona Responses
                persona_responses: {
                    amsterdammer: {
                        greetings: [
                            "Zo, waar gaan we heen dan?",
                            "Lekker rijden vandaag, toch?",
                            "Even kijken waar we zitten..."
                        ],
                        movement: [
                            "Lekker doorrijden zo!",
                            "Waar gaan we naartoe eigenlijk?",
                            "Mooi tempo hoor."
                        ],
                        turns: [
                            "Weer een bocht, typisch.",
                            "Links, rechts, links... wordt je ook moe van.",
                            "Navigatie werkt tenminste."
                        ],
                        stopped: [
                            "Zo, even pauze?",
                            "File zeker?",
                            "Lekker rustig hier."
                        ],
                        night: [
                            "Donker wordt het...",
                            "Avondspits?",
                            "Tijd om naar huis te gaan."
                        ]
                    },
                    belg: {
                        greetings: [
                            "Alleeh, daar gaan we weer...",
                            "C'est bon, een nieuwe reis.",
                            "Pff, waar rijden we heen?"
                        ],
                        movement: [
                            "Och ja, we rijden maar wat rond...",
                            "Alleeh, het leven gaat door.",
                            "C'est bon, lekker onderweg."
                        ],
                        turns: [
                            "Weer bochten... zoals het leven, hé.",
                            "Links, rechts, altijd hetzelfde verhaal.",
                            "Pff, wat een omwegen."
                        ],
                        stopped: [
                            "Stilstaan... net als mijn leven.",
                            "Alleeh, files... klassiek België.",
                            "C'est bon, even rust."
                        ],
                        night: [
                            "Donker... zoals mijn gedachten.",
                            "Avond valt, dag voorbij... zo gaat het altijd.",
                            "Pff, weer een dag erop."
                        ]
                    }
                },
                
                // Voice Commands
                voice_commands: {
                    "waar ben ik": "Je bent in {location}",
                    "wat zegt de": "Hier is wat {persona} zegt: {response}",
                    "zeg iets cynisch": "Het leven is een file, en wij staan stil.",
                    "nieuwe opmerking": "Nieuwe observatie wordt geladen...",
                    "help": "Ik kan je vertellen waar je bent, een nieuwe opmerking geven, of iets cynisch zeggen."
                }
            },
            
            en: {
                // UI Labels  
                choose_persona: "Choose your AI travel companion:",
                amsterdammer_desc: "Direct, pragmatic Amsterdam-style",
                belg_desc: "Melancholic but funny",
                brabander_desc: "Cozy Brabant humor",
                jordanees_desc: "Amsterdam working-class charm",
                current_location: "Current location:",
                welcome: "Welcome! I'll accompany you on your journey.",
                new_comment: "New comment",
                listening: "Listening...",
                try_commands: "Try these commands:",
                ask_companion: "Ask your companion something...",
                loading: "Loading TravelBot...",
                share_quote: "Share quote",
                download_image: "Download Image",
                copy_link: "Copy Link",
                settings: "Settings",
                update_interval: "Update interval (minutes):",
                voice_enabled: "Voice enabled:",
                auto_night_mode: "Automatic night mode:",
                developer_mode: "Developer mode:",
                
                // Status Messages
                gps_connecting: "📍 GPS connecting...",
                gps_connected: "📍 GPS connected",
                gps_error: "📍 GPS error",
                voice_on: "🎤 Voice: On",
                voice_off: "🎤 Voice: Off",
                route_detecting: "🧭 Route: Detecting...",
                route_straight: "🧭 Route: Straight",
                route_turning: "🧭 Route: Turning",
                route_stopped: "🧭 Route: Stopped",
                
                // Persona Responses
                persona_responses: {
                    amsterdammer: {
                        greetings: [
                            "So, where are we heading?",
                            "Nice drive today, right?",
                            "Let me check where we are..."
                        ],
                        movement: [
                            "Keep going like this!",
                            "Where are we actually going?",
                            "Good pace there."
                        ],
                        turns: [
                            "Another turn, typical.",
                            "Left, right, left... gets tiring too.",
                            "Navigation works at least."
                        ],
                        stopped: [
                            "Right, quick break?",
                            "Traffic jam for sure?",
                            "Nice and quiet here."
                        ],
                        night: [
                            "Getting dark...",
                            "Rush hour?",
                            "Time to go home."
                        ]
                    },
                    belg: {
                        greetings: [
                            "Alright, here we go again...",
                            "Well, a new journey.",
                            "Sigh, where are we driving?"
                        ],
                        movement: [
                            "Oh well, just driving around...",
                            "Alright, life goes on.",
                            "Fine, nicely on the road."
                        ],
                        turns: [
                            "More turns... like life, eh.",
                            "Left, right, always the same story.",
                            "Sigh, what detours."
                        ],
                        stopped: [
                            "Standing still... like my life.",
                            "Alright, traffic... classic Belgium.",
                            "Fine, some rest."
                        ],
                        night: [
                            "Dark... like my thoughts.",
                            "Evening falls, day passed... always the same.",
                            "Sigh, another day gone."
                        ]
                    }
                },
                
                // Voice Commands
                voice_commands: {
                    "where am i": "You are in {location}",
                    "what does the": "Here's what {persona} says: {response}",
                    "say something cynical": "Life is a traffic jam, and we're standing still.",
                    "new comment": "New observation loading...",
                    "help": "I can tell you where you are, give a new comment, or say something cynical."
                }
            },
            
            de: {
                // UI Labels
                choose_persona: "Wähle deinen KI-Reisebegleiter:",
                amsterdammer_desc: "Direkt, pragmatisch im Amsterdam-Stil",
                belg_desc: "Melancholisch aber lustig",
                brabander_desc: "Gemütlicher Brabanter Humor",
                jordanees_desc: "Amsterdamer Arbeiterklasse-Charme",
                current_location: "Aktuelle Position:",
                welcome: "Willkommen! Ich begleite dich auf deiner Reise.",
                new_comment: "Neuer Kommentar",
                listening: "Höre zu...",
                try_commands: "Probiere diese Befehle:",
                ask_companion: "Frag deinen Begleiter etwas...",
                loading: "TravelBot wird geladen...",
                share_quote: "Zitat teilen",
                download_image: "Bild herunterladen",
                copy_link: "Link kopieren",
                settings: "Einstellungen",
                update_interval: "Update-Intervall (Minuten):",
                voice_enabled: "Sprache aktiviert:",
                auto_night_mode: "Automatischer Nachtmodus:",
                developer_mode: "Entwicklermodus:",
                
                // Status Messages
                gps_connecting: "📍 GPS verbindet...",
                gps_connected: "📍 GPS verbunden",
                gps_error: "📍 GPS Fehler",
                voice_on: "🎤 Sprache: Ein",
                voice_off: "🎤 Sprache: Aus",
                route_detecting: "🧭 Route: Erkennung...",
                route_straight: "🧭 Route: Geradeaus",
                route_turning: "🧭 Route: Kurven",
                route_stopped: "🧭 Route: Stillstand",
                
                // Persona Responses
                persona_responses: {
                    amsterdammer: {
                        greetings: [
                            "Also, wo fahren wir hin?",
                            "Schöne Fahrt heute, oder?",
                            "Mal schauen wo wir sind..."
                        ],
                        movement: [
                            "Schön weiterfahren so!",
                            "Wo fahren wir eigentlich hin?",
                            "Gutes Tempo da."
                        ],
                        turns: [
                            "Wieder eine Kurve, typisch.",
                            "Links, rechts, links... wird auch müde davon.",
                            "Navigation funktioniert wenigstens."
                        ],
                        stopped: [
                            "So, kurze Pause?",
                            "Bestimmt Stau?",
                            "Schön ruhig hier."
                        ],
                        night: [
                            "Wird dunkel...",
                            "Rushhour?",
                            "Zeit nach Hause zu gehen."
                        ]
                    }
                },
                
                // Voice Commands
                voice_commands: {
                    "wo bin ich": "Du bist in {location}",
                    "was sagt der": "Hier ist was {persona} sagt: {response}",
                    "sag etwas zynisches": "Das Leben ist ein Stau, und wir stehen still.",
                    "neuer kommentar": "Neue Beobachtung wird geladen...",
                    "hilfe": "Ich kann dir sagen wo du bist, einen neuen Kommentar geben, oder etwas Zynisches sagen."
                }
            },
            
            fr: {
                // UI Labels
                choose_persona: "Choisissez votre compagnon de voyage IA :",
                amsterdammer_desc: "Style direct et pragmatique d'Amsterdam",
                belg_desc: "Mélancolique mais drôle",
                brabander_desc: "Humour chaleureux du Brabant",
                jordanees_desc: "Charme du quartier populaire d'Amsterdam",
                current_location: "Position actuelle :",
                welcome: "Bienvenue ! Je vais vous accompagner dans votre voyage.",
                new_comment: "Nouveau commentaire",
                listening: "J'écoute...",
                try_commands: "Essayez ces commandes :",
                ask_companion: "Demandez quelque chose à votre compagnon...",
                loading: "Chargement de TravelBot...",
                share_quote: "Partager la citation",
                download_image: "Télécharger l'image",
                copy_link: "Copier le lien",
                settings: "Paramètres",
                update_interval: "Intervalle de mise à jour (minutes) :",
                voice_enabled: "Voix activée :",
                auto_night_mode: "Mode nuit automatique :",
                developer_mode: "Mode développeur :",
                
                // Status Messages
                gps_connecting: "📍 GPS en connexion...",
                gps_connected: "📍 GPS connecté",
                gps_error: "📍 Erreur GPS",
                voice_on: "🎤 Voix : Activée",
                voice_off: "🎤 Voix : Désactivée",
                route_detecting: "🧭 Route : Détection...",
                route_straight: "🧭 Route : Tout droit",
                route_turning: "🧭 Route : Virages",
                route_stopped: "🧭 Route : Arrêt",
                
                // Voice Commands
                voice_commands: {
                    "où suis-je": "Vous êtes à {location}",
                    "que dit le": "Voici ce que dit {persona} : {response}",
                    "dis quelque chose de cynique": "La vie est un embouteillage, et nous sommes immobiles.",
                    "nouveau commentaire": "Nouvelle observation en cours de chargement...",
                    "aide": "Je peux vous dire où vous êtes, donner un nouveau commentaire, ou dire quelque chose de cynique."
                }
            }
        };
        
        this.init();
    }
    
    init() {
        // Detect browser language
        const browserLang = navigator.language.substring(0, 2);
        if (this.translations[browserLang]) {
            this.currentLanguage = browserLang;
        }
        
        // Load saved language preference
        const savedLang = localStorage.getItem('travelbot-language');
        if (savedLang && this.translations[savedLang]) {
            this.currentLanguage = savedLang;
        }
        
        this.updateLanguageSelector();
        this.translatePage();
    }
    
    setLanguage(lang) {
        if (this.translations[lang]) {
            this.currentLanguage = lang;
            localStorage.setItem('travelbot-language', lang);
            this.translatePage();
            this.updateTTSLanguage();
        }
    }
    
    getCurrentLanguage() {
        return this.currentLanguage;
    }
    
    translate(key, variables = {}) {
        const translation = this.translations[this.currentLanguage];
        if (!translation) return key;
        
        let text = this.getNestedProperty(translation, key) || key;
        
        // Replace variables in the format {variable}
        Object.keys(variables).forEach(variable => {
            text = text.replace(new RegExp(`{${variable}}`, 'g'), variables[variable]);
        });
        
        return text;
    }
    
    getPersonaResponse(persona, category) {
        const responses = this.translate(`persona_responses.${persona}.${category}`);
        if (Array.isArray(responses) && responses.length > 0) {
            return responses[Math.floor(Math.random() * responses.length)];
        }
        return this.translate('welcome');
    }
    
    getVoiceCommandResponse(command, variables = {}) {
        const commands = this.translate('voice_commands');
        const normalizedCommand = command.toLowerCase().trim();
        
        for (const [key, response] of Object.entries(commands)) {
            if (normalizedCommand.includes(key.toLowerCase())) {
                return this.translate('voice_commands.' + key, variables);
            }
        }
        
        return this.translate('voice_commands.help');
    }
    
    translatePage() {
        // Translate all elements with data-translate attribute
        const elements = document.querySelectorAll('[data-translate]');
        elements.forEach(element => {
            const key = element.getAttribute('data-translate');
            element.textContent = this.translate(key);
        });
        
        // Translate placeholder attributes
        const placeholderElements = document.querySelectorAll('[data-translate-placeholder]');
        placeholderElements.forEach(element => {
            const key = element.getAttribute('data-translate-placeholder');
            element.placeholder = this.translate(key);
        });
        
        // Update document language
        document.documentElement.lang = this.currentLanguage;
    }
    
    updateLanguageSelector() {
        const selector = document.getElementById('language-select');
        if (selector) {
            selector.value = this.currentLanguage;
        }
    }
    
    updateTTSLanguage() {
        // Inform TTS system about language change
        if (window.ttsManager) {
            window.ttsManager.setLanguage(this.getVoiceLanguage());
        }
    }
    
    getVoiceLanguage() {
        const voiceMap = {
            'nl': 'nl-NL',
            'en': 'en-US', 
            'de': 'de-DE',
            'fr': 'fr-FR'
        };
        return voiceMap[this.currentLanguage] || 'en-US';
    }
    
    getNestedProperty(obj, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined ? current[key] : undefined;
        }, obj);
    }
    
    // Night mode specific translations
    getNightModeResponses(persona) {
        return this.getPersonaResponse(persona, 'night');
    }
    
    // Route-based responses
    getRouteResponse(persona, routeType) {
        const routeMap = {
            'straight': 'movement',
            'turning': 'turns',
            'stopped': 'stopped'
        };
        
        const category = routeMap[routeType] || 'movement';
        return this.getPersonaResponse(persona, category);
    }
}

// Initialize translator when DOM is loaded
let translator;
document.addEventListener('DOMContentLoaded', () => {
    translator = new Translator();
    
    // Set up language selector event
    const languageSelect = document.getElementById('language-select');
    if (languageSelect) {
        languageSelect.addEventListener('change', (e) => {
            translator.setLanguage(e.target.value);
        });
    }
});

// Export for other modules
window.Translator = Translator;
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Translator;
}
