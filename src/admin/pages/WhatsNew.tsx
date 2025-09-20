import React from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Paper,
  Chip,
  Button,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  Divider,
} from '@mui/material';
import {
  NewReleases as NewIcon,
  CheckCircle as CheckIcon,
  BugReport as BugIcon,
  Security as SecurityIcon,
  Speed as SpeedIcon,
  Star as StarIcon,
} from '@mui/icons-material';

const WhatsNew: React.FC = () => {
  const updates = [
    {
      version: '1.2.0',
      date: '2024-01-15',
      type: 'major',
      title: 'Major Update: Enhanced Calendar System',
      description: 'Complete overhaul of the calendar system with improved performance and new features.',
      features: [
        'New drag-and-drop appointment scheduling',
        'Improved calendar view with multiple layouts',
        'Enhanced appointment conflict detection',
        'Better mobile responsiveness',
      ],
      improvements: [
        'Fixed appointment reminder timing issues',
        'Improved calendar loading performance',
        'Enhanced timezone handling',
      ],
      bugs: [
        'Fixed calendar not updating in real-time',
        'Resolved appointment deletion confirmation dialog',
      ]
    },
    {
      version: '1.1.5',
      date: '2024-01-10',
      type: 'minor',
      title: 'Security and Performance Updates',
      description: 'Important security patches and performance improvements.',
      features: [
        'Enhanced data encryption for patient information',
        'Improved API response times',
      ],
      improvements: [
        'Better error handling in booking process',
        'Optimized database queries',
      ],
      bugs: [
        'Fixed patient data export issue',
        'Resolved notification delivery problems',
      ]
    },
    {
      version: '1.1.0',
      date: '2024-01-05',
      type: 'minor',
      title: 'New Features and Improvements',
      description: 'Added new customization options and improved user experience.',
      features: [
        'Custom field builder for patient forms',
        'Enhanced notification templates',
        'New reporting dashboard',
        'Improved mobile app interface',
      ],
      improvements: [
        'Better form validation',
        'Enhanced search functionality',
        'Improved accessibility features',
      ],
      bugs: [
        'Fixed appointment booking on mobile devices',
        'Resolved email template rendering issues',
      ]
    },
  ];

  const getVersionColor = (type: string) => {
    switch (type) {
      case 'major': return 'error';
      case 'minor': return 'warning';
      case 'patch': return 'info';
      default: return 'default';
    }
  };

  const getVersionIcon = (type: string) => {
    switch (type) {
      case 'major': return <NewIcon />;
      case 'minor': return <StarIcon />;
      case 'patch': return <CheckIcon />;
      default: return <CheckIcon />;
    }
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        What's New
      </Typography>
      
      <Typography variant="body1" color="text.secondary" paragraph>
        Stay up to date with the latest features, improvements, and bug fixes in MedX360.
      </Typography>

      <Grid container spacing={3}>
        {updates.map((update, index) => (
          <Grid item xs={12} key={index}>
            <Card>
              <CardContent>
                <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
                  <Box display="flex" alignItems="center">
                    <Chip
                      icon={getVersionIcon(update.type)}
                      label={`v${update.version}`}
                      color={getVersionColor(update.type) as any}
                      sx={{ mr: 2 }}
                    />
                    <Typography variant="h6">
                      {update.title}
                    </Typography>
                  </Box>
                  <Typography variant="body2" color="text.secondary">
                    {new Date(update.date).toLocaleDateString()}
                  </Typography>
                </Box>
                
                <Typography variant="body1" paragraph>
                  {update.description}
                </Typography>

                <Grid container spacing={3}>
                  {/* New Features */}
                  {update.features.length > 0 && (
                    <Grid item xs={12} md={4}>
                      <Paper sx={{ p: 2, backgroundColor: '#e8f5e8' }}>
                        <Typography variant="subtitle1" fontWeight="bold" gutterBottom>
                          <CheckIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
                          New Features
                        </Typography>
                        <List dense>
                          {update.features.map((feature, idx) => (
                            <ListItem key={idx} sx={{ py: 0.5 }}>
                              <ListItemIcon sx={{ minWidth: 32 }}>
                                <CheckIcon color="success" fontSize="small" />
                              </ListItemIcon>
                              <ListItemText
                                primary={feature}
                                primaryTypographyProps={{ variant: 'body2' }}
                              />
                            </ListItem>
                          ))}
                        </List>
                      </Paper>
                    </Grid>
                  )}

                  {/* Improvements */}
                  {update.improvements.length > 0 && (
                    <Grid item xs={12} md={4}>
                      <Paper sx={{ p: 2, backgroundColor: '#e3f2fd' }}>
                        <Typography variant="subtitle1" fontWeight="bold" gutterBottom>
                          <SpeedIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
                          Improvements
                        </Typography>
                        <List dense>
                          {update.improvements.map((improvement, idx) => (
                            <ListItem key={idx} sx={{ py: 0.5 }}>
                              <ListItemIcon sx={{ minWidth: 32 }}>
                                <SpeedIcon color="primary" fontSize="small" />
                              </ListItemIcon>
                              <ListItemText
                                primary={improvement}
                                primaryTypographyProps={{ variant: 'body2' }}
                              />
                            </ListItem>
                          ))}
                        </List>
                      </Paper>
                    </Grid>
                  )}

                  {/* Bug Fixes */}
                  {update.bugs.length > 0 && (
                    <Grid item xs={12} md={4}>
                      <Paper sx={{ p: 2, backgroundColor: '#fff3e0' }}>
                        <Typography variant="subtitle1" fontWeight="bold" gutterBottom>
                          <BugIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
                          Bug Fixes
                        </Typography>
                        <List dense>
                          {update.bugs.map((bug, idx) => (
                            <ListItem key={idx} sx={{ py: 0.5 }}>
                              <ListItemIcon sx={{ minWidth: 32 }}>
                                <BugIcon color="warning" fontSize="small" />
                              </ListItemIcon>
                              <ListItemText
                                primary={bug}
                                primaryTypographyProps={{ variant: 'body2' }}
                              />
                            </ListItem>
                          ))}
                        </List>
                      </Paper>
                    </Grid>
                  )}
                </Grid>
              </CardContent>
            </Card>
          </Grid>
        ))}

        {/* Coming Soon */}
        <Grid item xs={12}>
          <Card sx={{ backgroundColor: '#f5f5f5' }}>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Coming Soon
              </Typography>
              <Typography variant="body1" color="text.secondary" paragraph>
                We're working on exciting new features for future releases.
              </Typography>
              
              <List>
                <ListItem>
                  <ListItemIcon>
                    <SecurityIcon color="primary" />
                  </ListItemIcon>
                  <ListItemText
                    primary="Enhanced HIPAA Compliance Tools"
                    secondary="Advanced security features and compliance reporting"
                  />
                </ListItem>
                <ListItem>
                  <ListItemIcon>
                    <StarIcon color="primary" />
                  </ListItemIcon>
                  <ListItemText
                    primary="AI-Powered Appointment Scheduling"
                    secondary="Smart scheduling suggestions based on patient preferences"
                  />
                </ListItem>
                <ListItem>
                  <ListItemIcon>
                    <NewIcon color="primary" />
                  </ListItemIcon>
                  <ListItemText
                    primary="Telehealth Integration"
                    secondary="Built-in video consultation capabilities"
                  />
                </ListItem>
              </List>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
};

export default WhatsNew;
