import React from 'react';
import { Outlet } from 'react-router-dom';
import Sidebar from './components/Sidebar';
import Header from './components/Header';
import { useApp } from './contexts/AppContext';

const Layout: React.FC = () => {
  const { state } = useApp();

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Sidebar */}
      <Sidebar />
      
      {/* Main Content */}
      <div className={`transition-all duration-300 ${
        state.ui.sidebarOpen ? 'ml-64' : 'ml-16'
      }`}>
        {/* Header */}
        <Header />
        
        {/* Page Content */}
        <main className="p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default Layout;
