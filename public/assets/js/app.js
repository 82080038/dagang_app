/**
 * Perdagangan System JavaScript
 * Native PHP MVC Pattern with Enhanced jQuery/Ajax
 */

// Note: jquery-ajax.js is loaded separately in the layout

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
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions (exclude module-specific delete buttons)
    $('.delete-btn:not([id^="company-delete-btn-"]):not([id^="branch-delete-btn-"]):not([id^="product-delete-btn-"]):not([id^="staff-delete-btn-"])').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var title = $(this).data('title');
        if (!title) title = 'Hapus Data';
        var message = $(this).data('message');
        if (!message) message = 'Apakah Anda yakin ingin menghapus data ini?';
        
        Modal.confirm(title, message, function() {
            window.location.href = url;
        });
    });
    
    // Form validation with enhanced feedback
    $('form').on('submit', function(e) {
        var form = this;
        var isValid = true;
        
        // Remove previous error messages
        $(form).find('.is-invalid').removeClass('is-invalid');
        $(form).find('.invalid-feedback').remove();
        
        // Validate required fields
        $(form).find('[required]').each(function() {
            var field = $(this);
            var value = field.val();
            
            if (!value || value.trim() === '') {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Field ini wajib diisi</div>');
                isValid = false;
            }
        });
        
        // Validate email fields
        $(form).find('[type="email"]').each(function() {
            var field = $(this);
            var value = field.val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value && !emailRegex.test(value)) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Format email tidak valid</div>');
                isValid = false;
            }
        });
        
        // Validate numeric fields
        $(form).find('[data-numeric="true"]').each(function() {
            var field = $(this);
            var value = field.val();
            
            if (value && !$.isNumeric(value)) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Field ini harus berupa angka</div>');
                isValid = false;
            }
        });
        
        // Validate min/max length
        $(form).find('[data-min]').each(function() {
            var field = $(this);
            var value = field.val();
            var minLength = parseInt(field.data('min'));
            
            if (value && value.length < minLength) {
                field.addClass('is-invalid');
                field.after(`<div class="invalid-feedback">Minimal ${minLength} karakter</div>`);
                isValid = false;
            }
        });
        
        $(form).find('[data-max]').each(function() {
            var field = $(this);
            var value = field.val();
            var maxLength = parseInt(field.data('max'));
            
            if (value && value.length > maxLength) {
                field.addClass('is-invalid');
                field.after(`<div class="invalid-feedback">Maksimal ${maxLength} karakter</div>`);
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first error field
            var firstError = $(form).find('.is-invalid').first();
            if (firstError.length) {
                firstError.focus();
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }
    });
    
    // Enhanced search functionality
    $('#search-input').on('input', function(e) {
        var query = $(this).val().toLowerCase();
        var searchTarget = $(this).data('search-target');
        if (!searchTarget) searchTarget = 'table-row';
        
        if (searchTarget === 'table-row') {
            $('tbody tr').each(function() {
                var row = $(this);
                var text = row.text().toLowerCase();
                
                if (text.includes(query)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }
    });
    
    // Clear search
    $('#search-clear').on('click', function() {
        $('#search-input').val('').trigger('input');
    });
    
    // Enhanced button actions
    $('.btn-action').on('click', function(e) {
        var $button = $(this);
        var action = $button.data('action');
        var target = $button.data('target');
        var confirm = $button.data('confirm');
        
        if (confirm) {
            e.preventDefault();
            Modal.confirm(
                'Konfirmasi Aksi',
                'Apakah Anda yakin ingin melakukan ' + action + '?',
                function() {
                    // Execute action
                    if (target) {
                        window.location.href = target;
                    } else if (action) {
                        // Execute custom action
                        console.log('Executing action:', action);
                    }
                }
            );
        } else {
            // Direct action
            if (target) {
                window.location.href = target;
            }
        }
    });
    
    // AJAX form submission
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var url = $form.attr('action');
        var method = $form.attr('method');
        if (!method) method = 'POST';
        var successCallback = $form.data('success');
        var errorCallback = $form.data('error');
        
        Form.submit($form, function(response) {
            if (successCallback) {
                successCallback(response);
            }
            showNotification('Data berhasil disimpan', 'success');
            
            // Reset form if successful
            if ($form.data('reset')) {
                Form.reset($form);
            }
        }, function(xhr, status, error) {
            if (errorCallback) {
                errorCallback(xhr, status, error);
            }
            showNotification('Terjadi kesalahan: ' + error, 'error');
        });
    });
    
    // Dynamic content loading
    $('.load-content').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var url = $button.data('url');
        var target = $button.data('target');
        var loadingText = $button.data('loading') || 'Loading...';
        
        Loading.show($button, loadingText);
        
        Ajax.get(url, null, function(response) {
            if (target) {
                DOM.replace(target, response.content);
            }
            Loading.hide($button);
        }, function(xhr, status, error) {
            Loading.hide($button);
            showNotification('Gagal memuat konten', 'error');
        });
    });
    
    // Dynamic form loading
    $('.load-form').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var url = $button.data('url');
        var target = $button.data('target');
        var loadingText = $button.data('loading') || 'Loading...';
        
        Loading.show($button, loadingText);
        
        Ajax.get(url, null, function(response) {
            if (target) {
                DOM.replace(target, response.html);
                
                // Initialize new form elements
                $(target).find('form').each(function() {
                    initializeForm($(this));
                });
            }
            Loading.hide($button);
        }, function(xhr, status, error) {
            Loading.hide($button);
            showNotification('Gagal memuat form', 'error');
        });
    });
    
    // Table operations
    $('.table-sortable thead th[data-sortable="true"]').on('click', function() {
        var $th = $(this);
        var column = $th.index();
        var currentDirection = $th.data('direction');
        if (!currentDirection) currentDirection = 'asc';
        var newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        
        // Update sort indicator
        $th.find('i').removeClass('bi-sort-up bi-sort-down').addClass('bi-sort-' + newDirection);
        $th.data('direction', newDirection);
        
        // Sort table
        Table.sort('#data-table', column, newDirection);
    });
    
    // Table filtering
    $('#table-search').on('input', function() {
        var searchTerm = $(this).val();
        Table.filter('#data-table', searchTerm);
    });
    
    // Table selection
    $('.select-all-checkbox').on('change', function() {
        Table.selectAll('#data-table');
    });
    
    // Export table data
    $('.export-table').on('click', function() {
        var $button = $(this);
        var format = $button.data('format');
        if (!format) format = 'json';
        var tableId = $button.data('table');
        if (!tableId) tableId = '#data-table';
        
        var data = Table.getData(tableId);
        var content = '';
        
        switch (format) {
            case 'json':
                content = JSON.stringify(data, null, 2);
                break;
            case 'csv':
                content = convertToCSV(data);
                break;
            case 'excel':
                content = convertToExcel(data);
                break;
        }
        
        // Download file
        downloadFile('table-data.' + format, content, 'text/plain');
    });
    
    // Initialize forms
    initializeForm($('form'));
});

