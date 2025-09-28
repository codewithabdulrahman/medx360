/**
 * Reusable Form Components
 */

import React from 'react';
import { Loader2, AlertCircle, CheckCircle } from 'lucide-react';
import clsx from 'clsx';

// ==================== FORM INPUT COMPONENTS ====================

export const FormInput = ({ 
  label, 
  error, 
  required = false, 
  className = '', 
  ...props 
}) => {
  return (
    <div className={clsx('space-y-2', className)}>
      {label && (
        <label className="block text-sm font-medium text-gray-700">
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}
      <input
        className={clsx(
          'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm',
          'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
          'disabled:bg-gray-50 disabled:text-gray-500',
          error && 'border-red-300 focus:ring-red-500 focus:border-red-500'
        )}
        {...props}
      />
      {error && (
        <p className="text-sm text-red-600 flex items-center">
          <AlertCircle className="h-4 w-4 mr-1" />
          {error}
        </p>
      )}
    </div>
  );
};

export const FormTextarea = ({ 
  label, 
  error, 
  required = false, 
  className = '', 
  ...props 
}) => {
  return (
    <div className={clsx('space-y-2', className)}>
      {label && (
        <label className="block text-sm font-medium text-gray-700">
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}
      <textarea
        className={clsx(
          'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm',
          'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
          'disabled:bg-gray-50 disabled:text-gray-500',
          error && 'border-red-300 focus:ring-red-500 focus:border-red-500'
        )}
        rows={3}
        {...props}
      />
      {error && (
        <p className="text-sm text-red-600 flex items-center">
          <AlertCircle className="h-4 w-4 mr-1" />
          {error}
        </p>
      )}
    </div>
  );
};

export const FormSelect = ({ 
  label, 
  error, 
  required = false, 
  options = [], 
  className = '', 
  ...props 
}) => {
  return (
    <div className={clsx('space-y-2', className)}>
      {label && (
        <label className="block text-sm font-medium text-gray-700">
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}
      <select
        className={clsx(
          'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm',
          'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
          'disabled:bg-gray-50 disabled:text-gray-500',
          error && 'border-red-300 focus:ring-red-500 focus:border-red-500'
        )}
        {...props}
      >
        <option value="">Select an option</option>
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      {error && (
        <p className="text-sm text-red-600 flex items-center">
          <AlertCircle className="h-4 w-4 mr-1" />
          {error}
        </p>
      )}
    </div>
  );
};

export const FormCheckbox = ({ 
  label, 
  error, 
  className = '', 
  ...props 
}) => {
  return (
    <div className={clsx('space-y-2', className)}>
      <label className="flex items-center">
        <input
          type="checkbox"
          className={clsx(
            'h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded',
            error && 'border-red-300'
          )}
          {...props}
        />
        <span className="ml-2 text-sm text-gray-700">{label}</span>
      </label>
      {error && (
        <p className="text-sm text-red-600 flex items-center">
          <AlertCircle className="h-4 w-4 mr-1" />
          {error}
        </p>
      )}
    </div>
  );
};

// ==================== FORM BUTTON COMPONENTS ====================

export const FormButton = ({ 
  children, 
  loading = false, 
  variant = 'primary', 
  size = 'md', 
  className = '', 
  ...props 
}) => {
  const baseClasses = 'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
  
  const variantClasses = {
    primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    outline: 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-primary-500',
  };
  
  const sizeClasses = {
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base',
  };
  
  return (
    <button
      className={clsx(
        baseClasses,
        variantClasses[variant],
        sizeClasses[size],
        className
      )}
      disabled={loading}
      {...props}
    >
      {loading && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
      {children}
    </button>
  );
};

// ==================== FORM LAYOUT COMPONENTS ====================

export const FormCard = ({ children, className = '' }) => {
  return (
    <div className={clsx('bg-white shadow rounded-lg p-6', className)}>
      {children}
    </div>
  );
};

export const FormSection = ({ title, description, children, className = '' }) => {
  return (
    <div className={clsx('space-y-6', className)}>
      {(title || description) && (
        <div>
          {title && (
            <h3 className="text-lg font-medium text-gray-900">{title}</h3>
          )}
          {description && (
            <p className="mt-1 text-sm text-gray-600">{description}</p>
          )}
        </div>
      )}
      <div className="space-y-4">
        {children}
      </div>
    </div>
  );
};

export const FormGrid = ({ children, cols = 2, className = '' }) => {
  const gridClasses = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 md:grid-cols-2',
    3: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
  };
  
  return (
    <div className={clsx('grid gap-4', gridClasses[cols], className)}>
      {children}
    </div>
  );
};

// ==================== FORM VALIDATION UTILITIES ====================

export const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

export const validatePhone = (phone) => {
  const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
  return phoneRegex.test(phone.replace(/\s/g, ''));
};

export const validateRequired = (value) => {
  return value && value.toString().trim().length > 0;
};

export const validateMinLength = (value, minLength) => {
  return value && value.toString().length >= minLength;
};

export const validateMaxLength = (value, maxLength) => {
  return !value || value.toString().length <= maxLength;
};

// ==================== FORM STATUS COMPONENTS ====================

export const FormStatus = ({ type, message, className = '' }) => {
  if (!message) return null;
  
  const statusClasses = {
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    info: 'bg-blue-50 border-blue-200 text-blue-800',
  };
  
  const iconClasses = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400',
  };
  
  const Icon = type === 'success' ? CheckCircle : AlertCircle;
  
  return (
    <div className={clsx(
      'border rounded-md p-4 flex items-start',
      statusClasses[type],
      className
    )}>
      <Icon className={clsx('h-5 w-5 mr-3 flex-shrink-0', iconClasses[type])} />
      <div className="text-sm font-medium">{message}</div>
    </div>
  );
};

// ==================== FORM LOADING COMPONENT ====================

export const FormLoading = ({ message = 'Loading...', className = '' }) => {
  return (
    <div className={clsx('flex items-center justify-center p-8', className)}>
      <div className="flex items-center space-x-3">
        <Loader2 className="h-6 w-6 animate-spin text-primary-600" />
        <span className="text-gray-600">{message}</span>
      </div>
    </div>
  );
};
