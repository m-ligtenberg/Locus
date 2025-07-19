/**
 * TravelBot v4.0 - Location & Route Manager
 * GPS tracking met bewegingsanalyse, route-detectie en navigatie-ondersteuning
 */

class LocationManager {
    constructor() {
        this.isSupported = false;
        this.isTracking = false;
        this.watchId = null;
        this.currentLocation = null;
        this.previousLocation = null;
        this.locationHistory = [];
        this.speed = 0;
        this.direction = null;
        this.routeType = 'detecting'; // detecting, straight, turning, stopped
        
        // Movement analysis
        this.movementBuffer = [];
        this.maxHistorySize = 20;
        this.stoppedThreshold = 2; // km/h
        this.turningThreshold = 15; // degrees
        this.smoothingWindow = 3;
        
        // Callbacks
        this.onLocationCallback = null;
        this.onMovementCallback = null;
        this.onRouteChangeCallback = null;
        this.onErrorCallback = null;
        
        this.init();
    }
    
    init() {
        this.checkSupport();
        this.loadSettings();
        this.requestPermissionWithFallback();
    }
    
    checkSupport() {
        this.isSupported = 'geolocation' in navigator;
        
        if (!this.isSupported) {
            console.error('Geolocation API niet ondersteund');
            this.showError('GPS niet ondersteund in deze browser');
            return false;
        }
        
        // Check if HTTPS (required for location on mobile)
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            console.warn('HTTPS vereist voor GPS op mobiele apparaten');
            this.showWarning('HTTPS vereist voor GPS op mobiel');
        }
        
