/**
 * Toast Context for sharing toast state across components
 */

import React, { createContext, useContext, useState } from 'react';

const ToastContext = createContext();

export const useToastContext = () => {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToastContext must be used within a ToastProvider');
  }
  return context;
};

export const ToastProvider = ({ children }) => {
  const [toasts, setToasts] = useState([]);

  const addToast = (toast) => {
    const id = Date.now() + Math.random();
    const newToast = {
      id,
      type: 'info',
      duration: 5000,
      ...toast
    };

    console.log('ToastContext: Adding toast', newToast);
    setToasts(prev => {
      const updated = [...prev, newToast];
      console.log('ToastContext: Updated toasts array', updated);
      return updated;
    });
    return id;
  };

  const removeToast = (id) => {
    setToasts(prev => prev.filter(toast => toast.id !== id));
  };

  const success = (title, message, options = {}) => {
    return addToast({ type: 'success', title, message, ...options });
  };

  const error = (title, message, options = {}) => {
    console.log('ToastContext: Adding error toast', { title, message, options });
    return addToast({ type: 'error', title, message, ...options });
  };

  const info = (title, message, options = {}) => {
    return addToast({ type: 'info', title, message, ...options });
  };

  const warning = (title, message, options = {}) => {
    return addToast({ type: 'warning', title, message, ...options });
  };

  const value = {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    info,
    warning
  };

  return (
    <ToastContext.Provider value={value}>
      {children}
    </ToastContext.Provider>
  );
};
