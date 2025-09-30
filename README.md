# MedX360 - Medical Booking Management Plugin

A comprehensive WordPress plugin for managing medical bookings, clinics, hospitals, doctors, consultations, services, staff, payments, and appointments with a modern React frontend and robust AJAX backend.

## Features

### Core Functionality
- **Clinic Management**: Create and manage multiple medical clinics
- **Hospital Management**: Organize hospitals under clinics
- **Doctor Management**: Manage medical staff with specializations and schedules
- **Service Management**: Define medical services with pricing and duration
- **Staff Management**: Manage non-medical staff members
- **Booking System**: Complete appointment booking and management
- **Payment Processing**: Handle payments and refunds
- **Consultation Management**: Track patient consultations and medical records
- **Onboarding Wizard**: Guided setup process for new installations

### Technical Features
- **Modern React Frontend**: Built with React 18, React Router, and React Query
- **WordPress AJAX Backend**: Secure AJAX endpoints with nonce verification
- **Responsive Design**: Mobile-first design with Tailwind CSS
- **Performance Optimized**: Caching, database optimization, and efficient queries
- **Security Focused**: Input validation, sanitization, and security logging
- **Error Handling**: Comprehensive error handling and logging system
- **Code Standards**: Follows WordPress and React coding standards

## Installation

1. Upload the plugin files to `/wp-content/plugins/medx360/` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to MedX360 in the admin menu to complete the setup wizard

## Development

### Prerequisites

- WordPress 5.0+
- PHP 7.4+
- Node.js 16+
- npm 8+

### Backend Development

The plugin follows WordPress coding standards and best practices:

```bash
# Install PHP CodeSniffer for WordPress
composer install

# Run code quality checks
./vendor/bin/phpcs --standard=.phpcs.xml
```

### Frontend Development

The React frontend is located in the `frontend/` directory:

```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Run linting
npm run lint

# Format code
npm run format
```

### Database Schema

The plugin creates the following optimized database tables:

- `wp_medx360_clinics` - Clinic information
- `wp_medx360_hospitals` - Hospital information
- `wp_medx360_doctors` - Doctor profiles and information
- `wp_medx360_services` - Medical services offered
- `wp_medx360_staff` - Non-medical staff members
- `wp_medx360_bookings` - Appointment bookings
- `wp_medx360_consultations` - Patient consultations
- `wp_medx360_payments` - Payment records
- `wp_medx360_doctor_schedules` - Doctor availability schedules
- `wp_medx360_doctor_availability` - Doctor availability exceptions

### AJAX Endpoints

The plugin provides comprehensive AJAX endpoints with the following naming convention:

#### Clinics
- `medx360_get_clinics` - List all clinics
- `medx360_create_clinic` - Create a new clinic
- `medx360_get_clinic` - Get specific clinic
- `medx360_update_clinic` - Update clinic
- `medx360_delete_clinic` - Delete clinic

#### Hospitals
- `medx360_get_hospitals` - List all hospitals
- `medx360_create_hospital` - Create a new hospital
- `medx360_get_hospital` - Get specific hospital
- `medx360_update_hospital` - Update hospital
- `medx360_delete_hospital` - Delete hospital

#### Doctors
- `medx360_get_doctors` - List all doctors
- `medx360_create_doctor` - Create a new doctor
- `medx360_get_doctor` - Get specific doctor
- `medx360_update_doctor` - Update doctor
- `medx360_delete_doctor` - Delete doctor

#### Services
- `medx360_get_services` - List all services
- `medx360_create_service` - Create a new service
- `medx360_get_service` - Get specific service
- `medx360_update_service` - Update service
- `medx360_delete_service` - Delete service

#### Staff
- `medx360_get_staff` - List all staff
- `medx360_create_staff` - Create a new staff member
- `medx360_get_staff_member` - Get specific staff member
- `medx360_update_staff` - Update staff member
- `medx360_delete_staff` - Delete staff member

#### Bookings
- `medx360_get_bookings` - List all bookings
- `medx360_create_booking` - Create a new booking
- `medx360_get_booking` - Get specific booking
- `medx360_update_booking` - Update booking
- `medx360_delete_booking` - Delete booking

#### Payments
- `medx360_get_payments` - List all payments
- `medx360_create_payment` - Create a new payment
- `medx360_get_payment` - Get specific payment
- `medx360_refund_payment` - Refund payment

#### Consultations
- `medx360_get_consultations` - List all consultations
- `medx360_create_consultation` - Create a new consultation
- `medx360_get_consultation` - Get specific consultation
- `medx360_update_consultation` - Update consultation
- `medx360_complete_consultation` - Complete consultation

#### Onboarding
- `medx360_get_onboarding_status` - Get setup status
- `medx360_get_onboarding_steps` - Get setup steps
- `medx360_get_onboarding_progress` - Get setup progress
- `medx360_get_onboarding_statistics` - Get system statistics
- `medx360_create_onboarding_clinic` - Create default clinic
- `medx360_create_onboarding_services` - Create default services
- `medx360_complete_onboarding` - Complete setup
- `medx360_reset_onboarding` - Reset setup

## Security

The plugin implements comprehensive security measures:

- **Nonce Verification**: All AJAX requests are protected with WordPress nonces
- **Input Validation**: All user inputs are validated and sanitized
- **Permission Checks**: Role-based access control for all operations
- **SQL Injection Prevention**: All database queries use prepared statements
- **XSS Protection**: All outputs are properly escaped
- **Security Logging**: All security events are logged for monitoring

## Performance

The plugin is optimized for performance:

- **Database Optimization**: Proper indexing and foreign key constraints
- **Caching System**: WordPress object cache integration
- **Query Optimization**: Efficient database queries with pagination
- **Asset Optimization**: Minified and compressed frontend assets
- **Lazy Loading**: Components and data are loaded as needed

## Error Handling

Comprehensive error handling and logging:

- **Structured Logging**: All errors are logged with context
- **User-Friendly Messages**: Clear error messages for users
- **Debug Information**: Detailed error information for developers
- **Performance Monitoring**: Slow operations are logged and monitored

## Code Quality

The plugin follows industry best practices:

- **WordPress Standards**: Follows WordPress coding standards
- **React Standards**: Follows React best practices and patterns
- **ESLint Configuration**: Comprehensive linting rules
- **Prettier Formatting**: Consistent code formatting
- **PHP CodeSniffer**: WordPress-specific code quality checks

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes following the coding standards
4. Run tests and quality checks
5. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and documentation, please visit the plugin's official website or create an issue in the repository.

## Changelog

### Version 1.0.0
- Initial release
- Complete medical booking management system
- React frontend with WordPress AJAX backend
- Comprehensive security and performance optimizations
- Full code quality and standards compliance