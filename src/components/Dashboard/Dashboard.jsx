import React from 'react';
import { Link } from 'react-router-dom';
import './Dashboard.css';

const Dashboard = () => {
  const stats = [
    { label: 'Today\'s Appointments', value: '12', icon: '📅', color: '#3498db' },
    { label: 'Total Patients', value: '1,247', icon: '👥', color: '#2ecc71' },
    { label: 'Pending Payments', value: '8', icon: '💳', color: '#f39c12' },
    { label: 'Staff Members', value: '15', icon: '👨‍⚕️', color: '#9b59b6' },
  ];

  const recentActivities = [
    { type: 'booking', message: 'New appointment booked for John Doe', time: '2 minutes ago' },
    { type: 'payment', message: 'Payment received from Sarah Wilson', time: '15 minutes ago' },
    { type: 'patient', message: 'New patient registered: Mike Johnson', time: '1 hour ago' },
    { type: 'staff', message: 'Dr. Smith updated availability', time: '2 hours ago' },
  ];

  const quickActions = [
    { path: '/booking/new', label: 'New Booking', icon: '➕', color: '#3498db' },
    { path: '/patients/new', label: 'Add Patient', icon: '👤', color: '#2ecc71' },
    { path: '/payments/new', label: 'Record Payment', icon: '💳', color: '#f39c12' },
    { path: '/staff/new', label: 'Add Staff', icon: '👨‍⚕️', color: '#9b59b6' },
  ];

  return (
    <div className="medx360-dashboard">
      <div className="medx360-dashboard-header">
        <h2>Welcome to Medx360 Dashboard</h2>
        <p>Manage your medical practice efficiently</p>
      </div>

      {/* Stats Cards */}
      <div className="medx360-stats-grid">
        {stats.map((stat, index) => (
          <div key={index} className="medx360-stat-card">
            <div className="medx360-stat-icon" style={{ backgroundColor: stat.color }}>
              {stat.icon}
            </div>
            <div className="medx360-stat-content">
              <h3>{stat.value}</h3>
              <p>{stat.label}</p>
            </div>
          </div>
        ))}
      </div>

      <div className="medx360-dashboard-content">
        {/* Quick Actions */}
        <div className="medx360-dashboard-section">
          <h3>Quick Actions</h3>
          <div className="medx360-quick-actions">
            {quickActions.map((action, index) => (
              <Link key={index} to={action.path} className="medx360-quick-action">
                <div className="medx360-quick-action-icon" style={{ backgroundColor: action.color }}>
                  {action.icon}
                </div>
                <span>{action.label}</span>
              </Link>
            ))}
          </div>
        </div>

        {/* Recent Activities */}
        <div className="medx360-dashboard-section">
          <h3>Recent Activities</h3>
          <div className="medx360-activities">
            {recentActivities.map((activity, index) => (
              <div key={index} className="medx360-activity">
                <div className={`medx360-activity-icon medx360-activity-${activity.type}`}>
                  {activity.type === 'booking' && '📅'}
                  {activity.type === 'payment' && '💳'}
                  {activity.type === 'patient' && '👤'}
                  {activity.type === 'staff' && '👨‍⚕️'}
                </div>
                <div className="medx360-activity-content">
                  <p>{activity.message}</p>
                  <span className="medx360-activity-time">{activity.time}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
