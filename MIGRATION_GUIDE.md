# MedX360 Database Migration System

## Overview

The MedX360 plugin includes a robust database migration system that allows you to safely add new tables, columns, indexes, and modify existing database structures without losing data.

## How It Works

### 1. **Version Tracking**
- Each migration is tied to a specific plugin version
- The system tracks the current migration version in the database
- Migrations only run when the plugin version increases

### 2. **Safe Schema Changes**
- Uses `dbDelta()` for intelligent table creation/updates
- Checks for existing columns/indexes before adding them
- Provides rollback capabilities for development/testing

### 3. **Automatic Execution**
- Migrations run automatically during plugin activation
- No manual intervention required for end users

## File Structure

```
wp-content/plugins/medx360/
├── includes/
│   ├── class-migration.php          # Migration system core
│   ├── class-database.php           # Database utilities
│   └── migrations/                  # Migration files directory
│       ├── 002_add_patient_table.php
│       ├── 003_add_notifications_table.php
│       ├── 004_add_appointment_reminders.php
│       └── 005_add_multi_language_support.php
└── migration-cli.php               # CLI tool for managing migrations
```

## Creating New Migrations

### Method 1: Using CLI Tool (Recommended)

```bash
# Navigate to plugin directory
cd wp-content/plugins/medx360/

# Create a new migration
php migration-cli.php create add_new_feature 1.5.0
```

This creates a template file: `includes/migrations/YYYY_MM_DD_HHMMSS_add_new_feature.php`

### Method 2: Manual Creation

1. Create a new file in `includes/migrations/` directory
2. Follow the naming convention: `XXX_description.php`
3. Use the template structure below

## Migration File Template

```php
<?php
/**
 * Migration: [Description]
 * Version: [Version Number]
 * Created: [Date]
 */

if (!defined('ABSPATH')) {
    exit;
}

class MedX360_Migration_[ClassName] {
    
    /**
     * Run the migration
     */
    public function up() {
        global $wpdb;
        
        // Your migration code here
        // Examples:
        
        // 1. Create new table
        $table_name = $wpdb->prefix . 'medx360_new_table';
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) " . $wpdb->get_charset_collate() . ";";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // 2. Add column to existing table
        MedX360_Database::add_column('bookings', 'new_field', 'varchar(255)');
        
        // 3. Add index
        MedX360_Database::add_index('bookings', 'new_index', 'new_field');
        
        error_log('MedX360 Migration: [Description] completed');
    }
    
    /**
     * Rollback the migration
     */
    public function down() {
        global $wpdb;
        
        // Your rollback code here
        // Examples:
        
        // 1. Drop table
        $table_name = $wpdb->prefix . 'medx360_new_table';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        // 2. Remove column
        MedX360_Database::remove_column('bookings', 'new_field');
        
        // 3. Remove index
        MedX360_Database::remove_index('bookings', 'new_index');
        
        error_log('MedX360 Migration: [Description] rolled back');
    }
}
```

## Database Utility Methods

The `MedX360_Database` class provides safe methods for schema changes:

### Adding Columns
```php
// Add a new column safely
MedX360_Database::add_column('bookings', 'patient_notes', 'text');

// Add column with constraints
MedX360_Database::add_column('bookings', 'priority', 'enum("low","medium","high") DEFAULT "medium"');
```

### Removing Columns
```php
// Remove a column safely
MedX360_Database::remove_column('bookings', 'old_field');
```

### Adding Indexes
```php
// Add single column index
MedX360_Database::add_index('bookings', 'patient_email_idx', 'patient_email');

// Add multi-column index
MedX360_Database::add_index('bookings', 'date_status_idx', 'appointment_date, status');
```

### Removing Indexes
```php
// Remove an index safely
MedX360_Database::remove_index('bookings', 'patient_email_idx');
```

### Checking Existence
```php
// Check if table exists
if (MedX360_Database::table_exists('new_table')) {
    // Table exists
}

// Check if column exists
if (MedX360_Database::column_exists('bookings', 'new_field')) {
    // Column exists
}
```

