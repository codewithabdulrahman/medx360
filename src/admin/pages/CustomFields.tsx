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
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  DragIndicator as DragIcon,
  Star as StarIcon,
} from '@mui/icons-material';

const CustomFields: React.FC = () => {
  const [openDialog, setOpenDialog] = useState(false);
  const [selectedField, setSelectedField] = useState(null);

  // Sample custom fields data
  const customFields = [
    {
      id: 1,
      name: 'Emergency Contact',
      type: 'text',
      label: 'Emergency Contact Name',
      required: true,
      placeholder: 'Enter emergency contact name',
      status: 'active',
      order: 1
    },
    {
      id: 2,
      name: 'Medical History',
      type: 'textarea',
      label: 'Medical History',
      required: false,
      placeholder: 'Please describe your medical history',
      status: 'active',
      order: 2
    },
    {
      id: 3,
      name: 'Allergies',
      type: 'select',
      label: 'Known Allergies',
      required: true,
      options: ['None', 'Penicillin', 'Latex', 'Food Allergies', 'Other'],
      status: 'active',
      order: 3
    },
    {
      id: 4,
      name: 'Preferred Language',
      type: 'radio',
      label: 'Preferred Language',
      required: false,
      options: ['English', 'Spanish', 'French', 'Other'],
      status: 'inactive',
      order: 4
    },
  ];

  const fieldTypes = [
    { value: 'text', label: 'Text Input' },
    { value: 'textarea', label: 'Text Area' },
    { value: 'select', label: 'Dropdown' },
    { value: 'radio', label: 'Radio Buttons' },
    { value: 'checkbox', label: 'Checkboxes' },
    { value: 'date', label: 'Date Picker' },
    { value: 'number', label: 'Number Input' },
    { value: 'email', label: 'Email Input' },
    { value: 'phone', label: 'Phone Input' },
  ];

  const getStatusColor = (status: string) => {
    return status === 'active' ? 'success' : 'default';
  };

  const handleAddField = () => {
    setSelectedField(null);
    setOpenDialog(true);
  };

  const handleEditField = (field: any) => {
    setSelectedField(field);
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setSelectedField(null);
  };

  const activeFields = customFields.filter(field => field.status === 'active');

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4">
          Custom Fields
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={handleAddField}
        >
          Add Field
        </Button>
      </Box>

      <Grid container spacing={3}>
        {/* Custom Fields Statistics */}
        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Total Fields
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {customFields.length}
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
                  <StarIcon />
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
                    Active Fields
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {activeFields.length}
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
                  <StarIcon />
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
                    Required Fields
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {customFields.filter(f => f.required).length}
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
                  <StarIcon />
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
                    Field Types
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {fieldTypes.length}
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
                  <StarIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Custom Fields Table */}
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              Custom Fields
            </Typography>
            
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>Order</TableCell>
                    <TableCell>Field Name</TableCell>
                    <TableCell>Type</TableCell>
                    <TableCell>Label</TableCell>
                    <TableCell>Required</TableCell>
                    <TableCell>Status</TableCell>
                    <TableCell>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {customFields.map((field) => (
                    <TableRow key={field.id}>
                      <TableCell>
                        <Box display="flex" alignItems="center">
                          <DragIcon sx={{ mr: 1, cursor: 'move' }} />
                          {field.order}
                        </Box>
                      </TableCell>
                      <TableCell>
                        <Typography variant="subtitle1" fontWeight="bold">
                          {field.name}
                        </Typography>
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={fieldTypes.find(ft => ft.value === field.type)?.label || field.type}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>{field.label}</TableCell>
                      <TableCell>
                        <Chip
                          label={field.required ? 'Yes' : 'No'}
                          color={field.required ? 'error' : 'default'}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={field.status}
                          color={getStatusColor(field.status) as any}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        <IconButton size="small" onClick={() => handleEditField(field)}>
                          <EditIcon />
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

      {/* Add/Edit Field Dialog */}
      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="md" fullWidth>
        <DialogTitle>
          {selectedField ? 'Edit Field' : 'Add New Field'}
        </DialogTitle>
        <DialogContent>
          <Box sx={{ pt: 2 }}>
            <TextField
              fullWidth
              label="Field Name"
              margin="normal"
              variant="outlined"
              defaultValue={selectedField?.name || ''}
            />
            <FormControl fullWidth margin="normal">
              <InputLabel>Field Type</InputLabel>
              <Select label="Field Type" defaultValue={selectedField?.type || 'text'}>
                {fieldTypes.map(type => (
                  <MenuItem key={type.value} value={type.value}>{type.label}</MenuItem>
                ))}
              </Select>
            </FormControl>
            <TextField
              fullWidth
              label="Field Label"
              margin="normal"
              variant="outlined"
              defaultValue={selectedField?.label || ''}
            />
            <TextField
              fullWidth
              label="Placeholder Text"
              margin="normal"
              variant="outlined"
              defaultValue={selectedField?.placeholder || ''}
            />
            <TextField
              fullWidth
              label="Options (one per line)"
              margin="normal"
              multiline
              rows={3}
              variant="outlined"
              defaultValue={selectedField?.options?.join('\n') || ''}
            />
            <Box sx={{ mt: 2 }}>
              <FormControlLabel
                control={<Switch defaultChecked={selectedField?.required || false} />}
                label="Required Field"
              />
            </Box>
            <FormControl fullWidth margin="normal">
              <InputLabel>Status</InputLabel>
              <Select label="Status" defaultValue={selectedField?.status || 'active'}>
                <MenuItem value="active">Active</MenuItem>
                <MenuItem value="inactive">Inactive</MenuItem>
              </Select>
            </FormControl>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseDialog}>Cancel</Button>
          <Button variant="contained" onClick={handleCloseDialog}>
            {selectedField ? 'Update Field' : 'Add Field'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default CustomFields;
