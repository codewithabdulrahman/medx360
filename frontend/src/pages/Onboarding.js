import React, { useState, useEffect } from 'react';
import { 
  CheckCircle, 
  ArrowRight, 
  ArrowLeft, 
  Building2, 
  UserCheck, 
  Stethoscope, 
  Calendar,
  CreditCard,
  Users,
  FileText,
  Settings
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { 
  useSetupStatus, 
  useOnboardingSteps, 
  useOnboardingProgress,
  useCreateDefaultClinic,
  useCreateDefaultServices,
  useCompleteOnboarding 
} from '@hooks/useApi';
import { 
  FormInput, 
  FormButton, 
  FormCard, 
  FormLoading, 
  FormStatus,
  FormSelect,
  FormTextarea 
} from '@components/forms';

const StepIndicator = ({ currentStep, totalSteps, steps }) => {
  return (
    <div className="flex items-center justify-center space-x-4 mb-8">
      {steps.map((step, index) => (
        <div key={index} className="flex items-center">
          <div className={`flex items-center justify-center w-8 h-8 rounded-full ${
            index < currentStep 
              ? 'bg-green-500 text-white' 
              : index === currentStep 
                ? 'bg-blue-500 text-white' 
                : 'bg-gray-300 text-gray-600'
          }`}>
            {index < currentStep ? (
              <CheckCircle className="h-5 w-5" />
            ) : (
              <span className="text-sm font-medium">{index + 1}</span>
            )}
          </div>
          <span className={`ml-2 text-sm font-medium ${
            index <= currentStep ? 'text-gray-900' : 'text-gray-500'
          }`}>
            {step.title}
          </span>
          {index < totalSteps - 1 && (
            <div className={`w-8 h-0.5 mx-4 ${
              index < currentStep ? 'bg-green-500' : 'bg-gray-300'
            }`} />
          )}
        </div>
      ))}
    </div>
  );
};

const WelcomeStep = ({ onNext }) => {
  return (
    <div className="text-center">
      <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-6">
        <Building2 className="h-6 w-6 text-blue-600" />
      </div>
      <h2 className="text-3xl font-bold text-gray-900 mb-4">
        Welcome to MedX360
      </h2>
      <p className="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
        Let's get your medical practice set up in just a few steps. 
        We'll help you create your first clinic, add doctors, and configure services.
      </p>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-green-100 mb-3">
            <Building2 className="h-5 w-5 text-green-600" />
          </div>
          <h3 className="text-sm font-medium text-gray-900">Create Clinic</h3>
          <p className="text-sm text-gray-500">Set up your medical facility</p>
        </div>
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-purple-100 mb-3">
            <UserCheck className="h-5 w-5 text-purple-600" />
          </div>
          <h3 className="text-sm font-medium text-gray-900">Add Doctors</h3>
          <p className="text-sm text-gray-500">Register medical professionals</p>
        </div>
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 mb-3">
            <Stethoscope className="h-5 w-5 text-yellow-600" />
          </div>
          <h3 className="text-sm font-medium text-gray-900">Configure Services</h3>
          <p className="text-sm text-gray-500">Define medical services</p>
        </div>
      </div>
      <FormButton onClick={onNext} size="lg">
        Get Started
        <ArrowRight className="h-5 w-5 ml-2" />
      </FormButton>
    </div>
  );
};

const ClinicSetupStep = ({ onNext, onPrevious }) => {
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    address: '',
    city: '',
    state: '',
    country: 'USA',
    postal_code: '',
    phone: '',
    email: '',
    website: '',
  });

  const [errors, setErrors] = useState({});
  const createClinicMutation = useCreateDefaultClinic();

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.name.trim()) {
      newErrors.name = 'Clinic name is required';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required';
    }
    
    if (!formData.address.trim()) {
      newErrors.address = 'Address is required';
    }
    
    if (!formData.city.trim()) {
      newErrors.city = 'City is required';
    }
    
    if (!formData.state.trim()) {
      newErrors.state = 'State is required';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (validateForm()) {
      try {
        await createClinicMutation.mutateAsync(formData);
        onNext();
      } catch (error) {
        console.error('Failed to create clinic:', error);
        
        // Error handling - validation errors are already logged
      }
    }
  };

  const handleChange = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    if (errors[field]) {
      setErrors(prev => ({ ...prev, [field]: null }));
    }
  };

  return (
    <div className="max-w-2xl mx-auto">
      <div className="text-center mb-8">
        <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <Building2 className="h-6 w-6 text-green-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900 mb-2">
          Create Your First Clinic
        </h2>
        <p className="text-gray-600">
          Let's start by setting up your main medical facility
        </p>
      </div>

      <FormCard>
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormInput
              label="Clinic Name"
              value={formData.name}
              onChange={(e) => handleChange('name', e.target.value)}
              error={errors.name}
              required
              placeholder="Downtown Medical Center"
            />
            
            <FormInput
              label="Email"
              type="email"
              value={formData.email}
              onChange={(e) => handleChange('email', e.target.value)}
              error={errors.email}
              required
              placeholder="info@clinic.com"
            />
            
            <FormInput
              label="Phone"
              value={formData.phone}
              onChange={(e) => handleChange('phone', e.target.value)}
              error={errors.phone}
              required
              placeholder="+1 (555) 123-4567"
            />
            
            <FormInput
              label="Website"
              value={formData.website}
              onChange={(e) => handleChange('website', e.target.value)}
              error={errors.website}
              placeholder="https://clinic.com"
            />
            
            <FormInput
              label="Address"
              value={formData.address}
              onChange={(e) => handleChange('address', e.target.value)}
              error={errors.address}
              required
              placeholder="123 Medical Street"
            />
            
            <FormInput
              label="City"
              value={formData.city}
              onChange={(e) => handleChange('city', e.target.value)}
              error={errors.city}
              required
              placeholder="Medical City"
            />
            
            <FormInput
              label="State"
              value={formData.state}
              onChange={(e) => handleChange('state', e.target.value)}
              error={errors.state}
              required
              placeholder="MC"
            />
            
            <FormInput
              label="Postal Code"
              value={formData.postal_code}
              onChange={(e) => handleChange('postal_code', e.target.value)}
              error={errors.postal_code}
              placeholder="12345"
            />
          </div>
          
          <FormTextarea
            label="Description"
            value={formData.description}
            onChange={(e) => handleChange('description', e.target.value)}
            error={errors.description}
            placeholder="Brief description of your clinic and services"
          />

          <div className="flex items-center justify-between pt-6 border-t">
            <FormButton
              type="button"
              variant="outline"
              onClick={onPrevious}
            >
              <ArrowLeft className="h-4 w-4 mr-2" />
              Back
            </FormButton>
            <FormButton 
              type="submit"
              loading={createClinicMutation.isPending}
            >
              Create Clinic
              <ArrowRight className="h-4 w-4 ml-2" />
            </FormButton>
          </div>
        </form>
      </FormCard>
    </div>
  );
};

