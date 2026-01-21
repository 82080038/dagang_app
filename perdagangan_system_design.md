# RANCANG APLIKASI PERDAGANGAN MULTI-CABANG
# Sistem Komprehensif untuk Pengusaha/Pedagang

## 🎯 **VISI APLIKASI PERDAGANGAN**

### **Target Users:**
- **Individu/Personal**: Pengguna pribadi yang menjual barang (UKM ultra-mikro)
- **Pedagang Desa**: Pedagang di desa terpencil dengan keterbatasan infrastruktur
- **Warung/Kios**: Usaha kecil dengan operasi sederhana
- **Toko Kelontong**: Toko sembako skala kecil-menengah
- **Minimarket**: Retail modern dengan multiple produk
- **Pengusaha Menengah**: Bisnis dengan beberapa karyawan
- **Distributor/Agen**: Pengedar grosir dengan network luas
- **Koperasi**: Organisasi bisnis kolektif
- **Perusahaan Besar**: Corporate dengan multiple cabang/branch
- **Franchise**: Bisnis waralaba dengan standardisasi tinggi

## 📊 **KEBUTUHAN BISNIS PERDAGANGAN**

### **1. Management Multi-Cabang**
- **Pusat**: Kantor pusat dengan dashboard lengkap
- **Cabang**: Toko/gerai di lokasi berbeda
- **Anggota**: Karyawan per cabang
- **Operasional**: Jam buka/tutup per cabang

### **2. Manajemen Produk**
- **Inventaris**: Stok per cabang
- **Transfer Barang**: Antar cabang
- **Harga**: Harga jual per cabang (bisa berbeda)
- **Kategori**: Produk per cabang

### **3. Transaksi Harian**
- **Penjualan**: POS (Point of Sale)
- **Pembelian**: Dari supplier/distributor
- **Retur**: Barang retur pelanggan
- **Diskon**: Program promosi per cabang

### **4. Keuangan & Akuntansi**
- **Kas**: Kas per cabang
- **Bank**: Rekening per cabang
- **Laporan**: Laba rugi per cabang
- **Pajak**: PPN, PPh, dll

### **5. Manajemen Lokasi**
- **Alamat**: Lokasi toko per cabang
- **Provinsi**: Data lokasi lengkap
- **Operasional**: Jam buka/tutup
- **Status**: Aktif/non-aktif per cabang

### **6. Customer Relationship Management (CRM)** 🆕
- **Database Pelanggan**: Informasi lengkap pelanggan
- **Riwayat Pembelian**: Track pembelian pelanggan
- **Program Loyalty**: Poin, reward, tier pelanggan
- **Segmentasi Pelanggan**: Kelompok pelanggan berdasarkan perilaku
- **Marketing Personal**: Promosi personal per pelanggan
- **Komunikasi**: Log komunikasi dengan pelanggan

### **7. Advanced Employee Management** 🆕
- **Penjadwalan Shift**: Sistem shift karyawan
- **Time Tracking**: Clock-in/clock-out digital
- **Performance Monitoring**: Tracking performa karyawan
- **Commission System**: Perhitungan komisi penjualan
- **Training Management**: Record pelatihan karyawan
- **Multi-Level Permissions**: Hak akses berdasarkan role

### **8. Supplier Relationship Management** 🆕
- **Database Supplier**: Informasi lengkap supplier
- **Purchase Order**: PO generation dan tracking
- **Performance Tracking**: Rating performa supplier
- **Auto-Reorder**: Order otomatis berdasarkan stok
- **Payment Terms**: Management terms pembayaran
- **Quality Control**: Tracking kualitas produk

### **9. Advanced Inventory Management** 🆕
- **Batch/Lot Tracking**: Track per batch produksi
- **Expiry Management**: Monitor tanggal kadaluarsa
- **Serial Number**: Track serial number produk
- **FIFO/LIFO**: Multiple costing methods
- **Stock Aging**: Analisis umur stok
- **Dead Stock Analysis**: Identifikasi stok mati

### **10. Marketing & Promotions** 🆕
- **Campaign Management**: Management promosi
- **Coupon System**: Generate dan manage coupon
- **Bundle Deals**: Paket produk diskon
- **Flash Sales**: Promosi terbatas waktu
- **BOGO Offers**: Buy 1 Get 1 deals
- **Targeted Marketing**: Marketing berbasis segment

### **11. E-commerce Integration** 🆕
- **Online Store Sync**: Integrasi toko online
- **Omnichannel Inventory**: Stok terintegrasi online/offline
- **Click & Collect**: Pesan online, ambil di toko
- **Delivery Management**: Management pengiriman
- **Payment Gateway**: Integrasi pembayaran online
- **Mobile App Support**: Aplikasi mobile pelanggan

### **12. Advanced Security & Compliance** 🆕
- **Role-Based Access**: Kontrol akses detail
- **Audit Trails**: Log semua aktivitas
- **Data Encryption**: Enkripsi data sensitif
- **Backup & Recovery**: System backup otomatis
- **Security Monitoring**: Monitoring keamanan real-time
- **Compliance Reporting**: Laporan kepatuhan

## 🏗️ **STRUKTUR DATABASE DARI NOL**

