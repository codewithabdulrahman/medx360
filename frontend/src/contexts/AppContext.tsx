import React, { createContext, useContext, useReducer, ReactNode } from 'react';
import { Clinic, Doctor, Booking, OnboardingStatus } from '../types';

// State interfaces
interface AppState {
  user: {
    isAuthenticated: boolean;
    user: any;
    permissions: string[];
  };
  currentClinic: Clinic | null;
  currentDoctor: Doctor | null;
  selectedBooking: Booking | null;
  onboarding: {
    status: OnboardingStatus | null;
    isVisible: boolean;
  };
  ui: {
    sidebarOpen: boolean;
    theme: 'light' | 'dark';
    loading: boolean;
  };
}

// Action types
type AppAction =
  | { type: 'SET_USER'; payload: { user: any; permissions: string[] } }
  | { type: 'LOGOUT' }
  | { type: 'SET_CURRENT_CLINIC'; payload: Clinic | null }
  | { type: 'SET_CURRENT_DOCTOR'; payload: Doctor | null }
  | { type: 'SET_SELECTED_BOOKING'; payload: Booking | null }
  | { type: 'SET_ONBOARDING_STATUS'; payload: OnboardingStatus | null }
  | { type: 'SHOW_ONBOARDING' }
  | { type: 'HIDE_ONBOARDING' }
  | { type: 'TOGGLE_SIDEBAR' }
  | { type: 'SET_THEME'; payload: 'light' | 'dark' }
  | { type: 'SET_LOADING'; payload: boolean };

// Initial state
const initialState: AppState = {
  user: {
    isAuthenticated: false,
    user: null,
    permissions: [],
  },
  currentClinic: null,
  currentDoctor: null,
  selectedBooking: null,
  onboarding: {
    status: null,
    isVisible: false,
  },
  ui: {
    sidebarOpen: true,
    theme: 'light',
    loading: false,
  },
};

// Reducer
function appReducer(state: AppState, action: AppAction): AppState {
  switch (action.type) {
    case 'SET_USER':
      return {
        ...state,
        user: {
          isAuthenticated: true,
          user: action.payload.user,
          permissions: action.payload.permissions,
        },
      };

    case 'LOGOUT':
      return {
        ...state,
        user: {
          isAuthenticated: false,
          user: null,
          permissions: [],
        },
        currentClinic: null,
        currentDoctor: null,
        selectedBooking: null,
      };

    case 'SET_CURRENT_CLINIC':
      return {
        ...state,
        currentClinic: action.payload,
      };

    case 'SET_CURRENT_DOCTOR':
      return {
        ...state,
        currentDoctor: action.payload,
      };

    case 'SET_SELECTED_BOOKING':
      return {
        ...state,
        selectedBooking: action.payload,
      };

    case 'SET_ONBOARDING_STATUS':
      return {
        ...state,
        onboarding: {
          ...state.onboarding,
          status: action.payload,
        },
      };

    case 'SHOW_ONBOARDING':
      return {
        ...state,
        onboarding: {
          ...state.onboarding,
          isVisible: true,
        },
      };

    case 'HIDE_ONBOARDING':
      return {
        ...state,
        onboarding: {
          ...state.onboarding,
          isVisible: false,
        },
      };

    case 'TOGGLE_SIDEBAR':
      return {
        ...state,
        ui: {
          ...state.ui,
          sidebarOpen: !state.ui.sidebarOpen,
        },
      };

    case 'SET_THEME':
      return {
        ...state,
        ui: {
          ...state.ui,
          theme: action.payload,
        },
      };

    case 'SET_LOADING':
      return {
        ...state,
        ui: {
          ...state.ui,
          loading: action.payload,
        },
      };

    default:
      return state;
  }
}

// Context
const AppContext = createContext<{
  state: AppState;
  dispatch: React.Dispatch<AppAction>;
} | null>(null);

// Provider component
interface AppProviderProps {
  children: ReactNode;
}

export function AppProvider({ children }: AppProviderProps) {
  const [state, dispatch] = useReducer(appReducer, initialState);

  return (
    <AppContext.Provider value={{ state, dispatch }}>
      {children}
    </AppContext.Provider>
  );
}

// Custom hook to use the context
export function useApp() {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
}

// Action creators
export const appActions = {
  setUser: (user: any, permissions: string[]) => ({
    type: 'SET_USER' as const,
    payload: { user, permissions },
  }),

  logout: () => ({
    type: 'LOGOUT' as const,
  }),

  setCurrentClinic: (clinic: Clinic | null) => ({
    type: 'SET_CURRENT_CLINIC' as const,
    payload: clinic,
  }),

  setCurrentDoctor: (doctor: Doctor | null) => ({
    type: 'SET_CURRENT_DOCTOR' as const,
    payload: doctor,
  }),

  setSelectedBooking: (booking: Booking | null) => ({
    type: 'SET_SELECTED_BOOKING' as const,
    payload: booking,
  }),

  setOnboardingStatus: (status: OnboardingStatus | null) => ({
    type: 'SET_ONBOARDING_STATUS' as const,
    payload: status,
  }),

  showOnboarding: () => ({
    type: 'SHOW_ONBOARDING' as const,
  }),

  hideOnboarding: () => ({
    type: 'HIDE_ONBOARDING' as const,
  }),

  toggleSidebar: () => ({
    type: 'TOGGLE_SIDEBAR' as const,
  }),

  setTheme: (theme: 'light' | 'dark') => ({
    type: 'SET_THEME' as const,
    payload: theme,
  }),

  setLoading: (loading: boolean) => ({
    type: 'SET_LOADING' as const,
    payload: loading,
  }),
};
