/**
 * jQuery/Ajax Helper Functions for Perdagangan System
 * Enhanced DOM manipulation and AJAX utilities
 */

// Global AJAX configuration
window.AjaxConfig = {
    baseUrl: BASE_URL || '/dagang',
    timeout: 30000,
    retryAttempts: 3,
    retryDelay: 1000
};

// CSRF Token Management
window.CSRF = {
    token: null,
    
    init: function() {
        this.token = $('meta[name="csrf-token"]').attr('content') || 
                   $('input[name="csrf_token"]').val();
    },
    
    get: function() {
        return this.token;
    },
    
    add: function(data) {
        if (this.token) {
            data.csrf_token = this.token;
        }
        return data;
    }
};

// Loading States
window.Loading = {
    show: function(element, text = 'Loading...') {
        const $element = $(element);
        $element.addClass('loading');
        
        if ($element.is('button')) {
            $element.prop('disabled', true);
            const originalText = $element.text();
            $element.data('original-text', originalText);
            $element.html('<i class="bi bi-hourglass-split"></i> ' + text);
        } else {
            $element.prepend('<div class="loading-overlay"><div class="spinner-border"></div></div>');
        }
    },
    
    hide: function(element) {
        const $element = $(element);
        $element.removeClass('loading');
        
        if ($element.is('button')) {
            $element.prop('disabled', false);
            const originalText = $element.data('original-text');
            if (originalText) {
                $element.text(originalText);
            }
        } else {
            $element.find('.loading-overlay').remove();
        }
    },
    
    showGlobal: function(text = 'Loading...') {
        if (!$('#global-loading').length) {
            $('body').append(`
                <div id="global-loading" class="global-loading">
                    <div class="loading-backdrop"></div>
                    <div class="loading-content">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">${text}</div>
                    </div>
                </div>
            `);
        }
    },
    
    hideGlobal: function() {
        $('#global-loading').fadeOut(300, function() {
            $(this).remove();
        });
    }
};

// AJAX Wrapper with retry mechanism
window.Ajax = {
    request: function(options) {
        const defaults = {
            type: 'POST',
            dataType: 'json',
            timeout: AjaxConfig.timeout,
            beforeSend: function(xhr) {
                Loading.showGlobal();
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                if (options.beforeSend) {
                    return options.beforeSend(xhr);
                }
            },
            complete: function() {
                Loading.hideGlobal();
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showNotification('Request failed: ' + error, 'error');
                if (options.error) {
                    return options.error(xhr, status, error);
                }
            }
        };
        
        const settings = $.extend(true, defaults, options);
        
        // Add CSRF token to data
        if (settings.type === 'POST' && settings.data) {
            settings.data = CSRF.add(settings.data);
        }
        
        return $.ajax(settings);
    },
    
    // GET request
    get: function(url, data, success, error) {
        return this.request({
            url: url,
            type: 'GET',
            data: data,
            success: success,
            error: error
        });
    },
    
    // POST request
    post: function(url, data, success, error) {
        return this.request({
            url: url,
            type: 'POST',
            data: data,
            success: success,
            error: error
        });
    },
    
    // PUT request
    put: function(url, data, success, error) {
        return this.request({
            url: url,
            type: 'PUT',
            data: data,
            success: success,
            error: error
        });
    },
    
    // DELETE request
    delete: function(url, data, success, error) {
        return this.request({
            url: url,
            type: 'DELETE',
            data: data,
            success: success,
            error: error
        });
    },
    
    // Form submission
    submit: function(form, success, error) {
        const $form = $(form);
        const url = $form.attr('action') || window.location.href;
        const method = $form.attr('method') || 'POST';
        const data = $form.serialize();
        
        return this.request({
            url: url,
            type: method.toUpperCase(),
            data: data,
            success: success,
            error: error,
            beforeSend: function() {
                Loading.show($form);
            },
            complete: function() {
                Loading.hide($form);
            }
        });
    }
};

