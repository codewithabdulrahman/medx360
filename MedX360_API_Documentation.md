# MedX360 API Documentation

## Overview

MedX360 is a comprehensive medical booking management system with REST API support for clinics, hospitals, doctors, consultations, and appointments. This document provides complete API documentation including endpoints, payloads, responses, and purposes.

## Base Information

- **Base URL**: `{your-site}/wp-json/medx360/v1/`
- **Authentication**: WordPress nonce authentication
- **Content-Type**: `application/json`
- **Required Header**: `X-WP-Nonce: {nonce}`

## Authentication

All API endpoints require WordPress authentication. Include the nonce in the request header:

```http
X-WP-Nonce: your-wordpress-nonce
```

## Common Response Format

### Success Response
```json
{
  "data": {...},
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total_items": 100,
    "total_pages": 10
  }
}
```

### Error Response
```json
{
  "code": "error_code",
  "message": "Error message",
  "data": {
    "status": 400
  }
}
```

## Common Query Parameters

Most collection endpoints support these parameters:

- `page` (integer): Current page (default: 1)
- `per_page` (integer): Items per page (default: 10, max: 100)
- `search` (string): Search term
- `orderby` (string): Sort field
- `order` (string): Sort direction (ASC/DESC)
- `status` (string): Filter by status

---

## 1. Clinics API

**Purpose**: Manage medical clinics and their information.

### Endpoints

#### GET /clinics
**Purpose**: Retrieve a list of clinics with pagination and filtering.

**Query Parameters**:
- `page`, `per_page`, `search`, `orderby`, `order` (common parameters)
- `status` (string): Filter by status (`active`, `inactive`, `pending`)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "City Medical Center",
      "slug": "city-medical-center",
      "description": "Full-service medical clinic",
      "address": "123 Main St",
      "city": "New York",
      "state": "NY",
      "country": "USA",
      "postal_code": "10001",
      "phone": "+1-555-0123",
      "email": "info@citymedical.com",
      "website": "https://citymedical.com",
      "logo_url": "https://example.com/logo.png",
      "status": "active",
      "settings": {},
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /clinics/{id}
**Purpose**: Retrieve a specific clinic by ID.

**Path Parameters**:
- `id` (integer): Clinic ID

**Response**: Single clinic object (same structure as above)

#### GET /clinics/slug/{slug}
**Purpose**: Retrieve a clinic by its slug.

**Path Parameters**:
- `slug` (string): Clinic slug

**Response**: Single clinic object

#### POST /clinics
**Purpose**: Create a new clinic.

**Request Payload**:
```json
{
  "name": "City Medical Center",
  "slug": "city-medical-center",
  "description": "Full-service medical clinic",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "postal_code": "10001",
  "phone": "+1-555-0123",
  "email": "info@citymedical.com",
  "website": "https://citymedical.com",
  "logo_url": "https://example.com/logo.png",
  "status": "active",
  "settings": {}
}
```

**Required Fields**: `name`, `slug`

**Response**: Created clinic object (201 status)

#### PUT /clinics/{id}
**Purpose**: Update an existing clinic.

**Path Parameters**:
- `id` (integer): Clinic ID

**Request Payload**: Same as POST (all fields optional)

**Response**: Updated clinic object

#### DELETE /clinics/{id}
**Purpose**: Delete a clinic.

**Path Parameters**:
- `id` (integer): Clinic ID

**Response**:
```json
{
  "message": "Clinic deleted successfully"
}
```

---

## 2. Hospitals API

**Purpose**: Manage hospitals associated with clinics.

### Endpoints

#### GET /hospitals
**Purpose**: Retrieve a list of hospitals with pagination and filtering.

