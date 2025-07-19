/**
 * TravelBot v4.0 - Smart AI Observations
 * Intelligente locatiebewuste observaties die alleen zinnige opmerkingen maken
 */

class SmartObservationManager {
    constructor() {
        this.lastObservationLocation = null;
        this.lastObservationTime = null;
        this.observationHistory = [];
        this.minimumDistance = 500; // meters between observations
        this.minimumTime = 30000; // 30 seconds between observations
        this.maxHistorySize = 50;
        
        // Context awareness
        this.isNavigatingActive = false;
        this.currentPersona = null;
        this.currentLanguage = 'nl';
        
        // Location intelligence
        this.geocoder = new ReverseGeocoder();
        this.poiDetector = new PointOfInterestDetector();
        this.contextAnalyzer = new LocationContextAnalyzer();
        
        console.log('🧠 SmartObservationManager initialized');
    }

    // Main observation trigger
    async considerObservation(location, movement) {
        // Check if we should make an observation
        if (!this.shouldObserve(location, movement)) {
            return null;
        }

        try {
            // Analyze location context
            const context = await this.analyzeLocationContext(location);
            
            // Generate observation if context is interesting
            const observation = await this.generateObservation(context, location, movement);
            
            if (observation) {
                // Record this observation
                this.recordObservation(location, observation);
                return observation;
            }

        } catch (error) {
            console.error('❌ Observation generation failed:', error);
        }

        return null;
    }

    // Determine if we should make an observation
    shouldObserve(location, movement) {
        // Don't observe during active navigation instructions
        if (this.isNavigatingActive && window.navigationManager?.isCurrentlyNavigating()) {
            const currentStep = window.navigationManager.getCurrentStep();
            if (currentStep && window.navigationManager.calculateDistanceToStep) {
                const distanceToStep = window.navigationManager.calculateDistanceToStep(location, currentStep);
                if (distanceToStep < 600) { // Don't interrupt navigation within 600m of instruction
                    return false;
                }
            }
        }

        // Check minimum distance
        if (this.lastObservationLocation) {
            const distance = this.calculateDistance(
                location.latitude, location.longitude,
                this.lastObservationLocation.latitude, this.lastObservationLocation.longitude
            );
            
            if (distance < this.minimumDistance) {
                return false;
            }
        }

        // Check minimum time
        if (this.lastObservationTime) {
            const timeDiff = Date.now() - this.lastObservationTime;
            if (timeDiff < this.minimumTime) {
                return false;
            }
        }

        // Don't observe when stopped for short time (unless something interesting)
        if (movement && movement.speed < 2) {
            // Only observe when stopped if we're at an interesting location
            return Math.random() < 0.3; // 30% chance when stopped
        }

        return true;
    }

    // Analyze location context to determine if interesting
    async analyzeLocationContext(location) {
        const context = {
            location: location,
            address: null,
            pois: [],
            roadInfo: null,
            isInteresting: false,
            interestLevel: 0,
            observationTriggers: []
        };

        try {
            // Get address information
            context.address = await this.geocoder.reverseGeocode(location.latitude, location.longitude);
            
            // Find nearby points of interest
            context.pois = await this.poiDetector.findNearbyPOIs(location);
            
            // Analyze road information
            context.roadInfo = this.contextAnalyzer.analyzeRoad(context.address);
            
            // Determine interest level
            context.interestLevel = this.calculateInterestLevel(context);
            context.isInteresting = context.interestLevel > 50;
            
            // Identify specific triggers
            context.observationTriggers = this.identifyObservationTriggers(context);

        } catch (error) {
            console.error('❌ Context analysis failed:', error);
        }

        return context;
    }

    // Calculate how interesting this location is (0-100)
    calculateInterestLevel(context) {
        let interest = 0;
        
        // POI scoring
        if (context.pois.length > 0) {
            interest += Math.min(context.pois.length * 15, 40);
        }

        // Notable road names
        if (context.roadInfo?.hasInterestingName) {
            interest += 20;
        }

        // City center / tourist areas
        if (context.address?.district?.includes('centrum') || 
            context.address?.city?.toLowerCase().includes('amsterdam') ||
            context.address?.city?.toLowerCase().includes('rotterdam')) {
            interest += 30;
        }

        // Highway/motorway
        if (context.roadInfo?.type === 'highway') {
            interest += 10;
        }

        // Unusual place names
        if (this.hasUnusualPlaceName(context.address)) {
            interest += 25;
        }

        return Math.min(interest, 100);
    }

