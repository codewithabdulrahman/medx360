import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { 
  Calendar, 
  Building2, 
  Hospital, 
  UserCheck, 
  Stethoscope, 
  Users, 
  CreditCard, 
  MessageSquare, 
  Settings,
  Menu,
  ChevronLeft,
  ChevronRight
} from 'lucide-react';
import { useApp, appActions } from '../contexts/AppContext';
import { cn } from '../utils';

const Sidebar: React.FC = () => {
  const { state, dispatch } = useApp();
  const location = useLocation();

  const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: Calendar },
    { name: 'Clinics', href: '/clinics', icon: Building2 },
    { name: 'Hospitals', href: '/hospitals', icon: Hospital },
    { name: 'Doctors', href: '/doctors', icon: UserCheck },
    { name: 'Services', href: '/services', icon: Stethoscope },
    { name: 'Staff', href: '/staff', icon: Users },
    { name: 'Bookings', href: '/bookings', icon: Calendar },
    { name: 'Consultations', href: '/consultations', icon: MessageSquare },
    { name: 'Payments', href: '/payments', icon: CreditCard },
    { name: 'Settings', href: '/settings', icon: Settings },
  ];

  const toggleSidebar = () => {
    dispatch(appActions.toggleSidebar());
  };

  return (
    <>
      {/* Mobile sidebar overlay */}
      {state.ui.sidebarOpen && (
        <div 
          className="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
          onClick={toggleSidebar}
        />
      )}

      {/* Sidebar */}
      <div className={cn(
        'fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0',
        state.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      )}>
        <div className="flex items-center justify-between h-16 px-4 border-b border-gray-200">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-sm">MX</span>
              </div>
            </div>
            <div className="ml-3">
              <h1 className="text-lg font-semibold text-gray-900">MedX360</h1>
            </div>
          </div>
          
          <button
            onClick={toggleSidebar}
            className="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
          >
            <ChevronLeft className="h-5 w-5" />
          </button>
        </div>

        <nav className="mt-5 px-2 space-y-1">
          {navigation.map((item) => {
            const isActive = location.pathname === item.href;
            return (
              <Link
                key={item.name}
                to={item.href}
                className={cn(
                  'group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                  isActive
                    ? 'bg-blue-100 text-blue-900'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                )}
              >
                <item.icon
                  className={cn(
                    'mr-3 h-5 w-5 flex-shrink-0',
                    isActive ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'
                  )}
                />
                {item.name}
              </Link>
            );
          })}
        </nav>

        {/* Collapsed sidebar toggle for desktop */}
        <div className="absolute top-4 -right-3 hidden lg:block">
          <button
            onClick={toggleSidebar}
            className="p-1 rounded-full bg-white shadow-md border border-gray-200 hover:bg-gray-50"
          >
            {state.ui.sidebarOpen ? (
              <ChevronLeft className="h-4 w-4 text-gray-500" />
            ) : (
              <ChevronRight className="h-4 w-4 text-gray-500" />
            )}
          </button>
        </div>
      </div>

      {/* Collapsed sidebar */}
      {!state.ui.sidebarOpen && (
        <div className="fixed inset-y-0 left-0 z-40 w-16 bg-white shadow-lg hidden lg:block">
          <div className="flex items-center justify-center h-16 border-b border-gray-200">
            <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-sm">MX</span>
            </div>
          </div>
          
          <nav className="mt-5 px-2 space-y-1">
            {navigation.map((item) => {
              const isActive = location.pathname === item.href;
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  className={cn(
                    'group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                    isActive
                      ? 'bg-blue-100 text-blue-900'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                  )}
                  title={item.name}
                >
                  <item.icon
                    className={cn(
                      'h-5 w-5 flex-shrink-0',
                      isActive ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'
                    )}
                  />
                </Link>
              );
            })}
          </nav>
        </div>
      )}
    </>
  );
};

export default Sidebar;
