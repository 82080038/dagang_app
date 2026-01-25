/**
 * Simple Application JavaScript
 * Basic functionality without complex selectors
 */

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Simple delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var title = $(this).data('title') || 'Hapus Data';
        var message = $(this).data('message') || 'Apakah Anda yakin ingin menghapus data ini?';
        
        if (confirm(title + '\n\n' + message)) {
            window.location.href = url;
        }
    });
    
    // Toast notification function
    window.showToast = function(type, message) {
        var toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        var toastElement = $(toastHtml);
        $('#globalToastContainer').append(toastElement);
        
        var toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
    };
    
    // Handle flash messages
    var alerts = $('.alert[data-flash="true"]');
    alerts.each(function() {
        var type = 'primary';
        if ($(this).hasClass('alert-success')) type = 'success';
        else if ($(this).hasClass('alert-danger')) type = 'error';
        else if ($(this).hasClass('alert-warning')) type = 'warning';
        
        var message = $(this).text().trim();
        if (message) showToast(type, message);
        $(this).remove();
    });
});

// Modal confirmation function
window.Modal = {
    confirm: function(title, message, callback) {
        if (confirm(title + '\n\n' + message)) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    },
    error: function(message) {
        alert('Error: ' + message);
    },
    success: function(message) {
        alert('Success: ' + message);
    },
    warning: function(message) {
        alert('Warning: ' + message);
    },
    info: function(message) {
        alert('Info: ' + message);
    }
};

// Toast notification system
window.Toast = {
    success: function(message, options) {
        showToast('success', message);
    },
    error: function(message, options) {
        showToast('error', message);
    },
    warning: function(message, options) {
        showToast('warning', message);
    },
    info: function(message, options) {
        showToast('info', message);
    }
};
