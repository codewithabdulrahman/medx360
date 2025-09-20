import React, { useState } from "react";
import { getWordPressUrl, WORDPRESS_PAGES } from "../../utils/wordpressUrls";
import "./SetupWizard.css";

const SetupWizard = () => {
  const [currentStep, setCurrentStep] = useState(1);
  const [formData, setFormData] = useState({
    // Step 1: Business/Practice Info
    practiceName: "",
    practiceType: "",
    businessEmail: "",
    businessPhone: "",
    address: "",
    timezone: "America/New_York",
    
    // Step 2: Services Setup
    services: [
      { name: "Consultation", duration: 30, fee: 100, description: "General consultation" }
    ],
    
    // Step 3: Staff/Practitioners
    practitioners: [
      { name: "", role: "Doctor", specialization: "", services: [], availability: {} }
    ],
    
    // Step 4: Booking Preferences
    slotInterval: 30,
    minNoticePeriod: 24,
    cancellationPolicy: 24,
    
    // Step 5: Notifications
    notifications: {
      email: true,
      sms: false,
      whatsapp: false
    },
    customTemplates: false,
    
    // Step 6: Payment
    acceptOnlinePayments: false,
    paymentGateway: "",
    
    // Step 7: Completion
    setupComplete: false
  });

  const totalSteps = 7;

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleNestedInputChange = (parent, field, value) => {
    setFormData(prev => ({
      ...prev,
      [parent]: {
        ...prev[parent],
        [field]: value
      }
    }));
  };

  const handleServiceChange = (index, field, value) => {
    const newServices = [...formData.services];
    newServices[index] = { ...newServices[index], [field]: value };
    setFormData(prev => ({
      ...prev,
      services: newServices
    }));
  };

  const addService = () => {
    setFormData(prev => ({
      ...prev,
      services: [...prev.services, { name: "", duration: 30, fee: 0, description: "" }]
    }));
  };

  const removeService = (index) => {
    if (formData.services.length > 1) {
      setFormData(prev => ({
        ...prev,
        services: prev.services.filter((_, i) => i !== index)
      }));
    }
  };

  const handlePractitionerChange = (index, field, value) => {
    const newPractitioners = [...formData.practitioners];
    newPractitioners[index] = { ...newPractitioners[index], [field]: value };
    setFormData(prev => ({
      ...prev,
      practitioners: newPractitioners
    }));
  };

  const addPractitioner = () => {
    setFormData(prev => ({
      ...prev,
      practitioners: [...prev.practitioners, { 
        name: "", 
        role: "Doctor", 
        specialization: "", 
        services: [], 
        availability: {} 
      }]
    }));
  };

  const removePractitioner = (index) => {
    if (formData.practitioners.length > 1) {
      setFormData(prev => ({
        ...prev,
        practitioners: prev.practitioners.filter((_, i) => i !== index)
      }));
    }
  };

  const nextStep = () => {
    if (currentStep < totalSteps) {
      setCurrentStep(currentStep + 1);
    }
  };

  const prevStep = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    }
  };

  const completeSetup = () => {
    // Save setup data (you will implement this)
    console.log("Setup completed:", formData);
    
    // Mark setup as complete in localStorage
    localStorage.setItem('medx360_setup_completed', 'true');
    localStorage.setItem('medx360_setup_data', JSON.stringify(formData));
    
    // Mark setup as complete
    setFormData(prev => ({ ...prev, setupComplete: true }));
    
    // Redirect to dashboard
    setTimeout(() => {
      window.location.href = getWordPressUrl(WORDPRESS_PAGES.DASHBOARD);
    }, 2000);
  };

  const testBooking = () => {
    // Open test booking form (you will implement this)
    window.open(getWordPressUrl("booking/new"), "_blank");
  };

  const renderStep1 = () => (
    <div className="medx360-setup-step">
      <div className="medx360-step-header">
        <h2>🏥 Business / Practice Info</h2>
        <p>Tell us about your medical practice</p>
      </div>
      
      <div className="medx360-form-grid">
        <div className="medx360-form-group">
          <label>Clinic / Practice Name *</label>
          <input
            type="text"
            value={formData.practiceName}
            onChange={(e) => handleInputChange("practiceName", e.target.value)}
            placeholder="e.g., Downtown Medical Center"
            required
          />
        </div>
        
        <div className="medx360-form-group">
          <label>Type of Practice *</label>
          <select
            value={formData.practiceType}
            onChange={(e) => handleInputChange("practiceType", e.target.value)}
            required
          >
            <option value="">Select Practice Type</option>
            <option value="doctor">Doctor / Physician</option>
            <option value="dentist">Dentist</option>
            <option value="therapist">Therapist</option>
            <option value="physiotherapist">Physiotherapist</option>
            <option value="psychologist">Psychologist</option>
            <option value="chiropractor">Chiropractor</option>
            <option value="other">Other</option>
          </select>
        </div>
        
        <div className="medx360-form-group">
          <label>Business Email *</label>
          <input
            type="email"
            value={formData.businessEmail}
            onChange={(e) => handleInputChange("businessEmail", e.target.value)}
            placeholder="info@yourpractice.com"
            required
          />
        </div>
        
        <div className="medx360-form-group">
          <label>Business Phone *</label>
          <input
            type="tel"
            value={formData.businessPhone}
            onChange={(e) => handleInputChange("businessPhone", e.target.value)}
            placeholder="+1 (555) 123-4567"
            required
          />
        </div>
        
        <div className="medx360-form-group medx360-full-width">
          <label>Address *</label>
          <textarea
            value={formData.address}
            onChange={(e) => handleInputChange("address", e.target.value)}
            placeholder="123 Main Street, City, State, ZIP Code"
            rows="3"
            required
          />
        </div>
        
        <div className="medx360-form-group">
          <label>Timezone *</label>
          <select
            value={formData.timezone}
            onChange={(e) => handleInputChange("timezone", e.target.value)}
            required
          >
            <option value="America/New_York">Eastern Time (ET)</option>
            <option value="America/Chicago">Central Time (CT)</option>
            <option value="America/Denver">Mountain Time (MT)</option>
            <option value="America/Los_Angeles">Pacific Time (PT)</option>
            <option value="Europe/London">London (GMT)</option>
            <option value="Europe/Paris">Paris (CET)</option>
            <option value="Asia/Tokyo">Tokyo (JST)</option>
          </select>
        </div>
      </div>
    </div>
  );

  const renderStep2 = () => (
    <div className="medx360-setup-step">
      <div className="medx360-step-header">
        <h2>🩺 Services Setup</h2>
        <p>What services do you offer to your patients?</p>
      </div>
      
      <div className="medx360-services-list">
        {formData.services.map((service, index) => (
          <div key={index} className="medx360-service-item">
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Service Name *</label>
                <input
                  type="text"
                  value={service.name}
                  onChange={(e) => handleServiceChange(index, "name", e.target.value)}
                  placeholder="e.g., Consultation, Therapy Session"
                  required
                />
              </div>
              
              <div className="medx360-form-group">
                <label>Duration (minutes) *</label>
                <select
                  value={service.duration}
                  onChange={(e) => handleServiceChange(index, "duration", parseInt(e.target.value))}
                  required
                >
                  <option value="15">15 minutes</option>
                  <option value="30">30 minutes</option>
                  <option value="45">45 minutes</option>
                  <option value="60">60 minutes</option>
                  <option value="90">90 minutes</option>
                  <option value="120">120 minutes</option>
                </select>
              </div>
              
              <div className="medx360-form-group">
                <label>Fee ($)</label>
                <input
                  type="number"
                  value={service.fee}
                  onChange={(e) => handleServiceChange(index, "fee", parseFloat(e.target.value) || 0)}
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                />
              </div>
              
              <div className="medx360-form-group medx360-full-width">
                <label>Description</label>
                <input
                  type="text"
                  value={service.description}
                  onChange={(e) => handleServiceChange(index, "description", e.target.value)}
                  placeholder="Brief description of this service"
                />
              </div>
            </div>
            
            {formData.services.length > 1 && (
              <button
                type="button"
                className="medx360-remove-btn"
                onClick={() => removeService(index)}
              >
                Remove Service
              </button>
            )}
          </div>
        ))}
        
        <button type="button" className="medx360-add-btn" onClick={addService}>
          + Add Another Service
        </button>
      </div>
    </div>
  );

  const renderStep3 = () => (
    <div className="medx360-setup-step">
      <div className="medx360-step-header">
        <h2>👥 Staff / Practitioners</h2>
        <p>Add your team members and their specializations</p>
      </div>
      
      <div className="medx360-practitioners-list">
        {formData.practitioners.map((practitioner, index) => (
          <div key={index} className="medx360-practitioner-item">
            <div className="medx360-form-grid">
              <div className="medx360-form-group">
                <label>Name *</label>
                <input
                  type="text"
                  value={practitioner.name}
                  onChange={(e) => handlePractitionerChange(index, "name", e.target.value)}
                  placeholder="Dr. John Smith"
                  required
                />
              </div>
              
              <div className="medx360-form-group">
                <label>Role *</label>
                <select
                  value={practitioner.role}
                  onChange={(e) => handlePractitionerChange(index, "role", e.target.value)}
                  required
                >
                  <option value="doctor">Doctor</option>
                  <option value="nurse">Nurse</option>
                  <option value="therapist">Therapist</option>
                  <option value="specialist">Specialist</option>
                  <option value="assistant">Assistant</option>
                </select>
              </div>
              
              <div className="medx360-form-group">
                <label>Specialization</label>
                <input
                  type="text"
                  value={practitioner.specialization}
                  onChange={(e) => handlePractitionerChange(index, "specialization", e.target.value)}
                  placeholder="e.g., Cardiology, Pediatrics"
                />
              </div>
            </div>
            
            {formData.practitioners.length > 1 && (
              <button
                type="button"
                className="medx360-remove-btn"
                onClick={() => removePractitioner(index)}
              >
                Remove Practitioner
              </button>
            )}
          </div>
        ))}
        
        <button type="button" className="medx360-add-btn" onClick={addPractitioner}>
          + Add Another Practitioner
        </button>
      </div>
    </div>
  );

  const renderStep4 = () => (
    <div className="medx360-setup-step">
      <div className="medx360-step-header">
        <h2>📅 Booking Preferences</h2>
        <p>Configure how patients can book appointments</p>
      </div>
      
      <div className="medx360-form-grid">
        <div className="medx360-form-group">
          <label>Appointment Slot Interval *</label>
          <select
            value={formData.slotInterval}
            onChange={(e) => handleInputChange("slotInterval", parseInt(e.target.value))}
            required
          >
            <option value="15">15 minutes</option>
            <option value="30">30 minutes</option>
            <option value="60">60 minutes</option>
          </select>
        </div>
        
        <div className="medx360-form-group">
          <label>Minimum Notice Period *</label>
          <select
            value={formData.minNoticePeriod}
            onChange={(e) => handleInputChange("minNoticePeriod", parseInt(e.target.value))}
            required
          >
            <option value="1">1 hour</option>
            <option value="24">24 hours</option>
            <option value="48">48 hours</option>
            <option value="72">72 hours</option>
          </select>
        </div>
        
        <div className="medx360-form-group">
          <label>Cancellation Policy</label>
          <select
            value={formData.cancellationPolicy}
            onChange={(e) => handleInputChange("cancellationPolicy", parseInt(e.target.value))}
          >
            <option value="0">No cancellation policy</option>
            <option value="24">24 hours notice required</option>
            <option value="48">48 hours notice required</option>
            <option value="72">72 hours notice required</option>
          </select>
        </div>
      </div>
    </div>
  );

  const renderStep5 = () => (
    <div className="medx360-setup-step">
      <div className="medx360-step-header">
        <h2>🔔 Notifications</h2>
        <p>How would you like to remind patients about appointments?</p>
      </div>
      
      <div className="medx360-notifications-section">
        <div className="medx360-notification-options">
          <label className="medx360-checkbox-label">
            <input
              type="checkbox"
              checked={formData.notifications.email}
              onChange={(e) => handleNestedInputChange("notifications", "email", e.target.checked)}
            />
            <span>📧 Email Reminders</span>
          </label>
          
          <label className="medx360-checkbox-label">
            <input
              type="checkbox"
              checked={formData.notifications.sms}
              onChange={(e) => handleNestedInputChange("notifications", "sms", e.target.checked)}
            />
            <span>📱 SMS Reminders</span>
          </label>
          
          <label className="medx360-checkbox-label">
            <input
              type="checkbox"
              checked={formData.notifications.whatsapp}
              onChange={(e) => handleNestedInputChange("notifications", "whatsapp", e.target.checked)}
            />
            <span>💬 WhatsApp Reminders</span>
          </label>
        </div>
        
        <div className="medx360-form-group">
          <label className="medx360-checkbox-label">
            <input
              type="checkbox"
              checked={formData.customTemplates}
              onChange={(e) => handleInputChange("customTemplates", e.target.checked)}
            />
            <span>Customize message templates</span>
          </label>
        </div>
      </div>
    </div>
  );

  const renderStep6 = () => (
    <div className="medx360-setup-step">
      <div className="medx360-step-header">
        <h2>💳 Payment (Optional)</h2>
        <p>Will you accept online payments from patients?</p>
      </div>
      
      <div className="medx360-payment-section">
        <div className="medx360-form-group">
          <label className="medx360-radio-label">
            <input
              type="radio"
              name="paymentMethod"
              checked={!formData.acceptOnlinePayments}
              onChange={() => handleInputChange("acceptOnlinePayments", false)}
            />
            <span>💵 Cash in person only</span>
          </label>
        </div>
        
        <div className="medx360-form-group">
          <label className="medx360-radio-label">
            <input
              type="radio"
              name="paymentMethod"
              checked={formData.acceptOnlinePayments}
              onChange={() => handleInputChange("acceptOnlinePayments", true)}
            />
            <span>💳 Accept online payments</span>
          </label>
        </div>
        
        {formData.acceptOnlinePayments && (
          <div className="medx360-form-group">
            <label>Payment Gateway</label>
            <select
              value={formData.paymentGateway}
              onChange={(e) => handleInputChange("paymentGateway", e.target.value)}
            >
              <option value="">Select Payment Gateway</option>
              <option value="stripe">Stripe</option>
              <option value="paypal">PayPal</option>
              <option value="square">Square</option>
              <option value="other">Other</option>
            </select>
          </div>
        )}
      </div>
    </div>
  );

  const renderStep7 = () => (
    <div className="medx360-setup-step">
      <div className="medx360-step-header">
        <h2>🎉 Finish & Quick Test</h2>
        <p>You are all set! Let us test your booking system</p>
      </div>
      
      <div className="medx360-completion-section">
        <div className="medx360-summary">
          <h3>Setup Summary</h3>
          <ul>
            <li>✅ Practice: {formData.practiceName}</li>
            <li>✅ Services: {formData.services.length} service(s)</li>
            <li>✅ Practitioners: {formData.practitioners.length} team member(s)</li>
            <li>✅ Notifications: {Object.values(formData.notifications).filter(Boolean).length} method(s)</li>
            <li>✅ Payment: {formData.acceptOnlinePayments ? "Online" : "Cash only"}</li>
          </ul>
        </div>
        
        <div className="medx360-test-actions">
          <button
            type="button"
            className="medx360-btn medx360-btn-secondary"
            onClick={testBooking}
          >
            🧪 Test Booking Form
          </button>
          
          <button
            type="button"
            className="medx360-btn medx360-btn-primary"
            onClick={completeSetup}
          >
            🚀 Go to Dashboard
          </button>
        </div>
      </div>
    </div>
  );

  const renderCurrentStep = () => {
    switch (currentStep) {
      case 1: return renderStep1();
      case 2: return renderStep2();
      case 3: return renderStep3();
      case 4: return renderStep4();
      case 5: return renderStep5();
      case 6: return renderStep6();
      case 7: return renderStep7();
      default: return renderStep1();
    }
  };

  const isStepValid = () => {
    switch (currentStep) {
      case 1:
        return formData.practiceName && formData.practiceType && formData.businessEmail && formData.businessPhone && formData.address;
      case 2:
        return formData.services.every(service => service.name && service.duration);
      case 3:
        return formData.practitioners.every(practitioner => practitioner.name && practitioner.role);
      case 4:
        return true;
      case 5:
        return true;
      case 6:
        return true;
      case 7:
        return true;
      default:
        return false;
    }
  };

  const getStepTitle = (step) => {
    const titles = [
      "Business Info",
      "Services",
      "Staff",
      "Booking",
      "Notifications",
      "Payment",
      "Finish"
    ];
    return titles[step - 1] || "Setup";
  };

  return (
    <div className="medx360-setup-wizard">
      <div className="medx360-wizard-container">
        <div className="medx360-wizard-header">
          <h1>🩺 Medx360 First-Time Setup</h1>
          <p>Let us get your practice up and running in just a few steps</p>
        </div>
        
        <div className="medx360-progress-bar">
          <div className="medx360-progress-steps">
            {Array.from({ length: totalSteps }, (_, i) => (
              <div
                key={i + 1}
                className={`medx360-step ${i + 1 <= currentStep ? "active" : ""} ${i + 1 < currentStep ? "completed" : ""}`}
              >
                <div className="medx360-step-number">{i + 1}</div>
                <div className="medx360-step-label">{getStepTitle(i + 1)}</div>
              </div>
            ))}
          </div>
        </div>
        
        <div className="medx360-wizard-content">
          {renderCurrentStep()}
        </div>
        
        <div className="medx360-wizard-footer">
          <div className="medx360-wizard-buttons">
            {currentStep > 1 && (
              <button
                type="button"
                className="medx360-btn medx360-btn-secondary"
                onClick={prevStep}
              >
                ← Previous
              </button>
            )}
            
            {currentStep < totalSteps ? (
              <button
                type="button"
                className="medx360-btn medx360-btn-primary"
                onClick={nextStep}
                disabled={!isStepValid()}
              >
                Next Step →
              </button>
            ) : (
              <div className="medx360-completion-buttons">
                <button
                  type="button"
                  className="medx360-btn medx360-btn-secondary"
                  onClick={testBooking}
                >
                  🧪 Test Booking
                </button>
                <button
                  type="button"
                  className="medx360-btn medx360-btn-primary"
                  onClick={completeSetup}
                >
                  🚀 Complete Setup
                </button>
              </div>
            )}
          </div>
          
          <div className="medx360-skip-setup">
            <button
              type="button"
              className="medx360-skip-btn"
              onClick={() => window.location.href = getWordPressUrl(WORDPRESS_PAGES.DASHBOARD)}
            >
              Skip Setup for Now
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SetupWizard;
