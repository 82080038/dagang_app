/**
 * Aplikasi Perdagangan - JavaScript Functions
 * jQuery & AJAX Integration untuk Dynamic Application
 */

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide flash messages
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Initialize dashboard if on dashboard page
    if (window.location.pathname.includes('dashboard')) {
        initDashboard();
    }
});

/**
 * Dashboard Functions
 */
function initDashboard() {
    loadScalabilityChart();
    loadSegmentChart();
    
    // Setup window resize handler for charts
    setupChartResizeHandler();
    
    // Load initial data
    loadDashboardData();
    
    // Setup auto-refresh
    setupAutoRefresh();
}

/**
 * Setup Chart Resize Handler
 */
function setupChartResizeHandler() {
    let resizeTimeout;
    
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Resize charts after window resize
            if (window.scalabilityChartInstance) {
                window.scalabilityChartInstance.resize();
            }
            if (window.segmentChartInstance) {
                window.segmentChartInstance.resize();
            }
        }, 250);
    });
}

/**
 * Load Dashboard Statistics via AJAX
 */
function loadDashboardStats(callback) {
    $.ajax({
        url: 'index.php?page=dashboard&action=api-stats',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            showLoading('.dashboard-card');
        },
        success: function(response) {
            if (response.status === 'success') {
                updateDashboardCards(response.data);
                updateDashboardTables(response.data);
                
                if (callback) callback();
            } else {
                showNotification('Error loading dashboard data', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Dashboard Stats Error:', error);
            showNotification('Failed to load dashboard statistics', 'error');
        },
        complete: function() {
            hideLoading('.dashboard-card');
        }
    });
}

/**
 * Load Real-time Statistics
 */
function loadRealtimeStats() {
    $.ajax({
        url: 'index.php?page=dashboard&action=api-realtime',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                updateRealtimeStats(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Realtime Stats Error:', error);
        }
    });
}

/**
 * Update Dashboard Cards
 */
function updateDashboardCards(data) {
    // Update company stats
    $('#totalCompanies').text(data.companies.total_companies || 0);
    $('#activeCompanies').text(data.companies.active_companies || 0);
    
    // Update branch stats
    $('#totalBranches').text(data.branches.total_branches || 0);
    $('#activeBranches').text(data.branches.active_branches || 0);
    
    // Animate number changes
    animateNumber('#totalCompanies', data.companies.total_companies);
    animateNumber('#activeCompanies', data.companies.active_companies);
    animateNumber('#totalBranches', data.branches.total_branches);
    animateNumber('#activeBranches', data.branches.active_branches);
}

/**
 * Update Real-time Statistics
 */
function updateRealtimeStats(data) {
    if (data.today_sales) {
        $('#todaySalesAmount').text(formatCurrency(data.today_sales.total_sales));
        $('#todayTransactionsCount').text(data.today_sales.total_transactions);
    }
    
    $('#openBranchesCount').text(data.open_branches_count);
    $('#lowStockAlertsCount').text(data.low_stock_alerts);
    
    // Update last refreshed time
    $('#lastUpdatedTime').text('Last updated: ' + formatDateTime(data.last_updated));
}

/**
 * Load Scalability Chart
 */
function loadScalabilityChart() {
    console.log('BASE_URL:', BASE_URL);
    console.log('Full URL:', BASE_URL + '/index.php?page=dashboard&action=api-scalability');
    
    $.ajax({
        url: BASE_URL + '/index.php?page=dashboard&action=api-scalability',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderScalabilityChart(response.data);
            } else {
                console.error('Scalability Chart API Error:', response.message);
                if (typeof Toast !== 'undefined') {
                    Toast.error('Gagal memuat data chart scalability');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Scalability Chart Error:', error);
            console.error('XHR Status:', xhr.status);
            console.error('Response Text:', xhr.responseText);
            if (typeof Toast !== 'undefined') {
                Toast.error('Terjadi kesalahan saat memuat chart scalability');
            }
        }
    });
}

/**
 * Load Segment Chart
 */
function loadSegmentChart() {
    console.log('BASE_URL:', BASE_URL);
    console.log('Full URL:', BASE_URL + '/index.php?page=dashboard&action=api-segments');
    
    $.ajax({
        url: BASE_URL + '/index.php?page=dashboard&action=api-segments',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderSegmentChart(response.data);
            } else {
                console.error('Segment Chart API Error:', response.message);
                if (typeof Toast !== 'undefined') {
                    Toast.error('Gagal memuat data chart segment');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Segment Chart Error:', error);
            if (typeof Toast !== 'undefined') {
                Toast.error('Terjadi kesalahan saat memuat chart segment');
            }
        }
    });
}

/**
 * Render Scalability Chart
 */
function renderScalabilityChart(data) {
    var ctx = document.getElementById('scalabilityChart');
    if (!ctx) return;
    
    // Destroy existing chart if exists
    if (window.scalabilityChartInstance) {
        window.scalabilityChartInstance.destroy();
    }
    
    window.scalabilityChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Jumlah Perusahaan',
                data: data.data,
                backgroundColor: 'rgba(13, 110, 253, 0.8)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 100,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    cornerRadius: 4,
                    displayColors: false
                }
            },
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });
}