    // Identify specific triggers for observations
    identifyObservationTriggers(context) {
        const triggers = [];

        // POI triggers
        context.pois.forEach(poi => {
            if (poi.type === 'restaurant' && poi.rating > 4.0) {
                triggers.push({ type: 'good_restaurant', data: poi });
            }
            if (poi.type === 'tourist_attraction') {
                triggers.push({ type: 'attraction', data: poi });
            }
            if (poi.type === 'historical') {
                triggers.push({ type: 'historical', data: poi });
            }
        });

        // Road name triggers
        if (context.address?.road) {
            const roadName = context.address.road.toLowerCase();
            
            if (roadName.includes('dood') || roadName.includes('hel') || roadName.includes('duivel')) {
                triggers.push({ type: 'dark_street_name', data: { name: context.address.road } });
            }
            
            if (roadName.includes('bloem') || roadName.includes('roos') || roadName.includes('tulp')) {
                triggers.push({ type: 'flower_street', data: { name: context.address.road } });
            }
            
            if (roadName.includes('koning') || roadName.includes('koningin') || roadName.includes('prins')) {
                triggers.push({ type: 'royal_street', data: { name: context.address.road } });
            }
        }

        // City triggers  
        if (context.address?.city) {
            const city = context.address.city.toLowerCase();
            
            if (['hell', 'kut', 'puke', 'boring'].some(word => city.includes(word))) {
                triggers.push({ type: 'funny_city_name', data: { city: context.address.city } });
            }
        }

        // Traffic situation
        if (context.roadInfo?.type === 'highway' && context.location.speed < 5) {
            triggers.push({ type: 'traffic_jam', data: null });
        }

        return triggers;
    }

    // Generate observation based on context
    async generateObservation(context, location, movement) {
        if (!context.isInteresting || context.observationTriggers.length === 0) {
            return null;
        }

        // Get persona for tone
        const persona = this.currentPersona || 'sarcastic';
        
        // Select most interesting trigger
        const trigger = context.observationTriggers[0];
        
        let observation = null;

        switch (trigger.type) {
            case 'good_restaurant':
                observation = this.generateRestaurantObservation(trigger.data, persona);
                break;
                
            case 'attraction':
                observation = this.generateAttractionObservation(trigger.data, persona);
                break;
                
            case 'dark_street_name':
                observation = this.generateDarkStreetObservation(trigger.data, persona);
                break;
                
            case 'flower_street':
                observation = this.generateFlowerStreetObservation(trigger.data, persona);
                break;
                
            case 'royal_street':
                observation = this.generateRoyalStreetObservation(trigger.data, persona);
                break;
                
            case 'funny_city_name':
                observation = this.generateFunnyCityObservation(trigger.data, persona);
                break;
                
            case 'traffic_jam':
                observation = this.generateTrafficJamObservation(persona);
                break;
                
            case 'historical':
                observation = this.generateHistoricalObservation(trigger.data, persona);
                break;
        }

        return observation;
    }

    // Generate restaurant observations
    generateRestaurantObservation(poi, persona) {
        const templates = {
            sarcastic: [
                `Oh kijk, ${poi.name}. Weer zo'n tent waar ze denken dat kleine porties artistiek zijn.`,
                `${poi.name}? Ik hoop dat je honger hebt... en een tweede hypotheek.`,
                `Ah, ${poi.name}. Perfect voor als je van wachten houdt en graag teveel betaalt.`
            ],
            friendly: [
                `We passeren ${poi.name}! Ziet er goed uit, misschien voor een volgende keer?`,
                `${poi.name} heeft goede reviews. Onthouden voor later?`,
                `Kijk, daar is ${poi.name}. Ruikt vast heerlijk!`
            ]
        };

        const personalityTemplates = templates[persona] || templates.sarcastic;
        return personalityTemplates[Math.floor(Math.random() * personalityTemplates.length)];
    }

    // Generate street name observations  
    generateDarkStreetObservation(data, persona) {
        const templates = {
            sarcastic: [
                `${data.name}... Nou, dat klinkt vrolijk. Wie heeft deze straatnamen bedacht, Edgar Allan Poe?`,
                `We rijden door de ${data.name}. Gezellige naam. Vast populair bij makelaars.`,
                `${data.name}? Ik ruik al de verhuurwaarde dalen.`
            ],
            friendly: [
                `Interessante straatnaam: ${data.name}. Heeft vast een verhaal.`,
                `We passeren de ${data.name}. Bijzondere naam!`
            ]
        };

        const personalityTemplates = templates[persona] || templates.sarcastic;
        return personalityTemplates[Math.floor(Math.random() * personalityTemplates.length)];
    }

    generateFlowerStreetObservation(data, persona) {
        const templates = {
            sarcastic: [
                `${data.name}... Laat me raden, er groeit hier helemaal niks?`,
                `De ${data.name}. Klinkt idyllisch. Ik zie vooral asfalt.`,
                `${data.name}? Hopelijk ruikt het hier beter dan het eruitziet.`
            ],
            friendly: [
                `We rijden door de ${data.name}. Wat een mooie naam!`,
                `${data.name} - klinkt als een aangename buurt.`
            ]
        };

        const personalityTemplates = templates[persona] || templates.sarcastic;
        return personalityTemplates[Math.floor(Math.random() * personalityTemplates.length)];
    }

    generateTrafficJamObservation(persona) {
        const templates = {
            sarcastic: [
                `File! Geweldig. Precies wat we nodig hadden om de dag compleet te maken.`,
                `We staan stil op de snelweg. Wat een verassing. Wie had dat zien aankomen?`,
                `File-tijd! Perfect moment om na te denken over je levensskeuzes.`,
                `Kijk, we gaan nergens naartoe. Dit is precies waarom ik geen benen heb.`
            ],
            friendly: [
                `We staan in de file. Goed moment voor een spelletje of muziek?`,
                `File gedetecteerd. Tijd voor wat ontspanning.`,
                `We staan wat stil. Misschien even de radio aanzetten?`
            ]
        };

        const personalityTemplates = templates[persona] || templates.sarcastic;
        return personalityTemplates[Math.floor(Math.random() * personalityTemplates.length)];
    }

