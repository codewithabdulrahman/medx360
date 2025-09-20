import React from 'react';
import './UpgradeBanner.css';

const UpgradeBanner = ({ featureName, featureDescription }) => {
  return (
    <div className="medx360-upgrade-banner">
      <div className="medx360-upgrade-content">
        <div className="medx360-upgrade-icon">🔒</div>
        <h2>Premium Feature Locked</h2>
        <p>{featureName} is a premium feature that requires an upgrade.</p>
        <p className="medx360-upgrade-description">{featureDescription}</p>
        <div className="medx360-upgrade-actions">
          <button className="medx360-upgrade-btn">
            Upgrade to Pro
          </button>
          <button className="medx360-learn-more-btn">
            Learn More
          </button>
        </div>
      </div>
    </div>
  );
};

export default UpgradeBanner;