// DOM Manipulation Helpers
window.DOM = {
    // Replace content with fade effect
    replace: function(element, content, callback) {
        const $element = $(element);
        $element.fadeOut(300, function() {
            $element.html(content).fadeIn(300, callback);
        });
    },
    
    // Append content with slide effect
    append: function(element, content, callback) {
        const $element = $(element);
        const $content = $(content).hide();
        $element.append($content);
        $content.slideDown(300, callback);
    },
    
    // Prepend content with slide effect
    prepend: function(element, content, callback) {
        const $element = $(element);
        const $content = $(content).hide();
        $element.prepend($content);
        $content.slideDown(300, callback);
    },
    
    // Remove element with fade effect
    remove: function(element, callback) {
        const $element = $(element);
        $element.fadeOut(300, function() {
            $element.remove();
            if (callback) callback();
        });
    },
    
    // Toggle visibility
    toggle: function(element, callback) {
        const $element = $(element);
        $element.slideToggle(300, callback);
    },
    
    // Show element
    show: function(element, callback) {
        const $element = $(element);
        $element.slideDown(300, callback);
    },
    
    // Hide element
    hide: function(element, callback) {
        const $element = $(element);
        $element.slideUp(300, callback);
    },
    
    // Update element value with animation
    updateValue: function(element, value, callback) {
        const $element = $(element);
        $element.addClass('updating');
        
        setTimeout(function() {
            $element.val(value).removeClass('updating');
            if (callback) callback();
        }, 300);
    },
    
    // Update element text with animation
    updateText: function(element, text, callback) {
        const $element = $(element);
        $element.addClass('updating');
        
        setTimeout(function() {
            $element.text(text).removeClass('updating');
            if (callback) callback();
        }, 300);
    },
    
    // Add class with animation
    addClass: function(element, className, callback) {
        const $element = $(element);
        $element.addClass(className);
        
        setTimeout(function() {
            if (callback) callback();
        }, 50);
    },
    
    // Remove class with animation
    removeClass: function(element, className, callback) {
        const $element = $(element);
        $element.removeClass(className);
        
        setTimeout(function() {
            if (callback) callback();
        }, 300);
    }
};

// Form Utilities
window.Form = {
    // Serialize form to JSON
    toJSON: function(form) {
        const $form = $(form);
        const data = {};
        
        $form.find('input, select, textarea').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            const type = $field.attr('type');
            const value = $field.val();
            
            if (name) {
                if (type === 'checkbox') {
                    data[name] = $field.is(':checked');
                } else if (type === 'radio') {
                    if ($field.is(':checked')) {
                        data[name] = value;
                    }
                } else {
                    data[name] = value;
                }
            }
        });
        
        return data;
    },
    
    // Validate form
    validate: function(form, rules) {
        const $form = $(form);
        const errors = {};
        let isValid = true;
        
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
        
        $.each(rules, function(field, rule) {
            const $field = $form.find(`[name="${field}"]`);
            const value = $field.val();
            
            if (rule.required && (!value || value.trim() === '')) {
                errors[field] = 'This field is required';
                $field.addClass('is-invalid');
                isValid = false;
            }
            
            if (rule.email && value && !this.isValidEmail(value)) {
                errors[field] = 'Please enter a valid email address';
                $field.addClass('is-invalid');
                isValid = false;
            }
            
            if (rule.min && value && value.length < rule.min) {
                errors[field] = `Minimum ${rule.min} characters required`;
                $field.addClass('is-invalid');
                isValid = false;
            }
            
            if (rule.max && value && value.length > rule.max) {
                errors[field] = `Maximum ${rule.max} characters allowed`;
                $field.addClass('is-invalid');
                isValid = false;
            }
            
            if (rule.pattern && value && !new RegExp(rule.pattern).test(value)) {
                errors[field] = 'Invalid format';
                $field.addClass('is-invalid');
                isValid = false;
            }
        });
        
        // Display errors
        if (!isValid) {
            $.each(errors, function(field, message) {
                const $field = $form.find(`[name="${field}"]`);
                $field.after(`<div class="invalid-feedback">${message}</div>`);
            });
            
            // Focus first error field
            const $firstError = $form.find('.is-invalid').first();
            if ($firstError.length) {
                $firstError.focus();
            }
        }
        
        return { valid: isValid, errors: errors };
    },
    
    // Check if email is valid
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    // Reset form
    reset: function(form) {
        const $form = $(form);
        $form[0].reset();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    },
    
    // Clear form
    clear: function(form) {
        const $form = $(form);
        $form.find('input, select, textarea').val('');
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    }
};