### **Tabel Utama:**
```sql
-- 1. Perusahaan (Pusat)
companies (id_company, company_name, company_type, ...)

-- 2. Cabang/Toko
branches (id_branch, company_id, branch_name, branch_code, ...)

-- 3. Lokasi/Alamat
locations (id_location, branch_id, address, province_id, ...)

-- 4. Anggota/Karyawan
members (id_member, branch_id, member_name, position, ...)

-- 5. Operasional
branch_operations (id_operation, branch_id, operation_date, open_time, close_time, ...)

-- 6. Produk
products (id_product, product_name, category, unit, ...)

-- 7. Stok per Cabang
branch_inventory (id_inventory, branch_id, product_id, stock, ...)

-- 8. Transaksi
transactions (id_transaction, branch_id, transaction_type, amount, ...)

-- 9. Detail Transaksi
transaction_items (id_item, transaction_id, product_id, quantity, price, ...)

-- 10. Keuangan
cash_accounts (id_cash, branch_id, account_name, balance, ...)
bank_accounts (id_bank, branch_id, bank_name, account_number, ...)

-- 11. Akuntansi
chart_of_accounts (id_account, branch_id, account_code, account_name, ...)
journal_entries (id_journal, branch_id, journal_date, description, ...)

-- 🆕 **NEW TABLES FROM MARKET ANALYSIS:**

-- 12. Customer Relationship Management (CRM)
customers (id_customer, customer_code, customer_name, email, phone, ...)
customer_loyalty (id_loyalty, customer_id, points, tier, ...)
customer_segments (id_segment, segment_name, criteria, ...)
purchase_history (id_history, customer_id, transaction_id, ...)

-- 13. Advanced Employee Management
employee_schedules (id_schedule, employee_id, shift_date, start_time, end_time, ...)
employee_attendance (id_attendance, employee_id, date, clock_in, clock_out, ...)
employee_performance (id_performance, employee_id, period, sales_amount, ...)
employee_commissions (id_commission, employee_id, transaction_id, commission_amount, ...)

-- 14. Supplier Relationship Management
suppliers (id_supplier, supplier_code, supplier_name, contact_info, ...)
purchase_orders (id_po, supplier_id, po_number, order_date, status, ...)
supplier_performance (id_performance, supplier_id, rating, feedback, ...)
supplier_quality_control (id_qc, supplier_id, product_id, quality_score, ...)

-- 15. Advanced Inventory Management
inventory_batches (id_batch, product_id, batch_number, expiry_date, ...)
inventory_serials (id_serial, product_id, serial_number, status, ...)
stock_movements (id_movement, product_id, movement_type, quantity, ...)
dead_stock (id_dead_stock, product_id, quantity, write_off_date, ...)

-- 16. Marketing & Promotions
marketing_campaigns (id_campaign, campaign_name, start_date, end_date, ...)
coupons (id_coupon, coupon_code, discount_type, discount_value, ...)
bundle_deals (id_bundle, bundle_name, product_ids, bundle_price, ...)
flash_sales (id_flash_sale, product_id, start_time, end_time, discount, ...)

-- 17. E-commerce Integration
online_orders (id_order, order_number, customer_id, status, ...)
order_items (id_order_item, order_id, product_id, quantity, price, ...)
delivery_tracking (id_delivery, order_id, tracking_number, status, ...)
payment_transactions (id_payment, order_id, payment_method, amount, status, ...)

-- 🆕 **DETAILED BUSINESS TABLES:**

-- 19. PLU (Price Look-Up) Management
plu_codes (id_plu, product_id, plu_code, barcode, price_level, ...)
plu_prices (id_price, plu_id, branch_id, price_type, price, ...)
plu_categories (id_plu_category, category_name, description, ...)

-- 20. Payment Methods & Processing
payment_methods (id_method, method_name, method_type, is_active, ...)
payment_transactions (id_payment, transaction_id, method_id, amount, status, ...)
cash_drawer_management (id_drawer, branch_id, opening_balance, closing_balance, ...)
payment_fees (id_fee, method_id, fee_type, fee_amount, ...)

-- 21. Debt & Receivable Management
accounts_payable (id_ap, supplier_id, invoice_id, due_date, amount, status, ...)
accounts_receivable (id_ar, customer_id, invoice_id, due_date, amount, status, ...)
debt_aging (id_aging, entity_id, entity_type, days_30, days_60, days_90, ...)
credit_limits (id_limit, entity_id, entity_type, credit_limit, current_balance, ...)

-- 22. General Ledger & Accounting
chart_of_accounts (id_account, account_code, account_name, account_type, parent_id, ...)
journal_entries (id_journal, entry_date, reference, description, total_amount, ...)
journal_details (id_detail, journal_id, account_id, debit, credit, description, ...)
trial_balance (id_tb, period, account_id, debit_total, credit_total, balance, ...)
financial_reports (id_report, report_type, period, data, generated_at, ...)

-- 23. Tax Management
tax_configurations (id_tax, tax_name, tax_rate, tax_type, is_active, ...)
tax_invoices (id_tax_invoice, invoice_id, tax_amount, tax_type, status, ...)
tax_reports (id_report, period, tax_type, total_tax, status, ...)

-- 24. Advanced Product Data
product_variants (id_variant, product_id, variant_name, sku, price, ...)
product_attributes (id_attribute, product_id, attribute_name, attribute_value, ...)
product_pricing (id_pricing, product_id, price_type, price, valid_from, valid_to, ...)
product_costs (id_cost, product_id, cost_type, cost_amount, effective_date, ...)

-- 25. Stock Movement Tracking
stock_movements (id_movement, product_id, movement_type, quantity, reference, ...)
stock_adjustments (id_adjustment, product_id, adjustment_type, quantity, reason, ...)
stock_counts (id_count, product_id, counted_qty, system_qty, variance, ...)
stock_valuation (id_valuation, product_id, valuation_method, unit_cost, total_value, ...)

-- 26. Customer & Supplier Classification
customer_types (id_type, type_name, credit_terms, discount_rate, ...)
supplier_types (id_type, type_name, payment_terms, lead_time, ...)
entity_classifications (id_classification, entity_id, classification_type, value, ...)

-- 27. Business Analytics
sales_analytics (id_analytic, period, product_id, customer_id, sales_amount, quantity, ...)
inventory_analytics (id_analytic, period, product_id, turnover, aging, ...)
financial_analytics (id_analytic, period, metric_type, metric_value, comparison, ...)

-- � **BUSINESS SETTINGS & TRACEABILITY TABLES:**

-- 28. Business Settings & Configuration
business_types (id_type, type_name, category, description, requirements, ...)
business_registrations (id_registration, company_id, license_type, number, expiry, ...)
operational_settings (id_setting, branch_id, setting_type, config_value, ...)
supplier_categories (id_category, category_name, supplier_type, requirements, ...)

-- 29. Supplier Diversity Management
individual_suppliers (id_supplier, supplier_type, id_number, address, bank_account, ...)
business_entities (id_entity, entity_type, legal_docs, tax_id, verification_status, ...)
supplier_classifications (id_classification, supplier_id, rating, performance_score, ...)
supplier_documents (id_doc, supplier_id, doc_type, file_path, expiry_date, ...)

-- 30. Supply Chain Traceability
product_origins (id_origin, product_id, source_type, producer_id, coordinates, ...)
custody_chain (id_custody, product_id, custodian_id, handover_date, conditions, ...)
traceability_records (id_record, product_id, stage, timestamp, handler_id, ...)
transport_tracking (id_transport, product_id, vehicle_id, route, temperature, ...)
storage_conditions (id_storage, product_id, location_id, temperature, humidity, ...)
compliance_certificates (id_cert, product_id, cert_type, issuer, valid_until, ...)

-- 31. Traceability Data Management
data_capture_points (id_point, location, capture_type, auto_capture, ...)
documentation_types (id_doc_type, type_name, template, required_fields, ...)
retention_policies (id_policy, doc_type, retention_period, legal_requirement, ...)
traceability_security (id_security, record_id, hash, signature, access_log, ...)

-- 32. Indonesian Localization Settings
locale_settings (id_locale, setting_type, indonesian_value, english_value, ...)
currency_formats (id_format, locale, symbol, decimal_sep, thousands_sep, ...)
date_formats (id_format, locale, date_pattern, time_pattern, ...)
translation_keys (id_key, module, key_text, indonesian_text, english_text, ...)

### **🔐 Owner Access Control**
```sql
-- Owner-specific tables
owner_dashboard (id_dashboard, widget_type, data_source, position, ...)
tenant_monitoring (id_tenant, tenant_name, status, subscription_level, ...)
system_metrics (id_metric, metric_name, value, timestamp, ...)
security_alerts (id_alert, alert_type, severity, description, resolved, ...)
```

## 💻 **FULLSTACK DEVELOPMENT APPROACH**

### **🛠️ Technology Stack**
```markdown
Frontend:
- React.js dengan TypeScript
- Material-UI atau Ant Design
- Redux Toolkit untuk state management
- Chart.js untuk visualisasi data
- PWA untuk mobile experience

