/**
 * Enhanced jQuery/Ajax and DOM Manipulation
 * Advanced interactions and real-time updates
 */

$(document).ready(function() {
    // Initialize enhanced features
    initEnhancedFeatures();
});

/**
 * Initialize all enhanced jQuery/Ajax features
 */
function initEnhancedFeatures() {
    // Real-time search with debouncing
    initRealTimeSearch();
    
    // Dynamic form validation
    initDynamicValidation();
    
    // Advanced table interactions
    initAdvancedTable();
    
    // Real-time notifications
    initRealTimeNotifications();
    
    // Keyboard shortcuts
    initKeyboardShortcuts();
    
    // Drag and drop functionality
    initDragAndDrop();
    
    // Auto-save functionality
    initAutoSave();
    
    // Live data updates
    initLiveDataUpdates();
    
    // Advanced modals
    initAdvancedModals();
    
    // Smart tooltips
    initSmartTooltips();
}

/**
 * Real-time search with debouncing
 */
function initRealTimeSearch() {
    var searchInputs = $('.search-input');
    
    searchInputs.each(function() {
        var $input = $(this);
        var targetTable = $input.data('target');
        var searchUrl = $input.data('url');
        
        if (targetTable && searchUrl) {
            $input.on('input', debounce(function() {
                var query = $(this).val();
                performRealTimeSearch(targetTable, searchUrl, query);
            }, 300));
        }
    });
}

/**
 * Perform real-time search
 */
function performRealTimeSearch(tableId, url, query) {
    var $table = $('#' + tableId);
    var $tbody = $table.find('tbody');
    
    // Show loading state
    $tbody.addClass('loading');
    $tbody.html('<tr><td colspan="100%" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</td></tr>');
    
    $.ajax({
        url: url,
        type: 'GET',
        data: { search: query },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderSearchResults($tbody, response.data);
            } else {
                $tbody.html('<tr><td colspan="100%" class="text-center text-danger">Search failed</td></tr>');
            }
        },
        error: function() {
            $tbody.html('<tr><td colspan="100%" class="text-center text-danger">Search error</td></tr>');
        },
        complete: function() {
            $tbody.removeClass('loading');
        }
    });
}

/**
 * Render search results with animations
 */
function renderSearchResults($tbody, data) {
    $tbody.empty();
    
    if (data.length === 0) {
        $tbody.html('<tr><td colspan="100%" class="text-center text-muted">No results found</td></tr>');
        return;
    }
    
    data.forEach(function(item, index) {
        var $row = createTableRow(item);
        $row.hide();
        $tbody.append($row);
        
        // Stagger animation
        setTimeout(function() {
            $row.fadeIn(200);
        }, index * 50);
    });
}

/**
 * Dynamic form validation
 */
function initDynamicValidation() {
    var forms = $('.dynamic-form');
    
    forms.each(function() {
        var $form = $(this);
        
        // Real-time validation
        $form.find('input, select, textarea').on('blur change', function() {
            validateField($(this));
        });
        
        // Form submission with validation
        $form.on('submit', function(e) {
            if (!validateForm($form)) {
                e.preventDefault();
                showFormErrors($form);
            }
        });
    });
}

/**
 * Validate individual field
 */
function validateField($field) {
    var value = $field.val();
    var type = $field.attr('type');
    var required = $field.prop('required');
    var isValid = true;
    var message = '';
    
    // Remove previous validation states
    $field.removeClass('is-valid is-invalid');
    $field.siblings('.invalid-feedback, .valid-feedback').remove();
    
    // Required validation
    if (required && !value) {
        isValid = false;
        message = 'This field is required';
    }
    
    // Email validation
    if (type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        message = 'Please enter a valid email address';
    }
    
    // Number validation
    if (type === 'number' && value && !isValidNumber(value)) {
        isValid = false;
        message = 'Please enter a valid number';
    }
    
    // Phone validation
    if ($field.hasClass('phone') && value && !isValidPhone(value)) {
        isValid = false;
        message = 'Please enter a valid phone number';
    }
    
    // Show validation feedback
    if (isValid) {
        $field.addClass('is-valid');
        if (message) {
            $field.after('<div class="valid-feedback">' + message + '</div>');
        }
    } else {
        $field.addClass('is-invalid');
        $field.after('<div class="invalid-feedback">' + message + '</div>');
    }
    
    return isValid;
}

/**
 * Advanced table interactions
 */
function initAdvancedTable() {
    var tables = $('.advanced-table');
    
    tables.each(function() {
        var $table = $(this);
        
        // Sortable columns
        initSortableColumns($table);
        
        // Filterable columns
        initFilterableColumns($table);
        
        // Row selection
        initRowSelection($table);
        
        // Bulk actions
        initBulkActions($table);
        
        // Inline editing
        initInlineEditing($table);
    });
}

/**
 * Initialize sortable columns
 */
