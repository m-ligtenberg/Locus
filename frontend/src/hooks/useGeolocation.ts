import { useState, useEffect, useCallback } from 'react';
import { Location } from '../types';
import { useAppStore } from '../stores/appStore';

interface GeolocationOptions {
  enableHighAccuracy?: boolean;
  timeout?: number;
  maximumAge?: number;
}

interface UseGeolocationReturn {
  location: Location | null;
  error: GeolocationPositionError | null;
  isLoading: boolean;
  startTracking: () => void;
  stopTracking: () => void;
  getCurrentPosition: () => Promise<Location>;
}

export const useGeolocation = (options: GeolocationOptions = {}): UseGeolocationReturn => {
  const [location, setLocation] = useState<Location | null>(null);
  const [error, setError] = useState<GeolocationPositionError | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [watchId, setWatchId] = useState<number | null>(null);
  
  const { setCurrentLocation } = useAppStore();

  const defaultOptions: PositionOptions = {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 30000,
    ...options,
  };

  const handleSuccess = useCallback((position: GeolocationPosition) => {
    const newLocation: Location = {
      id: crypto.randomUUID(),
      latitude: position.coords.latitude,
      longitude: position.coords.longitude,
      accuracy: position.coords.accuracy,
      timestamp: new Date(position.timestamp),
      speed: position.coords.speed || undefined,
      heading: position.coords.heading || undefined,
    };

    setLocation(newLocation);
    setCurrentLocation(newLocation);
    setError(null);
    setIsLoading(false);
  }, [setCurrentLocation]);

  const handleError = useCallback((err: GeolocationPositionError) => {
    setError(err);
    setIsLoading(false);
    
    // Log specific error types
    switch (err.code) {
      case err.PERMISSION_DENIED:
        console.error('Location access denied by user');
        break;
      case err.POSITION_UNAVAILABLE:
        console.error('Location information unavailable');
        break;
      case err.TIMEOUT:
        console.error('Location request timed out');
        break;
    }
  }, []);

  const startTracking = useCallback(() => {
    if (!navigator.geolocation) {
      setError({
        code: 2,
        message: 'Geolocation not supported',
        PERMISSION_DENIED: 1,
        POSITION_UNAVAILABLE: 2,
        TIMEOUT: 3,
      } as GeolocationPositionError);
      return;
    }

    setIsLoading(true);
    setError(null);

    const id = navigator.geolocation.watchPosition(
      handleSuccess,
      handleError,
      defaultOptions
    );

    setWatchId(id);
  }, [handleSuccess, handleError, defaultOptions]);

  const stopTracking = useCallback(() => {
    if (watchId !== null) {
      navigator.geolocation.clearWatch(watchId);
      setWatchId(null);
      setIsLoading(false);
    }
  }, [watchId]);

  const getCurrentPosition = useCallback((): Promise<Location> => {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error('Geolocation not supported'));
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const location: Location = {
            id: crypto.randomUUID(),
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            timestamp: new Date(position.timestamp),
            speed: position.coords.speed || undefined,
            heading: position.coords.heading || undefined,
          };
          resolve(location);
        },
        reject,
        defaultOptions
      );
    });
  }, [defaultOptions]);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
      }
    };
  }, [watchId]);

  return {
    location,
    error,
    isLoading,
    startTracking,
    stopTracking,
    getCurrentPosition,
  };
};