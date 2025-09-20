<?php

namespace MedX360\Infrastructure\WP\Admin;

use MedX360\Infrastructure\Database\DatabaseManager;

/**
 * Appointments Admin Management
 * 
 * @package MedX360\Infrastructure\WP\Admin
 */
class AppointmentsAdmin
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
        if (!isset($_POST['medx360_appointment_action']) || !wp_verify_nonce($_POST['medx360_appointment_nonce'], 'medx360_appointment_action')) {
            return;
        }

        $action = sanitize_text_field($_POST['medx360_appointment_action']);
        
        switch ($action) {
            case 'create':
                $this->createAppointment();
                break;
            case 'update':
                $this->updateAppointment();
                break;
            case 'delete':
                $this->deleteAppointment();
                break;
        }
    }

    /**
     * Create new appointment
     */
    private function createAppointment()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('appointments');
        
        $data = [
            'patient_id' => intval($_POST['patient_id']),
            'provider_id' => intval($_POST['provider_id']),
            'location_id' => !empty($_POST['location_id']) ? intval($_POST['location_id']) : null,
            'room_id' => !empty($_POST['room_id']) ? intval($_POST['room_id']) : null,
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'start_time' => sanitize_text_field($_POST['start_time']),
            'end_time' => sanitize_text_field($_POST['end_time']),
            'duration' => intval($_POST['duration']),
            'status' => sanitize_text_field($_POST['status']),
            'notes' => sanitize_textarea_field($_POST['notes']),
            'internal_notes' => sanitize_textarea_field($_POST['internal_notes']),
            'reminder_sent' => 0,
            'confirmation_sent' => 0
        ];
        
        $result = $wpdb->insert($table_name, $data, ['%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Appointment created successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to create appointment.</p></div>';
            });
        }
    }

    /**
     * Update appointment
     */
    private function updateAppointment()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('appointments');
        $id = intval($_POST['appointment_id']);
        
        $data = [
            'patient_id' => intval($_POST['patient_id']),
            'provider_id' => intval($_POST['provider_id']),
            'location_id' => !empty($_POST['location_id']) ? intval($_POST['location_id']) : null,
            'room_id' => !empty($_POST['room_id']) ? intval($_POST['room_id']) : null,
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'start_time' => sanitize_text_field($_POST['start_time']),
            'end_time' => sanitize_text_field($_POST['end_time']),
            'duration' => intval($_POST['duration']),
            'status' => sanitize_text_field($_POST['status']),
            'notes' => sanitize_textarea_field($_POST['notes']),
            'internal_notes' => sanitize_textarea_field($_POST['internal_notes']),
            'reminder_sent' => intval($_POST['reminder_sent']),
            'confirmation_sent' => intval($_POST['confirmation_sent'])
        ];
        
        $result = $wpdb->update($table_name, $data, ['id' => $id], ['%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Appointment updated successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to update appointment.</p></div>';
            });
        }
    }

    /**
     * Delete appointment
     */
    private function deleteAppointment()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('appointments');
        $id = intval($_POST['appointment_id']);
        
        $result = $wpdb->delete($table_name, ['id' => $id], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Appointment deleted successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to delete appointment.</p></div>';
            });
        }
    }

    /**
     * Get dropdown options
     */
    private function getDropdownOptions($table, $value_field, $label_field, $where = '')
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName($table);
        $where_clause = $where ? "WHERE {$where}" : '';
        $query = "SELECT {$value_field}, {$label_field} FROM {$table_name} {$where_clause} ORDER BY {$label_field} ASC";
        
        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Render appointments admin page
     */
    public function renderPage()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('appointments');
        $patients_table = $this->dbManager->getTableName('patients');
        $providers_table = $this->dbManager->getTableName('providers');
        $locations_table = $this->dbManager->getTableName('locations');
        
        // Get appointments with related data
        $query = "
            SELECT 
                a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.email as patient_email,
                CONCAT(pr.first_name, ' ', pr.last_name) as provider_name,
                l.name as location_name
            FROM {$table_name} a 
            LEFT JOIN {$patients_table} p ON a.patient_id = p.id
            LEFT JOIN {$providers_table} pr ON a.provider_id = pr.id
            LEFT JOIN {$locations_table} l ON a.location_id = l.id
            ORDER BY a.appointment_date DESC, a.start_time DESC
        ";
        
        $appointments = $wpdb->get_results($query, ARRAY_A);
        
        // Get appointment for editing
        $edit_appointment = null;
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $edit_id = intval($_GET['edit']);
            $edit_appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $edit_id), ARRAY_A);
        }
        
        // Get dropdown options
        $patients = $this->getDropdownOptions('patients', 'id', 'CONCAT(first_name, " ", last_name)', "status = 'active'");
        $providers = $this->getDropdownOptions('providers', 'id', 'CONCAT(first_name, " ", last_name)', "status = 'active'");
        $locations = $this->getDropdownOptions('locations', 'id', 'name', "status = 'active'");
        
        ?>
        <div class="wrap">
            <h1>Appointments Management</h1>
            
            <?php if ($edit_appointment): ?>
                <h2>Edit Appointment</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_appointment_action', 'medx360_appointment_nonce'); ?>
                    <input type="hidden" name="medx360_appointment_action" value="update">
                    <input type="hidden" name="appointment_id" value="<?php echo esc_attr($edit_appointment['id']); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="patient_id">Patient *</label></th>
                            <td>
                                <select id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo esc_attr($patient['id']); ?>" <?php selected($edit_appointment['patient_id'], $patient['id']); ?>>
                                            <?php echo esc_html($patient['CONCAT(first_name, " ", last_name)']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="provider_id">Provider *</label></th>
                            <td>
                                <select id="provider_id" name="provider_id" required>
                                    <option value="">Select Provider</option>
                                    <?php foreach ($providers as $provider): ?>
                                        <option value="<?php echo esc_attr($provider['id']); ?>" <?php selected($edit_appointment['provider_id'], $provider['id']); ?>>
                                            <?php echo esc_html($provider['CONCAT(first_name, " ", last_name)']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="location_id">Location</label></th>
                            <td>
                                <select id="location_id" name="location_id">
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?php echo esc_attr($location['id']); ?>" <?php selected($edit_appointment['location_id'], $location['id']); ?>>
                                            <?php echo esc_html($location['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="appointment_date">Date *</label></th>
                            <td><input type="date" id="appointment_date" name="appointment_date" value="<?php echo esc_attr($edit_appointment['appointment_date']); ?>" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="start_time">Start Time *</label></th>
                            <td><input type="time" id="start_time" name="start_time" value="<?php echo esc_attr($edit_appointment['start_time']); ?>" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="end_time">End Time *</label></th>
                            <td><input type="time" id="end_time" name="end_time" value="<?php echo esc_attr($edit_appointment['end_time']); ?>" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="duration">Duration (minutes)</label></th>
                            <td><input type="number" id="duration" name="duration" value="<?php echo esc_attr($edit_appointment['duration']); ?>" min="1"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="status">Status</label></th>
                            <td>
                                <select id="status" name="status">
                                    <option value="scheduled" <?php selected($edit_appointment['status'], 'scheduled'); ?>>Scheduled</option>
                                    <option value="confirmed" <?php selected($edit_appointment['status'], 'confirmed'); ?>>Confirmed</option>
                                    <option value="in_progress" <?php selected($edit_appointment['status'], 'in_progress'); ?>>In Progress</option>
                                    <option value="completed" <?php selected($edit_appointment['status'], 'completed'); ?>>Completed</option>
                                    <option value="cancelled" <?php selected($edit_appointment['status'], 'cancelled'); ?>>Cancelled</option>
                                    <option value="no_show" <?php selected($edit_appointment['status'], 'no_show'); ?>>No Show</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="notes">Notes</label></th>
                            <td><textarea id="notes" name="notes" rows="3" cols="50"><?php echo esc_textarea($edit_appointment['notes']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="internal_notes">Internal Notes</label></th>
                            <td><textarea id="internal_notes" name="internal_notes" rows="3" cols="50"><?php echo esc_textarea($edit_appointment['internal_notes']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="reminder_sent">Reminder Sent</label></th>
                            <td><input type="checkbox" id="reminder_sent" name="reminder_sent" value="1" <?php checked($edit_appointment['reminder_sent'], 1); ?>></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="confirmation_sent">Confirmation Sent</label></th>
                            <td><input type="checkbox" id="confirmation_sent" name="confirmation_sent" value="1" <?php checked($edit_appointment['confirmation_sent'], 1); ?>></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Update Appointment">
                        <a href="<?php echo admin_url('admin.php?page=medx360-appointments'); ?>" class="button">Cancel</a>
                    </p>
                </form>
            <?php else: ?>
                <h2>Add New Appointment</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_appointment_action', 'medx360_appointment_nonce'); ?>
                    <input type="hidden" name="medx360_appointment_action" value="create">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="patient_id">Patient *</label></th>
                            <td>
                                <select id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo esc_attr($patient['id']); ?>">
                                            <?php echo esc_html($patient['CONCAT(first_name, " ", last_name)']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="provider_id">Provider *</label></th>
                            <td>
                                <select id="provider_id" name="provider_id" required>
                                    <option value="">Select Provider</option>
                                    <?php foreach ($providers as $provider): ?>
                                        <option value="<?php echo esc_attr($provider['id']); ?>">
                                            <?php echo esc_html($provider['CONCAT(first_name, " ", last_name)']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="location_id">Location</label></th>
                            <td>
                                <select id="location_id" name="location_id">
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?php echo esc_attr($location['id']); ?>">
                                            <?php echo esc_html($location['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="appointment_date">Date *</label></th>
                            <td><input type="date" id="appointment_date" name="appointment_date" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="start_time">Start Time *</label></th>
                            <td><input type="time" id="start_time" name="start_time" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="end_time">End Time *</label></th>
                            <td><input type="time" id="end_time" name="end_time" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="duration">Duration (minutes)</label></th>
                            <td><input type="number" id="duration" name="duration" value="30" min="1"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="status">Status</label></th>
                            <td>
                                <select id="status" name="status">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="confirmed">Confirmed</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="notes">Notes</label></th>
                            <td><textarea id="notes" name="notes" rows="3" cols="50"></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="internal_notes">Internal Notes</label></th>
                            <td><textarea id="internal_notes" name="internal_notes" rows="3" cols="50"></textarea></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Add Appointment">
                    </p>
                </form>
            <?php endif; ?>
            
            <h2>Existing Appointments</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Provider</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">No appointments found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo esc_html(date('M j, Y', strtotime($appointment['appointment_date']))); ?></td>
                                <td><?php echo esc_html(date('g:i A', strtotime($appointment['start_time'])) . ' - ' . date('g:i A', strtotime($appointment['end_time']))); ?></td>
                                <td><strong><?php echo esc_html($appointment['patient_name']); ?></strong></td>
                                <td><?php echo esc_html($appointment['provider_name']); ?></td>
                                <td><?php echo esc_html($appointment['location_name']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($appointment['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=medx360-appointments&edit=' . $appointment['id']); ?>" class="button button-small">Edit</a>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                        <?php wp_nonce_field('medx360_appointment_action', 'medx360_appointment_nonce'); ?>
                                        <input type="hidden" name="medx360_appointment_action" value="delete">
                                        <input type="hidden" name="appointment_id" value="<?php echo esc_attr($appointment['id']); ?>">
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
        .status-scheduled {
            color: #0073aa;
            font-weight: bold;
        }
        .status-confirmed {
            color: #46b450;
            font-weight: bold;
        }
        .status-in-progress {
            color: #ffc107;
            font-weight: bold;
        }
        .status-completed {
            color: #00a32a;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3232;
            font-weight: bold;
        }
        .status-no-show {
            color: #6c757d;
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
        if (strpos($hook, 'medx360-appointments') !== false) {
            wp_enqueue_style('medx360-admin', MEDX360_URL . 'assets/css/admin.css', [], MEDX360_VERSION);
            // No JavaScript needed - pure PHP/HTML/CSS solution
        }
    }
}
