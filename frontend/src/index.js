import React from 'react';
import ReactDOM from 'react-dom/client';
import { HashRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';
import { Toaster } from 'react-hot-toast';
import App from './App';
import './index.css';

// Create a client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

// Get WordPress data
const getWPData = () => {
  // Check if WordPress data is already available
  if (window.medx360) {
    return Promise.resolve(window.medx360);
  }
  
  // Fallback for development or if data not available
  return Promise.resolve({
    api_url: '/wp-json/medx360/v1/',
    nonce: '',
    user: null,
    strings: {
      loading: 'Loading...',
      error: 'An error occurred',
      success: 'Success!'
    }
  });
};

// Initialize app
const initApp = async () => {
  try {
    const wpData = await getWPData();
    
    // Store WP data globally
    window.medx360 = wpData;
    
    const rootElement = document.getElementById('root');
    if (!rootElement) {
      console.error('Root element not found');
      return;
    }
    
    // Clear any existing content
    rootElement.innerHTML = '';
    
    const root = ReactDOM.createRoot(rootElement);
    
    root.render(
      <React.StrictMode>
        <QueryClientProvider client={queryClient}>
          <HashRouter>
            <App />
            <Toaster
              position="top-right"
              toastOptions={{
                duration: 4000,
                style: {
                  background: '#363636',
                  color: '#fff',
                },
              }}
            />
          </HashRouter>
        </QueryClientProvider>
      </React.StrictMode>
    );
  } catch (error) {
    console.error('Error initializing React app:', error);
    const rootElement = document.getElementById('root');
    if (rootElement) {
      rootElement.innerHTML = '<div style="padding: 20px; text-align: center;"><h2>Error Loading MedX360</h2><p>Please refresh the page or contact support.</p></div>';
    }
  }
};

// Wait for DOM to be ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initApp);
} else {
  initApp();
}