Backend:
- Node.js dengan Express.js
- TypeScript untuk type safety
- JWT untuk authentication
- Socket.io untuk real-time updates
- Bull Queue untuk background jobs

Database:
- PostgreSQL untuk primary database
- Redis untuk caching dan sessions
- MongoDB untuk analytics data
- Elasticsearch untuk search functionality

DevOps:
- Docker untuk containerization
- AWS/Azure untuk cloud hosting
- GitHub Actions untuk CI/CD
- Nginx untuk reverse proxy
- SSL certificates untuk security

Mobile:
- React Native untuk mobile apps
- Push notifications
- Offline data synchronization
- QR code scanning capability
```

### **🏗️ Architecture Pattern**
```markdown
1. **Microservices Architecture**
   - User Service
   - Product Service
   - Order Service
   - Payment Service
   - Analytics Service
   - Notification Service

2. **API Design**
   - RESTful API dengan OpenAPI documentation
   - GraphQL untuk complex queries
   - Rate limiting dan throttling
   - API versioning strategy

3. **Security Implementation**
   - OAuth 2.0 untuk authentication
   - Role-based access control (RBAC)
   - Data encryption at rest dan in transit
   - Regular security audits

4. **Performance Optimization**
   - Database indexing strategy
   - Caching layers (Redis)
   - CDN untuk static assets
   - Lazy loading untuk components
```

## � **OWNER MONITORING SYSTEM**

### **🎯 Role: Application Owner (Super Admin)**
```markdown
Sebagai pemilik aplikasi, Anda memiliki akses penuh untuk memonitor semua aktivitas:

1. **Multi-Company/Tenant Monitoring**
   - Dashboard overview semua perusahaan
   - Aktivitas per cabang/tenant
   - Performance metrics perusahaan
   - Revenue tracking per tenant
   - Usage statistics dan analytics

2. **System Health Monitoring**
   - Server performance monitoring
   - Database usage tracking
   - API response time monitoring
   - Error rate tracking
   - User activity analytics

3. **Financial Monitoring**
   - Total revenue dari semua tenant
   - Subscription fee tracking
   - Payment gateway status
   - Financial reports per tenant
   - Revenue forecasting

4. **Security & Compliance**
   - Security event monitoring
   - Compliance status per tenant
   - Audit trail review
   - Data access monitoring
   - GDPR/privacy compliance tracking

5. **Business Intelligence**
   - Market trend analysis
   - Customer behavior insights
   - Product performance analytics
   - Geographic distribution analysis
   - Growth metrics tracking
```

### **🔐 Owner Access Control**
```sql
-- Owner-specific tables
owner_dashboard (id_dashboard, widget_type, data_source, position, ...)
tenant_monitoring (id_tenant, tenant_name, status, subscription_level, ...)
system_metrics (id_metric, metric_name, value, timestamp, ...)
security_alerts (id_alert, alert_type, severity, description, resolved, ...)
```

## 💻 **FULLSTACK DEVELOPMENT APPROACH**

### **🛠️ Technology Stack**
```markdown
Frontend:
- React.js dengan TypeScript
- Material-UI atau Ant Design
- Redux Toolkit untuk state management
- Chart.js untuk visualisasi data
- PWA untuk mobile experience

Backend:
- Node.js dengan Express.js
- TypeScript untuk type safety
- JWT untuk authentication
- Socket.io untuk real-time updates
- Bull Queue untuk background jobs

Database:
- PostgreSQL untuk primary database
- Redis untuk caching dan sessions
- MongoDB untuk analytics data
- Elasticsearch untuk search functionality

DevOps:
- Docker untuk containerization
- AWS/Azure untuk cloud hosting
- GitHub Actions untuk CI/CD
- Nginx untuk reverse proxy
- SSL certificates untuk security

Mobile:
- React Native untuk mobile apps
- Push notifications
- Offline data synchronization
- QR code scanning capability
```

### **🏗️ Architecture Pattern**
```markdown
1. **Microservices Architecture**
   - User Service
   - Product Service
   - Order Service
   - Payment Service
   - Analytics Service
   - Notification Service

2. **API Design**
   - RESTful API dengan OpenAPI documentation
   - GraphQL untuk complex queries
   - Rate limiting dan throttling
   - API versioning strategy

3. **Security Implementation**
   - OAuth 2.0 untuk authentication
   - Role-based access control (RBAC)
   - Data encryption at rest dan in transit
   - Regular security audits

4. **Performance Optimization**
   - Database indexing strategy
1. **Customer Relationship Management (CRM)**
   - Customer database with purchase history
   - Loyalty points system
   - Customer segmentation
   - Birthday promotions
   - Customer tier levels (Bronze, Silver, Gold)

