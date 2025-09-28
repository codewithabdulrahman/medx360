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
  // Debug logging
  console.log('App component rendering');
  console.log('Current location:', window.location);
  console.log('WordPress data:', window.medx360);
  
  // Simple test component
  const TestComponent = () => (
    <div className="p-8">
      <h1 className="text-2xl font-bold text-gray-900 mb-4">MedX360 Test</h1>
      <p className="text-gray-600">React app is working!</p>
      <p className="text-sm text-gray-500 mt-2">Location: {window.location.href}</p>
    </div>
  );
  
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
        {/* Fallback route for testing */}
        <Route path="/test" element={<TestComponent />} />
      </Routes>
    </div>
  );
}

export default App;