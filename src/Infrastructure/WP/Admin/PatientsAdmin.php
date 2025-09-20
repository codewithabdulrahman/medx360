<?php

namespace MedX360\Infrastructure\WP\Admin;

use MedX360\Infrastructure\Database\DatabaseManager;

/**
 * Patients Admin Management
 * 
 * @package MedX360\Infrastructure\WP\Admin
 */
class PatientsAdmin
{
    private $dbManager;

    public function __construct()
    {
        $this->dbManager = new DatabaseManager();
        add_action('admin_init', [$this, 'handleFormSubmission']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    /**
     * Handle form submissions
     */
    public function handleFormSubmission()
    {
        if (!isset($_POST['medx360_patient_action']) || !wp_verify_nonce($_POST['medx360_patient_nonce'], 'medx360_patient_action')) {
            return;
        }

        $action = sanitize_text_field($_POST['medx360_patient_action']);
        
        switch ($action) {
            case 'create':
                $this->createPatient();
                break;
            case 'update':
                $this->updatePatient();
                break;
            case 'delete':
                $this->deletePatient();
                break;
        }
    }

    /**
     * Create new patient
     */
    private function createPatient()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('patients');
        
        $data = [
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'date_of_birth' => sanitize_text_field($_POST['date_of_birth']),
            'gender' => sanitize_text_field($_POST['gender']),
            'address' => sanitize_textarea_field($_POST['address']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'postal_code' => sanitize_text_field($_POST['postal_code']),
            'country' => sanitize_text_field($_POST['country']),
            'emergency_contact_name' => sanitize_text_field($_POST['emergency_contact_name']),
            'emergency_contact_phone' => sanitize_text_field($_POST['emergency_contact_phone']),
            'insurance_provider' => sanitize_text_field($_POST['insurance_provider']),
            'insurance_number' => sanitize_text_field($_POST['insurance_number']),
            'medical_history' => sanitize_textarea_field($_POST['medical_history']),
            'allergies' => sanitize_textarea_field($_POST['allergies']),
            'medications' => sanitize_textarea_field($_POST['medications']),
            'status' => 'active'
        ];
        
        $result = $wpdb->insert($table_name, $data, ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Patient created successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to create patient.</p></div>';
            });
        }
    }

    /**
     * Update patient
     */
    private function updatePatient()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('patients');
        $id = intval($_POST['patient_id']);
        