2. **Advanced Employee Management**
   - Shift scheduling system
   - Time tracking & attendance
   - Sales performance monitoring
   - Commission calculation
   - Multi-level user permissions

3. **Supplier Relationship Management**
   - Supplier database
   - Purchase order generation
   - Supplier performance tracking
   - Auto-reorder based on stock levels
   - Payment term management

4. **Advanced Inventory Management**
   - Batch/lot number tracking
   - Expiry date alerts
   - Serial number management
   - FIFO/LIFO costing methods
   - Stock aging reports
```

### **📈 Phase 2: Important Features (Months 3-4)**
```markdown
Priority: MEDIUM - Industry Standard Gap: 65%

1. **Marketing & Promotions**
   - Campaign management system
   - Coupon generation & management
   - Bundle deals creation
   - Flash sales management
   - Targeted marketing campaigns

2. **E-commerce Integration**
   - Online store synchronization
   - Omnichannel inventory management
   - Click & collect functionality
   - Delivery tracking system
   - Payment gateway integration

3. **Advanced Security & Compliance**
   - Role-based access control
   - Activity audit logs
   - Data encryption
   - Regular backup system
   - Security monitoring

4. **Mobile Features**
   - Mobile POS development
   - Mobile dashboard access
   - Push notifications
   - Offline mode support
   - QR code scanning
```

### **🤖 Phase 3: Advanced Features (Months 5-6)**
```markdown
Priority: LOW - Industry Standard Gap: 55%

1. **AI & Machine Learning Integration**
   - Demand forecasting
   - Customer behavior analytics
   - Product recommendations
   - Dynamic pricing
   - Churn prediction

2. **Business Intelligence**
   - Advanced analytics dashboard
   - Real-time reporting
   - Custom report builder
   - KPI tracking system
   - Data visualization

3. **API Ecosystem**
   - Third-party integrations
   - Webhook system
   - Developer API
   - Documentation
   - API analytics

4. **Enterprise Features**
   - Multi-currency support
   - Multi-language support
   - Workflow automation
   - Document management
   - Compliance reporting
```

## �� **IMPLEMENTASI DATABASE DARI NOL**

### **Phase 1: Database Structure**
```sql
-- 1. Database Setup
CREATE DATABASE perdagangan_system;
USE perdagangan_system;

-- 2. Tabel Perusahaan
CREATE TABLE companies (
    id_company INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    company_code VARCHAR(50) UNIQUE NOT NULL,
    company_type ENUM('pusat','cabang','franchise','koperasi','individual') DEFAULT 'pusat',
    owner_name VARCHAR(200),
    phone VARCHAR(50),
    email VARCHAR(100),
    address TEXT,
    tax_id VARCHAR(50),
    business_license VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Tabel Cabang
CREATE TABLE branches (
    id_branch INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    branch_name VARCHAR(200) NOT NULL,
    branch_code VARCHAR(50) NOT NULL,
    branch_type ENUM('toko','warung','minimarket','gerai','kios','online') DEFAULT 'toko',
    owner_name VARCHAR(200),
    phone VARCHAR(50),
    email VARCHAR(100),
    location_id INT,
    operation_hours JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id_company)
);

-- 4. Tabel Lokasi (Integrasi dengan alamat_db)
CREATE TABLE branch_locations (
    id_location INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    address TEXT NOT NULL,
    province_id INT,
    regency_id INT,
    district_id INT,
    village_id INT,
    postal_code VARCHAR(10),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch)
);

-- 5. Tabel Anggota/Karyawan
CREATE TABLE members (
    id_member INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    member_code VARCHAR(50) NOT NULL,
    member_name VARCHAR(200) NOT NULL,
    position ENUM('owner','manager','cashier','staff','security') DEFAULT 'staff',
    phone VARCHAR(50),
    email VARCHAR(100),
    password_hash VARCHAR(255),
    salary DECIMAL(10,2),
    join_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch)
);

-- 6. Tabel Operasional Cabang
CREATE TABLE branch_operations (
    id_operation INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    operation_date DATE NOT NULL,
    open_time TIME,
    close_time TIME,
    status ENUM('open','closed','holiday') DEFAULT 'open',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch)
);

-- 7. Tabel Produk
CREATE TABLE products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    unit VARCHAR(20) DEFAULT 'PCS',
    purchase_price DECIMAL(15,2),
    selling_price DECIMAL(15,2),
    barcode VARCHAR(50),
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 8. Tabel Kategori Produk
CREATE TABLE product_categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id_category)
);

-- 9. Tabel Stok per Cabang
CREATE TABLE branch_inventory (
    id_inventory INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    product_id INT NOT NULL,
    stock_quantity DECIMAL(15,2) DEFAULT 0,
    min_stock DECIMAL(15,2) DEFAULT 0,
    max_stock DECIMAL(15,2) DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch),
    FOREIGN KEY (product_id) REFERENCES products(id_product),
    UNIQUE KEY uk_branch_product (branch_id, product_id)
);

-- 10. Tabel Transaksi
CREATE TABLE transactions (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    transaction_number VARCHAR(50) NOT NULL,
    transaction_type ENUM('sale','purchase','return','transfer','adjustment') NOT NULL,
    transaction_date DATETIME NOT NULL,
    customer_name VARCHAR(200),
    total_amount DECIMAL(15,2),
    payment_method ENUM('cash','transfer','debit','credit','e_wallet') DEFAULT 'cash',
    status ENUM('draft','completed','cancelled','refunded') DEFAULT 'draft',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch),
    FOREIGN KEY (created_by) REFERENCES members(id_member)
);

-- 11. Tabel Detail Transaksi
CREATE TABLE transaction_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    total_price DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id_transaction),
    FOREIGN KEY (product_id) REFERENCES products(id_product)
);

-- 12. Tabel Akun Kas
CREATE TABLE cash_accounts (
    id_cash INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    account_type ENUM('cash','bank','e_wallet') DEFAULT 'cash',
    balance DECIMAL(15,2) DEFAULT 0,
    account_number VARCHAR(50),
    bank_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch)
);