**Query Parameters**:
- Common parameters
- `status` (string): Filter by status
- `clinic_id` (integer): Filter by clinic ID

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "clinic_id": 1,
      "name": "City General Hospital",
      "slug": "city-general-hospital",
      "description": "General hospital",
      "address": "456 Hospital Ave",
      "city": "New York",
      "state": "NY",
      "country": "USA",
      "postal_code": "10002",
      "phone": "+1-555-0456",
      "email": "info@citygeneral.com",
      "website": "https://citygeneral.com",
      "logo_url": "https://example.com/hospital-logo.png",
      "status": "active",
      "settings": {},
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /hospitals/{id}
**Purpose**: Retrieve a specific hospital by ID.

#### GET /hospitals/slug/{slug}
**Purpose**: Retrieve a hospital by its slug.

#### GET /hospitals/clinic/{clinic_id}
**Purpose**: Retrieve all hospitals for a specific clinic.

#### POST /hospitals
**Purpose**: Create a new hospital.

**Request Payload**:
```json
{
  "clinic_id": 1,
  "name": "City General Hospital",
  "slug": "city-general-hospital",
  "description": "General hospital",
  "address": "456 Hospital Ave",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "postal_code": "10002",
  "phone": "+1-555-0456",
  "email": "info@citygeneral.com",
  "website": "https://citygeneral.com",
  "logo_url": "https://example.com/hospital-logo.png",
  "status": "active",
  "settings": {}
}
```

**Required Fields**: `clinic_id`, `name`, `slug`

#### PUT /hospitals/{id}
**Purpose**: Update an existing hospital.

#### DELETE /hospitals/{id}
**Purpose**: Delete a hospital.

---

## 3. Doctors API

**Purpose**: Manage doctors and their schedules, availability, and information.

### Endpoints

#### GET /doctors
**Purpose**: Retrieve a list of doctors with pagination and filtering.

**Query Parameters**:
- Common parameters
- `status` (string): Filter by status
- `clinic_id` (integer): Filter by clinic ID
- `hospital_id` (integer): Filter by hospital ID
- `specialization` (string): Filter by specialization

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "clinic_id": 1,
      "hospital_id": 1,
      "user_id": 123,
      "first_name": "John",
      "last_name": "Smith",
      "full_name": "John Smith",
      "email": "john.smith@citymedical.com",
      "phone": "+1-555-0789",
      "specialization": "Cardiology",
      "license_number": "MD123456",
      "experience_years": 10,
      "education": "Harvard Medical School",
      "bio": "Experienced cardiologist",
      "profile_image": "https://example.com/doctor.jpg",
      "consultation_fee": 150.00,
      "status": "active",
      "settings": {},
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /doctors/{id}
**Purpose**: Retrieve a specific doctor by ID.

#### GET /doctors/clinic/{clinic_id}
**Purpose**: Retrieve all doctors for a specific clinic.

#### GET /doctors/hospital/{hospital_id}
**Purpose**: Retrieve all doctors for a specific hospital.

#### POST /doctors
**Purpose**: Create a new doctor.

**Request Payload**:
```json
{
  "clinic_id": 1,
  "hospital_id": 1,
  "user_id": 123,
  "first_name": "John",
  "last_name": "Smith",
  "email": "john.smith@citymedical.com",
  "phone": "+1-555-0789",
  "specialization": "Cardiology",
  "license_number": "MD123456",
  "experience_years": 10,
  "education": "Harvard Medical School",
  "bio": "Experienced cardiologist",
  "profile_image": "https://example.com/doctor.jpg",
  "consultation_fee": 150.00,
  "status": "active",
  "settings": {}
}
```

**Required Fields**: `clinic_id`, `first_name`, `last_name`, `email`

#### PUT /doctors/{id}
**Purpose**: Update an existing doctor.

#### DELETE /doctors/{id}
**Purpose**: Delete a doctor.

### Doctor Schedule Management

#### GET /doctors/{id}/schedule
**Purpose**: Retrieve doctor's weekly schedule.

**Response**:
```json
[
  {
    "id": 1,
    "doctor_id": 1,
    "day_of_week": 1,
    "start_time": "09:00:00",
    "end_time": "17:00:00",
    "is_available": 1,
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-01 10:00:00"
  }
]
```

