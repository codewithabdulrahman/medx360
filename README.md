# MedX360 Plugin

A comprehensive healthcare appointment booking system built with React, designed specifically for medical practices, hospitals, and healthcare organizations with HIPAA compliance.

## Features

- **React-Powered Interface**: Modern, responsive UI built with React 18 and Material-UI
- **Healthcare-Specific**: Designed for medical practices with patient management, clinical notes, and prescriptions
- **HIPAA Compliant**: Built with security and compliance in mind
- **Multi-Role System**: Support for patients, providers, and administrators
- **Real-Time Booking**: Live availability checking and appointment scheduling
- **Notification System**: Email and SMS reminders
- **Integration Ready**: API endpoints for EMR/EHR integration

## Technology Stack

### Frontend
- React 18 with TypeScript
- Material-UI (MUI) for components
- Redux Toolkit for state management
- React Hook Form with Zod validation
- React Router for navigation
- Vite for build tooling

### Backend
- PHP 8.0+ with WordPress
- Custom REST API endpoints
- MySQL database with healthcare-specific schema
- Dependency injection container

## Installation

### Prerequisites
- WordPress 5.0+
- PHP 8.0+
- Node.js 16+
- npm or yarn

### Setup

1. **Install the plugin**:
   ```bash
   # Copy the plugin to your WordPress plugins directory
   cp -r healthcare-booking /path/to/wordpress/wp-content/plugins/
   ```

2. **Install dependencies**:
   ```bash
   cd wp-content/plugins/healthcare-booking
   npm install
   ```

3. **Build the React applications**:
   ```bash
   # Build both admin and frontend
   npm run build:all
   
   # Or build individually
   npm run build:admin
   npm run build:frontend
   ```

4. **Activate the plugin** in WordPress admin

## Development

### Development Mode

```bash
# Start development server for admin
npm run dev -- --mode admin

# Start development server for frontend
npm run dev -- --mode frontend
```

### Building for Production

```bash
# Build all applications
npm run build:all

# Build individual applications
npm run build:admin
npm run build:frontend
```

### Code Quality

```bash
# Run ESLint
npm run lint

# Run TypeScript type checking
npm run type-check

# Run tests
npm run test
```

## Usage

### Admin Interface

Access the admin interface through WordPress admin:
- Navigate to `Healthcare Booking` in the admin menu
- Use the React-powered dashboard to manage:
  - Patients
  - Healthcare providers
  - Appointments
  - Clinical notes
  - Prescriptions
  - Reports

### Frontend Booking

Add the booking form to any page using the shortcode:
```
[medx360_booking]
```

Or use the Gutenberg block:
- Add a new block
- Search for "MedX360"
- Select the booking form block

### Configuration

Configure the plugin through:
- WordPress admin → MedX360 → Settings
- Customize booking forms, notifications, and integrations

## API Endpoints

The plugin provides REST API endpoints:

- `GET /wp-json/medx360/v1/patients` - List patients
- `POST /wp-json/medx360/v1/patients` - Create patient
- `GET /wp-json/medx360/v1/appointments` - List appointments
- `POST /wp-json/medx360/v1/appointments` - Create appointment
- And many more...

## Database Schema

The plugin creates the following tables:
- `wp_medx360_patients` - Patient information
- `wp_medx360_providers` - Healthcare providers
- `wp_medx360_appointments` - Appointments
- `wp_medx360_clinical_notes` - Clinical notes
- `wp_medx360_prescriptions` - Prescriptions
- And more...

## Security & Compliance

- HIPAA compliance features
- Data encryption
- Access controls
- Audit logging
- Secure API endpoints

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

## License

GPL v2 or later

## Support

For support and documentation, visit the plugin documentation or contact support.
