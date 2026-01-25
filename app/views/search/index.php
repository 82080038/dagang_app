<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-search me-2"></i>
                    Advanced Search
                </h1>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" id="search-analytics-btn">
                        <i class="fas fa-chart-bar me-2"></i>Analytics
                    </button>
                    <button type="button" class="btn btn-outline-success" id="search-settings-btn">
                        <i class="fas fa-cog me-2"></i>Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="total-searches"><?php echo $performance_metrics['total_searches'] ?? 0; ?></h4>
                            <p class="card-text">Total Searches</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-search fa-2x"></i>
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
                            <h4 class="card-title" id="unique-users"><?php echo $performance_metrics['unique_users'] ?? 0; ?></h4>
                            <p class="card-text">Unique Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
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
                            <h4 class="card-title" id="avg-results"><?php echo round($performance_metrics['avg_results'] ?? 0, 1); ?></h4>
                            <p class="card-text">Avg Results</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
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
                            <h4 class="card-title" id="avg-execution-time"><?php echo round($performance_metrics['avg_execution_time'] ?? 0, 1); ?>ms</h4>
                            <p class="card-text">Avg Execution</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Interface -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2"></i>
                        Search
                    </h5>
                </div>
                <div class="card-body">
                    <form id="search-form">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-lg" id="search-query" 
                                           placeholder="Search for products, customers, transactions, files..." 
                                           autocomplete="off">
                                    <div id="search-suggestions" class="search-suggestions"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="search-clear-btn">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="search-export-btn">
                                        <i class="fas fa-download me-2"></i>Export
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Filters -->
                        <div class="row mt-3" id="advanced-filters" style="display: none;">
                            <div class="col-md-3">
                                <label for="entity-types" class="form-label">Entity Types</label>
                                <select class="form-select" id="entity-types" multiple>
                                    <option value="product">Products</option>
                                    <option value="customer">Customers</option>
                                    <option value="supplier">Suppliers</option>
                                    <option value="transaction">Transactions</option>
                                    <option value="file">Files</option>
                                    <option value="member">Members</option>
                                    <option value="company">Companies</option>
                                    <option value="branch">Branches</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date-from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date-from">
                            </div>
                            <div class="col-md-3">
                                <label for="date-to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date-to">
                            </div>
                            <div class="col-md-3">
                                <label for="sort-by" class="form-label">Sort By</label>
                                <select class="form-select" id="sort-by">
                                    <option value="relevance">Relevance</option>
                                    <option value="date_desc">Date (Newest)</option>
                                    <option value="date_asc">Date (Oldest)</option>
                                    <option value="title_asc">Title (A-Z)</option>
                                    <option value="title_desc">Title (Z-A)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="toggle-filters-btn">
                                    <i class="fas fa-filter me-2"></i>Advanced Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="row mb-4" id="search-results-container" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Search Results
                        <span class="badge bg-primary ms-2" id="results-count">0</span>
                    </h5>
                    <div class="card-tools">
                        <span class="text-muted" id="search-info"></span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="search-results"></div>
                    <div id="search-pagination"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Searches -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-fire me-2"></i>
                        Popular Searches
                    </h5>
                </div>
                <div class="card-body">
                    <div id="popular-searches">
                        <?php if (!empty($popular_searches)): ?>
                            <?php foreach ($popular_searches as $search): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <a href="#" class="popular-search-link" data-query="<?php echo htmlspecialchars($search['query_text']); ?>">
                                    <?php echo htmlspecialchars($search['query_text']); ?>
                                </a>
                                <span class="badge bg-secondary"><?php echo $search['search_count']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No popular searches yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Entity Type Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div id="entity-stats">
                        <?php if (!empty($entity_stats)): ?>
                            <?php foreach ($entity_stats as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo ucfirst($stat['entity_type']); ?></span>
                                <span class="badge bg-info"><?php echo $stat['total_searches']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No entity statistics available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Performance Metrics</h6>
                        <div id="performance-metrics"></div>
                    </div>
                    <div class="col-md-6">
                        <h6>Entity Statistics</h6>
                        <div id="entity-statistics"></div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <h6>Daily Analytics</h6>
                        <div class="table-responsive">
                            <table class="table table-striped" id="analytics-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Total Searches</th>
                                        <th>Unique Users</th>
                                        <th>Avg Results</th>
                                        <th>Avg Execution Time</th>
                                        <th>No Result Queries</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="search-settings-form">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>General Settings</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="search-enabled" name="search_enabled" 
                                           <?php echo $settings['search_enabled'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="search-enabled">
                                        Enable Search
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto-indexing" name="auto_indexing" 
                                           <?php echo $settings['auto_indexing'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto-indexing">
                                        Auto Indexing
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="max-search-results" class="form-label">Max Search Results</label>
                                <input type="number" class="form-control" id="max-search-results" name="max_search_results" 
                                       value="<?php echo $settings['max_search_results']; ?>" min="10" max="1000">
                            </div>
                            <div class="mb-3">
                                <label for="search-timeout" class="form-label">Search Timeout (seconds)</label>
                                <input type="number" class="form-control" id="search-timeout" name="search_timeout_seconds" 
                                       value="<?php echo $settings['search_timeout_seconds']; ?>" min="5" max="300">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Analytics & Logging</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-analytics" name="enable_search_analytics" 
                                           <?php echo $settings['enable_search_analytics'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-analytics">
                                        Enable Analytics
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-suggestions" name="enable_search_suggestions" 
                                           <?php echo $settings['enable_search_suggestions'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-suggestions">
                                        Enable Suggestions
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-search-logging" name="enable_search_logging" 
                                           <?php echo $settings['enable_search_logging'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-search-logging">
                                        Enable Search Logging
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="cleanup-days" class="form-label">Cleanup Old Queries (days)</label>
                                <input type="number" class="form-control" id="cleanup-days" name="cleanup_old_queries_days" 
                                       value="<?php echo $settings['cleanup_old_queries_days']; ?>" min="7" max="365">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Search Features</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-fuzzy-search" name="enable_fuzzy_search" 
                                           <?php echo $settings['enable_fuzzy_search'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-fuzzy-search">
                                        Enable Fuzzy Search
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="boost-recent" name="boost_recent_content" 
                                           <?php echo $settings['boost_recent_content'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="boost-recent">
                                        Boost Recent Content
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-entity-boosting" name="enable_entity_boosting" 
                                           <?php echo $settings['enable_entity_boosting'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-entity-boosting">
                                        Enable Entity Boosting
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Performance</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable-search-caching" name="enable_search_caching" 
                                           <?php echo $settings['enable_search_caching'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable-search-caching">
                                        Enable Search Caching
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="cache-ttl" class="form-label">Cache TTL (seconds)</label>
                                <input type="number" class="form-control" id="cache-ttl" name="cache_ttl_seconds" 
                                       value="<?php echo $settings['cache_ttl_seconds']; ?>" min="60" max="3600">
                            </div>
                            <div class="mb-3">
                                <label for="index-batch-size" class="form-label">Index Batch Size</label>
                                <input type="number" class="form-control" id="index-batch-size" name="index_batch_size" 
                                       value="<?php echo $settings['index_batch_size']; ?>" min="10" max="1000">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Search Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="export-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="export-format" class="form-label">Export Format</label>
                        <select class="form-select" id="export-format" name="format">
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                            <option value="xml">XML</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="export-limit" class="form-label">Max Results</label>
                        <input type="number" class="form-control" id="export-limit" name="limit" 
                               value="1000" min="1" max="10000">
                    </div>
                    <input type="hidden" id="export-query" name="q">
                    <input type="hidden" id="export-filters" name="filters">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var SearchModule = {
    currentQuery: '',
    currentFilters: {},
    currentPage: 1,
    resultsPerPage: 20,
    
    init: function() {
        this.bindEvents();
        this.loadPopularSearches();
        this.initializeAutoComplete();
    },
    
    bindEvents: function() {
        // Search form
        $('#search-form').on('submit', this.handleSearch.bind(this));
        
        // Search input
        $('#search-query').on('input', this.handleSearchInput.bind(this));
        $('#search-query').on('focus', this.showSuggestions.bind(this));
        $(document).on('click', this.hideSuggestions.bind(this));
        
        // Clear button
        $('#search-clear-btn').on('click', this.clearSearch.bind(this));
        
        // Export button
        $('#search-export-btn').on('click', this.showExportModal.bind(this));
        
        // Toggle filters
        $('#toggle-filters-btn').on('click', this.toggleFilters.bind(this));
        
        // Popular search links
        $(document).on('click', '.popular-search-link', this.handlePopularSearch.bind(this));
        
        // Analytics button
        $('#search-analytics-btn').on('click', this.showAnalytics.bind(this));
        
        // Settings button
        $('#search-settings-btn').on('click', this.showSettings.bind(this));
        
        // Settings form
        $('#search-settings-form').on('submit', this.handleSettingsSave.bind(this));
        
        // Export form
        $('#export-form').on('submit', this.handleExport.bind(this));
    },
    
    handleSearch: function(e) {
        e.preventDefault();
        
        const query = $('#search-query').val().trim();
        if (query.length < 3) {
            Toast.error('Search query must be at least 3 characters long');
            return;
        }
        
        this.currentQuery = query;
        this.currentFilters = this.getFilters();
        this.currentPage = 1;
        
        this.performSearch();
    },
    
    handleSearchInput: function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            this.getSuggestions(query);
        } else {
            this.hideSuggestions();
        }
    },
    
    getSuggestions: function(query) {
        $.ajax({
            url: window.BASE_URL + '/index.php?page=search&action=getSuggestions',
            type: 'GET',
            data: { q: query, limit: 10 },
            success: (response) => {
                if (response.status === 'success') {
                    this.displaySuggestions(response.data);
                }
            }
        });
    },
    
    displaySuggestions: function(suggestions) {
        const container = $('#search-suggestions');
        container.empty();
        
        if (suggestions.length === 0) {
            container.hide();
            return;
        }
        
        const list = $('<ul class="list-group"></ul>');
        
        suggestions.forEach(suggestion => {
            const item = $(`
                <li class="list-group-item list-group-item-action suggestion-item" data-query="${suggestion.suggestion_text}">
                    <i class="fas fa-search me-2"></i>${suggestion.suggestion_text}
                    <small class="text-muted ms-2">(${suggestion.frequency})</small>
                </li>
            `);
            list.append(item);
        });
        
        container.html(list).show();
    },
    
    showSuggestions: function() {
        const query = $('#search-query').val().trim();
        if (query.length >= 2) {
            $('#search-suggestions').show();
        }
    },
    
    hideSuggestions: function(e) {
        if (!$(e.target).closest('#search-query, #search-suggestions').length) {
            $('#search-suggestions').hide();
        }
    },
    
    selectSuggestion: function(query) {
        $('#search-query').val(query);
        $('#search-suggestions').hide();
        this.currentQuery = query;
        this.performSearch();
    },
    
    performSearch: function() {
        const data = {
            q: this.currentQuery,
            limit: this.resultsPerPage,
            offset: (this.currentPage - 1) * this.resultsPerPage,
            sort_by: $('#sort-by').val(),
            entity_types: $('#entity-types').val() || null
        };
        
        // Add filters
        if ($('#date-from').val()) {
            data.filters = data.filters || {};
            data.filters.date_from = $('#date-from').val();
        }
        if ($('#date-to').val()) {
            data.filters = data.filters || {};
            data.filters.date_to = $('#date-to').val();
        }
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=search&action=performSearch',
            type: 'GET',
            data: data,
            beforeSend: () => {
                this.showLoading();
            },
            success: (response) => {
                if (response.status === 'success') {
                    this.displayResults(response.data);
                } else {
                    Toast.error(response.message || 'Search failed');
                }
            },
            error: () => {
                Toast.error('Search request failed');
            },
            complete: () => {
                this.hideLoading();
            }
        });
    },
    
    displayResults: function(data) {
        const container = $('#search-results-container');
        const resultsDiv = $('#search-results');
        const countBadge = $('#results-count');
        const searchInfo = $('#search-info');
        
        container.show();
        countBadge.text(data.total);
        searchInfo.text(`Found ${data.total} results in ${data.execution_time}ms`);
        
        if (data.results.length === 0) {
            resultsDiv.html(`
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>No results found</h5>
                    <p class="text-muted">Try adjusting your search terms or filters</p>
                </div>
            `);
            $('#search-pagination').empty();
            return;
        }
        
        const resultsHtml = data.results.map(result => `
            <div class="search-result-item mb-3 p-3 border rounded">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="${result.icon} fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <a href="${result.url}" class="text-decoration-none">${result.title}</a>
                            <span class="badge bg-secondary ms-2">${result.entity_type}</span>
                        </h6>
                        <p class="text-muted mb-2">${result.preview}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                ${new Date(result.created_at).toLocaleDateString()}
                            </small>
                            <small class="text-muted">
                                Relevance: ${result.relevance_score}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        resultsDiv.html(resultsHtml);
        this.displayPagination(data.pagination);
    },
    
    displayPagination: function(pagination) {
        const container = $('#search-pagination');
        
        if (pagination.total_pages <= 1) {
            container.empty();
            return;
        }
        
        let html = '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        if (pagination.has_prev) {
            html += `<li class="page-item">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
            </li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            const active = i === pagination.current_page ? 'active' : '';
            html += `<li class="page-item ${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        
        // Next button
        if (pagination.has_next) {
            html += `<li class="page-item">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
            </li>`;
        }
        
        html += '</ul></nav>';
        container.html(html);
        
        // Bind pagination events
        container.find('.page-link').on('click', (e) => {
            e.preventDefault();
            const page = $(e.target).data('page');
            this.goToPage(page);
        });
    },
    
    goToPage: function(page) {
        this.currentPage = page;
        this.performSearch();
    },
    
    clearSearch: function() {
        $('#search-query').val('');
        $('#search-results-container').hide();
        $('#search-suggestions').hide();
        this.currentQuery = '';
        this.currentFilters = {};
        this.currentPage = 1;
    },
    
    toggleFilters: function() {
        $('#advanced-filters').slideToggle();
        const btn = $('#toggle-filters-btn');
        const icon = btn.find('i');
        
        if ($('#advanced-filters').is(':visible')) {
            icon.removeClass('fa-filter').addClass('fa-filter-open');
        } else {
            icon.removeClass('fa-filter-open').addClass('fa-filter');
        }
    },
    
    getFilters: function() {
        const filters = {};
        
        if ($('#date-from').val()) {
            filters.date_from = $('#date-from').val();
        }
        if ($('#date-to').val()) {
            filters.date_to = $('#date-to').val();
        }
        
        return filters;
    },
    
    handlePopularSearch: function(e) {
        e.preventDefault();
        const query = $(e.target).data('query');
        $('#search-query').val(query);
        this.currentQuery = query;
        this.performSearch();
    },
    
    loadPopularSearches: function() {
        $.ajax({
            url: window.BASE_URL + '/index.php?page=search&action=getPopularSearches',
            type: 'GET',
            success: (response) => {
                if (response.status === 'success') {
                    this.updatePopularSearches(response.data);
                }
            }
        });
    },
    
    updatePopularSearches: function(searches) {
        const container = $('#popular-searches');
        
        if (searches.length === 0) {
            container.html('<p class="text-muted">No popular searches yet.</p>');
            return;
        }
        
        const html = searches.map(search => `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <a href="#" class="popular-search-link" data-query="${search.query_text}">
                    ${search.query_text}
                </a>
                <span class="badge bg-secondary">${search.search_count}</span>
            </div>
        `).join('');
        
        container.html(html);
    },
    
    showAnalytics: function() {
        $.ajax({
            url: window.BASE_URL + '/index.php?page=search&action=getAnalytics',
            type: 'GET',
            success: (response) => {
                if (response.status === 'success') {
                    this.displayAnalytics(response.data);
                    $('#analyticsModal').modal('show');
                }
            }
        });
    },
    
    displayAnalytics: function(data) {
        // Performance metrics
        const metrics = data.performance_metrics;
        $('#performance-metrics').html(`
            <div class="row">
                <div class="col-6">
                    <strong>Total Searches:</strong> ${metrics.total_searches}
                </div>
                <div class="col-6">
                    <strong>Unique Users:</strong> ${metrics.unique_users}
                </div>
                <div class="col-6 mt-2">
                    <strong>Avg Results:</strong> ${metrics.avg_results}
                </div>
                <div class="col-6 mt-2">
                    <strong>Avg Execution:</strong> ${metrics.avg_execution_time}ms
                </div>
            </div>
        `);
        
        // Entity statistics
        const entityStats = data.entity_stats;
        const entityHtml = entityStats.map(stat => `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>${stat.entity_type}</span>
                <span class="badge bg-info">${stat.total_searches}</span>
            </div>
        `).join('');
        $('#entity-statistics').html(entityHtml);
        
        // Analytics table
        const tbody = $('#analytics-table tbody');
        tbody.empty();
        
        data.analytics.forEach(analytic => {
            tbody.append(`
                <tr>
                    <td>${analytic.date}</td>
                    <td>${analytic.total_searches}</td>
                    <td>${analytic.unique_users}</td>
                    <td>${analytic.avg_results_per_search}</td>
                    <td>${analytic.avg_execution_time_ms}ms</td>
                    <td>${analytic.no_result_queries}</td>
                </tr>
            `);
        });
    },
    
    showSettings: function() {
        $('#settingsModal').modal('show');
    },
    
    handleSettingsSave: function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const settings = {};
        
        for (let [key, value] of formData.entries()) {
            const checkbox = e.target.querySelector(`[name="${key}"][type="checkbox"]`);
            if (checkbox) {
                settings[key] = checkbox.checked;
            } else {
                settings[key] = value;
            }
        }
        
        $.ajax({
            url: window.BASE_URL + '/index.php?page=search&action=updateSettings',
            type: 'POST',
            data: {
                settings: settings,
                csrf_token: window.CSRF_TOKEN
            },
            success: (response) => {
                if (response.status === 'success') {
                    Toast.success('Search settings updated successfully');
                    $('#settingsModal').modal('hide');
                } else {
                    Toast.error(response.message || 'Failed to update settings');
                }
            },
            error: () => {
                Toast.error('Failed to update settings');
            }
        });
    },
    
    showExportModal: function() {
        if (!this.currentQuery) {
            Toast.error('Please perform a search first');
            return;
        }
        
        $('#export-query').val(this.currentQuery);
        $('#export-filters').val(JSON.stringify(this.currentFilters));
        $('#exportModal').modal('show');
    },
    
    handleExport: function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const params = new URLSearchParams(formData);
        
        window.open(`${window.BASE_URL}/index.php?page=search&action=exportResults&${params.toString()}`, '_blank');
        $('#exportModal').modal('hide');
    },
    
    showLoading: function() {
        $('#search-results').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Searching...</span>
                </div>
                <p class="mt-2">Searching...</p>
            </div>
        `);
    },
    
    hideLoading: function() {
        // Loading will be replaced by results
    },
    
    initializeAutoComplete: function() {
        $(document).on('click', '.suggestion-item', (e) => {
            const query = $(e.currentTarget).data('query');
            this.selectSuggestion(query);
        });
    }
};

// Initialize on document ready
$(document).ready(function() {
    SearchModule.init();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