#### POST /doctors/{id}/schedule
**Purpose**: Create a schedule entry for a doctor.

**Request Payload**:
```json
{
  "day_of_week": 1,
  "start_time": "09:00:00",
  "end_time": "17:00:00",
  "is_available": 1
}
```

**Required Fields**: `day_of_week`, `start_time`, `end_time`

#### PUT /doctors/{id}/schedule
**Purpose**: Update doctor's schedule.

### Doctor Availability Management

#### GET /doctors/{id}/availability
**Purpose**: Retrieve doctor's availability for specific dates.

**Query Parameters**:
- `date` (string): Specific date (optional)

**Response**:
```json
[
  {
    "id": 1,
    "doctor_id": 1,
    "date": "2024-01-15",
    "start_time": "09:00:00",
    "end_time": "12:00:00",
    "is_available": 1,
    "reason": "Available",
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-01 10:00:00"
  }
]
```

#### POST /doctors/{id}/availability
**Purpose**: Create availability entry for a doctor.

**Request Payload**:
```json
{
  "date": "2024-01-15",
  "start_time": "09:00:00",
  "end_time": "12:00:00",
  "is_available": 1,
  "reason": "Available"
}
```

**Required Fields**: `date`

---

## 4. Services API

**Purpose**: Manage medical services offered by clinics and hospitals.

### Endpoints

#### GET /services
**Purpose**: Retrieve a list of services with pagination and filtering.

**Query Parameters**:
- Common parameters
- `status` (string): Filter by status (`active`, `inactive`)
- `clinic_id` (integer): Filter by clinic ID
- `hospital_id` (integer): Filter by hospital ID
- `category` (string): Filter by category

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "clinic_id": 1,
      "hospital_id": 1,
      "name": "General Consultation",
      "description": "Standard medical consultation",
      "duration_minutes": 30,
      "price": 100.00,
      "category": "Consultation",
      "status": "active",
      "settings": {},
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /services/{id}
**Purpose**: Retrieve a specific service by ID.

#### GET /services/clinic/{clinic_id}
**Purpose**: Retrieve all services for a specific clinic.

#### GET /services/hospital/{hospital_id}
**Purpose**: Retrieve all services for a specific hospital.

#### POST /services
**Purpose**: Create a new service.

**Request Payload**:
```json
{
  "clinic_id": 1,
  "hospital_id": 1,
  "name": "General Consultation",
  "description": "Standard medical consultation",
  "duration_minutes": 30,
  "price": 100.00,
  "category": "Consultation",
  "status": "active",
  "settings": {}
}
```

**Required Fields**: `clinic_id`, `name`

#### PUT /services/{id}
**Purpose**: Update an existing service.

#### DELETE /services/{id}
**Purpose**: Delete a service.

---

## 5. Staff API

**Purpose**: Manage clinic and hospital staff members.

### Endpoints

#### GET /staff
**Purpose**: Retrieve a list of staff members with pagination and filtering.

**Query Parameters**:
- Common parameters
- `status` (string): Filter by status
- `clinic_id` (integer): Filter by clinic ID
- `hospital_id` (integer): Filter by hospital ID
- `role` (string): Filter by role
- `department` (string): Filter by department

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "clinic_id": 1,
      "hospital_id": 1,
      "user_id": 124,
      "first_name": "Jane",
      "last_name": "Doe",
      "full_name": "Jane Doe",
      "email": "jane.doe@citymedical.com",
      "phone": "+1-555-0321",
      "role": "Nurse",
      "department": "Emergency",
      "status": "active",
      "settings": {},
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /staff/{id}
**Purpose**: Retrieve a specific staff member by ID.

#### GET /staff/clinic/{clinic_id}
**Purpose**: Retrieve all staff members for a specific clinic.

#### POST /staff
**Purpose**: Create a new staff member.

