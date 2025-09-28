# MedX360 Frontend

A modern React TypeScript frontend for the MedX360 Medical Booking Management System, built to integrate seamlessly with WordPress REST APIs.

## Features

### 🏥 **Comprehensive Medical Management**
- **Clinic Management**: Create and manage multiple medical clinics
- **Hospital Management**: Organize hospitals under clinics
- **Doctor Management**: Manage medical staff with specializations and schedules
- **Service Management**: Define medical services with pricing and duration
- **Staff Management**: Manage non-medical staff members
- **Booking System**: Complete appointment booking and management
- **Payment Processing**: Handle payments and refunds
- **Consultation Management**: Track patient consultations and medical records

### 🎨 **Modern UI/UX**
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- **Clean Interface**: Intuitive and user-friendly design
- **Dark Mode Support**: Automatic theme switching
- **Accessibility**: WCAG compliant components
- **Loading States**: Smooth loading indicators and error handling

### 🔧 **Technical Features**
- **TypeScript**: Full type safety and better development experience
- **React Query**: Efficient data fetching and caching
- **Context API**: Centralized state management
- **React Router**: Client-side routing
- **Form Validation**: Comprehensive form handling with react-hook-form
- **Toast Notifications**: User feedback with react-hot-toast
- **Icon System**: Beautiful icons with Lucide React

## Tech Stack

- **React 18** - Modern React with hooks and concurrent features
- **TypeScript** - Type-safe JavaScript
- **React Query** - Data fetching and state management
- **React Router** - Client-side routing
- **React Hook Form** - Form handling and validation
- **React Hot Toast** - Toast notifications
- **Lucide React** - Beautiful icons
- **Tailwind CSS** - Utility-first CSS framework
- **Axios** - HTTP client for API requests

## Project Structure

```
src/
├── components/          # Reusable UI components
│   ├── Layout.tsx      # Main layout wrapper
│   ├── Sidebar.tsx     # Navigation sidebar
│   ├── Header.tsx      # Top header bar
│   └── OnboardingWizard.tsx # Setup wizard
├── pages/              # Page components
│   ├── Dashboard.tsx   # Main dashboard
│   ├── Clinics.tsx     # Clinic management
│   ├── Doctors.tsx     # Doctor management
│   ├── Bookings.tsx    # Booking management
│   └── ...             # Other pages
├── hooks/              # Custom React hooks
│   └── useApi.ts       # API integration hooks
├── services/           # API services
│   └── api.ts          # WordPress REST API client
├── contexts/           # React Context providers
│   └── AppContext.tsx  # Global app state
├── types/              # TypeScript type definitions
│   └── index.ts        # All type definitions
├── utils/              # Utility functions
│   └── index.ts        # Helper functions
└── App.tsx            # Main app component
```

## Getting Started

### Prerequisites

- Node.js 16+ 
- npm or yarn
- WordPress site with MedX360 plugin installed

### Installation

1. **Navigate to frontend directory**
   ```bash
   cd wp-content/plugins/medx360/frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Configure environment**
   Create a `.env` file in the frontend directory:
   ```env
   REACT_APP_WP_URL=http://localhost:3000
   REACT_APP_WP_NONCE=your-wordpress-nonce
   ```

4. **Start development server**
   ```bash
   npm start
   ```

5. **Open in browser**
   Navigate to `http://localhost:3000`

### Building for Production

```bash
npm run build
```

This creates a `build` folder with optimized production files.

## API Integration

The frontend integrates with WordPress REST APIs through:

### **API Service Layer** (`services/api.ts`)
- Centralized API client using Axios
- Automatic nonce authentication
- Error handling and response formatting
- Type-safe API methods

### **React Query Hooks** (`hooks/useApi.ts`)
- Efficient data fetching and caching
- Optimistic updates
- Background refetching
- Error handling with toast notifications

### **Available Endpoints**
- `GET/POST/PUT/DELETE /clinics` - Clinic management
- `GET/POST/PUT/DELETE /hospitals` - Hospital management
- `GET/POST/PUT/DELETE /doctors` - Doctor management
- `GET/POST/PUT/DELETE /services` - Service management
- `GET/POST/PUT/DELETE /staff` - Staff management
- `GET/POST/PUT/DELETE /bookings` - Booking management
- `GET/POST/PUT/DELETE /consultations` - Consultation management
- `GET/POST/PUT/DELETE /payments` - Payment management
- `GET/POST /onboarding/*` - Onboarding wizard
- `GET/POST /settings` - System settings

## State Management

### **Context API** (`contexts/AppContext.tsx`)
- Global application state
- User authentication status
- Current clinic/doctor selection
- UI state (sidebar, theme, loading)
- Onboarding status

### **React Query**
- Server state management
- Caching and synchronization
- Background updates
- Optimistic updates

## Components

### **Layout Components**
- `Layout` - Main app wrapper with sidebar and header
- `Sidebar` - Collapsible navigation sidebar
- `Header` - Top header with search and user menu

### **Feature Components**
- `OnboardingWizard` - Step-by-step setup process
- `Dashboard` - Main dashboard with statistics
- Page components for each feature area

### **UI Components**
- Responsive cards and grids
- Form components with validation
- Loading states and error handling
- Toast notifications
- Status badges and indicators

## Styling

### **Tailwind CSS**
- Utility-first CSS framework
- Responsive design system
- Dark mode support
- Custom color palette

### **Custom Styles** (`App.css`)
- Base styles and resets
- Component-specific styles
- Print styles
- Responsive utilities

## Form Handling

### **React Hook Form**
- Performant form handling
- Built-in validation
- Error state management
- Type-safe form data

### **Validation**
- Email and phone validation
- Required field validation
- Custom validation rules
- Real-time error feedback

## Error Handling

### **API Errors**
- Centralized error handling
- User-friendly error messages
- Toast notifications
- Retry mechanisms

### **Form Errors**
- Field-level validation
- Real-time feedback
- Error state styling
- Accessibility support

## Responsive Design

### **Breakpoints**
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

### **Mobile Features**
- Collapsible sidebar
- Touch-friendly interfaces
- Optimized layouts
- Mobile navigation

## Accessibility

### **WCAG Compliance**
- Keyboard navigation
- Screen reader support
- Color contrast compliance
- Focus management

### **ARIA Labels**
- Proper labeling
- Role attributes
- Live regions
- Descriptive text

## Performance

### **Optimization**
- Code splitting
- Lazy loading
- Image optimization
- Bundle analysis

### **Caching**
- React Query caching
- Browser caching
- Service worker (PWA)
- Offline support

## Development

### **Scripts**
- `npm start` - Development server
- `npm run build` - Production build
- `npm test` - Run tests
- `npm run eject` - Eject from Create React App

### **Code Quality**
- TypeScript strict mode
- ESLint configuration
- Prettier formatting
- Husky git hooks

## Deployment

### **Production Build**
1. Run `npm run build`
2. Upload `build` folder to web server
3. Configure web server for SPA routing
4. Set up environment variables

### **WordPress Integration**
1. Enqueue React build files in WordPress
2. Pass nonce and API URLs to frontend
3. Handle routing for SPA
4. Configure CORS if needed

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

GPL v2 or later - Same as WordPress

## Support

For support and feature requests, please contact the development team.

---

**MedX360 Frontend** - Modern, responsive, and feature-rich medical booking management interface! 🏥✨
