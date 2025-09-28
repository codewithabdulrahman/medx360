import axios, { AxiosInstance, AxiosResponse } from 'axios';
import {
  Clinic,
  Hospital,
  Doctor,
  Service,
  Staff,
  Booking,
  Consultation,
  Payment,
  DoctorSchedule,
  DoctorAvailability,
  OnboardingStatus,
  OnboardingStatistics,
  PluginSettings,
  CreateClinicData,
  CreateDoctorData,
  CreateBookingData,
  ApiResponse,
  PaginatedResponse,
  ApiError
} from '../types';

class ApiService {
  private api: AxiosInstance;
  private baseURL: string;

  constructor() {
    // Get WordPress site URL from environment or use default
    this.baseURL = process.env.REACT_APP_WP_URL || window.location.origin;
    
    this.api = axios.create({
      baseURL: `${this.baseURL}/wp-json/medx360/v1`,
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
      },
    });

    // Add request interceptor to include nonce
    this.api.interceptors.request.use(
      (config) => {
        const nonce = this.getNonce();
        if (nonce) {
          config.headers['X-WP-Nonce'] = nonce;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Add response interceptor for error handling
    this.api.interceptors.response.use(
      (response: AxiosResponse) => response,
      (error) => {
        const apiError: ApiError = {
          code: error.response?.data?.code || 'UNKNOWN_ERROR',
          message: error.response?.data?.message || error.message || 'An error occurred',
          data: error.response?.data?.data || null,
        };
        return Promise.reject(apiError);
      }
    );
  }

  private getNonce(): string | null {
    // Try to get nonce from WordPress
    if (typeof window !== 'undefined' && (window as any).wpApiSettings) {
      return (window as any).wpApiSettings.nonce;
    }
    
    // Try to get from meta tag
    const nonceMeta = document.querySelector('meta[name="wp-nonce"]');
    if (nonceMeta) {
      return nonceMeta.getAttribute('content');
    }
    
    return null;
  }

  // Clinics API
  async getClinics(): Promise<PaginatedResponse<Clinic>> {
    const response = await this.api.get('/clinics');
    return response.data;
  }

  async getClinic(id: number): Promise<ApiResponse<Clinic>> {
    const response = await this.api.get(`/clinics/${id}`);
    return response.data;
  }

  async getClinicBySlug(slug: string): Promise<ApiResponse<Clinic>> {
    const response = await this.api.get(`/clinics/slug/${slug}`);
    return response.data;
  }

  async createClinic(data: CreateClinicData): Promise<ApiResponse<Clinic>> {
    const response = await this.api.post('/clinics', data);
    return response.data;
  }

  async updateClinic(id: number, data: Partial<CreateClinicData>): Promise<ApiResponse<Clinic>> {
    const response = await this.api.put(`/clinics/${id}`, data);
    return response.data;
  }

  async deleteClinic(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/clinics/${id}`);
    return response.data;
  }

  // Hospitals API
  async getHospitals(): Promise<PaginatedResponse<Hospital>> {
    const response = await this.api.get('/hospitals');
    return response.data;
  }

  async getHospital(id: number): Promise<ApiResponse<Hospital>> {
    const response = await this.api.get(`/hospitals/${id}`);
    return response.data;
  }

  async getHospitalsByClinic(clinicId: number): Promise<PaginatedResponse<Hospital>> {
    const response = await this.api.get(`/hospitals/clinic/${clinicId}`);
    return response.data;
  }

  async createHospital(data: Partial<Hospital>): Promise<ApiResponse<Hospital>> {
    const response = await this.api.post('/hospitals', data);
    return response.data;
  }

  async updateHospital(id: number, data: Partial<Hospital>): Promise<ApiResponse<Hospital>> {
    const response = await this.api.put(`/hospitals/${id}`, data);
    return response.data;
  }

  async deleteHospital(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/hospitals/${id}`);
    return response.data;
  }

  // Doctors API
  async getDoctors(): Promise<PaginatedResponse<Doctor>> {
    const response = await this.api.get('/doctors');
    return response.data;
  }

  async getDoctor(id: number): Promise<ApiResponse<Doctor>> {
    const response = await this.api.get(`/doctors/${id}`);
    return response.data;
  }

  async getDoctorsByClinic(clinicId: number): Promise<PaginatedResponse<Doctor>> {
    const response = await this.api.get(`/doctors/clinic/${clinicId}`);
    return response.data;
  }

  async getDoctorsByHospital(hospitalId: number): Promise<PaginatedResponse<Doctor>> {
    const response = await this.api.get(`/doctors/hospital/${hospitalId}`);
    return response.data;
  }

  async createDoctor(data: CreateDoctorData): Promise<ApiResponse<Doctor>> {
    const response = await this.api.post('/doctors', data);
    return response.data;
  }

  async updateDoctor(id: number, data: Partial<CreateDoctorData>): Promise<ApiResponse<Doctor>> {
    const response = await this.api.put(`/doctors/${id}`, data);
    return response.data;
  }

  async deleteDoctor(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/doctors/${id}`);
    return response.data;
  }

  // Doctor Schedules API
  async getDoctorSchedule(doctorId: number): Promise<ApiResponse<DoctorSchedule[]>> {
    const response = await this.api.get(`/doctors/${doctorId}/schedule`);
    return response.data;
  }

  async createDoctorSchedule(doctorId: number, data: Partial<DoctorSchedule>): Promise<ApiResponse<DoctorSchedule>> {
    const response = await this.api.post(`/doctors/${doctorId}/schedule`, data);
    return response.data;
  }

  async updateDoctorSchedule(doctorId: number, data: Partial<DoctorSchedule>): Promise<ApiResponse<DoctorSchedule>> {
    const response = await this.api.put(`/doctors/${doctorId}/schedule`, data);
    return response.data;
  }

  // Doctor Availability API
  async getDoctorAvailability(doctorId: number): Promise<ApiResponse<DoctorAvailability[]>> {
    const response = await this.api.get(`/doctors/${doctorId}/availability`);
    return response.data;
  }

  async createDoctorAvailability(doctorId: number, data: Partial<DoctorAvailability>): Promise<ApiResponse<DoctorAvailability>> {
    const response = await this.api.post(`/doctors/${doctorId}/availability`, data);
    return response.data;
  }

  // Services API
  async getServices(): Promise<PaginatedResponse<Service>> {
    const response = await this.api.get('/services');
    return response.data;
  }

  async getService(id: number): Promise<ApiResponse<Service>> {
    const response = await this.api.get(`/services/${id}`);
    return response.data;
  }

  async getServicesByClinic(clinicId: number): Promise<PaginatedResponse<Service>> {
    const response = await this.api.get(`/services/clinic/${clinicId}`);
    return response.data;
  }

  async getServicesByHospital(hospitalId: number): Promise<PaginatedResponse<Service>> {
    const response = await this.api.get(`/services/hospital/${hospitalId}`);
    return response.data;
  }

  async createService(data: Partial<Service>): Promise<ApiResponse<Service>> {
    const response = await this.api.post('/services', data);
    return response.data;
  }

  async updateService(id: number, data: Partial<Service>): Promise<ApiResponse<Service>> {
    const response = await this.api.put(`/services/${id}`, data);
    return response.data;
  }

  async deleteService(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/services/${id}`);
    return response.data;
  }

