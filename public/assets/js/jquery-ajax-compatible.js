/**
 * jQuery AJAX Helpers - Browser Compatible Version
 * All const/let declarations replaced with var for older browser compatibility
 */

(function($) {
    'use strict';
    
    // Loading states
    $.fn.loading = function(options) {
        var settings = $.extend({
            text: 'Loading...',
            spinner: true,
            overlay: true
        }, options);
        
        return this.each(function() {
            var $element = $(this);
            var originalText = $element.text();
            
            // Store original text
            $element.data('original-text', originalText);
            
            // Disable element
            $element.prop('disabled', true);
            
            // Update text
            if (settings.text) {
                $element.text(settings.text);
            }
            
            // Add loading class
            $element.addClass('loading');
            
            // Add spinner if needed
            if (settings.spinner) {
                $element.prepend('<i class="spinner fa fa-spinner fa-spin me-2"></i>');
            }
            
            // Add overlay if needed
            if (settings.overlay) {
                var $overlay = $('<div class="loading-overlay"></div>');
                $element.wrap('<div class="loading-wrapper"></div>');
                $element.parent().append($overlay);
            }
        });
    };
    
    $.fn.loaded = function() {
        return this.each(function() {
            var $element = $(this);
            var originalText = $element.data('original-text');
            
            // Restore original text
            if (originalText) {
                $element.text(originalText);
            }
            
            // Enable element
            $element.prop('disabled', false);
            
            // Remove loading class
            $element.removeClass('loading');
            
            // Remove spinner
            $element.find('.spinner').remove();
            
            // Remove overlay
            $element.closest('.loading-wrapper').find('.loading-overlay').remove();
            $element.unwrap();
        });
    };
    
    // Form submission helper
    $.fn.submitForm = function(options) {
        var settings = $.extend({
            url: null,
            method: 'POST',
            data: null,
            beforeSend: null,
            success: null,
            error: null,
            complete: null,
            resetForm: true,
            loadingText: 'Submitting...'
        }, options);
        
        var $form = this;
        
        // Set URL from form action if not provided
        if (!settings.url) {
            settings.url = $form.attr('action') || window.location.href;
        }
        
        // Set method from form if not provided
        if (!settings.method) {
            settings.method = $form.attr('method') || 'POST';
        }
        
        // Set data from form if not provided
        if (!settings.data) {
            settings.data = $form.serialize();
        }
        
        // Show loading state
        var $submitBtn = $form.find(':submit');
        var originalButtonText = $submitBtn.text();
        
        if (settings.loadingText) {
            $submitBtn.loading({text: settings.loadingText});
        }
        
        // AJAX submission
        $.ajax({
            url: settings.url,
            type: settings.method,
            data: settings.data,
            dataType: 'json',
            beforeSend: function(xhr) {
                if (settings.beforeSend) {
                    return settings.beforeSend.call($form, xhr);
                }
            },
            success: function(response) {
                if (settings.success) {
                    settings.success.call($form, response);
                }
                
                // Reset form if needed
                if (settings.resetForm) {
                    $form[0].reset();
                }
            },
            error: function(xhr, status, error) {
                if (settings.error) {
                    settings.error.call($form, xhr, status, error);
                } else {
                    // Default error handling
                    console.error('Form submission error:', error);
                }
            },
            complete: function() {
                // Restore button state
                $submitBtn.loaded();
                
                if (settings.complete) {
                    settings.complete.call($form);
                }
            }
        });
        
        return this;
    };
    
    // Table helper functions
    $.fn.sortableTable = function(options) {
        var settings = $.extend({
            column: 0,
            order: 'asc'
        }, options);
        
        return this.each(function() {
            var $table = $(this);
            var $thead = $table.find('thead');
            var $tbody = $table.find('tbody');
            
            // Add sort indicators to headers
            $thead.find('th').eq(settings.column).addClass('sortable');
            
            // Sort functionality
            $thead.find('th').on('click', function() {
                var $th = $(this);
                var columnIndex = $th.index();
                var currentOrder = $th.hasClass('asc') ? 'desc' : 'asc';
                
                // Remove all sort classes
                $thead.find('th').removeClass('asc desc');
                
                // Add current sort class
                $th.addClass(currentOrder);
                
                // Get all rows
                var rows = $tbody.find('tr').toArray();
                
                // Sort rows
                rows.sort(function(a, b) {
                    var aValue = $(a).find('td:eq(' + columnIndex + ')').text();
                    var bValue = $(b).find('td:eq(' + columnIndex + ')').text();
                    
                    if (currentOrder === 'asc') {
                        return aValue.localeCompare(bValue);
                    } else {
                        return bValue.localeCompare(aValue);
                    }
                });
                
                // Reorder rows
                $tbody.empty().append(rows);
            });
        });
    };
    
    // Search helper
    $.fn.searchTable = function(options) {
        var settings = $.extend({
            input: null,
            caseSensitive: false
        }, options);
        
        return this.each(function() {
            var $table = $(this);
            var $tbody = $table.find('tbody');
            var $rows = $tbody.find('tr');
            
            if (settings.input) {
                var $input = $(settings.input);
                
                $input.on('input', function() {
                    var searchTerm = $(this).val().toLowerCase();
                    
                    $rows.each(function() {
                        var $row = $(this);
                        var text = $row.text().toLowerCase();
                        
                        if (settings.caseSensitive) {
                            text = $row.text();
                        }
                        
                        if (text.includes(searchTerm)) {
                            $row.show();
                        } else {
                            $row.hide();
                        }
                    });
                });
            }
        });
    };
    
    // Modal helper
    $.fn.modalForm = function(options) {
        var settings = $.extend({
            modal: null,
            form: null,
            url: null,
            method: 'POST',
            success: null,
            error: null
        }, options);
        
        return this.each(function() {
            var $trigger = $(this);
            
            $trigger.on('click', function(e) {
                e.preventDefault();
                
                var $modal = $(settings.modal);
                var $form = $modal.find(settings.form);
                
                // Set form action if not provided
                if (!settings.url) {
                    $form.attr('action', settings.url);
                }
                
                // Set form method if not provided
                if (!settings.method) {
                    $form.attr('method', settings.method);
                }
                
                // Handle form submission
                $form.submitForm({
                    url: settings.url,
                    method: settings.method,
                    success: function(response) {
                        if (settings.success) {
                            settings.success(response);
                        }
                        
                        // Close modal
                        var modalInstance = bootstrap.Modal.getInstance($modal[0]);
                        if (modalInstance) {
                            modalInstance.hide();
                        } else {
                            $modal.modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        if (settings.error) {
                            settings.error(xhr, status, error);
                        }
                    }
                });
            });
        });
    };
    
    // Toast notification helper
    $.fn.toast = function(options) {
        var settings = $.extend({
            message: '',
            type: 'info',
            delay: 5000,
            autohide: true
        }, options);
        
        return this.each(function() {
            var toastId = 'toast-' + Date.now();
            var toastClass = 'toast-' + settings.type;
            var iconClass = getIcon(settings.type);
            var delay = settings.delay || 5000;
            var autohide = settings.autohide !== false;
            
            var toastHtml = '<div id="' + toastId + '" class="toast ' + toastClass + ' show" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="toast-header">' +
                '<i class="' + iconClass + ' me-2"></i>' +
                '<strong class="me-auto">Notification</strong>' +
                '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>' +
                '<div class="toast-body">' + settings.message + '</div>' +
                '</div>';
            
            $(this).append(toastHtml);
            
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
        });
    };
    
    // Icon helper
    function getIcon(type) {
        var icons = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        
        return icons[type] || icons.info;
    }
    
    // Initialize tooltips
    function initTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Initialize popovers
    function initPopovers() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        initTooltips();
        initPopovers();
    });
    
})(jQuery);
