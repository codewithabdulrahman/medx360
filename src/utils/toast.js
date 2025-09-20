/**
 * Toast Utility Functions
 * Provides convenient methods for showing toast notifications
 */

import { useToast } from '../components/Shared/ToastContext';

/**
 * Custom hook for toast notifications
 */
export const useToastNotifications = () => {
  const toast = useToast();

  return {
    // Basic toast methods
    showSuccess: (message, options = {}) => toast.success(message, options),
    showError: (message, options = {}) => toast.error(message, options),
    showWarning: (message, options = {}) => toast.warning(message, options),
    showInfo: (message, options = {}) => toast.info(message, options),
    showLoading: (message, options = {}) => toast.loading(message, options),

    // Promise-based toast
    handlePromise: (promise, messages = {}) => toast.promise(promise, messages),

    // CRUD operation toasts
    showCreateSuccess: (itemName = 'Item') => 
      toast.success(`${itemName} created successfully!`, { duration: 3000 }),
    
    showUpdateSuccess: (itemName = 'Item') => 
      toast.success(`${itemName} updated successfully!`, { duration: 3000 }),
    
    showDeleteSuccess: (itemName = 'Item') => 
      toast.success(`${itemName} deleted successfully!`, { duration: 3000 }),
    
    showCreateError: (itemName = 'Item', error = 'Unknown error') => 
      toast.error(`Failed to create ${itemName.toLowerCase()}. ${error}`, { duration: 5000 }),
    
    showUpdateError: (itemName = 'Item', error = 'Unknown error') => 
      toast.error(`Failed to update ${itemName.toLowerCase()}. ${error}`, { duration: 5000 }),
    
    showDeleteError: (itemName = 'Item', error = 'Unknown error') => 
      toast.error(`Failed to delete ${itemName.toLowerCase()}. ${error}`, { duration: 5000 }),

    // Form validation toasts
    showValidationError: (message = 'Please check your input') => 
      toast.error(message, { duration: 4000 }),
    
    showRequiredField: (fieldName) => 
      toast.warning(`${fieldName} is required`, { duration: 3000 }),

    // API operation toasts
    showApiError: (error, defaultMessage = 'An error occurred') => {
      const message = error?.response?.data?.message || error?.message || defaultMessage;
      toast.error(message, { duration: 5000 });
    },

    showNetworkError: () => 
      toast.error('Network error. Please check your connection.', { duration: 5000 }),

    showServerError: () => 
      toast.error('Server error. Please try again later.', { duration: 5000 }),

    // Authentication toasts
    showLoginSuccess: () => 
      toast.success('Welcome back!', { duration: 3000 }),
    
    showLoginError: (message = 'Invalid credentials') => 
      toast.error(message, { duration: 4000 }),
    
    showLogoutSuccess: () => 
      toast.success('Logged out successfully', { duration: 3000 }),

    // Permission toasts
    showPermissionDenied: () => 
      toast.warning('You do not have permission to perform this action', { duration: 4000 }),

    // File operation toasts
    showFileUploadSuccess: (fileName) => 
      toast.success(`File "${fileName}" uploaded successfully!`, { duration: 3000 }),
    
    showFileUploadError: (fileName, error = 'Upload failed') => 
      toast.error(`Failed to upload "${fileName}". ${error}`, { duration: 5000 }),
    
    showFileDeleteSuccess: (fileName) => 
      toast.success(`File "${fileName}" deleted successfully!`, { duration: 3000 }),

    // Booking system toasts
    showAppointmentBooked: (date, time) => 
      toast.success(`Appointment booked for ${date} at ${time}`, { duration: 4000 }),
    
    showAppointmentCancelled: () => 
      toast.success('Appointment cancelled successfully', { duration: 3000 }),
    
    showAppointmentRescheduled: (newDate, newTime) => 
      toast.success(`Appointment rescheduled to ${newDate} at ${newTime}`, { duration: 4000 }),

    // Payment toasts
    showPaymentSuccess: (amount) => 
      toast.success(`Payment of $${amount} processed successfully!`, { duration: 4000 }),
    
    showPaymentError: (error = 'Payment failed') => 
      toast.error(error, { duration: 5000 }),

    // Settings toasts
    showSettingsSaved: () => 
      toast.success('Settings saved successfully!', { duration: 3000 }),
    
    showSettingsError: (error = 'Failed to save settings') => 
      toast.error(error, { duration: 4000 }),

    // Clear all toasts
    clearAll: () => toast.clearAllToasts(),

    // Custom toast
    showCustom: (message, type = 'info', options = {}) => 
      toast.addToast({ message, type, ...options }),
  };
};