/**
 * Render Segment Chart
 */
function renderSegmentChart(data) {
    var ctx = document.getElementById('segmentChart');
    if (!ctx) return;
    
    // Destroy existing chart if exists
    if (window.segmentChartInstance) {
        window.segmentChartInstance.destroy();
    }
    
    window.segmentChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB', 
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 100,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    cornerRadius: 4,
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.parsed || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: false,
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });
}

/**
 * AJAX Form Submission
 */
function submitFormAjax(form, successCallback, errorCallback) {
    var formData = new FormData(form);
    var submitBtn = form.querySelector('button[type="submit"]');
    var originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    
    $.ajax({
        url: form.action,
        type: form.method,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showNotification(response.message || 'Operation successful', 'success');
                if (successCallback) successCallback(response);
            } else {
                showNotification(response.message || 'Operation failed', 'error');
                if (errorCallback) errorCallback(response);
            }
        },
        error: function(xhr, status, error) {
            var message = 'An error occurred';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showNotification(message, 'error');
            if (errorCallback) errorCallback({message: message});
        },
        complete: function() {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/**
 * AJAX Search Function
 */
function ajaxSearch(searchInput, resultsContainer, searchUrl, minChars = 3) {
    var searchTimeout;
    
    searchInput.on('input', function() {
        var query = $(this).val().trim();
        var $container = $(resultsContainer);
        
        clearTimeout(searchTimeout);
        
        if (query.length < minChars) {
            $container.hide();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            $container.html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Searching...</div>').show();
            
            $.ajax({
                url: searchUrl,
                type: 'GET',
                data: {q: query},
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        renderSearchResults(response.data, $container);
                    } else {
                        $container.html('<div class="text-center p-3 text-muted">No results found</div>');
                    }
                },
                error: function() {
                    $container.html('<div class="text-center p-3 text-danger">Search failed</div>');
                }
            });
        }, 300);
    });
}

/**
 * Render Search Results
 */
