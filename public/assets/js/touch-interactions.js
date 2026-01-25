/**
 * Touch Interactions JavaScript
 * Enhanced touch support for mobile and tablet devices
 */

$(document).ready(function() {
    // Initialize touch interactions
    initTouchInteractions();
});

/**
 * Initialize all touch-friendly features
 */
function initTouchInteractions() {
    // Touch feedback
    initTouchFeedback();
    
    // Swipe gestures
    initSwipeGestures();
    
    // Touch-optimized scrolling
    initTouchScrolling();
    
    // Touch-friendly modals
    initTouchModals();
    
    // Touch-friendly tables
    initTouchTables();
    
    // Touch-friendly forms
    initTouchForms();
    
    // Touch-friendly navigation
    initTouchNavigation();
    
    // Touch-friendly drag and drop
    initTouchDragDrop();
    
    // Touch detection and optimization
    initTouchDetection();
}

/**
 * Touch feedback for buttons and interactive elements
 */
function initTouchFeedback() {
    // Add touch feedback classes
    $('.btn, .nav-link, .dropdown-item, .list-group-item, .card').addClass('touch-ripple');
    
    // Touch event handlers
    $('.touch-ripple').on('touchstart', function(e) {
        $(this).addClass('touch-pulse');
        
        // Create ripple effect
        createRippleEffect(e, $(this));
    });
    
    $('.touch-ripple').on('touchend', function(e) {
        var $element = $(this);
        setTimeout(function() {
            $element.removeClass('touch-pulse');
        }, 200);
    });
    
    // Prevent context menu on long press
    $('.touch-ripple').on('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });
}

/**
 * Create ripple effect for touch interactions
 */
function createRippleEffect(e, $element) {
    var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
    var rect = $element[0].getBoundingClientRect();
    var size = Math.max(rect.width, rect.height);
    var x = touch.clientX - rect.left - size / 2;
    var y = touch.clientY - rect.top - size / 2;
    
    var ripple = $('<div class="touch-ripple-effect"></div>');
    ripple.css({
        width: size,
        height: size,
        left: x,
        top: y
    });
    
    $element.append(ripple);
    
    setTimeout(function() {
        ripple.remove();
    }, 600);
}

/**
 * Swipe gesture support
 */
function initSwipeGestures() {
    var swipeStartX = 0;
    var swipeStartY = 0;
    var swipeEndX = 0;
    var swipeEndY = 0;
    var swipeThreshold = 50;
    
    // Sidebar swipe for mobile
    $('.sidebar').on('touchstart', function(e) {
        swipeStartX = e.originalEvent.touches[0].clientX;
        swipeStartY = e.originalEvent.touches[0].clientY;
    });
    
    $('.sidebar').on('touchend', function(e) {
        swipeEndX = e.originalEvent.changedTouches[0].clientX;
        swipeEndY = e.originalEvent.changedTouches[0].clientY;
        
        var swipeDistance = swipeEndX - swipeStartX;
        var verticalDistance = Math.abs(swipeEndY - swipeStartY);
        
        // Only handle horizontal swipes
        if (Math.abs(swipeDistance) > swipeThreshold && verticalDistance < 100) {
            if (swipeDistance > 0) {
                // Swipe right - open sidebar
                $('body').addClass('sidebar-open');
            } else {
                // Swipe left - close sidebar
                $('body').removeClass('sidebar-open');
            }
        }
    });
    
    // Table row swipe for actions
    $('.table tbody tr').on('touchstart', function(e) {
        swipeStartX = e.originalEvent.touches[0].clientX;
        $(this).data('swipe-start', swipeStartX);
    });
    
    $('.table tbody tr').on('touchend', function(e) {
        var $row = $(this);
        swipeEndX = e.originalEvent.changedTouches[0].clientX;
        swipeStartX = $row.data('swipe-start');
        
        var swipeDistance = swipeEndX - swipeStartX;
        
        // Swipe left to reveal actions
        if (swipeDistance < -swipeThreshold) {
            revealRowActions($row);
        } else if (swipeDistance > swipeThreshold) {
            hideRowActions($row);
        }
    });
}

/**
 * Reveal row actions on swipe
 */
function revealRowActions($row) {
    hideRowActions($('.table tbody tr')); // Hide other rows first
    
    $row.addClass('swipe-actions-visible');
    $row.find('.btn-group').show();
}

/**
 * Hide row actions
 */
function hideRowActions($row) {
    $row.removeClass('swipe-actions-visible');
    $row.find('.btn-group').hide();
}

/**
 * Touch-optimized scrolling
 */
function initTouchScrolling() {
    // Add smooth scrolling for touch devices
    $('.touch-scroll').each(function() {
        var $element = $(this);
        
        // Momentum scrolling
        $element.on('scroll', function() {
            if ($element.scrollTop() === 0) {
                $element.addClass('at-top');
            } else {
                $element.removeClass('at-top');
            }
            
            if ($element[0].scrollHeight - $element.scrollTop() === $element.outerHeight()) {
                $element.addClass('at-bottom');
            } else {
                $element.removeClass('at-bottom');
            }
        });
    });
    
    // Pull to refresh
    initPullToRefresh();
}

/**
 * Pull to refresh functionality
 */
function initPullToRefresh() {
    var pullToRefreshElement = $('.pull-to-refresh');
    var startY = 0;
    var currentY = 0;
    var pulling = false;
    var triggerDistance = 80;
    
    pullToRefreshElement.on('touchstart', function(e) {
        startY = e.originalEvent.touches[0].clientY;
        
        if (pullToRefreshElement.scrollTop() === 0) {
            pulling = true;
        }
    });
    
    pullToRefreshElement.on('touchmove', function(e) {
        if (!pulling) return;
        
        currentY = e.originalEvent.touches[0].clientY;
        var distance = currentY - startY;
        
        if (distance > 0 && distance < triggerDistance * 2) {
            pullToRefreshElement.css('transform', 'translateY(' + distance + 'px)');
            
            if (distance > triggerDistance) {
                pullToRefreshElement.addClass('pull-to-refresh-trigger');
            } else {
                pullToRefreshElement.removeClass('pull-to-refresh-trigger');
            }
        }
    });
    
    pullToRefreshElement.on('touchend', function(e) {
        if (!pulling) return;
        
        var distance = currentY - startY;
        
        if (distance > triggerDistance) {
            // Trigger refresh
            performRefresh();
        }
        
        // Reset
        pullToRefreshElement.css('transform', '');
        pullToRefreshElement.removeClass('pull-to-refresh-trigger');
        pulling = false;
    });
}

/**
 * Perform refresh action
 */
function performRefresh() {
    // Show loading indicator
    showLoading();
    
    // Reload current page data
    if (window.location.pathname.includes('dashboard')) {
        // Refresh dashboard data
        loadDashboardData();
    } else if (window.location.pathname.includes('companies')) {
        // Refresh companies list
        loadCompaniesData();
    }
    
    // Hide loading after delay
    setTimeout(function() {
        hideLoading();
        showToast('Data refreshed', 'success');
    }, 1000);
}

/**
 * Touch-friendly modals
 */
function initTouchModals() {
    // Touch-friendly modal backdrop
    $('.modal').on('touchstart', function(e) {
        if ($(e.target).hasClass('modal')) {
            // Close modal on backdrop touch
            $(this).modal('hide');
        }
    });
    
    // Touch-friendly modal drag
    $('.modal-header').each(function() {
        var $header = $(this);
        var $modal = $header.closest('.modal');
        var $dialog = $modal.find('.modal-dialog');
        var isDragging = false;
        var startX = 0;
        var startY = 0;
        var currentX = 0;
        var currentY = 0;
        
        $header.on('touchstart', function(e) {
            isDragging = true;
            startX = e.originalEvent.touches[0].clientX;
            startY = e.originalEvent.touches[0].clientY;
            $dialog.addClass('touch-dragging');
        });
        
        $(document).on('touchmove', function(e) {
            if (!isDragging) return;
            
            currentX = e.originalEvent.touches[0].clientX;
            currentY = e.originalEvent.touches[0].clientY;
            
            var deltaX = currentX - startX;
            var deltaY = currentY - startY;
            
            $dialog.css('transform', 'translate(' + deltaX + 'px, ' + deltaY + 'px)');
        });
        
        $(document).on('touchend', function(e) {
            if (!isDragging) return;
            
            isDragging = false;
            $dialog.removeClass('touch-dragging');
            
            // Reset position
            $dialog.css('transform', '');
        });
    });
}

/**
 * Touch-friendly tables
 */
function initTouchTables() {
    // Touch-friendly table sorting
    $('.table th.sortable').on('touchend', function(e) {
        e.preventDefault();
        var $th = $(this);
        var sortField = $th.data('sort');
        var currentSort = $th.hasClass('asc') ? 'asc' : 'desc';
        var newSort = currentSort === 'asc' ? 'desc' : 'asc';
        
        // Update sort indicators
        $('.table th').removeClass('asc desc');
        $th.addClass(newSort);
        
        // Sort table
        sortTable(sortField, newSort);
    });
    
    // Touch-friendly table row selection
    $('.table tbody tr').on('touchend', function(e) {
        if (!$(e.target).closest('.btn, .dropdown, .form-check').length) {
            var $row = $(this);
            var checkbox = $row.find('input[type="checkbox"]');
            
            if (checkbox.length) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                $row.toggleClass('selected');
            }
        }
    });
}

