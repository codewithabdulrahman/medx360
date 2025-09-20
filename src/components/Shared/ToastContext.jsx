import React, { createContext, useContext, useState, useCallback } from 'react';
import './Toast.css';

const ToastContext = createContext();

export const useToast = () => {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within a ToastProvider');
  }
  return context;
};

export const ToastProvider = ({ children }) => {
  const [toasts, setToasts] = useState([]);

  const addToast = useCallback((toast) => {
    const id = Date.now() + Math.random();
    const newToast = {
      id,
      type: 'info', // default type
      duration: 5000, // default duration
      position: 'top-right', // default position
      ...toast,
    };

    setToasts(prev => [...prev, newToast]);

    // Auto remove toast after duration
    if (newToast.duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, newToast.duration);
    }

    return id;
  }, []);

  const removeToast = useCallback((id) => {
    setToasts(prev => prev.filter(toast => toast.id !== id));
  }, []);

  const clearAllToasts = useCallback(() => {
    setToasts([]);
  }, []);

  // Convenience methods for different toast types
  const success = useCallback((message, options = {}) => {
    return addToast({ message, type: 'success', ...options });
  }, [addToast]);

  const error = useCallback((message, options = {}) => {
    return addToast({ message, type: 'error', ...options });
  }, [addToast]);

  const warning = useCallback((message, options = {}) => {
    return addToast({ message, type: 'warning', ...options });
  }, [addToast]);

  const info = useCallback((message, options = {}) => {
    return addToast({ message, type: 'info', ...options });
  }, [addToast]);

  const loading = useCallback((message, options = {}) => {
    return addToast({ message, type: 'loading', duration: 0, ...options });
  }, [addToast]);

  const promise = useCallback(async (promise, messages = {}) => {
    const loadingId = addToast({
      message: messages.loading || 'Loading...',
      type: 'loading',
      duration: 0,
    });

    try {
      const result = await promise;
      removeToast(loadingId);
      addToast({
        message: messages.success || 'Success!',
        type: 'success',
      });
      return result;
    } catch (error) {
      removeToast(loadingId);
      addToast({
        message: messages.error || error.message || 'An error occurred',
        type: 'error',
      });
      throw error;
    }
  }, [addToast, removeToast]);

  const value = {
    toasts,
    addToast,
    removeToast,
    clearAllToasts,
    success,
    error,
    warning,
    info,
    loading,
    promise,
  };

  return (
    <ToastContext.Provider value={value}>
      {children}
      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </ToastContext.Provider>
  );
};

const ToastContainer = ({ toasts, removeToast }) => {
  const positions = {
    'top-left': { top: '20px', left: '20px' },
    'top-center': { top: '20px', left: '50%', transform: 'translateX(-50%)' },
    'top-right': { top: '20px', right: '20px' },
    'bottom-left': { bottom: '20px', left: '20px' },
    'bottom-center': { bottom: '20px', left: '50%', transform: 'translateX(-50%)' },
    'bottom-right': { bottom: '20px', right: '20px' },
  };

  const groupedToasts = toasts.reduce((acc, toast) => {
    const position = toast.position || 'top-right';
    if (!acc[position]) acc[position] = [];
    acc[position].push(toast);
    return acc;
  }, {});

  return (
    <>
      {Object.entries(groupedToasts).map(([position, positionToasts]) => (
        <div
          key={position}
          className={`medx360-toast-container medx360-toast-${position}`}
          style={positions[position]}
        >
          {positionToasts.map((toast) => (
            <Toast key={toast.id} toast={toast} removeToast={removeToast} />
          ))}
        </div>
      ))}
    </>
  );
};

const Toast = ({ toast, removeToast }) => {
  const handleClose = () => {
    removeToast(toast.id);
  };

  const getIcon = () => {
    switch (toast.type) {
      case 'success':
        return '✅';
      case 'error':
        return '❌';
      case 'warning':
        return '⚠️';
      case 'loading':
        return '⏳';
      case 'info':
      default:
        return 'ℹ️';
    }
  };

  return (
    <div
      className={`medx360-toast medx360-toast-${toast.type}`}
      onClick={toast.dismissible !== false ? handleClose : undefined}
    >
      <div className="medx360-toast-content">
        <div className="medx360-toast-icon">{getIcon()}</div>
        <div className="medx360-toast-message">
          {toast.title && <div className="medx360-toast-title">{toast.title}</div>}
          <div className="medx360-toast-text">{toast.message}</div>
        </div>
        {toast.dismissible !== false && (
          <button
            className="medx360-toast-close"
            onClick={handleClose}
            aria-label="Close notification"
          >
            ×
          </button>
        )}
      </div>
      {toast.duration > 0 && (
        <div className="medx360-toast-progress">
          <div
            className="medx360-toast-progress-bar"
            style={{
              animationDuration: `${toast.duration}ms`,
            }}
          />
        </div>
      )}
    </div>
  );
};