function renderSearchResults(results, container) {
    var html = '';
    
    results.forEach(function(item) {
        html += `
            <div class="search-result-item p-2 border-bottom hover-bg-light" onclick="selectSearchResult('${item.id}', '${item.name}')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">${item.description || ''}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary">${item.type}</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

/**
 * AJAX Pagination
 */
function ajaxPagination(container, url, page = 1) {
    var $container = $(container);
    
    $.ajax({
        url: url,
        type: 'GET',
        data: {page: page},
        dataType: 'json',
        beforeSend: function() {
            $container.find('.pagination-content').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        },
        success: function(response) {
            if (response.status === 'success') {
                renderPaginatedContent(response.data, $container);
                renderPaginationLinks(response.pagination, $container, url);
            }
        },
        error: function() {
            $container.find('.pagination-content').html('<div class="text-center p-4 text-danger">Failed to load data</div>');
        }
    });
}

/**
 * Show Loading State
 */
function showLoading(selector) {
    $(selector).addClass('loading').append('<div class="loading-overlay"><i class="fas fa-spinner fa-spin"></i></div>');
}

/**
 * Hide Loading State
 */
function hideLoading(selector) {
    $(selector).removeClass('loading').find('.loading-overlay').remove();
}

/**
 * Show Notification
 */
function showNotification(message, type = 'info') {
    var alertClass = type === 'success' ? 'alert-success' : 
                   type === 'error' ? 'alert-danger' : 
                   type === 'warning' ? 'alert-warning' : 'alert-info';
    
    var notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        notification.fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Animate Number
 */
function animateNumber(selector, targetValue, duration = 1000) {
    var $element = $(selector);
    var startValue = parseInt($element.text().replace(/[^0-9]/g, '')) || 0;
    var increment = (targetValue - startValue) / (duration / 16);
    var currentValue = startValue;
    
    var timer = setInterval(function() {
        currentValue += increment;
        
        if ((increment > 0 && currentValue >= targetValue) || (increment < 0 && currentValue <= targetValue)) {
            $element.text(Math.round(targetValue).toLocaleString());
            clearInterval(timer);
        } else {
            $element.text(Math.round(currentValue).toLocaleString());
        }
    }, 16);
}

/**
 * Format Currency
 */
function formatCurrency(amount) {
    return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

/**
 * Format Date Time
 */
function formatDateTime(dateString) {
    var date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Confirm Action
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Delete Item via AJAX
 */
function deleteItem(itemId, deleteUrl, successCallback) {
    confirmAction('Are you sure you want to delete this item?', function() {
        $.ajax({
            url: deleteUrl,
            type: 'POST',
            data: {id: itemId, _method: 'DELETE'},
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showNotification('Item deleted successfully', 'success');
                    if (successCallback) successCallback(response);
                } else {
                    showNotification(response.message || 'Failed to delete item', 'error');
                }
            },
            error: function() {
                showNotification('Failed to delete item', 'error');
            }
        });
    });
}

/**
 * Company Management Functions
 */
function loadCompanies(page = 1) {
    var search = $('#searchCompaniesInput').val();
    var filterType = $('#filterCompanyType').val();
    
    var params = new URLSearchParams({
        page_num: page,
        q: search,
        type: filterType
    });
    
    $.ajax({
        url: 'index.php?page=companies&' + params.toString(),
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#companiesTableBody').html('<tr><td colspan="8" class="text-center"><i class="bi bi-spinner fa-spin"></i> Loading...</td></tr>');
        },
        success: function(response) {
            if (response.status === 'success') {
                renderCompaniesTable(response.data.companies);
                renderPagination(response.data.pagination);
            } else {
                $('#companiesTableBody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
            }
        },
        error: function() {
            $('#companiesTableBody').html('<tr><td colspan="8" class="text-center text-danger">Terjadi kesalahan</td></tr>');
        }
    });
}

function renderCompaniesTable(companies) {
    var tbody = $('#companiesTableBody');
    tbody.empty();
    
    if (companies.length === 0) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted">Tidak ada data perusahaan</td></tr>');
        return;
    }
    
    companies.forEach(function(company) {
        var statusBadge = company.is_active ? 
            '<span class="badge bg-success">Aktif</span>' : 
            '<span class="badge bg-secondary">Non-aktif</span>';
        
        var row = `
            <tr>
                <td>
                    <strong>${company.company_name}</strong><br>
                    <small class="text-muted">${company.business_category || '-'}</small>
                </td>
                <td><code>${company.company_code}</code></td>
                <td><span class="badge bg-info">${company.company_type}</span></td>
                <td>${company.owner_name}</td>
                <td>${company.phone || '-'}</td>
                <td><span class="badge bg-warning">Level ${company.scalability_level}</span></td>
                <td>${statusBadge}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editCompany(${company.id_company})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-info" onclick="viewCompanyDetails(${company.id_company})" title="Detail">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="toggleCompanyStatus(${company.id_company})" title="Toggle Status">
                            <i class="bi bi-toggle-${company.is_active ? 'on' : 'off'}"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteCompany(${company.id_company}, '${company.company_name}')" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderPagination(pagination) {
    var container = $('#companiesPagination');
    container.empty();
    
    if (pagination.total_pages <= 1) return;
    
    var html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous
    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCompanies(${pagination.current_page - 1})">Previous</a></li>`;
    } else {
        html += '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    var start = Math.max(1, pagination.current_page - 2);
    var end = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    for (var i = start; i <= end; i++) {
        var active = i === pagination.current_page ? 'active' : '';
        html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadCompanies(${i})">${i}</a></li>`;
    }
    
    // Next
    if (pagination.current_page < pagination.total_pages) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCompanies(${pagination.current_page + 1})">Next</a></li>`;
    } else {
        html += '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    html += '</ul></nav>';
    container.html(html);
}

function refreshCompanies() {
    loadCompanies();
    showNotification('Data berhasil diperbarui', 'success');
}

function editCompany(companyId) {
    $.ajax({
        url: 'index.php?page=companies&action=details&id=' + companyId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var company = response.data.company;
                
                $('#companyModalTitle').text('Edit Perusahaan');
                $('#company_id').val(company.id_company);
                $('#company_name').val(company.company_name);
                $('#company_code').val(company.company_code);
                $('#company_type').val(company.company_type);
                $('#scalability_level').val(company.scalability_level);
                $('#owner_name').val(company.owner_name);
                $('#business_category').val(company.business_category);
                $('#email').val(company.email);
                $('#phone').val(company.phone);
                $('#address').val(company.address);
                $('#tax_id').val(company.tax_id);
                $('#business_license').val(company.business_license);
                
                $('#companyModal').modal('show');
            } else {
                showNotification('Gagal memuat data perusahaan', 'error');
            }
        },
        error: function() {
            showNotification('Terjadi kesalahan saat memuat data', 'error');
        }
    });
}

function viewCompanyDetails(companyId) {
    // Implementation for viewing company details in modal or separate page
    window.open('index.php?page=companies&action=details&id=' + companyId, '_blank');
}

function toggleCompanyStatus(companyId) {
    $.ajax({
        url: 'index.php?page=companies&action=toggle-status&id=' + companyId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showNotification(response.message, 'success');
                loadCompanies();
            } else {
                showNotification(response.message || 'Gagal mengubah status', 'error');
            }
        },
        error: function() {
            showNotification('Gagal mengubah status perusahaan', 'error');
        }
    });
}

function deleteCompany(companyId, companyName) {
    $('#deleteCompanyName').text(companyName);
    $('#confirmDeleteBtn').data('company-id', companyId);
    $('#deleteModal').modal('show');
}

// Global functions for easy access
window.submitFormAjax = submitFormAjax;
window.ajaxSearch = ajaxSearch;
window.ajaxPagination = ajaxPagination;
window.showNotification = showNotification;
window.confirmAction = confirmAction;
window.deleteItem = deleteItem;