/**
 * Sort table data
 */
function sortTable(field, order) {
    // Implementation depends on your table structure
    console.log('Sorting table by', field, order);
    
    // Add actual sorting logic here
    var $table = $('.table');
    var $tbody = $table.find('tbody');
    var $rows = $tbody.find('tr');
    
    $rows.sort(function(a, b) {
        var aValue = $(a).find('td').eq(getColumnIndex(field)).text();
        var bValue = $(b).find('td').eq(getColumnIndex(field)).text();
        
        if (order === 'asc') {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });
    
    $tbody.empty().append($rows);
}

/**
 * Get column index by field name
 */
function getColumnIndex(field) {
    // Map field names to column indices
    var fieldMap = {
        'name': 0,
        'code': 1,
        'type': 2,
        'status': 3
    };
    
    return fieldMap[field] || 0;
}

/**
 * Touch-friendly forms
 */
function initTouchForms() {
    // Touch-friendly input focus
    $('.form-control, .form-select').on('touchstart', function() {
        $(this).focus();
    });
    
    // Touch-friendly form validation
    $('.form-control, .form-select').on('blur', function() {
        validateField($(this));
    });
    
    // Touch-friendly file input
    $('.form-control[type="file"]').on('touchend', function(e) {
        e.preventDefault();
        $(this).click();
    });
    
    // Touch-friendly number input with step buttons
    $('.form-control[type="number"]').each(function() {
        var $input = $(this);
        var $container = $input.wrap('<div class="touch-number-input"></div>').parent();
        
        var $minus = $('<button type="button" class="touch-number-btn minus">-</button>');
        var $plus = $('<button type="button" class="touch-number-btn plus">+</button>');
        
        $container.append($minus, $plus);
        
        $minus.on('touchend', function(e) {
            e.preventDefault();
            var value = parseFloat($input.val()) || 0;
            var step = parseFloat($input.attr('step')) || 1;
            $input.val(value - step);
        });
        
        $plus.on('touchend', function(e) {
            e.preventDefault();
            var value = parseFloat($input.val()) || 0;
            var step = parseFloat($input.attr('step')) || 1;
            $input.val(value + step);
        });
    });
}

/**
 * Touch-friendly navigation
 */
function initTouchNavigation() {
    // Touch-friendly dropdown menus
    $('.dropdown-toggle').on('touchend', function(e) {
        e.preventDefault();
        var $dropdown = $(this).closest('.dropdown');
        
        if ($dropdown.hasClass('show')) {
            $dropdown.removeClass('show');
            $dropdown.find('.dropdown-menu').removeClass('show');
        } else {
            // Close other dropdowns
            $('.dropdown.show').removeClass('show').find('.dropdown-menu').removeClass('show');
            
            $dropdown.addClass('show');
            $dropdown.find('.dropdown-menu').addClass('show');
        }
    });
    
    // Touch outside to close dropdowns
    $(document).on('touchstart', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown.show').removeClass('show').find('.dropdown-menu').removeClass('show');
        }
    });
    
    // Touch-friendly sidebar toggle
    $('.navbar-toggler').on('touchend', function(e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-open');
    });
}

