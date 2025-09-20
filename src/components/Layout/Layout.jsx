import React from 'react';
import { useLocation } from 'react-router-dom';
import Header from './Header';
import Navigation from './Navigation';
import './Layout.css';

const Layout = ({ children }) => {
  const location = useLocation();

  return (
    <div className="medx360-layout">
      <Header currentPath={location.pathname} />
      <Navigation />
      
      <main className="medx360-content">
        {children}
      </main>
    </div>
  );
};

export default Layout;
