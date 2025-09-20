import React from 'react';
import { Link } from 'react-router-dom';

const Header = ({ currentPath }) => {
  const getPageTitle = (path) => {
    const titles = {
      '/dashboard': 'Dashboard',
      '/booking': 'Booking Calendar',
      '/booking/new': 'New Booking',
      '/booking/list': 'Bookings',
      '/patients': 'Patients',
      '/patients/new': 'New Patient',
      '/payments': 'Payments',
      '/payments/new': 'New Payment',
      '/billing': 'Billing Dashboard',
      '/staff': 'Staff Management',
      '/staff/new': 'New Staff Member',
      '/staff/schedule': 'Staff Schedule',
      '/notifications': 'Notifications',
      '/notifications/settings': 'Notification Settings',
      '/reports': 'Reports Dashboard',
      '/reports/appointments': 'Appointment Reports',
      '/reports/financial': 'Financial Reports',
      '/roles': 'Role Management',
      '/permissions': 'Permission Settings',
      '/settings': 'Settings',
      '/profile': 'Profile',
      '/multi-location': 'Multi-Location Management',
      '/advanced-staff': 'Advanced Staff Management',
      '/advanced-notifications': 'Advanced Notifications',
      '/integrations': 'Integrations',
      '/advanced-payments': 'Advanced Payments',
      '/advanced-reports': 'Advanced Reports',
    };
    return titles[path] || 'Medx360';
  };

  return (
    <div className="medx360-header">
      <div className="medx360-header-content">
        <h1 className="medx360-page-title">{getPageTitle(currentPath)}</h1>
        
        <div className="medx360-header-actions">
          <Link to="/notifications" className="medx360-notification-btn">
            🔔 <span className="medx360-notification-count">3</span>
          </Link>
          <Link to="/profile" className="medx360-profile-btn">
            👤 Admin User
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Header;