        return true;
    }
    
    async requestPermissionWithFallback() {
        if (!this.isSupported) return false;
        
        try {
            // Check current permission state
            if ('permissions' in navigator) {
                const permission = await navigator.permissions.query({name: 'geolocation'});
                console.log('GPS permission status:', permission.state);
                
                permission.onchange = () => {
                    console.log('GPS permission changed:', permission.state);
                    if (permission.state === 'granted' && !this.isTracking) {
                        this.startTracking();
                    } else if (permission.state === 'denied') {
                        this.showError('GPS toegang geweigerd');
                    }
                };
            }
            
            return true;
        } catch (error) {
            console.warn('Kon permission status niet controleren:', error);
            return true; // Continue anyway
        }
    }
    
    startTracking() {
        if (!this.isSupported) return false;
        
        if (this.isTracking) {
            console.log('Location tracking al actief');
            return true;
        }
        
        this.updateStatus('gps_connecting');
        
        const options = {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 60000
        };
        
        this.watchId = navigator.geolocation.watchPosition(
            (position) => this.handlePosition(position),
            (error) => this.handleError(error),
            options
        );
        
        this.isTracking = true;
        console.log('GPS tracking gestart');
        
        // Fallback timer for slow GPS
        setTimeout(() => {
            if (!this.currentLocation) {
                console.warn('GPS duurt lang - probeer mock locatie voor development');
                this.suggestMockLocation();
            }
        }, 10000);
        
        return true;
    }
    
    handlePosition(position) {
        const location = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            speed: position.coords.speed,
            heading: position.coords.heading,
            timestamp: position.timestamp
        };
        
        console.log('GPS locatie ontvangen:', {
            lat: location.latitude.toFixed(6),
            lng: location.longitude.toFixed(6),
            accuracy: Math.round(location.accuracy),
            speed: location.speed
        });
        
        this.processLocation(location);
    }
    
    processLocation(location) {
        this.previousLocation = this.currentLocation;
        this.currentLocation = location;
        
        // Add to history
        this.locationHistory.push({
            ...location,
            processedAt: Date.now()
        });
        
        // Limit history size
        if (this.locationHistory.length > this.maxHistorySize) {
            this.locationHistory = this.locationHistory.slice(-this.maxHistorySize);
        }
        
        // Calculate movement metrics
        this.calculateMovementMetrics();
        
        // Analyze route pattern
        this.analyzeRoute();
        
        // Update UI
        this.updateLocationUI();
        this.updateStatus('gps_connected');
        
        // Trigger callbacks
        if (this.onLocationCallback) {
            this.onLocationCallback(this.currentLocation, this.getLocationSummary());
        }
        
        // Get location name
        this.reverseGeocode(location);
    }
    
    calculateMovementMetrics() {
        if (!this.previousLocation || !this.currentLocation) return;
        
        // Calculate distance and speed
        const distance = this.calculateDistance(
            this.previousLocation.latitude,
            this.previousLocation.longitude,
            this.currentLocation.latitude,
            this.currentLocation.longitude
        );
        
        const timeDiff = (this.currentLocation.timestamp - this.previousLocation.timestamp) / 1000; // seconds
        
        // Calculate speed (km/h) if we don't have GPS speed
        if (!this.currentLocation.speed && timeDiff > 0) {
            this.speed = (distance / timeDiff) * 3.6; // m/s to km/h
        } else {
            this.speed = (this.currentLocation.speed || 0) * 3.6; // m/s to km/h
        }
        
        // Calculate direction
        if (distance > 10) { // Only calculate for meaningful movement
            this.direction = this.calculateBearing(
                this.previousLocation.latitude,
                this.previousLocation.longitude,
                this.currentLocation.latitude,
                this.currentLocation.longitude
            );
        }
        
        // Add to movement buffer for smoothing
        this.movementBuffer.push({
            speed: this.speed,
            direction: this.direction,
            timestamp: this.currentLocation.timestamp
        });
        
        if (this.movementBuffer.length > this.smoothingWindow) {
            this.movementBuffer = this.movementBuffer.slice(-this.smoothingWindow);
        }
        
        // Smooth the values
        this.speed = this.smoothSpeed();
        this.direction = this.smoothDirection();
        
        console.log('Movement metrics:', {
            speed: Math.round(this.speed),
            direction: this.direction ? Math.round(this.direction) : null,
            distance: Math.round(distance)
        });
    }
    
    analyzeRoute() {
        if (this.movementBuffer.length < 2) return;
        
        const previousRouteType = this.routeType;
        
        // Determine current route type
        if (this.speed < this.stoppedThreshold) {
            this.routeType = 'stopped';
        } else {
            // Check for turns
            const directionChange = this.getDirectionChange();
            if (directionChange > this.turningThreshold) {
                this.routeType = 'turning';
            } else {
                this.routeType = 'straight';
            }
        }
        
        // Trigger route change callback
        if (previousRouteType !== this.routeType) {
            console.log('Route type changed:', previousRouteType, '->', this.routeType);
            
            if (this.onRouteChangeCallback) {
                this.onRouteChangeCallback(this.routeType, {
                    previousType: previousRouteType,
                    speed: this.speed,
                    direction: this.direction
                });
            }
            
            this.updateRouteUI();
        }
        
        // Trigger movement callback
        if (this.onMovementCallback) {
            this.onMovementCallback({
                speed: this.speed,
                direction: this.direction,
                routeType: this.routeType,
                location: this.currentLocation
            });
        }
    }
    
    getDirectionChange() {
        if (this.movementBuffer.length < 2) return 0;
        
        const recent = this.movementBuffer.slice(-2);
        if (!recent[0].direction || !recent[1].direction) return 0;
        
        let change = Math.abs(recent[1].direction - recent[0].direction);
        
        // Handle 360° wraparound
        if (change > 180) {
            change = 360 - change;
        }
        
        return change;
    }
    
    smoothSpeed() {
        if (this.movementBuffer.length === 0) return 0;
        
        const speeds = this.movementBuffer.map(m => m.speed);
        return speeds.reduce((sum, speed) => sum + speed, 0) / speeds.length;
    }
    
    smoothDirection() {
        const validDirections = this.movementBuffer
            .map(m => m.direction)
            .filter(d => d !== null);
            
        if (validDirections.length === 0) return null;
        
        // Simple average - could be improved for circular mean
        return validDirections.reduce((sum, dir) => sum + dir, 0) / validDirections.length;
    }
    
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth radius in meters
        const dLat = this.toRadians(lat2 - lat1);
        const dLon = this.toRadians(lon2 - lon1);
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    calculateBearing(lat1, lon1, lat2, lon2) {
        const dLon = this.toRadians(lon2 - lon1);
        const lat1Rad = this.toRadians(lat1);
        const lat2Rad = this.toRadians(lat2);
        
        const y = Math.sin(dLon) * Math.cos(lat2Rad);
        const x = Math.cos(lat1Rad) * Math.sin(lat2Rad) -
                Math.sin(lat1Rad) * Math.cos(lat2Rad) * Math.cos(dLon);
        
        let bearing = this.toDegrees(Math.atan2(y, x));
        return (bearing + 360) % 360; // Normalize to 0-360
    }
    
    toRadians(degrees) {
        return degrees * (Math.PI / 180);
    }
    
    toDegrees(radians) {
        return radians * (180 / Math.PI);
    }
    
    async reverseGeocode(location) {
        try {
            // Use a free geocoding service (example: OpenStreetMap Nominatim)
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${location.latitude}&lon=${location.longitude}&zoom=18&addressdetails=1`,
                {
                    headers: {
                        'User-Agent': 'TravelBot/3.0'
                    }
                }
            );
            
            if (response.ok) {
                const data = await response.json();
                const address = this.formatAddress(data);
                this.updateLocationName(address);
            }
        } catch (error) {
            console.warn('Reverse geocoding failed:', error);
            this.updateLocationName(`${location.latitude.toFixed(4)}, ${location.longitude.toFixed(4)}`);
        }
    }
    
    formatAddress(data) {
        const address = data.address;
        if (!address) return data.display_name;
        
        const parts = [];
        
        // Add street info
        if (address.road) {
            let street = address.road;
            if (address.house_number) {
                street = `${address.road} ${address.house_number}`;
            }
            parts.push(street);
        }
        
        // Add city/town
        const city = address.city || address.town || address.village || address.municipality;
        if (city) {
            parts.push(city);
        }
        
        // Add country for international travel
        if (address.country && address.country !== 'Nederland') {
            parts.push(address.country);
        }
        
        return parts.length > 0 ? parts.join(', ') : data.display_name;
    }
    
    handleError(error) {
        console.error('GPS Error:', error);
        
        const errorMessages = {
            1: 'GPS toegang geweigerd. Sta locatietoegrang toe in je browser.',
            2: 'GPS positie niet beschikbaar. Controleer je internetverbinding.',
            3: 'GPS timeout. Probeer het opnieuw.',
        };
        
        const message = errorMessages[error.code] || `GPS fout: ${error.message}`;
        this.showError(message);
        this.updateStatus('gps_error');
        
        if (this.onErrorCallback) {
            this.onErrorCallback(error.code, message);
        }
        
        // Suggest mock location for development
        if (error.code === 1 || error.code === 2) {
            this.suggestMockLocation();
        }
    }
    
    stopTracking() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
        
        this.isTracking = false;
        console.log('GPS tracking gestopt');
    }
    
    // Mock location for development/testing
    suggestMockLocation() {
        if (window.mockManager && window.mockManager.isEnabled()) {
            console.log('Mock locatie beschikbaar - activeer development mode');
            window.mockManager.showMockLocationDialog();
        }
    }
    
    setMockLocation(lat, lng, options = {}) {
        const mockLocation = {
            latitude: lat,
            longitude: lng,
            accuracy: options.accuracy || 10,
            speed: options.speed || 0,
            heading: options.heading || null,
            timestamp: Date.now()
        };
        
        console.log('Mock locatie ingesteld:', mockLocation);
        this.processLocation(mockLocation);
    }
    
    // Movement simulation for development
    simulateMovement(route) {
        let index = 0;
        const interval = setInterval(() => {
            if (index >= route.length) {
                clearInterval(interval);
                return;
            }
            
            const point = route[index];
            this.setMockLocation(point.lat, point.lng, point.options);
            index++;
        }, 2000);
        
        return interval;
    }
    
    // UI Methods
    updateLocationUI() {
        // Update location display
        const locationElement = document.getElementById('location-details');
        if (locationElement && this.currentLocation) {
            const lat = this.currentLocation.latitude.toFixed(6);
            const lng = this.currentLocation.longitude.toFixed(6);
            const accuracy = Math.round(this.currentLocation.accuracy);
            locationElement.innerHTML = `📍 ${lat}, ${lng} <small>(±${accuracy}m)</small>`;
        }
        
        // Update speed indicator
        const speedElement = document.getElementById('speed-indicator');
        if (speedElement) {
            const speedKmh = Math.round(this.speed);
            speedElement.textContent = `🚗 ${speedKmh} km/h`;
        }
        
        // Update direction indicator  
        const directionElement = document.getElementById('direction-indicator');
        if (directionElement) {
            if (this.direction !== null) {
                const compass = this.getCompassDirection(this.direction);
                directionElement.textContent = `🧭 ${compass}`;
            } else {
                directionElement.textContent = '🧭 --';
            }
        }
    }
    
    updateLocationName(address) {
        const locationElement = document.getElementById('location-details');
        if (locationElement) {
            locationElement.innerHTML = `📍 ${address}`;
        }
    }
    
    updateRouteUI() {
        const routeElement = document.getElementById('route-status');
        if (routeElement) {
            const statusMap = {
                'detecting': translator?.translate('route_detecting') || '🧭 Route: Detecteren...',
                'straight': translator?.translate('route_straight') || '🧭 Route: Rechtdoor',
                'turning': translator?.translate('route_turning') || '🧭 Route: Bochten',
                'stopped': translator?.translate('route_stopped') || '🧭 Route: Stilstand'
            };
            
            routeElement.textContent = statusMap[this.routeType] || statusMap['detecting'];
        }
    }
    
    updateStatus(statusKey) {
        const statusElement = document.getElementById('location-status');
        if (statusElement) {
            const statusText = translator?.translate(statusKey) || statusKey;
            statusElement.textContent = statusText;
        }
    }
    
    getCompassDirection(degrees) {
        const directions = ['N', 'NNO', 'NO', 'ONO', 'O', 'OZO', 'ZO', 'ZZO', 'Z', 'ZZW', 'ZW', 'WZW', 'W', 'WNW', 'NW', 'NNW'];
        const index = Math.round(degrees / 22.5) % 16;
        return directions[index];
    }
    
    getLocationSummary() {
        return {
            location: this.currentLocation,
            speed: this.speed,
            direction: this.direction,
            routeType: this.routeType,
            accuracy: this.currentLocation?.accuracy,
            isMoving: this.speed > this.stoppedThreshold
        };
    }
    
    showError(message) {
        console.error('Location error:', message);
        
        // Show in UI
        const statusElement = document.getElementById('location-status');
        if (statusElement) {
            statusElement.textContent = `❌ ${message}`;
        }
    }
    
    showWarning(message) {
        console.warn('Location warning:', message);
        
        // Show in UI with less alarming styling
        const statusElement = document.getElementById('location-status');
        if (statusElement) {
            statusElement.textContent = `⚠️ ${message}`;
        }
    }
    
    // Settings
    saveSettings() {
        const settings = {
            trackingEnabled: this.isTracking,
            stoppedThreshold: this.stoppedThreshold,
            turningThreshold: this.turningThreshold
        };
        
        localStorage.setItem('travelbot-location-settings', JSON.stringify(settings));
    }
    
    loadSettings() {
        const saved = localStorage.getItem('travelbot-location-settings');
        if (saved) {
            try {
                const settings = JSON.parse(saved);
                this.stoppedThreshold = settings.stoppedThreshold || this.stoppedThreshold;
                this.turningThreshold = settings.turningThreshold || this.turningThreshold;
            } catch (error) {
                console.warn('Kon locatie instellingen niet laden:', error);
            }
        }
    }
    
    // Callback setters
    onLocation(callback) {
        this.onLocationCallback = callback;
    }
    
    onMovement(callback) {
        this.onMovementCallback = callback;
    }
    
    onRouteChange(callback) {
        this.onRouteChangeCallback = callback;
    }
    
    onError(callback) {
        this.onErrorCallback = callback;
    }

    // v4.0 Navigation integration methods
    getCurrentLocation() {
        return this.currentLocation;
    }

    getLocationHistory() {
        return this.locationHistory.slice();
    }

    getCurrentMovement() {
        return {
            speed: this.speed,
            direction: this.direction,
            routeType: this.routeType
        };
    }

    // Force location update for navigation
    requestLocationUpdate() {
        if (this.isTracking && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => this.handleLocationSuccess(position),
                (error) => this.handleLocationError(error),
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 5000
                }
            );
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
}

// Initialize when DOM is loaded
let locationManager;
document.addEventListener('DOMContentLoaded', () => {
    locationManager = new LocationManager();
    
    // Auto-start tracking when page loads (with user gesture)
    document.addEventListener('click', () => {
        if (!locationManager.isTracking) {
            locationManager.startTracking();
        }
    }, { once: true });
});

// Export for other modules
window.LocationManager = LocationManager;
