// Core entity types for MedX360 API

export interface Clinic {
  id: number;
  name: string;
  slug: string;
  description?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  postal_code?: string;
  phone?: string;
  email?: string;
  website?: string;
  logo_url?: string;
  status: 'active' | 'inactive' | 'pending';
  settings?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface Hospital {
  id: number;
  clinic_id: number;
  name: string;
  slug: string;
  description?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  postal_code?: string;
  phone?: string;
  email?: string;
  website?: string;
  logo_url?: string;
  status: 'active' | 'inactive' | 'pending';
  settings?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface Doctor {
  id: number;
  clinic_id: number;
  hospital_id?: number;
  user_id?: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  specialization?: string;
  license_number?: string;
  experience_years?: number;
  education?: string;
  bio?: string;
  profile_image?: string;
  consultation_fee?: number;
  status: 'active' | 'inactive' | 'pending';
  settings?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface Service {
  id: number;
  clinic_id: number;
  hospital_id?: number;
  name: string;
  description?: string;
  duration_minutes: number;
  price?: number;
  category?: string;
  status: 'active' | 'inactive';
  settings?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface Staff {
  id: number;
  clinic_id: number;
  hospital_id?: number;
  user_id?: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  role: string;
  department?: string;
  status: 'active' | 'inactive' | 'pending';
  settings?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface Booking {
  id: number;
  clinic_id: number;
  hospital_id?: number;
  doctor_id?: number;
  service_id?: number;
  patient_name: string;
  patient_email: string;
  patient_phone?: string;
  patient_dob?: string;
  patient_gender?: 'male' | 'female' | 'other';
  appointment_date: string;
  appointment_time: string;
  duration_minutes: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show';
  notes?: string;
  total_amount?: number;
  payment_status: 'pending' | 'paid' | 'refunded' | 'failed';
  payment_method?: string;
  payment_reference?: string;
  created_at: string;
  updated_at: string;
}

export interface Consultation {
  id: number;
  booking_id: number;
  doctor_id: number;
  patient_id?: number;
  consultation_type: 'in_person' | 'video' | 'phone';
  diagnosis?: string;
  prescription?: string;
  notes?: string;
  follow_up_date?: string;
  status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
  created_at: string;
  updated_at: string;
}

export interface Payment {
  id: number;
  booking_id: number;
  amount: number;
  currency: string;
  payment_method: string;
  payment_gateway?: string;
  transaction_id?: string;
  status: 'pending' | 'completed' | 'failed' | 'refunded' | 'cancelled';
  gateway_response?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface DoctorSchedule {
  id: number;
  doctor_id: number;
  day_of_week: number; // 1=Monday, 7=Sunday
  start_time: string;
  end_time: string;
  is_available: boolean;
  created_at: string;
  updated_at: string;
}

export interface DoctorAvailability {
  id: number;
  doctor_id: number;
  date: string;
  start_time?: string;
  end_time?: string;
  is_available: boolean;
  reason?: string;
  created_at: string;
  updated_at: string;
}

// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: T[];
  pagination: {
    page: number;
    per_page: number;
    total: number;
    total_pages: number;
  };
}

// Onboarding types
export interface OnboardingStatus {
  is_completed: boolean;
  progress: number;
  next_step: string;
  steps: OnboardingStep[];
}

export interface OnboardingStep {
  id: string;
  title: string;
  description: string;
  completed: boolean;
  required: boolean;
}

export interface OnboardingStatistics {
  clinics: number;
  hospitals: number;
  doctors: number;
  services: number;
  staff: number;
  bookings: number;
}

// Settings types
export interface PluginSettings {
  booking_advance_days: number;
  booking_cancellation_hours: number;
  email_notifications: boolean;
  sms_notifications: boolean;
  reminder_notifications: boolean;
  timezone: string;
  date_format: string;
  time_format: string;
  currency: string;
  currency_symbol: string;
  payment_gateway: 'manual' | 'stripe' | 'paypal';
}

// Form types
export interface CreateClinicData {
  name: string;
  slug?: string;
  description?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  postal_code?: string;
  phone?: string;
  email?: string;
  website?: string;
  logo_url?: string;
  status?: 'active' | 'inactive' | 'pending';
}

export interface CreateDoctorData {
  clinic_id: number;
  hospital_id?: number;
  user_id?: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  specialization?: string;
  license_number?: string;
  experience_years?: number;
  education?: string;
  bio?: string;
  profile_image?: string;
  consultation_fee?: number;
  status?: 'active' | 'inactive' | 'pending';
}

export interface CreateBookingData {
  clinic_id: number;
  hospital_id?: number;
  doctor_id?: number;
  service_id?: number;
  patient_name: string;
  patient_email: string;
  patient_phone?: string;
  patient_dob?: string;
  patient_gender?: 'male' | 'female' | 'other';
  appointment_date: string;
  appointment_time: string;
  duration_minutes?: number;
  notes?: string;
  total_amount?: number;
}

// Error types
export interface ApiError {
  code: string;
  message: string;
  data?: Record<string, any>;
}

// Loading states
export interface LoadingState {
  isLoading: boolean;
  error?: string;
}
