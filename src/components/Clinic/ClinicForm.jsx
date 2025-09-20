import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import { useToastNotifications } from '../../utils/toast';
import './ClinicForm.css';

const ClinicForm = () => {
  const toast = useToastNotifications();
  
  const [formData, setFormData] = useState({
    name: '',
    type: 'General Practice',
    address: '',
    phone: '',
    email: '',
    website: '',
    licenseNumber: '',
    status: 'active',
    establishedDate: new Date().toISOString().split('T')[0],
    services: [],
    notes: '',
    operatingHours: {
      monday: { open: '09:00', close: '17:00', closed: false },
      tuesday: { open: '09:00', close: '17:00', closed: false },
      wednesday: { open: '09:00', close: '17:00', closed: false },
      thursday: { open: '09:00', close: '17:00', closed: false },
      friday: { open: '09:00', close: '17:00', closed: false },
      saturday: { open: '09:00', close: '13:00', closed: false },
      sunday: { open: '09:00', close: '17:00', closed: true }
    }
  });

  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const [isEditMode, setIsEditMode] = useState(false);
  const [availableServices, setAvailableServices] = useState([]);

  // Check if we're editing an existing clinic
  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const clinicId = urlParams.get('id');
    
    if (clinicId) {
      setIsEditMode(true);
      // Load existing clinic data
      loadClinicData(clinicId);
    }
    
    // Load available services
    loadAvailableServices();
  }, []);

  const loadClinicData = (clinicId) => {
    // In a real app, this would fetch from API
    // For now, we'll simulate loading existing data
    const existingClinic = {
      name: 'Downtown Medical Center',
      type: 'General Practice',
      address: '123 Main Street, Downtown, NY 10001',
      phone: '+1 (555) 123-4567',
      email: 'info@downtownmedical.com',
      website: 'https://downtownmedical.com',
      licenseNumber: 'CL001',
      status: 'active',
      establishedDate: '2020-01-15',
      services: ['General Consultation', 'Cardiology', 'Pediatrics'],
      notes: 'Main clinic location with full services'
    };
    
    setFormData(prev => ({
      ...prev,
      ...existingClinic
    }));
  };

  const loadAvailableServices = () => {
    // In a real app, this would fetch from API
    const services = [
      'General Consultation',
      'Cardiology Consultation',
      'Pediatric Check-up',
      'Physical Therapy Session',
      'Dental Cleaning',
      'Mental Health Counseling',
      'Emergency Care',
      'Surgery Consultation'
    ];
    setAvailableServices(services);
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

  const handleOperatingHoursChange = (day, field, value) => {
    setFormData(prev => ({
      ...prev,
      operatingHours: {
        ...prev.operatingHours,
        [day]: {
          ...prev.operatingHours[day],
          [field]: value
        }
      }
    }));
  };

  const handleServiceToggle = (service) => {
    setFormData(prev => ({
      ...prev,
      services: prev.services.includes(service)
        ? prev.services.filter(s => s !== service)
        : [...prev.services, service]
    }));
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Clinic name is required';
    }

    if (!formData.address.trim()) {
      newErrors.address = 'Address is required';
    }

    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid';
    }

    if (!formData.licenseNumber.trim()) {
      newErrors.licenseNumber = 'License number is required';
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
      const clinics = JSON.parse(localStorage.getItem('medx360_clinics') || '[]');
      const clinicData = {
        ...formData,
        id: isEditMode ? parseInt(new URLSearchParams(window.location.search).get('id')) : Date.now(),
        createdAt: isEditMode ? new Date().toISOString() : new Date().toISOString(),
        updatedAt: new Date().toISOString()
      };

      if (isEditMode) {
        const index = clinics.findIndex(c => c.id === clinicData.id);
        if (index !== -1) {
          clinics[index] = clinicData;
        }
      } else {
        clinics.push(clinicData);
      }

      localStorage.setItem('medx360_clinics', JSON.stringify(clinics));
      
      console.log('Clinic data saved:', clinicData);
      
      // Show success message
      if (isEditMode) {
        toast.showUpdateSuccess('Clinic');
      } else {
        toast.showCreateSuccess('Clinic');
      }
      
      // Redirect to clinic list
      setTimeout(() => {
        window.location.href = getWordPressUrl(WORDPRESS_PAGES.CLINIC);
      }, 1000);
      
    } catch (error) {
      console.error('Error saving clinic:', error);
      toast.showUpdateError('Clinic', error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    if (window.confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
      window.location.href = getWordPressUrl(WORDPRESS_PAGES.CLINIC);
    }
  };

  const days = [
    { key: 'monday', label: 'Monday' },
    { key: 'tuesday', label: 'Tuesday' },
    { key: 'wednesday', label: 'Wednesday' },
    { key: 'thursday', label: 'Thursday' },
    { key: 'friday', label: 'Friday' },
    { key: 'saturday', label: 'Saturday' },
    { key: 'sunday', label: 'Sunday' }
  ];

  return (
    <div className="medx360-clinic-form">
      <div className="medx360-page-header">
        <div className="medx360-header-content">
          <div>
            <h1>{isEditMode ? 'Edit Clinic' : 'Add New Clinic'}</h1>
            <p>{isEditMode ? 'Update clinic information' : 'Add a new clinic location to your practice'}</p>
          </div>
          <div className="medx360-header-actions">
            <button
              type="button"
              onClick={handleCancel}
              className="medx360-btn medx360-btn-secondary"
            >
              ← Back to Clinic List
            </button>
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="medx360-form">
        <div className="medx360-form-sections">
          {/* Basic Information */}
          <div className="medx360-form-section">
            <h3>🏥 Basic Information</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Clinic Name *</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => handleInputChange('name', e.target.value)}
                  placeholder="Downtown Medical Center"
                  className={errors.name ? 'medx360-input-error' : ''}
                />
                {errors.name && <span className="medx360-error-message">{errors.name}</span>}
              </div>

              <div className="medx360-form-group">
                <label>Clinic Type *</label>
                <select
                  value={formData.type}
                  onChange={(e) => handleInputChange('type', e.target.value)}
                >
                  <option value="General Practice">General Practice</option>
                  <option value="Family Practice">Family Practice</option>
                  <option value="Specialty Practice">Specialty Practice</option>
                  <option value="Dental Practice">Dental Practice</option>
                  <option value="Mental Health">Mental Health</option>
                  <option value="Emergency Care">Emergency Care</option>
                </select>
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
                <label>Established Date</label>
                <input
                  type="date"
                  value={formData.establishedDate}
                  onChange={(e) => handleInputChange('establishedDate', e.target.value)}
                />
              </div>
            </div>
          </div>

          {/* Contact Information */}
          <div className="medx360-form-section">
            <h3>📞 Contact Information</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group medx360-full-width">
                <label>Address *</label>
                <textarea
                  value={formData.address}
                  onChange={(e) => handleInputChange('address', e.target.value)}
                  placeholder="123 Main Street, City, State, ZIP Code"
                  rows="3"
                  className={errors.address ? 'medx360-input-error' : ''}
                />
                {errors.address && <span className="medx360-error-message">{errors.address}</span>}
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

              <div className="medx360-form-group">
                <label>Email Address *</label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => handleInputChange('email', e.target.value)}
                  placeholder="info@clinic.com"
                  className={errors.email ? 'medx360-input-error' : ''}
                />
                {errors.email && <span className="medx360-error-message">{errors.email}</span>}
              </div>

              <div className="medx360-form-group">
                <label>Website</label>
                <input
                  type="url"
                  value={formData.website}
                  onChange={(e) => handleInputChange('website', e.target.value)}
                  placeholder="https://clinic.com"
                />
              </div>
            </div>
          </div>

          {/* Professional Information */}
          <div className="medx360-form-section">
            <h3>📋 Professional Information</h3>
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>License Number *</label>
                <input
                  type="text"
                  value={formData.licenseNumber}
                  onChange={(e) => handleInputChange('licenseNumber', e.target.value)}
                  placeholder="CL001"
                  className={errors.licenseNumber ? 'medx360-input-error' : ''}
                />
                {errors.licenseNumber && <span className="medx360-error-message">{errors.licenseNumber}</span>}
              </div>
            </div>
          </div>

          {/* Services Offered */}
          <div className="medx360-form-section">
            <h3>🩺 Services Offered</h3>
            <div className="medx360-services-selection">
              <p>Select the services offered at this clinic:</p>
              <div className="medx360-services-grid">
                {availableServices.map(service => (
                  <label key={service} className="medx360-service-checkbox">
                    <input
                      type="checkbox"
                      checked={formData.services.includes(service)}
                      onChange={() => handleServiceToggle(service)}
                    />
                    <span className="medx360-checkbox-label">{service}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>

          {/* Operating Hours */}
          <div className="medx360-form-section">
            <h3>🕒 Operating Hours</h3>
            <div className="medx360-operating-hours">
              {days.map(day => (
                <div key={day.key} className="medx360-hours-row">
                  <div className="medx360-day-label">
                    <label className="medx360-checkbox-label">
                      <input
                        type="checkbox"
                        checked={!formData.operatingHours[day.key].closed}
                        onChange={(e) => handleOperatingHoursChange(day.key, 'closed', !e.target.checked)}
                      />
                      <span>{day.label}</span>
                    </label>
                  </div>
                  <div className="medx360-hours-inputs">
                    <input
                      type="time"
                      value={formData.operatingHours[day.key].open}
                      onChange={(e) => handleOperatingHoursChange(day.key, 'open', e.target.value)}
                      disabled={formData.operatingHours[day.key].closed}
                    />
                    <span>to</span>
                    <input
                      type="time"
                      value={formData.operatingHours[day.key].close}
                      onChange={(e) => handleOperatingHoursChange(day.key, 'close', e.target.value)}
                      disabled={formData.operatingHours[day.key].closed}
                    />
                  </div>
                </div>
              ))}
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
                placeholder="Any additional information about this clinic..."
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
                {isEditMode ? '✏️ Update Clinic' : '➕ Add Clinic'}
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ClinicForm;
