<?php
// Include necessary files
require_once __DIR__ . '/../../app/config/bootstrap.php';
require_once __DIR__ . '/../../app/core/ApiDocumentation.php';

// Initialize API documentation
$apiDoc = new ApiDocumentation();

// Handle different requests
$action = $_GET['action'] ?? 'view';

switch ($action) {
    case 'json':
        header('Content-Type: application/json');
        echo $apiDoc->exportAsJSON();
        break;
        
    case 'yaml':
        header('Content-Type: application/x-yaml');
        echo $apiDoc->exportAsYAML();
        break;
        
    case 'stats':
        header('Content-Type: application/json');
        echo json_encode($apiDoc->getAPIStats(), JSON_PRETTY_PRINT);
        break;
        
    case 'examples':
        header('Content-Type: application/json');
        echo json_encode($apiDoc->generateCodeExamples(), JSON_PRETTY_PRINT);
        break;
        
    case 'view':
    default:
        // Serve interactive API documentation page
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>API Documentation - Perdagangan System</title>
            
            <!-- Bootstrap 5 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Bootstrap Icons -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
            <!-- Prism.js for code highlighting -->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
            
            <style>
                .api-sidebar {
                    min-height: calc(100vh - 56px);
                    background: #f8f9fa;
                    border-right: 1px solid #dee2e6;
                }
                
                .endpoint-card {
                    border-left: 4px solid #6c757d;
                    transition: all 0.3s ease;
                }
                
                .endpoint-card.get { border-left-color: #198754; }
                .endpoint-card.post { border-left-color: #0dcaf0; }
                .endpoint-card.put { border-left-color: #ffc107; }
                .endpoint-card.delete { border-left-color: #dc3545; }
                
                .endpoint-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                
                .method-badge {
                    font-size: 0.75rem;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                
                .code-container {
                    background: #2d3748;
                    border-radius: 8px;
                    overflow: hidden;
                }
                
                .nav-pills .nav-link.active {
                    background-color: #0d6efd;
                }
                
                .schema-tree {
                    font-family: 'Courier New', monospace;
                    font-size: 0.875rem;
                }
                
                .schema-property {
                    margin-left: 20px;
                    position: relative;
                }
                
                .schema-property::before {
                    content: "├─";
                    position: absolute;
                    left: -15px;
                    color: #6c757d;
                }
                
                .schema-property:last-child::before {
                    content: "└─";
                }
                
                .required-field {
                    color: #dc3545;
                    font-weight: bold;
                }
                
                .optional-field {
                    color: #6c757d;
                }
                
                .stats-card {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }
                
                .loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    display: none;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                }
            </style>
        </head>
        <body>
            <!-- Navigation -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="../">
                        <i class="bi bi-box-seam me-2"></i>
                        Perdagangan System
                    </a>
                    <div class="navbar-nav ms-auto">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-download me-1"></i>
                                Export
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?action=json" target="_blank">
                                    <i class="bi bi-filetype-json me-2"></i>OpenAPI JSON
                                </a></li>
                                <li><a class="dropdown-item" href="?action=yaml" target="_blank">
                                    <i class="bi bi-filetype-yml me-2"></i>OpenAPI YAML
                                </a></li>
                            </ul>
                        </div>
                        <a class="nav-link" href="#" onclick="toggleTheme()">
                            <i class="bi bi-moon-stars" id="theme-icon"></i>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="row">
                    <!-- Sidebar -->
                    <div class="col-md-3 col-lg-2 api-sidebar p-0">
                        <div class="p-3">
                            <h6 class="text-muted mb-3">API ENDPOINTS</h6>
                            <nav class="nav nav-pills flex-column" id="api-nav">
                                <!-- Navigation will be populated by JavaScript -->
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-md-9 col-lg-10">
                        <div class="p-4">
                            <!-- Header -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <h1 class="h2 mb-3">API Documentation</h1>
                                    <p class="text-muted">
                                        Complete REST API documentation for Perdagangan System. 
                                        Interactive explorer with code examples and testing capabilities.
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <div class="stats-card p-3 rounded">
                                        <h6 class="mb-2">API Statistics</h6>
                                        <div id="api-stats">
                                            <div class="spinner-border spinner-border-sm text-light" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Bar -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="search-input" 
                                               placeholder="Search endpoints...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary" onclick="filterByMethod('all')">
                                            All
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="filterByMethod('get')">
                                            GET
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="filterByMethod('post')">
                                            POST
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="filterByMethod('put')">
                                            PUT
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="filterByMethod('delete')">
                                            DELETE
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- API Content -->
                            <div id="api-content">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading API documentation...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Loading API documentation...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loading-overlay">
                <div class="text-center text-white">
                    <div class="spinner-border mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Processing request...</p>
                </div>
            </div>

            <!-- Test Endpoint Modal -->
            <div class="modal fade" id="testModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Test API Endpoint</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Endpoint URL</label>
                                <input type="text" class="form-control" id="test-url" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Method</label>
                                <input type="text" class="form-control" id="test-method" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Headers</label>
                                <textarea class="form-control" id="test-headers" rows="3" placeholder='{"Authorization": "Bearer YOUR_TOKEN"}'></textarea>
                            </div>
                            <div class="mb-3" id="request-body-group">
                                <label class="form-label">Request Body</label>
                                <textarea class="form-control" id="test-body" rows="5" placeholder='{"key": "value"}'></textarea>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="sendTestRequest()">
                                <i class="bi bi-send me-1"></i>Send Request
                            </button>
                        </div>
                        <div class="modal-footer">
                            <div class="w-100">
                                <h6>Response</h6>
                                <div id="test-response" class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background: #f8f9fa;">
                                    <em class="text-muted">Response will appear here...</em>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
            
            <script>
                // Global variables
                let apiSpec = null;
                let currentFilter = 'all';
                let currentSearch = '';

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', function() {
                    loadAPIDocumentation();
                    loadAPIStats();
                    setupEventListeners();
                });

                // Load API documentation
                async function loadAPIDocumentation() {
                    try {
                        const response = await fetch('?action=json');
                        apiSpec = await response.json();
                        renderAPIDocumentation();
                        renderNavigation();
                    } catch (error) {
                        console.error('Error loading API documentation:', error);
                        document.getElementById('api-content').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Failed to load API documentation. Please try again later.
                            </div>
                        `;
                    }
                }

                // Load API statistics
                async function loadAPIStats() {
                    try {
                        const response = await fetch('?action=stats');
                        const stats = await response.json();
                        renderStats(stats);
                    } catch (error) {
                        console.error('Error loading API stats:', error);
                    }
                }

                // Render statistics
                function renderStats(stats) {
                    const statsHtml = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="h4 mb-0">${stats.total_endpoints}</div>
                                <small>Endpoints</small>
                            </div>
                            <div>
                                <div class="h4 mb-0">${stats.total_schemas}</div>
                                <small>Schemas</small>
                            </div>
                            <div>
                                <div class="h4 mb-0">${stats.authentication_required}</div>
                                <small>Secured</small>
                            </div>
                        </div>
                    `;
                    document.getElementById('api-stats').innerHTML = statsHtml;
                }

                // Render navigation
                function renderNavigation() {
                    const nav = document.getElementById('api-nav');
                    const tags = {};
                    
                    // Group endpoints by tags
                    Object.keys(apiSpec.paths).forEach(path => {
                        Object.keys(apiSpec.paths[path]).forEach(method => {
                            const endpoint = apiSpec.paths[path][method];
                            if (endpoint.tags) {
                                endpoint.tags.forEach(tag => {
                                    if (!tags[tag]) tags[tag] = [];
                                    tags[tag].push({ path, method, endpoint });
                                });
                            }
                        });
                    });

                    // Render navigation
                    let navHtml = '';
                    Object.keys(tags).forEach(tag => {
                        navHtml += `
                            <h6 class="text-muted mt-3 mb-2">${tag}</h6>
                            ${tags[tag].map(item => `
                                <a class="nav-link" href="#" onclick="scrollToEndpoint('${item.path}', '${item.method}')">
                                    <span class="method-badge badge bg-${getMethodColor(item.method)} me-2">${item.method}</span>
                                    ${item.path}
                                </a>
                            `).join('')}
                        `;
                    });

                    nav.innerHTML = navHtml;
                }

                // Render API documentation
                function renderAPIDocumentation() {
                    const content = document.getElementById('api-content');
                    let html = '';

                    Object.keys(apiSpec.paths).forEach(path => {
                        Object.keys(apiSpec.paths[path]).forEach(method => {
                            const endpoint = apiSpec.paths[path][method];
                            html += renderEndpoint(path, method, endpoint);
                        });
                    });

                    content.innerHTML = html;
                    
                    // Apply filters
                    applyFilters();
                    
                    // Highlight code
                    Prism.highlightAll();
                }

                // Render individual endpoint
                function renderEndpoint(path, method, endpoint) {
                    const methodColor = getMethodColor(method);
                    const hasRequestBody = endpoint.requestBody && endpoint.requestBody.required;
                    
                    return `
                        <div class="endpoint-card ${method} mb-4 p-4 bg-white rounded shadow-sm" 
                             data-path="${path}" data-method="${method}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <span class="method-badge badge bg-${methodColor} me-2">${method}</span>
                                        ${path}
                                    </h5>
                                    <p class="text-muted mb-2">${endpoint.summary || endpoint.description || 'No description available'}</p>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="openTestModal('${path}', '${method}', ${hasRequestBody})">
                                        <i class="bi bi-play-circle me-1"></i>Test
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary ms-1" onclick="showCodeExamples('${path}', '${method}')">
                                        <i class="bi bi-code-slash"></i>
                                    </button>
                                </div>
                            </div>

                            ${endpoint.description ? `<p class="mb-3">${endpoint.description}</p>` : ''}

                            <!-- Parameters -->
                            ${endpoint.parameters ? renderParameters(endpoint.parameters) : ''}

                            <!-- Request Body -->
                            ${endpoint.requestBody ? renderRequestBody(endpoint.requestBody) : ''}

                            <!-- Responses -->
                            ${renderResponses(endpoint.responses)}

                            <!-- Security -->
                            ${endpoint.security ? renderSecurity(endpoint.security) : ''}
                        </div>
                    `;
                }

                // Render parameters
                function renderParameters(parameters) {
                    return `
                        <div class="mb-3">
                            <h6>Parameters</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>In</th>
                                            <th>Type</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${parameters.map(param => `
                                            <tr>
                                                <td><code>${param.name}</code></td>
                                                <td><span class="badge bg-secondary">${param.in}</span></td>
                                                <td>${param.schema?.type || 'N/A'}</td>
                                                <td>${param.required ? '<span class="badge bg-danger">Required</span>' : 'Optional'}</td>
                                                <td>${param.description || '-'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                // Render request body
                function renderRequestBody(requestBody) {
                    const content = requestBody.content;
                    const jsonContent = content && content['application/json'];
                    
                    return `
                        <div class="mb-3">
                            <h6>Request Body</h6>
                            ${requestBody.required ? '<span class="badge bg-danger mb-2">Required</span>' : ''}
                            ${jsonContent ? `
                                <div class="code-container">
                                    <pre><code class="language-json">${JSON.stringify(jsonContent.schema, null, 2)}</code></pre>
                                </div>
                            ` : '<p class="text-muted">No request body schema available</p>'}
                        </div>
                    `;
                }

                // Render responses
                function renderResponses(responses) {
                    return `
                        <div class="mb-3">
                            <h6>Responses</h6>
                            ${Object.keys(responses).map(status => {
                                const response = responses[status];
                                const statusColor = getStatusColor(status);
                                
                                return `
                                    <div class="mb-2">
                                        <span class="badge bg-${statusColor} me-2">${status}</span>
                                        <strong>${response.description}</strong>
                                        ${response.content ? renderResponseContent(response.content) : ''}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `;
                }

                // Render response content
                function renderResponseContent(content) {
                    const jsonContent = content['application/json'];
                    if (!jsonContent) return '';
                    
                    return `
                        <div class="mt-2">
                            <strong>Response Schema:</strong>
                            <div class="code-container">
                                <pre><code class="language-json">${JSON.stringify(jsonContent.schema, null, 2)}</code></pre>
                            </div>
                        </div>
                    `;
                }

                // Render security
                function renderSecurity(security) {
                    return `
                        <div class="mb-3">
                            <h6>Security</h6>
                            <span class="badge bg-warning">
                                <i class="bi bi-shield-lock me-1"></i>
                                Authentication Required
                            </span>
                            <p class="text-muted mt-2">This endpoint requires authentication using Bearer token.</p>
                        </div>
                    `;
                }

                // Get method color
                function getMethodColor(method) {
                    const colors = {
                        'get': 'success',
                        'post': 'info',
                        'put': 'warning',
                        'delete': 'danger',
                        'patch': 'secondary'
                    };
                    return colors[method.toLowerCase()] || 'secondary';
                }

                // Get status color
                function getStatusColor(status) {
                    const statusInt = parseInt(status);
                    if (statusInt >= 200 && statusInt < 300) return 'success';
                    if (statusInt >= 300 && statusInt < 400) return 'info';
                    if (statusInt >= 400 && statusInt < 500) return 'warning';
                    if (statusInt >= 500) return 'danger';
                    return 'secondary';
                }

                // Setup event listeners
                function setupEventListeners() {
                    // Search functionality
                    document.getElementById('search-input').addEventListener('input', function(e) {
                        currentSearch = e.target.value.toLowerCase();
                        applyFilters();
                    });
                }

                // Apply filters
                function applyFilters() {
                    const endpoints = document.querySelectorAll('.endpoint-card');
                    
                    endpoints.forEach(endpoint => {
                        const path = endpoint.dataset.path.toLowerCase();
                        const method = endpoint.dataset.method.toLowerCase();
                        
                        let show = true;
                        
                        // Method filter
                        if (currentFilter !== 'all' && method !== currentFilter) {
                            show = false;
                        }
                        
                        // Search filter
                        if (currentSearch && !path.includes(currentSearch)) {
                            show = false;
                        }
                        
                        endpoint.style.display = show ? 'block' : 'none';
                    });
                }

                // Filter by method
                function filterByMethod(method) {
                    currentFilter = method;
                    applyFilters();
                    
                    // Update button states
                    document.querySelectorAll('.btn-group .btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    event.target.classList.add('active');
                }

                // Scroll to endpoint
                function scrollToEndpoint(path, method) {
                    const endpoint = document.querySelector(`[data-path="${path}"][data-method="${method}"]`);
                    if (endpoint) {
                        endpoint.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        endpoint.classList.add('highlight');
                        setTimeout(() => endpoint.classList.remove('highlight'), 2000);
                    }
                }

                // Open test modal
                function openTestModal(path, method, hasRequestBody) {
                    document.getElementById('test-url').value = `${window.location.origin}${path}`;
                    document.getElementById('test-method').value = method.toUpperCase();
                    document.getElementById('request-body-group').style.display = hasRequestBody ? 'block' : 'none';
                    
                    const modal = new bootstrap.Modal(document.getElementById('testModal'));
                    modal.show();
                }

                // Send test request
                async function sendTestRequest() {
                    const url = document.getElementById('test-url').value;
                    const method = document.getElementById('test-method').value;
                    const headersText = document.getElementById('test-headers').value;
                    const body = document.getElementById('test-body').value;
                    
                    let headers = {
                        'Content-Type': 'application/json'
                    };
                    
                    if (headersText) {
                        try {
                            const customHeaders = JSON.parse(headersText);
                            headers = { ...headers, ...customHeaders };
                        } catch (e) {
                            alert('Invalid headers JSON format');
                            return;
                        }
                    }
                    
                    const options = {
                        method: method,
                        headers: headers
                    };
                    
                    if (body && ['POST', 'PUT', 'PATCH'].includes(method)) {
                        try {
                            options.body = body;
                        } catch (e) {
                            alert('Invalid request body JSON format');
                            return;
                        }
                    }
                    
                    showLoading(true);
                    
                    try {
                        const response = await fetch(url, options);
                        const responseText = await response.text();
                        
                        let responseData;
                        try {
                            responseData = JSON.parse(responseText);
                        } catch {
                            responseData = responseText;
                        }
                        
                        const responseHtml = `
                            <div class="mb-2">
                                <strong>Status:</strong> 
                                <span class="badge bg-${getStatusColor(response.status)}">${response.status}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Headers:</strong>
                                <pre class="mb-0">${JSON.stringify(Object.fromEntries(response.headers), null, 2)}</pre>
                            </div>
                            <div>
                                <strong>Body:</strong>
                                <pre class="mb-0">${JSON.stringify(responseData, null, 2)}</pre>
                            </div>
                        `;
                        
                        document.getElementById('test-response').innerHTML = responseHtml;
                    } catch (error) {
                        document.getElementById('test-response').innerHTML = `
                            <div class="alert alert-danger">
                                <strong>Error:</strong> ${error.message}
                            </div>
                        `;
                    } finally {
                        showLoading(false);
                    }
                }

                // Show code examples
                async function showCodeExamples(path, method) {
                    try {
                        const response = await fetch('?action=examples');
                        const examples = await response.json();
                        
                        let exampleHtml = `
                            <div class="modal fade" id="codeExamplesModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Code Examples - ${method.toUpperCase()} ${path}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="nav nav-tabs" role="tablist">
                        `;
                        
                        Object.keys(examples).forEach((lang, index) => {
                            exampleHtml += `
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link ${index === 0 ? 'active' : ''}" 
                                            data-bs-toggle="tab" data-bs-target="#${lang}">
                                        ${lang.charAt(0).toUpperCase() + lang.slice(1)}
                                    </button>
                                </li>
                            `;
                        });
                        
                        exampleHtml += `
                                            </ul>
                                            <div class="tab-content mt-3">
                        `;
                        
                        Object.keys(examples).forEach((lang, index) => {
                            exampleHtml += `
                                <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" id="${lang}">
                                    <h6>${examples[lang].description}</h6>
                                    <div class="code-container">
                                        <pre><code class="language-${lang}">${examples[lang].code}</code></pre>
                                    </div>
                                </div>
                            `;
                        });
                        
                        exampleHtml += `
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Remove existing modal if any
                        const existingModal = document.getElementById('codeExamplesModal');
                        if (existingModal) {
                            existingModal.remove();
                        }
                        
                        // Add modal to body
                        document.body.insertAdjacentHTML('beforeend', exampleHtml);
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('codeExamplesModal'));
                        modal.show();
                        
                        // Highlight code
                        Prism.highlightAll();
                        
                        // Clean up modal on hide
                        document.getElementById('codeExamplesModal').addEventListener('hidden.bs.modal', function() {
                            this.remove();
                        });
                        
                    } catch (error) {
                        console.error('Error loading code examples:', error);
                        alert('Failed to load code examples');
                    }
                }

                // Show/hide loading overlay
                function showLoading(show) {
                    const overlay = document.getElementById('loading-overlay');
                    overlay.style.display = show ? 'flex' : 'none';
                }

                // Toggle theme (placeholder)
                function toggleTheme() {
                    document.body.classList.toggle('dark-theme');
                    const icon = document.getElementById('theme-icon');
                    icon.className = document.body.classList.contains('dark-theme') ? 'bi bi-sun' : 'bi bi-moon-stars';
                }
            </script>
            
            <style>
                .endpoint-card.highlight {
                    border-left-width: 6px;
                    box-shadow: 0 0 20px rgba(13, 110, 253, 0.3);
                }
                
                .dark-theme {
                    background-color: #1a1a1a;
                    color: #fff;
                }
                
                .dark-theme .endpoint-card {
                    background-color: #2d3748;
                    color: #fff;
                }
                
                .dark-theme .api-sidebar {
                    background-color: #2d3748;
                    border-right-color: #4a5568;
                }
            </style>
        </body>
        </html>
        <?php
        break;
}
?>