/**
 * Toast message templates
 */
export const TOAST_MESSAGES = {
  // Success messages
  SUCCESS: {
    CREATED: 'Created successfully!',
    UPDATED: 'Updated successfully!',
    DELETED: 'Deleted successfully!',
    SAVED: 'Saved successfully!',
    SENT: 'Sent successfully!',
    UPLOADED: 'Uploaded successfully!',
    DOWNLOADED: 'Downloaded successfully!',
    COPIED: 'Copied to clipboard!',
    IMPORTED: 'Imported successfully!',
    EXPORTED: 'Exported successfully!',
  },

  // Error messages
  ERROR: {
    CREATION_FAILED: 'Failed to create item',
    UPDATE_FAILED: 'Failed to update item',
    DELETE_FAILED: 'Failed to delete item',
    SAVE_FAILED: 'Failed to save changes',
    SEND_FAILED: 'Failed to send message',
    UPLOAD_FAILED: 'Failed to upload file',
    DOWNLOAD_FAILED: 'Failed to download file',
    COPY_FAILED: 'Failed to copy to clipboard',
    IMPORT_FAILED: 'Failed to import data',
    EXPORT_FAILED: 'Failed to export data',
    NETWORK_ERROR: 'Network error. Please check your connection.',
    SERVER_ERROR: 'Server error. Please try again later.',
    VALIDATION_ERROR: 'Please check your input',
    PERMISSION_DENIED: 'You do not have permission to perform this action',
  },

  // Warning messages
  WARNING: {
    UNSAVED_CHANGES: 'You have unsaved changes',
    CONFIRM_DELETE: 'Are you sure you want to delete this item?',
    CONFIRM_ACTION: 'Are you sure you want to perform this action?',
    REQUIRED_FIELD: 'This field is required',
    INVALID_FORMAT: 'Invalid format',
    EXPIRING_SOON: 'This item is expiring soon',
  },

  // Info messages
  INFO: {
    LOADING: 'Loading...',
    PROCESSING: 'Processing...',
    SAVING: 'Saving...',
    UPLOADING: 'Uploading...',
    DOWNLOADING: 'Downloading...',
    SYNCING: 'Syncing...',
    CONNECTING: 'Connecting...',
  },
};

/**
 * Toast configuration presets
 */
export const TOAST_CONFIG = {
  // Duration presets (in milliseconds)
  DURATION: {
    SHORT: 2000,
    MEDIUM: 4000,
    LONG: 6000,
    PERSISTENT: 0, // No auto-dismiss
  },

  // Position presets
  POSITION: {
    TOP_LEFT: 'top-left',
    TOP_CENTER: 'top-center',
    TOP_RIGHT: 'top-right',
    BOTTOM_LEFT: 'bottom-left',
    BOTTOM_CENTER: 'bottom-center',
    BOTTOM_RIGHT: 'bottom-right',
  },

  // Type presets
  TYPE: {
    SUCCESS: 'success',
    ERROR: 'error',
    WARNING: 'warning',
    INFO: 'info',
    LOADING: 'loading',
  },
};

/**
 * Utility function to create toast options
 */
export const createToastOptions = (options = {}) => ({
  duration: TOAST_CONFIG.DURATION.MEDIUM,
  position: TOAST_CONFIG.POSITION.TOP_RIGHT,
  dismissible: true,
  ...options,
});

/**
 * Utility function to handle API errors with toast
 */
export const handleApiError = (error, toast) => {
  if (error?.response?.status === 401) {
    toast.showError('Please log in again', { duration: 4000 });
  } else if (error?.response?.status === 403) {
    toast.showPermissionDenied();
  } else if (error?.response?.status === 404) {
    toast.showError('Item not found', { duration: 4000 });
  } else if (error?.response?.status >= 500) {
    toast.showServerError();
  } else if (error?.code === 'NETWORK_ERROR') {
    toast.showNetworkError();
  } else {
    toast.showApiError(error);
  }
};
