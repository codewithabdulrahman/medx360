import React from 'react';
import UpgradeBanner from '../Shared/UpgradeBanner';
import './Integrations.css';

const Integrations = () => {
  return (
    <div className="medx360-integrations">
      <UpgradeBanner 
        featureName="Integrations"
        featureDescription="Connect with third-party services, EHR systems, payment gateways, and API integrations for seamless workflow automation."
      />
    </div>
  );
};

export default Integrations;
