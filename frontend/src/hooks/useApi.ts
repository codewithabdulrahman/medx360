import { useQuery, useMutation, useQueryClient } from 'react-query';
import { toast } from 'react-hot-toast';
import apiService from './api';
import {
  Clinic,
  Hospital,
  Doctor,
  Service,
  Staff,
  Booking,
  Consultation,
  Payment,
  DoctorSchedule,
  DoctorAvailability,
  OnboardingStatus,
  OnboardingStatistics,
  PluginSettings,
  CreateClinicData,
  CreateDoctorData,
  CreateBookingData,
  ApiError
} from '../types';

// Query Keys
export const queryKeys = {
  clinics: ['clinics'] as const,
  clinic: (id: number) => ['clinics', id] as const,
  clinicBySlug: (slug: string) => ['clinics', 'slug', slug] as const,
  hospitals: ['hospitals'] as const,
  hospital: (id: number) => ['hospitals', id] as const,
  hospitalsByClinic: (clinicId: number) => ['hospitals', 'clinic', clinicId] as const,
  doctors: ['doctors'] as const,
  doctor: (id: number) => ['doctors', id] as const,
  doctorsByClinic: (clinicId: number) => ['doctors', 'clinic', clinicId] as const,
  doctorsByHospital: (hospitalId: number) => ['doctors', 'hospital', hospitalId] as const,
  doctorSchedule: (doctorId: number) => ['doctors', doctorId, 'schedule'] as const,
  doctorAvailability: (doctorId: number) => ['doctors', doctorId, 'availability'] as const,
  services: ['services'] as const,
  service: (id: number) => ['services', id] as const,
  servicesByClinic: (clinicId: number) => ['services', 'clinic', clinicId] as const,
  servicesByHospital: (hospitalId: number) => ['services', 'hospital', hospitalId] as const,
  staff: ['staff'] as const,
  staffMember: (id: number) => ['staff', id] as const,
  staffByClinic: (clinicId: number) => ['staff', 'clinic', clinicId] as const,
  bookings: ['bookings'] as const,
  booking: (id: number) => ['bookings', id] as const,
  bookingsByClinic: (clinicId: number) => ['bookings', 'clinic', clinicId] as const,
  bookingsByDoctor: (doctorId: number) => ['bookings', 'doctor', doctorId] as const,
  consultations: ['consultations'] as const,
  consultation: (id: number) => ['consultations', id] as const,
  consultationsByBooking: (bookingId: number) => ['consultations', 'booking', bookingId] as const,
  consultationsByDoctor: (doctorId: number) => ['consultations', 'doctor', doctorId] as const,
  payments: ['payments'] as const,
  payment: (id: number) => ['payments', id] as const,
  paymentsByBooking: (bookingId: number) => ['payments', 'booking', bookingId] as const,
  onboardingStatus: ['onboarding', 'status'] as const,
  onboardingSteps: ['onboarding', 'steps'] as const,
  onboardingProgress: ['onboarding', 'progress'] as const,
  onboardingStatistics: ['onboarding', 'statistics'] as const,
  settings: ['settings'] as const,
};

// Clinics Hooks
export const useClinics = () => {
  return useQuery(queryKeys.clinics, () => apiService.getClinics(), {
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

export const useClinic = (id: number) => {
  return useQuery(queryKeys.clinic(id), () => apiService.getClinic(id), {
    enabled: !!id,
  });
};

export const useClinicBySlug = (slug: string) => {
  return useQuery(queryKeys.clinicBySlug(slug), () => apiService.getClinicBySlug(slug), {
    enabled: !!slug,
  });
};

export const useCreateClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: CreateClinicData) => apiService.createClinic(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.clinics);
        toast.success('Clinic created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create clinic');
      },
    }
  );
};

export const useUpdateClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<CreateClinicData> }) => 
      apiService.updateClinic(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.clinic(id));
        queryClient.invalidateQueries(queryKeys.clinics);
        toast.success('Clinic updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update clinic');
      },
    }
  );
};

export const useDeleteClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.deleteClinic(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.clinics);
        toast.success('Clinic deleted successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to delete clinic');
      },
    }
  );
};

