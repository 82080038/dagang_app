@extends('layouts.main')

@section('title', 'Advanced Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Advanced Reports</h1>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                        <i class="fas fa-plus"></i> Generate Report
                    </button>
                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="refreshReports()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="totalReports">0</h4>
                            <p class="card-text">Total Reports</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="completedReports">0</h4>
                            <p class="card-text">Completed</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="processingReports">0</h4>
                            <p class="card-text">Processing</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-spinner fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="avgAccuracy">0%</h4>
                            <p class="card-text">Avg Accuracy</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Models Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">AI Models Status</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="aiModelsStatus">
                        <!-- AI models will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">Recent Reports</h5>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm" id="reportTypeFilter" style="width: 150px;">
                                    <option value="">All Types</option>
                                    <option value="sales">Sales</option>
                                    <option value="inventory">Inventory</option>
                                    <option value="customer">Customer</option>
                                    <option value="financial">Financial</option>
                                    <option value="performance">Performance</option>
                                </select>
                                <select class="form-select form-select-sm" id="statusFilter" style="width: 120px;">
                                    <option value="">All Status</option>
                                    <option value="completed">Completed</option>
                                    <option value="processing">Processing</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="reportsTable">
                            <thead>
                                <tr>
                                    <th>Report ID</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Date Range</th>
                                    <th>AI Model</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                <!-- Reports will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted" id="reportsInfo">
                            Showing 0 to 0 of 0 entries
                        </div>
                        <div class="btn-group" id="reportsPagination">
                            <!-- Pagination will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Report Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate AI Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="generateReportForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reportType" class="form-label">Report Type</label>
                                <select class="form-select" id="reportType" name="report_type" required>
                                    <option value="">Select Report Type</option>
                                    <option value="sales">Sales Analysis</option>
                                    <option value="inventory">Inventory Analysis</option>
                                    <option value="customer">Customer Analysis</option>
                                    <option value="financial">Financial Analysis</option>
                                    <option value="performance">Performance Analysis</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateRange" class="form-label">Date Range</label>
                                <select class="form-select" id="dateRange" name="date_range" required>
                                    <option value="1d">Last 24 Hours</option>
                                    <option value="7d" selected>Last 7 Days</option>
                                    <option value="30d">Last 30 Days</option>
                                    <option value="90d">Last 90 Days</option>
                                    <option value="1y">Last Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="customDateRange" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="end_date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="aiModel" class="form-label">AI Model</label>
                                <select class="form-select" id="aiModel" name="ai_model">
                                    <option value="standard">Standard Analysis</option>
                                    <option value="sales_forecasting">Sales Forecasting</option>
                                    <option value="inventory_optimization">Inventory Optimization</option>
                                    <option value="customer_segmentation">Customer Segmentation</option>
                                    <option value="price_optimization">Price Optimization</option>
                                    <option value="anomaly_detection">Anomaly Detection</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reportTitle" class="form-label">Report Title</label>
                                <input type="text" class="form-control" id="reportTitle" name="report_title" placeholder="Enter report title">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reportDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="reportDescription" name="report_description" rows="3" placeholder="Enter report description"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="companyFilter" class="form-label">Company</label>
                                <select class="form-select" id="companyFilter" name="company_id">
                                    <option value="">All Companies</option>
                                    <!-- Companies will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branchFilter" class="form-label">Branch</label>
                                <select class="form-select" id="branchFilter" name="branch_id">
                                    <option value="">All Branches</option>
                                    <!-- Branches will be loaded here -->
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="generateReport()">Generate Report</button>
            </div>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div class="modal fade" id="reportDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reportDetailsContent">
                    <!-- Report details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="exportReportBtn">Export</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Advanced Reports Module
var AdvancedReportsModule = {
    currentPage: 1,
    currentFilters: {},
    
    init: function() {
        this.bindEvents();
        this.loadReports();
        this.loadStatistics();
        this.loadAIModels();
    },
    
    bindEvents: function() {
        $('#generateReportForm').on('submit', function(e) {
            e.preventDefault();
            AdvancedReportsModule.generateReport();
        });
        
        $('#reportTypeFilter').on('change', function() {
            AdvancedReportsModule.currentFilters.report_type = $(this).val();
            AdvancedReportsModule.loadReports();
        });
        
        $('#statusFilter').on('change', function() {
            AdvancedReportsModule.currentFilters.status = $(this).val();
            AdvancedReportsModule.loadReports();
        });
        
        $('#dateRange').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#customDateRange').show();
            } else {
                $('#customDateRange').hide();
            }
        });
        
        $('#reportType').on('change', function() {
            AdvancedReportsModule.updateAIModelOptions($(this).val());
        });
    },
    
    loadReports: function() {
        var params = {
            page: this.currentPage,
            ...this.currentFilters
        };
        
        $.ajax({
            url: 'index.php?page=advanced_reports&action=index',
            type: 'GET',
            data: params,
            success: function(response) {
                // Handle reports data
                console.log('Reports loaded:', response);
            },
            error: function(xhr, status, error) {
                Toast.error('Failed to load reports: ' + error);
            }
        });
    },
    
    loadStatistics: function() {
        $.ajax({
            url: 'index.php?page=advanced_reports&action=statistics',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#totalReports').text(response.data.total_reports || 0);
                    $('#completedReports').text(response.data.completed_reports || 0);
                    $('#processingReports').text(response.data.processing_reports || 0);
                    $('#avgAccuracy').text((response.data.avg_accuracy || 0) + '%');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load statistics:', error);
            }
        });
    },
    
    loadAIModels: function() {
        $.ajax({
            url: 'index.php?page=advanced_reports&action=ai_models',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    AdvancedReportsModule.renderAIModels(response.data.models);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load AI models:', error);
            }
        });
    },
    
    renderAIModels: function(models) {
        var container = $('#aiModelsStatus');
        container.empty();
        
        models.forEach(function(model) {
            var statusClass = model.status === 'trained' ? 'success' : 'warning';
            var modelCard = `
                <div class="col-md-4 mb-3">
                    <div class="card border-${statusClass}">
                        <div class="card-body">
                            <h6 class="card-title">${model.name}</h6>
                            <p class="card-text small">${model.description}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-${statusClass}">${model.status}</span>
                                <small>Accuracy: ${(model.accuracy * 100).toFixed(1)}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(modelCard);
        });
    },
    
    updateAIModelOptions: function(reportType) {
        var aiModelSelect = $('#aiModel');
        aiModelSelect.empty();
        
        var options = {
            'sales': [
                { value: 'sales_forecasting', text: 'Sales Forecasting' },
                { value: 'anomaly_detection', text: 'Anomaly Detection' }
            ],
            'inventory': [
                { value: 'inventory_optimization', text: 'Inventory Optimization' },
                { value: 'anomaly_detection', text: 'Anomaly Detection' }
            ],
            'customer': [
                { value: 'customer_segmentation', text: 'Customer Segmentation' },
                { value: 'price_optimization', text: 'Price Optimization' }
            ],
            'financial': [
                { value: 'standard', text: 'Standard Analysis' },
                { value: 'anomaly_detection', text: 'Anomaly Detection' }
            ],
            'performance': [
                { value: 'standard', text: 'Standard Analysis' },
                { value: 'anomaly_detection', text: 'Anomaly Detection' }
            ]
        };
        
        var defaultOptions = [
            { value: 'standard', text: 'Standard Analysis' }
        ];
        
        var modelOptions = options[reportType] || defaultOptions;
        
        modelOptions.forEach(function(option) {
            aiModelSelect.append(`<option value="${option.value}">${option.text}</option>`);
        });
    },
    
    generateReport: function() {
        var formData = $('#generateReportForm').serialize();
        
        $.ajax({
            url: 'index.php?page=advanced_reports&action=generateAIReport',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    Toast.success('Report generation started successfully!');
                    $('#generateReportModal').modal('hide');
                    AdvancedReportsModule.loadReports();
                } else {
                    Toast.error(response.message || 'Failed to generate report');
                }
            },
            error: function(xhr, status, error) {
                Toast.error('Failed to generate report: ' + error);
            }
        });
    }
};

// Initialize module when page loads
$(document).ready(function() {
    AdvancedReportsModule.init();
});

// Global functions
function refreshReports() {
    AdvancedReportsModule.loadReports();
    AdvancedReportsModule.loadStatistics();
    AdvancedReportsModule.loadAIModels();
}

function generateReport() {
    AdvancedReportsModule.generateReport();
}
</script>
@endpush
