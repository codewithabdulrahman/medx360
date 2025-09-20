import React from 'react';
import { Box, Typography, Paper } from '@mui/material';

const Reports: React.FC = () => {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Reports
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography>
          Reports and analytics interface will be implemented here.
        </Typography>
      </Paper>
    </Box>
  );
};

export default Reports;
