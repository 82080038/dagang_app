# LINUX DEPLOYMENT GUIDE

## 🚀 Complete Linux Deployment Package

### 📁 Files Created:
1. **perdagangan_system_backup.sql** - Main application database backup
2. **alamat_db_backup.sql** - Address reference database backup
3. **setup_linux_database.sh** - Automated database setup script
4. **database_config_linux.php** - Database configuration template
5. **DATABASE_BACKUP_LINUX_DEPLOYMENT.md** - Complete deployment documentation

### 🎯 Quick Start for Linux Deployment:

#### Step 1: Copy Files to Linux Server
```bash
# Copy all files to Linux server
scp -r database_exports/ user@linux-server:/tmp/
scp DATABASE_BACKUP_LINUX_DEPLOYMENT.md user@linux-server:/tmp/
```

#### Step 2: Run Automated Setup
```bash
# Make setup script executable
chmod +x /tmp/setup_linux_database.sh

# Run the setup script
cd /tmp
sudo ./setup_linux_database.sh
```

#### Step 3: Deploy Application
```bash
# Copy application files to web directory
sudo cp -r /path/to/dagang/* /var/www/html/

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chmod -R 644 /var/www/html/app/config/
```

#### Step 4: Update Configuration
```bash
# Copy database configuration
sudo cp /tmp/database_config_linux.php /var/www/html/app/config/database.php

# Update other configuration files as needed
sudo nano /var/www/html/app/config/app.php
```

### 🔧 Manual Setup (if automated script fails):

#### 1. Create Databases
```bash
mysql -u root -p
CREATE DATABASE IF NOT EXISTS perdagangan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS alamat_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### 2. Import Data
```bash
# Import address database first
mysql -u root -p alamat_db < /tmp/alamat_db_backup.sql

# Import main database
mysql -u root -p perdagangan_system < /tmp/perdagangan_system_backup.sql
```

#### 3. Create Application User
```bash
mysql -u root -p
CREATE USER 'dagang_app'@'localhost' IDENTIFIED BY 'DagangApp2024!';
GRANT SELECT, INSERT, UPDATE, DELETE ON perdagangan_system.* TO 'dagang_app'@'localhost';
GRANT SELECT ON alamat_db.* TO 'dagang_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 🔍 Verification Commands:

#### Test Database Connections
```bash
# Test main database
mysql -u dagang_app -p'DagangApp2024!' -e "USE perdagangan_system; SHOW TABLES;"

# Test address database
mysql -u dagang_app -p'DagangApp2024!' -e "USE alamat_db; SHOW TABLES;"

# Test cross-database view
mysql -u dagang_app -p'DagangApp2024!' -e "USE perdagangan_system; SELECT * FROM v_branch_summary LIMIT 5;"
```

#### Test PHP Configuration
```bash
# Test database configuration
php /var/www/html/app/config/database.php
```

### 🌐 Web Server Configuration:

#### Apache2 Setup
```bash
# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod php8.1

# Create virtual host
sudo nano /etc/apache2/sites-available/dagang.conf
```

Virtual Host Configuration:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/dagang_error.log
    CustomLog ${APACHE_LOG_DIR}/dagang_access.log combined
</VirtualHost>
```

```bash
# Enable site and restart Apache
sudo a2ensite dagang.conf
sudo systemctl reload apache2
```

#### Nginx Setup
```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/dagang
```

Nginx Configuration:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

```bash
# Enable site and restart Nginx
sudo ln -s /etc/nginx/sites-available/dagang /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 📋 PHP Requirements:

#### Install PHP Extensions
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install php8.1 php8.1-mysql php8.1-pdo php8.1-mbstring php8.1-json php8.1-xml php8.1-curl

# CentOS/RHEL
sudo yum install php php-mysql php-pdo php-mbstring php-json php-xml php-curl
```

#### PHP Configuration
```bash
# Edit php.ini
sudo nano /etc/php/8.1/apache2/php.ini
```

Key Settings:
```ini
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

### 🔒 Security Considerations:

