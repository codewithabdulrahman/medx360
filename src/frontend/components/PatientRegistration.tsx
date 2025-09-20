import React from 'react';
import { Box, Container, Paper, Typography } from '@mui/material';

const PatientRegistration: React.FC = () => {
  return (
    <Container maxWidth="md" sx={{ py: 4 }}>
      <Paper sx={{ p: 4 }}>
        <Typography variant="h4" align="center" gutterBottom>
          Patient Registration
        </Typography>
        <Typography>
          Patient registration form will be implemented here.
        </Typography>
      </Paper>
    </Container>
  );
};

export default PatientRegistration;
