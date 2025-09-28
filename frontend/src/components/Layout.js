import React, { useEffect } from 'react';
import { Outlet, useLocation, useNavigate } from 'react-router-dom';
import Header from '@components/Header';
import { useSetupStatus } from '@hooks/useApi';

const Layout = () => {
  const location = useLocation();
  const navigate = useNavigate();
  
  // Debug logging
  console.log('Layout component rendering');
  console.log('Current path:', location.pathname);
  
  // Temporarily disable setup status check for debugging
  // const { data: setupStatus, isLoading } = useSetupStatus();

  // Redirect to onboarding if setup is not completed
  // useEffect(() => {
  //   if (!isLoading && setupStatus && !setupStatus.is_completed) {
  //     navigate('/onboarding');
  //   }
  // }, [setupStatus, isLoading, navigate]);

  // Don't show header on onboarding page
  const showHeader = location.pathname !== '/onboarding';

  return (
    <div className="min-h-screen bg-gray-100" style={{ marginTop: '-32px', paddingTop: '32px' }}>
      {/* Header */}
      {showHeader && <Header />}

      {/* Page content */}
      <main className="overflow-x-hidden overflow-y-auto bg-gray-50">
        <div className="container mx-auto px-6 py-8">
          <Outlet />
        </div>
      </main>
    </div>
  );
};

export default Layout;
