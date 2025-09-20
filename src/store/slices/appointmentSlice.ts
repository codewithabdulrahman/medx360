import { createSlice, PayloadAction } from '@reduxjs/toolkit';

interface Appointment {
  id: number;
  patient_id: number;
  provider_id: number;
  service_id?: number;
  location_id?: number;
  room_id?: number;
  appointment_type: string;
  status: string;
  scheduled_at: string;
  duration_minutes: number;
  notes?: string;
  clinical_notes?: string;
  prescription_data?: any;
  lab_orders?: any;
  referrals?: any;
  created_at: string;
  updated_at: string;
}

interface AppointmentState {
  appointments: Appointment[];
  selectedAppointment: Appointment | null;
  isLoading: boolean;
  error: string | null;
  filters: {
    search: string;
    status: string;
    provider_id: number | null;
    patient_id: number | null;
    date_from: string;
    date_to: string;
    sortBy: string;
    sortOrder: 'asc' | 'desc';
  };
}

const initialState: AppointmentState = {
  appointments: [],
  selectedAppointment: null,
  isLoading: false,
  error: null,
  filters: {
    search: '',
    status: '',
    provider_id: null,
    patient_id: null,
    date_from: '',
    date_to: '',
    sortBy: 'scheduled_at',
    sortOrder: 'asc',
  },
};

const appointmentSlice = createSlice({
  name: 'appointments',
  initialState,
  reducers: {
    setAppointments: (state, action: PayloadAction<Appointment[]>) => {
      state.appointments = action.payload;
    },
    addAppointment: (state, action: PayloadAction<Appointment>) => {
      state.appointments.unshift(action.payload);
    },
    updateAppointment: (state, action: PayloadAction<Appointment>) => {
      const index = state.appointments.findIndex(a => a.id === action.payload.id);
      if (index !== -1) {
        state.appointments[index] = action.payload;
      }
    },
    removeAppointment: (state, action: PayloadAction<number>) => {
      state.appointments = state.appointments.filter(a => a.id !== action.payload);
    },
    setSelectedAppointment: (state, action: PayloadAction<Appointment | null>) => {
      state.selectedAppointment = action.payload;
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.isLoading = action.payload;
    },
    setError: (state, action: PayloadAction<string>) => {
      state.error = action.payload;
    },
    clearError: (state) => {
      state.error = null;
    },
    setFilters: (state, action: PayloadAction<Partial<AppointmentState['filters']>>) => {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearFilters: (state) => {
      state.filters = initialState.filters;
    },
  },
});

export const {
  setAppointments,
  addAppointment,
  updateAppointment,
  removeAppointment,
  setSelectedAppointment,
  setLoading,
  setError,
  clearError,
  setFilters,
  clearFilters,
} = appointmentSlice.actions;

export default appointmentSlice.reducer;
