// Core types for Locus v5.0
export interface Location {
  id: string;
  latitude: number;
  longitude: number;
  accuracy: number;
  timestamp: Date;
  speed?: number;
  heading?: number;
}

export interface User {
  id: string;
  email: string;
  name: string;
  preferences: UserPreferences;
  createdAt: Date;
  updatedAt: Date;
}

export interface UserPreferences {
  language: 'nl' | 'en' | 'de' | 'fr';
  persona: 'amsterdammer' | 'belgique' | 'brabander' | 'jordanees';
  voiceEnabled: boolean;
  nightMode: boolean;
  gpsAccuracy: 'high' | 'medium' | 'low';
  observationFrequency: 'high' | 'medium' | 'low';
}

export interface Route {
  id: string;
  userId: string;
  name: string;
  startLocation: Location;
  endLocation: Location;
  waypoints: Location[];
  distance: number; // meters
  duration: number; // seconds
  status: 'planning' | 'active' | 'completed' | 'paused';
  createdAt: Date;
  steps: RouteStep[];
}

export interface RouteStep {
  id: string;
  instruction: string;
  distance: number;
  duration: number;
  maneuver: string;
  coordinates: [number, number][];
}

export interface AIObservation {
  id: string;
  userId: string;
  locationId: string;
  content: string;
  persona: string;
  language: string;
  confidence: number;
  timestamp: Date;
  contextData: {
    nearbyPOIs: PointOfInterest[];
    weatherCondition?: string;
    timeOfDay: 'morning' | 'afternoon' | 'evening' | 'night';
    userMovement: 'stationary' | 'walking' | 'driving' | 'cycling';
  };
}

export interface PointOfInterest {
  id: string;
  name: string;
  category: string;
  coordinates: [number, number];
  description?: string;
  rating?: number;
  distance: number; // from user location
}

export interface NavigationState {
  isNavigating: boolean;
  currentRoute: Route | null;
  currentStepIndex: number;
  remainingDistance: number;
  remainingTime: number;
  nextInstruction: string | null;
}

export interface AppState {
  user: User | null;
  currentLocation: Location | null;
  navigationState: NavigationState;
  isLoading: boolean;
  error: string | null;
  isOnline: boolean;
}

// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  error?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
  limit: number;
  hasNext: boolean;
  hasPrev: boolean;
}

// WebSocket message types
export interface WebSocketMessage {
  type: 'location_update' | 'navigation_instruction' | 'ai_observation' | 'route_update';
  data: any;
  timestamp: Date;
  userId: string;
}