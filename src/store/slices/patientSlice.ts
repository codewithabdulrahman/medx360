import { createSlice, PayloadAction } from '@reduxjs/toolkit';

interface Patient {
  id: number;
  user_id?: number;
  medical_record_number?: string;
  first_name: string;
  last_name: string;
  date_of_birth: string;
  gender?: string;
  phone?: string;
  email?: string;
  address?: string;
  emergency_contact?: any;
  insurance_info?: any;
  medical_history?: any;
  allergies?: any;
  medications?: any;
  notes?: string;
  status: string;
  created_at: string;
  updated_at: string;
}

interface PatientState {
  patients: Patient[];
  selectedPatient: Patient | null;
  isLoading: boolean;
  error: string | null;
  filters: {
    search: string;
    status: string;
    sortBy: string;
    sortOrder: 'asc' | 'desc';
  };
}

const initialState: PatientState = {
  patients: [],
  selectedPatient: null,
  isLoading: false,
  error: null,
  filters: {
    search: '',
    status: '',
    sortBy: 'created_at',
    sortOrder: 'desc',
  },
};

const patientSlice = createSlice({
  name: 'patients',
  initialState,
  reducers: {
    setPatients: (state, action: PayloadAction<Patient[]>) => {
      state.patients = action.payload;
    },
    addPatient: (state, action: PayloadAction<Patient>) => {
      state.patients.unshift(action.payload);
    },
    updatePatient: (state, action: PayloadAction<Patient>) => {
      const index = state.patients.findIndex(p => p.id === action.payload.id);
      if (index !== -1) {
        state.patients[index] = action.payload;
      }
    },
    removePatient: (state, action: PayloadAction<number>) => {
      state.patients = state.patients.filter(p => p.id !== action.payload);
    },
    setSelectedPatient: (state, action: PayloadAction<Patient | null>) => {
      state.selectedPatient = action.payload;
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
    setFilters: (state, action: PayloadAction<Partial<PatientState['filters']>>) => {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearFilters: (state) => {
      state.filters = initialState.filters;
    },
  },
});

export const {
  setPatients,
  addPatient,
  updatePatient,
  removePatient,
  setSelectedPatient,
  setLoading,
  setError,
  clearError,
  setFilters,
  clearFilters,
} = patientSlice.actions;

export default patientSlice.reducer;
