# 📋 DOM ELEMENTS ID DOCUMENTATION

## 🎯 **OVERVIEW**

Dokumentasi ini berisi semua ID elements yang digunakan untuk AJAX operations di aplikasi perdagangan multi-cabang. Semua ID telah distandarisasi dan divalidasi untuk memastikan konsistensi dan kemudahan maintenance.

---

## 📊 **DASHBOARD PAGE** (`app/views/dashboard/index.php`)

### **Header & Navigation**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `refreshDashboardBtn` | Button | Refresh dashboard data | AJAX refresh |
| `exportDropdownBtn` | Button | Export dropdown toggle | Bootstrap dropdown |
| `exportDropdownMenu` | Dropdown menu | Export options list | Bootstrap dropdown |
| `exportJsonBtn` | Link | Export JSON | File download |
| `exportCsvBtn` | Link | Export CSV | File download |

### **Statistics Cards**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `totalCompanies` | H4 | Total companies count | AJAX update |
| `activeCompanies` | H4 | Active companies count | AJAX update |
| `totalBranches` | H4 | Total branches count | AJAX update |
| `openBranchesCount` | H4 | Open branches count | AJAX update |

### **Real-time Statistics**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `realtimeStatsContainer` | Div | Real-time stats container | AJAX update |
| `todaySalesAmount` | H5 | Today's sales amount | AJAX update |
| `todayTransactionsCount` | H5 | Today's transaction count | AJAX update |
| `lowStockAlertsCount` | H5 | Low stock alerts count | AJAX update |
| `lastUpdatedTime` | Small | Last updated timestamp | AJAX update |

### **Charts**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `chartsContainer` | Div | Charts container | Layout |
| `scalabilityChart` | Canvas | Scalability distribution chart | Chart.js |
| `segmentChart` | Canvas | Business segment chart | Chart.js |

### **Tables**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `tablesContainer` | Div | Tables container | Layout |
| `branchesTableTitle` | H5 | Branches table title | Text |
| `branchesInventoryTable` | Table | Branches inventory table | AJAX update |
| `branchesInventoryTableBody` | Tbody | Table body content | AJAX update |
| `openBranchesTitle` | H5 | Open branches title | Text |
| `openBranchesList` | Div | Open branches list | AJAX update |
| `noOpenBranchesMsg` | Div | No branches message | Show/hide |

### **Recent Companies**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `recentCompaniesContainer` | Div | Recent companies container | Layout |
| `recentCompaniesTitle` | H5 | Recent companies title | Text |
| `recentCompaniesTable` | Table | Recent companies table | AJAX update |
| `recentCompaniesTableBody` | Tbody | Table body content | AJAX update |

---

## 🏢 **COMPANYAAN PERUSAHAAN** (`app/views/companies/index.php`)

### **Header & Controls**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `companiesHeader` | Div | Page header container | Layout |
| `refreshCompaniesBtn` | Button | Refresh companies data | AJAX refresh |
| `addCompanyBtn` | Button | Add company button | Modal trigger |

### **Search & Filter**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `companiesSearchBar` | Div | Search bar container | Layout |
| `searchCompaniesInput` | Input | Search input field | AJAX search |
| `filterCompanyType` | Select | Company type filter | AJAX filter |

### **Companies Table**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `companiesTableCard` | Card | Table container card | Layout |
| `companiesTableTitle` | H5 | Table title | Text |
| `companiesTable` | Table | Companies table | AJAX update |
| `companiesTableBody` | Tbody | Table body content | AJAX update |
| `companiesPagination` | Div | Pagination container | AJAX pagination |

### **Company Modal**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `companyModal` | Modal | Company form modal | Bootstrap modal |
| `companyModalTitle` | H5 | Modal title | Text update |
| `closeCompanyModalBtn` | Button | Close modal button | Bootstrap |
| `companyForm` | Form | Company form | AJAX submit |
| `company_name` | Input | Company name field | Form validation |
| `company_code` | Input | Company code field | Form validation |
| `company_type` | Select | Company type field | Form validation |
| `scalability_level` | Select | Scalability level field | Form validation |
| `owner_name` | Input | Owner name field | Form validation |
| `business_category` | Select | Business category field | Form validation |
| `email` | Input | Email field | Form validation |
| `phone` | Input | Phone field | Form validation |
| `address` | Textarea | Address field | Form validation |
| `tax_id` | Input | Tax ID field | Form validation |
| `business_license` | Input | Business license field | Form validation |
| `company_id` | Hidden | Company ID field | Form data |
| `cancelCompanyBtn` | Button | Cancel button | Modal close |
| `saveCompanyBtn` | Button | Save button | Form submit |

### **Delete Confirmation Modal**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `deleteModal` | Modal | Delete confirmation modal | Bootstrap modal |
| `deleteModalTitle` | H5 | Modal title | Text |
| `closeDeleteModalBtn` | Button | Close modal button | Bootstrap |
| `deleteCompanyName` | Span | Company name display | Text update |
| `cancelDeleteBtn` | Button | Cancel button | Modal close |
| `confirmDeleteBtn` | Button | Confirm delete button | AJAX delete |

---

## 🔐 **LOGIN PAGE** (`app/views/auth/login.php`)

### **Login Form**
| ID | Element | Description | Usage |
|-----|---------|-------------|-------|
| `loginForm` | Form | Login form | Form submit |
| `loginErrorAlert` | Alert | Error message display | Show/hide |
| `loginUsername` | Input | Username field | Form validation |
| `loginPassword` | Input | Password field | Form validation |
| `loginRemember` | Checkbox | Remember me checkbox | Form data |
| `loginSubmitBtn` | Button | Submit button | Form submit |

---

## 🎯 **JAVASCRIPT FUNCTIONS** (`assets/js/app.js`)

