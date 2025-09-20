import React from 'react';
import { Box, Typography, Paper } from '@mui/material';

const Providers: React.FC = () => {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Healthcare Providers
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography>
          Provider management interface will be implemented here.
        </Typography>
      </Paper>
    </Box>
  );
};

export default Providers;
