import React, { useState, useEffect } from 'react';
import { getWordPressUrl, WORDPRESS_PAGES } from '../../utils/wordpressUrls';
import { dashboardAPI } from '../../utils/api';
import { useToast } from '../Shared/ToastContext';
import './Dashboard.css';

const Dashboard = () => {
  const [showSetupBanner, setShowSetupBanner] = useState(true);
  const [stats, setStats] = useState([]);
  const [recentActivities, setRecentActivities] = useState([]);
  const [loading, setLoading] = useState(true);
  const { showToast } = useToast();

  useEffect(() => {
    // Check if user has completed setup
    const hasCompletedSetup = localStorage.getItem('medx360_setup_completed');
    setShowSetupBanner(!hasCompletedSetup);
    
    // Load dashboard data
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      setLoading(true);
      
      // Fetch dashboard stats
      const statsData = await dashboardAPI.getStats();
      
      // Transform stats data to match component structure
      const transformedStats = [
        { 
          label: 'Today\'s Appointments', 
          value: statsData.today_appointments?.toString() || '0', 
          icon: '📅', 
          color: '#2CA6A4' 
        },
        { 
          label: 'Total Patients', 
          value: statsData.total_patients?.toString() || '0', 
          icon: '👥', 
          color: '#56C596' 
        },
        { 
          label: 'Pending Payments', 
          value: statsData.pending_payments?.toString() || '0', 
          icon: '💳', 
          color: '#FFC107' 
        },
        { 
          label: 'Staff Members', 
          value: statsData.total_staff?.toString() || '0', 
          icon: '👨‍⚕️', 
          color: '#5C7AEA' 
        },
      ];
      
      setStats(transformedStats);
      
      // Fetch recent activities
      const activitiesData = await dashboardAPI.getRecentActivities({ limit: 4 });
      setRecentActivities(activitiesData || []);
      
    } catch (error) {
      console.error('Error loading dashboard data:', error);
      showToast('Failed to load dashboard data', 'error');
      
      // Fallback to default stats if API fails
      setStats([
        { label: 'Today\'s Appointments', value: '0', icon: '📅', color: '#2CA6A4' },
        { label: 'Total Patients', value: '0', icon: '👥', color: '#56C596' },
        { label: 'Pending Payments', value: '0', icon: '💳', color: '#FFC107' },
        { label: 'Staff Members', value: '0', icon: '👨‍⚕️', color: '#5C7AEA' },
      ]);
      setRecentActivities([]);
    } finally {
      setLoading(false);
    }
  };

  const dismissSetupBanner = () => {
    setShowSetupBanner(false);
    localStorage.setItem('medx360_setup_completed', 'true');
  };

  const quickActions = [
    { page: WORDPRESS_PAGES.BOOKING_NEW, label: 'New Booking', icon: '➕', color: '#2CA6A4' },
    { page: WORDPRESS_PAGES.PATIENTS_NEW, label: 'Add Patient', icon: '👤', color: '#56C596' },
    { page: WORDPRESS_PAGES.PAYMENTS_NEW, label: 'Record Payment', icon: '💳', color: '#FFC107' },
    { page: WORDPRESS_PAGES.STAFF_NEW, label: 'Add Staff', icon: '👨‍⚕️', color: '#5C7AEA' },
  ];

  return (
    <div className="medx360-dashboard">
      <div className="medx360-dashboard-header">
        <h2>Welcome to Medx360 Dashboard</h2>
        <p>Manage your medical practice efficiently</p>
      </div>

      {/* Setup Banner for New Users */}
      {showSetupBanner && (
        <div className="medx360-setup-banner">
          <div className="medx360-setup-content">
            <div className="medx360-setup-icon">🩺</div>
            <div className="medx360-setup-text">
              <h3>First time using Medx360?</h3>
              <p>Complete the setup wizard to configure your practice, services, and staff</p>
            </div>
            <div className="medx360-setup-actions">
              <a
                href={getWordPressUrl(WORDPRESS_PAGES.SETUP)}
                className="medx360-btn medx360-btn-primary"
              >
                Start Setup
              </a>
              <button 
                className="medx360-dismiss-btn"
                onClick={dismissSetupBanner}
                title="Dismiss setup banner"
              >
                ×
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Stats Cards */}
      <div className="medx360-stats-grid">
        {loading ? (
          // Loading skeleton for stats
          Array.from({ length: 4 }).map((_, index) => (
            <div key={index} className="medx360-stat-card medx360-loading-skeleton">
              <div className="medx360-stat-icon"></div>
              <div className="medx360-stat-content">
                <div className="medx360-skeleton-line medx360-skeleton-title"></div>
                <div className="medx360-skeleton-line medx360-skeleton-text"></div>
              </div>
            </div>
          ))
        ) : (
          stats.map((stat, index) => (
            <div key={index} className="medx360-stat-card">
              <div className="medx360-stat-icon" style={{ backgroundColor: stat.color }}>
                {stat.icon}
              </div>
              <div className="medx360-stat-content">
                <h3>{stat.value}</h3>
                <p>{stat.label}</p>
              </div>
            </div>
          ))
        )}
      </div>

      <div className="medx360-dashboard-content">
        {/* Quick Actions */}
        <div className="medx360-dashboard-section">
          <h3>Quick Actions</h3>
          <div className="medx360-quick-actions">
            {quickActions.map((action, index) => (
              <a key={index} href={getWordPressUrl(action.page)} className="medx360-quick-action">
                <div className="medx360-quick-action-icon" style={{ backgroundColor: action.color }}>
                  {action.icon}
                </div>
                <span>{action.label}</span>
              </a>
            ))}
          </div>
        </div>

        {/* Recent Activities */}
        <div className="medx360-dashboard-section">
          <h3>Recent Activities</h3>
          <div className="medx360-activities">
            {loading ? (
              // Loading skeleton for activities
              Array.from({ length: 4 }).map((_, index) => (
                <div key={index} className="medx360-activity medx360-loading-skeleton">
                  <div className="medx360-activity-icon"></div>
                  <div className="medx360-activity-content">
                    <div className="medx360-skeleton-line medx360-skeleton-text"></div>
                    <div className="medx360-skeleton-line medx360-skeleton-time"></div>
                  </div>
                </div>
              ))
            ) : recentActivities.length > 0 ? (
              recentActivities.map((activity, index) => (
                <div key={index} className="medx360-activity">
                  <div className={`medx360-activity-icon medx360-activity-${activity.type}`}>
                    {activity.icon || '📅'}
                  </div>
                  <div className="medx360-activity-content">
                    <p>{activity.message}</p>
                    <span className="medx360-activity-time">{activity.time}</span>
                  </div>
                </div>
              ))
            ) : (
              <div className="medx360-empty-state">
                <div className="medx360-empty-icon">📋</div>
                <h3>No recent activities</h3>
                <p>Activities will appear here as they happen.</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
