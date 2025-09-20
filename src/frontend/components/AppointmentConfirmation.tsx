import React from 'react';
import { Box, Container, Paper, Typography } from '@mui/material';
import { useParams } from 'react-router-dom';

const AppointmentConfirmation: React.FC = () => {
  const { appointmentId } = useParams<{ appointmentId: string }>();

  return (
    <Container maxWidth="md" sx={{ py: 4 }}>
      <Paper sx={{ p: 4 }}>
        <Typography variant="h4" align="center" gutterBottom>
          Appointment Confirmed
        </Typography>
        <Typography>
          Your appointment has been confirmed. Appointment ID: {appointmentId}
        </Typography>
      </Paper>
    </Container>
  );
};

export default AppointmentConfirmation;
