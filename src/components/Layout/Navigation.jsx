import React from 'react';
import { Link, useLocation } from 'react-router-dom';

const Navigation = () => {
  const location = useLocation();

  const freeFeatures = [
    { path: '/dashboard', label: 'Dashboard', icon: '📊' },
    { path: '/booking', label: 'Booking', icon: '📅' },
    { path: '/patients', label: 'Patients', icon: '👥' },
    { path: '/payments', label: 'Payments', icon: '💳' },
    { path: '/staff', label: 'Staff', icon: '👨‍⚕️' },
    { path: '/notifications', label: 'Notifications', icon: '🔔' },
    { path: '/reports', label: 'Reports', icon: '📈' },
    { path: '/roles', label: 'Roles & Permissions', icon: '🔐' },
    { path: '/settings', label: 'Settings', icon: '⚙️' },
  ];

  const paidFeatures = [
    { path: '/multi-location', label: 'Multi-Location', icon: '🏢' },
    { path: '/advanced-staff', label: 'Advanced Staff', icon: '👨‍💼' },
    { path: '/advanced-notifications', label: 'Advanced Notifications', icon: '📢' },
    { path: '/integrations', label: 'Integrations', icon: '🔗' },
    { path: '/advanced-payments', label: 'Advanced Payments', icon: '💎' },
    { path: '/advanced-reports', label: 'Advanced Reports', icon: '📊' },
  ];

  return (
    <div className="medx360-navigation">
      {/* Free Features Tabs */}
      <div className="medx360-nav-tabs">
        {freeFeatures.map(feature => (
          <Link
            key={feature.path}
            to={feature.path}
            className={`medx360-nav-tab ${location.pathname === feature.path ? 'active' : ''}`}
          >
            <span className="medx360-nav-icon">{feature.icon}</span>
            {feature.label}
          </Link>
        ))}
      </div>

      {/* Premium Features Section */}
      <div className="medx360-epic-section">
        <h3>Premium Features</h3>
        <div className="medx360-epic-grid">
          {paidFeatures.map(feature => (
            <Link
              key={feature.path}
              to={feature.path}
              className={`medx360-epic-item paid ${location.pathname === feature.path ? 'active' : ''}`}
            >
              <span className="medx360-epic-icon">{feature.icon}</span>
              <div className="medx360-epic-title">{feature.label}</div>
              <div className="medx360-epic-description">
                Advanced features for professional medical practices
              </div>
              <span className="medx360-epic-badge paid">PRO</span>
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Navigation;