## CLI Tool Usage

### Check Migration Status
```bash
php migration-cli.php status
```
Output:
```
=== MedX360 Migration Status ===
Current Version: 1.0.0
Target Version: 1.4.0
Needs Update: Yes

Pending Migrations:
- 1.1.0: 002_add_patient_table.php
- 1.2.0: 003_add_notifications_table.php
- 1.3.0: 004_add_appointment_reminders.php
- 1.4.0: 005_add_multi_language_support.php
```

### Run Migrations
```bash
php migration-cli.php run
```

### Create New Migration
```bash
php migration-cli.php create add_payment_gateways 1.5.0
```

### Rollback Migration (Development Only)
```bash
php migration-cli.php rollback 1.4.0
```

## Migration Examples

### Example 1: Adding a New Table
```php
public function up() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'medx360_payment_methods';
    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        type enum('credit_card','bank_transfer','cash','other') NOT NULL,
        is_active tinyint(1) DEFAULT 1,
        settings longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY type (type),
        KEY is_active (is_active)
    ) " . $wpdb->get_charset_collate() . ";";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
```

### Example 2: Adding Columns to Existing Table
```php
public function up() {
    // Add multiple columns to bookings table
    MedX360_Database::add_column('bookings', 'payment_method_id', 'int(11)');
    MedX360_Database::add_column('bookings', 'discount_amount', 'decimal(10,2) DEFAULT 0.00');
    MedX360_Database::add_column('bookings', 'notes', 'text');
    
    // Add index for performance
    MedX360_Database::add_index('bookings', 'payment_method_idx', 'payment_method_id');
}
```

### Example 3: Complex Schema Change
```php
public function up() {
    global $wpdb;
    
    // Create new table
    $table_name = $wpdb->prefix . 'medx360_appointment_feedback';
    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        booking_id int(11) NOT NULL,
        rating int(1) NOT NULL,
        feedback text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY booking_id (booking_id),
        KEY rating (rating)
    ) " . $wpdb->get_charset_collate() . ";";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add column to existing table
    MedX360_Database::add_column('bookings', 'feedback_requested', 'tinyint(1) DEFAULT 0');
    
    // Insert default data
    $wpdb->insert(
        $wpdb->prefix . 'medx360_settings',
        array(
            'setting_name' => 'feedback_enabled',
            'setting_value' => '1',
            'created_at' => current_time('mysql')
        )
    );
}
```

## Best Practices

### 1. **Always Test Migrations**
- Test migrations on a development environment first
- Use the rollback functionality to test both directions

### 2. **Use Safe Methods**
- Always use `MedX360_Database::add_column()` instead of raw SQL
- Check for existence before making changes

### 3. **Version Management**
- Increment version numbers logically (1.0.0 → 1.1.0 → 1.2.0)
- Don't skip versions

### 4. **Error Handling**
- Wrap migration code in try-catch blocks
- Log errors for debugging

### 5. **Data Migration**
- When changing column types, consider data conversion
- Provide default values for new required columns

## Troubleshooting

### Migration Not Running
1. Check if migration file exists in `includes/migrations/`
2. Verify class name matches file name
3. Check WordPress error logs for PHP errors

### Rollback Issues
1. Ensure rollback code is properly implemented
2. Test rollbacks in development environment
3. Backup database before running migrations in production

### Performance Issues
1. Add indexes for frequently queried columns
2. Consider data migration in batches for large tables
3. Use `LIMIT` clauses for large data operations

## Integration with Plugin Updates

When you release a new plugin version:

1. **Update Version Number**: Change `MEDX360_VERSION` in `medx360.php`
2. **Create Migration**: Add migration file for schema changes
3. **Test Thoroughly**: Ensure migrations work correctly
4. **Release**: Users will automatically get migrations on activation

The migration system ensures that your medical booking data is always preserved while allowing you to evolve the database schema as your plugin grows! 🏥✨