-- 13. Tabel Akuntansi
CREATE TABLE chart_of_accounts (
    id_account INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    account_type ENUM('asset','liability','equity','revenue','expense') NOT NULL,
    normal_balance ENUM('debit','credit') NOT NULL,
    opening_balance DECIMAL(15,2) DEFAULT 0,
    current_balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch)
);

-- 14. Tabel Jurnal
CREATE TABLE journal_entries (
    id_journal INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    journal_number VARCHAR(50) NOT NULL,
    journal_date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    debit_amount DECIMAL(15,2) DEFAULT 0,
    credit_amount DECIMAL(15,2) DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch),
    FOREIGN KEY (created_by) REFERENCES members(id_member)
);
```

### **Phase 2: Data Awal**
```sql
-- 1. Insert Perusahaan Pusat
INSERT INTO companies (company_name, company_code, company_type, owner_name, phone, email, address) 
VALUES ('Toko Sejahtera Bersama', 'TSB001', 'pusat', 'Ahmad Wijaya', '021-1234-5678', 'info@tokosejahtera.com', 'Jakarta Pusat');

-- 2. Insert Cabang
INSERT INTO branches (company_id, branch_name, branch_code, branch_type, owner_name, phone, email) 
VALUES 
(1, 'Toko Cabang A', 'TSB001-A', 'toko', 'Budi Santoso', '021-2345-6789', 'cabanga@tokosejahtera.com'),
(1, 'Toko Cabang B', 'TSB001-B', 'warung', 'Siti Nurhaliza', '021-3456-7890', 'cabangb@tokosejahtera.com');

-- 3. Insert Produk
INSERT INTO products (product_code, product_name, category, unit, purchase_price, selling_price) 
VALUES 
('PRD001', 'Beras Premium', 'Makanan Pokok', 'KG', 12000, 15000),
('PRD002', 'Minyak Goreng', 'Makanan Pokok', 'LITER', 14000, 18000),
('PRD003', 'Gula Pasir', 'Makanan Pokok', 'KG', 13000, 16000),
('PRD004', 'Kopi Sachet', 'Minuman', 'PCS', 2000, 3000),
('PRD005', 'Indomie Mie', 'Makanan Cepat', 'PCS', 2500, 3500);

-- 4. Insert Anggota
INSERT INTO members (branch_id, member_code, member_name, position, phone, email, password_hash) 
VALUES 
(1, 'MEM001', 'Budi Santoso', 'owner', '021-2345-6789', 'budi@tokosejahtera.com', '$2y$10$...'),
(1, 'MEM002', 'Ahmad Karyawan', 'staff', '021-2345-6789', 'ahmad@tokosejahtera.com', '$2y$10$...'),
(2, 'MEM003', 'Siti Nurhaliza', 'owner', '021-3456-7890', 'siti@tokosejahtera.com', '$2y$10$...');

-- 5. Insert Stok Awal
INSERT INTO branch_inventory (branch_id, product_id, stock_quantity, min_stock, max_stock) 
VALUES 
(1, 1, 100, 20, 500),  -- Beras
(1, 2, 50, 10, 200),   -- Minyak
(1, 3, 30, 5, 100),    -- Gula
(1, 4, 200, 50, 500),  -- Kopi
(1, 5, 150, 30, 300),  -- Indomie
(2, 1, 80, 15, 400),   -- Beras
(2, 2, 40, 8, 150),    -- Minyak
(2, 3, 25, 5, 80),     -- Gula
(2, 4, 150, 40, 400),  -- Kopi
(2, 5, 100, 20, 250);  -- Indomie
```

## 📱 **STRUKTUR APLIKASI**

### **1. Backend (PHP)**
```
perdagangan/
├── config/
│   ├── database.php
│   ├── app.php
│   └── session.php
├── models/
│   ├── Company.php
│   ├── Branch.php
│   ├── Member.php
│   ├── Product.php
│   ├── Transaction.php
│   └── Inventory.php
├── controllers/
│   ├── DashboardController.php
│   ├── BranchController.php
│   ├── ProductController.php
│   ├── TransactionController.php
│   └── ReportController.php
├── views/
│   ├── dashboard/
│   ├── branches/
│   ├── products/
│   ├── transactions/
│   └── reports/
├── api/
│   ├── v1/
│   │   ├── companies.php
│   │   ├── branches.php
│   │   ├── products.php
│   │   ├── transactions.php
│   │   └── reports.php
│   └── middleware/
├── helpers/
│   ├── Database.php
│   ├── Validator.php
│   ├── Logger.php
│   └── Auth.php
└── public/
    ├── index.php
    ├── assets/
    └── uploads/
