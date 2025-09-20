import React from 'react';
import { Routes, Route } from 'react-router-dom';
import { Box } from '@mui/material';

import BookingForm from './components/BookingForm';
import PatientRegistration from './components/PatientRegistration';
import AppointmentConfirmation from './components/AppointmentConfirmation';

const App: React.FC = () => {
  // Get configuration from data attribute
  const container = document.getElementById('medx360-booking-app');
  const config = container?.getAttribute('data-config');
  const parsedConfig = config ? JSON.parse(config) : {};

  return (
    <Box sx={{ minHeight: '100vh', backgroundColor: '#f5f5f5' }}>
      <Routes>
        <Route path="/" element={<BookingForm config={parsedConfig} />} />
        <Route path="/register" element={<PatientRegistration />} />
        <Route path="/confirm/:appointmentId" element={<AppointmentConfirmation />} />
      </Routes>
    </Box>
  );
};

export default App;