/**
 * Touch-friendly drag and drop
 */
function initTouchDragDrop() {
    var draggedElement = null;
    var touchOffset = { x: 0, y: 0 };
    
    $('.draggable').each(function() {
        var $element = $(this);
        
        $element.on('touchstart', function(e) {
            draggedElement = $element;
            var touch = e.originalEvent.touches[0];
            var rect = $element[0].getBoundingClientRect();
            touchOffset.x = touch.clientX - rect.left;
            touchOffset.y = touch.clientY - rect.top;
            
            $element.addClass('dragging');
            $element.css('position', 'fixed');
            $element.css('z-index', '1000');
        });
        
        $element.on('touchmove', function(e) {
            if (!draggedElement) return;
            
            e.preventDefault();
            var touch = e.originalEvent.touches[0];
            draggedElement.css({
                left: (touch.clientX - touchOffset.x) + 'px',
                top: (touch.clientY - touchOffset.y) + 'px'
            });
        });
        
        $element.on('touchend', function(e) {
            if (!draggedElement) return;
            
            var touch = e.originalEvent.changedTouches[0];
            var elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);
            var $dropZone = $(elementBelow).closest('.droppable');
            
            if ($dropZone.length) {
                $dropZone.addClass('drop-hover');
                handleDrop($dropZone, draggedElement);
                
                setTimeout(function() {
                    $dropZone.removeClass('drop-hover');
                }, 500);
            }
            
            // Reset dragged element
            draggedElement.removeClass('dragging');
            draggedElement.css({
                position: '',
                left: '',
                top: '',
                'z-index': ''
            });
            
            draggedElement = null;
        });
    });
}

