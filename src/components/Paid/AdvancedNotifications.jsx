import React from 'react';
import UpgradeBanner from '../Shared/UpgradeBanner';
import './AdvancedNotifications.css';

const AdvancedNotifications = () => {
  return (
    <div className="medx360-advanced-notifications">
      <UpgradeBanner 
        featureName="Advanced Notifications"
        featureDescription="Custom notification templates, automated reminders, multi-channel notifications, and advanced communication workflows."
      />
    </div>
  );
};

export default AdvancedNotifications;