### **Dashboard Functions**
| Function | Target ID | Description |
|---------|------------|-------------|
| `updateDashboardCards()` | `#totalCompanies`, `#activeCompanies`, `#totalBranches`, `#activeBranches` | Update dashboard statistics |
| `updateRealtimeStats()` | `#todaySalesAmount`, `#todayTransactionsCount`, `#lowStockAlertsCount`, `#lastUpdatedTime` | Update real-time data |
| `loadScalabilityChart()` | `#scalabilityChart` | Load scalability chart |
| `loadSegmentChart()` | `#segmentChart` | Load segment chart |

### **Company Management Functions**
| Function | Target ID | Description |
|---------|------------|-------------|
| `loadCompanies()` | `#companiesTableBody`, `#companiesPagination` | Load companies table |
| `renderCompaniesTable()` | `#companiesTableBody` | Render table rows |
| `renderPagination()` | `#companiesPagination` | Render pagination |
| `refreshCompanies()` | `#companiesTableBody` | Refresh table data |
| `editCompany()` | `#companyModal`, form fields | Edit company data |
| `deleteCompany()` | `#deleteModal`, `#deleteCompanyName`, `#confirmDeleteBtn` | Delete company |
| `toggleCompanyStatus()` | Table rows | Toggle company status |

### **Form Functions**
| Function | Target ID | Description |
|---------|------------|-------------|
| `submitFormAjax()` | Form submit buttons | AJAX form submission |
| `ajaxSearch()` | Search inputs | Live search |
| `ajaxPagination()` | Pagination containers | AJAX pagination |

### **Utility Functions**
| Function | Target ID | Description |
|---------|------------|-------------|
| `showNotification()` | Body | Toast notifications |
| `showLoading()` | Any element | Loading overlay |
| `hideLoading()` | Any element | Hide loading |
| `animateNumber()` | Number elements | Number animation |

---

## 🔧 **AJAX ENDPOINTS**

### **Dashboard API**
| Endpoint | Method | Response | Target Elements |
|----------|--------|----------|----------------|
| `index.php?page=dashboard&action=api-stats` | GET | JSON | Dashboard cards |
| `index.php?page=dashboard&action=api-realtime` | GET | JSON | Real-time stats |
| `index.php?page=dashboard&action=api-scalability` | GET | JSON | Scalability chart |
| `index.php?page=dashboard&action=api-segments` | GET | JSON | Segment chart |

### **Company API**
| Endpoint | Method | Response | Target Elements |
|----------|--------|----------|----------------|
| `index.php?page=companies` | GET | JSON | Companies table |
| `index.php?page=companies&action=create` | POST | JSON | Form reset |
| `index.php?page=companies&action=edit&id={id}` | POST | JSON | Form update |
| `index.php?page=companies&action=delete&id={id}` | POST | JSON | Table row removal |
| `index.php?page=companies&action=details&id={id}` | GET | JSON | Modal population |
| `index.php?page=companies&action=toggle-status&id={id}` | POST | JSON | Status badge update |

---

## 📋 **SELECTOR PATTERNS**

### **jQuery Selectors Used**
```javascript
// Element selection
$('#elementId')                    // Single element
$('.className')                   // Multiple elements
$('#parentElement .childElement')    // Nested elements
```

### **Event Handlers**
```javascript
// Form events
$('#formId').on('submit', function(e) { ... });
$('#inputId').on('input', function() { ... });

// Click events
$('#buttonId').on('click', function() { ... });

// Modal events
$('#modalId').on('shown.bs.modal', function() { ... });
```

### **AJAX Patterns**
```javascript
// GET request
$.ajax({
    url: 'endpoint',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
        $('#targetId').text(response.data);
    }
});

// POST request
$.ajax({
    url: 'endpoint',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
        if (response.status === 'success') {
            showNotification(response.message);
        }
    }
});
```

---

## 🎨 **BEST PRACTICES**

### **ID Naming Conventions**
- **CamelCase** untuk form elements: `loginUsername`, `companyName`
- **Kebab-case** untuk containers: `companies-table`, `search-bar`
- **Suffixes** untuk clarity: `Btn` (buttons), `Input` (fields), `Modal` (modals)
- **Unique IDs** untuk setiap element di halaman yang sama

### **Consistency Rules**
1. **One ID per element** - Tidak ada duplikasi ID
2. **Semantic naming** - ID harus deskriptifikan fungsinya
3. **Consistent prefixes** - Group elements dengan prefix yang sama
4. **JavaScript ready** - Pastikan DOM loaded sebelum akses

### **Performance Tips**
1. **Cache selectors** - Simpan jQuery objects di variabel
2. **Event delegation** - Gunakan untuk dynamic content
3. **Debouncing** - Untuk search dan input events
4. **Lazy loading** - Load data saat dibutuhkan saja

---

## 🔄 **MAINTENANCE**

### **Adding New Elements**
1. **Gunakan ID unik** dengan format konsisten
2. **Update dokumentasi** ini dengan ID baru
3. **Tambah JavaScript function** jika perlu
4. **Test AJAX functionality** dengan benar

### **Modifying Existing Elements**
1. **Update semua references** di JavaScript
2. **Test semua affected AJAX calls**
3. **Update dokumentasi** jika ID berubah
4. **Validasi form validation** jika field form berubah

### **Debugging**
1. **Browser Console** - Cek jQuery selector errors
2. **Network Tab** - Monitor AJAX requests/responses
3. **Breakpoints** - Debug JavaScript functions
4. **Console.log** - Log data untuk debugging

---

**📋 Dokumentasi ini akan selalu diupdate saat ada perubahan struktur aplikasi. Pastikan untuk selalu merujuk dokumentasi ini saat menambah atau memodifikasi fitur AJAX.**
