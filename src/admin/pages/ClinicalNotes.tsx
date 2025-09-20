import React from 'react';
import { Box, Typography, Paper } from '@mui/material';

const ClinicalNotes: React.FC = () => {
  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Clinical Notes
      </Typography>
      <Paper sx={{ p: 3 }}>
        <Typography>
          Clinical notes management interface will be implemented here.
        </Typography>
      </Paper>
    </Box>
  );
};

export default ClinicalNotes;
