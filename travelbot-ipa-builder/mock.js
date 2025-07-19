/**
 * TravelBot v3.0 - Mock & Development Tools
 * Simulatie tools voor development en testing
 */

class MockManager {
    constructor() {
        this.isEnabled = false;
        this.mockLocation = null;
        this.simulationInterval = null;
        this.simulationRoutes = {};
        this.debugMode = false;
        this.performanceMetrics = {
            fps: 0,
            memory: 0,
            apiCalls: 0,
            errors: 0
        };
        
        this.init();
    }
    
    init() {
        this.loadSettings();
        this.setupMockRoutes();
        this.initPerformanceMonitoring();
        
        // Enable mock mode if in development
        if (this.isDevelopmentMode()) {
            this.enable();
        }
    }
    
    isDevelopmentMode() {
        return location.hostname === 'localhost' || 
               location.hostname === '127.0.0.1' ||
               location.search.includes('debug=true');
    }
    
    enable() {
        this.isEnabled = true;
        console.log('🧪 Mock mode enabled');
        this.showDebugPanel();
        this.setupDebugControls();
    }
    
    disable() {
        this.isEnabled = false;
        this.hideDebugPanel();
        this.stopSimulation();
    }
    
    setupMockRoutes() {
        // Predefined routes for simulation
        this.simulationRoutes = {
            amsterdam_center: [
                { lat: 52.3676, lng: 4.9041, speed: 0, desc: "Dam Square" },
                { lat: 52.3738, lng: 4.8910, speed: 30, desc: "Vondelpark" },
                { lat: 52.3784, lng: 4.9009, speed: 25, desc: "Museumplein" },
                { lat: 52.3731, lng: 4.8922, speed: 35, desc: "Leidseplein" },
                { lat: 52.3676, lng: 4.9041, speed: 0, desc: "Dam Square (return)" }
            ],
            highway_trip: [
                { lat: 52.3676, lng: 4.9041, speed: 30, desc: "Amsterdam" },
                { lat: 52.2930, lng: 4.9685, speed: 80, desc: "A4 Highway" },
                { lat: 52.1601, lng: 4.4970, speed: 120, desc: "A4 Highway" },
                { lat: 52.0705, lng: 4.3007, speed: 90, desc: "Den Haag approach" },
                { lat: 52.0799, lng: 4.3113, speed: 0, desc: "Den Haag Centraal" }
            ],
            city_traffic: [
                { lat: 52.3676, lng: 4.9041, speed: 25, desc: "City center" },
                { lat: 52.3680, lng: 4.9045, speed: 5, desc: "Traffic jam" },
                { lat: 52.3685, lng: 4.9050, speed: 0, desc: "Red light" },
                { lat: 52.3690, lng: 4.9055, speed: 15, desc: "Slow traffic" },
                { lat: 52.3695, lng: 4.9060, speed: 30, desc: "Clear road" }
            ],
            belgian_route: [
                { lat: 51.2194, lng: 4.4025, speed: 50, desc: "Antwerpen" },
                { lat: 51.0500, lng: 3.7303, speed: 90, desc: "E17 towards Gent" },
                { lat: 51.0538, lng: 3.7250, speed: 60, desc: "Gent" },
                { lat: 50.8476, lng: 4.3572, speed: 40, desc: "Brussels approach" },
                { lat: 50.8503, lng: 4.3517, speed: 0, desc: "Brussels Central" }
            ]
        };
    }
    