**Request Payload**:
```json
{
  "clinic_id": 1,
  "hospital_id": 1,
  "user_id": 124,
  "first_name": "Jane",
  "last_name": "Doe",
  "email": "jane.doe@citymedical.com",
  "phone": "+1-555-0321",
  "role": "Nurse",
  "department": "Emergency",
  "status": "active",
  "settings": {}
}
```

**Required Fields**: `clinic_id`, `first_name`, `last_name`, `email`, `role`

#### PUT /staff/{id}
**Purpose**: Update an existing staff member.

#### DELETE /staff/{id}
**Purpose**: Delete a staff member.

---

## 6. Bookings API

**Purpose**: Manage patient appointments and bookings.

### Endpoints

#### GET /bookings
**Purpose**: Retrieve a list of bookings with pagination and filtering.

**Query Parameters**:
- Common parameters
- `status` (string): Filter by status (`pending`, `confirmed`, `cancelled`, `completed`, `no_show`)
- `payment_status` (string): Filter by payment status (`pending`, `paid`, `refunded`, `failed`)
- `clinic_id` (integer): Filter by clinic ID
- `hospital_id` (integer): Filter by hospital ID
- `doctor_id` (integer): Filter by doctor ID
- `service_id` (integer): Filter by service ID
- `appointment_date` (string): Filter by appointment date

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "clinic_id": 1,
      "hospital_id": 1,
      "doctor_id": 1,
      "service_id": 1,
      "patient_name": "John Patient",
      "patient_email": "john.patient@email.com",
      "patient_phone": "+1-555-0987",
      "patient_dob": "1990-01-01",
      "patient_gender": "male",
      "appointment_date": "2024-01-15",
      "appointment_time": "10:00:00",
      "duration_minutes": 30,
      "status": "confirmed",
      "notes": "Regular checkup",
      "total_amount": 100.00,
      "payment_status": "paid",
      "payment_method": "card",
      "payment_reference": "TXN123456",
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /bookings/{id}
**Purpose**: Retrieve a specific booking by ID.

#### GET /bookings/clinic/{clinic_id}
**Purpose**: Retrieve all bookings for a specific clinic.

**Query Parameters**:
- `date` (string): Filter by specific date (optional)

#### GET /bookings/doctor/{doctor_id}
**Purpose**: Retrieve all bookings for a specific doctor.

**Query Parameters**:
- `date` (string): Filter by specific date (optional)

#### POST /bookings
**Purpose**: Create a new booking.

**Request Payload**:
```json
{
  "clinic_id": 1,
  "hospital_id": 1,
  "doctor_id": 1,
  "service_id": 1,
  "patient_name": "John Patient",
  "patient_email": "john.patient@email.com",
  "patient_phone": "+1-555-0987",
  "patient_dob": "1990-01-01",
  "patient_gender": "male",
  "appointment_date": "2024-01-15",
  "appointment_time": "10:00:00",
  "duration_minutes": 30,
  "status": "pending",
  "notes": "Regular checkup",
  "total_amount": 100.00,
  "payment_status": "pending",
  "payment_method": "card",
  "payment_reference": "TXN123456"
}
```

**Required Fields**: `clinic_id`, `patient_name`, `patient_email`, `appointment_date`, `appointment_time`

#### PUT /bookings/{id}
**Purpose**: Update an existing booking.

#### DELETE /bookings/{id}
**Purpose**: Delete a booking.

### Booking Status Management

#### PUT /bookings/{id}/confirm
**Purpose**: Confirm a pending booking.

**Response**: Updated booking object with status "confirmed"

#### PUT /bookings/{id}/cancel
**Purpose**: Cancel a booking.

**Response**: Updated booking object with status "cancelled"

---

## 7. Payments API

**Purpose**: Manage payment transactions for bookings.

### Endpoints

#### GET /payments
**Purpose**: Retrieve a list of payments with pagination and filtering.