    // Helper: Check for unusual place names
    hasUnusualPlaceName(address) {
        if (!address) return false;

        const funnyWords = ['puke', 'hell', 'boring', 'ugly', 'kut', 'lul', 'dood', 'gek'];
        const placeName = `${address.city || ''} ${address.road || ''}`.toLowerCase();
        
        return funnyWords.some(word => placeName.includes(word));
    }

    // Record observation to prevent repeats
    recordObservation(location, observation) {
        this.lastObservationLocation = location;
        this.lastObservationTime = Date.now();
        
        this.observationHistory.push({
            location: location,
            observation: observation,
            timestamp: Date.now()
        });

        // Keep history manageable
        if (this.observationHistory.length > this.maxHistorySize) {
            this.observationHistory.shift();
        }
    }

    // Calculate distance between two points
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

    // Public API
    setNavigationState(isActive) {
        this.isNavigatingActive = isActive;
    }

    setPersona(persona) {
        this.currentPersona = persona;
    }

    setLanguage(language) {
        this.currentLanguage = language;
    }
}

/**
 * Reverse Geocoder - Convert coordinates to address
 */
class ReverseGeocoder {
    constructor() {
        this.cache = new Map();
        this.cacheTimeout = 300000; // 5 minutes
    }

    async reverseGeocode(lat, lng) {
        const key = `${lat.toFixed(4)},${lng.toFixed(4)}`;
        
        // Check cache
        if (this.cache.has(key)) {
            const cached = this.cache.get(key);
            if (Date.now() - cached.timestamp < this.cacheTimeout) {
                return cached.data;
            }
        }

        try {
            // Use Nominatim (free OpenStreetMap service)
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=nl`
            );

            if (!response.ok) {
                throw new Error(`Geocoding failed: ${response.status}`);
            }

            const data = await response.json();
            
            const address = {
                road: data.address?.road || data.address?.pedestrian || null,
                city: data.address?.city || data.address?.town || data.address?.village || null,
                district: data.address?.suburb || data.address?.district || null,
                postcode: data.address?.postcode || null,
                country: data.address?.country || null,
                formatted: data.display_name || null
            };

            // Cache result
            this.cache.set(key, {
                data: address,
                timestamp: Date.now()
            });

            return address;

        } catch (error) {
            console.error('❌ Reverse geocoding failed:', error);
            return null;
        }
    }
}

/**
 * Point of Interest Detector
 */
class PointOfInterestDetector {
    constructor() {
        this.overpassUrl = 'https://overpass-api.de/api/interpreter';
    }

    async findNearbyPOIs(location, radius = 200) {
        try {
            // Query Overpass API for nearby POIs
            const query = `
                [out:json][timeout:5];
                (
                    node(around:${radius},${location.latitude},${location.longitude})[amenity~"^(restaurant|cafe|museum|theatre|cinema|attraction)$"];
                    node(around:${radius},${location.latitude},${location.longitude})[tourism~"^(attraction|museum)$"];
                );
                out 10;
            `;

            const response = await fetch(this.overpassUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'text/plain' },
                body: query
            });

            if (!response.ok) {
                throw new Error(`POI query failed: ${response.status}`);
            }

            const data = await response.json();
            
            return data.elements.map(element => ({
                name: element.tags.name || 'Onbekend',
                type: element.tags.amenity || element.tags.tourism || 'unknown',
                cuisine: element.tags.cuisine || null,
                rating: parseFloat(element.tags.rating) || null,
                distance: this.calculateDistance(
                    location.latitude, location.longitude,
                    element.lat, element.lon
                )
            })).filter(poi => poi.name !== 'Onbekend');

        } catch (error) {
            console.error('❌ POI detection failed:', error);
            return [];
        }
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
}

/**
 * Location Context Analyzer
 */
class LocationContextAnalyzer {
    analyzeRoad(address) {
        if (!address || !address.road) {
            return { type: 'unknown', hasInterestingName: false };
        }

        const road = address.road.toLowerCase();
        
        // Determine road type
        let type = 'street';
        if (road.includes('snelweg') || road.includes('autoweg') || road.match(/a\d+/)) {
            type = 'highway';
        } else if (road.includes('laan') || road.includes('boulevard')) {
            type = 'avenue';
        }

        // Check for interesting names
        const interestingKeywords = [
            'dood', 'hel', 'duivel', 'gek', 'rare', 'vreemd',
            'bloem', 'roos', 'tulp', 'lelie',
            'koning', 'koningin', 'prins', 'graaf'
        ];
        
        const hasInterestingName = interestingKeywords.some(keyword => 
            road.includes(keyword)
        );

        return { type, hasInterestingName };
    }
}

// Initialize smart observation manager
window.smartObservationManager = new SmartObservationManager();