function initSortableColumns($table) {
    $table.find('.sortable').on('click', function() {
        var $th = $(this);
        var column = $th.data('column');
        var currentOrder = $th.data('order') || 'asc';
        var newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
        
        // Update sort indicators
        $table.find('.sortable').removeClass('asc desc').data('order', null);
        $th.addClass(newOrder).data('order', newOrder);
        
        // Sort table
        sortTable($table, column, newOrder);
    });
}

/**
 * Sort table data
 */
function sortTable($table, column, order) {
    var $tbody = $table.find('tbody');
    var rows = $tbody.find('tr').get();
    
    rows.sort(function(a, b) {
        var aValue = $(a).find('td').eq($(b).index()).text();
        var bValue = $(b).find('td').eq($(a).index()).text();
        
        if (order === 'asc') {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });
    
    $tbody.empty();
    rows.forEach(function(row) {
        $tbody.append(row);
    });
}

/**
 * Initialize row selection
 */
function initRowSelection($table) {
    var $checkboxes = $table.find('.row-checkbox');
    var $selectAll = $table.find('.select-all-checkbox');
    
    // Individual row selection
    $checkboxes.on('change', function() {
        updateBulkActions($table);
    });
    
    // Select all functionality
    $selectAll.on('change', function() {
        var isChecked = $(this).prop('checked');
        $checkboxes.prop('checked', isChecked);
        updateBulkActions($table);
    });
}

/**
 * Update bulk actions visibility
 */
function updateBulkActions($table) {
    var $checkedBoxes = $table.find('.row-checkbox:checked');
    var $bulkActions = $table.closest('.table-container').find('.bulk-actions');
    
    if ($checkedBoxes.length > 0) {
        $bulkActions.show();
        $bulkActions.find('.selected-count').text($checkedBoxes.length);
    } else {
        $bulkActions.hide();
    }
}

/**
 * Real-time notifications
 */
function initRealTimeNotifications() {
    // Check for notifications every 30 seconds
    setInterval(function() {
        checkNotifications();
    }, 30000);
    
    // Notification sound
    initNotificationSound();
}

/**
 * Check for new notifications
 */
function checkNotifications() {
    $.ajax({
        url: 'index.php?page=notifications&action=check',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data.notifications.length > 0) {
                showNotifications(response.data.notifications);
            }
        }
    });
}

/**
 * Show notifications with animations
 */
function showNotifications(notifications) {
    notifications.forEach(function(notification, index) {
        setTimeout(function() {
            showNotification(notification.message, notification.type, 5000);
            
            // Play sound if enabled
            if (notification.sound) {
                playNotificationSound();
            }
        }, index * 1000);
    });
}

/**
 * Keyboard shortcuts
 */
function initKeyboardShortcuts() {
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S for save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            var $form = $('.modal.show form').first();
            if ($form.length) {
                $form.submit();
            }
        }
        
        // Ctrl/Cmd + N for new
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            var $newBtn = $('.btn-new').first();
            if ($newBtn.length) {
                $newBtn.click();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            var $modal = $('.modal.show').last();
            if ($modal.length) {
                $modal.modal('hide');
            }
        }
        
        // Ctrl/Cmd + F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            var $searchInput = $('.search-input').first();
            if ($searchInput.length) {
                $searchInput.focus();
            }
        }
    });
}

/**
 * Drag and drop functionality
 */
function initDragAndDrop() {
    var $draggableItems = $('.draggable-item');
    var $dropZones = $('.drop-zone');
    
    $draggableItems.draggable({
        revert: 'invalid',
        helper: 'clone',
        opacity: 0.7
    });
    
    $dropZones.droppable({
        accept: '.draggable-item',
        drop: function(event, ui) {
            handleDrop($(this), ui.draggable);
        },
        over: function() {
            $(this).addClass('drop-over');
        },
        out: function() {
            $(this).removeClass('drop-over');
        }
    });
}

/**
 * Handle drop event
 */
function handleDrop($dropZone, $draggableItem) {
    var itemId = $draggableItem.data('id');
    var zoneId = $dropZone.data('zone');
    
    $.ajax({
        url: 'index.php?page=drag-drop&action=drop',
        type: 'POST',
        data: {
            item_id: itemId,
            zone_id: zoneId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $dropZone.append($draggableItem.clone());
                showNotification('Item moved successfully', 'success');
            } else {
                showNotification('Failed to move item', 'error');
            }
        }
    });
}

/**
 * Auto-save functionality
 */
function initAutoSave() {
    var $forms = $('.auto-save-form');
    
    $forms.each(function() {
        var $form = $(this);
        var saveUrl = $form.data('save-url');
        var saveInterval = $form.data('interval') || 30000; // 30 seconds default
        
        if (saveUrl) {
            // Auto-save on interval
            setInterval(function() {
                autoSaveForm($form, saveUrl);
            }, saveInterval);
            
            // Auto-save on field change
            $form.find('input, select, textarea').on('change', function() {
                autoSaveForm($form, saveUrl);
            });
        }
    });
}

/**
 * Auto-save form data
 */