// Initialize form elements
function initializeForm($form) {
    // Add dynamic validation
    $form.find('input[data-validate]').on('blur', function() {
        var $field = $(this);
        var rules = $field.data('validate').split('|');
        var validationRules = {};
        
        rules.forEach(function(rule) {
            var parts = rule.split(':');
            var ruleName = parts[0];
            var ruleValue = parts[1];
        if (!ruleValue) ruleValue = '';
            
            validationRules[ruleName] = {};
            
            if (ruleValue.includes('required')) {
                validationRules[ruleName].required = true;
            }
            
            if (ruleValue.includes('email')) {
                validationRules[ruleName].email = true;
            }
            
            if (ruleValue.includes('min:')) {
                validationRules[ruleName].min = parseInt(ruleValue.split(':')[1]);
            }
            
            if (ruleValue.includes('max:')) {
                validationRules[ruleName].max = parseInt(ruleValue.split(':')[1]);
            }
            
            if (ruleValue.includes('pattern:')) {
                validationRules[ruleName].pattern = ruleValue.split(':')[1];
            }
        });
        
        // Validate field
        var result = Form.validate($field, validationRules);
        if (!result.valid) {
            $field.addClass('is-invalid');
            $field.after('<div class="invalid-feedback">' + Object.values(result.errors)[0] + '</div>');
        } else {
            $field.removeClass('is-invalid');
            $field.siblings('.invalid-feedback').remove();
        }
    });
    
    // Auto-save functionality
    $form.find('.auto-save').on('input change', function() {
        clearTimeout(window.autoSaveTimer);
        window.autoSaveTimer = setTimeout(function() {
            saveForm($form);
        }, 3000);
    });
}

