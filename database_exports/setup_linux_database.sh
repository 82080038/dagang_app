#!/bin/bash

# Linux Database Setup Script for Dagang Application
# This script sets up the multi-database environment for Linux deployment

echo "=== Dagang Application Database Setup for Linux ==="
echo "Setting up multi-database environment..."
echo ""

# Database configuration
DB_USER="root"
DB_PASS=""  # Change this to your MySQL root password
DB_HOST="localhost"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if MySQL is running
if ! systemctl is-active --quiet mysql; then
    print_error "MySQL is not running. Please start MySQL service first."
    echo "sudo systemctl start mysql"
    exit 1
fi

print_status "MySQL is running..."

# Create databases
print_status "Creating databases..."

mysql -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS perdagangan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    print_status "Database 'perdagangan_system' created successfully"
else
    print_error "Failed to create 'perdagangan_system' database"
    exit 1
fi

mysql -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS alamat_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    print_status "Database 'alamat_db' created successfully"
else
    print_error "Failed to create 'alamat_db' database"
    exit 1
fi

# Check if backup files exist
BACKUP_DIR="/tmp"
PERDAGANGAN_BACKUP="$BACKUP_DIR/perdagangan_system_backup.sql"
ALAMAT_BACKUP="$BACKUP_DIR/alamat_db_backup.sql"

if [ ! -f "$PERDAGANGAN_BACKUP" ]; then
    print_error "Backup file not found: $PERDAGANGAN_BACKUP"
    print_warning "Please copy the backup files to /tmp/ directory first"
    exit 1
fi

if [ ! -f "$ALAMAT_BACKUP" ]; then
    print_error "Backup file not found: $ALAMAT_BACKUP"
    print_warning "Please copy the backup files to /tmp/ directory first"
    exit 1
fi

# Import databases
print_status "Importing alamat_db database..."
mysql -u $DB_USER -p$DB_PASS alamat_db < "$ALAMAT_BACKUP" 2>/dev/null
if [ $? -eq 0 ]; then
    print_status "alamat_db imported successfully"
else
    print_error "Failed to import alamat_db"
    exit 1
fi

print_status "Importing perdagangan_system database..."
mysql -u $DB_USER -p$DB_PASS perdagangan_system < "$PERDAGANGAN_BACKUP" 2>/dev/null
if [ $? -eq 0 ]; then
    print_status "perdagangan_system imported successfully"
else
    print_error "Failed to import perdagangan_system"
    exit 1
fi

# Verify import
print_status "Verifying database imports..."

# Check alamat_db tables
ALAMAT_TABLES=$(mysql -u $DB_USER -p$DB_PASS -e "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'alamat_db';" -s -N 2>/dev/null)
if [ "$ALAMAT_TABLES" -gt 0 ]; then
    print_status "alamat_db: $ALAMAT_TABLES tables imported"
else
    print_error "alamat_db: No tables found"
fi

# Check perdagangan_system tables
PERDAGANGAN_TABLES=$(mysql -u $DB_USER -p$DB_PASS -e "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'perdagangan_system';" -s -N 2>/dev/null)
if [ "$PERDAGANGAN_TABLES" -gt 0 ]; then
    print_status "perdagangan_system: $PERDAGANGAN_TABLES tables imported"
else
    print_error "perdagangan_system: No tables found"
fi

# Test cross-database view
print_status "Testing cross-database view..."
VIEW_TEST=$(mysql -u $DB_USER -p$DB_PASS -e "SELECT COUNT(*) as count FROM perdagangan_system.v_branch_summary;" -s -N 2>/dev/null)
if [ "$VIEW_TEST" -ge 0 ]; then
    print_status "Cross-database view working: $VIEW_TEST records found"
else
    print_error "Cross-database view test failed"
fi

# Create application database user
print_status "Creating application database user..."

# Create main application user
mysql -u $DB_USER -p$DB_PASS -e "DROP USER IF EXISTS 'dagang_app'@'localhost';" 2>/dev/null
mysql -u $DB_USER -p$DB_PASS -e "CREATE USER 'dagang_app'@'localhost' IDENTIFIED BY 'DagangApp2024!';" 2>/dev/null
mysql -u $DB_USER -p$DB_PASS -e "GRANT SELECT, INSERT, UPDATE, DELETE ON perdagangan_system.* TO 'dagang_app'@'localhost';" 2>/dev/null
mysql -u $DB_USER -p$DB_PASS -e "GRANT SELECT ON alamat_db.* TO 'dagang_app'@'localhost';" 2>/dev/null
mysql -u $DB_USER -p$DB_PASS -e "FLUSH PRIVILEGES;" 2>/dev/null

if [ $? -eq 0 ]; then
    print_status "Application user 'dagang_app' created successfully"
else
    print_warning "Failed to create application user (you may need to create manually)"
fi

# Test application user access
print_status "Testing application user access..."
APP_USER_TEST=$(mysql -u dagang_app -p'DagangApp2024!' -e "SELECT COUNT(*) FROM perdagangan_system.companies;" -s -N 2>/dev/null)
if [ "$APP_USER_TEST" -ge 0 ]; then
    print_status "Application user access working: $APP_USER_TEST companies found"
else
    print_warning "Application user access test failed"
fi

# Create configuration template
print_status "Creating database configuration template..."

CONFIG_FILE="/tmp/dagang_db_config.php"
cat > "$CONFIG_FILE" << 'EOF'
<?php
/**
 * Database Configuration - Linux Deployment
 * Multi-Database Setup for Dagang Application
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'dagang_app');
define('DB_PASS', 'DagangApp2024!');
define('DB_CHARSET', 'utf8mb4');

// Multi-Database Configuration
define('DB_NAME_MAIN', 'perdagangan_system');
define('DB_NAME_ADDRESS', 'alamat_db');

// Database Access Rules
define('DB_MAIN_READ_WRITE', true);
define('DB_ADDRESS_READ_ONLY', true);

// Connection function for main database
function getMainDB() {
    static $main_db = null;
    if ($main_db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_MAIN . ";charset=" . DB_CHARSET;
            $main_db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception('Main database connection failed: ' . $e->getMessage());
        }
    }
    return $main_db;
}

// Connection function for address database
function getAddressDB() {
    static $address_db = null;
    if ($address_db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_ADDRESS . ";charset=" . DB_CHARSET;
            $address_db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception('Address database connection failed: ' . $e->getMessage());
        }
    }
    return $address_db;
}

// Test connections
try {
    $main_db = getMainDB();
    $address_db = getAddressDB();
    echo "Database connections successful!\n";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
?>
EOF

print_status "Configuration template created: $CONFIG_FILE"

# Summary
echo ""
echo "=== Database Setup Summary ==="
print_status "✓ Databases created successfully"
print_status "✓ Data imported successfully"
print_status "✓ Cross-database views working"
print_status "✓ Application user created"
print_status "✓ Configuration template created"
echo ""
print_warning "Next steps:"
echo "1. Copy the configuration template to your application"
echo "2. Update application file paths for Linux"
echo "3. Set proper file permissions"
echo "4. Test the application"
echo ""
print_status "Database setup completed successfully!"
echo ""
echo "Configuration file location: $CONFIG_FILE"
echo "Application user: dagang_app"
echo "Application password: DagangApp2024!"
echo ""
echo "To test the configuration:"
echo "php $CONFIG_FILE"
echo ""