        $data = [
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'date_of_birth' => sanitize_text_field($_POST['date_of_birth']),
            'gender' => sanitize_text_field($_POST['gender']),
            'address' => sanitize_textarea_field($_POST['address']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'postal_code' => sanitize_text_field($_POST['postal_code']),
            'country' => sanitize_text_field($_POST['country']),
            'emergency_contact_name' => sanitize_text_field($_POST['emergency_contact_name']),
            'emergency_contact_phone' => sanitize_text_field($_POST['emergency_contact_phone']),
            'insurance_provider' => sanitize_text_field($_POST['insurance_provider']),
            'insurance_number' => sanitize_text_field($_POST['insurance_number']),
            'medical_history' => sanitize_textarea_field($_POST['medical_history']),
            'allergies' => sanitize_textarea_field($_POST['allergies']),
            'medications' => sanitize_textarea_field($_POST['medications']),
            'status' => sanitize_text_field($_POST['status'])
        ];
        
        $result = $wpdb->update($table_name, $data, ['id' => $id], ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Patient updated successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to update patient.</p></div>';
            });
        }
    }

    /**
     * Delete patient
     */
    private function deletePatient()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('patients');
        $id = intval($_POST['patient_id']);
        
        $result = $wpdb->update($table_name, ['status' => 'deleted'], ['id' => $id], ['%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Patient deleted successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to delete patient.</p></div>';
            });
        }
    }

    /**
     * Render patients admin page
     */
    public function renderPage()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('patients');
        $patients = $wpdb->get_results("SELECT * FROM {$table_name} WHERE status != 'deleted' ORDER BY first_name ASC, last_name ASC", ARRAY_A);
        
        // Get patient for editing
        $edit_patient = null;
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $edit_id = intval($_GET['edit']);
            $edit_patient = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $edit_id), ARRAY_A);
        }
        
        ?>
        <div class="wrap">
            <h1>Patients Management</h1>
            
            <?php if ($edit_patient): ?>
                <h2>Edit Patient</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_patient_action', 'medx360_patient_nonce'); ?>
                    <input type="hidden" name="medx360_patient_action" value="update">
                    <input type="hidden" name="patient_id" value="<?php echo esc_attr($edit_patient['id']); ?>">
                    
                    <div class="medx360-form-section">
                        <h3>Personal Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="first_name">First Name *</label></th>
                                <td><input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($edit_patient['first_name']); ?>" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="last_name">Last Name *</label></th>
                                <td><input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($edit_patient['last_name']); ?>" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="email">Email *</label></th>
                                <td><input type="email" id="email" name="email" value="<?php echo esc_attr($edit_patient['email']); ?>" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="phone">Phone</label></th>
                                <td><input type="tel" id="phone" name="phone" value="<?php echo esc_attr($edit_patient['phone']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="date_of_birth">Date of Birth</label></th>
                                <td><input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo esc_attr($edit_patient['date_of_birth']); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gender">Gender</label></th>
                                <td>
                                    <select id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php selected($edit_patient['gender'], 'male'); ?>>Male</option>
                                        <option value="female" <?php selected($edit_patient['gender'], 'female'); ?>>Female</option>
                                        <option value="other" <?php selected($edit_patient['gender'], 'other'); ?>>Other</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Address Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="address">Address</label></th>
                                <td><textarea id="address" name="address" rows="3" cols="50"><?php echo esc_textarea($edit_patient['address']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="city">City</label></th>
                                <td><input type="text" id="city" name="city" value="<?php echo esc_attr($edit_patient['city']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="state">State/Province</label></th>
                                <td><input type="text" id="state" name="state" value="<?php echo esc_attr($edit_patient['state']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="postal_code">Postal Code</label></th>
                                <td><input type="text" id="postal_code" name="postal_code" value="<?php echo esc_attr($edit_patient['postal_code']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="country">Country</label></th>
                                <td><input type="text" id="country" name="country" value="<?php echo esc_attr($edit_patient['country']); ?>" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Emergency Contact</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="emergency_contact_name">Emergency Contact Name</label></th>
                                <td><input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo esc_attr($edit_patient['emergency_contact_name']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="emergency_contact_phone">Emergency Contact Phone</label></th>
                                <td><input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo esc_attr($edit_patient['emergency_contact_phone']); ?>" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Insurance Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="insurance_provider">Insurance Provider</label></th>
                                <td><input type="text" id="insurance_provider" name="insurance_provider" value="<?php echo esc_attr($edit_patient['insurance_provider']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="insurance_number">Insurance Number</label></th>
                                <td><input type="text" id="insurance_number" name="insurance_number" value="<?php echo esc_attr($edit_patient['insurance_number']); ?>" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Medical Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="medical_history">Medical History</label></th>
                                <td><textarea id="medical_history" name="medical_history" rows="4" cols="50"><?php echo esc_textarea($edit_patient['medical_history']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="allergies">Allergies</label></th>
                                <td><textarea id="allergies" name="allergies" rows="3" cols="50"><?php echo esc_textarea($edit_patient['allergies']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="medications">Current Medications</label></th>
                                <td><textarea id="medications" name="medications" rows="3" cols="50"><?php echo esc_textarea($edit_patient['medications']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="status">Status</label></th>
                                <td>
                                    <select id="status" name="status">
                                        <option value="active" <?php selected($edit_patient['status'], 'active'); ?>>Active</option>
                                        <option value="inactive" <?php selected($edit_patient['status'], 'inactive'); ?>>Inactive</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Update Patient">
                        <a href="<?php echo admin_url('admin.php?page=medx360-patients'); ?>" class="button">Cancel</a>
                    </p>
                </form>
            <?php else: ?>
                <h2>Add New Patient</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_patient_action', 'medx360_patient_nonce'); ?>
                    <input type="hidden" name="medx360_patient_action" value="create">
                    
                    <div class="medx360-form-section">
                        <h3>Personal Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="first_name">First Name *</label></th>
                                <td><input type="text" id="first_name" name="first_name" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="last_name">Last Name *</label></th>
                                <td><input type="text" id="last_name" name="last_name" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="email">Email *</label></th>
                                <td><input type="email" id="email" name="email" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="phone">Phone</label></th>
                                <td><input type="tel" id="phone" name="phone" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="date_of_birth">Date of Birth</label></th>
                                <td><input type="date" id="date_of_birth" name="date_of_birth"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gender">Gender</label></th>
                                <td>
                                    <select id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Address Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="address">Address</label></th>
                                <td><textarea id="address" name="address" rows="3" cols="50"></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="city">City</label></th>
                                <td><input type="text" id="city" name="city" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="state">State/Province</label></th>
                                <td><input type="text" id="state" name="state" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="postal_code">Postal Code</label></th>
                                <td><input type="text" id="postal_code" name="postal_code" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="country">Country</label></th>
                                <td><input type="text" id="country" name="country" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Emergency Contact</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="emergency_contact_name">Emergency Contact Name</label></th>
                                <td><input type="text" id="emergency_contact_name" name="emergency_contact_name" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="emergency_contact_phone">Emergency Contact Phone</label></th>
                                <td><input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Insurance Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="insurance_provider">Insurance Provider</label></th>
                                <td><input type="text" id="insurance_provider" name="insurance_provider" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="insurance_number">Insurance Number</label></th>
                                <td><input type="text" id="insurance_number" name="insurance_number" class="regular-text"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="medx360-form-section">
                        <h3>Medical Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="medical_history">Medical History</label></th>
                                <td><textarea id="medical_history" name="medical_history" rows="4" cols="50"></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="allergies">Allergies</label></th>
                                <td><textarea id="allergies" name="allergies" rows="3" cols="50"></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="medications">Current Medications</label></th>
                                <td><textarea id="medications" name="medications" rows="3" cols="50"></textarea></td>
                            </tr>
                        </table>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Add Patient">
                    </p>
                </form>
            <?php endif; ?>
            
            <h2>Existing Patients</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Insurance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No patients found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><strong><?php echo esc_html($patient['first_name'] . ' ' . $patient['last_name']); ?></strong></td>
                                <td><?php echo esc_html($patient['email']); ?></td>
                                <td><?php echo esc_html($patient['phone']); ?></td>
                                <td><?php echo esc_html($patient['date_of_birth']); ?></td>
                                <td><?php echo esc_html(ucfirst($patient['gender'])); ?></td>
                                <td><?php echo esc_html($patient['insurance_provider']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($patient['status']); ?>">
                                        <?php echo ucfirst($patient['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=medx360-patients&edit=' . $patient['id']); ?>" class="button button-small">Edit</a>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this patient?');">
                                        <?php wp_nonce_field('medx360_patient_action', 'medx360_patient_nonce'); ?>
                                        <input type="hidden" name="medx360_patient_action" value="delete">
                                        <input type="hidden" name="patient_id" value="<?php echo esc_attr($patient['id']); ?>">
                                        <input type="submit" class="button button-small" value="Delete" style="color: #a00;">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .medx360-form {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        .medx360-form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .medx360-form-section:last-child {
            border-bottom: none;
        }
        .medx360-form-section h3 {
            margin-top: 0;
            color: #23282d;
        }
        .status-active {
            color: #46b450;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3232;
            font-weight: bold;
        }
        </style>
        <?php
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueueAdminScripts($hook)
    {
        if (strpos($hook, 'medx360-patients') !== false) {
            wp_enqueue_style('medx360-admin', MEDX360_URL . 'assets/css/admin.css', [], MEDX360_VERSION);
            // No JavaScript needed - pure PHP/HTML/CSS solution
        }
    }
}
