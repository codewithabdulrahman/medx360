import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import { useToastNotifications } from '../../utils/toast';
import './StaffForm.css';

const StaffForm = () => {
  const toast = useToastNotifications();
  
  const [formData, setFormData] = useState({
    name: '',
    role: 'doctor',
    specialization: '',
    email: '',
    phone: '',
    address: '',
    emergencyContact: '',
    emergencyPhone: '',
    licenseNumber: '',
    status: 'active',
    joinDate: new Date().toISOString().split('T')[0],
    notes: ''
  });

  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [isEditMode, setIsEditMode] = useState(false);

  // Check if we're editing an existing staff member
  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const staffId = urlParams.get('id');
    
    if (staffId) {
      setIsEditMode(true);
      // In a real app, you would fetch the staff data here
      // For now, we'll simulate loading existing data
      setFormData(prev => ({
        ...prev,
        name: 'Dr. Sarah Johnson',
        specialization: 'Cardiology',
        email: 'sarah.johnson@clinic.com',
        phone: '+1 (555) 123-4567'
      }));
    }
  }, []);

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Name is required';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid';
    }

    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required';
    }

    if (!formData.specialization.trim()) {
      newErrors.specialization = 'Specialization is required';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setLoading(true);
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Save to localStorage for persistence
      const staff = JSON.parse(localStorage.getItem('medx360_staff') || '[]');
      const staffData = {
        ...formData,
        id: isEditMode ? parseInt(new URLSearchParams(window.location.search).get('id')) : Date.now(),
        createdAt: isEditMode ? new Date().toISOString() : new Date().toISOString(),
        updatedAt: new Date().toISOString(),
        avatar: formData.role === 'doctor' ? '👨‍⚕️' : formData.role === 'nurse' ? '👩‍⚕️' : '👨‍💼'
      };

      if (isEditMode) {
        const index = staff.findIndex(s => s.id === staffData.id);
        if (index !== -1) {
          staff[index] = staffData;
        }
      } else {
        staff.push(staffData);
      }

      localStorage.setItem('medx360_staff', JSON.stringify(staff));
      
      console.log('Staff data saved:', staffData);
      
      // Show success message
      if (isEditMode) {
        toast.showUpdateSuccess('Staff member');
      } else {
        toast.showCreateSuccess('Staff member');
      }
      
      // Redirect to staff list
      setTimeout(() => {
        window.location.href = getWordPressUrl(WORDPRESS_PAGES.STAFF);
      }, 1000);
      
    } catch (error) {
      console.error('Error saving staff:', error);
      toast.showUpdateError('Staff member', error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    if (window.confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
      window.location.href = getWordPressUrl(WORDPRESS_PAGES.STAFF);
    }
  };

  return (
    <div className="medx360-staff-form">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>{isEditMode ? 'Edit Staff Member' : 'Add New Staff Member'}</h1>
            <p>{isEditMode ? 'Update staff member information' : 'Add a new team member to your medical practice'}</p>
          </div>
          <div className="medx360-header-actions">
            <button
              type="button"
              onClick={handleCancel}
              className="medx360-btn medx360-btn-secondary"
            >
              ← Back to Staff List
            </button>
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="medx360-form">
        <div className="medx360-form-sections">
          {/* Basic Information */}
          <div className="medx360-form-section">
            <h3>👤 Basic Information</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Full Name *</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => handleInputChange('name', e.target.value)}
                  placeholder="Dr. John Smith"
                  className={errors.name ? 'medx360-input-error' : ''}
                />
                {errors.name && <span className="medx360-error-message">{errors.name}</span>}
              </div>

              <div className="medx360-form-group">
                <label>Role *</label>
                <select
                  value={formData.role}
                  onChange={(e) => handleInputChange('role', e.target.value)}
                >
                  <option value="doctor">Doctor</option>
                  <option value="nurse">Nurse</option>
                  <option value="therapist">Therapist</option>
                  <option value="specialist">Specialist</option>
                  <option value="assistant">Assistant</option>
                  <option value="admin">Administrator</option>
                </select>
              </div>

              <div className="medx360-form-group">
                <label>Specialization *</label>
                <input
                  type="text"
                  value={formData.specialization}
                  onChange={(e) => handleInputChange('specialization', e.target.value)}
                  placeholder="e.g., Cardiology, Pediatrics"
                  className={errors.specialization ? 'medx360-input-error' : ''}
                />
                {errors.specialization && <span className="medx360-error-message">{errors.specialization}</span>}
              </div>

              <div className="medx360-form-group">
                <label>Status</label>
                <select
                  value={formData.status}
                  onChange={(e) => handleInputChange('status', e.target.value)}
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>

          {/* Contact Information */}
          <div className="medx360-form-section">
            <h3>📞 Contact Information</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Email Address *</label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => handleInputChange('email', e.target.value)}
                  placeholder="john.smith@clinic.com"
                  className={errors.email ? 'medx360-input-error' : ''}
                />
                {errors.email && <span className="medx360-error-message">{errors.email}</span>}
              </div>

              <div className="medx360-form-group">
                <label>Phone Number *</label>
                <input
                  type="tel"
                  value={formData.phone}
                  onChange={(e) => handleInputChange('phone', e.target.value)}
                  placeholder="+1 (555) 123-4567"
                  className={errors.phone ? 'medx360-input-error' : ''}
                />
                {errors.phone && <span className="medx360-error-message">{errors.phone}</span>}
              </div>

              <div className="medx360-form-group medx360-full-width">
                <label>Address</label>
                <textarea
                  value={formData.address}
                  onChange={(e) => handleInputChange('address', e.target.value)}
                  placeholder="123 Main Street, City, State, ZIP Code"
                  rows="3"
                />
              </div>
            </div>
          </div>

          {/* Professional Information */}
          <div className="medx360-form-section">
            <h3>🏥 Professional Information</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>License Number</label>
                <input
                  type="text"
                  value={formData.licenseNumber}
                  onChange={(e) => handleInputChange('licenseNumber', e.target.value)}
                  placeholder="e.g., MD123456"
                />
              </div>

              <div className="medx360-form-group">
                <label>Join Date</label>
                <input
                  type="date"
                  value={formData.joinDate}
                  onChange={(e) => handleInputChange('joinDate', e.target.value)}
                />
              </div>
            </div>
          </div>

          {/* Emergency Contact */}
          <div className="medx360-form-section">
            <h3>🚨 Emergency Contact</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Emergency Contact Name</label>
                <input
                  type="text"
                  value={formData.emergencyContact}
                  onChange={(e) => handleInputChange('emergencyContact', e.target.value)}
                  placeholder="Jane Smith"
                />
              </div>

              <div className="medx360-form-group">
                <label>Emergency Contact Phone</label>
                <input
                  type="tel"
                  value={formData.emergencyPhone}
                  onChange={(e) => handleInputChange('emergencyPhone', e.target.value)}
                  placeholder="+1 (555) 987-6543"
                />
              </div>
            </div>
          </div>

          {/* Additional Notes */}
          <div className="medx360-form-section">
            <h3>📝 Additional Notes</h3>
            <div className="medx360-form-group">
              <label>Notes</label>
              <textarea
                value={formData.notes}
                onChange={(e) => handleInputChange('notes', e.target.value)}
                placeholder="Any additional information about this staff member..."
                rows="4"
              />
            </div>
          </div>
        </div>

        <div className="medx360-form-actions">
          <button
            type="button"
            onClick={handleCancel}
            className="medx360-btn medx360-btn-secondary"
            disabled={loading}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="medx360-btn medx360-btn-primary"
            disabled={loading}
          >
            {loading ? (
              <>
                <div className="medx360-spinner-small"></div>
                {isEditMode ? 'Updating...' : 'Adding...'}
              </>
            ) : (
              <>
                {isEditMode ? '✏️ Update Staff Member' : '➕ Add Staff Member'}
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

export default StaffForm;