#### File Permissions
```bash
# Secure sensitive files
sudo chmod 600 /var/www/html/app/config/database.php
sudo chmod 600 /var/www/html/app/config/app.php

# Set proper ownership
sudo chown -R www-data:www-data /var/www/html
sudo chown -R root:root /var/www/html/app/config
```

#### Database Security
```bash
# Remove test databases
mysql -u root -p -e "DROP DATABASE IF EXISTS test;"

# Remove anonymous users
mysql -u root -p -e "DELETE FROM mysql.user WHERE User='';"

# Set root password (if not set)
mysql -u root -p -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('strong_password');"
```

### 📊 Application Testing:

#### Test Database Operations
```bash
# Test address functions
php -r "
require_once '/var/www/html/app/config/database.php';
echo 'Provinces: ' . count(getProvinces()) . PHP_EOL;
echo 'Regencies for Jakarta: ' . count(getRegencies(12)) . PHP_EOL;
echo 'Full Address: ' . getFullAddress(12, 158, 1959, 25526) . PHP_EOL;
"
```

#### Test Web Application
```bash
# Test basic functionality
curl -I http://localhost/
curl -I http://localhost/index.php
```

### 🚨 Troubleshooting:

#### Common Issues and Solutions:

1. **Database Connection Failed**
   ```bash
   # Check MySQL status
   sudo systemctl status mysql
   
   # Check MySQL logs
   sudo tail -f /var/log/mysql/error.log
   ```

2. **Permission Denied**
   ```bash
   # Check file permissions
   ls -la /var/www/html/
   
   # Fix permissions
   sudo chown -R www-data:www-data /var/www/html/
   ```

3. **PHP Errors**
   ```bash
   # Check PHP error log
   sudo tail -f /var/log/php_errors.log
   
   # Check Apache error log
   sudo tail -f /var/log/apache2/error.log
   ```

4. **Cross-Database View Not Working**
   ```bash
   # Test manual query
   mysql -u dagang_app -p'DagangApp2024!' -e "USE perdagangan_system; SELECT COUNT(*) FROM v_branch_summary;"
   ```

### 📈 Performance Optimization:

#### MySQL Optimization
```bash
# Edit MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add to configuration:
```ini
[mysqld]
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 32M
query_cache_type = 1
```

#### PHP Optimization
```bash
# Enable OPcache
sudo phpenmod opcache
```

### 🎯 Final Verification:

#### Complete System Test
```bash
# Test all components
echo "Testing Database Connections..."
mysql -u dagang_app -p'DagangApp2024!' -e "SELECT 'Main DB OK' as status;" 2>/dev/null && echo "✓ Main Database OK"
mysql -u dagang_app -p'DagangApp2024!' -e "SELECT 'Address DB OK' as status;" 2>/dev/null && echo "✓ Address Database OK"

echo "Testing Web Server..."
curl -s http://localhost/ > /dev/null && echo "✓ Web Server OK"

echo "Testing PHP..."
php -r "echo 'PHP OK';" > /dev/null && echo "✓ PHP OK"

echo "Testing Application..."
curl -s http://localhost/index.php | grep -q "dagang" && echo "✓ Application OK"
```

### 📞 Support Information:

#### Log Locations:
- **Apache Error Log**: `/var/log/apache2/error.log`
- **MySQL Error Log**: `/var/log/mysql/error.log`
- **PHP Error Log**: `/var/log/php_errors.log`
- **Application Log**: `/var/www/html/logs/`

#### Configuration Files:
- **Database Config**: `/var/www/html/app/config/database.php`
- **Application Config**: `/var/www/html/app/config/app.php`
- **Apache Config**: `/etc/apache2/sites-available/dagang.conf`
- **Nginx Config**: `/etc/nginx/sites-available/dagang`

### 🎉 Deployment Complete!

Once all steps are completed, your Dagang application should be running on Linux with:
- ✅ Multi-database configuration
- ✅ Cross-platform compatibility
- ✅ Security optimizations
- ✅ Performance tuning
- ✅ Proper logging and monitoring

Access your application at: `http://your-domain.com` or `http://your-server-ip`
