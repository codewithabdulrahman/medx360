import React, { useState } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Paper,
  Button,
  IconButton,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Switch,
  FormControlLabel,
} from '@mui/material';
import {
  Notifications as NotificationIcon,
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Email as EmailIcon,
  Sms as SmsIcon,
  Send as SendIcon,
} from '@mui/icons-material';

const Notifications: React.FC = () => {
  const [openDialog, setOpenDialog] = useState(false);
  const [selectedTemplate, setSelectedTemplate] = useState(null);

  // Sample notification templates
  const templates = [
    {
      id: 1,
      name: 'Appointment Confirmation',
      type: 'email',
      subject: 'Appointment Confirmed - {{patient_name}}',
      content: 'Dear {{patient_name}}, your appointment with {{provider_name}} on {{appointment_date}} at {{appointment_time}} has been confirmed.',
      status: 'active',
      triggers: ['appointment_confirmed']
    },
    {
      id: 2,
      name: 'Appointment Reminder',
      type: 'sms',
      subject: 'Appointment Reminder',
      content: 'Reminder: You have an appointment with {{provider_name}} tomorrow at {{appointment_time}}. Please arrive 15 minutes early.',
      status: 'active',
      triggers: ['24_hours_before']
    },
    {
      id: 3,
      name: 'Payment Due',
      type: 'email',
      subject: 'Payment Due - {{amount}}',
      content: 'Dear {{patient_name}}, your payment of {{amount}} is due. Please make payment at your earliest convenience.',
      status: 'active',
      triggers: ['payment_due']
    },
    {
      id: 4,
      name: 'Appointment Cancelled',
      type: 'email',
      subject: 'Appointment Cancelled',
      content: 'Dear {{patient_name}}, your appointment on {{appointment_date}} has been cancelled. Please contact us to reschedule.',
      status: 'inactive',
      triggers: ['appointment_cancelled']
    },
  ];

  const notificationStats = {
    totalTemplates: templates.length,
    activeTemplates: templates.filter(t => t.status === 'active').length,
    emailsSent: 1250,
    smsSent: 850,
  };

  const getTypeIcon = (type: string) => {
    return type === 'email' ? <EmailIcon /> : <SmsIcon />;
  };

  const getTypeColor = (type: string) => {
    return type === 'email' ? 'primary' : 'secondary';
  };

  const handleAddTemplate = () => {
    setSelectedTemplate(null);
    setOpenDialog(true);
  };

  const handleEditTemplate = (template: any) => {
    setSelectedTemplate(template);
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setSelectedTemplate(null);
  };

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4">
          Notifications
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={handleAddTemplate}
        >
          Add Template
        </Button>
      </Box>

      <Grid container spacing={3}>
        {/* Notification Statistics */}
        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Total Templates
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {notificationStats.totalTemplates}
                  </Typography>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#1976d2',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <NotificationIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Active Templates
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {notificationStats.activeTemplates}
                  </Typography>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#388e3c',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <EmailIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Emails Sent
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {notificationStats.emailsSent}
                  </Typography>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#dc004e',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <EmailIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    SMS Sent
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {notificationStats.smsSent}
                  </Typography>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#f57c00',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <SmsIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Templates Table */}
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              Notification Templates
            </Typography>
            
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>Template Name</TableCell>
                    <TableCell>Type</TableCell>
                    <TableCell>Subject</TableCell>
                    <TableCell>Triggers</TableCell>
                    <TableCell>Status</TableCell>
                    <TableCell>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {templates.map((template) => (
                    <TableRow key={template.id}>
                      <TableCell>
                        <Typography variant="subtitle1" fontWeight="bold">
                          {template.name}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Chip
                          icon={getTypeIcon(template.type)}
                          label={template.type.toUpperCase()}
                          color={getTypeColor(template.type) as any}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">
                          {template.subject}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Box display="flex" flexWrap="wrap" gap={0.5}>
                          {template.triggers.map((trigger, index) => (
                            <Chip key={index} label={trigger} size="small" />
                          ))}
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={template.status}
                          color={template.status === 'active' ? 'success' : 'default'}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        <IconButton size="small" onClick={() => handleEditTemplate(template)}>
                          <EditIcon />
                        </IconButton>
                        <IconButton size="small">
                          <SendIcon />
                        </IconButton>
                        <IconButton size="small" color="error">
                          <DeleteIcon />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          </Paper>
        </Grid>
      </Grid>

      {/* Add/Edit Template Dialog */}
      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="md" fullWidth>
        <DialogTitle>
          {selectedTemplate ? 'Edit Template' : 'Add New Template'}
        </DialogTitle>
        <DialogContent>
          <Box sx={{ pt: 2 }}>
            <TextField
              fullWidth
              label="Template Name"
              margin="normal"
              variant="outlined"
              defaultValue={selectedTemplate?.name || ''}
            />
            <FormControl fullWidth margin="normal">
              <InputLabel>Type</InputLabel>
              <Select label="Type" defaultValue={selectedTemplate?.type || 'email'}>
                <MenuItem value="email">Email</MenuItem>
                <MenuItem value="sms">SMS</MenuItem>
              </Select>
            </FormControl>
            <TextField
              fullWidth
              label="Subject"
              margin="normal"
              variant="outlined"
              defaultValue={selectedTemplate?.subject || ''}
            />
            <TextField
              fullWidth
              label="Content"
              margin="normal"
              multiline
              rows={4}
              variant="outlined"
              defaultValue={selectedTemplate?.content || ''}
            />
            <TextField
              fullWidth
              label="Triggers (comma separated)"
              margin="normal"
              variant="outlined"
              defaultValue={selectedTemplate?.triggers?.join(', ') || ''}
            />
            <FormControl fullWidth margin="normal">
              <InputLabel>Status</InputLabel>
              <Select label="Status" defaultValue={selectedTemplate?.status || 'active'}>
                <MenuItem value="active">Active</MenuItem>
                <MenuItem value="inactive">Inactive</MenuItem>
              </Select>
            </FormControl>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseDialog}>Cancel</Button>
          <Button variant="contained" onClick={handleCloseDialog}>
            {selectedTemplate ? 'Update Template' : 'Add Template'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default Notifications;
