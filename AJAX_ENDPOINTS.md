# MedX360 AJAX Endpoints Documentation

This document provides a comprehensive overview of all AJAX endpoints available in the MedX360 plugin, organized by module and following standard HTTP methods (GET, POST, PUT/PATCH, DELETE).

## Authentication

All endpoints require WordPress nonce authentication. Include the nonce in your POST data:

```javascript
const formData = new FormData();
formData.append('action', 'medx360_get_clinics');
formData.append('nonce', window.medx360.nonce);
```

## Base URL

All AJAX requests should be sent to: `/wp-admin/admin-ajax.php`

## Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        // Response data here
    }
}
```

### Error Response
```json
{
    "success": false,
    "data": {
        "code": "error_code",
        "message": "Error message",
        "status": 400
    }
}
```

---

## 1. Clinics Module

### GET Endpoints

#### List All Clinics
- **Action**: `medx360_get_clinics`
- **Method**: POST (WordPress AJAX requirement)
- **Parameters**:
  - `page` (optional): Page number for pagination (default: 1)
  - `per_page` (optional): Items per page (default: 10, max: 100)
  - `search` (optional): Search term
  - `status` (optional): Filter by status (active, inactive, pending)
  - `orderby` (optional): Sort field (default: id)
  - `order` (optional): Sort direction (ASC, DESC, default: DESC)

#### Get Single Clinic
- **Action**: `medx360_get_clinic`
- **Method**: POST
- **Parameters**:
  - `id` (required): Clinic ID

#### Get Clinic by Slug
- **Action**: `medx360_get_clinic_by_slug`
- **Method**: POST
- **Parameters**:
  - `slug` (required): Clinic slug

### POST Endpoints

#### Create New Clinic
- **Action**: `medx360_create_clinic`
- **Method**: POST
- **Parameters**:
  - `name` (required): Clinic name
  - `slug` (required): Clinic slug
  - `description` (optional): Clinic description
  - `address` (optional): Clinic address
  - `city` (optional): City
  - `state` (optional): State
  - `country` (optional): Country
  - `postal_code` (optional): Postal code
  - `phone` (optional): Phone number
  - `email` (optional): Email address
  - `website` (optional): Website URL
  - `status` (optional): Status (default: active)

### PUT Endpoints

#### Update Clinic
- **Action**: `medx360_update_clinic`
- **Method**: POST
- **Parameters**:
  - `id` (required): Clinic ID
  - All clinic fields (same as create)

### DELETE Endpoints

#### Delete Clinic
- **Action**: `medx360_delete_clinic`
- **Method**: POST
- **Parameters**:
  - `id` (required): Clinic ID

---

## 2. Hospitals Module

### GET Endpoints

#### List All Hospitals
- **Action**: `medx360_get_hospitals`
- **Method**: POST
- **Parameters**:
  - `page` (optional): Page number
  - `per_page` (optional): Items per page
  - `search` (optional): Search term
  - `status` (optional): Filter by status
  - `clinic_id` (optional): Filter by clinic
  - `orderby` (optional): Sort field
  - `order` (optional): Sort direction

#### Get Single Hospital
- **Action**: `medx360_get_hospital`
- **Method**: POST
- **Parameters**:
  - `id` (required): Hospital ID

#### Get Hospital by Slug
- **Action**: `medx360_get_hospital_by_slug`
- **Method**: POST
- **Parameters**:
  - `slug` (required): Hospital slug

#### Get Hospitals by Clinic
- **Action**: `medx360_get_hospitals_by_clinic`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID

### POST Endpoints

#### Create New Hospital
- **Action**: `medx360_create_hospital`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Parent clinic ID
  - `name` (required): Hospital name
  - `slug` (required): Hospital slug
  - `description` (optional): Hospital description
  - `address` (optional): Hospital address
  - `city` (optional): City
  - `state` (optional): State
  - `country` (optional): Country
  - `postal_code` (optional): Postal code
  - `phone` (optional): Phone number
  - `email` (optional): Email address
  - `website` (optional): Website URL
  - `capacity` (optional): Bed capacity
  - `specialties` (optional): Medical specialties
  - `status` (optional): Status (default: active)

### PUT Endpoints

#### Update Hospital
- **Action**: `medx360_update_hospital`
- **Method**: POST
- **Parameters**:
  - `id` (required): Hospital ID
  - All hospital fields (same as create)

### DELETE Endpoints

#### Delete Hospital
- **Action**: `medx360_delete_hospital`
- **Method**: POST
- **Parameters**:
  - `id` (required): Hospital ID

---

## 3. Doctors Module

### GET Endpoints

#### List All Doctors
- **Action**: `medx360_get_doctors`
- **Method**: POST
- **Parameters**:
  - `page` (optional): Page number
  - `per_page` (optional): Items per page
  - `search` (optional): Search term
  - `status` (optional): Filter by status
  - `clinic_id` (optional): Filter by clinic
  - `hospital_id` (optional): Filter by hospital
  - `specialization` (optional): Filter by specialization
  - `orderby` (optional): Sort field
  - `order` (optional): Sort direction

#### Get Single Doctor
- **Action**: `medx360_get_doctor`
- **Method**: POST
- **Parameters**:
  - `id` (required): Doctor ID

#### Get Doctors by Clinic
- **Action**: `medx360_get_doctors_by_clinic`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID

#### Get Doctors by Hospital
- **Action**: `medx360_get_doctors_by_hospital`
- **Method**: POST
- **Parameters**:
  - `hospital_id` (required): Hospital ID

#### Get Doctor Schedule
- **Action**: `medx360_get_doctor_schedule`
- **Method**: POST
- **Parameters**:
  - `doctor_id` (required): Doctor ID

#### Get Doctor Availability
- **Action**: `medx360_get_doctor_availability`
- **Method**: POST
- **Parameters**:
  - `doctor_id` (required): Doctor ID
  - `date` (optional): Specific date

### POST Endpoints

#### Create New Doctor
- **Action**: `medx360_create_doctor`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID
  - `first_name` (required): First name
  - `last_name` (required): Last name
  - `email` (required): Email address
  - `hospital_id` (optional): Hospital ID
  - `user_id` (optional): WordPress user ID
  - `phone` (optional): Phone number
  - `specialization` (optional): Medical specialization
  - `license_number` (optional): License number
  - `experience_years` (optional): Years of experience
  - `education` (optional): Education background
  - `bio` (optional): Biography
  - `profile_image` (optional): Profile image URL
  - `consultation_fee` (optional): Consultation fee
  - `status` (optional): Status (default: active)

#### Create Doctor Schedule
- **Action**: `medx360_create_doctor_schedule`
- **Method**: POST
- **Parameters**:
  - `doctor_id` (required): Doctor ID
  - `day_of_week` (required): Day of week (1-7)
  - `start_time` (required): Start time
  - `end_time` (required): End time
  - `is_available` (optional): Available flag (default: 1)

#### Create Doctor Availability Exception
- **Action**: `medx360_create_doctor_availability`
- **Method**: POST
- **Parameters**:
  - `doctor_id` (required): Doctor ID
  - `date` (required): Date
  - `start_time` (optional): Start time
  - `end_time` (optional): End time
  - `is_available` (optional): Available flag (default: 1)
  - `reason` (optional): Reason for exception

### PUT Endpoints

#### Update Doctor
- **Action**: `medx360_update_doctor`
- **Method**: POST
- **Parameters**:
  - `id` (required): Doctor ID
  - All doctor fields (same as create)

#### Update Doctor Schedule
- **Action**: `medx360_update_doctor_schedule`
- **Method**: POST
- **Parameters**:
  - `id` (required): Schedule ID
  - All schedule fields (same as create)

### DELETE Endpoints

#### Delete Doctor
- **Action**: `medx360_delete_doctor`
- **Method**: POST
- **Parameters**:
  - `id` (required): Doctor ID

---

## 4. Services Module

### GET Endpoints

#### List All Services
- **Action**: `medx360_get_services`
- **Method**: POST
- **Parameters**:
  - `page` (optional): Page number
  - `per_page` (optional): Items per page
  - `search` (optional): Search term
  - `status` (optional): Filter by status
  - `clinic_id` (optional): Filter by clinic
  - `hospital_id` (optional): Filter by hospital
  - `category` (optional): Filter by category
  - `orderby` (optional): Sort field
  - `order` (optional): Sort direction

#### Get Single Service
- **Action**: `medx360_get_service`
- **Method**: POST
- **Parameters**:
  - `id` (required): Service ID

#### Get Services by Clinic
- **Action**: `medx360_get_services_by_clinic`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID

#### Get Services by Hospital
- **Action**: `medx360_get_services_by_hospital`
- **Method**: POST
- **Parameters**:
  - `hospital_id` (required): Hospital ID

### POST Endpoints

#### Create New Service
- **Action**: `medx360_create_service`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID
  - `name` (required): Service name
  - `hospital_id` (optional): Hospital ID
  - `description` (optional): Service description
  - `duration_minutes` (optional): Duration in minutes (default: 30)
  - `price` (optional): Service price
  - `category` (optional): Service category
  - `status` (optional): Status (default: active)

### PUT Endpoints

#### Update Service
- **Action**: `medx360_update_service`
- **Method**: POST
- **Parameters**:
  - `id` (required): Service ID
  - All service fields (same as create)

### DELETE Endpoints

#### Delete Service
- **Action**: `medx360_delete_service`
- **Method**: POST
- **Parameters**:
  - `id` (required): Service ID

---

## 5. Staff Module

### GET Endpoints

#### List All Staff
- **Action**: `medx360_get_staff`
- **Method**: POST
- **Parameters**:
  - `page` (optional): Page number
  - `per_page` (optional): Items per page
  - `search` (optional): Search term
  - `status` (optional): Filter by status
  - `clinic_id` (optional): Filter by clinic
  - `hospital_id` (optional): Filter by hospital
  - `role` (optional): Filter by role
  - `department` (optional): Filter by department
  - `orderby` (optional): Sort field
  - `order` (optional): Sort direction

#### Get Single Staff Member
- **Action**: `medx360_get_staff_member`
- **Method**: POST
- **Parameters**:
  - `id` (required): Staff ID

#### Get Staff by Clinic
- **Action**: `medx360_get_staff_by_clinic`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID

### POST Endpoints

#### Create New Staff Member
- **Action**: `medx360_create_staff`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID
  - `first_name` (required): First name
  - `last_name` (required): Last name
  - `email` (required): Email address
  - `hospital_id` (optional): Hospital ID
  - `user_id` (optional): WordPress user ID
  - `phone` (optional): Phone number
  - `role` (required): Staff role
  - `department` (optional): Department
  - `status` (optional): Status (default: active)

### PUT Endpoints

#### Update Staff Member
- **Action**: `medx360_update_staff`
- **Method**: POST
- **Parameters**:
  - `id` (required): Staff ID
  - All staff fields (same as create)

### DELETE Endpoints

#### Delete Staff Member
- **Action**: `medx360_delete_staff`
- **Method**: POST
- **Parameters**:
  - `id` (required): Staff ID

---

## 6. Bookings Module

### GET Endpoints

#### List All Bookings
- **Action**: `medx360_get_bookings`
- **Method**: POST
- **Parameters**:
  - `page` (optional): Page number
  - `per_page` (optional): Items per page
  - `search` (optional): Search term
  - `status` (optional): Filter by status
  - `clinic_id` (optional): Filter by clinic
  - `hospital_id` (optional): Filter by hospital
  - `doctor_id` (optional): Filter by doctor
  - `service_id` (optional): Filter by service
  - `payment_status` (optional): Filter by payment status
  - `appointment_date` (optional): Filter by appointment date
  - `orderby` (optional): Sort field
  - `order` (optional): Sort direction

#### Get Single Booking
- **Action**: `medx360_get_booking`
- **Method**: POST
- **Parameters**:
  - `id` (required): Booking ID

#### Get Bookings by Clinic
- **Action**: `medx360_get_bookings_by_clinic`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID

#### Get Bookings by Doctor
- **Action**: `medx360_get_bookings_by_doctor`
- **Method**: POST
- **Parameters**:
  - `doctor_id` (required): Doctor ID

### POST Endpoints

#### Create New Booking
- **Action**: `medx360_create_booking`
- **Method**: POST
- **Parameters**:
  - `clinic_id` (required): Clinic ID
  - `patient_name` (required): Patient name
  - `patient_email` (required): Patient email
  - `appointment_date` (required): Appointment date
  - `appointment_time` (required): Appointment time
  - `hospital_id` (optional): Hospital ID
  - `doctor_id` (optional): Doctor ID
  - `service_id` (optional): Service ID
  - `patient_phone` (optional): Patient phone
  - `patient_dob` (optional): Patient date of birth
  - `patient_gender` (optional): Patient gender
  - `duration_minutes` (optional): Duration in minutes
  - `notes` (optional): Booking notes
  - `total_amount` (optional): Total amount
  - `status` (optional): Status (default: pending)

### PUT Endpoints

#### Update Booking
- **Action**: `medx360_update_booking`
- **Method**: POST
- **Parameters**:
  - `id` (required): Booking ID
  - All booking fields (same as create)

#### Confirm Booking
- **Action**: `medx360_confirm_booking`
- **Method**: POST
- **Parameters**:
  - `id` (required): Booking ID

#### Cancel Booking
- **Action**: `medx360_cancel_booking`
- **Method**: POST
- **Parameters**:
  - `id` (required): Booking ID
  - `reason` (optional): Cancellation reason

### DELETE Endpoints

#### Delete Booking
- **Action**: `medx360_delete_booking`
- **Method**: POST
- **Parameters**:
  - `id` (required): Booking ID

---

## 7. Consultations Module

### GET Endpoints

#### List All Consultations
- **Action**: `medx360_get_consultations`
- **Method**: POST
- **Parameters**:
  - `page` (optional): Page number
  - `per_page` (optional): Items per page
  - `search` (optional): Search term
  - `status` (optional): Filter by status
  - `booking_id` (optional): Filter by booking
  - `doctor_id` (optional): Filter by doctor
  - `consultation_type` (optional): Filter by type
  - `orderby` (optional): Sort field
  - `order` (optional): Sort direction

#### Get Single Consultation
- **Action**: `medx360_get_consultation`
- **Method**: POST
- **Parameters**:
  - `id` (required): Consultation ID

#### Get Consultations by Booking
- **Action**: `medx360_get_consultations_by_booking`
- **Method**: POST
- **Parameters**:
  - `booking_id` (required): Booking ID

#### Get Consultations by Doctor
- **Action**: `medx360_get_consultations_by_doctor`
- **Method**: POST
- **Parameters**:
  - `doctor_id` (required): Doctor ID

### POST Endpoints

#### Create New Consultation
- **Action**: `medx360_create_consultation`
- **Method**: POST
- **Parameters**:
  - `booking_id` (required): Booking ID
  - `doctor_id` (required): Doctor ID
  - `patient_id` (optional): Patient ID
  - `consultation_type` (optional): Type (in_person, video, phone)
  - `diagnosis` (optional): Diagnosis
  - `prescription` (optional): Prescription
  - `notes` (optional): Consultation notes
  - `follow_up_date` (optional): Follow-up date
  - `status` (optional): Status (default: scheduled)

### PUT Endpoints

#### Update Consultation
- **Action**: `medx360_update_consultation`
- **Method**: POST
- **Parameters**:
  - `id` (required): Consultation ID
  - All consultation fields (same as create)

#### Complete Consultation
- **Action**: `medx360_complete_consultation`
- **Method**: POST
- **Parameters**:
  - `id` (required): Consultation ID
  - `diagnosis` (optional): Final diagnosis
  - `prescription` (optional): Final prescription
  - `notes` (optional): Final notes

### DELETE Endpoints

#### Delete Consultation
- **Action**: `medx360_delete_consultation`
- **Method**: POST
- **Parameters**:
  - `id` (required): Consultation ID

---

## 8. Payments Module

### GET Endpoints

#### List All Payments
- **Action**: `medx360_get_payments`
- **Method**: POST
- **Parameters**:
  - `page` (optional): Page number
  - `per_page` (optional): Items per page
  - `search` (optional): Search term
  - `status` (optional): Filter by status
  - `booking_id` (optional): Filter by booking
  - `payment_method` (optional): Filter by payment method
  - `payment_gateway` (optional): Filter by payment gateway
  - `orderby` (optional): Sort field
  - `order` (optional): Sort direction

#### Get Single Payment
- **Action**: `medx360_get_payment`
- **Method**: POST
- **Parameters**:
  - `id` (required): Payment ID

#### Get Payments by Booking
- **Action**: `medx360_get_payments_by_booking`
- **Method**: POST
- **Parameters**:
  - `booking_id` (required): Booking ID

### POST Endpoints

#### Create New Payment
- **Action**: `medx360_create_payment`
- **Method**: POST
- **Parameters**:
  - `booking_id` (required): Booking ID
  - `amount` (required): Payment amount
  - `payment_method` (required): Payment method
  - `currency` (optional): Currency (default: USD)
  - `payment_gateway` (optional): Payment gateway
  - `transaction_id` (optional): Transaction ID
  - `status` (optional): Status (default: pending)
  - `gateway_response` (optional): Gateway response data

### PUT Endpoints

#### Update Payment
- **Action**: `medx360_update_payment`
- **Method**: POST
- **Parameters**:
  - `id` (required): Payment ID
  - All payment fields (same as create)

#### Refund Payment
- **Action**: `medx360_refund_payment`
- **Method**: POST
- **Parameters**:
  - `id` (required): Payment ID
  - `refund_amount` (optional): Refund amount (default: full amount)
  - `reason` (optional): Refund reason

### DELETE Endpoints

#### Delete Payment
- **Action**: `medx360_delete_payment`
- **Method**: POST
- **Parameters**:
  - `id` (required): Payment ID

---

## Usage Examples

### JavaScript Example

```javascript
// Get all clinics
async function getClinics() {
    const formData = new FormData();
    formData.append('action', 'medx360_get_clinics');
    formData.append('nonce', window.medx360.nonce);
    formData.append('page', '1');
    formData.append('per_page', '10');
    
    try {
        const response = await fetch(window.medx360.ajax_url, {
            method: 'POST',
            body: formData,
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('Clinics:', result.data);
        } else {
            console.error('Error:', result.data.message);
        }
    } catch (error) {
        console.error('Request failed:', error);
    }
}

// Create a new clinic
async function createClinic(clinicData) {
    const formData = new FormData();
    formData.append('action', 'medx360_create_clinic');
    formData.append('nonce', window.medx360.nonce);
    
    // Add clinic data
    Object.keys(clinicData).forEach(key => {
        formData.append(key, clinicData[key]);
    });
    
    try {
        const response = await fetch(window.medx360.ajax_url, {
            method: 'POST',
            body: formData,
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('Clinic created:', result.data);
        } else {
            console.error('Error:', result.data.message);
        }
    } catch (error) {
        console.error('Request failed:', error);
    }
}
```

### PHP Example

```php
// Using WordPress AJAX in PHP
add_action('wp_ajax_my_custom_action', 'my_custom_handler');

function my_custom_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'medx360_ajax')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    // Make internal AJAX call
    $response = wp_remote_post(admin_url('admin-ajax.php'), array(
        'body' => array(
            'action' => 'medx360_get_clinics',
            'nonce' => wp_create_nonce('medx360_ajax'),
            'page' => 1,
            'per_page' => 10
        )
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error('Request failed');
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    wp_send_json_success($data);
}
```

## Error Codes

Common error codes returned by the API:

- `invalid_nonce`: Invalid or missing nonce
- `permission_denied`: User lacks required permissions
- `validation_error`: Input validation failed
- `not_found`: Requested resource not found
- `duplicate_entry`: Resource already exists
- `database_error`: Database operation failed
- `invalid_data`: Invalid data provided

## Rate Limiting

The plugin implements basic rate limiting to prevent abuse:

- Maximum 100 requests per minute per user
- Maximum 1000 requests per hour per user
- Automatic blocking for excessive requests

## Caching

The plugin implements intelligent caching:

- Query results are cached for 1 hour
- User-specific data is cached for 30 minutes
- Statistics are cached for 30 minutes
- Cache is automatically invalidated on data changes

## Security Features

- Nonce verification for all requests
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Role-based access control
- Security event logging
- Rate limiting
- CSRF protection
