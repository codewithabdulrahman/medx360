import React from 'react';
import { useToastNotifications } from '../../utils/toast';
import './ToastDemo.css';

const ToastDemo = () => {
  const toast = useToastNotifications();

  const handleSuccessToast = () => {
    toast.showSuccess('This is a success message!', {
      title: 'Success',
      duration: 4000,
    });
  };

  const handleErrorToast = () => {
    toast.showError('This is an error message!', {
      title: 'Error',
      duration: 5000,
    });
  };

  const handleWarningToast = () => {
    toast.showWarning('This is a warning message!', {
      title: 'Warning',
      duration: 4000,
    });
  };

  const handleInfoToast = () => {
    toast.showInfo('This is an info message!', {
      title: 'Information',
      duration: 3000,
    });
  };

  const handleLoadingToast = () => {
    const loadingId = toast.showLoading('Processing your request...', {
      title: 'Loading',
    });

    // Simulate async operation
    setTimeout(() => {
      toast.removeToast(loadingId);
      toast.showSuccess('Operation completed successfully!');
    }, 3000);
  };

  const handlePromiseToast = () => {
    const simulateApiCall = () => {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          if (Math.random() > 0.5) {
            resolve('Data loaded successfully!');
          } else {
            reject(new Error('Failed to load data'));
          }
        }, 2000);
      });
    };

    toast.handlePromise(simulateApiCall(), {
      loading: 'Loading data...',
      success: 'Data loaded successfully!',
      error: 'Failed to load data. Please try again.',
    });
  };

  const handleCrudToasts = () => {
    toast.showCreateSuccess('Patient');
    setTimeout(() => toast.showUpdateSuccess('Appointment'), 1000);
    setTimeout(() => toast.showDeleteSuccess('Service'), 2000);
  };

  const handleValidationToast = () => {
    toast.showValidationError('Please fill in all required fields');
  };

  const handlePermissionToast = () => {
    toast.showPermissionDenied();
  };

  const handleNetworkErrorToast = () => {
    toast.showNetworkError();
  };

  const handleCustomToast = () => {
    toast.showCustom('This is a custom toast message!', 'info', {
      title: 'Custom Toast',
      duration: 3000,
      position: 'top-center',
    });
  };

  const handleClearAll = () => {
    toast.clearAll();
  };

  return (
    <div className="medx360-toast-demo">
      <div className="medx360-toast-demo-header">
        <h2>🍞 Toast Notification System Demo</h2>
        <p>Click the buttons below to see different types of toast notifications in action!</p>
      </div>

      <div className="medx360-toast-demo-grid">
        <div className="medx360-toast-demo-section">
          <h3>Basic Toast Types</h3>
          <div className="medx360-toast-demo-buttons">
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-success"
              onClick={handleSuccessToast}
            >
              ✅ Success Toast
            </button>
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-error"
              onClick={handleErrorToast}
            >
              ❌ Error Toast
            </button>
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-warning"
              onClick={handleWarningToast}
            >
              ⚠️ Warning Toast
            </button>
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-info"
              onClick={handleInfoToast}
            >
              ℹ️ Info Toast
            </button>
          </div>
        </div>

        <div className="medx360-toast-demo-section">
          <h3>Advanced Features</h3>
          <div className="medx360-toast-demo-buttons">
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-loading"
              onClick={handleLoadingToast}
            >
              ⏳ Loading Toast
            </button>
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-promise"
              onClick={handlePromiseToast}
            >
              🔄 Promise Toast
            </button>
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-custom"
              onClick={handleCustomToast}
            >
              🎨 Custom Toast
            </button>
          </div>
        </div>

        <div className="medx360-toast-demo-section">
          <h3>CRUD Operations</h3>
          <div className="medx360-toast-demo-buttons">
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-crud"
              onClick={handleCrudToasts}
            >
              📝 CRUD Toasts
            </button>
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-validation"
              onClick={handleValidationToast}
            >
              ✏️ Validation Error
            </button>
          </div>
        </div>

        <div className="medx360-toast-demo-section">
          <h3>Error Handling</h3>
          <div className="medx360-toast-demo-buttons">
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-permission"
              onClick={handlePermissionToast}
            >
              🔒 Permission Denied
            </button>
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-network"
              onClick={handleNetworkErrorToast}
            >
              🌐 Network Error
            </button>
          </div>
        </div>

        <div className="medx360-toast-demo-section">
          <h3>Utility Actions</h3>
          <div className="medx360-toast-demo-buttons">
            <button 
              className="medx360-toast-demo-btn medx360-toast-demo-btn-clear"
              onClick={handleClearAll}
            >
              🗑️ Clear All Toasts
            </button>
          </div>
        </div>
      </div>

      <div className="medx360-toast-demo-info">
        <h3>Features:</h3>
        <ul>
          <li>✅ Multiple toast types (success, error, warning, info, loading)</li>
          <li>✅ Auto-dismiss with customizable duration</li>
          <li>✅ Manual dismiss with close button</li>
          <li>✅ Progress bar animation</li>
          <li>✅ Multiple positions (top-left, top-center, top-right, etc.)</li>
          <li>✅ Promise-based toast handling</li>
          <li>✅ CRUD operation shortcuts</li>
          <li>✅ Mobile responsive design</li>
          <li>✅ Dark mode support</li>
          <li>✅ Accessibility features</li>
          <li>✅ Smooth animations</li>
          <li>✅ Stack management</li>
        </ul>
      </div>
    </div>
  );
};

export default ToastDemo;