// Hospitals Hooks
export const useHospitals = () => {
  return useQuery(queryKeys.hospitals, () => apiService.getHospitals(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const useHospital = (id: number) => {
  return useQuery(queryKeys.hospital(id), () => apiService.getHospital(id), {
    enabled: !!id,
  });
};

export const useHospitalsByClinic = (clinicId: number) => {
  return useQuery(queryKeys.hospitalsByClinic(clinicId), () => apiService.getHospitalsByClinic(clinicId), {
    enabled: !!clinicId,
  });
};

export const useCreateHospital = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: Partial<Hospital>) => apiService.createHospital(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.hospitals);
        toast.success('Hospital created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create hospital');
      },
    }
  );
};

export const useUpdateHospital = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<Hospital> }) => 
      apiService.updateHospital(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.hospital(id));
        queryClient.invalidateQueries(queryKeys.hospitals);
        toast.success('Hospital updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update hospital');
      },
    }
  );
};

export const useDeleteHospital = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.deleteHospital(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.hospitals);
        toast.success('Hospital deleted successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to delete hospital');
      },
    }
  );
};

// Doctors Hooks
export const useDoctors = () => {
  return useQuery(queryKeys.doctors, () => apiService.getDoctors(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const useDoctor = (id: number) => {
  return useQuery(queryKeys.doctor(id), () => apiService.getDoctor(id), {
    enabled: !!id,
  });
};

export const useDoctorsByClinic = (clinicId: number) => {
  return useQuery(queryKeys.doctorsByClinic(clinicId), () => apiService.getDoctorsByClinic(clinicId), {
    enabled: !!clinicId,
  });
};

export const useDoctorsByHospital = (hospitalId: number) => {
  return useQuery(queryKeys.doctorsByHospital(hospitalId), () => apiService.getDoctorsByHospital(hospitalId), {
    enabled: !!hospitalId,
  });
};

export const useCreateDoctor = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: CreateDoctorData) => apiService.createDoctor(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.doctors);
        toast.success('Doctor created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create doctor');
      },
    }
  );
};

export const useUpdateDoctor = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<CreateDoctorData> }) => 
      apiService.updateDoctor(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.doctor(id));
        queryClient.invalidateQueries(queryKeys.doctors);
        toast.success('Doctor updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update doctor');
      },
    }
  );
};

export const useDeleteDoctor = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.deleteDoctor(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.doctors);
        toast.success('Doctor deleted successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to delete doctor');
      },
    }
  );
};

// Doctor Schedule Hooks
export const useDoctorSchedule = (doctorId: number) => {
  return useQuery(queryKeys.doctorSchedule(doctorId), () => apiService.getDoctorSchedule(doctorId), {
    enabled: !!doctorId,
  });
};

export const useCreateDoctorSchedule = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ doctorId, data }: { doctorId: number; data: Partial<DoctorSchedule> }) => 
      apiService.createDoctorSchedule(doctorId, data),
    {
      onSuccess: (_, { doctorId }) => {
        queryClient.invalidateQueries(queryKeys.doctorSchedule(doctorId));
        toast.success('Schedule created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create schedule');
      },
    }
  );
};

export const useUpdateDoctorSchedule = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ doctorId, data }: { doctorId: number; data: Partial<DoctorSchedule> }) => 
      apiService.updateDoctorSchedule(doctorId, data),
    {
      onSuccess: (_, { doctorId }) => {
        queryClient.invalidateQueries(queryKeys.doctorSchedule(doctorId));
        toast.success('Schedule updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update schedule');
      },
    }
  );
};

// Services Hooks
export const useServices = () => {
  return useQuery(queryKeys.services, () => apiService.getServices(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const useService = (id: number) => {
  return useQuery(queryKeys.service(id), () => apiService.getService(id), {
    enabled: !!id,
  });
};

export const useServicesByClinic = (clinicId: number) => {
  return useQuery(queryKeys.servicesByClinic(clinicId), () => apiService.getServicesByClinic(clinicId), {
    enabled: !!clinicId,
  });
};

export const useCreateService = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: Partial<Service>) => apiService.createService(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.services);
        toast.success('Service created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create service');
      },
    }
  );
};

export const useUpdateService = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<Service> }) => 
      apiService.updateService(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.service(id));
        queryClient.invalidateQueries(queryKeys.services);
        toast.success('Service updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update service');
      },
    }
  );
};

export const useDeleteService = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.deleteService(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.services);
        toast.success('Service deleted successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to delete service');
      },
    }
  );
};