// Save form data
function saveForm($form) {
    var url = $form.data('autosave-url');
    
    if (url) {
        var data = Form.toJSON($form);
        
        Ajax.post(url, data, function(response) {
            showNotification('Data tersimpan otomatis', 'success');
        }, function(xhr, status, error) {
            showNotification('Gagal menyimpan data otomatis', 'warning');
        });
    }
}

// Convert data to CSV
function convertToCSV(data) {
    if (!data || data.length === 0) {
        return '';
    }
    
    var headers = Object.keys(data[0]);
    var csv = headers.join(',') + '\n';
    
    data.forEach(function(row) {
        var values = headers.map(function(header) {
            return '"' + (row[header] || '').toString().replace(/"/g, '""') + '"';
        });
        csv += values.join(',') + '\n';
    });
    
    return csv;
}

// Convert data to Excel (simplified)
function convertToExcel(data) {
    if (!data || data.length === 0) {
        return '';
    }
    
    var headers = Object.keys(data[0]);
    var excel = '<table>';
    
    // Header row
    excel += '<tr>';
    headers.forEach(function(header) {
        excel += '<th>' + header + '</th>';
    });
    excel += '</tr>';
    
    // Data rows
    data.forEach(function(row) {
        excel += '<tr>';
        headers.forEach(function(header) {
            excel += '<td>' + (row[header] || '') + '</td>';
        });
        excel += '</tr>';
    });
    
    excel += '</table>';
    
    return excel;
}

// Download file
function downloadFile(filename, content, mimeType) {
    var blob = new Blob([content], { type: mimeType });
    var url = window.URL.createObjectURL(blob);
    
    var $link = $('<a></a>')
        .attr('href', url)
        .attr('download', filename)
        .css('display', 'none');
    
    $('body').append($link);
    $link[0].click();
    
    window.URL.revokeObjectURL(url);
    $link.remove();
}

// Toast Notification System
window.Toast = {
    show: function(message, type, options) {
        type = type || 'info';
        options = options || {};
        
        var delay = options.delay || 5000;
        var autohide = options.autohide !== false;
        
        // Create toast container if it doesn't exist
        if ($('#toast-container').length === 0) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>');
        }
        
        var toastId = 'toast-' + Date.now();
        var toastClass = 'bg-' + (type === 'error' ? 'danger' : type);
        var iconClass = this.getIcon(type);
        
        var toastHtml = '<div id="' + toastId + '" class="toast" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="toast-header ' + toastClass + ' text-white">' +
            '<i class="' + iconClass + ' me-2"></i>' +
            '<strong class="me-auto">Notifikasi</strong>' +
            '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '<div class="toast-body">' + message + '</div>' +
            '</div>';
        
        $('#toast-container').append(toastHtml);
        
        var toastElement = document.getElementById(toastId);
        var toast = new bootstrap.Toast(toastElement, {
            delay: delay,
            autohide: autohide
        });
        
        toast.show();
        
        // Auto remove after delay
        if (autohide) {
            setTimeout(function() {
                toast.hide();
                setTimeout(function() {
                    $(toastElement).remove();
                }, 500);
            }, delay);
        }
    },
    
    success: function(message, options) {
        this.show(message, 'success', options);
    },
    
    error: function(message, options) {
        this.show(message, 'error', options);
    },
    
    warning: function(message, options) {
        this.show(message, 'warning', options);
    },
    
    info: function(message, options) {
        this.show(message, 'info', options);
    },
    
    getIcon: function(type) {
        var icons = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        
        return icons[type] || icons.info;
    }
};

// Global notification function (backward compatibility)
window.showNotification = function(message, type, options) {
    Toast.show(message, type, options);
};

// Global loading function (backward compatibility)
window.showLoading = function(text) {
    Loading.showGlobal(text);
};

// Global hide loading function (backward compatibility)
window.hideLoading = function() {
    Loading.hideGlobal();
};


window.formatCurrency = function(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

window.formatDate = function(date, format) {
        var format = format || 'DD/MM/YYYY';
    var d = new Date(date);
    var day = d.getDate().toString().padStart(2, '0');
    var month = (d.getMonth() + 1).toString().padStart(2, '0');
    var year = d.getFullYear();
    var hours = d.getHours().toString().padStart(2, '0');
    var minutes = d.getMinutes().toString().padStart(2, '0');
    
    return format
        .replace('DD', day)
        .replace('MM', month)
        .replace('YYYY', year)
        .replace('HH', hours)
        .replace('mm', minutes);
};
