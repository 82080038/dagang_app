/**
 * jQuery AJAX Utilities
 * Common AJAX functions and utilities
 */

$(document).ready(function() {
    // Global AJAX settings
    $.ajaxSetup({
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        beforeSend: function(xhr) {
            // Show loading indicator
            if (typeof showLoading === 'function') {
                showLoading();
            }
        },
        complete: function(xhr) {
            // Hide loading indicator
            if (typeof hideLoading === 'function') {
                hideLoading();
            }
        }
    });
    
    // CSRF token setup
    setupCSRF();
    
    // Global error handler
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('AJAX Error:', error);
        
        if (xhr.status === 401) {
            // Unauthorized - redirect to login
            window.location.href = 'index.php?page=login';
        } else if (xhr.status === 403) {
            // Forbidden - show error message
            showNotification('Access denied', 'error');
        } else if (xhr.status === 404) {
            // Not found
            showNotification('Resource not found', 'error');
        } else if (xhr.status >= 500) {
            // Server error
            showNotification('Server error occurred', 'error');
        }
    });
});

/**
 * Setup CSRF token for all AJAX requests
 */
function setupCSRF() {
    // Get CSRF token from meta tag or form
    var csrfToken = $('meta[name="csrf-token"]').attr('content') || 
                   $('input[name="csrf_token"]').val() || 
                   $('[name="_token"]').val();
    
    if (csrfToken) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    }
}

/**
 * Show notification message
 */
function showNotification(message, type, duration) {
    type = type || 'info';
    duration = duration || 3000;
    
    var alertClass = 'alert-' + type;
    var icon = getNotificationIcon(type);
    
    var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
        '<div class="d-flex align-items-center">' +
            '<i class="fas ' + icon + ' me-2"></i>' +
            '<span>' + message + '</span>' +
            '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>' +
        '</div>' +
        '</div>');
    
    $('body').append(notification);
    
    // Auto-dismiss after duration
    setTimeout(function() {
        notification.fadeOut(function() {
            $(this).remove();
        });
    }, duration);
}

/**
 * Get notification icon based on type
 */
function getNotificationIcon(type) {
    var icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    return icons[type] || icons.info;
}

/**
 * Show loading indicator
 */
function showLoading() {
    if ($('#loadingOverlay').length === 0) {
        $('body').append('<div id="loadingOverlay" class="loading-overlay">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '</div>');
    }
    
    $('#loadingOverlay').show();
}

/**
 * Hide loading indicator
 */
function hideLoading() {
    $('#loadingOverlay').hide();
}

/**
 * Confirm dialog
 */
function confirmDialog(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
    }
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(date, format) {
    format = format || 'YYYY-MM-DD';
    
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    
    return format
        .replace('YYYY', year)
        .replace('MM', month)
        .replace('DD', day);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    var timeout;
    return function executedFunction() {
        var context = this;
        var args = arguments;
        var later = function() {
            timeout = null;
            func.apply(context, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    var inThrottle;
    return function() {
        var args = arguments;
        var context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(function() {
                inThrottle = false;
            }, limit);
        }
    };
}

/**
 * Get URL parameters
 */
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

/**
 * Set URL parameter
 */
function setUrlParameter(param, value) {
    var url = new URL(window.location);
    url.searchParams.set(param, value);
    window.history.replaceState({}, '', url);
}

/**
 * Remove URL parameter
 */
function removeUrlParameter(param) {
    var url = new URL(window.location);
    url.searchParams.delete(param);
    window.history.replaceState({}, '', url);
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('Copied to clipboard', 'success', 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
        });
    } else {
        // Fallback for older browsers
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showNotification('Copied to clipboard', 'success', 2000);
        } catch (err) {
            console.error('Failed to copy: ', err);
        }
        
        document.body.removeChild(textArea);
    }
}

/**
 * Print element
 */
function printElement(elementId) {
    var element = document.getElementById(elementId);
    if (element) {
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(element.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename) {
    var table = document.getElementById(tableId);
    if (!table) return;
    
    var rows = table.querySelectorAll('tr');
    var csv = [];
    
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var cols = row.querySelectorAll('td, th');
        var csvRow = [];
        
        for (var j = 0; j < cols.length; j++) {
            var col = cols[j];
            var text = col.textContent.trim();
            
            // Escape quotes and wrap in quotes if contains comma
            if (text.includes(',') || text.includes('"')) {
                text = '"' + text.replace(/"/g, '""') + '"';
            }
            
            csvRow.push(text);
        }
        
        csv.push(csvRow.join(','));
    }
    
    var csvContent = csv.join('\n');
    var blob = new Blob([csvContent], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    
    var a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize popovers
 */
function initPopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Modal utilities
 */
var Modal = {
    show: function(modalId) {
        var modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    },
    
    hide: function(modalId) {
        var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) {
            modal.hide();
        }
    },
    
    confirm: function(modalId, callback) {
        var modal = document.getElementById(modalId);
        var confirmBtn = modal.querySelector('.btn-confirm');
        
        if (confirmBtn) {
            $(confirmBtn).off('click').on('click', function() {
                if (typeof callback === 'function') {
                    callback();
                }
                Modal.hide(modalId);
            });
        }
        
        Modal.show(modalId);
    }
};

/**
 * Form utilities
 */
var Form = {
    serialize: function(formId) {
        var form = document.getElementById(formId);
        if (form) {
            return $(form).serialize();
        }
        return '';
    },
    
    serializeObject: function(formId) {
        var form = document.getElementById(formId);
        if (form) {
            var data = {};
            $(form).serializeArray().map(function(x) {
                data[x.name] = x.value;
            });
            return data;
        }
        return {};
    },
    
    reset: function(formId) {
        var form = document.getElementById(formId);
        if (form) {
            form.reset();
        }
    },
    
    validate: function(formId) {
        var form = document.getElementById(formId);
        if (form) {
            return form.checkValidity();
        }
        return false;
    }
};

/**
 * Table utilities
 */
var Table = {
    reload: function(tableId, url, data) {
        $.ajax({
            url: url,
            type: 'GET',
            data: data || {},
            success: function(response) {
                if (response.status === 'success') {
                    var tbody = $('#' + tableId + ' tbody');
                    tbody.html(response.data.html);
                }
            }
        });
    },
    
    sort: function(tableId, column, order) {
        var url = getUrlParameter('url') || window.location.pathname;
        setUrlParameter('sort', column);
        setUrlParameter('order', order);
        Table.reload(tableId, url);
    },
    
    filter: function(tableId, filters) {
        var url = getUrlParameter('url') || window.location.pathname;
        Table.reload(tableId, url, filters);
    }
};

// Initialize on document ready
$(document).ready(function() {
    initTooltips();
    initPopovers();
});

// Export to global scope
window.showNotification = showNotification;
window.confirmDialog = confirmDialog;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.debounce = debounce;
window.throttle = throttle;
window.getUrlParameter = getUrlParameter;
window.setUrlParameter = setUrlParameter;
window.removeUrlParameter = removeUrlParameter;
window.copyToClipboard = copyToClipboard;
window.printElement = printElement;
window.exportTableToCSV = exportTableToCSV;
window.Modal = Modal;
window.Form = Form;
window.Table = Table;