const ServicesSetupStep = ({ onNext, onPrevious }) => {
  const [services, setServices] = useState([
    { name: 'General Consultation', description: 'General medical consultation', duration: 30, price: 100 },
    { name: 'Follow-up Visit', description: 'Follow-up appointment', duration: 15, price: 50 },
    { name: 'Emergency Consultation', description: 'Urgent medical consultation', duration: 45, price: 150 },
  ]);

  const createServicesMutation = useCreateDefaultServices();

  const handleServiceChange = (index, field, value) => {
    setServices(prev => prev.map((service, i) => 
      i === index ? { ...service, [field]: value } : service
    ));
  };

  const addService = () => {
    setServices(prev => [...prev, { name: '', description: '', duration: 30, price: 0 }]);
  };

  const removeService = (index) => {
    setServices(prev => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await createServicesMutation.mutateAsync({ services });
      onNext();
    } catch (error) {
      console.error('Failed to create services:', error);
      
      // Error handling - validation errors are already logged
    }
  };

  return (
    <div className="max-w-4xl mx-auto">
      <div className="text-center mb-8">
        <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
          <Stethoscope className="h-6 w-6 text-yellow-600" />
        </div>
        <h2 className="text-2xl font-bold text-gray-900 mb-2">
          Configure Medical Services
        </h2>
        <p className="text-gray-600">
          Set up the medical services you offer to patients
        </p>
      </div>

      <FormCard>
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="space-y-4">
            {services.map((service, index) => (
              <div key={index} className="border border-gray-200 rounded-lg p-4">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-lg font-medium text-gray-900">
                    Service {index + 1}
                  </h3>
                  {services.length > 1 && (
                    <button
                      type="button"
                      onClick={() => removeService(index)}
                      className="text-red-600 hover:text-red-800"
                    >
                      Remove
                    </button>
                  )}
                </div>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <FormInput
                    label="Service Name"
                    value={service.name}
                    onChange={(e) => handleServiceChange(index, 'name', e.target.value)}
                    required
                  />
                  
                  <FormInput
                    label="Duration (minutes)"
                    type="number"
                    value={service.duration}
                    onChange={(e) => handleServiceChange(index, 'duration', parseInt(e.target.value))}
                    required
                  />
                  
                  <FormInput
                    label="Price ($)"
                    type="number"
                    step="0.01"
                    value={service.price}
                    onChange={(e) => handleServiceChange(index, 'price', parseFloat(e.target.value))}
                    required
                  />
                </div>
                
                <FormTextarea
                  label="Description"
                  value={service.description}
                  onChange={(e) => handleServiceChange(index, 'description', e.target.value)}
                  rows={2}
                />
              </div>
            ))}
          </div>

          <div className="flex justify-center">
            <FormButton
              type="button"
              variant="outline"
              onClick={addService}
            >
              <Stethoscope className="h-4 w-4 mr-2" />
              Add Another Service
            </FormButton>
          </div>

          <div className="flex items-center justify-between pt-6 border-t">
            <FormButton
              type="button"
              variant="outline"
              onClick={onPrevious}
            >
              <ArrowLeft className="h-4 w-4 mr-2" />
              Back
            </FormButton>
            <FormButton 
              type="submit"
              loading={createServicesMutation.isPending}
            >
              Create Services
              <ArrowRight className="h-4 w-4 ml-2" />
            </FormButton>
          </div>
        </form>
      </FormCard>
    </div>
  );
};

