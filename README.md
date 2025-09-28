# MedX360 - Medical Booking Management Plugin

A comprehensive WordPress plugin for managing medical bookings, clinics, hospitals, doctors, consultations, services, staff, payments, and appointments with a robust REST API backend.

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

### REST API Endpoints

The plugin provides a comprehensive REST API with the following endpoints:

#### Clinics
- `GET /medx360/v1/clinics` - List all clinics
- `POST /medx360/v1/clinics` - Create a new clinic
- `GET /medx360/v1/clinics/{id}` - Get specific clinic
- `PUT /medx360/v1/clinics/{id}` - Update clinic
- `DELETE /medx360/v1/clinics/{id}` - Delete clinic
- `GET /medx360/v1/clinics/slug/{slug}` - Get clinic by slug

#### Hospitals
- `GET /medx360/v1/hospitals` - List all hospitals
- `POST /medx360/v1/hospitals` - Create a new hospital
- `GET /medx360/v1/hospitals/{id}` - Get specific hospital
- `PUT /medx360/v1/hospitals/{id}` - Update hospital
- `DELETE /medx360/v1/hospitals/{id}` - Delete hospital
- `GET /medx360/v1/hospitals/clinic/{clinic_id}` - Get hospitals by clinic

#### Doctors
- `GET /medx360/v1/doctors` - List all doctors
- `POST /medx360/v1/doctors` - Create a new doctor
- `GET /medx360/v1/doctors/{id}` - Get specific doctor
- `PUT /medx360/v1/doctors/{id}` - Update doctor
- `DELETE /medx360/v1/doctors/{id}` - Delete doctor
- `GET /medx360/v1/doctors/clinic/{clinic_id}` - Get doctors by clinic
- `GET /medx360/v1/doctors/hospital/{hospital_id}` - Get doctors by hospital
- `GET /medx360/v1/doctors/{id}/schedule` - Get doctor schedule
- `POST /medx360/v1/doctors/{id}/schedule` - Create doctor schedule
- `PUT /medx360/v1/doctors/{id}/schedule` - Update doctor schedule
- `GET /medx360/v1/doctors/{id}/availability` - Get doctor availability
- `POST /medx360/v1/doctors/{id}/availability` - Create availability exception

#### Services
- `GET /medx360/v1/services` - List all services
- `POST /medx360/v1/services` - Create a new service
- `GET /medx360/v1/services/{id}` - Get specific service
- `PUT /medx360/v1/services/{id}` - Update service
- `DELETE /medx360/v1/services/{id}` - Delete service
- `GET /medx360/v1/services/clinic/{clinic_id}` - Get services by clinic
- `GET /medx360/v1/services/hospital/{hospital_id}` - Get services by hospital

#### Staff
- `GET /medx360/v1/staff` - List all staff
- `POST /medx360/v1/staff` - Create a new staff member
- `GET /medx360/v1/staff/{id}` - Get specific staff member
- `PUT /medx360/v1/staff/{id}` - Update staff member
- `DELETE /medx360/v1/staff/{id}` - Delete staff member
- `GET /medx360/v1/staff/clinic/{clinic_id}` - Get staff by clinic

#### Bookings
- `GET /medx360/v1/bookings` - List all bookings
- `POST /medx360/v1/bookings` - Create a new booking
- `GET /medx360/v1/bookings/{id}` - Get specific booking
- `PUT /medx360/v1/bookings/{id}` - Update booking
- `DELETE /medx360/v1/bookings/{id}` - Delete booking
- `GET /medx360/v1/bookings/clinic/{clinic_id}` - Get bookings by clinic
- `GET /medx360/v1/bookings/doctor/{doctor_id}` - Get bookings by doctor
- `PUT /medx360/v1/bookings/{id}/confirm` - Confirm booking
- `PUT /medx360/v1/bookings/{id}/cancel` - Cancel booking

#### Payments
- `GET /medx360/v1/payments` - List all payments
- `POST /medx360/v1/payments` - Create a new payment
- `GET /medx360/v1/payments/{id}` - Get specific payment
- `PUT /medx360/v1/payments/{id}` - Update payment
- `GET /medx360/v1/payments/booking/{booking_id}` - Get payments by booking
- `PUT /medx360/v1/payments/{id}/refund` - Refund payment

#### Consultations
- `GET /medx360/v1/consultations` - List all consultations
- `POST /medx360/v1/consultations` - Create a new consultation
- `GET /medx360/v1/consultations/{id}` - Get specific consultation
- `PUT /medx360/v1/consultations/{id}` - Update consultation
- `DELETE /medx360/v1/consultations/{id}` - Delete consultation
- `GET /medx360/v1/consultations/booking/{booking_id}` - Get consultations by booking
- `GET /medx360/v1/consultations/doctor/{doctor_id}` - Get consultations by doctor
- `PUT /medx360/v1/consultations/{id}/complete` - Complete consultation

