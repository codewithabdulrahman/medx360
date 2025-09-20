import React from 'react';
import { getWordPressUrl, getCurrentPage, isCurrentPage, WORDPRESS_PAGES } from '../../utils/wordpressUrls';

const Navigation = () => {
  const currentPage = getCurrentPage();

  const features = [
    { page: WORDPRESS_PAGES.DASHBOARD, label: 'Dashboard', icon: '📊' },
    { page: WORDPRESS_PAGES.BOOKING, label: 'Booking', icon: '📅' },
    { page: WORDPRESS_PAGES.PATIENTS, label: 'Patients', icon: '👥' },
    { page: WORDPRESS_PAGES.PAYMENTS, label: 'Payments', icon: '💳' },
    { page: WORDPRESS_PAGES.STAFF, label: 'Staff', icon: '👨‍⚕️' },
    { page: WORDPRESS_PAGES.CLINIC, label: 'Clinic', icon: '🏥' },
    { page: WORDPRESS_PAGES.SERVICE, label: 'Services', icon: '🩺' },
    { page: WORDPRESS_PAGES.NOTIFICATIONS, label: 'Notifications', icon: '🔔' },
    { page: WORDPRESS_PAGES.REPORTS, label: 'Reports', icon: '📈' },
    { page: WORDPRESS_PAGES.ROLES, label: 'Roles & Permissions', icon: '🔐' },
    { page: WORDPRESS_PAGES.SETTINGS, label: 'Settings', icon: '⚙️' },
  ];

  return (
    <div className="medx360-navigation">
      <div className="medx360-nav-tabs">
        {features.map(feature => (
          <a
            key={feature.page}
            href={getWordPressUrl(feature.page)}
            className={`medx360-nav-tab ${isCurrentPage(feature.page) ? 'active' : ''}`}
          >
            <span className="medx360-nav-icon">{feature.icon}</span>
            {feature.label}
          </a>
        ))}
      </div>
    </div>
  );
};

export default Navigation;