```

### **2. Frontend (HTML/CSS/JavaScript)**
```
public/
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── dashboard.css
│   │   └── custom.css
│   ├── js/
│   │   ├── jquery.min.js
│   │   ├── bootstrap.min.js
│   │   ├── chart.js
│   │   └── app.js
│   └── images/
├── views/
│   ├── dashboard.php
│   ├── branches/
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   ├── products/
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   ├── transactions/
│   │   ├── pos.php
│   │   ├── sales.php
│   │   └── purchases.php
│   └── reports/
│       ├── sales.php
│       ├── inventory.php
│       └── financial.php
```

## 🔄 **ALUR IMPLEMENTASI**

### **Phase 1: Database Setup**
1. **Buat database** `perdagangan_system`
2. **Import data alamat** dari `alamat_db`
3. **Buat tabel-tabel** sesuai struktur
4. **Insert data awal** untuk testing

### **Phase 2: Backend Development**
1. **Setup framework** (CodeIgniter/Laravel)
2. **Buat models** untuk setiap tabel
3. **Buat controllers** untuk API
4. **Buat middleware** untuk auth
5. **Setup routing** API

### **Phase 3: Frontend Development**
1. **Setup template** Bootstrap
2. **Buat dashboard** responsive
3. **Buat form** CRUD untuk semua modul
4. **Integrasi API** dengan backend
5. **Setup chart** untuk laporan

### **Phase 4: Testing & Deployment**
1. **Unit testing** untuk backend
2. **Integration testing** API
3. **User acceptance testing**
4. **Deployment** ke production

## 🎯 **FITUR UTAMA APLIKASI**

### **1. Dashboard Pusat**
- **Ringkasan**: Total cabang, total transaksi, total pendapatan
- **Laporan**: Grafik penjualan per cabang, produk terlaris
- **Notifikasi**: Stok minimum, transaksi pending
- **Quick Actions**: Tambah cabang, transfer barang

### **2. Management Cabang**
- **Daftar Cabang**: Status cabang, lokasi, jam operasional
- **Detail Cabang**: Info lengkap, anggota, stok
- **Operasional**: Jam buka/tutup per cabang
- **Transfer**: Transfer barang antar cabang

### **3. POS (Point of Sale)**
- **Scan Barcode**: Cepat dan akurat
- **Search Produk**: Cari produk cepat
- **Keranjang**: Multiple item sekaligus
- **Pembayaran**: Multiple payment method
- **Struk**: Cetak struk otomatis

### **4. Manajemen Produk**
- **Master Produk**: Daftar produk global
- **Stok per Cabang**: Stok real-time per cabang
- **Transfer Barang**: Antar cabang
- **Harga**: Harga jual per cabang
- **Kategori**: Organisasi produk

### **5. Laporan & Analisis**
- **Laporan Penjualan**: Per cabang, per produk, per periode
- **Laporan Stok**: Stok minimum, overstock, movement
- **Laporan Keuangan**: Laba rugi per cabang
- **Analisis**: Produk terlaris, customer analysis

## 📊 **INTEGRASI LOKASI (alamat_db)**

### **1. Import Data Provinsi/Kabupaten**
```sql
-- Import dari alamat_db
CREATE TABLE provinces AS SELECT * FROM alamat_db.provinces;
CREATE TABLE regencies AS SELECT * FROM alamat_db.regencies;
CREATE TABLE districts AS SELECT * FROM alamat_db.districts;
CREATE TABLE villages AS SELECT * FROM alamat_db.villages;
```

### **2. Mapping Lokasi Cabang**
```sql
-- Update lokasi cabang
UPDATE branches b 
SET location_id = (
    SELECT l.id_location 
    FROM branch_locations l 
    WHERE l.branch_id = b.id_branch
    LIMIT 1
);
```

### **3. API Lokasi**
```php
// API untuk mendapatkan lokasi
GET /api/v1/locations/provinces
GET /api/v1/locations/regencies/{province_id}
GET /api/v1/locations/districts/{regency_id}
GET /api/v1/locations/villages/{district_id}
```

## 🏆 **ANALISIS KOMPETITIF & STRATEGI INOVASI**

### **📊 Analisis Aplikasi Kasir Top Indonesia**
```markdown
Berdasarkan analisis mendalam terhadap 17+ aplikasi kasir terpopuler di Indonesia:

1. **HashMicro POS** - Enterprise features dengan pricing premium
   - ✅ Keunggulan: Loyalty system, central inventory, multi-cabang
   - ❌ Kekurangan: Harga tinggi, kompleks untuk pemula

2. **Moka POS** - Cloud-based dengan fleksibilitas tinggi
   - ✅ Keunggulan: Multi-payment, sales tracking, cloud sync
   - ❌ Kekurangan: Biaya berlangganan, maintenance frequent

3. **Pawoon** - User-friendly dengan real-time reporting
   - ✅ Keunggulan: Interface intuitif, stock management
   - ❌ Kekurangan: Ketergantungan internet, pricing tidak pasti

4. **Qasir** - UKM-focused dengan interface sederhana
   - ✅ Keunggulan: Mudah dipahami, accessible, desain ramah
   - ❌ Kekurangan: Fitur terbatas, dukungan teknis terbatas

5. **Jurnal Touch** - Multi-store dengan akuntansi integration
   - ✅ Keunggulan: Sinkronisasi otomatis, offline capability
   - ❌ Kekurangan: Kurang spesifik POS, pricing tidak transparan
```

### **🚨 Kekurangan Kritikal Pasar**
```markdown
1. **Keterbatasan Transaksi** - 500 transaksi limit untuk free version
2. **Keamanan Data** - Rentan virus, backup tidak otomatis
3. **Ketergantungan Internet** - Tidak bisa transaksi offline
4. **User Experience** - Learning curve curam, interface tidak intuitif
5. **Pricing Model** - Subscription fatigue, hidden fees
6. **Integration Issues** - Limited third-party, API documentation buruk
7. **Reporting Limitations** - Basic reports only, tidak real-time
8. **Multi-Location** - Setup kompleks, sync issues, control limited
```

### **💡 Strategi Inovasi Superior**
```markdown
1. **Hybrid Architecture**
   - Offline-first design dengan smart sync
   - Queue system untuk transaksi offline
   - Conflict resolution otomatis
   - Progressive Web App untuk universal access

2. **Intelligent Pricing**
   - Freemium model yang generous
   - Pay-as-you-grow pricing
   - One-time purchase option
   - Transparent pricing tanpa hidden fees

3. **Advanced Analytics**
   - AI-powered business insights
   - Predictive analytics untuk inventory
   - Customer behavior analysis
   - Real-time dashboard dengan drill-down

4. **Seamless User Experience**
   - Contextual onboarding dengan tutorials
   - Adaptive UI yang belajar dari user behavior
   - Voice commands untuk hands-free operation
   - Multi-language support dengan Indonesian focus

5. **Ecosystem Integration**
   - Open API dengan comprehensive documentation
   - Marketplace untuk third-party integrations
   - Plugin architecture untuk extensibility
   - Webhook system untuk real-time events

6. **Indonesian Market Specialization**
   - QRIS integration native
   - E-wallet support (OVO, GoPay, Dana, ShopeePay)
   - Tax compliance otomatis (PPN, PPh)
   - BPJS integration untuk employee management
   - Local payment methods (cicilan, kredit toko)
```

### **🎯 Fitur Differentiation Strategy**
```markdown
1. **Speed Excellence**
   - Ultra-fast transaction processing (<1 second)
   - Optimized database queries
   - Caching layers for performance
   - Minimal API response time

2. **Reliability Superior**
   - 99.9% uptime guarantee
   - Offline capability dengan queue system
   - Automatic backup dan recovery
   - Redundant infrastructure

3. **Flexibility Maximum**
   - Works untuk semua ukuran bisnis
   - Scalable architecture
   - Customizable workflows
   - Industry-specific templates

4. **Intelligence Built-in**
   - AI-powered recommendations
   - Fraud detection algorithms
   - Demand forecasting
   - Business optimization insights

