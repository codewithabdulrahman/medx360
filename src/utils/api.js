/**
 * WordPress REST API utility functions
 * Handles all API calls to WordPress backend
 */

const API_BASE_URL = window.medx360Data?.apiUrl || '/wp-json/medx360/v1';
const API_NONCE = window.medx360Data?.nonce || '';

/**
 * Generic API request function
 */
const apiRequest = async (endpoint, options = {}) => {
  const url = `${API_BASE_URL}${endpoint}`;
  
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': API_NONCE,
    },
  };

  const config = {
    ...defaultOptions,
    ...options,
    headers: {
      ...defaultOptions.headers,
      ...options.headers,
    },
  };

  try {
    const response = await fetch(url, config);
    
    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    
    return await response.json();
  } catch (error) {
    console.error('API request failed:', error);
    throw error;
  }
};

/**
 * Staff API functions
 */
export const staffAPI = {
  // Get all staff members
  getAll: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/staff?${queryParams}`);
  },

  // Get single staff member
  get: async (id) => {
    return apiRequest(`/staff/${id}`);
  },

  // Create new staff member
  create: async (data) => {
    return apiRequest('/staff', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // Update staff member
  update: async (id, data) => {
    return apiRequest(`/staff/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  // Delete staff member
  delete: async (id) => {
    return apiRequest(`/staff/${id}`, {
      method: 'DELETE',
    });
  },
};

/**
 * Clinic API functions
 */
export const clinicAPI = {
  // Get all clinics
  getAll: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/clinics?${queryParams}`);
  },

  // Get single clinic
  get: async (id) => {
    return apiRequest(`/clinics/${id}`);
  },

  // Create new clinic
  create: async (data) => {
    return apiRequest('/clinics', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // Update clinic
  update: async (id, data) => {
    return apiRequest(`/clinics/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  // Delete clinic
  delete: async (id) => {
    return apiRequest(`/clinics/${id}`, {
      method: 'DELETE',
    });
  },
};

/**
 * Service API functions
 */
export const serviceAPI = {
  // Get all services
  getAll: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/services?${queryParams}`);
  },

  // Get single service
  get: async (id) => {
    return apiRequest(`/services/${id}`);
  },

  // Create new service
  create: async (data) => {
    return apiRequest('/services', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // Update service
  update: async (id, data) => {
    return apiRequest(`/services/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  // Delete service
  delete: async (id) => {
    return apiRequest(`/services/${id}`, {
      method: 'DELETE',
    });
  },
};

/**
 * Dashboard API functions
 */
export const dashboardAPI = {
  // Get dashboard stats
  getStats: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/dashboard/stats?${queryParams}`);
  },

  // Get recent activities
  getRecentActivities: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/dashboard/recent-activities?${queryParams}`);
  },
};

/**
 * Patient API functions
 */
export const patientAPI = {
  // Get all patients
  getAll: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/patients?${queryParams}`);
  },

  // Get single patient
  get: async (id) => {
    return apiRequest(`/patients/${id}`);
  },

  // Create new patient
  create: async (data) => {
    return apiRequest('/patients', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // Update patient
  update: async (id, data) => {
    return apiRequest(`/patients/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  // Delete patient
  delete: async (id) => {
    return apiRequest(`/patients/${id}`, {
      method: 'DELETE',
    });
  },
};

/**
 * Appointment API functions
 */
export const appointmentAPI = {
  // Get all appointments
  getAll: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/appointments?${queryParams}`);
  },

  // Get single appointment
  get: async (id) => {
    return apiRequest(`/appointments/${id}`);
  },

  // Create new appointment
  create: async (data) => {
    return apiRequest('/appointments', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // Update appointment
  update: async (id, data) => {
    return apiRequest(`/appointments/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  // Delete appointment
  delete: async (id) => {
    return apiRequest(`/appointments/${id}`, {
      method: 'DELETE',
    });
  },

  // Get calendar appointments
  getCalendar: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/appointments/calendar?${queryParams}`);
  },
};

/**
 * Payment API functions
 */
export const paymentAPI = {
  // Get all payments
  getAll: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/payments?${queryParams}`);
  },

  // Get single payment
  get: async (id) => {
    return apiRequest(`/payments/${id}`);
  },

  // Create new payment
  create: async (data) => {
    return apiRequest('/payments', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // Update payment
  update: async (id, data) => {
    return apiRequest(`/payments/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },
};

/**
 * Notification API functions
 */
export const notificationAPI = {
  // Get all notifications
  getAll: async (params = {}) => {
    const queryParams = new URLSearchParams(params);
    return apiRequest(`/notifications?${queryParams}`);
  },

  // Mark notification as read
  markAsRead: async (id) => {
    return apiRequest(`/notifications/${id}/read`, {
      method: 'PUT',
    });
  },

  // Mark all notifications as read
  markAllAsRead: async () => {
    return apiRequest('/notifications/mark-all-read', {
      method: 'PUT',
    });
  },
};

/**
 * Settings API functions
 */
export const settingsAPI = {
  // Get all settings
  getAll: async () => {
    return apiRequest('/settings');
  },

  // Get single setting
  get: async (key) => {
    return apiRequest(`/settings/${key}`);
  },

  // Update settings
  update: async (settings) => {
    return apiRequest('/settings', {
      method: 'PUT',
      body: JSON.stringify({ settings }),
    });
  },
};

/**
 * Role API functions
 */
export const roleAPI = {
  // Get all roles
  getAll: async () => {
    return apiRequest('/roles');
  },

  // Create new role
  create: async (data) => {
    return apiRequest('/roles', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  // Update role
  update: async (id, data) => {
    return apiRequest(`/roles/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  // Get user permissions
  getPermissions: async (userId = null) => {
    const params = userId ? `?user_id=${userId}` : '';
    return apiRequest(`/permissions${params}`);
  },
};

export default {
  staffAPI,
  clinicAPI,
  serviceAPI,
  dashboardAPI,
  patientAPI,
  appointmentAPI,
  paymentAPI,
  notificationAPI,
  settingsAPI,
  roleAPI,
};