/**
 * Handle drop action
 */
function handleDrop($dropZone, $draggedElement) {
    var itemId = $draggedElement.data('id');
    var zoneId = $dropZone.data('zone');
    
    // Visual feedback
    $dropZone.addClass('drop-success');
    showToast('Item moved successfully', 'success');
    
    // Process drop via AJAX
    $.ajax({
        url: 'index.php?page=drag-drop&action=process',
        type: 'POST',
        data: {
            item_id: itemId,
            zone_id: zoneId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                console.log('Drop processed successfully');
            } else {
                showToast('Failed to move item', 'error');
            }
        },
        error: function() {
            showToast('Error processing drop', 'error');
        }
    });
    
    setTimeout(function() {
        $dropZone.removeClass('drop-success');
    }, 1000);
}

/**
 * Touch detection and optimization
 */
function initTouchDetection() {
    var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    
    if (isTouchDevice) {
        $('body').addClass('touch-device');
        
        // Add touch-friendly CSS
        $('head').append('<link rel="stylesheet" href="' + BASE_URL + '/public/assets/css/touch-friendly.css">');
        
        // Optimize for touch
        optimizeForTouch();
        
        // Prevent double-tap zoom
        preventDoubleTapZoom();
        
        // Handle orientation changes
        handleOrientationChange();
    }
}

/**
 * Optimize interface for touch devices
 */
function optimizeForTouch() {
    // Increase tap targets
    $('.btn-sm').addClass('btn-touch');
    
    // Add touch-friendly classes
    $('.form-control, .form-select').addClass('touch-input');
    
    // Optimize tables for touch
    $('.table').addClass('touch-table');
    
    // Add touch scrolling
    $('.sidebar, .main-content').addClass('touch-scroll');
    
    // Disable hover effects on touch devices
    $('body').addClass('no-hover');
}

/**
 * Prevent double-tap zoom
 */
function preventDoubleTapZoom() {
    var lastTouchEnd = 0;
    
    $(document).on('touchend', function(e) {
        var now = Date.now();
        
        if (now - lastTouchEnd <= 300) {
            e.preventDefault();
        }
        
        lastTouchEnd = now;
    });
}

/**
 * Handle orientation changes
 */
function handleOrientationChange() {
    window.addEventListener('orientationchange', function() {
        // Adjust layout for new orientation
        setTimeout(function() {
            adjustLayoutForOrientation();
        }, 100);
    });
    
    window.addEventListener('resize', function() {
        if (window.orientation !== undefined) {
            adjustLayoutForOrientation();
        }
    });
}

/**
 * Adjust layout for orientation
 */
function adjustLayoutForOrientation() {
    var orientation = window.orientation || (window.innerWidth > window.innerHeight ? 90 : 0);
    
    if (Math.abs(orientation) === 90) {
        // Landscape
        $('body').addClass('landscape').removeClass('portrait');
    } else {
        // Portrait
        $('body').addClass('portrait').removeClass('landscape');
    }
    
    // Adjust modal positions
    $('.modal.show').each(function() {
        var $modal = $(this);
        $modal.modal('handleUpdate');
    });
}

/**
 * Utility functions
 */
function showToast(message, type) {
    if (typeof showNotification === 'function') {
        showNotification(message, type);
    }
}

function showLoading() {
    if (typeof showLoadingIndicator === 'function') {
        showLoadingIndicator();
    }
}

function hideLoading() {
    if (typeof hideLoadingIndicator === 'function') {
        hideLoadingIndicator();
    }
}

function loadDashboardData() {
    // Reload dashboard stats
    if (typeof loadDashboardStats === 'function') {
        loadDashboardStats();
    }
}

function loadCompaniesData() {
    // Reload companies list
    if (typeof refreshCompaniesTable === 'function') {
        refreshCompaniesTable();
    }
}

function validateField($field) {
    // Basic validation
    var value = $field.val().trim();
    var isValid = true;
    
    $field.removeClass('is-valid is-invalid');
    
    if ($field.prop('required') && !value) {
        isValid = false;
    }
    
    if ($field.attr('type') === 'email' && value && !isValidEmail(value)) {
        isValid = false;
    }
    
    if (isValid) {
        $field.addClass('is-valid');
    } else {
        $field.addClass('is-invalid');
    }
    
    return isValid;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Export for global access
window.initTouchInteractions = initTouchInteractions;
window.createRippleEffect = createRippleEffect;
window.handleDrop = handleDrop;