    showMockLocationDialog() {
        const dialog = document.createElement('div');
        dialog.className = 'mock-dialog';
        dialog.innerHTML = `
            <div class="mock-dialog-content">
                <h3>🧪 Mock Location</h3>
                <p>GPS niet beschikbaar. Wil je een mock locatie gebruiken voor development?</p>
                <div class="mock-options">
                    <button onclick="mockManager.setMockLocation(52.3676, 4.9041, 'Amsterdam Dam')">📍 Amsterdam Dam</button>
                    <button onclick="mockManager.setMockLocation(51.2194, 4.4025, 'Antwerpen')">📍 Antwerpen</button>
                    <button onclick="mockManager.setMockLocation(52.0799, 4.3113, 'Den Haag')">📍 Den Haag</button>
                    <button onclick="mockManager.setMockLocation(51.0538, 3.7250, 'Gent')">📍 Gent</button>
                </div>
                <button class="mock-close" onclick="this.parentElement.parentElement.remove()">Sluiten</button>
            </div>
        `;
        
        dialog.style.cssText = `
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;
        
        document.body.appendChild(dialog);
    }
    
    setMockLocation(lat, lng, description = '') {
        this.mockLocation = { lat, lng, description };
        
        if (window.locationManager) {
            window.locationManager.setMockLocation(lat, lng, {
                accuracy: 10,
                speed: 0
            });
        }
        
        console.log('🧪 Mock location set:', { lat, lng, description });
        this.logDebug(`Mock location: ${description} (${lat}, ${lng})`);
        
        // Close dialog if open
        const dialog = document.querySelector('.mock-dialog');
        if (dialog) dialog.remove();
    }
    
    simulateRoute(routeName) {
        if (!this.simulationRoutes[routeName]) {
            console.error('Unknown route:', routeName);
            return;
        }
        
        this.stopSimulation();
        
        const route = this.simulationRoutes[routeName];
        let index = 0;
        
        console.log('🚗 Starting route simulation:', routeName);
        this.logDebug(`Simulating route: ${routeName} (${route.length} points)`);
        
        this.simulationInterval = setInterval(() => {
            if (index >= route.length) {
                this.stopSimulation();
                this.logDebug('Route simulation completed');
                return;
            }
            
            const point = route[index];
            this.setMockLocation(point.lat, point.lng, point.desc);
            
            // Simulate speed
            if (window.locationManager) {
                window.locationManager.speed = point.speed || 0;
            }
            
            index++;
        }, 3000); // 3 second intervals
    }
    
    stopSimulation() {
        if (this.simulationInterval) {
            clearInterval(this.simulationInterval);
            this.simulationInterval = null;
            console.log('🛑 Route simulation stopped');
            this.logDebug('Route simulation stopped');
        }
    }
    
    simulateMovement(speed = 50) {
        if (!this.mockLocation) {
            console.warn('No mock location set for movement simulation');
            return;
        }
        
        let direction = 0;
        const baseLocation = { ...this.mockLocation };
        
        console.log('🏃 Starting movement simulation');
        this.logDebug(`Simulating movement at ${speed} km/h`);
        
        this.simulationInterval = setInterval(() => {
            // Calculate new position based on speed and direction
            const distance = (speed * 1000) / 3600 * 3; // Distance in 3 seconds
            const deltaLat = (distance * Math.cos(direction * Math.PI / 180)) / 111320; // ~111320m per degree
            const deltaLng = (distance * Math.sin(direction * Math.PI / 180)) / (111320 * Math.cos(baseLocation.lat * Math.PI / 180));
            
            baseLocation.lat += deltaLat;
            baseLocation.lng += deltaLng;
            
            this.setMockLocation(baseLocation.lat, baseLocation.lng, 'Moving simulation');
            
            // Randomly change direction occasionally
            if (Math.random() < 0.3) {
                direction += (Math.random() - 0.5) * 60; // ±30 degree change
                direction = (direction + 360) % 360;
            }
            
            // Update speed display
            if (window.locationManager) {
                window.locationManager.speed = speed + (Math.random() - 0.5) * 10;
            }
            
        }, 3000);
    }
    
    showDebugPanel() {
        const debugPanel = document.getElementById('debug-panel');
        if (debugPanel) {
            debugPanel.classList.remove('hidden');
        }
    }
    
    hideDebugPanel() {
        const debugPanel = document.getElementById('debug-panel');
        if (debugPanel) {
            debugPanel.classList.add('hidden');
        }
    }
    
    setupDebugControls() {
        // Mock location button
        const mockLocationBtn = document.getElementById('mock-location-btn');
        if (mockLocationBtn) {
            mockLocationBtn.addEventListener('click', () => {
                this.showMockLocationDialog();
            });
        }
        
        // Simulate movement button
        const simulateMovementBtn = document.getElementById('simulate-movement-btn');
        if (simulateMovementBtn) {
            simulateMovementBtn.addEventListener('click', () => {
                if (this.simulationInterval) {
                    this.stopSimulation();
                    simulateMovementBtn.textContent = '🚗 Simuleer Beweging';
                } else {
                    this.simulateMovement(50);
                    simulateMovementBtn.textContent = '🛑 Stop Simulatie';
                }
            });
        }
        
        // Test voices button
        const testVoicesBtn = document.getElementById('test-voices-btn');
        if (testVoicesBtn) {
            testVoicesBtn.addEventListener('click', () => {
                this.testAllVoices();
            });
        }
        
        // Performance log button
        const performanceLogBtn = document.getElementById('performance-log-btn');
        if (performanceLogBtn) {
            performanceLogBtn.addEventListener('click', () => {
                this.showPerformanceLog();
            });
        }
    }
    
    testAllVoices() {
        if (!window.ttsManager) {
            this.logDebug('TTS Manager not available');
            return;
        }
        
        const testText = 'Dit is een test van de Text-to-Speech functionaliteit.';
        const personas = ['amsterdammer', 'belg', 'brabander', 'jordanees'];
        
        let index = 0;
        const testNext = () => {
            if (index >= personas.length) {
                this.logDebug('Voice testing completed');
                return;
            }
            
            const persona = personas[index];
            this.logDebug(`Testing voice for ${persona}`);
            
            window.ttsManager.speakPersonaResponse(`${testText} Ik ben ${persona}.`, persona);
            
            setTimeout(() => {
                index++;
                testNext();
            }, 3000);
        };
        
        testNext();
    }
    
    initPerformanceMonitoring() {
        if (!this.isEnabled) return;
        
        // FPS monitoring
        let lastTime = performance.now();
        let frames = 0;
        
        const updateFPS = () => {
            frames++;
            const currentTime = performance.now();
            
            if (currentTime - lastTime >= 1000) {
                this.performanceMetrics.fps = Math.round((frames * 1000) / (currentTime - lastTime));
                frames = 0;
                lastTime = currentTime;
            }
            
            requestAnimationFrame(updateFPS);
        };
        
        requestAnimationFrame(updateFPS);
        
        // Memory monitoring
        if (performance.memory) {
            setInterval(() => {
                this.performanceMetrics.memory = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024);
            }, 1000);
        }
        
        // API call monitoring
        const originalFetch = window.fetch;
        window.fetch = (...args) => {
            this.performanceMetrics.apiCalls++;
            return originalFetch(...args).catch(error => {
                this.performanceMetrics.errors++;
                throw error;
            });
        };
        
        // Error monitoring
        window.addEventListener('error', () => {
            this.performanceMetrics.errors++;
        });
        
        // Update performance display
        setInterval(() => {
            this.updatePerformanceDisplay();
        }, 1000);
    }
    
    updatePerformanceDisplay() {
        const debugOutput = document.getElementById('debug-output');
        if (!debugOutput) return;
        
        const performanceText = [
            `FPS: ${this.performanceMetrics.fps}`,
            `Memory: ${this.performanceMetrics.memory}MB`,
            `API Calls: ${this.performanceMetrics.apiCalls}`,
            `Errors: ${this.performanceMetrics.errors}`,
            `Mock Location: ${this.mockLocation ? `${this.mockLocation.lat.toFixed(4)}, ${this.mockLocation.lng.toFixed(4)}` : 'None'}`,
            `Simulation: ${this.simulationInterval ? 'Running' : 'Stopped'}`,
            '',
            '--- Debug Log ---',
            ...this.debugLog.slice(-10)
        ].join('\n');
        
        debugOutput.textContent = performanceText;
    }
    
    debugLog = [];
    
    logDebug(message) {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = `[${timestamp}] ${message}`;
        this.debugLog.push(logEntry);
        
        // Keep only last 50 entries
        if (this.debugLog.length > 50) {
            this.debugLog = this.debugLog.slice(-50);
        }
        
        console.log('🧪', message);
    }
    
    showPerformanceLog() {
        const logWindow = window.open('', '_blank', 'width=800,height=600');
        logWindow.document.write(`
            <html>
            <head>
                <title>TravelBot Performance Log</title>
                <style>
                    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #fff; }
                    .metric { margin: 10px 0; padding: 5px; background: #333; }
                    .log-entry { margin: 2px 0; padding: 2px; font-size: 12px; }
                </style>
            </head>
            <body>
                <h1>TravelBot v3.0 Performance Metrics</h1>
                <div class="metric">FPS: ${this.performanceMetrics.fps}</div>
                <div class="metric">Memory Usage: ${this.performanceMetrics.memory}MB</div>
                <div class="metric">API Calls: ${this.performanceMetrics.apiCalls}</div>
                <div class="metric">Errors: ${this.performanceMetrics.errors}</div>
                
                <h2>Debug Log</h2>
                <div id="log-entries">
                    ${this.debugLog.map(entry => `<div class="log-entry">${entry}</div>`).join('')}
                </div>
                
                <script>
                    setInterval(() => location.reload(), 5000);
                </script>
            </body>
            </html>
        `);
    }
    
    // Mock API responses for development
    mockApiResponse(endpoint, data) {
        const responses = {
            '/api/persona-chat': {
                response: this.generateMockPersonaResponse(data.persona, data.message),
                confidence: 0.8,
                processingTime: Math.random() * 1000
            },
            '/api/location-comment': {
                comment: this.generateMockLocationComment(data.persona, data.location),
                persona: data.persona,
                timestamp: Date.now()
            }
        };
        
        return responses[endpoint] || { error: 'Mock endpoint not found' };
    }
    
    generateMockPersonaResponse(persona, message) {
        const responses = {
            'amsterdammer': [
                'Ja, dat zie ik ook zo.',
                'Lekker bezig, hoor.',
                'Helder verhaal.',
                'Dat is gewoon logisch.'
            ],
            'belg': [
                'Alleeh, dat is wel waar ja...',
                'Pff, het leven, hé.',
                'C\'est bon, zo is het.',
                'Och ja, wat moet je ervan zeggen.'
            ],
            'brabander': [
                'Hè ja, dat is gezellig zo.',
                'Lekker ding, zeg.',
                'Dat is toch mooi, hè.',
                'Ja hoor, dat kan wel.'
            ],
            'jordanees': [
                'Weet je wat, dat klopt helemaal.',
                'Precies joh, zo is het.',
                'Dat zeg ik ook altijd.',
                'Helemaal mee eens, kerel.'
            ]
        };
        
        const personaResponses = responses[persona] || responses['amsterdammer'];
        return personaResponses[Math.floor(Math.random() * personaResponses.length)];
    }
    
    generateMockLocationComment(persona, location) {
        const locationComments = {
            'amsterdammer': [
                'Zo, hier ben ik wel eens vaker geweest.',
                'Lekker plekje om even te stoppen.',
                'Typisch hier, altijd druk.',
                'Mooi uitzicht vandaag.'
            ],
            'belg': [
                'Alleeh, weer zo\'n plek waar iedereen naartoe wil...',
                'Pff, drukte overal.',
                'C\'est bon, laten we maar doorrijden.',
                'Och, maakt ook niet uit waar we zijn.'
            ]
        };
        
        const comments = locationComments[persona] || locationComments['amsterdammer'];
        return comments[Math.floor(Math.random() * comments.length)];
    }
    
    // Test scenarios
    runTestScenario(scenarioName) {
        const scenarios = {
            'morning_commute': () => {
                this.logDebug('Running morning commute scenario');
                this.simulateRoute('city_traffic');
                // Simulate morning time
                Object.defineProperty(Date.prototype, 'getHours', {
                    value: () => 8
                });
            },
            'highway_trip': () => {
                this.logDebug('Running highway trip scenario');
                this.simulateRoute('highway_trip');
            },
            'night_mode_test': () => {
                this.logDebug('Testing night mode');
                document.body.classList.add('night-mode');
                // Simulate night time
                Object.defineProperty(Date.prototype, 'getHours', {
                    value: () => 22
                });
            },
            'error_handling': () => {
                this.logDebug('Testing error scenarios');
                // Simulate GPS error
                if (window.locationManager) {
                    window.locationManager.handleError({ code: 1, message: 'Permission denied' });
                }
            }
        };
        
        const scenario = scenarios[scenarioName];
        if (scenario) {
            scenario();
        } else {
            this.logDebug(`Unknown test scenario: ${scenarioName}`);
        }
    }
    
    // Export debug data
    exportDebugData() {
        const debugData = {
            timestamp: Date.now(),
            metrics: this.performanceMetrics,
            log: this.debugLog,
            mockLocation: this.mockLocation,
            isSimulating: !!this.simulationInterval,
            userAgent: navigator.userAgent,
            location: window.location.href,
            localStorage: this.getLocalStorageData()
        };
        
        const dataStr = JSON.stringify(debugData, null, 2);
        const blob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `travelbot-debug-${Date.now()}.json`;
        a.click();
        
        URL.revokeObjectURL(url);
        this.logDebug('Debug data exported');
    }
    
    getLocalStorageData() {
        const data = {};
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('travelbot-')) {
                data[key] = localStorage.getItem(key);
            }
        }
        return data;
    }
    
    // Settings
    saveSettings() {
        const settings = {
            isEnabled: this.isEnabled,
            debugMode: this.debugMode,
            mockLocation: this.mockLocation
        };
        
        localStorage.setItem('travelbot-mock-settings', JSON.stringify(settings));
    }
    
    loadSettings() {
        const saved = localStorage.getItem('travelbot-mock-settings');
        if (saved) {
            try {
                const settings = JSON.parse(saved);
                this.isEnabled = settings.isEnabled || false;
                this.debugMode = settings.debugMode || false;
                this.mockLocation = settings.mockLocation || null;
            } catch (error) {
                console.warn('Could not load mock settings:', error);
            }
        }
    }
}

// Initialize when DOM is loaded
let mockManager;
document.addEventListener('DOMContentLoaded', () => {
    mockManager = new MockManager();
    
    // Add debug keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey) {
            switch (e.key) {
                case 'D':
                    e.preventDefault();
                    mockManager.isEnabled ? mockManager.disable() : mockManager.enable();
                    break;
                case 'L':
                    e.preventDefault();
                    mockManager.showMockLocationDialog();
                    break;
                case 'M':
                    e.preventDefault();
                    mockManager.simulateMovement();
                    break;
                case 'P':
                    e.preventDefault();
                    mockManager.showPerformanceLog();
                    break;
            }
        }
    });
});

// Export for other modules
window.MockManager = MockManager;
