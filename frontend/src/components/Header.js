import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { 
  LayoutDashboard, 
  Building2, 
  Building, 
  UserCheck, 
  Stethoscope, 
  Users, 
  Calendar, 
  FileText, 
  CreditCard, 
  Settings,
  Bell,
  Menu,
  X
} from 'lucide-react';
import clsx from 'clsx';

const Header = () => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const location = useLocation();

  const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { name: 'Clinics', href: '/clinics', icon: Building2 },
    { name: 'Hospitals', href: '/hospitals', icon: Building },
    { name: 'Doctors', href: '/doctors', icon: UserCheck },
    { name: 'Services', href: '/services', icon: Stethoscope },
    { name: 'Staff', href: '/staff', icon: Users },
    { name: 'Bookings', href: '/bookings', icon: Calendar },
    { name: 'Consultations', href: '/consultations', icon: FileText },
    { name: 'Payments', href: '/payments', icon: CreditCard },
    { name: 'Settings', href: '/settings', icon: Settings },
  ];

  return (
    <header className="bg-white shadow-sm border-b border-gray-200">
      <div className="flex items-center justify-between h-16 px-6">
        <div className="flex items-center">
          <h1 className="text-xl font-semibold text-gray-900 mr-8">
            MedX360
          </h1>
          
          {/* Desktop Navigation Menu */}
          <nav className="hidden lg:flex items-center space-x-1">
            {navigation.map((item) => {
              const isActive = location.pathname === item.href;
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  className={clsx(
                    'flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                    isActive
                      ? 'bg-primary-50 text-primary-700'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                  )}
                >
                  <item.icon
                    className={clsx(
                      'mr-2 h-4 w-4 flex-shrink-0',
                      isActive ? 'text-primary-500' : 'text-gray-400'
                    )}
                  />
                  {item.name}
                </Link>
              );
            })}
          </nav>
        </div>

        <div className="flex items-center space-x-4">
          {/* Mobile menu button */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
          >
            {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </button>

          {/* Notifications */}
          <button className="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
            <Bell className="h-5 w-5" />
          </button>
        </div>
      </div>

      {/* Mobile Navigation Menu */}
      {mobileMenuOpen && (
        <div className="lg:hidden border-t border-gray-200 bg-white">
          <nav className="px-6 py-4">
            <div className="space-y-1">
              {navigation.map((item) => {
                const isActive = location.pathname === item.href;
                return (
                  <Link
                    key={item.name}
                    to={item.href}
                    onClick={() => setMobileMenuOpen(false)}
                    className={clsx(
                      'flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                      isActive
                        ? 'bg-primary-50 text-primary-700'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    )}
                  >
                    <item.icon
                      className={clsx(
                        'mr-3 h-5 w-5 flex-shrink-0',
                        isActive ? 'text-primary-500' : 'text-gray-400'
                      )}
                    />
                    {item.name}
                  </Link>
                );
              })}
            </div>
          </nav>
        </div>
      )}
    </header>
  );
};

export default Header;