5. **Integration Excellence**
   - Most comprehensive API ecosystem
   - Pre-built connectors untuk popular services
   - Custom integration support
   - Real-time synchronization
```

## � **DETAILED BUSINESS REQUIREMENTS & CRITICAL SUCCESS FACTORS**

### **🏪 Micro-Level Business Operations**
```markdown
1. **Daily Store Operations**
   - Opening procedures: System boot-up, cash drawer initialization, daily targets
   - Closing procedures: Sales reconciliation, cash counting, system backup
   - Shift management: Handover protocols, task tracking, performance metrics

2. **Advanced Payment Processing**
   - Multiple methods: Cash, cards, e-wallets (OVO, GoPay, Dana, ShopeePay), QRIS
   - Payment security: PCI compliance, encryption, fraud detection
   - Reconciliation: Daily settlement, fee tracking, dispute handling

3. **Granular Inventory Management**
   - Product lifecycle: Creation, barcode generation, price history
   - Stock movements: Receipts, transfers, returns, adjustments
   - Valuation methods: FIFO, average cost, market valuation

4. **Customer Management Details**
   - Data points: Personal info, behavior tracking, communication preferences
   - Loyalty mechanics: Points, tiers, rewards, expiration policies
   - Analytics: Purchase frequency, average value, churn risk

5. **Financial Management Micro-Details**
   - Revenue recognition: Sales, service income, commissions
   - Expense management: COGS, operating costs, depreciation
   - Cash flow: Daily position, aging reports, forecasting
```

### **🎯 Critical Success Factors - Comprehensive Implementation Strategy**
```markdown
1. **User Adoption Strategies**
   📋 **Onboarding Excellence:**
   - Guided setup wizard dengan 5-step process
   - Interactive tutorial dengan real scenarios
   - Role-based training modules (owner, manager, cashier, staff)
   - 24/7 Indonesian support via WhatsApp, phone, email
   - Video tutorials dalam Bahasa Indonesia dengan subtitle
   - Progressive disclosure untuk complexity management

   🎨 **UX Design Optimization:**
   - Indonesian UI patterns dengan cultural adaptation
   - Minimal clicks principle (max 3 clicks untuk core functions)
   - Personalization dashboard berdasarkan user behavior
   - Voice navigation untuk accessibility (Bahasa Indonesia)
   - Dark mode untuk eye comfort
   - Gesture-based controls untuk mobile users

   🔄 **Change Management Framework:**
   - Phased rollout dengan pilot program (3 bulan)
   - User feedback collection dengan weekly surveys
   - Beta testing group dengan 100+ diverse users
   - Change champions program untuk internal advocates
   - Regular update communications dengan changelog
   - Migration assistance dari existing systems

2. **Data Accuracy Measures**
   ✅ **Validation Systems:**
   - Real-time field validation dengan instant feedback
   - Business rules engine dengan customizable logic
   - Data quality scoring system (0-100 scale)
   - Duplicate detection dengan smart algorithms
   - Cross-reference validation antar modules
   - Mandatory field completion tracking

   🔄 **Synchronization Protocols:**
   - Multi-location sync dengan conflict resolution
   - Automated backup dengan 3-tier storage (local, cloud, offsite)
   - Comprehensive audit trails dengan immutable logs
   - Real-time sync status indicators
   - Manual override capabilities dengan approval workflow
   - Data integrity checks setiap 15 menit

   🔍 **Quality Control Framework:**
   - Automatic data cleansing dengan AI suggestions
   - Standardization templates untuk consistent formatting
   - Verification workflows untuk critical data
   - Periodic data health reports dengan recommendations
   - Error tracking dengan root cause analysis
   - Data governance policies dengan enforcement

3. **System Reliability Requirements**
   ⚡ **Performance Standards:**
   - POS transaction processing <2 seconds (99.9% compliance)
   - 99.9% uptime guarantee dengan SLA monitoring
   - Horizontal scaling dengan auto-scaling groups
   - Database query optimization dengan <100ms response
   - Mobile app startup time <3 seconds
   - API response time <500ms for 95% requests

   🔧 **Maintenance Protocols:**
   - Preventive maintenance schedules dengan automated checks
   - MTTR (Mean Time To Repair) targets <4 hours
   - Real-time monitoring dengan predictive analytics
   - Capacity planning dengan 6-month forecast
   - Security patch management dalam 48 hours
   - Performance tuning dengan monthly reviews

   🛡️ **Security & Compliance:**
   - End-to-end encryption (AES-256) untuk all data
   - Role-based access control dengan least privilege principle
   - Indonesian compliance (PSE, PDPA, Tax regulations)
   - Multi-factor authentication untuk admin access
   - Regular security audits dengan penetration testing
   - GDPR-inspired data protection untuk Indonesian context

4. **Business Value Metrics**
   💰 **Financial KPIs:**
   - Revenue enhancement target: 15-25% dalam 6 bulan
   - Cost reduction through automation: 20-30% operational costs
   - ROI calculation dengan 12-month payback period
   - Inventory carrying cost reduction: 15-20%
   - Labor cost optimization: 10-15% through efficiency
   - Payment processing cost reduction: 5-10%

   📊 **Operational Metrics:**
   - Transaction time reduction: 50% faster checkout
   - Inventory turnover improvement: 25% increase
   - Customer satisfaction scores: Target 4.5/5.0
   - Employee productivity: 30% increase in tasks/hour
   - Error rate reduction: 90% fewer manual errors
   - Stock accuracy: 99.5% inventory accuracy

   🎯 **Strategic Indicators:**
   - Market share growth: 5-10% dalam target segment
   - Competitive advantage score: Top 3 dalam market analysis
   - Scalability index: Support 10x growth tanpa performance degradation
   - Customer retention rate: 85% annual retention
   - Net Promoter Score (NPS): Target 50+
   - Innovation adoption rate: 60% feature adoption dalam 3 bulan

