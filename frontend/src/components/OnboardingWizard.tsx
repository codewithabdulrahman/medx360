import React, { useState, useEffect } from 'react';
import { CheckCircle, Circle, ArrowRight, ArrowLeft, Building2, Stethoscope, Users, Calendar } from 'lucide-react';
import { useApp, appActions } from '../contexts/AppContext';
import { useOnboardingStatus, useCreateDefaultClinic, useCreateDefaultServices, useCompleteOnboarding } from '../hooks/useApi';
import { CreateClinicData } from '../types';
import { cn } from '../utils';

const OnboardingWizard: React.FC = () => {
  const { state, dispatch } = useApp();
  const { data: onboardingStatus, isLoading } = useOnboardingStatus();
  const createDefaultClinic = useCreateDefaultClinic();
  const createDefaultServices = useCreateDefaultServices();
  const completeOnboarding = useCompleteOnboarding();

  const [currentStep, setCurrentStep] = useState(0);
  const [clinicData, setClinicData] = useState<CreateClinicData>({
    name: '',
    description: '',
    address: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    phone: '',
    email: '',
    website: '',
  });

  const steps = [
    {
      id: 'welcome',
      title: 'Welcome to MedX360',
      description: 'Let\'s set up your medical booking system',
      icon: CheckCircle,
      component: WelcomeStep,
    },
    {
      id: 'clinic',
      title: 'Create Your Clinic',
      description: 'Set up your main clinic information',
      icon: Building2,
      component: ClinicStep,
    },
    {
      id: 'services',
      title: 'Add Services',
      description: 'Create your medical services',
      icon: Stethoscope,
      component: ServicesStep,
    },
    {
      id: 'staff',
      title: 'Add Staff',
      description: 'Invite your medical staff',
      icon: Users,
      component: StaffStep,
    },
    {
      id: 'schedule',
      title: 'Set Schedules',
      description: 'Configure doctor schedules',
      icon: Calendar,
      component: ScheduleStep,
    },
    {
      id: 'complete',
      title: 'Complete Setup',
      description: 'You\'re all set!',
      icon: CheckCircle,
      component: CompleteStep,
    },
  ];

  useEffect(() => {
    if (onboardingStatus?.data && !onboardingStatus.data.is_completed) {
      dispatch(appActions.showOnboarding());
    }
  }, [onboardingStatus, dispatch]);

  const handleNext = () => {
    if (currentStep < steps.length - 1) {
      setCurrentStep(currentStep + 1);
    }
  };

  const handlePrevious = () => {
    if (currentStep > 0) {
      setCurrentStep(currentStep - 1);
    }
  };

  const handleSkip = () => {
    if (currentStep === steps.length - 1) {
      completeOnboarding.mutate();
    } else {
      handleNext();
    }
  };

  const handleClinicSubmit = () => {
    createDefaultClinic.mutate(clinicData, {
      onSuccess: (response) => {
        if (response.data?.id) {
          createDefaultServices.mutate(response.data.id);
        }
        handleNext();
      },
    });
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="spinner mx-auto mb-4"></div>
          <p className="text-gray-600">Loading onboarding...</p>
        </div>
      </div>
    );
  }

  if (onboardingStatus?.data?.is_completed) {
    return null; // Redirect to main app
  }

  const CurrentStepComponent = steps[currentStep].component;

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {/* Progress Bar */}
        <div className="mb-8">
          <div className="flex items-center justify-between mb-4">
            <h1 className="text-2xl font-bold text-gray-900">Setup MedX360</h1>
            <span className="text-sm text-gray-500">
              Step {currentStep + 1} of {steps.length}
            </span>
          </div>
          
          <div className="flex items-center space-x-2">
            {steps.map((step, index) => (
              <div key={step.id} className="flex items-center">
                <div className={cn(
                  'flex items-center justify-center w-8 h-8 rounded-full border-2',
                  index <= currentStep
                    ? 'bg-blue-600 border-blue-600 text-white'
                    : 'bg-white border-gray-300 text-gray-400'
                )}>
                  {index < currentStep ? (
                    <CheckCircle className="h-5 w-5" />
                  ) : (
                    <step.icon className="h-5 w-5" />
                  )}
                </div>
                {index < steps.length - 1 && (
                  <div className={cn(
                    'w-16 h-0.5 mx-2',
                    index < currentStep ? 'bg-blue-600' : 'bg-gray-300'
                  )} />
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Step Content */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <div className="mb-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-2">
              {steps[currentStep].title}
            </h2>
            <p className="text-gray-600">
              {steps[currentStep].description}
            </p>
          </div>

          <CurrentStepComponent
            data={clinicData}
            onDataChange={setClinicData}
            onNext={handleNext}
            onPrevious={handlePrevious}
            onSkip={handleSkip}
            onSubmit={handleClinicSubmit}
            isLoading={createDefaultClinic.isLoading || createDefaultServices.isLoading}
          />
        </div>

        {/* Navigation */}
        <div className="flex justify-between mt-8">
          <button
            onClick={handlePrevious}
            disabled={currentStep === 0}
            className="btn btn-outline disabled:opacity-50"
          >
            <ArrowLeft className="h-4 w-4 mr-2" />
            Previous
          </button>

          <div className="flex space-x-4">
            <button
              onClick={handleSkip}
              className="btn btn-outline"
            >
              Skip
            </button>
            
            {currentStep === steps.length - 1 ? (
              <button
                onClick={() => completeOnboarding.mutate()}
                disabled={completeOnboarding.isLoading}
                className="btn btn-primary"
              >
                {completeOnboarding.isLoading ? (
                  <>
                    <div className="spinner mr-2"></div>
                    Completing...
                  </>
                ) : (
                  'Complete Setup'
                )}
              </button>
            ) : (
              <button
                onClick={handleNext}
                className="btn btn-primary"
              >
                Next
                <ArrowRight className="h-4 w-4 ml-2" />
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

// Step Components
const WelcomeStep: React.FC<any> = ({ onNext }) => {
  return (
    <div className="text-center">
      <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <CheckCircle className="h-8 w-8 text-blue-600" />
      </div>
      
      <h3 className="text-lg font-medium text-gray-900 mb-4">
        Welcome to MedX360!
      </h3>
      
      <p className="text-gray-600 mb-6 max-w-2xl mx-auto">
        MedX360 is a comprehensive medical booking management system that will help you 
        manage your clinic, doctors, appointments, and patients efficiently. Let's get 
        you started with a quick setup process.
      </p>
      
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div className="p-4 bg-gray-50 rounded-lg">
          <Building2 className="h-6 w-6 text-blue-600 mx-auto mb-2" />
          <h4 className="font-medium text-gray-900 mb-1">Clinic Management</h4>
          <p className="text-sm text-gray-600">Manage multiple clinics and hospitals</p>
        </div>
        
        <div className="p-4 bg-gray-50 rounded-lg">
          <Calendar className="h-6 w-6 text-blue-600 mx-auto mb-2" />
          <h4 className="font-medium text-gray-900 mb-1">Appointment Booking</h4>
          <p className="text-sm text-gray-600">Easy online booking system</p>
        </div>
        
        <div className="p-4 bg-gray-50 rounded-lg">
          <Users className="h-6 w-6 text-blue-600 mx-auto mb-2" />
          <h4 className="font-medium text-gray-900 mb-1">Staff Management</h4>
          <p className="text-sm text-gray-600">Manage doctors and staff</p>
        </div>
      </div>
      
      <button onClick={onNext} className="btn btn-primary">
        Get Started
        <ArrowRight className="h-4 w-4 ml-2" />
      </button>
    </div>
  );
};

const ClinicStep: React.FC<any> = ({ data, onDataChange, onSubmit, isLoading }) => {
  const handleChange = (field: keyof CreateClinicData, value: string) => {
    onDataChange({ ...data, [field]: value });
  };

  return (
    <div className="max-w-2xl mx-auto">
      <form onSubmit={(e) => { e.preventDefault(); onSubmit(); }}>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="form-group">
            <label className="form-label">Clinic Name *</label>
            <input
              type="text"
              value={data.name}
              onChange={(e) => handleChange('name', e.target.value)}
              className="form-input"
              placeholder="Enter clinic name"
              required
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">Email</label>
            <input
              type="email"
              value={data.email}
              onChange={(e) => handleChange('email', e.target.value)}
              className="form-input"
              placeholder="clinic@example.com"
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">Phone</label>
            <input
              type="tel"
              value={data.phone}
              onChange={(e) => handleChange('phone', e.target.value)}
              className="form-input"
              placeholder="+1 (555) 123-4567"
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">Website</label>
            <input
              type="url"
              value={data.website}
              onChange={(e) => handleChange('website', e.target.value)}
              className="form-input"
              placeholder="https://yourclinic.com"
            />
          </div>
          
          <div className="form-group md:col-span-2">
            <label className="form-label">Description</label>
            <textarea
              value={data.description}
              onChange={(e) => handleChange('description', e.target.value)}
              className="form-textarea"
              placeholder="Brief description of your clinic"
              rows={3}
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">Address</label>
            <input
              type="text"
              value={data.address}
              onChange={(e) => handleChange('address', e.target.value)}
              className="form-input"
              placeholder="Street address"
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">City</label>
            <input
              type="text"
              value={data.city}
              onChange={(e) => handleChange('city', e.target.value)}
              className="form-input"
              placeholder="City"
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">State</label>
            <input
              type="text"
              value={data.state}
              onChange={(e) => handleChange('state', e.target.value)}
              className="form-input"
              placeholder="State/Province"
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">Country</label>
            <input
              type="text"
              value={data.country}
              onChange={(e) => handleChange('country', e.target.value)}
              className="form-input"
              placeholder="Country"
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">Postal Code</label>
            <input
              type="text"
              value={data.postal_code}
              onChange={(e) => handleChange('postal_code', e.target.value)}
              className="form-input"
              placeholder="ZIP/Postal Code"
            />
          </div>
        </div>
        
        <div className="flex justify-end mt-6">
          <button
            type="submit"
            disabled={!data.name || isLoading}
            className="btn btn-primary"
          >
            {isLoading ? (
              <>
                <div className="spinner mr-2"></div>
                Creating Clinic...
              </>
            ) : (
              'Create Clinic'
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

const ServicesStep: React.FC<any> = ({ onNext }) => {
  return (
    <div className="text-center">
      <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <Stethoscope className="h-8 w-8 text-green-600" />
      </div>
      
      <h3 className="text-lg font-medium text-gray-900 mb-4">
        Default Services Created
      </h3>
      
      <p className="text-gray-600 mb-6">
        We've created some common medical services for your clinic. You can add more 
        services later from the Services page.
      </p>
      
      <div className="bg-gray-50 rounded-lg p-6 mb-6">
        <h4 className="font-medium text-gray-900 mb-4">Created Services:</h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div className="flex items-center">
            <CheckCircle className="h-4 w-4 text-green-600 mr-2" />
            General Consultation (30 min)
          </div>
          <div className="flex items-center">
            <CheckCircle className="h-4 w-4 text-green-600 mr-2" />
            Follow-up Visit (15 min)
          </div>
          <div className="flex items-center">
            <CheckCircle className="h-4 w-4 text-green-600 mr-2" />
            Physical Examination (45 min)
          </div>
          <div className="flex items-center">
            <CheckCircle className="h-4 w-4 text-green-600 mr-2" />
            Emergency Consultation (60 min)
          </div>
        </div>
      </div>
      
      <button onClick={onNext} className="btn btn-primary">
        Continue
        <ArrowRight className="h-4 w-4 ml-2" />
      </button>
    </div>
  );
};

const StaffStep: React.FC<any> = ({ onNext }) => {
  return (
    <div className="text-center">
      <div className="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <Users className="h-8 w-8 text-purple-600" />
      </div>
      
      <h3 className="text-lg font-medium text-gray-900 mb-4">
        Add Your Staff
      </h3>
      
      <p className="text-gray-600 mb-6">
        You can add doctors and staff members later from the Doctors and Staff pages. 
        For now, let's continue with the setup.
      </p>
      
      <div className="bg-blue-50 rounded-lg p-6 mb-6">
        <h4 className="font-medium text-gray-900 mb-2">Next Steps:</h4>
        <ul className="text-sm text-gray-600 text-left space-y-1">
          <li>• Add doctors from the Doctors page</li>
          <li>• Set up doctor schedules</li>
          <li>• Add staff members from the Staff page</li>
          <li>• Configure clinic settings</li>
        </ul>
      </div>
      
      <button onClick={onNext} className="btn btn-primary">
        Continue
        <ArrowRight className="h-4 w-4 ml-2" />
      </button>
    </div>
  );
};

const ScheduleStep: React.FC<any> = ({ onNext }) => {
  return (
    <div className="text-center">
      <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <Calendar className="h-8 w-8 text-orange-600" />
      </div>
      
      <h3 className="text-lg font-medium text-gray-900 mb-4">
        Set Doctor Schedules
      </h3>
      
      <p className="text-gray-600 mb-6">
        After adding doctors, you can set their working schedules and availability 
        from the Doctors page. This helps patients book appointments at the right times.
      </p>
      
      <div className="bg-yellow-50 rounded-lg p-6 mb-6">
        <h4 className="font-medium text-gray-900 mb-2">Schedule Features:</h4>
        <ul className="text-sm text-gray-600 text-left space-y-1">
          <li>• Set weekly working hours</li>
          <li>• Add vacation and break times</li>
          <li>• Configure appointment durations</li>
          <li>• Set booking advance limits</li>
        </ul>
      </div>
      
      <button onClick={onNext} className="btn btn-primary">
        Continue
        <ArrowRight className="h-4 w-4 ml-2" />
      </button>
    </div>
  );
};

const CompleteStep: React.FC<any> = () => {
  return (
    <div className="text-center">
      <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <CheckCircle className="h-8 w-8 text-green-600" />
      </div>
      
      <h3 className="text-lg font-medium text-gray-900 mb-4">
        Setup Complete!
      </h3>
      
      <p className="text-gray-600 mb-6">
        Congratulations! Your MedX360 medical booking system is now ready to use. 
        You can start adding doctors, managing appointments, and customizing your settings.
      </p>
      
      <div className="bg-green-50 rounded-lg p-6 mb-6">
        <h4 className="font-medium text-gray-900 mb-2">What's Next:</h4>
        <ul className="text-sm text-gray-600 text-left space-y-1">
          <li>• Add your doctors and staff</li>
          <li>• Set up doctor schedules</li>
          <li>• Configure clinic settings</li>
          <li>• Start accepting bookings</li>
        </ul>
      </div>
      
      <div className="flex justify-center space-x-4">
        <button className="btn btn-outline">
          View Dashboard
        </button>
        <button className="btn btn-primary">
          Complete Setup
        </button>
      </div>
    </div>
  );
};

export default OnboardingWizard;