// Staff Hooks
export const useStaff = () => {
  return useQuery(queryKeys.staff, () => apiService.getStaff(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const useStaffMember = (id: number) => {
  return useQuery(queryKeys.staffMember(id), () => apiService.getStaffMember(id), {
    enabled: !!id,
  });
};

export const useStaffByClinic = (clinicId: number) => {
  return useQuery(queryKeys.staffByClinic(clinicId), () => apiService.getStaffByClinic(clinicId), {
    enabled: !!clinicId,
  });
};

export const useCreateStaff = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: Partial<Staff>) => apiService.createStaff(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.staff);
        toast.success('Staff member created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create staff member');
      },
    }
  );
};

export const useUpdateStaff = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<Staff> }) => 
      apiService.updateStaff(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.staffMember(id));
        queryClient.invalidateQueries(queryKeys.staff);
        toast.success('Staff member updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update staff member');
      },
    }
  );
};

export const useDeleteStaff = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.deleteStaff(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.staff);
        toast.success('Staff member deleted successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to delete staff member');
      },
    }
  );
};

// Bookings Hooks
export const useBookings = () => {
  return useQuery(queryKeys.bookings, () => apiService.getBookings(), {
    staleTime: 2 * 60 * 1000, // 2 minutes for more frequent updates
  });
};

export const useBooking = (id: number) => {
  return useQuery(queryKeys.booking(id), () => apiService.getBooking(id), {
    enabled: !!id,
  });
};

export const useBookingsByClinic = (clinicId: number) => {
  return useQuery(queryKeys.bookingsByClinic(clinicId), () => apiService.getBookingsByClinic(clinicId), {
    enabled: !!clinicId,
  });
};

export const useBookingsByDoctor = (doctorId: number) => {
  return useQuery(queryKeys.bookingsByDoctor(doctorId), () => apiService.getBookingsByDoctor(doctorId), {
    enabled: !!doctorId,
  });
};

export const useCreateBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: CreateBookingData) => apiService.createBooking(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.bookings);
        toast.success('Booking created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create booking');
      },
    }
  );
};

export const useUpdateBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<CreateBookingData> }) => 
      apiService.updateBooking(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.booking(id));
        queryClient.invalidateQueries(queryKeys.bookings);
        toast.success('Booking updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update booking');
      },
    }
  );
};

export const useConfirmBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.confirmBooking(id),
    {
      onSuccess: (_, id) => {
        queryClient.invalidateQueries(queryKeys.booking(id));
        queryClient.invalidateQueries(queryKeys.bookings);
        toast.success('Booking confirmed successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to confirm booking');
      },
    }
  );
};

export const useCancelBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.cancelBooking(id),
    {
      onSuccess: (_, id) => {
        queryClient.invalidateQueries(queryKeys.booking(id));
        queryClient.invalidateQueries(queryKeys.bookings);
        toast.success('Booking cancelled successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to cancel booking');
      },
    }
  );
};

export const useDeleteBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.deleteBooking(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.bookings);
        toast.success('Booking deleted successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to delete booking');
      },
    }
  );
};

// Consultations Hooks
export const useConsultations = () => {
  return useQuery(queryKeys.consultations, () => apiService.getConsultations(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const useConsultation = (id: number) => {
  return useQuery(queryKeys.consultation(id), () => apiService.getConsultation(id), {
    enabled: !!id,
  });
};

export const useConsultationsByBooking = (bookingId: number) => {
  return useQuery(queryKeys.consultationsByBooking(bookingId), () => apiService.getConsultationsByBooking(bookingId), {
    enabled: !!bookingId,
  });
};

export const useConsultationsByDoctor = (doctorId: number) => {
  return useQuery(queryKeys.consultationsByDoctor(doctorId), () => apiService.getConsultationsByDoctor(doctorId), {
    enabled: !!doctorId,
  });
};

export const useCreateConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: Partial<Consultation>) => apiService.createConsultation(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.consultations);
        toast.success('Consultation created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create consultation');
      },
    }
  );
};

export const useUpdateConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<Consultation> }) => 
      apiService.updateConsultation(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.consultation(id));
        queryClient.invalidateQueries(queryKeys.consultations);
        toast.success('Consultation updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update consultation');
      },
    }
  );
};

