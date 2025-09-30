/**
 * MedX360 API Service
 * Centralized API client for all WordPress AJAX endpoints
 */

class MedX360API {
  constructor() {
    // No need to set ajaxURL in constructor since we'll use getter
  }
  
  get ajaxURL() {
    return window.medx360?.ajax_url || window.location.origin + '/wp-admin/admin-ajax.php';
  }
  
  get nonce() {
    return window.medx360?.nonce || '';
  }

  /**
   * Make authenticated AJAX request
   */
  async ajaxRequest(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('nonce', this.nonce);
    
    // Add all data fields
    Object.keys(data).forEach(key => {
      if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key]);
      }
    });

    try {
      const response = await fetch(this.ajaxURL, {
        method: 'POST',
        body: formData,
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const result = await response.json();
      
      if (!result.success) {
        // Handle validation errors with detailed messages
        if (result.data?.code === 'validation_error' && result.data?.message) {
          const error = new Error(result.data.message);
          error.code = result.data.code;
          error.status = result.data.status;
          throw error;
        }
        
        // Handle other types of errors
        throw new Error(result.data?.message || 'Request failed');
      }

      return result.data;
    } catch (error) {
      console.error('AJAX Request failed:', error);
      throw error;
    }
  }

  // ==================== CLINICS API ====================
  
  async getClinics(params = {}) {
    return this.ajaxRequest('medx360_get_clinics', params);
  }

  async getClinic(id) {
    return this.ajaxRequest('medx360_get_clinic', { id });
  }

  async createClinic(data) {
    return this.ajaxRequest('medx360_create_clinic', data);
  }

  async updateClinic(id, data) {
    return this.ajaxRequest('medx360_update_clinic', { id, ...data });
  }

  async deleteClinic(id) {
    return this.ajaxRequest('medx360_delete_clinic', { id });
  }

  async getClinicBySlug(slug) {
    return this.ajaxRequest('medx360_get_clinic_by_slug', { slug });
  }

  // ==================== HOSPITALS API ====================
  
  async getHospitals(params = {}) {
    return this.ajaxRequest('medx360_get_hospitals', params);
  }

  async getHospital(id) {
    return this.ajaxRequest('medx360_get_hospital', { id });
  }

  async createHospital(data) {
    return this.ajaxRequest('medx360_create_hospital', data);
  }

  async updateHospital(id, data) {
    return this.ajaxRequest('medx360_update_hospital', { id, ...data });
  }

  async deleteHospital(id) {
    return this.ajaxRequest('medx360_delete_hospital', { id });
  }

  async getHospitalsByClinic(clinicId) {
    return this.ajaxRequest('medx360_get_hospitals_by_clinic', { clinic_id: clinicId });
  }

  // ==================== DOCTORS API ====================
  
  async getDoctors(params = {}) {
    return this.ajaxRequest('medx360_get_doctors', params);
  }

  async getDoctor(id) {
    return this.ajaxRequest('medx360_get_doctor', { id });
  }

  async createDoctor(data) {
    return this.ajaxRequest('medx360_create_doctor', data);
  }

  async updateDoctor(id, data) {
    return this.ajaxRequest('medx360_update_doctor', { id, ...data });
  }

  async deleteDoctor(id) {
    return this.ajaxRequest('medx360_delete_doctor', { id });
  }

  async getDoctorsByClinic(clinicId) {
    return this.ajaxRequest('medx360_get_doctors_by_clinic', { clinic_id: clinicId });
  }

  async getDoctorsByHospital(hospitalId) {
    return this.ajaxRequest('medx360_get_doctors_by_hospital', { hospital_id: hospitalId });
  }

  async getDoctorSchedule(doctorId) {
    return this.ajaxRequest('medx360_get_doctor_schedule', { id: doctorId });
  }

  async createDoctorSchedule(doctorId, data) {
    return this.ajaxRequest('medx360_create_doctor_schedule', { id: doctorId, ...data });
  }

  async updateDoctorSchedule(doctorId, data) {
    return this.ajaxRequest('medx360_update_doctor_schedule', { id: doctorId, ...data });
  }

  async getDoctorAvailability(doctorId) {
    return this.ajaxRequest('medx360_get_doctor_availability', { id: doctorId });
  }

  async createDoctorAvailability(doctorId, data) {
    return this.ajaxRequest('medx360_create_doctor_availability', { id: doctorId, ...data });
  }

  // ==================== SERVICES API ====================
  
  async getServices(params = {}) {
    return this.ajaxRequest('medx360_get_services', params);
  }

  async getService(id) {
    return this.ajaxRequest('medx360_get_service', { id });
  }

  async createService(data) {
    return this.ajaxRequest('medx360_create_service', data);
  }

  async updateService(id, data) {
    return this.ajaxRequest('medx360_update_service', { id, ...data });
  }

  async deleteService(id) {
    return this.ajaxRequest('medx360_delete_service', { id });
  }

  async getServicesByClinic(clinicId) {
    return this.ajaxRequest('medx360_get_services_by_clinic', { clinic_id: clinicId });
  }

  async getServicesByHospital(hospitalId) {
    return this.ajaxRequest('medx360_get_services_by_hospital', { hospital_id: hospitalId });
  }

  // ==================== STAFF API ====================
  
  async getStaff(params = {}) {
    return this.ajaxRequest('medx360_get_staff', params);
  }

  async getStaffMember(id) {
    return this.ajaxRequest('medx360_get_staff_member', { id });
  }

  async createStaffMember(data) {
    return this.ajaxRequest('medx360_create_staff_member', data);
  }

  async updateStaffMember(id, data) {
    return this.ajaxRequest('medx360_update_staff_member', { id, ...data });
  }

  async deleteStaffMember(id) {
    return this.ajaxRequest('medx360_delete_staff_member', { id });
  }

  async getStaffByClinic(clinicId) {
    return this.ajaxRequest('medx360_get_staff_by_clinic', { clinic_id: clinicId });
  }

  // ==================== BOOKINGS API ====================
  
  async getBookings(params = {}) {
    return this.ajaxRequest('medx360_get_bookings', params);
  }

  async getBooking(id) {
    return this.ajaxRequest('medx360_get_booking', { id });
  }

  async createBooking(data) {
    return this.ajaxRequest('medx360_create_booking', data);
  }

  async updateBooking(id, data) {
    return this.ajaxRequest('medx360_update_booking', { id, ...data });
  }

  async deleteBooking(id) {
    return this.ajaxRequest('medx360_delete_booking', { id });
  }

  async getBookingsByClinic(clinicId) {
    return this.ajaxRequest('medx360_get_bookings_by_clinic', { clinic_id: clinicId });
  }

  async getBookingsByDoctor(doctorId) {
    return this.ajaxRequest('medx360_get_bookings_by_doctor', { doctor_id: doctorId });
  }

  async confirmBooking(id) {
    return this.ajaxRequest('medx360_confirm_booking', { id });
  }

  async cancelBooking(id) {
    return this.ajaxRequest('medx360_cancel_booking', { id });
  }

  // ==================== PAYMENTS API ====================
  
  async getPayments(params = {}) {
    return this.ajaxRequest('medx360_get_payments', params);
  }

  async getPayment(id) {
    return this.ajaxRequest('medx360_get_payment', { id });
  }

  async createPayment(data) {
    return this.ajaxRequest('medx360_create_payment', data);
  }

  async updatePayment(id, data) {
    return this.ajaxRequest('medx360_update_payment', { id, ...data });
  }

  async getPaymentsByBooking(bookingId) {
    return this.ajaxRequest('medx360_get_payments_by_booking', { booking_id: bookingId });
  }

  async refundPayment(id) {
    return this.ajaxRequest('medx360_refund_payment', { id });
  }

  // ==================== CONSULTATIONS API ====================
  
  async getConsultations(params = {}) {
    return this.ajaxRequest('medx360_get_consultations', params);
  }

  async getConsultation(id) {
    return this.ajaxRequest('medx360_get_consultation', { id });
  }

  async createConsultation(data) {
    return this.ajaxRequest('medx360_create_consultation', data);
  }

  async updateConsultation(id, data) {
    return this.ajaxRequest('medx360_update_consultation', { id, ...data });
  }

  async deleteConsultation(id) {
    return this.ajaxRequest('medx360_delete_consultation', { id });
  }

  async getConsultationsByBooking(bookingId) {
    return this.ajaxRequest('medx360_get_consultations_by_booking', { booking_id: bookingId });
  }

  async getConsultationsByDoctor(doctorId) {
    return this.ajaxRequest('medx360_get_consultations_by_doctor', { doctor_id: doctorId });
  }

  async completeConsultation(id) {
    return this.ajaxRequest('medx360_complete_consultation', { id });
  }

  // ==================== ONBOARDING API ====================
  
  async getOnboardingStatus() {
    return this.ajaxRequest('medx360_get_onboarding_status');
  }

  async getOnboardingSteps() {
    return this.ajaxRequest('medx360_get_onboarding_steps');
  }

  async getOnboardingProgress() {
    return this.ajaxRequest('medx360_get_onboarding_progress');
  }

  async getOnboardingStatistics() {
    return this.ajaxRequest('medx360_get_onboarding_statistics');
  }

  async createDefaultClinic(data) {
    return this.ajaxRequest('medx360_create_onboarding_clinic', data);
  }

  async createDefaultServices(data) {
    return this.ajaxRequest('medx360_create_onboarding_services', data);
  }

  async completeOnboarding() {
    return this.ajaxRequest('medx360_complete_onboarding');
  }

  async resetOnboarding() {
    return this.ajaxRequest('medx360_reset_onboarding');
  }

  // ==================== TEST API ====================
  
  async testConnection() {
    return this.ajaxRequest('medx360_test');
  }

  async testSimple() {
    return this.ajaxRequest('medx360_test_simple');
  }

  async testDoctors() {
    return this.ajaxRequest('medx360_get_doctors_test');
  }
}

// Export singleton instance
export default new MedX360API();
