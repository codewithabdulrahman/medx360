import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
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

export default function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="/dashboard" element={<Dashboard />} />
          
          {/* Epic 1: Core Booking System (Free) */}
          <Route path="/booking" element={<BookingCalendar />} />
          <Route path="/booking/new" element={<BookingForm />} />
          <Route path="/booking/list" element={<BookingList />} />
          
          {/* Epic 2: Patient Management (Free) */}
          <Route path="/patients" element={<PatientList />} />
          <Route path="/patients/new" element={<PatientForm />} />
          <Route path="/patients/:id" element={<PatientProfile />} />
          
          {/* Epic 3: Payment & Billing (Free) */}
          <Route path="/payments" element={<PaymentList />} />
          <Route path="/payments/new" element={<PaymentForm />} />
          <Route path="/billing" element={<BillingDashboard />} />
          
          {/* Epic 4: Staff Management (Free) */}
          <Route path="/staff" element={<StaffList />} />
          <Route path="/staff/new" element={<StaffForm />} />
          <Route path="/staff/schedule" element={<StaffSchedule />} />
          
          {/* Epic 5: Notifications (Free) */}
          <Route path="/notifications" element={<NotificationCenter />} />
          <Route path="/notifications/settings" element={<NotificationSettings />} />
          
          {/* Epic 6: Reporting (Free) */}
          <Route path="/reports" element={<ReportsDashboard />} />
          <Route path="/reports/appointments" element={<AppointmentReports />} />
          <Route path="/reports/financial" element={<FinancialReports />} />
          
          {/* Epic 7: Roles & Permissions (Free) */}
          <Route path="/roles" element={<RoleManagement />} />
          <Route path="/permissions" element={<PermissionSettings />} />
          
          {/* Epic 8: UI/UX Enhancements (Free) */}
          <Route path="/settings" element={<Settings />} />
          <Route path="/profile" element={<Profile />} />
          
          {/* Epic 9-14: Paid Features */}
          <Route path="/multi-location" element={<MultiLocation />} />
          <Route path="/advanced-staff" element={<AdvancedStaff />} />
          <Route path="/advanced-notifications" element={<AdvancedNotifications />} />
          <Route path="/integrations" element={<Integrations />} />
          <Route path="/advanced-payments" element={<AdvancedPayments />} />
          <Route path="/advanced-reports" element={<AdvancedReports />} />
        </Routes>
      </Layout>
    </Router>
  );
}
