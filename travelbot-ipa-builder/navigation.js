/**
 * TravelBot v4.0 - Navigation System
 * Volledig navigatiesysteem met routing API en real-time instructies
 */

class NavigationManager {
    constructor() {
        this.isNavigating = false;
        this.currentRoute = null;
        this.routeSteps = [];
        this.currentStepIndex = 0;
        this.triggerDistances = [500, 100, 20]; // meters
        this.lastSpokenStep = null;
        this.routingService = null;
        
        // Navigation state
        this.destination = null;
        this.totalDistance = 0;
        this.totalDuration = 0;
        this.remainingDistance = 0;
        this.remainingDuration = 0;
        
        // Callbacks
        this.onInstructionCallback = null;
        this.onArrivalCallback = null;
        this.onRouteUpdateCallback = null;
        this.onNavigationStartCallback = null;
        this.onNavigationStopCallback = null;
        
        this.init();
    }

    init() {
        this.setupRoutingService();
        console.log('🧭 NavigationManager initialized');
    }

    setupRoutingService() {
        // Use OpenRouteService as primary routing service
        this.routingService = new OpenRouteServiceClient();
    }

    // Start navigation to destination
    async startNavigation(destination, origin = null) {
        try {
            console.log('🚀 Starting navigation to:', destination);
            
            // Get current location if origin not provided
            if (!origin && window.locationManager) {
                origin = window.locationManager.getCurrentLocation();
            }

            if (!origin) {
                throw new Error('Geen startlocatie beschikbaar');
            }

            // Calculate route
            const route = await this.routingService.calculateRoute(origin, destination);
            
            if (!route || !route.steps || route.steps.length === 0) {
                throw new Error('Geen route gevonden');
            }

            // Setup navigation state
            this.currentRoute = route;
            this.routeSteps = this.parseRouteSteps(route.steps);
            this.currentStepIndex = 0;
            this.destination = destination;
            this.totalDistance = route.summary.distance;
            this.totalDuration = route.summary.duration;
            this.remainingDistance = this.totalDistance;
            this.remainingDuration = this.totalDuration;
            this.isNavigating = true;
            this.lastSpokenStep = null;

            // Start navigation monitoring
            this.startNavigationMonitoring();
            
            // Notify app
            if (this.onNavigationStartCallback) {
                this.onNavigationStartCallback(route);
            }

            // Give initial instruction
            this.speakInstruction(`Navigatie gestart. ${this.getRouteOverview()}`);

            console.log('✅ Navigation started successfully');
            return route;

        } catch (error) {
            console.error('❌ Navigation start failed:', error);
            throw error;
        }
    }

    // Stop navigation
    stopNavigation() {
        if (!this.isNavigating) return;

        console.log('🛑 Stopping navigation');
        
        this.isNavigating = false;
        this.currentRoute = null;
        this.routeSteps = [];
        this.currentStepIndex = 0;
        this.destination = null;
        this.lastSpokenStep = null;

        if (this.onNavigationStopCallback) {
            this.onNavigationStopCallback();
        }

        this.speakInstruction('Navigatie gestopt');
    }

    // Parse route steps from routing service
    parseRouteSteps(rawSteps) {
        return rawSteps.map((step, index) => ({
            id: index,
            instruction: this.formatInstruction(step),
            distance: step.distance,
            duration: step.duration,
            coordinates: step.maneuver?.location || null,
            roadName: step.name || '',
            direction: step.maneuver?.bearing_after || 0,
            maneuver: step.maneuver?.type || 'straight',
            hasSpoken: false,
            triggerPoints: this.calculateTriggerPoints(step.distance)
        }));
    }

    // Format instruction for TTS
    formatInstruction(step) {
        const maneuver = step.maneuver?.type || 'straight';
        const roadName = step.name || '';
        const distance = Math.round(step.distance);

        let instruction = '';

        switch (maneuver) {
            case 'turn-left':
                instruction = 'Sla linksaf';
                break;
            case 'turn-right':
                instruction = 'Sla rechtsaf';
                break;
            case 'turn-slight-left':
                instruction = 'Ga linksaf';
                break;
            case 'turn-slight-right':
                instruction = 'Ga rechtsaf';
                break;
            case 'continue':
            case 'straight':
                instruction = 'Ga rechtdoor';
                break;
            case 'uturn':
                instruction = 'Keer om';
                break;
            case 'merge':
                instruction = 'Voeg in';
                break;
            case 'roundabout-enter':
                instruction = 'Ga de rotonde op';
                break;
            case 'roundabout-exit':
                instruction = 'Verlaat de rotonde';
                break;
            default:
                instruction = 'Ga rechtdoor';
        }

        // Add road name if available
        if (roadName && roadName !== '' && roadName !== 'Unknown') {
            instruction += ` naar de ${roadName}`;
        }

        return instruction;
    }