function autoSaveForm($form, url) {
    var formData = $form.serialize();
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $form.addClass('auto-saved');
                setTimeout(function() {
                    $form.removeClass('auto-saved');
                }, 2000);
            }
        }
    });
}

/**
 * Live data updates
 */
function initLiveDataUpdates() {
    var $liveElements = $('.live-update');
    
    $liveElements.each(function() {
        var $element = $(this);
        var updateUrl = $element.data('update-url');
        var updateInterval = $element.data('interval') || 10000; // 10 seconds default
        
        if (updateUrl) {
            setInterval(function() {
                updateLiveData($element, updateUrl);
            }, updateInterval);
        }
    });
}

/**
 * Update live data
 */
function updateLiveData($element, url) {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var oldValue = $element.text();
                var newValue = response.data.value;
                
                if (oldValue !== newValue) {
                    $element.addClass('updated');
                    $element.text(newValue);
                    
                    setTimeout(function() {
                        $element.removeClass('updated');
                    }, 1000);
                }
            }
        }
    });
}

/**
 * Advanced modals
 */
function initAdvancedModals() {
    // Dynamic modal loading
    $(document).on('click', '[data-toggle="dynamic-modal"]', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var modalUrl = $btn.data('url');
        var modalSize = $btn.data('size') || 'md';
        
        loadDynamicModal(modalUrl, modalSize);
    });
    
    // Modal keyboard navigation
    $(document).on('keydown', '.modal', function(e) {
        if (e.key === 'Tab') {
            handleModalTabNavigation(e);
        }
    });
}

/**
 * Load dynamic modal content
 */
function loadDynamicModal(url, size) {
    var $modal = $('#dynamicModal');
    
    if ($modal.length === 0) {
        $modal = $('<div id="dynamicModal" class="modal fade" tabindex="-1">' +
            '<div class="modal-dialog modal-' + size + '">' +
            '<div class="modal-content">' +
            '<div class="modal-body text-center">' +
            '<i class="fas fa-spinner fa-spin fa-2x"></i>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>');
        $('body').append($modal);
    }
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(response) {
            $modal.find('.modal-content').html(response);
            $modal.modal('show');
        },
        error: function() {
            $modal.find('.modal-body').html('<div class="alert alert-danger">Failed to load content</div>');
            $modal.modal('show');
        }
    });
}

/**
 * Smart tooltips
 */
function initSmartTooltips() {
    var $tooltips = $('[data-toggle="smart-tooltip"]');
    
    $tooltips.each(function() {
        var $element = $(this);
        var tooltipType = $element.data('tooltip-type') || 'hover';
        var tooltipContent = $element.data('content');
        
        if (tooltipType === 'hover') {
            $element.on('mouseenter', function() {
                showSmartTooltip($element, tooltipContent);
            }).on('mouseleave', function() {
                hideSmartTooltip();
            });
        } else if (tooltipType === 'click') {
            $element.on('click', function(e) {
                e.preventDefault();
                showSmartTooltip($element, tooltipContent);
            });
        }
    });
}

/**
 * Show smart tooltip
 */
function showSmartTooltip($element, content) {
    hideSmartTooltip(); // Hide any existing tooltip
    
    var $tooltip = $('<div class="smart-tooltip">' + content + '</div>');
    var position = $element.offset();
    
    $tooltip.css({
        position: 'absolute',
        top: position.top - $tooltip.outerHeight() - 10,
        left: position.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2),
        zIndex: 9999
    });
    
    $('body').append($tooltip);
    $tooltip.fadeIn(200);
}

/**
 * Hide smart tooltip
 */
function hideSmartTooltip() {
    $('.smart-tooltip').fadeOut(200, function() {
        $(this).remove();
    });
}

/**
 * Utility functions
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

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidNumber(number) {
    return !isNaN(number) && !isNaN(parseFloat(number));
}

function isValidPhone(phone) {
    return /^[+]?[\d\s-()]+$/.test(phone);
}

function createTableRow(data) {
    var $row = $('<tr></tr>');
    
    // Add cells based on data structure
    Object.keys(data).forEach(function(key) {
        var value = data[key];
        var $cell = $('<td></td>');
        
        if (key === 'status') {
            $cell.html('<span class="badge bg-' + (value === 'active' ? 'success' : 'secondary') + '">' + value + '</span>');
        } else if (key === 'actions') {
            $cell.html(value);
        } else {
            $cell.text(value);
        }
        
        $row.append($cell);
    });
    
    return $row;
}

function playNotificationSound() {
    // Create audio element and play notification sound
    var audio = new Audio('/assets/sounds/notification.mp3');
    audio.play().catch(function(e) {
        console.log('Could not play notification sound:', e);
    });
}

// Export functions to global scope
window.initEnhancedFeatures = initEnhancedFeatures;
window.performRealTimeSearch = performRealTimeSearch;
window.validateField = validateField;
window.showNotifications = showNotifications;
window.autoSaveForm = autoSaveForm;
window.updateLiveData = updateLiveData;
window.loadDynamicModal = loadDynamicModal;
window.showSmartTooltip = showSmartTooltip;
