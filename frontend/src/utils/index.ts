import { clsx, type ClassValue } from 'clsx';
import { format, parseISO, isValid } from 'date-fns';

// Utility function for conditional classes
export function cn(...inputs: ClassValue[]) {
  return clsx(inputs);
}

// Date formatting utilities
export const formatDate = (date: string | Date, formatStr: string = 'MMM dd, yyyy'): string => {
  try {
    const dateObj = typeof date === 'string' ? parseISO(date) : date;
    if (!isValid(dateObj)) return 'Invalid Date';
    return format(dateObj, formatStr);
  } catch (error) {
    return 'Invalid Date';
  }
};

export const formatTime = (time: string): string => {
  try {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours, 10);
    const minute = parseInt(minutes, 10);
    
    if (isNaN(hour) || isNaN(minute)) return 'Invalid Time';
    
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${period}`;
  } catch (error) {
    return 'Invalid Time';
  }
};

export const formatDateTime = (dateTime: string): string => {
  try {
    const date = parseISO(dateTime);
    if (!isValid(date)) return 'Invalid Date';
    return format(date, 'MMM dd, yyyy h:mm a');
  } catch (error) {
    return 'Invalid Date';
  }
};

// Currency formatting
export const formatCurrency = (amount: number, currency: string = 'USD', symbol: string = '$'): string => {
  try {
    return `${symbol}${amount.toFixed(2)}`;
  } catch (error) {
    return `${symbol}0.00`;
  }
};

// Status formatting
export const formatStatus = (status: string): string => {
  return status
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

// Status colors
export const getStatusColor = (status: string): string => {
  const statusColors: Record<string, string> = {
    active: 'text-green-600 bg-green-100',
    inactive: 'text-gray-600 bg-gray-100',
    pending: 'text-yellow-600 bg-yellow-100',
    confirmed: 'text-green-600 bg-green-100',
    cancelled: 'text-red-600 bg-red-100',
    completed: 'text-blue-600 bg-blue-100',
    'no_show': 'text-orange-600 bg-orange-100',
    paid: 'text-green-600 bg-green-100',
    refunded: 'text-purple-600 bg-purple-100',
    failed: 'text-red-600 bg-red-100',
  };
  
  return statusColors[status] || 'text-gray-600 bg-gray-100';
};

// Validation utilities
export const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

export const validatePhone = (phone: string): boolean => {
  const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
  return phoneRegex.test(phone.replace(/\s/g, ''));
};

export const validateRequired = (value: any): boolean => {
  if (typeof value === 'string') {
    return value.trim().length > 0;
  }
  return value !== null && value !== undefined;
};

// Form validation
export interface ValidationRule {
  required?: boolean;
  minLength?: number;
  maxLength?: number;
  pattern?: RegExp;
  custom?: (value: any) => string | null;
}

export const validateField = (value: any, rules: ValidationRule): string | null => {
  if (rules.required && !validateRequired(value)) {
    return 'This field is required';
  }
  
  if (typeof value === 'string') {
    if (rules.minLength && value.length < rules.minLength) {
      return `Minimum length is ${rules.minLength}`;
    }
    
    if (rules.maxLength && value.length > rules.maxLength) {
      return `Maximum length is ${rules.maxLength}`;
    }
    
    if (rules.pattern && !rules.pattern.test(value)) {
      return 'Invalid format';
    }
  }
  
  if (rules.custom) {
    return rules.custom(value);
  }
  
  return null;
};

// Debounce utility
export const debounce = <T extends (...args: any[]) => any>(
  func: T,
  wait: number
): ((...args: Parameters<T>) => void) => {
  let timeout: NodeJS.Timeout;
  
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

// Local storage utilities
export const storage = {
  get: <T>(key: string, defaultValue: T): T => {
    try {
      const item = localStorage.getItem(key);
      return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
      return defaultValue;
    }
  },
  
  set: <T>(key: string, value: T): void => {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
      console.error('Failed to save to localStorage:', error);
    }
  },
  
  remove: (key: string): void => {
    try {
      localStorage.removeItem(key);
    } catch (error) {
      console.error('Failed to remove from localStorage:', error);
    }
  },
};

// API error handling
export const getErrorMessage = (error: any): string => {
  if (typeof error === 'string') {
    return error;
  }
  
  if (error?.message) {
    return error.message;
  }
  
  if (error?.response?.data?.message) {
    return error.response.data.message;
  }
  
  return 'An unexpected error occurred';
};

// Pagination utilities
export const getPaginationInfo = (page: number, perPage: number, total: number) => {
  const totalPages = Math.ceil(total / perPage);
  const startItem = (page - 1) * perPage + 1;
  const endItem = Math.min(page * perPage, total);
  
  return {
    totalPages,
    startItem,
    endItem,
    hasNextPage: page < totalPages,
    hasPrevPage: page > 1,
  };
};

// Search utilities
export const searchItems = <T>(
  items: T[],
  searchTerm: string,
  searchFields: (keyof T)[]
): T[] => {
  if (!searchTerm.trim()) {
    return items;
  }
  
  const term = searchTerm.toLowerCase();
  
  return items.filter(item =>
    searchFields.some(field => {
      const value = item[field];
      if (typeof value === 'string') {
        return value.toLowerCase().includes(term);
      }
      if (typeof value === 'number') {
        return value.toString().includes(term);
      }
      return false;
    })
  );
};

// Sort utilities
export const sortItems = <T>(
  items: T[],
  field: keyof T,
  direction: 'asc' | 'desc' = 'asc'
): T[] => {
  return [...items].sort((a, b) => {
    const aValue = a[field];
    const bValue = b[field];
    
    if (aValue < bValue) {
      return direction === 'asc' ? -1 : 1;
    }
    if (aValue > bValue) {
      return direction === 'asc' ? 1 : -1;
    }
    return 0;
  });
};

// Time utilities
export const getTimeSlots = (startTime: string, endTime: string, duration: number = 30): string[] => {
  const slots: string[] = [];
  const start = new Date(`2000-01-01T${startTime}`);
  const end = new Date(`2000-01-01T${endTime}`);
  
  let current = new Date(start);
  
  while (current < end) {
    slots.push(current.toTimeString().slice(0, 5));
    current.setMinutes(current.getMinutes() + duration);
  }
  
  return slots;
};

// Booking utilities
export const isBookingTimeAvailable = (
  date: string,
  time: string,
  doctorId: number,
  existingBookings: any[]
): boolean => {
  const bookingDateTime = new Date(`${date}T${time}`);
  const now = new Date();
  
  // Check if booking is in the past
  if (bookingDateTime <= now) {
    return false;
  }
  
  // Check for conflicts with existing bookings
  return !existingBookings.some(booking => {
    const existingDateTime = new Date(`${booking.appointment_date}T${booking.appointment_time}`);
    return existingDateTime.getTime() === bookingDateTime.getTime();
  });
};

// Notification utilities
export const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
  // This will be implemented with react-hot-toast
  console.log(`${type.toUpperCase()}: ${message}`);
};

// Export all utilities
export default {
  cn,
  formatDate,
  formatTime,
  formatDateTime,
  formatCurrency,
  formatStatus,
  getStatusColor,
  validateEmail,
  validatePhone,
  validateRequired,
  validateField,
  debounce,
  storage,
  getErrorMessage,
  getPaginationInfo,
  searchItems,
  sortItems,
  getTimeSlots,
  isBookingTimeAvailable,
  showNotification,
};
