import axios from 'axios';

class ApiService {
  constructor() {
    this.baseURL = window.medx360?.api_url || '/wp-json/medx360/v1/';
    this.nonce = window.medx360?.nonce || '';
    
    this.client = axios.create({
      baseURL: this.baseURL,
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      },
    });

    // Request interceptor
    this.client.interceptors.request.use(
      (config) => {
        // Update nonce if available
        if (window.medx360?.nonce) {
          config.headers['X-WP-Nonce'] = window.medx360.nonce;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor
    this.client.interceptors.response.use(
      (response) => {
        return response.data;
      },
      (error) => {
        if (error.response?.status === 401) {
          // Handle unauthorized access
          console.error('Unauthorized access');
        }
        return Promise.reject(error);
      }
    );
  }

  // Generic methods
  async get(endpoint, params = {}) {
    return this.client.get(endpoint, { params });
  }

  async post(endpoint, data = {}) {
    return this.client.post(endpoint, data);
  }

  async put(endpoint, data = {}) {
    return this.client.put(endpoint, data);
  }

  async delete(endpoint) {
    return this.client.delete(endpoint);
  }

  // Specific API methods
  async getClinics(params = {}) {
    return this.get('/clinics', params);
  }

  async createClinic(data) {
    return this.post('/clinics', data);
  }

  async updateClinic(id, data) {
    return this.put(`/clinics/${id}`, data);
  }

  async deleteClinic(id) {
    return this.delete(`/clinics/${id}`);
  }

  async getHospitals(params = {}) {
    return this.get('/hospitals', params);
  }

  async createHospital(data) {
    return this.post('/hospitals', data);
  }

  async updateHospital(id, data) {
    return this.put(`/hospitals/${id}`, data);
  }

  async deleteHospital(id) {
    return this.delete(`/hospitals/${id}`);
  }

  async getDoctors(params = {}) {
    return this.get('/doctors', params);
  }

  async createDoctor(data) {
    return this.post('/doctors', data);
  }

  async updateDoctor(id, data) {
    return this.put(`/doctors/${id}`, data);
  }

  async deleteDoctor(id) {
    return this.delete(`/doctors/${id}`);
  }

  async getServices(params = {}) {
    return this.get('/services', params);
  }

  async createService(data) {
    return this.post('/services', data);
  }

  async updateService(id, data) {
    return this.put(`/services/${id}`, data);
  }

  async deleteService(id) {
    return this.delete(`/services/${id}`);
  }

  async getStaff(params = {}) {
    return this.get('/staff', params);
  }

  async createStaff(data) {
    return this.post('/staff', data);
  }

  async updateStaff(id, data) {
    return this.put(`/staff/${id}`, data);
  }

  async deleteStaff(id) {
    return this.delete(`/staff/${id}`);
  }

  async getBookings(params = {}) {
    return this.get('/bookings', params);
  }

  async createBooking(data) {
    return this.post('/bookings', data);
  }

  async updateBooking(id, data) {
    return this.put(`/bookings/${id}`, data);
  }

  async deleteBooking(id) {
    return this.delete(`/bookings/${id}`);
  }

  async getConsultations(params = {}) {
    return this.get('/consultations', params);
  }

  async createConsultation(data) {
    return this.post('/consultations', data);
  }

  async updateConsultation(id, data) {
    return this.put(`/consultations/${id}`, data);
  }

  async deleteConsultation(id) {
    return this.delete(`/consultations/${id}`);
  }

  async getPayments(params = {}) {
    return this.get('/payments', params);
  }

  async createPayment(data) {
    return this.post('/payments', data);
  }

  async updatePayment(id, data) {
    return this.put(`/payments/${id}`, data);
  }

  async getSettings() {
    return this.get('/settings');
  }

  async updateSettings(data) {
    return this.post('/settings', data);
  }

  async getSetupStatus() {
    return this.get('/onboarding/status');
  }

  async getStatistics() {
    return this.get('/onboarding/statistics');
  }

  async completeSetup() {
    return this.put('/onboarding/complete');
  }
}

export const apiService = new ApiService();