export const useCompleteConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.completeConsultation(id),
    {
      onSuccess: (_, id) => {
        queryClient.invalidateQueries(queryKeys.consultation(id));
        queryClient.invalidateQueries(queryKeys.consultations);
        toast.success('Consultation completed successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to complete consultation');
      },
    }
  );
};

export const useDeleteConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.deleteConsultation(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.consultations);
        toast.success('Consultation deleted successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to delete consultation');
      },
    }
  );
};

// Payments Hooks
export const usePayments = () => {
  return useQuery(queryKeys.payments, () => apiService.getPayments(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const usePayment = (id: number) => {
  return useQuery(queryKeys.payment(id), () => apiService.getPayment(id), {
    enabled: !!id,
  });
};

export const usePaymentsByBooking = (bookingId: number) => {
  return useQuery(queryKeys.paymentsByBooking(bookingId), () => apiService.getPaymentsByBooking(bookingId), {
    enabled: !!bookingId,
  });
};

export const useCreatePayment = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: Partial<Payment>) => apiService.createPayment(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.payments);
        toast.success('Payment created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create payment');
      },
    }
  );
};

export const useUpdatePayment = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    ({ id, data }: { id: number; data: Partial<Payment> }) => 
      apiService.updatePayment(id, data),
    {
      onSuccess: (_, { id }) => {
        queryClient.invalidateQueries(queryKeys.payment(id));
        queryClient.invalidateQueries(queryKeys.payments);
        toast.success('Payment updated successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to update payment');
      },
    }
  );
};

export const useRefundPayment = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (id: number) => apiService.refundPayment(id),
    {
      onSuccess: (_, id) => {
        queryClient.invalidateQueries(queryKeys.payment(id));
        queryClient.invalidateQueries(queryKeys.payments);
        toast.success('Payment refunded successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to refund payment');
      },
    }
  );
};

// Onboarding Hooks
export const useOnboardingStatus = () => {
  return useQuery(queryKeys.onboardingStatus, () => apiService.getOnboardingStatus(), {
    staleTime: 30 * 1000, // 30 seconds for real-time updates
  });
};

export const useOnboardingSteps = () => {
  return useQuery(queryKeys.onboardingSteps, () => apiService.getOnboardingSteps(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const useOnboardingProgress = () => {
  return useQuery(queryKeys.onboardingProgress, () => apiService.getOnboardingProgress(), {
    staleTime: 30 * 1000,
  });
};

export const useOnboardingStatistics = () => {
  return useQuery(queryKeys.onboardingStatistics, () => apiService.getOnboardingStatistics(), {
    staleTime: 2 * 60 * 1000,
  });
};

export const useCreateDefaultClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (data: Partial<CreateClinicData>) => apiService.createDefaultClinic(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.onboardingStatus);
        queryClient.invalidateQueries(queryKeys.onboardingStatistics);
        queryClient.invalidateQueries(queryKeys.clinics);
        toast.success('Default clinic created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create default clinic');
      },
    }
  );
};

export const useCreateDefaultServices = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (clinicId: number) => apiService.createDefaultServices(clinicId),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.onboardingStatus);
        queryClient.invalidateQueries(queryKeys.onboardingStatistics);
        queryClient.invalidateQueries(queryKeys.services);
        toast.success('Default services created successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to create default services');
      },
    }
  );
};

export const useCompleteOnboarding = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    () => apiService.completeOnboarding(),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.onboardingStatus);
        queryClient.invalidateQueries(queryKeys.onboardingProgress);
        toast.success('Onboarding completed successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to complete onboarding');
      },
    }
  );
};

export const useResetOnboarding = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    () => apiService.resetOnboarding(),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.onboardingStatus);
        queryClient.invalidateQueries(queryKeys.onboardingProgress);
        queryClient.invalidateQueries(queryKeys.onboardingStatistics);
        toast.success('Onboarding reset successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to reset onboarding');
      },
    }
  );
};

// Settings Hooks
export const useSettings = () => {
  return useQuery(queryKeys.settings, () => apiService.getSettings(), {
    staleTime: 5 * 60 * 1000,
  });
};

export const useSaveSettings = () => {
  const queryClient = useQueryClient();
  
  return useMutation(
    (settings: Partial<PluginSettings>) => apiService.saveSettings(settings),
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeys.settings);
        toast.success('Settings saved successfully!');
      },
      onError: (error: ApiError) => {
        toast.error(error.message || 'Failed to save settings');
      },
    }
  );
};
