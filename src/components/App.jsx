import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useLocation } from 'react-router-dom';
import Layout from './Layout/Layout';
import Dashboard from './Dashboard/Dashboard';
import './Shared/GlobalStyles.css';

// Epic 1: Core Booking System (Free)
import BookingCalendar from './Booking/BookingCalendar';
import BookingForm from './Booking/BookingForm';
import BookingList from './Booking/BookingList';

// Epic 2: Patient Management (Free)
import PatientList from './Patients/PatientList';
import PatientForm from './Patients/PatientForm';
import PatientProfile from './Patients/PatientProfile';

// Epic 3: Payment & Billing (Free)
import PaymentList from './Payments/PaymentList';
import PaymentForm from './Payments/PaymentForm';
import BillingDashboard from './Payments/BillingDashboard';

// Epic 4: Staff Management (Free)
import StaffList from './Staff/StaffList';
import StaffForm from './Staff/StaffForm';
import StaffSchedule from './Staff/StaffSchedule';

// Epic 5: Notifications (Free)
import NotificationCenter from './Notifications/NotificationCenter';
import NotificationSettings from './Notifications/NotificationSettings';

// Epic 6: Reporting (Free)
import ReportsDashboard from './Reports/ReportsDashboard';
import AppointmentReports from './Reports/AppointmentReports';
import FinancialReports from './Reports/FinancialReports';

// Epic 7: Roles & Permissions (Free)
import RoleManagement from './Roles/RoleManagement';
import PermissionSettings from './Roles/PermissionSettings';

// Epic 8: UI/UX Enhancements (Free)
import Settings from './Settings/Settings';
import Profile from './Profile/Profile';

// Epic 9-14: Paid Features
import MultiLocation from './Paid/MultiLocation';
import AdvancedStaff from './Paid/AdvancedStaff';
import AdvancedNotifications from './Paid/AdvancedNotifications';
import Integrations from './Paid/Integrations';
import AdvancedPayments from './Paid/AdvancedPayments';
import AdvancedReports from './Paid/AdvancedReports';

import { getCurrentPage } from '../utils/wordpressUrls';

// Main App Router Component
function AppRouter() {
  const [currentPage, setCurrentPage] = useState(getCurrentPage());
  
  // Listen for hash changes
  useEffect(() => {
    const handleHashChange = () => {
      setCurrentPage(getCurrentPage());
    };
    
    // Listen for hash changes
    window.addEventListener('hashchange', handleHashChange);
    
    // Cleanup
    return () => {
      window.removeEventListener('hashchange', handleHashChange);
    };
  }, []);
  
  // Render component based on current page
  const renderPage = () => {
    switch (currentPage) {
      case 'dashboard':
        return <Dashboard />;
      
      // Epic 1: Core Booking System (Free)
      case 'booking':
        return <BookingCalendar />;
      case 'booking/new':
        return <BookingForm />;
      case 'booking/list':
        return <BookingList />;
      
      // Epic 2: Patient Management (Free)
      case 'patients':
        return <PatientList />;
      case 'patients/new':
        return <PatientForm />;
      case 'patients/profile':
        return <PatientProfile />;
      
      // Epic 3: Payment & Billing (Free)
      case 'payments':
        return <PaymentList />;
      case 'payments/new':
        return <PaymentForm />;
      case 'billing':
        return <BillingDashboard />;
      
      // Epic 4: Staff Management (Free)
      case 'staff':
        return <StaffList />;
      case 'staff/new':
        return <StaffForm />;
      case 'staff/schedule':
        return <StaffSchedule />;
      
      // Epic 5: Notifications (Free)
      case 'notifications':
        return <NotificationCenter />;
      case 'notifications/settings':
        return <NotificationSettings />;
      
      // Epic 6: Reporting (Free)
      case 'reports':
        return <ReportsDashboard />;
      case 'reports/appointments':
        return <AppointmentReports />;
      case 'reports/financial':
        return <FinancialReports />;
      
      // Epic 7: Roles & Permissions (Free)
      case 'roles':
        return <RoleManagement />;
      case 'permissions':
        return <PermissionSettings />;
      
      // Epic 8: UI/UX Enhancements (Free)
      case 'settings':
        return <Settings />;
      case 'profile':
        return <Profile />;
      
      // Epic 9-14: Paid Features
      case 'multi-location':
        return <MultiLocation />;
      case 'advanced-staff':
        return <AdvancedStaff />;
      case 'advanced-notifications':
        return <AdvancedNotifications />;
      case 'integrations':
        return <Integrations />;
      case 'advanced-payments':
        return <AdvancedPayments />;
      case 'advanced-reports':
        return <AdvancedReports />;
      
      default:
        return <Dashboard />;
    }
  };
  
  return <Layout>{renderPage()}</Layout>;
}

export default function App() {
  return (
    <Router>
      <AppRouter />
    </Router>
  );
}
