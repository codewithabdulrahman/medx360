import React from 'react';
import UpgradeBanner from '../Shared/UpgradeBanner';
import './AdvancedStaff.css';

const AdvancedStaff = () => {
  return (
    <div className="medx360-advanced-staff">
      <UpgradeBanner 
        featureName="Advanced Staff & Resource Management"
        featureDescription="Advanced staff scheduling, resource allocation, performance tracking, and comprehensive staff workflow optimization."
      />
    </div>
  );
};

export default AdvancedStaff;