    // Calculate trigger points for instruction
    calculateTriggerPoints(stepDistance) {
        const triggers = [];
        
        this.triggerDistances.forEach(distance => {
            if (stepDistance > distance) {
                triggers.push(distance);
            }
        });

        // Always add immediate trigger
        triggers.push(0);
        
        return triggers.sort((a, b) => b - a); // Sort descending
    }

    // Monitor navigation progress
    startNavigationMonitoring() {
        if (!this.isNavigating) return;

        // Listen to location updates
        if (window.locationManager) {
            window.locationManager.onLocation((location) => {
                this.updateNavigationProgress(location);
            });
        }
    }

    // Update navigation based on current location
    updateNavigationProgress(currentLocation) {
        if (!this.isNavigating || !currentLocation || this.routeSteps.length === 0) {
            return;
        }

        const currentStep = this.routeSteps[this.currentStepIndex];
        if (!currentStep) {
            // Navigation complete
            this.handleArrival();
            return;
        }

        // Calculate distance to next maneuver
        const distanceToStep = this.calculateDistanceToStep(currentLocation, currentStep);
        
        // Update remaining distance/duration
        this.updateRemainingStats(distanceToStep);

        // Check if we should give instruction
        this.checkInstructionTriggers(distanceToStep, currentStep);

        // Check if we've passed this step
        if (distanceToStep < 20) { // 20 meters threshold
            this.advanceToNextStep();
        }

        // Notify app of progress
        if (this.onRouteUpdateCallback) {
            this.onRouteUpdateCallback({
                currentStep,
                distanceToStep,
                remainingDistance: this.remainingDistance,
                remainingDuration: this.remainingDuration,
                progress: ((this.totalDistance - this.remainingDistance) / this.totalDistance) * 100
            });
        }
    }

    // Calculate distance to step coordinates
    calculateDistanceToStep(currentLocation, step) {
        if (!step.coordinates) {
            return 0;
        }

        return this.calculateDistance(
            currentLocation.latitude,
            currentLocation.longitude,
            step.coordinates[1], // lat
            step.coordinates[0]  // lng
        );
    }

    // Haversine distance calculation
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Check if we should give instruction based on distance
    checkInstructionTriggers(distanceToStep, currentStep) {
        for (const triggerDistance of currentStep.triggerPoints) {
            if (distanceToStep <= triggerDistance && 
                (!this.lastSpokenStep || this.lastSpokenStep.id !== currentStep.id || 
                 !this.lastSpokenStep.triggeredDistances.includes(triggerDistance))) {
                
                // Speak instruction
                let instructionText = '';
                
                if (triggerDistance > 0) {
                    instructionText = `Over ${triggerDistance} meter, ${currentStep.instruction.toLowerCase()}`;
                } else {
                    instructionText = `Nu ${currentStep.instruction.toLowerCase()}`;
                }

                this.speakInstruction(instructionText);

                // Mark as spoken
                if (!this.lastSpokenStep || this.lastSpokenStep.id !== currentStep.id) {
                    this.lastSpokenStep = {
                        id: currentStep.id,
                        triggeredDistances: [triggerDistance]
                    };
                } else {
                    this.lastSpokenStep.triggeredDistances.push(triggerDistance);
                }

                break; // Only trigger one instruction at a time
            }
        }
    }

    // Move to next step
    advanceToNextStep() {
        this.currentStepIndex++;
        console.log(`📍 Advanced to step ${this.currentStepIndex}`);
    }

