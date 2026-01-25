/**
 * Search Module
 * 
 * Handles search functionality with auto-complete, filters, and result display
 */

var SearchModule = {
    init: function() {
        this.bindEvents();
        this.initializeSearch();
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
    
    initializeSearch: function() {
        this.loadPopularSearches();
        this.resultsPerPage = 20;
        this.currentPage = 1;
        this.currentQuery = '';
        this.currentFilters = {};
    }
};

// Initialize on document ready
$(document).ready(function() {
    SearchModule.init();
});
