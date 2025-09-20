import React from 'react';
import { Box, Typography, Paper } from '@mui/material';

const Prescriptions: React.FC = () => {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Prescriptions
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography>
          Prescription management interface will be implemented here.
        </Typography>
      </Paper>
    </Box>
  );
};

export default Prescriptions;
