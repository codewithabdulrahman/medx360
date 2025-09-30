import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Layout from '@components/Layout';
import Dashboard from '@pages/Dashboard';
import Onboarding from '@pages/Onboarding';
import { 
  Clinics, 
  Hospitals, 
  Doctors, 
  Services, 
  Staff, 
  Bookings, 
  Consultations, 
  Payments, 
  Settings 
} from '@pages';

function App() {
  return (
    <div className="min-h-screen bg-gray-50">
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="clinics" element={<Clinics />} />
          <Route path="hospitals" element={<Hospitals />} />
          <Route path="doctors" element={<Doctors />} />
          <Route path="services" element={<Services />} />
          <Route path="staff" element={<Staff />} />
          <Route path="bookings" element={<Bookings />} />
          <Route path="consultations" element={<Consultations />} />
          <Route path="payments" element={<Payments />} />
          <Route path="settings" element={<Settings />} />
          <Route path="onboarding" element={<Onboarding />} />
        </Route>
      </Routes>
    </div>
  );
}

export default App;