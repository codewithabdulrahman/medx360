import React, { useState } from 'react';
import {
  Box,
  Container,
  Paper,
  Typography,
  Stepper,
  Step,
  StepLabel,
  Button,
  Grid,
} from '@mui/material';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

// Form validation schema
const bookingSchema = z.object({
  provider_id: z.number().min(1, 'Please select a provider'),
  service_id: z.number().min(1, 'Please select a service'),
  appointment_date: z.string().min(1, 'Please select a date'),
  appointment_time: z.string().min(1, 'Please select a time'),
  patient_info: z.object({
    first_name: z.string().min(1, 'First name is required'),
    last_name: z.string().min(1, 'Last name is required'),
    email: z.string().email('Please enter a valid email'),
    phone: z.string().min(1, 'Phone number is required'),
    date_of_birth: z.string().min(1, 'Date of birth is required'),
  }),
});

type BookingFormData = z.infer<typeof bookingSchema>;

interface BookingFormProps {
  config: any;
}

const BookingForm: React.FC<BookingFormProps> = ({ config }) => {
  const [activeStep, setActiveStep] = useState(0);
  const [selectedProvider, setSelectedProvider] = useState<number | null>(null);
  const [selectedService, setSelectedService] = useState<number | null>(null);
  const [selectedDate, setSelectedDate] = useState<string>('');
  const [selectedTime, setSelectedTime] = useState<string>('');

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
    watch,
  } = useForm<BookingFormData>({
    resolver: zodResolver(bookingSchema),
  });

  const steps = [
    'Select Provider',
    'Select Service',
    'Choose Date & Time',
    'Patient Information',
    'Confirm Booking',
  ];

  const handleNext = () => {
    setActiveStep((prevActiveStep) => prevActiveStep + 1);
  };

  const handleBack = () => {
    setActiveStep((prevActiveStep) => prevActiveStep - 1);
  };

  const onSubmit = (data: BookingFormData) => {
    console.log('Booking data:', data);
    // TODO: Submit booking to API
  };

  const renderStepContent = (step: number) => {
    switch (step) {
      case 0:
        return (
          <Box>
            <Typography variant="h6" gutterBottom>
              Select Healthcare Provider
            </Typography>
            <Typography color="textSecondary" paragraph>
              Choose the healthcare provider you would like to see.
            </Typography>
            {/* TODO: Add provider selection component */}
            <Button
              variant="contained"
              onClick={() => {
                setSelectedProvider(1);
                setValue('provider_id', 1);
                handleNext();
              }}
            >
              Select Provider
            </Button>
          </Box>
        );

      case 1:
        return (
          <Box>
            <Typography variant="h6" gutterBottom>
              Select Service
            </Typography>
            <Typography color="textSecondary" paragraph>
              Choose the type of service you need.
            </Typography>
            {/* TODO: Add service selection component */}
            <Button
              variant="contained"
              onClick={() => {
                setSelectedService(1);
                setValue('service_id', 1);
                handleNext();
              }}
            >
              Select Service
            </Button>
          </Box>
        );

      case 2:
        return (
          <Box>
            <Typography variant="h6" gutterBottom>
              Choose Date & Time
            </Typography>
            <Typography color="textSecondary" paragraph>
              Select your preferred appointment date and time.
            </Typography>
            {/* TODO: Add date/time selection component */}
            <Button
              variant="contained"
              onClick={() => {
                setSelectedDate('2024-01-15');
                setSelectedTime('10:00');
                setValue('appointment_date', '2024-01-15');
                setValue('appointment_time', '10:00');
                handleNext();
              }}
            >
              Select Date & Time
            </Button>
          </Box>
        );

      case 3:
        return (
          <Box>
            <Typography variant="h6" gutterBottom>
              Patient Information
            </Typography>
            <Typography color="textSecondary" paragraph>
              Please provide your contact information.
            </Typography>
            {/* TODO: Add patient information form */}
            <Button
              variant="contained"
              onClick={() => {
                setValue('patient_info.first_name', 'John');
                setValue('patient_info.last_name', 'Doe');
                setValue('patient_info.email', 'john@example.com');
                setValue('patient_info.phone', '123-456-7890');
                setValue('patient_info.date_of_birth', '1990-01-01');
                handleNext();
              }}
            >
              Continue
            </Button>
          </Box>
        );

      case 4:
        return (
          <Box>
            <Typography variant="h6" gutterBottom>
              Confirm Your Booking
            </Typography>
            <Typography color="textSecondary" paragraph>
              Please review your appointment details before confirming.
            </Typography>
            {/* TODO: Add confirmation details */}
            <Button
              variant="contained"
              onClick={handleSubmit(onSubmit)}
            >
              Confirm Booking
            </Button>
          </Box>
        );

      default:
        return 'Unknown step';
    }
  };

  return (
    <Container maxWidth="md" sx={{ py: 4 }}>
      <Paper sx={{ p: 4 }}>
        <Typography variant="h4" align="center" gutterBottom>
          Book Your Appointment
        </Typography>
        
        <Stepper activeStep={activeStep} sx={{ mb: 4 }}>
          {steps.map((label) => (
            <Step key={label}>
              <StepLabel>{label}</StepLabel>
            </Step>
          ))}
        </Stepper>

        <Box sx={{ mb: 4 }}>
          {renderStepContent(activeStep)}
        </Box>

        <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
          <Button
            disabled={activeStep === 0}
            onClick={handleBack}
            sx={{ mr: 1 }}
          >
            Back
          </Button>
          <Box />
        </Box>
      </Paper>
    </Container>
  );
};

export default BookingForm;
