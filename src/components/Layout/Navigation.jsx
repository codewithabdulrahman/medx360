import React from 'react';
import { getWordPressUrl, getCurrentPage, isCurrentPage, WORDPRESS_PAGES } from '../../utils/wordpressUrls';

const Navigation = () => {
  const currentPage = getCurrentPage();

  const freeFeatures = [
    { page: WORDPRESS_PAGES.DASHBOARD, label: 'Dashboard', icon: '📊' },
    { page: WORDPRESS_PAGES.BOOKING, label: 'Booking', icon: '📅' },
    { page: WORDPRESS_PAGES.PATIENTS, label: 'Patients', icon: '👥' },
    { page: WORDPRESS_PAGES.PAYMENTS, label: 'Payments', icon: '💳' },
    { page: WORDPRESS_PAGES.STAFF, label: 'Staff', icon: '👨‍⚕️' },
    { page: WORDPRESS_PAGES.NOTIFICATIONS, label: 'Notifications', icon: '🔔' },
    { page: WORDPRESS_PAGES.REPORTS, label: 'Reports', icon: '📈' },
    { page: WORDPRESS_PAGES.ROLES, label: 'Roles & Permissions', icon: '🔐' },
    { page: WORDPRESS_PAGES.SETTINGS, label: 'Settings', icon: '⚙️' },
  ];

  const paidFeatures = [
    { page: WORDPRESS_PAGES.MULTI_LOCATION, label: 'Multi-Location', icon: '🏢' },
    { page: WORDPRESS_PAGES.ADVANCED_STAFF, label: 'Advanced Staff', icon: '👨‍💼' },
    { page: WORDPRESS_PAGES.ADVANCED_NOTIFICATIONS, label: 'Advanced Notifications', icon: '📢' },
    { page: WORDPRESS_PAGES.INTEGRATIONS, label: 'Integrations', icon: '🔗' },
    { page: WORDPRESS_PAGES.ADVANCED_PAYMENTS, label: 'Advanced Payments', icon: '💎' },
    { page: WORDPRESS_PAGES.ADVANCED_REPORTS, label: 'Advanced Reports', icon: '📊' },
  ];

  return (
    <div className="medx360-navigation">
      {/* Free Features Tabs */}
      <div className="medx360-nav-tabs">
        {freeFeatures.map(feature => (
          <a
            key={feature.page}
            href={getWordPressUrl(feature.page)}
            className={`medx360-nav-tab ${isCurrentPage(feature.page) ? 'active' : ''}`}
          >
            <span className="medx360-nav-icon">{feature.icon}</span>
            {feature.label}
          </a>
        ))}
        
        {/* Premium Features as Locked Tabs */}
        {paidFeatures.map(feature => (
          <a
            key={feature.page}
            href={getWordPressUrl(feature.page)}
            className="medx360-nav-tab locked"
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