// Table Utilities
window.Table = {
    // Sort table
    sort: function(table, column, direction) {
        const $table = $(table);
        const $tbody = $table.find('tbody');
        const rows = $tbody.find('tr').toArray();
        
        rows.sort(function(a, b) {
            const $a = $(a);
            const $b = $(b);
            const aValue = $a.find(`td:eq(${column})`).text();
            const bValue = $b.find(`td:eq(${column})`).text();
            
            if (direction === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
        
        $tbody.empty().append(rows);
    },
    
    // Filter table
    filter: function(table, searchTerm) {
        const $table = $(table);
        const $tbody = $table.find('tbody');
        const $rows = $tbody.find('tr');
        
        if (!searchTerm) {
            $rows.show();
            return;
        }
        
        $rows.each(function() {
            const $row = $(this);
            const text = $row.text().toLowerCase();
            
            if (text.includes(searchTerm.toLowerCase())) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    },
    
    // Get selected rows
    getSelected: function(table) {
        const $table = $(table);
        return $table.find('input[type="checkbox"]:checked').closest('tr');
    },
    
    // Select all rows
    selectAll: function(table) {
        const $table = $(table);
        const $checkbox = $table.find('input[type="checkbox"]');
        const $selectAll = $table.find('thead input[type="checkbox"]');
        
        $checkbox.prop('checked', $selectAll.prop('checked'));
    },
    
    // Get table data as array
    getData: function(table) {
        const $table = $(table);
        const $rows = $table.find('tbody tr');
        const data = [];
        
        $rows.each(function() {
            const $row = $(this);
            const rowData = {};
            
            $row.find('td').each(function(index) {
                const $th = $table.find('thead th').eq(index);
                const columnName = $th.text().toLowerCase().replace(/\s+/g, '_');
                rowData[columnName] = $(this).text().trim();
            });
            
            data.push(rowData);
        });
        
        return data;
    }
};

// Modal Utilities
window.Modal = {
    // Show modal
    show: function(modalId, options) {
        const $modal = $(modalId);
        
        if (options && options.title) {
            $modal.find('.modal-title').text(options.title);
        }
        
        if (options && options.content) {
            $modal.find('.modal-body').html(options.content);
        }
        
        if (options && options.size) {
            $modal.find('.modal-dialog').removeClass('modal-sm modal-lg modal-xl').addClass('modal-' + options.size);
        }
        
        const modal = new bootstrap.Modal($modal[0]);
        modal.show();
        
        return modal;
    },
    
    // Hide modal
    hide: function(modalId) {
        const $modal = $(modalId);
        const modal = bootstrap.Modal.getInstance($modal[0]);
        
        if (modal) {
            modal.hide();
        }
    },
    
    // Show confirmation modal
    confirm: function(title, message, onConfirm, onCancel) {
        const $modal = $('#confirmModal');
        
        if (!$modal.length) {
            // Create modal if not exists
            $('body').append(`
                <div class="modal fade" id="confirmModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="confirm-message"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary confirm-btn">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        $modal.find('.modal-title').text(title);
        $modal.find('.confirm-message').text(message);
        
        const modal = new bootstrap.Modal($modal[0]);
        
        $modal.find('.confirm-btn').off('click').on('click', function() {
            if (onConfirm) onConfirm();
            modal.hide();
        });
        
        $modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
            if (onCancel) onCancel();
        });
        
        modal.show();
        return modal;
    },
    
    // Show alert modal
    alert: function(title, message, type, onOk) {
        const $modal = $('#alertModal');
        
        if (!$modal.length) {
            // Create modal if not exists
            $('body').append(`
                <div class="modal fade" id="alertModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Alert</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="alert-message"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary ok-btn">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        $modal.find('.modal-title').text(title);
        $modal.find('.alert-message').text(message);
        
        // Set alert type
        $modal.find('.modal-header').removeClass('bg-success bg-danger bg-warning bg-info text-white')
            .addClass('bg-' + (type || 'primary') + ' text-white');
        
        const modal = new bootstrap.Modal($modal[0]);
        
        $modal.find('.ok-btn').off('click').on('click', function() {
            if (onOk) onOk();
            modal.hide();
        });
        
        modal.show();
        return modal;
    }
};

// Notification System
window.Notification = {
    show: function(message, type, options) {
        type = type || 'info';
        options = options || {};
        
        const alertClass = 'alert-' + type;
        const iconClass = this.getIcon(type);
        
        const $notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <div class="d-flex align-items-center">
                    <i class="${iconClass} me-2"></i>
                    <span class="message">${message}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `);
        
        $('body').append($notification);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, options.duration || 5000);
        
        return $notification;
    },
    
    success: function(message, options) {
        return this.show(message, 'success', options);
    },
    
    error: function(message, options) {
        return this.show(message, 'danger', options);
    },
    
    warning: function(message, options) {
        return this.show(message, 'warning', options);
    },
    
    info: function(message, options) {
        return this.show(message, 'info', options);
    },
    
    getIcon: function(type) {
        const icons = {
            success: 'bi bi-check-circle-fill',
            error: 'bi bi-x-circle-fill',
            warning: 'bi bi-exclamation-triangle-fill',
            info: 'bi bi-info-circle-fill'
        };
        
        return icons[type] || icons.info;
    }
};

// Initialize CSRF token
$(document).ready(function() {
    CSRF.init();
});

// Auto-initialize tooltips
$(document).ready(function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Auto-initialize popovers
$(document).ready(function() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
