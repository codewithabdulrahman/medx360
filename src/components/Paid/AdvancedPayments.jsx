import React from 'react';
import UpgradeBanner from '../Shared/UpgradeBanner';
import './AdvancedPayments.css';

const AdvancedPayments = () => {
  return (
    <div className="medx360-advanced-payments">
      <UpgradeBanner 
        featureName="Advanced Payments"
        featureDescription="Advanced payment processing, insurance billing, financial analytics, and comprehensive revenue management tools."
      />
    </div>
  );
};

export default AdvancedPayments;