**Query Parameters**:
- Common parameters
- `status` (string): Filter by status (`pending`, `completed`, `failed`, `refunded`, `cancelled`)
- `payment_method` (string): Filter by payment method
- `payment_gateway` (string): Filter by payment gateway
- `booking_id` (integer): Filter by booking ID

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "booking_id": 1,
      "amount": 100.00,
      "currency": "USD",
      "payment_method": "card",
      "payment_gateway": "stripe",
      "transaction_id": "TXN123456",
      "status": "completed",
      "gateway_response": {
        "transaction_id": "stripe_txn_123",
        "status": "succeeded"
      },
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /payments/{id}
**Purpose**: Retrieve a specific payment by ID.

#### GET /payments/booking/{booking_id}
**Purpose**: Retrieve all payments for a specific booking.

#### POST /payments
**Purpose**: Create a new payment.

**Request Payload**:
```json
{
  "booking_id": 1,
  "amount": 100.00,
  "currency": "USD",
  "payment_method": "card",
  "payment_gateway": "stripe",
  "transaction_id": "TXN123456",
  "status": "completed",
  "gateway_response": {
    "transaction_id": "stripe_txn_123",
    "status": "succeeded"
  }
}
```

**Required Fields**: `booking_id`, `amount`, `payment_method`

#### PUT /payments/{id}
**Purpose**: Update an existing payment.

#### PUT /payments/{id}/refund
**Purpose**: Refund a completed payment.

**Response**: Updated payment object with status "refunded"

---

## 8. Consultations API

**Purpose**: Manage medical consultations and their records.

### Endpoints

#### GET /consultations
**Purpose**: Retrieve a list of consultations with pagination and filtering.

