<?php

namespace MedX360\Infrastructure\WP\Admin;

use MedX360\Infrastructure\Database\DatabaseManager;

/**
 * Locations Admin Management
 * 
 * @package MedX360\Infrastructure\WP\Admin
 */
class LocationsAdmin
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
        if (!isset($_POST['medx360_location_action']) || !wp_verify_nonce($_POST['medx360_location_nonce'], 'medx360_location_action')) {
            return;
        }

        $action = sanitize_text_field($_POST['medx360_location_action']);
        
        switch ($action) {
            case 'create':
                $this->createLocation();
                break;
            case 'update':
                $this->updateLocation();
                break;
            case 'delete':
                $this->deleteLocation();
                break;
        }
    }

    /**
     * Create new location
     */
    private function createLocation()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('locations');
        
        $data = [
            'name' => sanitize_text_field($_POST['name']),
            'address' => sanitize_textarea_field($_POST['address']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'postal_code' => sanitize_text_field($_POST['postal_code']),
            'country' => sanitize_text_field($_POST['country']),
            'phone' => sanitize_text_field($_POST['phone']),
            'email' => sanitize_email($_POST['email']),
            'latitude' => !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null,
            'longitude' => !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null,
            'status' => 'active'
        ];
        
        $result = $wpdb->insert($table_name, $data, ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Location created successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to create location.</p></div>';
            });
        }
    }

    /**
     * Update location
     */
    private function updateLocation()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('locations');
        $id = intval($_POST['location_id']);
        
        $data = [
            'name' => sanitize_text_field($_POST['name']),
            'address' => sanitize_textarea_field($_POST['address']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'postal_code' => sanitize_text_field($_POST['postal_code']),
            'country' => sanitize_text_field($_POST['country']),
            'phone' => sanitize_text_field($_POST['phone']),
            'email' => sanitize_email($_POST['email']),
            'latitude' => !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null,
            'longitude' => !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null,
            'status' => sanitize_text_field($_POST['status'])
        ];
        
        $result = $wpdb->update($table_name, $data, ['id' => $id], ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Location updated successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to update location.</p></div>';
            });
        }
    }

    /**
     * Delete location
     */
    private function deleteLocation()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('locations');
        $id = intval($_POST['location_id']);
        
        $result = $wpdb->update($table_name, ['status' => 'deleted'], ['id' => $id], ['%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Location deleted successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to delete location.</p></div>';
            });
        }
    }

    /**
     * Render locations admin page
     */
    public function renderPage()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('locations');
        $locations = $wpdb->get_results("SELECT * FROM {$table_name} WHERE status != 'deleted' ORDER BY name ASC", ARRAY_A);
        
        // Get location for editing
        $edit_location = null;
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $edit_id = intval($_GET['edit']);
            $edit_location = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $edit_id), ARRAY_A);
        }
        
        ?>
        <div class="wrap">
            <h1>Locations Management</h1>
            
            <?php if ($edit_location): ?>
                <h2>Edit Location</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_location_action', 'medx360_location_nonce'); ?>
                    <input type="hidden" name="medx360_location_action" value="update">
                    <input type="hidden" name="location_id" value="<?php echo esc_attr($edit_location['id']); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="name">Location Name *</label></th>
                            <td><input type="text" id="name" name="name" value="<?php echo esc_attr($edit_location['name']); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="address">Address</label></th>
                            <td><textarea id="address" name="address" rows="3" cols="50"><?php echo esc_textarea($edit_location['address']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="city">City</label></th>
                            <td><input type="text" id="city" name="city" value="<?php echo esc_attr($edit_location['city']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="state">State/Province</label></th>
                            <td><input type="text" id="state" name="state" value="<?php echo esc_attr($edit_location['state']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="postal_code">Postal Code</label></th>
                            <td><input type="text" id="postal_code" name="postal_code" value="<?php echo esc_attr($edit_location['postal_code']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="country">Country</label></th>
                            <td><input type="text" id="country" name="country" value="<?php echo esc_attr($edit_location['country']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="phone">Phone</label></th>
                            <td><input type="tel" id="phone" name="phone" value="<?php echo esc_attr($edit_location['phone']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="email">Email</label></th>
                            <td><input type="email" id="email" name="email" value="<?php echo esc_attr($edit_location['email']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="latitude">Latitude</label></th>
                            <td><input type="number" id="latitude" name="latitude" value="<?php echo esc_attr($edit_location['latitude']); ?>" step="any"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="longitude">Longitude</label></th>
                            <td><input type="number" id="longitude" name="longitude" value="<?php echo esc_attr($edit_location['longitude']); ?>" step="any"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="status">Status</label></th>
                            <td>
                                <select id="status" name="status">
                                    <option value="active" <?php selected($edit_location['status'], 'active'); ?>>Active</option>
                                    <option value="inactive" <?php selected($edit_location['status'], 'inactive'); ?>>Inactive</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Update Location">
                        <a href="<?php echo admin_url('admin.php?page=medx360-locations'); ?>" class="button">Cancel</a>
                    </p>
                </form>
            <?php else: ?>
                <h2>Add New Location</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_location_action', 'medx360_location_nonce'); ?>
                    <input type="hidden" name="medx360_location_action" value="create">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="name">Location Name *</label></th>
                            <td><input type="text" id="name" name="name" class="regular-text" required></td>
                        </tr>
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
                        <tr>
                            <th scope="row"><label for="phone">Phone</label></th>
                            <td><input type="tel" id="phone" name="phone" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="email">Email</label></th>
                            <td><input type="email" id="email" name="email" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="latitude">Latitude</label></th>
                            <td><input type="number" id="latitude" name="latitude" step="any"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="longitude">Longitude</label></th>
                            <td><input type="number" id="longitude" name="longitude" step="any"></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Add Location">
                    </p>
                </form>
            <?php endif; ?>
            
            <h2>Existing Locations</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($locations)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No locations found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($locations as $location): ?>
                            <tr>
                                <td><strong><?php echo esc_html($location['name']); ?></strong></td>
                                <td><?php echo esc_html($location['address']); ?></td>
                                <td><?php echo esc_html($location['city']); ?></td>
                                <td><?php echo esc_html($location['state']); ?></td>
                                <td><?php echo esc_html($location['phone']); ?></td>
                                <td><?php echo esc_html($location['email']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($location['status']); ?>">
                                        <?php echo ucfirst($location['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=medx360-locations&edit=' . $location['id']); ?>" class="button button-small">Edit</a>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this location?');">
                                        <?php wp_nonce_field('medx360_location_action', 'medx360_location_nonce'); ?>
                                        <input type="hidden" name="medx360_location_action" value="delete">
                                        <input type="hidden" name="location_id" value="<?php echo esc_attr($location['id']); ?>">
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
        if (strpos($hook, 'medx360-locations') !== false) {
            wp_enqueue_style('medx360-admin', MEDX360_URL . 'assets/css/admin.css', [], MEDX360_VERSION);
            // No JavaScript needed - pure PHP/HTML/CSS solution
        }
    }
}
