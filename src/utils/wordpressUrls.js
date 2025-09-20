/**
 * WordPress URL utilities for Medx360 plugin
 */

/**
 * Generate WordPress admin URL for a given page using hash fragments
 * @param {string} page - The page identifier (e.g., 'dashboard', 'staff')
 * @returns {string} - Complete WordPress admin URL with hash
 */
export const getWordPressUrl = (page) => {
  const baseUrl = window.location.origin + '/wp-admin/admin.php';
  return `${baseUrl}?page=medx360#${page}`;
};

/**
 * Get current page from WordPress URL hash fragment
 * @returns {string} - Current page identifier
 */
export const getCurrentPage = () => {
  // First check hash fragment
  const hash = window.location.hash.replace('#', '');
  if (hash) {
    return hash;
  }
  
  // Fallback to URL parameters for backward compatibility
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get('page');
  
  if (page && page.startsWith('medx360/')) {
    return page.replace('medx360/', '');
  }
  
  return 'dashboard';
};

/**
 * Check if current page matches the given page
 * @param {string} page - Page to check against
 * @returns {boolean} - True if current page matches
 */
export const isCurrentPage = (page) => {
  const currentPage = getCurrentPage();
  return currentPage === page || (page === 'dashboard' && !currentPage);
};

/**
 * Navigate to a page using hash fragment
 * @param {string} page - Page to navigate to
 */
export const navigateToPage = (page) => {
  window.location.hash = page;
};

/**
 * Navigate to a page using hash fragment (programmatic)
 * @param {string} page - Page to navigate to
 */
export const navigateToPageProgrammatic = (page) => {
  // Update the hash without triggering a page reload
  window.history.pushState(null, null, `#${page}`);
  
  // Dispatch a custom event to notify components
  window.dispatchEvent(new HashChangeEvent('hashchange'));
};

/**
 * WordPress admin page mappings (hash fragments)
 */
export const WORDPRESS_PAGES = {
  DASHBOARD: 'dashboard',
  SETUP: 'setup',
  BOOKING: 'booking',
  BOOKING_NEW: 'booking/new',
  BOOKING_LIST: 'booking/list',
  PATIENTS: 'patients',
  PATIENTS_NEW: 'patients/new',
  PATIENTS_PROFILE: 'patients/profile',
  PAYMENTS: 'payments',
  PAYMENTS_NEW: 'payments/new',
  BILLING: 'billing',
  STAFF: 'staff',
  STAFF_NEW: 'staff/new',
  STAFF_SCHEDULE: 'staff/schedule',
  NOTIFICATIONS: 'notifications',
  NOTIFICATIONS_SETTINGS: 'notifications/settings',
  REPORTS: 'reports',
  REPORTS_APPOINTMENTS: 'reports/appointments',
  REPORTS_FINANCIAL: 'reports/financial',
  ROLES: 'roles',
  PERMISSIONS: 'permissions',
  SETTINGS: 'settings',
  PROFILE: 'profile',
  CLINIC: 'clinic',
  CLINIC_NEW: 'clinic/new',
  CLINIC_EDIT: 'clinic/edit',
  SERVICE: 'service',
  SERVICE_NEW: 'service/new',
  SERVICE_EDIT: 'service/edit',
};
