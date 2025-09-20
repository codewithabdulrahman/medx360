import React from 'react';
import { Box, Typography, Paper } from '@mui/material';

const Settings: React.FC = () => {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Settings
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography>
          Plugin settings interface will be implemented here.
        </Typography>
      </Paper>
    </Box>
  );
};

export default Settings;
