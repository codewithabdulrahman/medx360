/**
 * Toast Notification Component
 */

import React, { useEffect, useState } from 'react';
import { CheckCircle, XCircle, AlertCircle, Info, X } from 'lucide-react';
import clsx from 'clsx';

const Toast = ({ 
  id,
  type = 'info', 
  title, 
  message, 
  duration = 5000, 
  onClose,
  position = 'top-right'
}) => {
  console.log('Toast: Rendering toast', { id, type, title, message });
  
  const [isVisible, setIsVisible] = useState(true);
  const [isLeaving, setIsLeaving] = useState(false);

  useEffect(() => {
    if (duration > 0) {
      const timer = setTimeout(() => {
        handleClose();
      }, duration);

      return () => clearTimeout(timer);
    }
  }, [duration]);

  const handleClose = () => {
    setIsLeaving(true);
    setTimeout(() => {
      setIsVisible(false);
      onClose?.(id);
    }, 300);
  };

  if (!isVisible) return null;

  const typeConfig = {
    success: {
      icon: CheckCircle,
      bgColor: 'bg-green-50',
      borderColor: 'border-green-200',
      iconColor: 'text-green-400',
      titleColor: 'text-green-800',
      messageColor: 'text-green-700'
    },
    error: {
      icon: XCircle,
      bgColor: 'bg-red-50',
      borderColor: 'border-red-200',
      iconColor: 'text-red-400',
      titleColor: 'text-red-800',
      messageColor: 'text-red-700'
    },
    warning: {
      icon: AlertCircle,
      bgColor: 'bg-yellow-50',
      borderColor: 'border-yellow-200',
      iconColor: 'text-yellow-400',
      titleColor: 'text-yellow-800',
      messageColor: 'text-yellow-700'
    },
    info: {
      icon: Info,
      bgColor: 'bg-blue-50',
      borderColor: 'border-blue-200',
      iconColor: 'text-blue-400',
      titleColor: 'text-blue-800',
      messageColor: 'text-blue-700'
    }
  };

  const positionClasses = {
    'top-right': 'top-4 right-4',
    'top-left': 'top-4 left-4',
    'bottom-right': 'bottom-4 right-4',
    'bottom-left': 'bottom-4 left-4',
    'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
    'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2'
  };

  const config = typeConfig[type];
  const Icon = config.icon;

  return (
    <div className={clsx(
      'fixed z-50 max-w-sm w-full',
      positionClasses[position],
      'transition-all duration-300 ease-in-out',
      isLeaving ? 'opacity-0 scale-95' : 'opacity-100 scale-100'
    )}>
      <div className={clsx(
        'rounded-lg border p-4 shadow-lg',
        config.bgColor,
        config.borderColor
      )}>
        <div className="flex items-start">
          <div className="flex-shrink-0">
            <Icon className={clsx('h-5 w-5', config.iconColor)} />
          </div>
          <div className="ml-3 flex-1">
            {title && (
              <h4 className={clsx('text-sm font-medium', config.titleColor)}>
                {title}
              </h4>
            )}
            {message && (
              <p className={clsx(
                'text-sm',
                config.messageColor,
                title ? 'mt-1' : ''
              )}>
                {message}
              </p>
            )}
          </div>
          <div className="ml-4 flex-shrink-0">
            <button
              onClick={handleClose}
              className={clsx(
                'rounded-md p-1 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500',
                config.iconColor
              )}
            >
              <X className="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

// Toast Container Component
export const ToastContainer = ({ toasts, onRemove }) => {
  console.log('ToastContainer: Rendering toasts', toasts);
  
  return (
    <div className="fixed inset-0 pointer-events-none z-50">
      {toasts.map((toast) => (
        <Toast
          key={toast.id}
          {...toast}
          onClose={onRemove}
        />
      ))}
    </div>
  );
};

// Toast Hook
export const useToast = () => {
  const [toasts, setToasts] = useState([]);

  const addToast = (toast) => {
    const id = Date.now() + Math.random();
    const newToast = {
      id,
      type: 'info',
      duration: 5000,
      ...toast
    };

    setToasts(prev => [...prev, newToast]);
    return id;
  };

  const removeToast = (id) => {
    setToasts(prev => prev.filter(toast => toast.id !== id));
  };

  const success = (title, message, options = {}) => {
    return addToast({ type: 'success', title, message, ...options });
  };

  const error = (title, message, options = {}) => {
    return addToast({ type: 'error', title, message, ...options });
  };

  const warning = (title, message, options = {}) => {
    return addToast({ type: 'warning', title, message, ...options });
  };

  const info = (title, message, options = {}) => {
    return addToast({ type: 'info', title, message, ...options });
  };

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info
  };
};

export default Toast;
