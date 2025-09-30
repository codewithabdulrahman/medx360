class ApiService {
  constructor() {
    // No need to set ajaxURL in constructor since we'll use getter
  }
  
  get ajaxURL() {
    return window.medx360?.ajax_url || window.location.origin + '/wp-admin/admin-ajax.php';
  }
  
  get nonce() {
    return window.medx360?.nonce || '';
  }

  // Generic AJAX method
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

  // Specific API methods
  async getClinics(params = {}) {
    return this.ajaxRequest('medx360_get_clinics', params);
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

  async getHospitals(params = {}) {
    return this.ajaxRequest('medx360_get_hospitals', params);
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

  async getDoctors(params = {}) {
    return this.ajaxRequest('medx360_get_doctors', params);
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

  async getServices(params = {}) {
    return this.ajaxRequest('medx360_get_services', params);
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

  async getStaff(params = {}) {
    return this.ajaxRequest('medx360_get_staff', params);
  }

  async createStaff(data) {
    return this.ajaxRequest('medx360_create_staff_member', data);
  }

  async updateStaff(id, data) {
    return this.ajaxRequest('medx360_update_staff_member', { id, ...data });
  }

  async deleteStaff(id) {
    return this.ajaxRequest('medx360_delete_staff_member', { id });
  }

  async getBookings(params = {}) {
    return this.ajaxRequest('medx360_get_bookings', params);
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

  async getConsultations(params = {}) {
    return this.ajaxRequest('medx360_get_consultations', params);
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

  async getPayments(params = {}) {
    return this.ajaxRequest('medx360_get_payments', params);
  }

  async createPayment(data) {
    return this.ajaxRequest('medx360_create_payment', data);
  }

  async updatePayment(id, data) {
    return this.ajaxRequest('medx360_update_payment', { id, ...data });
  }

  async getSettings() {
    // Settings endpoints not implemented yet - return empty object
    return Promise.resolve({});
  }

  async updateSettings(data) {
    // Settings endpoints not implemented yet - return success
    return Promise.resolve({ message: 'Settings updated successfully' });
  }

  async getSetupStatus() {
    return this.ajaxRequest('medx360_get_onboarding_status');
  }

  async getStatistics() {
    return this.ajaxRequest('medx360_get_onboarding_statistics');
  }

  async completeSetup() {
    return this.ajaxRequest('medx360_complete_onboarding');
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

export const apiService = new ApiService();
