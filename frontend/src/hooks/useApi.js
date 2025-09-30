/**
 * MedX360 API Hooks
 * React Query hooks for all API endpoints
 */

import { useQuery, useMutation, useQueryClient } from 'react-query';
import api from '../services/api';

// ==================== CLINICS HOOKS ====================

export const useClinics = (params = {}) => {
  return useQuery({
    queryKey: ['clinics', params],
    queryFn: () => api.getClinics(params),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

export const useClinic = (id) => {
  return useQuery({
    queryKey: ['clinic', id],
    queryFn: () => api.getClinic(id),
    enabled: !!id,
  });
};

export const useCreateClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createClinic(data),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['clinics'] });
      return data;
    },
  });
};

export const useUpdateClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updateClinic(id, data),
    onSuccess: (data, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['clinics'] });
      queryClient.invalidateQueries({ queryKey: ['clinic', id] });
      return data;
    },
  });
};

export const useDeleteClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.deleteClinic(id),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['clinics'] });
      return data;
    },
  });
};

// ==================== HOSPITALS HOOKS ====================

export const useHospitals = (params = {}) => {
  return useQuery({
    queryKey: ['hospitals', params],
    queryFn: () => api.getHospitals(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useHospital = (id) => {
  return useQuery({
    queryKey: ['hospital', id],
    queryFn: () => api.getHospital(id),
    enabled: !!id,
  });
};

export const useCreateHospital = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createHospital(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['hospitals'] });
    },
  });
};

export const useUpdateHospital = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updateHospital(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['hospitals'] });
      queryClient.invalidateQueries({ queryKey: ['hospital', id] });
    },
  });
};

export const useDeleteHospital = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.deleteHospital(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['hospitals'] });
    },
  });
};

export const useHospitalsByClinic = (clinicId) => {
  return useQuery({
    queryKey: ['hospitals', 'clinic', clinicId],
    queryFn: () => api.getHospitalsByClinic(clinicId),
    enabled: !!clinicId,
  });
};

// ==================== DOCTORS HOOKS ====================

export const useDoctors = (params = {}) => {
  return useQuery({
    queryKey: ['doctors', params],
    queryFn: () => api.getDoctors(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useDoctor = (id) => {
  return useQuery({
    queryKey: ['doctor', id],
    queryFn: () => api.getDoctor(id),
    enabled: !!id,
  });
};

export const useCreateDoctor = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createDoctor(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['doctors'] });
    },
  });
};

export const useUpdateDoctor = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updateDoctor(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['doctors'] });
      queryClient.invalidateQueries({ queryKey: ['doctor', id] });
    },
  });
};

export const useDeleteDoctor = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.deleteDoctor(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['doctors'] });
    },
  });
};

export const useDoctorsByClinic = (clinicId) => {
  return useQuery({
    queryKey: ['doctors', 'clinic', clinicId],
    queryFn: () => api.getDoctorsByClinic(clinicId),
    enabled: !!clinicId,
  });
};

export const useDoctorsByHospital = (hospitalId) => {
  return useQuery({
    queryKey: ['doctors', 'hospital', hospitalId],
    queryFn: () => api.getDoctorsByHospital(hospitalId),
    enabled: !!hospitalId,
  });
};

// ==================== SERVICES HOOKS ====================

export const useServices = (params = {}) => {
  return useQuery({
    queryKey: ['services', params],
    queryFn: () => api.getServices(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useService = (id) => {
  return useQuery({
    queryKey: ['service', id],
    queryFn: () => api.getService(id),
    enabled: !!id,
  });
};

export const useCreateService = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createService(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['services'] });
    },
  });
};

export const useUpdateService = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updateService(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['services'] });
      queryClient.invalidateQueries({ queryKey: ['service', id] });
    },
  });
};

export const useDeleteService = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.deleteService(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['services'] });
    },
  });
};

// ==================== STAFF HOOKS ====================

export const useStaff = (params = {}) => {
  return useQuery({
    queryKey: ['staff', params],
    queryFn: () => api.getStaff(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useStaffMember = (id) => {
  return useQuery({
    queryKey: ['staff', id],
    queryFn: () => api.getStaffMember(id),
    enabled: !!id,
  });
};

export const useCreateStaffMember = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createStaffMember(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['staff'] });
    },
  });
};

