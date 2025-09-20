import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import { useToastNotifications } from '../../utils/toast';
import './ServiceForm.css';

const ServiceForm = () => {
  const toast = useToastNotifications();
  
  const [formData, setFormData] = useState({
    name: '',
    category: 'General',
    duration: 30,
    price: 0,
    description: '',
    status: 'active',
    createdDate: new Date().toISOString().split('T')[0],
    icon: '🩺',
    staffAssigned: [],
    notes: '',
    requirements: '',
    preparationInstructions: '',
    followUpInstructions: ''
  });

  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [isEditMode, setIsEditMode] = useState(false);
  const [availableStaff, setAvailableStaff] = useState([]);

  // Check if we're editing an existing service
  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const serviceId = urlParams.get('id');
    
    if (serviceId) {
      setIsEditMode(true);
      // Load existing service data
      loadServiceData(serviceId);
    }
    
    // Load available staff
    loadAvailableStaff();
  }, []);

  const loadServiceData = (serviceId) => {
    // In a real app, this would fetch from API
    // For now, we'll simulate loading existing data
    const existingService = {
      name: 'General Consultation',
      category: 'General',
      duration: 30,
      price: 150.00,
      description: 'Comprehensive general health consultation and examination',
      status: 'active',
      createdDate: '2023-01-15',
      icon: '🩺',
      staffAssigned: ['Dr. Sarah Johnson', 'Dr. Michael Chen'],
      notes: 'Standard consultation service',
      requirements: 'Valid ID and insurance card',
      preparationInstructions: 'Please arrive 15 minutes early',
      followUpInstructions: 'Follow up in 2 weeks if needed'
    };
    
    setFormData(prev => ({
      ...prev,
      ...existingService
    }));
  };

  const loadAvailableStaff = () => {
    // In a real app, this would fetch from API
    const staff = [
      'Dr. Sarah Johnson',
      'Dr. Michael Chen',
      'Nurse Emily Davis',
      'Dr. Robert Wilson',
      'Dr. Lisa Anderson',
      'Therapist Mark Thompson'
    ];
    setAvailableStaff(staff);
  };

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

  const handleStaffToggle = (staff) => {
    setFormData(prev => ({
      ...prev,
      staffAssigned: prev.staffAssigned.includes(staff)
        ? prev.staffAssigned.filter(s => s !== staff)
        : [...prev.staffAssigned, staff]
    }));
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Service name is required';
    }

    if (!formData.description.trim()) {
      newErrors.description = 'Description is required';
    }

    if (formData.duration <= 0) {
      newErrors.duration = 'Duration must be greater than 0';
    }

    if (formData.price < 0) {
      newErrors.price = 'Price cannot be negative';
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
      const services = JSON.parse(localStorage.getItem('medx360_services') || '[]');
      const serviceData = {
        ...formData,
        id: isEditMode ? parseInt(new URLSearchParams(window.location.search).get('id')) : Date.now(),
        createdAt: isEditMode ? new Date().toISOString() : new Date().toISOString(),
        updatedAt: new Date().toISOString(),
        bookingCount: isEditMode ? (services.find(s => s.id === parseInt(new URLSearchParams(window.location.search).get('id')))?.bookingCount || 0) : 0
      };

      if (isEditMode) {
        const index = services.findIndex(s => s.id === serviceData.id);
        if (index !== -1) {
          services[index] = serviceData;
        }
      } else {
        services.push(serviceData);
      }

      localStorage.setItem('medx360_services', JSON.stringify(services));
      
      console.log('Service data saved:', serviceData);
      
      // Show success message
      if (isEditMode) {
        toast.showUpdateSuccess('Service');
      } else {
        toast.showCreateSuccess('Service');
      }
      
      // Redirect to service list
      setTimeout(() => {
        window.location.href = getWordPressUrl(WORDPRESS_PAGES.SERVICE);
      }, 1000);
      
    } catch (error) {
      console.error('Error saving service:', error);
      toast.showUpdateError('Service', error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    if (window.confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
      window.location.href = getWordPressUrl(WORDPRESS_PAGES.SERVICE);
    }
  };

  const serviceIcons = [
    '🩺', '❤️', '👶', '🏃‍♂️', '🦷', '🧠', '🚨', '⚕️', '👁️', '🦴', '🧬', '💊'
  ];

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(price);
  };

  return (
    <div className="medx360-service-form">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>{isEditMode ? 'Edit Service' : 'Add New Service'}</h1>
            <p>{isEditMode ? 'Update service information' : 'Add a new service to your practice'}</p>
          </div>
          <div className="medx360-header-actions">
            <button
              type="button"
              onClick={handleCancel}
              className="medx360-btn medx360-btn-secondary"
            >
              ← Back to Service List
            </button>
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="medx360-form">
        <div className="medx360-form-sections">
          {/* Basic Information */}
          <div className="medx360-form-section">
            <h3>🩺 Basic Information</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Service Name *</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => handleInputChange('name', e.target.value)}
                  placeholder="General Consultation"
                  className={errors.name ? 'medx360-input-error' : ''}
                />
                {errors.name && <span className="medx360-error-message">{errors.name}</span>}
              </div>

              <div className="medx360-form-group">
                <label>Category *</label>
                <select
                  value={formData.category}
                  onChange={(e) => handleInputChange('category', e.target.value)}
                >
                  <option value="General">General</option>
                  <option value="Specialty">Specialty</option>
                  <option value="Pediatrics">Pediatrics</option>
                  <option value="Therapy">Therapy</option>
                  <option value="Dental">Dental</option>
                  <option value="Mental Health">Mental Health</option>
                  <option value="Emergency">Emergency</option>
                  <option value="Surgery">Surgery</option>
                </select>
              </div>

              <div className="medx360-form-group">
                <label>Duration (minutes) *</label>
                <input
                  type="number"
                  value={formData.duration}
                  onChange={(e) => handleInputChange('duration', parseInt(e.target.value) || 0)}
                  min="1"
                  max="480"
                  className={errors.duration ? 'medx360-input-error' : ''}
                />
                {errors.duration && <span className="medx360-error-message">{errors.duration}</span>}
              </div>

              <div className="medx360-form-group">
                <label>Price ($) *</label>
                <input
                  type="number"
                  value={formData.price}
                  onChange={(e) => handleInputChange('price', parseFloat(e.target.value) || 0)}
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  className={errors.price ? 'medx360-input-error' : ''}
                />
                {errors.price && <span className="medx360-error-message">{errors.price}</span>}
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

              <div className="medx360-form-group">
                <label>Icon</label>
                <div className="medx360-icon-selection">
                  {serviceIcons.map(icon => (
                    <button
                      key={icon}
                      type="button"
                      className={`medx360-icon-option ${formData.icon === icon ? 'selected' : ''}`}
                      onClick={() => handleInputChange('icon', icon)}
                    >
                      {icon}
                    </button>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* Description */}
          <div className="medx360-form-section">
            <h3>📝 Description</h3>
            <div className="medx360-form-group">
              <label>Service Description *</label>
              <textarea
                value={formData.description}
                onChange={(e) => handleInputChange('description', e.target.value)}
                placeholder="Describe what this service includes..."
                rows="4"
                className={errors.description ? 'medx360-input-error' : ''}
              />
              {errors.description && <span className="medx360-error-message">{errors.description}</span>}
            </div>
          </div>

          {/* Staff Assignment */}
          <div className="medx360-form-section">
            <h3>👥 Staff Assignment</h3>
            <div className="medx360-staff-selection">
              <p>Select staff members who can provide this service:</p>
              <div className="medx360-staff-grid">
                {availableStaff.map(staff => (
                  <label key={staff} className="medx360-staff-checkbox">
                    <input
                      type="checkbox"
                      checked={formData.staffAssigned.includes(staff)}
                      onChange={() => handleStaffToggle(staff)}
                    />
                    <span className="medx360-checkbox-label">👨‍⚕️ {staff}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>

          {/* Service Details */}
          <div className="medx360-form-section">
            <h3>📋 Service Details</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Requirements</label>
                <textarea
                  value={formData.requirements}
                  onChange={(e) => handleInputChange('requirements', e.target.value)}
                  placeholder="What patients need to bring or prepare..."
                  rows="3"
                />
              </div>

              <div className="medx360-form-group">
                <label>Preparation Instructions</label>
                <textarea
                  value={formData.preparationInstructions}
                  onChange={(e) => handleInputChange('preparationInstructions', e.target.value)}
                  placeholder="Instructions for patients before the service..."
                  rows="3"
                />
              </div>

              <div className="medx360-form-group medx360-full-width">
                <label>Follow-up Instructions</label>
                <textarea
                  value={formData.followUpInstructions}
                  onChange={(e) => handleInputChange('followUpInstructions', e.target.value)}
                  placeholder="Instructions for patients after the service..."
                  rows="3"
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
                placeholder="Any additional information about this service..."
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
                {isEditMode ? '✏️ Update Service' : '➕ Add Service'}
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ServiceForm;
