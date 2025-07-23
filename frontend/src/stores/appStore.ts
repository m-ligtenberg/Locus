import { create } from 'zustand';
import { devtools, persist } from 'zustand/middleware';
import { AppState, User, Location, NavigationState } from '../types';

interface AppStore extends AppState {
  // Actions
  setUser: (user: User | null) => void;
  setCurrentLocation: (location: Location | null) => void;
  setNavigationState: (state: Partial<NavigationState>) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  setOnlineStatus: (online: boolean) => void;
  
  // Computed
  isAuthenticated: () => boolean;
  getCurrentPersona: () => string | null;
  
  // Actions
  reset: () => void;
}

const initialState: AppState = {
  user: null,
  currentLocation: null,
  navigationState: {
    isNavigating: false,
    currentRoute: null,
    currentStepIndex: 0,
    remainingDistance: 0,
    remainingTime: 0,
    nextInstruction: null,
  },
  isLoading: false,
  error: null,
  isOnline: navigator.onLine,
};

export const useAppStore = create<AppStore>()(
  devtools(
    persist(
      (set, get) => ({
        ...initialState,
        
        setUser: (user) => set({ user }),
        
        setCurrentLocation: (location) => set({ currentLocation: location }),
        
        setNavigationState: (navState) => 
          set((state) => ({ 
            navigationState: { ...state.navigationState, ...navState } 
          })),
        
        setLoading: (loading) => set({ isLoading: loading }),
        
        setError: (error) => set({ error }),
        
        setOnlineStatus: (online) => set({ isOnline: online }),
        
        isAuthenticated: () => !!get().user,
        
        getCurrentPersona: () => get().user?.preferences.persona || null,
        
        reset: () => set(initialState),
      }),
      {
        name: 'locus-app-state',
        partialize: (state) => ({
          user: state.user,
        }),
      }
    ),
    { name: 'AppStore' }
  )
);

// Network status listener
window.addEventListener('online', () => useAppStore.getState().setOnlineStatus(true));
window.addEventListener('offline', () => useAppStore.getState().setOnlineStatus(false));