    // Update remaining statistics
    updateRemainingStats(distanceToCurrentStep) {
        // Calculate remaining distance
        let remaining = distanceToCurrentStep;
        
        for (let i = this.currentStepIndex + 1; i < this.routeSteps.length; i++) {
            remaining += this.routeSteps[i].distance;
        }

        this.remainingDistance = remaining;
        this.remainingDuration = (remaining / 1000) * 60; // Rough estimate: 1 minute per km
    }

    // Handle arrival at destination
    handleArrival() {
        console.log('🎯 Arrived at destination!');
        
        this.speakInstruction('U bent aangekomen op uw bestemming');
        
        if (this.onArrivalCallback) {
            this.onArrivalCallback();
        }

        this.stopNavigation();
    }

    // Get route overview for initial instruction
    getRouteOverview() {
        if (!this.currentRoute) return '';

        const distanceKm = Math.round(this.totalDistance / 1000);
        const durationMin = Math.round(this.totalDuration / 60);

        return `Route van ${distanceKm} kilometer, ongeveer ${durationMin} minuten rijden.`;
    }

    // Speak instruction via TTS
    speakInstruction(text) {
        console.log(`🔊 Navigation: ${text}`);
        
        if (this.onInstructionCallback) {
            this.onInstructionCallback(text);
        }

        // Use TTS manager if available
        if (window.ttsManager) {
            window.ttsManager.speak(text, { priority: 'high' });
        }
    }

    // Public API methods
    isCurrentlyNavigating() {
        return this.isNavigating;
    }

    getCurrentRoute() {
        return this.currentRoute;
    }

    getCurrentStep() {
        return this.routeSteps[this.currentStepIndex] || null;
    }

    getRemainingDistance() {
        return this.remainingDistance;
    }

    getRemainingDuration() {
        return this.remainingDuration;
    }

    // Event listener setup
    onInstruction(callback) {
        this.onInstructionCallback = callback;
    }

    onArrival(callback) {
        this.onArrivalCallback = callback;
    }

    onRouteUpdate(callback) {
        this.onRouteUpdateCallback = callback;
    }

    onNavigationStart(callback) {
        this.onNavigationStartCallback = callback;
    }

    onNavigationStop(callback) {
        this.onNavigationStopCallback = callback;
    }
}

/**
 * OpenRouteService API Client
 */
class OpenRouteServiceClient {
    constructor() {
        this.apiKey = '5b3ce3597851110001cf6248b8a1f99b8c1f42c0ab0e433b9f39ad48'; // Free tier key
        this.baseUrl = 'https://api.openrouteservice.org/v2';
    }

    async calculateRoute(origin, destination) {
        try {
            const url = `${this.baseUrl}/directions/driving-car`;
            
            const requestBody = {
                coordinates: [
                    [origin.longitude, origin.latitude],
                    [destination.lng || destination.longitude, destination.lat || destination.latitude]
                ],
                format: 'geojson',
                instructions: true,
                language: 'nl'
            };

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Authorization': this.apiKey,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestBody)
            });

            if (!response.ok) {
                throw new Error(`Routing API error: ${response.status}`);
            }

            const data = await response.json();
            
            return this.parseOpenRouteServiceResponse(data);

        } catch (error) {
            console.error('❌ Route calculation failed:', error);
            throw error;
        }
    }

    parseOpenRouteServiceResponse(data) {
        const feature = data.features[0];
        const properties = feature.properties;
        
        return {
            coordinates: feature.geometry.coordinates,
            summary: {
                distance: properties.summary.distance,
                duration: properties.summary.duration
            },
            steps: properties.segments[0].steps.map(step => ({
                distance: step.distance,
                duration: step.duration,
                instruction: step.instruction,
                name: step.name || '',
                maneuver: {
                    type: this.mapManeuverType(step.type),
                    location: step.maneuver?.location || null,
                    bearing_after: step.maneuver?.bearing_after || 0
                }
            }))
        };
    }

    mapManeuverType(orsType) {
        const mapping = {
            0: 'straight',
            1: 'turn-right', 
            2: 'turn-left',
            3: 'turn-slight-right',
            4: 'turn-slight-left',
            5: 'continue',
            6: 'roundabout-enter',
            7: 'roundabout-exit',
            8: 'uturn',
            11: 'merge'
        };

        return mapping[orsType] || 'straight';
    }
}

// Initialize navigation manager
window.navigationManager = new NavigationManager();
