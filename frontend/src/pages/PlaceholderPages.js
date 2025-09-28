import React from 'react';
import { 
  UserCheck, 
  Stethoscope, 
  Users, 
  Calendar, 
  FileText, 
  CreditCard,
  Settings
} from 'lucide-react';
import { FormCard } from '@components/forms';

const PlaceholderPage = ({ title, description, icon: Icon, color = 'blue' }) => {
  const colorClasses = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    purple: 'bg-purple-100 text-purple-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    red: 'bg-red-100 text-red-600',
    indigo: 'bg-indigo-100 text-indigo-600',
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
        <p className="mt-1 text-sm text-gray-600">{description}</p>
      </div>

      <FormCard>
        <div className="text-center py-12">
          <div className={`mx-auto flex items-center justify-center h-16 w-16 rounded-full ${colorClasses[color]} mb-6`}>
            <Icon className="h-8 w-8" />
          </div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            {title} Management
          </h3>
          <p className="text-gray-600 mb-6">
            This page is under development. Full functionality will be available soon.
          </p>
          <div className="text-sm text-gray-500">
            Coming soon: Create, edit, and manage {title.toLowerCase()}
          </div>
        </div>
      </FormCard>
    </div>
  );
};

export const Doctors = () => (
  <PlaceholderPage
    title="Doctors"
    description="Manage medical professionals and their schedules"
    icon={UserCheck}
    color="green"
  />
);

export const Services = () => (
  <PlaceholderPage
    title="Services"
    description="Configure medical services and pricing"
    icon={Stethoscope}
    color="yellow"
  />
);

export const Staff = () => (
  <PlaceholderPage
    title="Staff"
    description="Manage non-medical staff members"
    icon={Users}
    color="purple"
  />
);

export const Bookings = () => (
  <PlaceholderPage
    title="Bookings"
    description="Schedule and manage patient appointments"
    icon={Calendar}
    color="blue"
  />
);

export const Consultations = () => (
  <PlaceholderPage
    title="Consultations"
    description="Track patient consultations and medical records"
    icon={FileText}
    color="indigo"
  />
);

export const Payments = () => (
  <PlaceholderPage
    title="Payments"
    description="Process payments and handle billing"
    icon={CreditCard}
    color="red"
  />
);

export const SettingsPage = () => (
  <PlaceholderPage
    title="Settings"
    description="Configure system settings and preferences"
    icon={Settings}
    color="gray"
  />
);
