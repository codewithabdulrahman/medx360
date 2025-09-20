import React from 'react';
import UpgradeBanner from '../Shared/UpgradeBanner';
import './MultiLocation.css';

const MultiLocation = () => {
  return (
    <div className="medx360-multi-location">
      <UpgradeBanner 
        featureName="Multi-Location Management"
        featureDescription="Manage multiple clinic locations from a single dashboard with advanced scheduling, staff allocation, and location-specific settings."
      />
    </div>
  );
};

export default MultiLocation;