**Query Parameters**:
- Common parameters
- `status` (string): Filter by status (`scheduled`, `in_progress`, `completed`, `cancelled`)
- `consultation_type` (string): Filter by type (`in_person`, `video`, `phone`)
- `booking_id` (integer): Filter by booking ID
- `doctor_id` (integer): Filter by doctor ID
- `patient_id` (integer): Filter by patient ID

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "booking_id": 1,
      "doctor_id": 1,
      "patient_id": 1,
      "consultation_type": "in_person",
      "diagnosis": "Hypertension",
      "prescription": "Lisinopril 10mg daily",
      "notes": "Patient shows improvement",
      "follow_up_date": "2024-02-15",
      "status": "completed",
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "pagination": {...}
}
```

#### GET /consultations/{id}
**Purpose**: Retrieve a specific consultation by ID.

#### GET /consultations/booking/{booking_id}
**Purpose**: Retrieve all consultations for a specific booking.

#### GET /consultations/doctor/{doctor_id}
**Purpose**: Retrieve all consultations for a specific doctor.

**Query Parameters**:
- `date` (string): Filter by specific date (optional)

#### POST /consultations
**Purpose**: Create a new consultation.

**Request Payload**:
```json
{
  "booking_id": 1,
  "doctor_id": 1,
  "patient_id": 1,
  "consultation_type": "in_person",
  "diagnosis": "Hypertension",
  "prescription": "Lisinopril 10mg daily",
  "notes": "Patient shows improvement",
  "follow_up_date": "2024-02-15",
  "status": "scheduled"
}
```

**Required Fields**: `booking_id`, `doctor_id`

#### PUT /consultations/{id}
**Purpose**: Update an existing consultation.

#### DELETE /consultations/{id}
**Purpose**: Delete a consultation.

#### PUT /consultations/{id}/complete
**Purpose**: Mark a consultation as completed.

**Response**: Updated consultation object with status "completed"

---

## 9. Onboarding API

**Purpose**: Manage system setup and onboarding process.

### Endpoints

#### GET /onboarding/status
**Purpose**: Get the current setup status.

**Response**:
```json
{
  "is_completed": false,
  "next_step": "create_clinic",
  "progress": 25
}
```

#### GET /onboarding/steps
**Purpose**: Get all setup steps.

**Response**:
```json
[
  {
    "id": "create_clinic",
    "title": "Create Clinic",
    "description": "Set up your main clinic",
    "completed": false,
    "required": true
  },
  {
    "id": "add_services",
    "title": "Add Services",
    "description": "Define your medical services",
    "completed": false,
    "required": true
  }
]
```

#### GET /onboarding/progress
**Purpose**: Get setup progress information.

**Response**:
```json
{
  "progress_percentage": 25,
  "steps": [...]
}
```

#### GET /onboarding/statistics
**Purpose**: Get setup statistics.

**Response**:
```json
{
  "clinics_count": 1,
  "hospitals_count": 0,
  "doctors_count": 0,
  "services_count": 0,
  "staff_count": 0
}
```

#### POST /onboarding/clinic
**Purpose**: Create a default clinic during setup.

**Request Payload**:
```json
{
  "name": "My Medical Clinic",
  "description": "Primary care clinic",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "postal_code": "10001",
  "phone": "+1-555-0123",
  "email": "info@myclinic.com",
  "website": "https://myclinic.com",
  "logo_url": "https://example.com/logo.png"
}
```

**Required Fields**: `name`

**Response**:
```json
{
  "clinic_id": 1,
  "clinic": {...},
  "message": "Default clinic created successfully"
}
```

#### POST /onboarding/services
**Purpose**: Create default services for a clinic.

**Query Parameters**:
- `clinic_id` (integer): Clinic ID (required)

**Response**:
```json
{
  "service_ids": [1, 2, 3],
  "services": [...],
  "message": "Default services created successfully"
}
```

#### PUT /onboarding/complete
**Purpose**: Mark setup as completed.

**Response**:
```json
{
  "message": "Setup completed successfully",
  "is_completed": true
}
```

#### PUT /onboarding/reset
**Purpose**: Reset the setup process.

**Response**:
```json
{
  "message": "Setup reset successfully",
  "is_completed": false
}
```

---

## 10. Settings API

**Purpose**: Manage system-wide settings.

### Endpoints

#### GET /settings
**Purpose**: Retrieve current system settings.

**Response**:
```json
{
  "booking_advance_days": 30,
  "booking_cancellation_hours": 24,
  "email_notifications": true,
  "sms_notifications": false,
  "reminder_notifications": true,
  "timezone": "America/New_York",
  "date_format": "Y-m-d",
  "time_format": "H:i",
  "currency": "USD",
  "currency_symbol": "$",
  "payment_gateway": "stripe"
}
```

#### POST /settings
**Purpose**: Update system settings.

**Request Payload**:
```json
{
  "booking_advance_days": 30,
  "booking_cancellation_hours": 24,
  "email_notifications": true,
  "sms_notifications": false,
  "reminder_notifications": true,
  "timezone": "America/New_York",
  "date_format": "Y-m-d",
  "time_format": "H:i",
  "currency": "USD",
  "currency_symbol": "$",
  "payment_gateway": "stripe"
}
```

**Response**:
```json
{
  "message": "Settings saved successfully",
  "settings": {...}
}
```

---

## Error Codes

Common error codes returned by the API:

- `validation_error` (400): Invalid input data
- `clinic_not_found` (404): Clinic does not exist
- `hospital_not_found` (404): Hospital does not exist
- `doctor_not_found` (404): Doctor does not exist
- `service_not_found` (404): Service does not exist
- `staff_not_found` (404): Staff member does not exist
- `booking_not_found` (404): Booking does not exist
- `payment_not_found` (404): Payment does not exist
- `consultation_not_found` (404): Consultation does not exist
- `slug_exists` (400): Slug already exists
- `time_conflict` (400): Time slot already booked
- `invalid_status` (400): Invalid status for operation
- `create_failed` (500): Failed to create resource
- `update_failed` (500): Failed to update resource
- `delete_failed` (500): Failed to delete resource

---

## Rate Limiting

The API implements standard WordPress rate limiting. Excessive requests may result in temporary blocking.

## Versioning

Current API version: `v1`

The API uses URL-based versioning. Future versions will be available at `/medx360/v2/`, etc.

## Support

For API support and questions, please contact the MedX360 development team.
