# DATABASE BACKUP FILES FOR LINUX DEPLOYMENT

## 📋 Database Backup Files

### 🗄️ Database Structure:
1. **perdagangan_system** - Main application database (read/write)
2. **alamat_db** - Address reference database (read-only)

### 📁 Backup Files:
- **perdagangan_system_backup.sql** - Main application database backup
- **alamat_db_backup.sql** - Address reference database backup

### 🔧 Database Configuration:

#### perdagangan_system (Main Database):
- **Tables**: 15 tables + 1 view
- **Purpose**: Main application data
- **Access**: Read/Write
- **Key Tables**:
  - companies (dengan address foreign keys)
  - branches (dengan address foreign keys)
  - branch_locations (dengan address foreign keys)
  - products, transactions, inventory, etc.
  - v_branch_summary (view dengan alamat_db integration)

#### alamat_db (Address Database):
- **Tables**: 7 tables
- **Purpose**: Address reference data (Indonesia)
- **Access**: Read-Only
- **Key Tables**:
  - provinces (id, code, name)
  - regencies (id, province_id, code, name)
  - districts (id, regency_id, code, name)
  - villages (id, district_id, code, name)

### 🚀 Linux Deployment Instructions:

#### 1. Copy Files to Linux Server:
```bash
# Copy backup files to Linux server
scp perdagangan_system_backup.sql user@linux-server:/tmp/
scp alamat_db_backup.sql user@linux-server:/tmp/
```

#### 2. Create Databases in Linux:
```bash
# Login to MySQL/MariaDB
mysql -u root -p

# Create databases
CREATE DATABASE IF NOT EXISTS perdagangan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS alamat_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit MySQL
EXIT;
```

#### 3. Import Databases:
```bash
# Import alamat_db (address database - import first)
mysql -u root -p alamat_db < /tmp/alamat_db_backup.sql

# Import perdagangan_system (main database)
mysql -u root -p perdagangan_system < /tmp/perdagangan_system_backup.sql
```

#### 4. Verify Import:
```bash
# Check alamat_db tables
mysql -u root -p -e "USE alamat_db; SHOW TABLES;"

# Check perdagangan_system tables
mysql -u root -p -e "USE perdagangan_system; SHOW TABLES;"

# Check view functionality
mysql -u root -p -e "USE perdagangan_system; SELECT * FROM v_branch_summary LIMIT 5;"
```

#### 5. Update Application Configuration:
```php
// Update app/config/database.php for Linux
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_linux_password');
define('DB_NAME', 'perdagangan_system');
define('DB_CHARSET', 'utf8mb4');

// Update app/config/database_multi.php for Linux
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_linux_password');
define('DB_NAME_MAIN', 'perdagangan_system');
define('DB_NAME_ADDRESS', 'alamat_db');
```

### 🔍 Database Relationships:

#### Foreign Key Structure:
- **perdagangan_system** companies.province_id → **alamat_db** provinces.id
- **perdagangan_system** companies.regency_id → **alamat_db** regencies.id
- **perdagangan_system** companies.district_id → **alamat_db** districts.id
- **perdagangan_system** companies.village_id → **alamat_db** villages.id
- **perdagangan_system** branches.province_id → **alamat_db** provinces.id
- **perdagangan_system** branches.regency_id → **alamat_db** regencies.id
- **perdagangan_system** branches.district_id → **alamat_db** districts.id
- **perdagangan_system** branches.village_id → **alamat_db** villages.id

#### Cross-Database View:
```sql
CREATE OR REPLACE VIEW v_branch_summary AS
SELECT 
    b.id_branch, b.branch_name, b.branch_code, b.branch_type, c.company_name, bl.address,
    p.name as province_name, r.name as regency_name, d.name as district_name, v.name as village_name,
    COUNT(DISTINCT m.id_member) as total_members,
    COUNT(DISTINCT bi.id_inventory) as total_products,
    SUM(bi.stock_quantity) as total_stock,
    COUNT(DISTINCT t.id_transaction) as total_transactions,
    COALESCE(SUM(t.final_amount), 0) as total_revenue, b.is_active
FROM branches b
LEFT JOIN companies c ON b.company_id = c.id_company
LEFT JOIN branch_locations bl ON b.id_branch = bl.branch_id
LEFT JOIN alamat_db.provinces p ON bl.province_id = p.id
LEFT JOIN alamat_db.regencies r ON bl.regency_id = r.id
LEFT JOIN alamat_db.districts d ON bl.district_id = d.id
LEFT JOIN alamat_db.villages v ON bl.village_id = v.id
LEFT JOIN members m ON b.id_branch = m.branch_id AND m.is_active = 1
LEFT JOIN branch_inventory bi ON b.id_branch = bi.branch_id
LEFT JOIN transactions t ON b.id_branch = t.branch_id AND t.status = 'completed'
GROUP BY b.id_branch, b.branch_name, b.branch_code, b.branch_type, c.company_name, bl.address, p.name, r.name, d.name, v.name, b.is_active;
```

### 🔒 Security Notes:

#### Database Access:
- **alamat_db** should be read-only for application users
- **perdagangan_system** should have read/write access
- Create separate database users for security:
```sql
-- Create user for main application
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON perdagangan_system.* TO 'app_user'@'localhost';

-- Create read-only user for address database
CREATE USER 'address_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT ON alamat_db.* TO 'address_user'@'localhost';
```

### 📊 File Sizes:
- **perdagangan_system_backup.sql**: ~59KB (small, mainly structure)
- **alamat_db_backup.sql**: ~12MB (contains all Indonesia address data)

### 🎯 Next Steps for Linux Development:

1. **Copy Files**: Transfer backup files to Linux server
2. **Import Databases**: Create and import both databases
3. **Update Configuration**: Modify database connection settings
4. **Test Functionality**: Verify multi-database operations
5. **Deploy Application**: Copy application files to Linux
6. **Test Integration**: Ensure all features work correctly

### 🔧 Linux Environment Setup:

#### Required PHP Extensions:
```bash
# Install required PHP extensions for Linux
sudo apt-get install php-mysql php-pdo php-mbstring php-json
```

#### File Permissions:
```bash
# Set proper permissions for Linux
sudo chown -R www-data:www-data /var/www/html/dagang
sudo chmod -R 755 /var/www/html/dagang
sudo chmod -R 644 /var/www/html/dagang/app/config/
```

### 📞 Troubleshooting:

#### Common Issues:
1. **Database Connection**: Check MySQL service status
2. **Permissions**: Verify database user permissions
3. **Cross-Database Queries**: Ensure MySQL user has access to both databases
4. **File Paths**: Update paths for Linux environment
5. **Character Encoding**: Ensure utf8mb4 support

#### Verification Commands:
```bash
# Test database connections
mysql -u root -p -e "SHOW DATABASES;"
mysql -u root -p -e "USE perdagangan_system; SHOW TABLES;"
mysql -u root -p -e "USE alamat_db; SHOW TABLES;"

# Test cross-database query
mysql -u root -p -e "SELECT COUNT(*) FROM alamat_db.provinces;"
```

### 🎯 Status:
✅ **Database backups created successfully**
✅ **Files ready for Linux deployment**
✅ **Multi-database configuration documented**
✅ **Deployment instructions provided**
✅ **Security considerations documented**

**Next Step**: Copy backup files to Linux server and follow deployment instructions.
