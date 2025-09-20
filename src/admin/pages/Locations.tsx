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
  LocationOn as LocationIcon,
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Visibility as ViewIcon,
  MeetingRoom as RoomIcon,
  Star as StarIcon,
} from '@mui/icons-material';

const Locations: React.FC = () => {
  const [openDialog, setOpenDialog] = useState(false);
  const [selectedLocation, setSelectedLocation] = useState(null);

  // Sample locations data
  const locations = [
    {
      id: 1,
      name: 'Main Clinic',
      address: '123 Medical Center Dr, City, State 12345',
      phone: '(555) 123-4567',
      email: 'main@medx360.com',
      status: 'active',
      rooms: 8,
      capacity: 50,
      features: ['Parking', 'Wheelchair Access', 'WiFi', 'Pharmacy'],
      isPrimary: true
    },
    {
      id: 2,
      name: 'Downtown Branch',
      address: '456 Business Ave, City, State 12345',
      phone: '(555) 234-5678',
      email: 'downtown@medx360.com',
      status: 'active',
      rooms: 5,
      capacity: 30,
      features: ['Parking', 'WiFi'],
      isPrimary: false
    },
    {
      id: 3,
      name: 'Emergency Center',
      address: '789 Emergency St, City, State 12345',
      phone: '(555) 345-6789',
      email: 'emergency@medx360.com',
      status: 'active',
      rooms: 12,
      capacity: 75,
      features: ['24/7 Access', 'Ambulance Bay', 'Helipad', 'ICU'],
      isPrimary: false
    },
  ];

  const getStatusColor = (status: string) => {
    return status === 'active' ? 'success' : 'default';
  };

  const handleAddLocation = () => {
    setSelectedLocation(null);
    setOpenDialog(true);
  };

  const handleEditLocation = (location: any) => {
    setSelectedLocation(location);
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setSelectedLocation(null);
  };

  const activeLocations = locations.filter(location => location.status === 'active');
  const totalRooms = locations.reduce((sum, location) => sum + location.rooms, 0);
  const totalCapacity = locations.reduce((sum, location) => sum + location.capacity, 0);

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4">
          Locations
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={handleAddLocation}
        >
          Add Location
        </Button>
      </Box>

      <Grid container spacing={3}>
        {/* Location Statistics */}
        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Total Locations
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {locations.length}
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
                  <LocationIcon />
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
                    Active Locations
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {activeLocations.length}
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
                  <RoomIcon />
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
                    Total Rooms
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {totalRooms}
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
                  <RoomIcon />
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
                    Total Capacity
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {totalCapacity}
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
                  <LocationIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Locations Table */}
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              All Locations
            </Typography>
            
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>Location Name</TableCell>
                    <TableCell>Address</TableCell>
                    <TableCell>Contact</TableCell>
                    <TableCell>Rooms</TableCell>
                    <TableCell>Capacity</TableCell>
                    <TableCell>Features</TableCell>
                    <TableCell>Status</TableCell>
                    <TableCell>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {locations.map((location) => (
                    <TableRow key={location.id}>
                      <TableCell>
                        <Box display="flex" alignItems="center">
                          <Typography variant="subtitle1" fontWeight="bold">
                            {location.name}
                          </Typography>
                          {location.isPrimary && (
                            <StarIcon sx={{ ml: 1, color: '#ff9800' }} />
                          )}
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">
                          {location.address}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Typography variant="body2">
                          {location.phone}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          {location.email}
                        </Typography>
                      </TableCell>
                      <TableCell>{location.rooms}</TableCell>
                      <TableCell>{location.capacity}</TableCell>
                      <TableCell>
                        <Box display="flex" flexWrap="wrap" gap={0.5}>
                          {location.features.map((feature, index) => (
                            <Chip key={index} label={feature} size="small" />
                          ))}
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={location.status}
                          color={getStatusColor(location.status) as any}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        <IconButton size="small" onClick={() => handleEditLocation(location)}>
                          <EditIcon />
                        </IconButton>
                        <IconButton size="small">
                          <ViewIcon />
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

      {/* Add/Edit Location Dialog */}
      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="md" fullWidth>
        <DialogTitle>
          {selectedLocation ? 'Edit Location' : 'Add New Location'}
        </DialogTitle>
        <DialogContent>
          <Box sx={{ pt: 2 }}>
            <TextField
              fullWidth
              label="Location Name"
              margin="normal"
              variant="outlined"
              defaultValue={selectedLocation?.name || ''}
            />
            <TextField
              fullWidth
              label="Address"
              margin="normal"
              variant="outlined"
              defaultValue={selectedLocation?.address || ''}
            />
            <Grid container spacing={2}>
              <Grid item xs={6}>
                <TextField
                  fullWidth
                  label="Phone"
                  margin="normal"
                  variant="outlined"
                  defaultValue={selectedLocation?.phone || ''}
                />
              </Grid>
              <Grid item xs={6}>
                <TextField
                  fullWidth
                  label="Email"
                  margin="normal"
                  variant="outlined"
                  defaultValue={selectedLocation?.email || ''}
                />
              </Grid>
            </Grid>
            <Grid container spacing={2}>
              <Grid item xs={6}>
                <TextField
                  fullWidth
                  label="Number of Rooms"
                  type="number"
                  margin="normal"
                  variant="outlined"
                  defaultValue={selectedLocation?.rooms || ''}
                />
              </Grid>
              <Grid item xs={6}>
                <TextField
                  fullWidth
                  label="Capacity"
                  type="number"
                  margin="normal"
                  variant="outlined"
                  defaultValue={selectedLocation?.capacity || ''}
                />
              </Grid>
            </Grid>
            <TextField
              fullWidth
              label="Features (comma separated)"
              margin="normal"
              variant="outlined"
              defaultValue={selectedLocation?.features?.join(', ') || ''}
            />
            <Box sx={{ mt: 2 }}>
              <FormControlLabel
                control={<Switch defaultChecked={selectedLocation?.isPrimary || false} />}
                label="Primary Location"
              />
            </Box>
            <FormControl fullWidth margin="normal">
              <InputLabel>Status</InputLabel>
              <Select label="Status" defaultValue={selectedLocation?.status || 'active'}>
                <MenuItem value="active">Active</MenuItem>
                <MenuItem value="inactive">Inactive</MenuItem>
              </Select>
            </FormControl>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseDialog}>Cancel</Button>
          <Button variant="contained" onClick={handleCloseDialog}>
            {selectedLocation ? 'Update Location' : 'Add Location'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default Locations;