  // Staff API
  async getStaff(): Promise<PaginatedResponse<Staff>> {
    const response = await this.api.get('/staff');
    return response.data;
  }

  async getStaffMember(id: number): Promise<ApiResponse<Staff>> {
    const response = await this.api.get(`/staff/${id}`);
    return response.data;
  }

  async getStaffByClinic(clinicId: number): Promise<PaginatedResponse<Staff>> {
    const response = await this.api.get(`/staff/clinic/${clinicId}`);
    return response.data;
  }

  async createStaff(data: Partial<Staff>): Promise<ApiResponse<Staff>> {
    const response = await this.api.post('/staff', data);
    return response.data;
  }

  async updateStaff(id: number, data: Partial<Staff>): Promise<ApiResponse<Staff>> {
    const response = await this.api.put(`/staff/${id}`, data);
    return response.data;
  }

  async deleteStaff(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/staff/${id}`);
    return response.data;
  }

  // Bookings API
  async getBookings(): Promise<PaginatedResponse<Booking>> {
    const response = await this.api.get('/bookings');
    return response.data;
  }

  async getBooking(id: number): Promise<ApiResponse<Booking>> {
    const response = await this.api.get(`/bookings/${id}`);
    return response.data;
  }

  async getBookingsByClinic(clinicId: number): Promise<PaginatedResponse<Booking>> {
    const response = await this.api.get(`/bookings/clinic/${clinicId}`);
    return response.data;
  }

  async getBookingsByDoctor(doctorId: number): Promise<PaginatedResponse<Booking>> {
    const response = await this.api.get(`/bookings/doctor/${doctorId}`);
    return response.data;
  }

  async createBooking(data: CreateBookingData): Promise<ApiResponse<Booking>> {
    const response = await this.api.post('/bookings', data);
    return response.data;
  }

  async updateBooking(id: number, data: Partial<CreateBookingData>): Promise<ApiResponse<Booking>> {
    const response = await this.api.put(`/bookings/${id}`, data);
    return response.data;
  }

  async deleteBooking(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/bookings/${id}`);
    return response.data;
  }

  async confirmBooking(id: number): Promise<ApiResponse<Booking>> {
    const response = await this.api.put(`/bookings/${id}/confirm`);
    return response.data;
  }

  async cancelBooking(id: number): Promise<ApiResponse<Booking>> {
    const response = await this.api.put(`/bookings/${id}/cancel`);
    return response.data;
  }

  // Consultations API
  async getConsultations(): Promise<PaginatedResponse<Consultation>> {
    const response = await this.api.get('/consultations');
    return response.data;
  }

  async getConsultation(id: number): Promise<ApiResponse<Consultation>> {
    const response = await this.api.get(`/consultations/${id}`);
    return response.data;
  }

  async getConsultationsByBooking(bookingId: number): Promise<PaginatedResponse<Consultation>> {
    const response = await this.api.get(`/consultations/booking/${bookingId}`);
    return response.data;
  }

  async getConsultationsByDoctor(doctorId: number): Promise<PaginatedResponse<Consultation>> {
    const response = await this.api.get(`/consultations/doctor/${doctorId}`);
    return response.data;
  }

  async createConsultation(data: Partial<Consultation>): Promise<ApiResponse<Consultation>> {
    const response = await this.api.post('/consultations', data);
    return response.data;
  }

  async updateConsultation(id: number, data: Partial<Consultation>): Promise<ApiResponse<Consultation>> {
    const response = await this.api.put(`/consultations/${id}`, data);
    return response.data;
  }

  async deleteConsultation(id: number): Promise<ApiResponse<void>> {
    const response = await this.api.delete(`/consultations/${id}`);
    return response.data;
  }

  async completeConsultation(id: number): Promise<ApiResponse<Consultation>> {
    const response = await this.api.put(`/consultations/${id}/complete`);
    return response.data;
  }

  // Payments API
  async getPayments(): Promise<PaginatedResponse<Payment>> {
    const response = await this.api.get('/payments');
    return response.data;
  }

  async getPayment(id: number): Promise<ApiResponse<Payment>> {
    const response = await this.api.get(`/payments/${id}`);
    return response.data;
  }

  async getPaymentsByBooking(bookingId: number): Promise<PaginatedResponse<Payment>> {
    const response = await this.api.get(`/payments/booking/${bookingId}`);
    return response.data;
  }

  async createPayment(data: Partial<Payment>): Promise<ApiResponse<Payment>> {
    const response = await this.api.post('/payments', data);
    return response.data;
  }

  async updatePayment(id: number, data: Partial<Payment>): Promise<ApiResponse<Payment>> {
    const response = await this.api.put(`/payments/${id}`, data);
    return response.data;
  }

  async refundPayment(id: number): Promise<ApiResponse<Payment>> {
    const response = await this.api.put(`/payments/${id}/refund`);
    return response.data;
  }

  // Onboarding API
  async getOnboardingStatus(): Promise<ApiResponse<OnboardingStatus>> {
    const response = await this.api.get('/onboarding/status');
    return response.data;
  }

  async getOnboardingSteps(): Promise<ApiResponse<any>> {
    const response = await this.api.get('/onboarding/steps');
    return response.data;
  }

  async getOnboardingProgress(): Promise<ApiResponse<any>> {
    const response = await this.api.get('/onboarding/progress');
    return response.data;
  }

  async getOnboardingStatistics(): Promise<ApiResponse<OnboardingStatistics>> {
    const response = await this.api.get('/onboarding/statistics');
    return response.data;
  }

  async createDefaultClinic(data: Partial<CreateClinicData>): Promise<ApiResponse<Clinic>> {
    const response = await this.api.post('/onboarding/clinic', data);
    return response.data;
  }

  async createDefaultServices(clinicId: number): Promise<ApiResponse<Service[]>> {
    const response = await this.api.post('/onboarding/services', { clinic_id: clinicId });
    return response.data;
  }

  async completeOnboarding(): Promise<ApiResponse<void>> {
    const response = await this.api.put('/onboarding/complete');
    return response.data;
  }

  async resetOnboarding(): Promise<ApiResponse<void>> {
    const response = await this.api.put('/onboarding/reset');
    return response.data;
  }

  // Settings API
  async getSettings(): Promise<ApiResponse<PluginSettings>> {
    const response = await this.api.get('/settings');
    return response.data;
  }

  async saveSettings(settings: Partial<PluginSettings>): Promise<ApiResponse<PluginSettings>> {
    const response = await this.api.post('/settings', settings);
    return response.data;
  }
}

// Export singleton instance
export const apiService = new ApiService();
export default apiService;
