import React from 'react';
import { Box, Typography, Paper } from '@mui/material';

const Appointments: React.FC = () => {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Appointments
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography>
          Appointment management interface will be implemented here.
        </Typography>
      </Paper>
    </Box>
  );
};

export default Appointments;
