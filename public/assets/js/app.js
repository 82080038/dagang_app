/**
 * Perdagangan System JavaScript
 * Native PHP MVC Pattern
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
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var title = $(this).data('title') || 'Hapus Data';
        var message = $(this).data('message') || 'Apakah Anda yakin ingin menghapus data ini?';
        
        if (confirm(title + '\n\n' + message)) {
            window.location.href = url;
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var form = $(this);
        var isValid = true;
        
        // Remove previous error messages
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();
        
        // Validate required fields
        form.find('[required]').each(function() {
            var field = $(this);
            var value = field.val();
            
            if (!value || value.trim() === '') {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Field ini wajib diisi</div>');
                isValid = false;
            }
        });
        
        // Validate email fields
        form.find('[type="email"]').each(function() {
            var field = $(this);
            var value = field.val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value && !emailRegex.test(value)) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Format email tidak valid</div>');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first error
            var firstError = form.find('.is-invalid').first();
            if (firstError.length) {
                firstError.focus();
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }
    });
    
    // Search functionality
    $('#search-input').on('keyup', function(e) {
        var query = $(this).val().toLowerCase();
        var searchTarget = $(this).data('search-target') || 'table-row';
        
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
        $('#search-input').val('').trigger('keyup');
    });
    
    // Toggle sidebar
    $('#sidebar-toggle').on('click', function() {
        $('body').toggleClass('sidebar-collapsed');
        $(this).find('i').toggleClass('fa-chevron-left fa-chevron-right');
    });
    
    // Modal confirm
    $('.modal-confirm').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var modal = $('#confirmModal');
        var title = button.data('title') || 'Konfirmasi';
        var message = button.data('message') || 'Apakah Anda yakin?';
        var action = button.data('action');
        
        modal.find('.modal-title').text(title);
        modal.find('.modal-body').text(message);
        modal.find('#confirmAction').data('action', action);
        modal.find('#confirmAction').data('target', button.attr('href'));
        
        modal.modal('show');
    });
    
    // Confirm action
    $('#confirmAction').on('click', function() {
        var action = $(this).data('action');
        var target = $(this).data('target');
        
        if (action === 'delete') {
            window.location.href = target;
        } else if (action === 'form') {
            $(target).submit();
        }
        
        $('#confirmModal').modal('hide');
    });
    
    // Auto-save functionality
    var autoSaveTimer;
    $('.auto-save').on('input change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            saveForm();
        }, 3000);
    });
    
    function saveForm() {
        var form = $('.auto-save').closest('form');
        var data = form.serialize();
        var url = form.data('autosave-url');
        
        if (url) {
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(response) {
                    showNotification('Data berhasil disimpan otomatis', 'success');
                },
                error: function() {
                    showNotification('Gagal menyimpan data otomatis', 'error');
                }
            });
        }
    }
    
    // Notification system
    function showNotification(message, type = 'info') {
        var alertClass = 'alert-' + type;
        var icon = getNotificationIcon(type);
        
        var notification = $('<div>')
            .addClass('alert ' + alertClass + ' alert-dismissible fade show position-fixed')
            .css({
                'top': '20px',
                'right': '20px',
                'z-index': '9999',
                'min-width': '300px'
            })
            .html(
                '<div class="d-flex align-items-center">' +
                '<i class="fas ' + icon + ' me-2"></i>' +
                '<span>' + message + '</span>' +
                '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>' +
                '</div>'
            );
        
        $('body').append(notification);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            notification.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    function getNotificationIcon(type) {
        var icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        return icons[type] || icons['info'];
    }
    
    // Loading state
    function showLoading(element) {
        var loadingHtml = '<div class="text-center py-3">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '<p class="mt-2 text-muted">Memuat data...</p>' +
            '</div>';
        
        $(element).html(loadingHtml);
    }
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    }
    
    // Format date
    function formatDate(date, format = 'DD/MM/YYYY') {
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
    }
    
    // Copy to clipboard
    $('.copy-btn').on('click', function() {
        var text = $(this).data('copy');
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showNotification('Tersalin ke clipboard!', 'success');
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Tersalin ke clipboard!', 'success');
        }
    });
    
    // Print functionality
    $('.print-btn').on('click', function() {
        window.print();
    });
    
    // Export functionality
    $('.export-btn').on('click', function() {
        var format = $(this).data('format');
        var url = $(this).data('url');
        
        if (format && url) {
            window.open(url + '?format=' + format, '_blank');
        }
    });
    
    // Number formatting
    $('.number-format').on('input', function() {
        var value = $(this).val();
        var formatted = value.replace(/\D/g, '');
        $(this).val(formatted);
    });
    
    // Phone number formatting
    $('.phone-format').on('input', function() {
        var value = $(this).val();
        var formatted = value.replace(/[^\d\+\-\s]/g, '');
        $(this).val(formatted);
    });
    
    // File upload preview
    $('.file-upload').on('change', function() {
        var file = this.files[0];
        var preview = $(this).siblings('.file-preview');
        
        if (file && file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" class="img-fluid" style="max-height: 200px;">');
            };
            reader.readAsDataURL(file);
        } else {
            preview.html('<div class="text-muted">File: ' + file.name + '</div>');
        }
    });
    
    // Initialize datatables if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']]
        });
    }
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+S to save
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('form').submit();
        }
        
        // Ctrl+F to focus search
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            $('#search-input').focus();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            $('.modal').modal('hide');
        }
    });
    
    // Initialize tooltips for dynamic content
    $(document).on('DOMNodeInserted', function(e) {
        if ($(e.target).is('[data-bs-toggle="tooltip"]')) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
});

// Global functions
window.showNotification = function(message, type) {
    var alertClass = 'alert-' + type;
    var icon = type === 'success' ? 'fa-check-circle' : 
              type === 'error' ? 'fa-exclamation-circle' : 
              type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    var notification = $('<div>')
        .addClass('alert ' + alertClass + ' alert-dismissible fade show position-fixed')
        .css({
            'top': '20px',
            'right': '20px',
            'z-index': '9999',
            'min-width': '300px'
        })
        .html(
            '<div class="d-flex align-items-center">' +
            '<i class="fas ' + icon + ' me-2"></i>' +
            '<span>' + message + '</span>' +
            '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>' +
            '</div>'
        );
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
};

window.formatCurrency = function(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

window.formatDate = function(date, format) {
    format = format || 'DD/MM/YYYY';
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
