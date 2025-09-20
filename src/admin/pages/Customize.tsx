import React, { useState } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Paper,
  Button,
  Switch,
  FormControlLabel,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Divider,
  Avatar,
} from '@mui/material';
import {
  Palette as PaletteIcon,
  Image as ImageIcon,
  Settings as SettingsIcon,
  Visibility as PreviewIcon,
} from '@mui/icons-material';

const Customize: React.FC = () => {
  const [settings, setSettings] = useState({
    // Branding
    logo: '',
    favicon: '',
    primaryColor: '#1976d2',
    secondaryColor: '#dc004e',
    
    // Layout
    sidebarCollapsed: false,
    darkMode: false,
    
    // Booking Form
    showPatientPhoto: true,
    requireInsurance: false,
    allowOnlinePayment: true,
    
    // Notifications
    emailNotifications: true,
    smsNotifications: true,
    pushNotifications: false,
  });

  const handleSettingChange = (key: string, value: any) => {
    setSettings(prev => ({
      ...prev,
      [key]: value
    }));
  };

  const handleSaveSettings = () => {
    // Save settings logic here
    console.log('Saving settings:', settings);
  };

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4">
          Customize
        </Typography>
        <Button
          variant="contained"
          onClick={handleSaveSettings}
        >
          Save Changes
        </Button>
      </Box>

      <Grid container spacing={3}>
        {/* Branding Settings */}
        <Grid item xs={12} md={6}>
          <Paper sx={{ p: 3 }}>
            <Box display="flex" alignItems="center" mb={2}>
              <PaletteIcon sx={{ mr: 1 }} />
              <Typography variant="h6">Branding</Typography>
            </Box>
            
            <Box mb={3}>
              <Typography variant="subtitle1" gutterBottom>
                Logo Upload
              </Typography>
              <Box display="flex" alignItems="center" mb={2}>
                <Avatar
                  src={settings.logo}
                  sx={{ width: 60, height: 60, mr: 2 }}
                >
                  <ImageIcon />
                </Avatar>
                <Button variant="outlined" component="label">
                  Upload Logo
                  <input type="file" hidden accept="image/*" />
                </Button>
              </Box>
            </Box>

            <Box mb={3}>
              <Typography variant="subtitle1" gutterBottom>
                Favicon Upload
              </Typography>
              <Box display="flex" alignItems="center" mb={2}>
                <Avatar
                  src={settings.favicon}
                  sx={{ width: 32, height: 32, mr: 2 }}
                >
                  <ImageIcon />
                </Avatar>
                <Button variant="outlined" component="label">
                  Upload Favicon
                  <input type="file" hidden accept="image/*" />
                </Button>
              </Box>
            </Box>

            <Grid container spacing={2}>
              <Grid item xs={6}>
                <TextField
                  fullWidth
                  label="Primary Color"
                  type="color"
                  value={settings.primaryColor}
                  onChange={(e) => handleSettingChange('primaryColor', e.target.value)}
                />
              </Grid>
              <Grid item xs={6}>
                <TextField
                  fullWidth
                  label="Secondary Color"
                  type="color"
                  value={settings.secondaryColor}
                  onChange={(e) => handleSettingChange('secondaryColor', e.target.value)}
                />
              </Grid>
            </Grid>
          </Paper>
        </Grid>

        {/* Layout Settings */}
        <Grid item xs={12} md={6}>
          <Paper sx={{ p: 3 }}>
            <Box display="flex" alignItems="center" mb={2}>
              <SettingsIcon sx={{ mr: 1 }} />
              <Typography variant="h6">Layout</Typography>
            </Box>
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.sidebarCollapsed}
                  onChange={(e) => handleSettingChange('sidebarCollapsed', e.target.checked)}
                />
              }
              label="Collapsed Sidebar by Default"
            />
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.darkMode}
                  onChange={(e) => handleSettingChange('darkMode', e.target.checked)}
                />
              }
              label="Dark Mode"
            />
          </Paper>
        </Grid>

        {/* Booking Form Settings */}
        <Grid item xs={12} md={6}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" gutterBottom>
              Booking Form
            </Typography>
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.showPatientPhoto}
                  onChange={(e) => handleSettingChange('showPatientPhoto', e.target.checked)}
                />
              }
              label="Show Patient Photo Upload"
            />
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.requireInsurance}
                  onChange={(e) => handleSettingChange('requireInsurance', e.target.checked)}
                />
              }
              label="Require Insurance Information"
            />
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.allowOnlinePayment}
                  onChange={(e) => handleSettingChange('allowOnlinePayment', e.target.checked)}
                />
              }
              label="Allow Online Payment"
            />
          </Paper>
        </Grid>

        {/* Notification Settings */}
        <Grid item xs={12} md={6}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" gutterBottom>
              Notifications
            </Typography>
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.emailNotifications}
                  onChange={(e) => handleSettingChange('emailNotifications', e.target.checked)}
                />
              }
              label="Email Notifications"
            />
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.smsNotifications}
                  onChange={(e) => handleSettingChange('smsNotifications', e.target.checked)}
                />
              }
              label="SMS Notifications"
            />
            
            <FormControlLabel
              control={
                <Switch
                  checked={settings.pushNotifications}
                  onChange={(e) => handleSettingChange('pushNotifications', e.target.checked)}
                />
              }
              label="Push Notifications"
            />
          </Paper>
        </Grid>

        {/* Preview */}
        <Grid item xs={12}>
          <Paper sx={{ p: 3 }}>
            <Box display="flex" alignItems="center" mb={2}>
              <PreviewIcon sx={{ mr: 1 }} />
              <Typography variant="h6">Preview</Typography>
            </Box>
            
            <Box
              sx={{
                border: '2px dashed #ccc',
                borderRadius: 2,
                p: 4,
                textAlign: 'center',
                backgroundColor: '#f5f5f5',
              }}
            >
              <Typography color="text.secondary">
                Preview of your customized booking form will appear here
              </Typography>
              <Button variant="outlined" sx={{ mt: 2 }}>
                Preview Booking Form
              </Button>
            </Box>
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
};

export default Customize;
