import React from 'react';
import { 
  Building2, 
  Building, 
  UserCheck, 
  Stethoscope, 
  Users, 
  Calendar, 
  FileText, 
  CreditCard,
  TrendingUp,
  Clock,
  CheckCircle,
  AlertCircle
} from 'lucide-react';
import { useOnboardingStatistics } from '@hooks/useApi';
import { FormLoading, FormStatus } from '@components/forms';

const StatCard = ({ title, value, icon: Icon, color = 'blue', trend = null }) => {
  const colorClasses = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    red: 'bg-red-500',
    purple: 'bg-purple-500',
    indigo: 'bg-indigo-500',
  };

  return (
    <div className="bg-white overflow-hidden shadow rounded-lg">
      <div className="p-5">
        <div className="flex items-center">
          <div className="flex-shrink-0">
            <div className={`p-3 rounded-md ${colorClasses[color]}`}>
              <Icon className="h-6 w-6 text-white" />
            </div>
          </div>
          <div className="ml-5 w-0 flex-1">
            <dl>
              <dt className="text-sm font-medium text-gray-500 truncate">
                {title}
              </dt>
              <dd className="flex items-baseline">
                <div className="text-2xl font-semibold text-gray-900">
                  {value}
                </div>
                {trend && (
                  <div className={`ml-2 flex items-baseline text-sm font-semibold ${
                    trend > 0 ? 'text-green-600' : trend < 0 ? 'text-red-600' : 'text-gray-500'
                  }`}>
                    <TrendingUp className="h-4 w-4 mr-1" />
                    {Math.abs(trend)}%
                  </div>
                )}
              </dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  );
};

const QuickActionCard = ({ title, description, icon: Icon, onClick, color = 'blue' }) => {
  const colorClasses = {
    blue: 'bg-blue-50 hover:bg-blue-100 text-blue-700',
    green: 'bg-green-50 hover:bg-green-100 text-green-700',
    yellow: 'bg-yellow-50 hover:bg-yellow-100 text-yellow-700',
    red: 'bg-red-50 hover:bg-red-100 text-red-700',
    purple: 'bg-purple-50 hover:bg-purple-100 text-purple-700',
    indigo: 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700',
  };

  return (
    <button
      onClick={onClick}
      className={`w-full p-6 rounded-lg border-2 border-dashed border-gray-300 ${colorClasses[color]} transition-colors duration-200`}
    >
      <div className="flex items-center">
        <Icon className="h-8 w-8 mr-4" />
        <div className="text-left">
          <h3 className="text-lg font-medium">{title}</h3>
          <p className="text-sm opacity-75">{description}</p>
        </div>
      </div>
    </button>
  );
};

const RecentActivityItem = ({ type, title, time, status = 'completed' }) => {
  const statusIcons = {
    completed: CheckCircle,
    pending: Clock,
    error: AlertCircle,
  };

  const statusColors = {
    completed: 'text-green-500',
    pending: 'text-yellow-500',
    error: 'text-red-500',
  };

  const Icon = statusIcons[status];

  return (
    <div className="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-md">
      <Icon className={`h-5 w-5 ${statusColors[status]}`} />
      <div className="flex-1 min-w-0">
        <p className="text-sm font-medium text-gray-900 truncate">
          {title}
        </p>
        <p className="text-sm text-gray-500">
          {type} • {time}
        </p>
      </div>
    </div>
  );
};

const Dashboard = () => {
  const { data: stats, isLoading, error } = useOnboardingStatistics();

  if (isLoading) {
    return <FormLoading message="Loading dashboard..." />;
  }

  if (error) {
    return (
      <FormStatus 
        type="error" 
        message="Failed to load dashboard data. Please try again." 
      />
    );
  }

  const quickActions = [
    {
      title: 'Add New Clinic',
      description: 'Create a new medical clinic',
      icon: Building2,
      color: 'blue',
      onClick: () => console.log('Add clinic'),
    },
    {
      title: 'Add Doctor',
      description: 'Register a new doctor',
      icon: UserCheck,
      color: 'green',
      onClick: () => console.log('Add doctor'),
    },
    {
      title: 'Create Booking',
      description: 'Schedule an appointment',
      icon: Calendar,
      color: 'purple',
      onClick: () => console.log('Create booking'),
    },
    {
      title: 'Add Service',
      description: 'Define a new medical service',
      icon: Stethoscope,
      color: 'indigo',
      onClick: () => console.log('Add service'),
    },
  ];

  const recentActivities = [
    {
      type: 'Booking',
      title: 'New appointment scheduled for John Doe',
      time: '2 minutes ago',
      status: 'completed',
    },
    {
      type: 'Payment',
      title: 'Payment received for consultation',
      time: '15 minutes ago',
      status: 'completed',
    },
    {
      type: 'Doctor',
      title: 'Dr. Smith added to Cardiology',
      time: '1 hour ago',
      status: 'completed',
    },
    {
      type: 'Clinic',
      title: 'Downtown Medical Center created',
      time: '2 hours ago',
      status: 'completed',
    },
  ];

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="mt-1 text-sm text-gray-600">
          Welcome to MedX360. Here's what's happening with your medical practice.
        </p>
      </div>

      {/* Statistics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total Clinics"
          value={stats?.clinics_count || 0}
          icon={Building2}
          color="blue"
          trend={5}
        />
        <StatCard
          title="Active Doctors"
          value={stats?.doctors_count || 0}
          icon={UserCheck}
          color="green"
          trend={12}
        />
        <StatCard
          title="Total Bookings"
          value={stats?.bookings_count || 0}
          icon={Calendar}
          color="purple"
          trend={8}
        />
        <StatCard
          title="Revenue"
          value={`$${stats?.total_revenue || 0}`}
          icon={CreditCard}
          color="yellow"
          trend={15}
        />
      </div>

      {/* Quick Actions */}
      <div>
        <h2 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {quickActions.map((action, index) => (
            <QuickActionCard key={index} {...action} />
          ))}
        </div>
      </div>

      {/* Recent Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white shadow rounded-lg">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Recent Activity</h3>
          </div>
          <div className="divide-y divide-gray-200">
            {recentActivities.map((activity, index) => (
              <RecentActivityItem key={index} {...activity} />
            ))}
          </div>
        </div>

        {/* System Status */}
        <div className="bg-white shadow rounded-lg">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">System Status</h3>
          </div>
          <div className="p-6 space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-gray-700">API Status</span>
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <CheckCircle className="h-3 w-3 mr-1" />
                Online
              </span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-gray-700">Database</span>
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <CheckCircle className="h-3 w-3 mr-1" />
                Connected
              </span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-gray-700">Last Backup</span>
              <span className="text-sm text-gray-500">2 hours ago</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-gray-700">Uptime</span>
              <span className="text-sm text-gray-500">99.9%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;