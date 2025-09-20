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
} from '@mui/material';
import {
  CalendarToday as CalendarIcon,
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  ArrowBackIos as ArrowBackIcon,
  ArrowForwardIos as ArrowForwardIcon,
} from '@mui/icons-material';

const Calendar: React.FC = () => {
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [openDialog, setOpenDialog] = useState(false);
  const [selectedTimeSlot, setSelectedTimeSlot] = useState('');

  // Sample calendar data
  const calendarEvents = [
    { id: 1, date: '2024-01-15', time: '09:00', patient: 'John Doe', provider: 'Dr. Smith', status: 'confirmed' },
    { id: 2, date: '2024-01-15', time: '10:30', patient: 'Jane Smith', provider: 'Dr. Johnson', status: 'pending' },
    { id: 3, date: '2024-01-16', time: '14:00', patient: 'Bob Wilson', provider: 'Dr. Brown', status: 'confirmed' },
  ];

  const timeSlots = [
    '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
    '16:00', '16:30', '17:00', '17:30', '18:00'
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'confirmed': return 'success';
      case 'pending': return 'warning';
      case 'cancelled': return 'error';
      default: return 'default';
    }
  };

  const handlePreviousMonth = () => {
    const newDate = new Date(selectedDate);
    newDate.setMonth(newDate.getMonth() - 1);
    setSelectedDate(newDate);
  };

  const handleNextMonth = () => {
    const newDate = new Date(selectedDate);
    newDate.setMonth(newDate.getMonth() + 1);
    setSelectedDate(newDate);
  };

  const handleAddAppointment = () => {
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setSelectedTimeSlot('');
  };

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4">
          Calendar
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={handleAddAppointment}
        >
          Add Appointment
        </Button>
      </Box>

      <Grid container spacing={3}>
        {/* Calendar Navigation */}
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: 2 }}>
            <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
              <Typography variant="h6">
                {selectedDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
              </Typography>
              <Box>
                <IconButton onClick={handlePreviousMonth}>
                  <ArrowBackIcon />
                </IconButton>
                <IconButton onClick={handleNextMonth}>
                  <ArrowForwardIcon />
                </IconButton>
              </Box>
            </Box>

            {/* Calendar Grid */}
            <Grid container spacing={1}>
              {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((day) => (
                <Grid item xs key={day}>
                  <Typography variant="subtitle2" align="center" fontWeight="bold">
                    {day}
                  </Typography>
                </Grid>
              ))}
              
              {/* Calendar Days */}
              {Array.from({ length: 35 }, (_, i) => {
                const day = i - 6; // Start from Sunday
                const date = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), day);
                const isCurrentMonth = date.getMonth() === selectedDate.getMonth();
                const isToday = date.toDateString() === new Date().toDateString();
                
                return (
                  <Grid item xs key={i}>
                    <Box
                      sx={{
                        minHeight: 60,
                        border: '1px solid #e0e0e0',
                        p: 1,
                        backgroundColor: isCurrentMonth ? 'white' : '#f5f5f5',
                        backgroundColor: isToday ? '#e3f2fd' : undefined,
                      }}
                    >
                      <Typography variant="body2" color={isCurrentMonth ? 'text.primary' : 'text.secondary'}>
                        {date.getDate()}
                      </Typography>
                      
                      {/* Events for this day */}
                      {calendarEvents
                        .filter(event => event.date === date.toISOString().split('T')[0])
                        .map(event => (
                          <Chip
                            key={event.id}
                            label={`${event.time} - ${event.patient}`}
                            size="small"
                            color={getStatusColor(event.status) as any}
                            sx={{ fontSize: '0.7rem', height: 16, mb: 0.5 }}
                          />
                        ))}
                    </Box>
                  </Grid>
                );
              })}
            </Grid>
          </Paper>
        </Grid>

        {/* Today's Appointments */}
        <Grid item xs={12} md={4}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              Today's Appointments
            </Typography>
            
            {calendarEvents
              .filter(event => event.date === new Date().toISOString().split('T')[0])
              .map(event => (
                <Card key={event.id} sx={{ mb: 2 }}>
                  <CardContent sx={{ p: 2, '&:last-child': { pb: 2 } }}>
                    <Box display="flex" justifyContent="space-between" alignItems="center">
                      <Box>
                        <Typography variant="subtitle1" fontWeight="bold">
                          {event.patient}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          {event.time} - {event.provider}
                        </Typography>
                      </Box>
                      <Box>
                        <Chip
                          label={event.status}
                          color={getStatusColor(event.status) as any}
                          size="small"
                        />
                        <IconButton size="small" sx={{ ml: 1 }}>
                          <EditIcon />
                        </IconButton>
                      </Box>
                    </Box>
                  </CardContent>
                </Card>
              ))}
            
            {calendarEvents.filter(event => event.date === new Date().toISOString().split('T')[0]).length === 0 && (
              <Typography color="text.secondary" align="center">
                No appointments scheduled for today
              </Typography>
            )}
          </Paper>
        </Grid>
      </Grid>

      {/* Add Appointment Dialog */}
      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="sm" fullWidth>
        <DialogTitle>Add New Appointment</DialogTitle>
        <DialogContent>
          <Box sx={{ pt: 2 }}>
            <TextField
              fullWidth
              label="Patient Name"
              margin="normal"
              variant="outlined"
            />
            <FormControl fullWidth margin="normal">
              <InputLabel>Provider</InputLabel>
              <Select label="Provider">
                <MenuItem value="dr-smith">Dr. Smith</MenuItem>
                <MenuItem value="dr-johnson">Dr. Johnson</MenuItem>
                <MenuItem value="dr-brown">Dr. Brown</MenuItem>
              </Select>
            </FormControl>
            <FormControl fullWidth margin="normal">
              <InputLabel>Time Slot</InputLabel>
              <Select
                label="Time Slot"
                value={selectedTimeSlot}
                onChange={(e) => setSelectedTimeSlot(e.target.value)}
              >
                {timeSlots.map(slot => (
                  <MenuItem key={slot} value={slot}>{slot}</MenuItem>
                ))}
              </Select>
            </FormControl>
            <TextField
              fullWidth
              label="Notes"
              margin="normal"
              multiline
              rows={3}
              variant="outlined"
            />
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseDialog}>Cancel</Button>
          <Button variant="contained" onClick={handleCloseDialog}>
            Add Appointment
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default Calendar;
