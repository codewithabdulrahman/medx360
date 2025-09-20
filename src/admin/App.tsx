import React from 'react';
import { Box } from '@mui/material';

import Dashboard from './pages/Dashboard';
import Calendar from './pages/Calendar';
import Events from './pages/Events';
import Services from './pages/Services';
import Locations from './pages/Locations';
import Customers from './pages/Customers';
import Finance from './pages/Finance';
import Notifications from './pages/Notifications';
import Customize from './pages/Customize';
import CustomFields from './pages/CustomFields';
import WhatsNew from './pages/WhatsNew';
import LiteVsPremium from './pages/LiteVsPremium';
import Appointments from './pages/Appointments';
import ClinicalNotes from './pages/ClinicalNotes';
import Prescriptions from './pages/Prescriptions';
import Settings from './pages/Settings';
import Reports from './pages/Reports';

const App: React.FC = () => {
  // Get the page slug from the data attribute
  const container = document.getElementById('medx360-admin');
  const pageSlug = container?.getAttribute('data-page') || 'dashboard';

  // Component mapping
  const components: { [key: string]: React.ComponentType } = {
    'dashboard': Dashboard,
    'calendar': Calendar,
    'appointments': Appointments,
    'events': Events,
    'services': Services,
    'locations': Locations,
    'customers': Customers,
    'finance': Finance,
    'notifications': Notifications,
    'customize': Customize,
    'custom-fields': CustomFields,
    'whats-new': WhatsNew,
    'lite-vs-premium': LiteVsPremium,
    'settings': Settings,
    'clinical-notes': ClinicalNotes,
    'prescriptions': Prescriptions,
    'reports': Reports,
  };

  // Get the component to render
  const ComponentToRender = components[pageSlug] || Dashboard;

  return (
    <Box sx={{ minHeight: '100vh' }}>
      <ComponentToRender />
    </Box>
  );
};

export default App;