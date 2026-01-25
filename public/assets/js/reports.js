/**
 * Reports Module - Advanced Reporting System
 * Handles all report generation, filtering, and visualization
 */

var ReportsModule = {
    charts: {
        mainChart: null,
        pieChart: null
    },
    currentFilters: {},
    currentReportType: null,
    
    init: function() {
        this.bindEvents();
        this.loadInitialData();
        this.initializeCharts();
    },
    
    bindEvents: function() {
        // Filter form submission
        $('#report-filters-form').on('submit', function(e) {
            e.preventDefault();
            ReportsModule.generateReport();
        });
        
        // Export modal
        $('#export-form').on('submit', function(e) {
            e.preventDefault();
            ReportsModule.handleExport();
        });
        
        // Filter changes
        $('#report-type').on('change', function() {
            ReportsModule.updateFilterDisplay();
        });
        
        $('#start-date, #end-date').on('change', function() {
            ReportsModule.updateFilterDisplay();
        });
        
        $('#company-filter, #branch-filter').on('change', function() {
            ReportsModule.updateFilterDisplay();
        });
        
        // Table entries
        $('#table-entries').on('change', function() {
            ReportsModule.updateTableDisplay();
        });
        
        // Legacy refresh button
        $('#refreshReportsBtn').on('click', function() {
            ReportsModule.loadInitialData();
        });
    },
    
    loadInitialData: function() {
        // Load legacy API data for compatibility
        this.loadLegacyReports();
        
        // Load current statistics
        this.loadCurrentStatistics();
    },
    
    loadLegacyReports: function() {
        var self = this;
        
        function setState(loading, error, empty) {
            $('#reportsLoading').toggleClass('d-none', !loading);
            $('#reportsError').toggleClass('d-none', !error);
            $('#reportsEmpty').toggleClass('d-none', !empty);
            $('#reportsContent').toggleClass('d-none', loading || error || empty);
        }
        
        function formatCurrency(n) {
            const num = parseFloat(n || 0);
            return 'Rp ' + num.toLocaleString('id-ID', {maximumFractionDigits: 0});
        }
        
        setState(true, false, false);
        
        $.ajax({
            url: BASE_URL + '/index.php?page=reports&action=api',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status !== 'success') {
                    setState(false, true, false);
                    return;
                }
                
                const data = response.data || {};
                const today = data.today || {total: 0, count: 0};
                const last7 = Array.isArray(data.last7) ? data.last7 : [];
                const topProducts = Array.isArray(data.topProducts) ? data.topProducts : [];
                const lowStock = data.lowStock || 0;
                
                const isEmpty = (parseFloat(today.total) === 0 && parseInt(today.count) === 0 && 
                               lowStock === 0 && last7.length === 0 && topProducts.length === 0);
                
                if (isEmpty) {
                    setState(false, false, true);
                    return;
                }
                
                // Update summary cards
                $('#todayTotal').text(formatCurrency(today.total));
                $('#todayCount').text(today.count);
                $('#lowStockCount').text(lowStock);
                
                // Update stats cards
                $('#total-transactions').text(today.count || 0);
                $('#total-revenue').text(formatCurrency(today.total));
                $('#avg-transaction').text(formatCurrency(today.count > 0 ? today.total / today.count : 0));
                $('#low-stock-count').text(lowStock);
                
                // Populate last7 table
                const last7Tbody = $('#last7TableBody');
                last7Tbody.empty();
                last7.forEach(function(row) {
                    const dStr = new Date(row.d).toLocaleDateString('id-ID', {
                        day: '2-digit', month: 'short', year: 'numeric'
                    });
                    const tr = $('<tr>')
                        .append('<td>' + dStr + '</td>')
                        .append('<td class="text-end">' + formatCurrency(row.total) + '</td>')
                        .append('<td class="text-end">' + (row.count || 0) + '</td>');
                    last7Tbody.append(tr);
                });
                
                // Populate top products
                const topTbody = $('#topProductsTableBody');
                topTbody.empty();
                topProducts.forEach(function(p) {
                    const tr = $('<tr>')
                        .append('<td>' + (p.product_name || '-') + '</td>')
                        .append('<td class="text-end">' + parseFloat(p.qty || 0).toLocaleString('id-ID') + '</td>')
                        .append('<td class="text-end">' + formatCurrency(p.revenue) + '</td>');
                    topTbody.append(tr);
                });
                
                setState(false, false, false);
                
                // Show charts section with data
                self.updateChartsWithData(data);
            },
            error: function() {
                setState(false, true, false);
            }
        });
    },
    
    loadCurrentStatistics: function() {
        // Already loaded in legacy reports
    },
    
    generateReport: function() {
        var reportType = $('#report-type').val();
        var startDate = $('#start-date').val();
        var endDate = $('#end-date').val();
        var companyId = $('#company-filter').val();
        var branchId = $('#branch-filter').val();
        
        if (!reportType) {
            this.showToast('Silakan pilih tipe laporan', 'warning');
            return;
        }
        
        // Store current filters
        this.currentFilters = {
            report_type: reportType,
            start_date: startDate,
            end_date: endDate,
            company_id: companyId,
            branch_id: branchId
        };
        
        this.currentReportType = reportType;
        
        // Show loading
        this.showLoading(true);
        
        // Build API URL based on report type
        var apiUrl = this.buildApiUrl(reportType);
        
        $.ajax({
            url: apiUrl,
            type: 'GET',
            data: this.currentFilters,
            dataType: 'json',
            success: function(response) {
                ReportsModule.handleReportResponse(response, reportType);
            },
            error: function(xhr, status, error) {
                ReportsModule.showLoading(false);
                ReportsModule.showToast('Gagal memuat laporan: ' + error, 'error');
            }
        });
    },
    
    buildApiUrl: function(reportType) {
        var baseUrl = BASE_URL + '/index.php?page=reports&action=';
        
        switch(reportType) {
            case 'sales_summary':
            case 'sales_daily':
            case 'sales_weekly':
            case 'sales_monthly':
            case 'sales_by_branch':
            case 'sales_top_products':
                return baseUrl + 'salesReport';
            case 'inventory_stock_levels':
            case 'inventory_valuation':
            case 'inventory_movements':
                return baseUrl + 'inventoryReport';
            case 'financial_profit_loss':
            case 'financial_cash_flow':
                return baseUrl + 'financialReport';
            case 'branch_comparison':
            case 'branch_trends':
                return baseUrl + 'branchPerformanceReport';
            default:
                return baseUrl + 'salesReport';
        }
    },
    
    handleReportResponse: function(response, reportType) {
        this.showLoading(false);
        
        if (response.status !== 'success') {
            this.showToast(response.message || 'Gagal memuat laporan', 'error');
            return;
        }
        
        var data = response.data || [];
        
        if (Array.isArray(data) && data.length === 0) {
            this.showToast('Tidak ada data untuk filter yang dipilih', 'info');
            this.clearReportTable();
            return;
        }
        
        // Update charts
        this.updateCharts(data, reportType);
        
        // Update table
        this.updateReportTable(data, reportType);
        
        // Update statistics
        this.updateStatistics(data, reportType);
        
        // Show filter section
        this.showFilterSection();
        
        this.showToast('Laporan berhasil dimuat', 'success');
    },
    
    initializeCharts: function() {
        // Initialize main chart
        var mainCtx = document.getElementById('main-chart');
        if (mainCtx) {
            this.charts.mainChart = new Chart(mainCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Revenue',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Initialize pie chart
        var pieCtx = document.getElementById('pie-chart');
        if (pieCtx) {
            this.charts.pieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    },
    
    updateCharts: function(data, reportType) {
        if (!this.charts.mainChart || !this.charts.pieChart) {
            return;
        }
        
        var chartData = this.prepareChartData(data, reportType);
        
        // Update main chart
        this.charts.mainChart.data.labels = chartData.labels;
        this.charts.mainChart.data.datasets[0].data = chartData.values;
        this.charts.mainChart.data.datasets[0].label = chartData.label;
        this.charts.mainChart.update();
        
        // Update pie chart
        this.charts.pieChart.data.labels = chartData.pieLabels;
        this.charts.pieChart.data.datasets[0].data = chartData.pieValues;
        this.charts.pieChart.update();
    },
    
    prepareChartData: function(data, reportType) {
        var labels = [];
        var values = [];
        var pieLabels = [];
        var pieValues = [];
        var label = 'Data';
        
        switch(reportType) {
            case 'sales_daily':
                label = 'Penjualan Harian';
                data.forEach(function(item) {
                    labels.push(new Date(item.date).toLocaleDateString('id-ID'));
                    values.push(item.revenue || item.total_revenue || 0);
                });
                break;
                
            case 'sales_weekly':
                label = 'Penjualan Mingguan';
                data.forEach(function(item) {
                    labels.push('Minggu ' + item.week);
                    values.push(item.revenue || 0);
                });
                break;
                
            case 'sales_monthly':
                label = 'Penjualan Bulanan';
                data.forEach(function(item) {
                    labels.push(item.month_name);
                    values.push(item.revenue || 0);
                });
                break;
                
            case 'sales_by_branch':
                label = 'Penjualan per Cabang';
                data.forEach(function(item) {
                    labels.push(item.branch_name);
                    values.push(item.revenue || 0);
                    pieLabels.push(item.branch_name);
                    pieValues.push(item.revenue || 0);
                });
                break;
                
            case 'sales_top_products':
                label = 'Produk Terlaris';
                data.forEach(function(item) {
                    labels.push(item.product_name);
                    values.push(item.revenue || 0);
                });
                break;
                
            default:
                label = 'Data Laporan';
                data.forEach(function(item) {
                    labels.push(item.label || item.name || 'Data');
                    values.push(item.value || item.amount || 0);
                });
        }
        
        return {
            labels: labels,
            values: values,
            pieLabels: pieLabels.length > 0 ? pieLabels : labels.slice(0, 5),
            pieValues: pieValues.length > 0 ? pieValues : values.slice(0, 5),
            label: label
        };
    },
    
    updateChartsWithData: function(data) {
        // Update charts with legacy data
        if (data.last7 && this.charts.mainChart) {
            var labels = [];
            var values = [];
            
            data.last7.forEach(function(item) {
                labels.push(new Date(item.d).toLocaleDateString('id-ID', { 
                    day: '2-digit', month: 'short' 
                }));
                values.push(item.total);
            });
            
            this.charts.mainChart.data.labels = labels;
            this.charts.mainChart.data.datasets[0].data = values;
            this.charts.mainChart.update();
        }
    },
    
    updateReportTable: function(data, reportType) {
        var tbody = $('#report-table-body');
        tbody.empty();
        
        if (!Array.isArray(data) || data.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Tidak ada data</td></tr>');
            return;
        }
        
        data.forEach(function(item) {
            var row = ReportsModule.buildTableRow(item, reportType);
            tbody.append(row);
        });
    },
    
    buildTableRow: function(item, reportType) {
        var row = $('<tr>');
        
        switch(reportType) {
            case 'sales_daily':
                row.append('<td>' + new Date(item.date).toLocaleDateString('id-ID') + '</td>');
                row.append('<td>Penjualan</td>');
                row.append('<td>' + (item.transactions || 0) + '</td>');
                row.append('<td class="text-end">' + this.formatCurrency(item.revenue || 0) + '</td>');
                row.append('<td><span class="badge bg-success">Selesai</span></td>');
                break;
                
            case 'sales_by_branch':
                row.append('<td>-</td>');
                row.append('<td>' + item.branch_name + '</td>');
                row.append('<td>' + (item.transactions || 0) + '</td>');
                row.append('<td class="text-end">' + this.formatCurrency(item.revenue || 0) + '</td>');
                row.append('<td><span class="badge bg-info">Aktif</span></td>');
                break;
                
            case 'inventory_stock_levels':
                row.append('<td>-</td>');
                row.append('<td>' + item.product_name + '</td>');
                row.append('<td>' + item.current_stock + '</td>');
                row.append('<td class="text-end">' + this.formatCurrency(item.total_value || 0) + '</td>');
                row.append('<td><span class="badge bg-' + this.getStockStatusColor(item.stock_status) + '">' + item.stock_status + '</span></td>');
                break;
                
            default:
                row.append('<td>' + (item.date || item.created_at || '-') + '</td>');
                row.append('<td>' + (item.type || item.category || '-') + '</td>');
                row.append('<td>' + (item.quantity || item.count || 0) + '</td>');
                row.append('<td class="text-end">' + this.formatCurrency(item.amount || item.total || 0) + '</td>');
                row.append('<td><span class="badge bg-secondary">-</span></td>');
        }
        
        return row;
    },
    
    getStockStatusColor: function(status) {
        switch(status) {
            case 'Low Stock': return 'warning';
            case 'Out of Stock': return 'danger';
            case 'Overstock': return 'info';
            default: return 'success';
        }
    },
    
    updateStatistics: function(data, reportType) {
        if (!Array.isArray(data) || data.length === 0) {
            return;
        }
        
        var totalTransactions = 0;
        var totalRevenue = 0;
        var avgTransaction = 0;
        
        switch(reportType) {
            case 'sales_daily':
            case 'sales_weekly':
            case 'sales_monthly':
                data.forEach(function(item) {
                    totalTransactions += item.transactions || 0;
                    totalRevenue += item.revenue || 0;
                });
                break;
                
            case 'sales_by_branch':
                data.forEach(function(item) {
                    totalTransactions += item.transactions || 0;
                    totalRevenue += item.revenue || 0;
                });
                break;
                
            default:
                // Use first item for other types
                if (data[0]) {
                    totalTransactions = data[0].transactions || data[0].count || 0;
                    totalRevenue = data[0].revenue || data[0].amount || 0;
                }
        }
        
        avgTransaction = totalTransactions > 0 ? totalRevenue / totalTransactions : 0;
        
        $('#total-transactions').text(totalTransactions.toLocaleString('id-ID'));
        $('#total-revenue').text(this.formatCurrency(totalRevenue));
        $('#avg-transaction').text(this.formatCurrency(avgTransaction));
    },
    
    clearReportTable: function() {
        $('#report-table-body').empty()
            .append('<tr><td colspan="6" class="text-center text-muted">Tidak ada data untuk filter yang dipilih</td></tr>');
    },
    
    showLoading: function(show) {
        $('#loading-spinner').toggle(show);
        $('#report-content').toggle(!show);
    },
    
    showFilterSection: function() {
        $('#filter-section').removeClass('d-none');
        this.updateFilterDisplay();
    },
    
    updateFilterDisplay: function() {
        var reportType = $('#report-type').val();
        var startDate = $('#start-date').val();
        var endDate = $('#end-date').val();
        var companyId = $('#company-filter').val();
        var branchId = $('#branch-filter').val();
        
        // Update report type badge
        if (reportType) {
            var reportText = $('#report-type option:selected').text();
            $('#filter-badge-report-type').text(reportText).show();
        } else {
            $('#filter-badge-report-type').hide();
        }
        
        // Update date range badge
        if (startDate && endDate) {
            $('#filter-badge-date-range').text(startDate + ' - ' + endDate).show();
        } else {
            $('#filter-badge-date-range').hide();
        }
        
        // Update company badge
        if (companyId) {
            var companyText = $('#company-filter option:selected').text();
            $('#filter-badge-company').text(companyText).show();
        } else {
            $('#filter-badge-company').hide();
        }
        
        // Update branch badge
        if (branchId) {
            var branchText = $('#branch-filter option:selected').text();
            $('#filter-badge-branch').text(branchText).show();
        } else {
            $('#filter-badge-branch').hide();
        }
    },
    
    clearFilters: function() {
        $('#report-filters-form')[0].reset();
        $('#filter-section').addClass('d-none');
        this.clearReportTable();
        this.loadInitialData();
    },
    
    resetFilters: function() {
        this.clearFilters();
    },
    
    refreshReports: function() {
        this.loadInitialData();
        if (this.currentReportType) {
            this.generateReport();
        }
    },
    
    exportReport: function() {
        $('#exportModal').modal('show');
        
        // Set current filters in export form
        if (this.currentReportType) {
            $('#export-report-type').val(this.currentReportType);
            $('#export-filters').val(JSON.stringify(this.currentFilters));
        }
    },
    
    handleExport: function() {
        var format = $('#export-format').val();
        var reportType = $('#export-report-type').val();
        var filters = $('#export-filters').val();
        
        // Create form and submit
        var form = $('<form>', {
            method: 'POST',
            action: BASE_URL + '/index.php?page=reports&action=exportReport'
        });
        
        form.append($('<input>', {type: 'hidden', name: 'format', value: format}));
        form.append($('<input>', {type: 'hidden', name: 'report_type', value: reportType}));
        form.append($('<input>', {type: 'hidden', name: 'filters', value: filters}));
        
        $('body').append(form);
        form.submit();
        form.remove();
        
        $('#exportModal').modal('hide');
        this.showToast('Export sedang diproses...', 'info');
    },
    
    updateTableDisplay: function() {
        // Placeholder for table display updates
    },
    
    formatCurrency: function(amount) {
        const num = parseFloat(amount || 0);
        return 'Rp ' + num.toLocaleString('id-ID', {maximumFractionDigits: 0});
    },
    
    showToast: function(message, type) {
        // Create toast notification
        var toastHtml = '<div class="toast align-items-center text-white bg-' + type + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '</div>';
        
        var toastElement = $(toastHtml);
        $('.toast-container').append(toastElement);
        
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
        
        // Remove toast after hidden
        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
};

// Auto-initialize when document is ready
$(document).ready(function() {
    ReportsModule.init();
});
