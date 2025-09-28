/**
 * MedX360 API Service
 * Centralized API client for all WordPress REST API endpoints
 */

class MedX360API {
  constructor() {
    this.baseURL = window.medx360?.api_url || '/wp-json/medx360/v1/';
    this.nonce = window.medx360?.nonce || '';
  }

  /**
   * Make authenticated API request
   */
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
        ...options.headers,
      },
      ...options,
    };

    try {
      const response = await fetch(url, config);
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      console.error('API Request failed:', error);
      throw error;
    }
  }

  // ==================== CLINICS API ====================
  
  async getClinics(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`clinics${queryString ? `?${queryString}` : ''}`);
  }

  async getClinic(id) {
    return this.request(`clinics/${id}`);
  }

  async createClinic(data) {
    return this.request('clinics', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateClinic(id, data) {
    return this.request(`clinics/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteClinic(id) {
    return this.request(`clinics/${id}`, {
      method: 'DELETE',
    });
  }

  async getClinicBySlug(slug) {
    return this.request(`clinics/slug/${slug}`);
  }

  // ==================== HOSPITALS API ====================
  
  async getHospitals(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`hospitals${queryString ? `?${queryString}` : ''}`);
  }

  async getHospital(id) {
    return this.request(`hospitals/${id}`);
  }

  async createHospital(data) {
    return this.request('hospitals', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateHospital(id, data) {
    return this.request(`hospitals/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteHospital(id) {
    return this.request(`hospitals/${id}`, {
      method: 'DELETE',
    });
  }

  async getHospitalsByClinic(clinicId) {
    return this.request(`hospitals/clinic/${clinicId}`);
  }

  // ==================== DOCTORS API ====================
  
  async getDoctors(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`doctors${queryString ? `?${queryString}` : ''}`);
  }

  async getDoctor(id) {
    return this.request(`doctors/${id}`);
  }

  async createDoctor(data) {
    return this.request('doctors', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateDoctor(id, data) {
    return this.request(`doctors/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteDoctor(id) {
    return this.request(`doctors/${id}`, {
      method: 'DELETE',
    });
  }

  async getDoctorsByClinic(clinicId) {
    return this.request(`doctors/clinic/${clinicId}`);
  }

  async getDoctorsByHospital(hospitalId) {
    return this.request(`doctors/hospital/${hospitalId}`);
  }

  async getDoctorSchedule(doctorId) {
    return this.request(`doctors/${doctorId}/schedule`);
  }

  async createDoctorSchedule(doctorId, data) {
    return this.request(`doctors/${doctorId}/schedule`, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateDoctorSchedule(doctorId, data) {
    return this.request(`doctors/${doctorId}/schedule`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async getDoctorAvailability(doctorId) {
    return this.request(`doctors/${doctorId}/availability`);
  }

  async createDoctorAvailability(doctorId, data) {
    return this.request(`doctors/${doctorId}/availability`, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // ==================== SERVICES API ====================
  
  async getServices(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`services${queryString ? `?${queryString}` : ''}`);
  }

  async getService(id) {
    return this.request(`services/${id}`);
  }

  async createService(data) {
    return this.request('services', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateService(id, data) {
    return this.request(`services/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteService(id) {
    return this.request(`services/${id}`, {
      method: 'DELETE',
    });
  }

  async getServicesByClinic(clinicId) {
    return this.request(`services/clinic/${clinicId}`);
  }

  async getServicesByHospital(hospitalId) {
    return this.request(`services/hospital/${hospitalId}`);
  }

  // ==================== STAFF API ====================
  
  async getStaff(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`staff${queryString ? `?${queryString}` : ''}`);
  }

  async getStaffMember(id) {
    return this.request(`staff/${id}`);
  }

  async createStaffMember(data) {
    return this.request('staff', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateStaffMember(id, data) {
    return this.request(`staff/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteStaffMember(id) {
    return this.request(`staff/${id}`, {
      method: 'DELETE',
    });
  }

  async getStaffByClinic(clinicId) {
    return this.request(`staff/clinic/${clinicId}`);
  }

  // ==================== BOOKINGS API ====================
  
  async getBookings(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`bookings${queryString ? `?${queryString}` : ''}`);
  }

  async getBooking(id) {
    return this.request(`bookings/${id}`);
  }

  async createBooking(data) {
    return this.request('bookings', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateBooking(id, data) {
    return this.request(`bookings/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteBooking(id) {
    return this.request(`bookings/${id}`, {
      method: 'DELETE',
    });
  }

  async getBookingsByClinic(clinicId) {
    return this.request(`bookings/clinic/${clinicId}`);
  }

  async getBookingsByDoctor(doctorId) {
    return this.request(`bookings/doctor/${doctorId}`);
  }

  async confirmBooking(id) {
    return this.request(`bookings/${id}/confirm`, {
      method: 'PUT',
    });
  }

  async cancelBooking(id) {
    return this.request(`bookings/${id}/cancel`, {
      method: 'PUT',
    });
  }

  // ==================== PAYMENTS API ====================
  
  async getPayments(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`payments${queryString ? `?${queryString}` : ''}`);
  }

  async getPayment(id) {
    return this.request(`payments/${id}`);
  }

  async createPayment(data) {
    return this.request('payments', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updatePayment(id, data) {
    return this.request(`payments/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async getPaymentsByBooking(bookingId) {
    return this.request(`payments/booking/${bookingId}`);
  }

  async refundPayment(id) {
    return this.request(`payments/${id}/refund`, {
      method: 'PUT',
    });
  }

  // ==================== CONSULTATIONS API ====================
  
  async getConsultations(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`consultations${queryString ? `?${queryString}` : ''}`);
  }

  async getConsultation(id) {
    return this.request(`consultations/${id}`);
  }

  async createConsultation(data) {
    return this.request('consultations', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async updateConsultation(id, data) {
    return this.request(`consultations/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteConsultation(id) {
    return this.request(`consultations/${id}`, {
      method: 'DELETE',
    });
  }

  async getConsultationsByBooking(bookingId) {
    return this.request(`consultations/booking/${bookingId}`);
  }

  async getConsultationsByDoctor(doctorId) {
    return this.request(`consultations/doctor/${doctorId}`);
  }

  async completeConsultation(id) {
    return this.request(`consultations/${id}/complete`, {
      method: 'PUT',
    });
  }

  // ==================== ONBOARDING API ====================
  
  async getOnboardingStatus() {
    return this.request('onboarding/status');
  }

  async getOnboardingSteps() {
    return this.request('onboarding/steps');
  }

  async getOnboardingProgress() {
    return this.request('onboarding/progress');
  }

  async getOnboardingStatistics() {
    return this.request('onboarding/statistics');
  }

  async createDefaultClinic(data) {
    return this.request('onboarding/clinic', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async createDefaultServices(data) {
    return this.request('onboarding/services', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async completeOnboarding() {
    return this.request('onboarding/complete', {
      method: 'PUT',
    });
  }

  async resetOnboarding() {
    return this.request('onboarding/reset', {
      method: 'PUT',
    });
  }
}

// Export singleton instance
export default new MedX360API();