export const useUpdateStaffMember = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updateStaffMember(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['staff'] });
      queryClient.invalidateQueries({ queryKey: ['staff', id] });
    },
  });
};

export const useDeleteStaffMember = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.deleteStaffMember(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['staff'] });
    },
  });
};

// ==================== BOOKINGS HOOKS ====================

export const useBookings = (params = {}) => {
  return useQuery({
    queryKey: ['bookings', params],
    queryFn: () => api.getBookings(params),
    staleTime: 2 * 60 * 1000, // 2 minutes for bookings
  });
};

export const useBooking = (id) => {
  return useQuery({
    queryKey: ['booking', id],
    queryFn: () => api.getBooking(id),
    enabled: !!id,
  });
};

export const useCreateBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createBooking(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
    },
  });
};

export const useUpdateBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updateBooking(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
      queryClient.invalidateQueries({ queryKey: ['booking', id] });
    },
  });
};

export const useDeleteBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.deleteBooking(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
    },
  });
};

export const useConfirmBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.confirmBooking(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
    },
  });
};

export const useCancelBooking = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.cancelBooking(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
    },
  });
};

// ==================== PAYMENTS HOOKS ====================

export const usePayments = (params = {}) => {
  return useQuery({
    queryKey: ['payments', params],
    queryFn: () => api.getPayments(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const usePayment = (id) => {
  return useQuery({
    queryKey: ['payment', id],
    queryFn: () => api.getPayment(id),
    enabled: !!id,
  });
};

export const useCreatePayment = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createPayment(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payments'] });
    },
  });
};

export const useUpdatePayment = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updatePayment(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['payments'] });
      queryClient.invalidateQueries({ queryKey: ['payment', id] });
    },
  });
};

export const useRefundPayment = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.refundPayment(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payments'] });
    },
  });
};

// ==================== CONSULTATIONS HOOKS ====================

export const useConsultations = (params = {}) => {
  return useQuery({
    queryKey: ['consultations', params],
    queryFn: () => api.getConsultations(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useConsultation = (id) => {
  return useQuery({
    queryKey: ['consultation', id],
    queryFn: () => api.getConsultation(id),
    enabled: !!id,
  });
};

export const useCreateConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createConsultation(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['consultations'] });
    },
  });
};

export const useUpdateConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, data }) => api.updateConsultation(id, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['consultations'] });
      queryClient.invalidateQueries({ queryKey: ['consultation', id] });
    },
  });
};

export const useDeleteConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.deleteConsultation(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['consultations'] });
    },
  });
};

export const useCompleteConsultation = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id) => api.completeConsultation(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['consultations'] });
    },
  });
};

// ==================== ONBOARDING HOOKS ====================

export const useSetupStatus = () => {
  return useQuery({
    queryKey: ['onboarding', 'status'],
    queryFn: () => api.getOnboardingStatus(),
    staleTime: 1 * 60 * 1000, // 1 minute
  });
};

export const useOnboardingSteps = () => {
  return useQuery({
    queryKey: ['onboarding', 'steps'],
    queryFn: () => api.getOnboardingSteps(),
  });
};

export const useOnboardingProgress = () => {
  return useQuery({
    queryKey: ['onboarding', 'progress'],
    queryFn: () => api.getOnboardingProgress(),
  });
};

export const useOnboardingStatistics = () => {
  return useQuery({
    queryKey: ['onboarding', 'statistics'],
    queryFn: () => api.getOnboardingStatistics(),
  });
};

export const useCreateDefaultClinic = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createDefaultClinic(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['onboarding'] });
      queryClient.invalidateQueries({ queryKey: ['clinics'] });
    },
  });
};

export const useCreateDefaultServices = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (data) => api.createDefaultServices(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['onboarding'] });
      queryClient.invalidateQueries({ queryKey: ['services'] });
    },
  });
};

export const useCompleteOnboarding = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: () => api.completeOnboarding(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['onboarding'] });
    },
  });
};

export const useResetOnboarding = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: () => api.resetOnboarding(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['onboarding'] });
    },
  });
};