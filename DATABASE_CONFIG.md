# DATABASE CONFIGURATION - MULTI-DATABASE SETUP

## 📋 Database Structure

### 🗄️ Databases:
1. **perdagangan_system** - Main application database (read/write)
2. **alamat_db** - Address reference database (read-only)

### 🔗 Relationship:
- **perdagangan_system** stores only foreign keys (id) from **alamat_db**
- **alamat_db** is reference-only, cannot be modified
- All address data should be fetched from **alamat_db**

## 📊 Database Tables

### 🏢 Main Database (perdagangan_system) - Read/Write
- **companies** - Company data
- **branches** - Branch data
- **products** - Product data
- **categories** - Product categories
- **suppliers** - Supplier data
- **customers** - Customer data
- **transactions** - Transaction data
- **transaction_details** - Transaction details
- **users** - User accounts
- **modules** - Module management
- **company_settings** - Company settings

### 📍 Address Database (alamat_db) - Read Only
- **provinces** - Province data
- **regencies** - Regency data
- **districts** - District data
- **villages** - Village data

## 🔧 Configuration

### Database Access Rules:
- **DB_MAIN_READ_WRITE** = true (Can read and write)
- **DB_ADDRESS_READ_ONLY** = true (Read-only access)

### Connection Details:
- **Host**: localhost
- **User**: root
- **Password**: (empty)
- **Charset**: utf8mb4

## 🔒 Security Features

### Address Database Protection:
- **Read-only access** - No modification allowed
- **Operation blocking** - INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, TRUNCATE
- **Validation functions** - Address ID validation
- **Relationship validation** - Hierarchical address validation

## 📝 Helper Functions

### Address Functions:
- `getProvinces()` - Get all provinces
- `getRegencies($province_id)` - Get regencies by province
- `getDistricts($regency_id)` - Get districts by regency
- `getVillages($district_id)` - Get villages by district
- `getAddressName($table, $id)` - Get address name by ID
- `getFullAddress($province_id, $regency_id, $district_id, $village_id)` - Get full address string

### Validation Functions:
- `validateAddressId($table, $id)` - Validate address ID exists
- `validateAddressRelationships($data)` - Validate address relationships

## 🚀 Implementation Notes

### Important Rules:
1. **Address data** must be fetched from **alamat_db** only
2. **Main database** stores only foreign keys (province_id, regency_id, district_id, village_id)
3. **Address database** cannot be modified through the application
4. **All address operations** must use helper functions
5. **Validation** must check address relationships before saving

### Usage Examples:
```php
// Get provinces
$provinces = getProvinces();

// Get regencies by province
$regencies = getRegencies($province_id);

// Validate address data
$errors = validateAddressRelationships($data);
if (!empty($errors)) {
    // Handle validation errors
}

// Get full address
$full_address = getFullAddress($province_id, $regency_id, $district_id, $village_id);
```

## 📋 Database Status

### Current Status:
✅ **perdagangan_system** - Exists and accessible
✅ **alamat_db** - Exists and accessible
✅ **Multi-database configuration** - Implemented
✅ **Security protection** - Read-only for address database
✅ **Helper functions** - Available for address operations

### Connection Testing:
```php
// Test main database connection
$main_db = getMainDB();
echo "Main database connected successfully";

// Test address database connection
$address_db = getAddressDB();
echo "Address database connected successfully";
```

## 🔧 Configuration File

The multi-database configuration is stored in:
```
app/config/database_multi.php
```

This file contains:
- Database connection settings
- Helper functions for address operations
- Validation functions
- Security protection for address database
- Logging configuration

## 📊 Performance Considerations

### Optimization:
- **Connection pooling** - Separate connections for each database
- **Caching** - Cache frequently accessed address data
- **Lazy loading** - Load address data only when needed
- **Validation caching** - Cache validation results

### Best Practices:
- Use helper functions for all address operations
- Validate address relationships before saving
- Handle connection errors gracefully
- Log all database operations for debugging

## 🚨 Important Notes

### Do NOT:
- Modify address database directly
- Store address names in main database
- Bypass security protections
- Ignore validation functions

### DO:
- Use provided helper functions
- Validate all address relationships
- Log database operations
- Handle connection errors properly

## 📞 Support

For any database-related issues:
1. Check database connections
2. Verify table structures
3. Validate address relationships
4. Check error logs
5. Test helper functions