#### Onboarding
- `GET /medx360/v1/onboarding/status` - Get setup status
- `GET /medx360/v1/onboarding/steps` - Get setup steps
- `GET /medx360/v1/onboarding/progress` - Get setup progress
- `GET /medx360/v1/onboarding/statistics` - Get system statistics
- `POST /medx360/v1/onboarding/clinic` - Create default clinic
- `POST /medx360/v1/onboarding/services` - Create default services
- `PUT /medx360/v1/onboarding/complete` - Complete setup
- `PUT /medx360/v1/onboarding/reset` - Reset setup

## Installation

1. Upload the plugin files to `/wp-content/plugins/medx360/` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to MedX360 in the admin menu to complete the setup wizard

## Database Schema

The plugin creates the following database tables:

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

## Authentication

The API uses WordPress nonce authentication. Include the `X-WP-Nonce` header in your requests:

```javascript
fetch('/wp-json/medx360/v1/clinics', {
    headers: {
        'X-WP-Nonce': wpApiSettings.nonce
    }
})
```

## Usage Examples

### Creating a Clinic

```javascript
const clinicData = {
    name: 'City Medical Center',
    slug: 'city-medical-center',
    description: 'A comprehensive medical facility',
    address: '123 Medical Street',
    city: 'Medical City',
    state: 'MC',
    country: 'USA',
    postal_code: '12345',
    phone: '+1234567890',
    email: 'info@citymedical.com',
    website: 'https://citymedical.com',
    status: 'active'
};

fetch('/wp-json/medx360/v1/clinics', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify(clinicData)
});
```

### Creating a Doctor

```javascript
const doctorData = {
    clinic_id: 1,
    first_name: 'John',
    last_name: 'Smith',
    email: 'john.smith@citymedical.com',
    phone: '+1234567891',
    specialization: 'Cardiology',
    license_number: 'MD123456',
    experience_years: 10,
    consultation_fee: 150.00,
    status: 'active'
};

fetch('/wp-json/medx360/v1/doctors', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify(doctorData)
});
```

### Creating a Booking

```javascript
const bookingData = {
    clinic_id: 1,
    doctor_id: 1,
    service_id: 1,
    patient_name: 'Jane Doe',
    patient_email: 'jane.doe@email.com',
    patient_phone: '+1234567892',
    appointment_date: '2024-01-15',
    appointment_time: '10:00:00',
    duration_minutes: 30,
    status: 'pending',
    total_amount: 150.00
};

fetch('/wp-json/medx360/v1/bookings', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify(bookingData)
});
```

## Settings

The plugin includes comprehensive settings for:

- **Booking Settings**: Advance booking days, cancellation policies
- **Notification Settings**: Email, SMS, and reminder notifications
- **System Settings**: Timezone, date/time formats
- **Payment Settings**: Currency, payment gateways

## Permissions

The plugin uses WordPress user capabilities for access control:

- `manage_options` - Full access to all features
- `edit_posts` - Can manage clinics, hospitals, doctors, services, staff, and bookings
- `read` - Can view data

## Development

### File Structure

```
wp-content/plugins/medx360/
├── medx360.php                 # Main plugin file
├── includes/
│   ├── class-database.php     # Database management
│   ├── class-api-controller.php # Base API controller
│   ├── class-auth.php         # Authentication and permissions
│   ├── class-validator.php    # Data validation
│   ├── class-onboarding.php   # Onboarding functionality
│   └── api/
│       ├── class-clinics-api.php
│       ├── class-hospitals-api.php
│       ├── class-doctors-api.php
│       ├── class-services-api.php
│       ├── class-staff-api.php
│       ├── class-bookings-api.php
│       ├── class-payments-api.php
│       ├── class-consultations-api.php
│       └── class-onboarding-api.php
├── assets/
│   ├── css/
│   │   └── admin.css          # Admin styles
│   └── js/
│       └── admin.js           # Admin JavaScript
└── languages/                 # Translation files
```

### Hooks and Filters

The plugin provides various hooks for customization:

- `medx360_before_create_clinic` - Before creating a clinic
- `medx360_after_create_clinic` - After creating a clinic
- `medx360_before_create_booking` - Before creating a booking
- `medx360_after_create_booking` - After creating a booking

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## License

GPL v2 or later

## Support

For support and feature requests, please contact the development team.

## Changelog

### Version 1.0.0
- Initial release
- Complete REST API implementation
- Onboarding wizard
- Admin dashboard
- Comprehensive medical booking system
