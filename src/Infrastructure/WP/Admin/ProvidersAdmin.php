<?php

namespace MedX360\Infrastructure\WP\Admin;

use MedX360\Infrastructure\Database\DatabaseManager;

/**
 * Providers Admin Management
 * 
 * @package MedX360\Infrastructure\WP\Admin
 */
class ProvidersAdmin
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
        if (!isset($_POST['medx360_provider_action']) || !wp_verify_nonce($_POST['medx360_provider_nonce'], 'medx360_provider_action')) {
            return;
        }

        $action = sanitize_text_field($_POST['medx360_provider_action']);
        
        switch ($action) {
            case 'create':
                $this->createProvider();
                break;
            case 'update':
                $this->updateProvider();
                break;
            case 'delete':
                $this->deleteProvider();
                break;
        }
    }

    /**
     * Create new provider
     */
    private function createProvider()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('providers');
        
        $data = [
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'title' => sanitize_text_field($_POST['title']),
            'specialization' => sanitize_text_field($_POST['specialization']),
            'license_number' => sanitize_text_field($_POST['license_number']),
            'bio' => sanitize_textarea_field($_POST['bio']),
            'profile_image' => sanitize_url($_POST['profile_image']),
            'working_hours' => sanitize_textarea_field($_POST['working_hours']),
            'status' => 'active'
        ];
        
        $result = $wpdb->insert($table_name, $data, ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Provider created successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to create provider.</p></div>';
            });
        }
    }

    /**
     * Update provider
     */
    private function updateProvider()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('providers');
        $id = intval($_POST['provider_id']);
        
        $data = [
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'title' => sanitize_text_field($_POST['title']),
            'specialization' => sanitize_text_field($_POST['specialization']),
            'license_number' => sanitize_text_field($_POST['license_number']),
            'bio' => sanitize_textarea_field($_POST['bio']),
            'profile_image' => sanitize_url($_POST['profile_image']),
            'working_hours' => sanitize_textarea_field($_POST['working_hours']),
            'status' => sanitize_text_field($_POST['status'])
        ];
        
        $result = $wpdb->update($table_name, $data, ['id' => $id], ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Provider updated successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to update provider.</p></div>';
            });
        }
    }

    /**
     * Delete provider
     */
    private function deleteProvider()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('providers');
        $id = intval($_POST['provider_id']);
        
        $result = $wpdb->update($table_name, ['status' => 'deleted'], ['id' => $id], ['%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Provider deleted successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to delete provider.</p></div>';
            });
        }
    }

    /**
     * Render providers admin page
     */
    public function renderPage()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('providers');
        $providers = $wpdb->get_results("SELECT * FROM {$table_name} WHERE status != 'deleted' ORDER BY first_name ASC, last_name ASC", ARRAY_A);
        
        // Get provider for editing
        $edit_provider = null;
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $edit_id = intval($_GET['edit']);
            $edit_provider = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $edit_id), ARRAY_A);
        }
        
        ?>
        <div class="wrap">
            <h1>Providers Management</h1>
            
            <?php if ($edit_provider): ?>
                <h2>Edit Provider</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_provider_action', 'medx360_provider_nonce'); ?>
                    <input type="hidden" name="medx360_provider_action" value="update">
                    <input type="hidden" name="provider_id" value="<?php echo esc_attr($edit_provider['id']); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="first_name">First Name *</label></th>
                            <td><input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($edit_provider['first_name']); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="last_name">Last Name *</label></th>
                            <td><input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($edit_provider['last_name']); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="email">Email *</label></th>
                            <td><input type="email" id="email" name="email" value="<?php echo esc_attr($edit_provider['email']); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="phone">Phone</label></th>
                            <td><input type="tel" id="phone" name="phone" value="<?php echo esc_attr($edit_provider['phone']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="title">Title</label></th>
                            <td><input type="text" id="title" name="title" value="<?php echo esc_attr($edit_provider['title']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="specialization">Specialization</label></th>
                            <td><input type="text" id="specialization" name="specialization" value="<?php echo esc_attr($edit_provider['specialization']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="license_number">License Number</label></th>
                            <td><input type="text" id="license_number" name="license_number" value="<?php echo esc_attr($edit_provider['license_number']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bio">Bio</label></th>
                            <td><textarea id="bio" name="bio" rows="4" cols="50"><?php echo esc_textarea($edit_provider['bio']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="profile_image">Profile Image URL</label></th>
                            <td><input type="url" id="profile_image" name="profile_image" value="<?php echo esc_attr($edit_provider['profile_image']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="working_hours">Working Hours</label></th>
                            <td><textarea id="working_hours" name="working_hours" rows="3" cols="50" placeholder="e.g., Monday-Friday: 9:00 AM - 5:00 PM"><?php echo esc_textarea($edit_provider['working_hours']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="status">Status</label></th>
                            <td>
                                <select id="status" name="status">
                                    <option value="active" <?php selected($edit_provider['status'], 'active'); ?>>Active</option>
                                    <option value="inactive" <?php selected($edit_provider['status'], 'inactive'); ?>>Inactive</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Update Provider">
                        <a href="<?php echo admin_url('admin.php?page=medx360-providers'); ?>" class="button">Cancel</a>
                    </p>
                </form>
            <?php else: ?>
                <h2>Add New Provider</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_provider_action', 'medx360_provider_nonce'); ?>
                    <input type="hidden" name="medx360_provider_action" value="create">
                    
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
                            <th scope="row"><label for="title">Title</label></th>
                            <td><input type="text" id="title" name="title" class="regular-text" placeholder="e.g., Dr., MD, RN"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="specialization">Specialization</label></th>
                            <td><input type="text" id="specialization" name="specialization" class="regular-text" placeholder="e.g., Cardiology, Pediatrics"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="license_number">License Number</label></th>
                            <td><input type="text" id="license_number" name="license_number" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="bio">Bio</label></th>
                            <td><textarea id="bio" name="bio" rows="4" cols="50"></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="profile_image">Profile Image URL</label></th>
                            <td><input type="url" id="profile_image" name="profile_image" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="working_hours">Working Hours</label></th>
                            <td><textarea id="working_hours" name="working_hours" rows="3" cols="50" placeholder="e.g., Monday-Friday: 9:00 AM - 5:00 PM"></textarea></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Add Provider">
                    </p>
                </form>
            <?php endif; ?>
            
            <h2>Existing Providers</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Specialization</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>License</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($providers)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No providers found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($providers as $provider): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($provider['first_name'] . ' ' . $provider['last_name']); ?></strong>
                                    <?php if ($provider['profile_image']): ?>
                                        <img src="<?php echo esc_url($provider['profile_image']); ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; margin-left: 10px;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($provider['title']); ?></td>
                                <td><?php echo esc_html($provider['specialization']); ?></td>
                                <td><?php echo esc_html($provider['email']); ?></td>
                                <td><?php echo esc_html($provider['phone']); ?></td>
                                <td><?php echo esc_html($provider['license_number']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($provider['status']); ?>">
                                        <?php echo ucfirst($provider['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=medx360-providers&edit=' . $provider['id']); ?>" class="button button-small">Edit</a>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this provider?');">
                                        <?php wp_nonce_field('medx360_provider_action', 'medx360_provider_nonce'); ?>
                                        <input type="hidden" name="medx360_provider_action" value="delete">
                                        <input type="hidden" name="provider_id" value="<?php echo esc_attr($provider['id']); ?>">
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
        if (strpos($hook, 'medx360-providers') !== false) {
            wp_enqueue_style('medx360-admin', MEDX360_URL . 'assets/css/admin.css', [], MEDX360_VERSION);
            // No JavaScript needed - pure PHP/HTML/CSS solution
        }
    }
}
