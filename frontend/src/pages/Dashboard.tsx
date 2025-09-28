import React from 'react';
import { Calendar, Building2, Users, CreditCard } from 'lucide-react';
import { useOnboardingStatistics } from '../hooks/useApi';

const Dashboard: React.FC = () => {
  const { data: stats, isLoading } = useOnboardingStatistics();

  const statsCards = [
    {
      title: 'Total Clinics',
      value: stats?.data?.clinics || 0,
      icon: Building2,
      color: 'bg-blue-500',
    },
    {
      title: 'Total Doctors',
      value: stats?.data?.doctors || 0,
      icon: Users,
      color: 'bg-green-500',
    },
    {
      title: 'Total Bookings',
      value: stats?.data?.bookings || 0,
      icon: Calendar,
      color: 'bg-purple-500',
    },
    {
      title: 'Total Revenue',
      value: '$0.00',
      icon: CreditCard,
      color: 'bg-orange-500',
    },
  ];

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="spinner"></div>
      </div>
    );
  }

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600">Welcome to MedX360 Medical Management System</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {statsCards.map((stat, index) => (
          <div key={index} className="card">
            <div className="flex items-center">
              <div className={`p-3 rounded-lg ${stat.color} text-white`}>
                <stat.icon className="h-6 w-6" />
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-600">{stat.title}</p>
                <p className="text-2xl font-semibold text-gray-900">{stat.value}</p>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="space-y-3">
            <button className="w-full btn btn-primary text-left">
              <Building2 className="h-4 w-4 mr-2" />
              Add New Clinic
            </button>
            <button className="w-full btn btn-outline text-left">
              <Users className="h-4 w-4 mr-2" />
              Add New Doctor
            </button>
            <button className="w-full btn btn-outline text-left">
              <Calendar className="h-4 w-4 mr-2" />
              View Bookings
            </button>
          </div>
        </div>

        <div className="card">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
          <div className="space-y-3">
            <div className="flex items-center text-sm text-gray-600">
              <div className="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
              System setup completed
            </div>
            <div className="flex items-center text-sm text-gray-600">
              <div className="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
              Default clinic created
            </div>
            <div className="flex items-center text-sm text-gray-600">
              <div className="w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
              Default services added
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
