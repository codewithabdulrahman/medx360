import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { Toaster } from 'react-hot-toast';
import { AppProvider } from './contexts/AppContext';
import Layout from './components/Layout';
import OnboardingWizard from './components/OnboardingWizard';
import Dashboard from './pages/Dashboard';
import Clinics from './pages/Clinics';
import Hospitals from './pages/Hospitals';
import Doctors from './pages/Doctors';
import Services from './pages/Services';
import Staff from './pages/Staff';
import Bookings from './pages/Bookings';
import Consultations from './pages/Consultations';
import Payments from './pages/Payments';
import Settings from './pages/Settings';
import './App.css';

// Create a client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
      staleTime: 5 * 60 * 1000, // 5 minutes
    },
    mutations: {
      retry: 1,
    },
  },
});

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AppProvider>
        <Router>
          <div className="App">
            <Routes>
              {/* Onboarding Route */}
              <Route path="/onboarding" element={<OnboardingWizard />} />
              
              {/* Main App Routes */}
              <Route path="/" element={<Layout />}>
                <Route index element={<Navigate to="/dashboard" replace />} />
                <Route path="dashboard" element={<Dashboard />} />
                <Route path="clinics" element={<Clinics />} />
                <Route path="hospitals" element={<Hospitals />} />
                <Route path="doctors" element={<Doctors />} />
                <Route path="services" element={<Services />} />
                <Route path="staff" element={<Staff />} />
                <Route path="bookings" element={<Bookings />} />
                <Route path="consultations" element={<Consultations />} />
                <Route path="payments" element={<Payments />} />
                <Route path="settings" element={<Settings />} />
              </Route>
              
              {/* Catch all route */}
              <Route path="*" element={<Navigate to="/dashboard" replace />} />
            </Routes>
            
            {/* Toast notifications */}
            <Toaster
              position="top-right"
              toastOptions={{
                duration: 4000,
                style: {
                  background: '#363636',
                  color: '#fff',
                },
                success: {
                  duration: 3000,
                  iconTheme: {
                    primary: '#4ade80',
                    secondary: '#fff',
                  },
                },
                error: {
                  duration: 5000,
                  iconTheme: {
                    primary: '#ef4444',
                    secondary: '#fff',
                  },
                },
              }}
            />
          </div>
        </Router>
      </AppProvider>
    </QueryClientProvider>
  );
}

export default App;