5. **Advanced Success Metrics**
   📈 **Growth Indicators:**
   - Monthly Active Users (MAU) growth: 20% month-over-month
   - Transaction volume growth: 25% quarterly growth
   - Average Revenue Per User (ARPU): 15% annual increase
   - Customer Lifetime Value (CLV): 30% improvement
   - Cross-sell/upsell conversion: 35% increase
   - Referral rate: 20% new users dari existing customers

   🎯 **User Engagement Metrics:**
   - Daily login rate: 70% untuk active users
   - Feature adoption rate: 80% untuk core features
   - Support ticket reduction: 50% fewer tickets/user
   - User satisfaction score: 4.5+ average rating
   - Training completion rate: 90% for new users
   - Community engagement: 40% participation dalam forums

   🔍 **Quality & Innovation Metrics:**
   - Bug resolution time: <24 hours untuk critical issues
   - Feature release frequency: Monthly major updates
   - Innovation index: 3 new features per quarter
   - Technology debt ratio: <15% of development time
   - User feedback implementation: 70% of suggestions reviewed
   - Competitive feature parity: 95% vs top competitors
```

## 🎯 **SKALABILITAS UNIVERSAL - DARI INDIVIDU HINGGA ENTERPRISE**

### **👤 Level 1: Individu/Personal (Ultra-Mikro)**
```markdown
Target: Pedagang pribadi, penjual barang rumahan, UKM ultra-mikro

📱 **Device Requirements:**
- Smartphone Android minimal (RAM 2GB+)
- Tablet 7+ inch (opsional)
- Tidak memerlukan komputer desktop

🌐 **Koneksi:**
- Offline-first design
- Sync saat WiFi tersedia
- Data usage minimal (<100MB/bulan)

💰 **Pricing:**
- Gratis untuk basic features
- Rp 50.000/bulan untuk advanced features
- Pay-per-use untuk premium features

📋 **Essential Features:**
- Simple product catalog (max 50 items)
- Basic POS dengan barcode scanning
- Simple sales reporting
- WhatsApp receipt sharing
- Basic inventory tracking
```

### **🏘️ Level 2: Warung/Kios (Mikro)**
```markdown
Target: Warung makan, kios kecil, toko kelontong

📱 **Device Requirements:**
- Smartphone atau tablet
- Optional desktop/laptop
- Thermal printer (optional)

🌐 **Koneksi:**
- Offline capability
- Automatic sync saat online
- Low bandwidth optimization

💰 **Pricing:**
- Rp 100.000/bulan
- All-inclusive basic package
- Add-on modules available

📋 **Enhanced Features:**
- Product catalog (max 200 items)
- Customer database basic
- Supplier management
- Expense tracking
- Daily/monthly reports
- Promotions management
```

### **🏪 Level 3: Toko Kelontong (Kecil-Menengah)**
```markdown
Target: Toko sembako, retail skala kecil

💻 **Device Requirements:**
- Desktop/laptop recommended
- Tablet untuk mobile POS
- Receipt printer
- Barcode scanner

🌐 **Koneksi:**
- Cloud-based dengan offline backup
- Real-time sync multi-device
- API integration ready

💰 **Pricing:**
- Rp 250.000/bulan
- Complete package
- Custom modules available

📋 **Advanced Features:**
- Unlimited product catalog
- Complete CRM system
- Multi-payment methods
- Advanced inventory
- Financial reporting
- Employee management (max 5 users)
```

### **🏬 Level 4: Minimarket/Pengusaha Menengah**
```markdown
Target: Minimarket, retail modern, bisnis menengah

💻 **Device Requirements:**
- Desktop/laptop essential
- Multiple POS terminals
- Barcode scanners
- Receipt printers
- Cash drawers

🌐 **Koneksi:**
- High-speed internet required
- Real-time multi-location sync
- API ecosystem integration

💰 **Pricing:**
- Rp 500.000/bulan
- Enterprise features included
- Custom pricing available

📋 **Professional Features:**
- Multi-location management
- Advanced analytics dashboard
- Complete accounting system
- Multi-user management
- API access
- Custom reporting
```

### **🏢 Level 5: Distributor/Perusahaan Menengah**
```markdown
Target: Distributor grosir, perusahaan menengah

💻 **Device Requirements:**
- Server infrastructure
- Multiple POS stations
- Warehouse management devices
- Mobile devices for field staff

🌐 **Koneksi:**
- Enterprise-grade connectivity
- Dedicated server options
- High-availability setup
- Global CDN integration

💰 **Pricing:**
- Rp 1.000.000/bulan
- Custom enterprise pricing
- SLA included

📋 **Enterprise Features:**
- Multi-branch management
- Advanced supply chain
- Complete ERP integration
- Custom workflows
- Advanced security
- 24/7 support
```

### **🏭 Level 6: Perusahaan Besar/Franchise**
```markdown
Target: Corporate besar, franchise, multi-national

💻 **Device Requirements:**
- Cloud infrastructure
- Enterprise servers
- Global network setup
- IoT integration devices

🌐 **Koneksi:**
- Global infrastructure
- Multi-region deployment
- High-availability clusters
- Edge computing

💰 **Pricing:**
- Custom enterprise pricing
- Volume discounts
- Implementation services

📋 **Corporate Features:**
- Global multi-location
- Advanced compliance
- Custom development
- White-label options
- Dedicated support team
- Consulting services
```

### **� Adaptive Architecture**
```markdown
1. **Progressive Feature Unlock**
   - Start dengan basic features
   - Upgrade sesuai kebutuhan
   - Seamless transition antar level

2. **Device Flexibility**
   - Mobile-first untuk level 1-2
   - Hybrid untuk level 3-4
   - Desktop-first untuk level 5-6

3. **Data Scalability**
   - Local storage untuk level 1-2
   - Cloud hybrid untuk level 3-4
   - Full cloud untuk level 5-6

4. **Support Tiers**
   - Community support (level 1-2)
   - Email support (level 3-4)
   - Priority support (level 5-6)
   - Dedicated team (enterprise)
```

### **🌍 Desa Terpencil Considerations**
```markdown
1. **Offline-First Design**
   - Full functionality tanpa internet
   - Queue system untuk sync
   - Local data storage

2. **Low Resource Requirements**
   - Minimal RAM usage
   - Low storage needs
   - Battery optimization

3. **Simple Interface**
   - Large buttons for touch screens
   - High contrast visibility
   - Minimal text, more icons

4. **Local Language Support**
   - Bahasa Indonesia dengan dialek lokal
   - Voice commands (optional)
   - Tutorial video offline

5. **Affordable Hardware**
   - Compatible dengan low-end Android
   - Works dengan basic printers
   - No expensive hardware required
```

## 🔧 **TEKNOLOGI YANG DIGUNAKAN**
