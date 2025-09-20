import { configureStore } from '@reduxjs/toolkit';
import { setupListeners } from '@reduxjs/toolkit/query/react';
import { api } from './api';
import authSlice from './slices/authSlice';
import uiSlice from './slices/uiSlice';
import patientSlice from './slices/patientSlice';
import appointmentSlice from './slices/appointmentSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice,
    ui: uiSlice,
    patients: patientSlice,
    appointments: appointmentSlice,
    [api.reducerPath]: api.reducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: [api.util.resetApiState.type],
      },
    }).concat(api.middleware),
  devTools: process.env.NODE_ENV !== 'production',
});

setupListeners(store.dispatch);

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
