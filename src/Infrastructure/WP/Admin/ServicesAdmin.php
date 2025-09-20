<?php

namespace MedX360\Infrastructure\WP\Admin;

use MedX360\Infrastructure\Database\DatabaseManager;

/**
 * Services Admin Management
 * 
 * @package MedX360\Infrastructure\WP\Admin
 */
class ServicesAdmin
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
        if (!isset($_POST['medx360_service_action']) || !wp_verify_nonce($_POST['medx360_service_nonce'], 'medx360_service_action')) {
            return;
        }

        $action = sanitize_text_field($_POST['medx360_service_action']);
        
        switch ($action) {
            case 'create':
                $this->createService();
                break;
            case 'update':
                $this->updateService();
                break;
            case 'delete':
                $this->deleteService();
                break;
        }
    }

    /**
     * Create new service
     */
    private function createService()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('services');
        
        $data = [
            'name' => sanitize_text_field($_POST['name']),
            'description' => sanitize_textarea_field($_POST['description']),
            'duration' => intval($_POST['duration']),
            'price' => floatval($_POST['price']),
            'category' => sanitize_text_field($_POST['category']),
            'color' => sanitize_text_field($_POST['color']),
            'status' => 'active'
        ];
        
        $result = $wpdb->insert($table_name, $data, ['%s', '%s', '%d', '%f', '%s', '%s', '%s']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Service created successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to create service.</p></div>';
            });
        }
    }

    /**
     * Update service
     */
    private function updateService()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('services');
        $id = intval($_POST['service_id']);
        
        $data = [
            'name' => sanitize_text_field($_POST['name']),
            'description' => sanitize_textarea_field($_POST['description']),
            'duration' => intval($_POST['duration']),
            'price' => floatval($_POST['price']),
            'category' => sanitize_text_field($_POST['category']),
            'color' => sanitize_text_field($_POST['color']),
            'status' => sanitize_text_field($_POST['status'])
        ];
        
        $result = $wpdb->update($table_name, $data, ['id' => $id], ['%s', '%s', '%d', '%f', '%s', '%s', '%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Service updated successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to update service.</p></div>';
            });
        }
    }

    /**
     * Delete service
     */
    private function deleteService()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('services');
        $id = intval($_POST['service_id']);
        
        $result = $wpdb->update($table_name, ['status' => 'deleted'], ['id' => $id], ['%s'], ['%d']);
        
        if ($result !== false) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Service deleted successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to delete service.</p></div>';
            });
        }
    }

    /**
     * Render services admin page
     */
    public function renderPage()
    {
        global $wpdb;
        
        $table_name = $this->dbManager->getTableName('services');
        $services = $wpdb->get_results("SELECT * FROM {$table_name} WHERE status != 'deleted' ORDER BY name ASC", ARRAY_A);
        
        // Get service for editing
        $edit_service = null;
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $edit_id = intval($_GET['edit']);
            $edit_service = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $edit_id), ARRAY_A);
        }
        
        ?>
        <div class="wrap">
            <h1>Services Management</h1>
            
            <?php if ($edit_service): ?>
                <h2>Edit Service</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_service_action', 'medx360_service_nonce'); ?>
                    <input type="hidden" name="medx360_service_action" value="update">
                    <input type="hidden" name="service_id" value="<?php echo esc_attr($edit_service['id']); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="name">Service Name *</label></th>
                            <td><input type="text" id="name" name="name" value="<?php echo esc_attr($edit_service['name']); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="description">Description</label></th>
                            <td><textarea id="description" name="description" rows="4" cols="50"><?php echo esc_textarea($edit_service['description']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="duration">Duration (minutes) *</label></th>
                            <td><input type="number" id="duration" name="duration" value="<?php echo esc_attr($edit_service['duration']); ?>" min="1" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="price">Price</label></th>
                            <td><input type="number" id="price" name="price" value="<?php echo esc_attr($edit_service['price']); ?>" step="0.01" min="0"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="category">Category</label></th>
                            <td><input type="text" id="category" name="category" value="<?php echo esc_attr($edit_service['category']); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="color">Color</label></th>
                            <td><input type="color" id="color" name="color" value="<?php echo esc_attr($edit_service['color']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="status">Status</label></th>
                            <td>
                                <select id="status" name="status">
                                    <option value="active" <?php selected($edit_service['status'], 'active'); ?>>Active</option>
                                    <option value="inactive" <?php selected($edit_service['status'], 'inactive'); ?>>Inactive</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Update Service">
                        <a href="<?php echo admin_url('admin.php?page=medx360-services'); ?>" class="button">Cancel</a>
                    </p>
                </form>
            <?php else: ?>
                <h2>Add New Service</h2>
                <form method="post" class="medx360-form">
                    <?php wp_nonce_field('medx360_service_action', 'medx360_service_nonce'); ?>
                    <input type="hidden" name="medx360_service_action" value="create">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="name">Service Name *</label></th>
                            <td><input type="text" id="name" name="name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="description">Description</label></th>
                            <td><textarea id="description" name="description" rows="4" cols="50"></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="duration">Duration (minutes) *</label></th>
                            <td><input type="number" id="duration" name="duration" value="30" min="1" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="price">Price</label></th>
                            <td><input type="number" id="price" name="price" step="0.01" min="0"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="category">Category</label></th>
                            <td><input type="text" id="category" name="category" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="color">Color</label></th>
                            <td><input type="color" id="color" name="color" value="#007cba"></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Add Service">
                    </p>
                </form>
            <?php endif; ?>
            
            <h2>Existing Services</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">No services found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($service['name']); ?></strong>
                                    <div style="width: 20px; height: 20px; background-color: <?php echo esc_attr($service['color']); ?>; display: inline-block; margin-left: 10px; border-radius: 3px;"></div>
                                </td>
                                <td><?php echo esc_html(wp_trim_words($service['description'], 10)); ?></td>
                                <td><?php echo esc_html($service['duration']); ?> min</td>
                                <td><?php echo $service['price'] ? '$' . number_format($service['price'], 2) : 'N/A'; ?></td>
                                <td><?php echo esc_html($service['category']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($service['status']); ?>">
                                        <?php echo ucfirst($service['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=medx360-services&edit=' . $service['id']); ?>" class="button button-small">Edit</a>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                        <?php wp_nonce_field('medx360_service_action', 'medx360_service_nonce'); ?>
                                        <input type="hidden" name="medx360_service_action" value="delete">
                                        <input type="hidden" name="service_id" value="<?php echo esc_attr($service['id']); ?>">
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
        if (strpos($hook, 'medx360-services') !== false) {
            wp_enqueue_style('medx360-admin', MEDX360_URL . 'assets/css/admin.css', [], MEDX360_VERSION);
            // No JavaScript needed - pure PHP/HTML/CSS solution
        }
    }
}