const CompletionStep = ({ onComplete }) => {
  const completeOnboardingMutation = useCompleteOnboarding();

  const handleComplete = async () => {
    try {
      await completeOnboardingMutation.mutateAsync();
      onComplete();
    } catch (error) {
      console.error('Failed to complete onboarding:', error);
      
      // Error handling - validation errors are already logged
    }
  };

  return (
    <div className="text-center">
      <div className="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
        <CheckCircle className="h-8 w-8 text-green-600" />
      </div>
      <h2 className="text-3xl font-bold text-gray-900 mb-4">
        Setup Complete!
      </h2>
      <p className="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
        Congratulations! Your MedX360 medical practice is now set up and ready to use. 
        You can start managing appointments, patients, and medical records right away.
      </p>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 mb-3">
            <Calendar className="h-5 w-5 text-blue-600" />
          </div>
          <h3 className="text-sm font-medium text-gray-900">Book Appointments</h3>
          <p className="text-sm text-gray-500">Schedule patient visits</p>
        </div>
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-green-100 mb-3">
            <UserCheck className="h-5 w-5 text-green-600" />
          </div>
          <h3 className="text-sm font-medium text-gray-900">Manage Doctors</h3>
          <p className="text-sm text-gray-500">Add medical professionals</p>
        </div>
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-purple-100 mb-3">
            <FileText className="h-5 w-5 text-purple-600" />
          </div>
          <h3 className="text-sm font-medium text-gray-900">Track Consultations</h3>
          <p className="text-sm text-gray-500">Record patient visits</p>
        </div>
        <div className="text-center">
          <div className="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 mb-3">
            <CreditCard className="h-5 w-5 text-yellow-600" />
          </div>
          <h3 className="text-sm font-medium text-gray-900">Process Payments</h3>
          <p className="text-sm text-gray-500">Handle billing</p>
        </div>
      </div>

      <FormButton 
        onClick={handleComplete}
        size="lg"
        loading={completeOnboardingMutation.isPending}
      >
        Go to Dashboard
        <ArrowRight className="h-5 w-5 ml-2" />
      </FormButton>
    </div>
  );
};

const Onboarding = () => {
  const navigate = useNavigate();
  const [currentStep, setCurrentStep] = useState(0);

  const { data: setupStatus, isLoading } = useSetupStatus();
  const { data: steps } = useOnboardingSteps();

  useEffect(() => {
    if (setupStatus?.is_completed) {
      navigate('/dashboard');
    }
  }, [setupStatus, navigate]);

  const onboardingSteps = [
    { title: 'Welcome', component: WelcomeStep },
    { title: 'Clinic Setup', component: ClinicSetupStep },
    { title: 'Services', component: ServicesSetupStep },
    { title: 'Complete', component: CompletionStep },
  ];

  const handleNext = () => {
    if (currentStep < onboardingSteps.length - 1) {
      setCurrentStep(currentStep + 1);
    }
  };

  const handlePrevious = () => {
    if (currentStep > 0) {
      setCurrentStep(currentStep - 1);
    }
  };

  const handleComplete = () => {
    navigate('/dashboard');
  };

  if (isLoading) {
    return <FormLoading message="Loading setup..." />;
  }

  const CurrentStepComponent = onboardingSteps[currentStep].component;

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        <StepIndicator 
          currentStep={currentStep}
          totalSteps={onboardingSteps.length}
          steps={onboardingSteps}
        />
        
        <div className="bg-white shadow-xl rounded-lg p-8">
          <CurrentStepComponent
            onNext={handleNext}
            onPrevious={handlePrevious}
            onComplete={handleComplete}
          />
        </div>
      </div>
    </div>
  );
};

export default Onboarding;