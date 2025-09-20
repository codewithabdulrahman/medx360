import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';

// Get API configuration from WordPress
const getApiConfig = () => {
  if (typeof window !== 'undefined' && (window as any).healthcareBookingAdmin) {
    return (window as any).healthcareBookingAdmin;
  }
  return {
    apiUrl: '/wp-json/healthcare-booking/v1',
    nonce: '',
  };
};

export const api = createApi({
  reducerPath: 'api',
  baseQuery: fetchBaseQuery({
    baseUrl: getApiConfig().apiUrl,
    prepareHeaders: (headers) => {
      const config = getApiConfig();
      headers.set('X-WP-Nonce', config.nonce);
      headers.set('Content-Type', 'application/json');
      return headers;
    },
  }),
  tagTypes: [
    'Patient',
    'Provider',
    'Appointment',
    'ClinicalNote',
    'Prescription',
    'Service',
    'Location',
    'Room',
    'Notification',
  ],
  endpoints: (builder) => ({
    // Patients
    getPatients: builder.query({
      query: (params = {}) => ({
        url: 'patients',
        params,
      }),
      providesTags: ['Patient'],
    }),
    getPatient: builder.query({
      query: (id) => `patients/${id}`,
      providesTags: (result, error, id) => [{ type: 'Patient', id }],
    }),
    createPatient: builder.mutation({
      query: (patient) => ({
        url: 'patients',
        method: 'POST',
        body: patient,
      }),
      invalidatesTags: ['Patient'],
    }),
    updatePatient: builder.mutation({
      query: ({ id, ...patient }) => ({
        url: `patients/${id}`,
        method: 'PUT',
        body: patient,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'Patient', id }],
    }),
    deletePatient: builder.mutation({
      query: (id) => ({
        url: `patients/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Patient'],
    }),

    // Providers
    getProviders: builder.query({
      query: (params = {}) => ({
        url: 'providers',
        params,
      }),
      providesTags: ['Provider'],
    }),
    getProvider: builder.query({
      query: (id) => `providers/${id}`,
      providesTags: (result, error, id) => [{ type: 'Provider', id }],
    }),
    createProvider: builder.mutation({
      query: (provider) => ({
        url: 'providers',
        method: 'POST',
        body: provider,
      }),
      invalidatesTags: ['Provider'],
    }),
    updateProvider: builder.mutation({
      query: ({ id, ...provider }) => ({
        url: `providers/${id}`,
        method: 'PUT',
        body: provider,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'Provider', id }],
    }),
    deleteProvider: builder.mutation({
      query: (id) => ({
        url: `providers/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Provider'],
    }),

    // Appointments
    getAppointments: builder.query({
      query: (params = {}) => ({
        url: 'appointments',
        params,
      }),
      providesTags: ['Appointment'],
    }),
    getAppointment: builder.query({
      query: (id) => `appointments/${id}`,
      providesTags: (result, error, id) => [{ type: 'Appointment', id }],
    }),
    createAppointment: builder.mutation({
      query: (appointment) => ({
        url: 'appointments',
        method: 'POST',
        body: appointment,
      }),
      invalidatesTags: ['Appointment'],
    }),
    updateAppointment: builder.mutation({
      query: ({ id, ...appointment }) => ({
        url: `appointments/${id}`,
        method: 'PUT',
        body: appointment,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'Appointment', id }],
    }),
    deleteAppointment: builder.mutation({
      query: (id) => ({
        url: `appointments/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Appointment'],
    }),
    getAppointmentAvailability: builder.query({
      query: (params) => ({
        url: 'appointments/availability',
        params,
      }),
    }),

    // Clinical Notes
    getClinicalNotes: builder.query({
      query: (params = {}) => ({
        url: 'clinical-notes',
        params,
      }),
      providesTags: ['ClinicalNote'],
    }),
    getClinicalNote: builder.query({
      query: (id) => `clinical-notes/${id}`,
      providesTags: (result, error, id) => [{ type: 'ClinicalNote', id }],
    }),
    createClinicalNote: builder.mutation({
      query: (note) => ({
        url: 'clinical-notes',
        method: 'POST',
        body: note,
      }),
      invalidatesTags: ['ClinicalNote'],
    }),
    updateClinicalNote: builder.mutation({
      query: ({ id, ...note }) => ({
        url: `clinical-notes/${id}`,
        method: 'PUT',
        body: note,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'ClinicalNote', id }],
    }),
    deleteClinicalNote: builder.mutation({
      query: (id) => ({
        url: `clinical-notes/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['ClinicalNote'],
    }),

    // Prescriptions
    getPrescriptions: builder.query({
      query: (params = {}) => ({
        url: 'prescriptions',
        params,
      }),
      providesTags: ['Prescription'],
    }),
    getPrescription: builder.query({
      query: (id) => `prescriptions/${id}`,
      providesTags: (result, error, id) => [{ type: 'Prescription', id }],
    }),
    createPrescription: builder.mutation({
      query: (prescription) => ({
        url: 'prescriptions',
        method: 'POST',
        body: prescription,
      }),
      invalidatesTags: ['Prescription'],
    }),
    updatePrescription: builder.mutation({
      query: ({ id, ...prescription }) => ({
        url: `prescriptions/${id}`,
        method: 'PUT',
        body: prescription,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'Prescription', id }],
    }),
    deletePrescription: builder.mutation({
      query: (id) => ({
        url: `prescriptions/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Prescription'],
    }),

    // Services
    getServices: builder.query({
      query: (params = {}) => ({
        url: 'services',
        params,
      }),
      providesTags: ['Service'],
    }),
    getService: builder.query({
      query: (id) => `services/${id}`,
      providesTags: (result, error, id) => [{ type: 'Service', id }],
    }),
    createService: builder.mutation({
      query: (service) => ({
        url: 'services',
        method: 'POST',
        body: service,
      }),
      invalidatesTags: ['Service'],
    }),
    updateService: builder.mutation({
      query: ({ id, ...service }) => ({
        url: `services/${id}`,
        method: 'PUT',
        body: service,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'Service', id }],
    }),
    deleteService: builder.mutation({
      query: (id) => ({
        url: `services/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Service'],
    }),

    // Locations
    getLocations: builder.query({
      query: (params = {}) => ({
        url: 'locations',
        params,
      }),
      providesTags: ['Location'],
    }),
    getLocation: builder.query({
      query: (id) => `locations/${id}`,
      providesTags: (result, error, id) => [{ type: 'Location', id }],
    }),
    createLocation: builder.mutation({
      query: (location) => ({
        url: 'locations',
        method: 'POST',
        body: location,
      }),
      invalidatesTags: ['Location'],
    }),
    updateLocation: builder.mutation({
      query: ({ id, ...location }) => ({
        url: `locations/${id}`,
        method: 'PUT',
        body: location,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'Location', id }],
    }),
    deleteLocation: builder.mutation({
      query: (id) => ({
        url: `locations/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Location'],
    }),

    // Rooms
    getRooms: builder.query({
      query: (params = {}) => ({
        url: 'rooms',
        params,
      }),
      providesTags: ['Room'],
    }),
    getRoom: builder.query({
      query: (id) => `rooms/${id}`,
      providesTags: (result, error, id) => [{ type: 'Room', id }],
    }),
    createRoom: builder.mutation({
      query: (room) => ({
        url: 'rooms',
        method: 'POST',
        body: room,
      }),
      invalidatesTags: ['Room'],
    }),
    updateRoom: builder.mutation({
      query: ({ id, ...room }) => ({
        url: `rooms/${id}`,
        method: 'PUT',
        body: room,
      }),
      invalidatesTags: (result, error, { id }) => [{ type: 'Room', id }],
    }),
    deleteRoom: builder.mutation({
      query: (id) => ({
        url: `rooms/${id}`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Room'],
    }),

    // Dashboard Stats
    getDashboardStats: builder.query({
      query: (params = {}) => ({
        url: 'dashboard/stats',
        params,
      }),
    }),

    // Reports
    getReports: builder.query({
      query: (params = {}) => ({
        url: 'reports',
        params,
      }),
    }),
  }),
});

export const {
  // Patients
  useGetPatientsQuery,
  useGetPatientQuery,
  useCreatePatientMutation,
  useUpdatePatientMutation,
  useDeletePatientMutation,

  // Providers
  useGetProvidersQuery,
  useGetProviderQuery,
  useCreateProviderMutation,
  useUpdateProviderMutation,
  useDeleteProviderMutation,

  // Appointments
  useGetAppointmentsQuery,
  useGetAppointmentQuery,
  useCreateAppointmentMutation,
  useUpdateAppointmentMutation,
  useDeleteAppointmentMutation,
  useGetAppointmentAvailabilityQuery,

  // Clinical Notes
  useGetClinicalNotesQuery,
  useGetClinicalNoteQuery,
  useCreateClinicalNoteMutation,
  useUpdateClinicalNoteMutation,
  useDeleteClinicalNoteMutation,

  // Prescriptions
  useGetPrescriptionsQuery,
  useGetPrescriptionQuery,
  useCreatePrescriptionMutation,
  useUpdatePrescriptionMutation,
  useDeletePrescriptionMutation,

  // Services
  useGetServicesQuery,
  useGetServiceQuery,
  useCreateServiceMutation,
  useUpdateServiceMutation,
  useDeleteServiceMutation,

  // Locations
  useGetLocationsQuery,
  useGetLocationQuery,
  useCreateLocationMutation,
  useUpdateLocationMutation,
  useDeleteLocationMutation,

  // Rooms
  useGetRoomsQuery,
  useGetRoomQuery,
  useCreateRoomMutation,
  useUpdateRoomMutation,
  useDeleteRoomMutation,

  // Dashboard
  useGetDashboardStatsQuery,

  // Reports
  useGetReportsQuery,
} = api;
