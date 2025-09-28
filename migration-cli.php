<?php
/**
 * MedX360 Migration CLI Tool
 * Usage: php migration-cli.php [command] [options]
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('You must be logged in as an administrator to run this tool.');
}

// Load migration class
require_once('includes/class-migration.php');

class MedX360_Migration_CLI {
    
    private $migration;
    
    public function __construct() {
        $this->migration = MedX360_Migration::get_instance();
    }
    
    public function run($args) {
        $command = $args[1] ?? 'help';
        
        switch ($command) {
            case 'status':
                $this->show_status();
                break;
            case 'run':
                $this->run_migrations();
                break;
            case 'create':
                $name = $args[2] ?? null;
                $version = $args[3] ?? '1.0.0';
                $this->create_migration($name, $version);
                break;
            case 'rollback':
                $version = $args[2] ?? null;
                $this->rollback_migration($version);
                break;
            case 'help':
            default:
                $this->show_help();
                break;
        }
    }
    
    private function show_status() {
        echo "=== MedX360 Migration Status ===\n";
        
        $status = $this->migration->get_migration_status();
        
        echo "Current Version: " . $status['current_version'] . "\n";
        echo "Target Version: " . $status['target_version'] . "\n";
        echo "Needs Update: " . ($status['needs_update'] ? 'Yes' : 'No') . "\n";
        
        if (!empty($status['pending_migrations'])) {
            echo "\nPending Migrations:\n";
            foreach ($status['pending_migrations'] as $migration) {
                echo "- " . $migration['version'] . ": " . $migration['file'] . "\n";
            }
        } else {
            echo "\nNo pending migrations.\n";
        }
    }
    
    private function run_migrations() {
        echo "=== Running MedX360 Migrations ===\n";
        
        $status = $this->migration->get_migration_status();
        
        if (!$status['needs_update']) {
            echo "No migrations needed.\n";
            return;
        }
        
        echo "Running " . count($status['pending_migrations']) . " migration(s)...\n";
        
        $this->migration->run_migrations();
        
        echo "Migrations completed.\n";
    }
    
    private function create_migration($name, $version) {
        if (!$name) {
            echo "Error: Migration name is required.\n";
            echo "Usage: php migration-cli.php create [name] [version]\n";
            return;
        }
        
        echo "=== Creating Migration ===\n";
        echo "Name: $name\n";
        echo "Version: $version\n";
        
        $filepath = $this->migration->create_migration($name, $version);
        
        echo "Migration file created: $filepath\n";
        echo "Edit the file to add your migration logic.\n";
    }
    
    private function rollback_migration($version) {
        if (!$version) {
            echo "Error: Version is required for rollback.\n";
            echo "Usage: php migration-cli.php rollback [version]\n";
            return;
        }
        
        echo "=== Rolling Back Migration ===\n";
        echo "Version: $version\n";
        
        $result = $this->migration->rollback_migration($version);
        
        if ($result) {
            echo "Migration rolled back successfully.\n";
        } else {
            echo "Failed to rollback migration.\n";
        }
    }
    
    private function show_help() {
        echo "=== MedX360 Migration CLI Tool ===\n\n";
        echo "Available commands:\n";
        echo "  status                    - Show migration status\n";
        echo "  run                       - Run pending migrations\n";
        echo "  create [name] [version]   - Create new migration file\n";
        echo "  rollback [version]        - Rollback a migration\n";
        echo "  help                      - Show this help\n\n";
        echo "Examples:\n";
        echo "  php migration-cli.php status\n";
        echo "  php migration-cli.php run\n";
        echo "  php migration-cli.php create add_new_feature 1.5.0\n";
        echo "  php migration-cli.php rollback 1.4.0\n";
    }
}

// Run the CLI tool
$cli = new MedX360_Migration_CLI();
$cli->run($argv);
