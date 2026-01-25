/**
 * Advanced DOM Manipulation Library
 * Enhanced jQuery/Ajax interactions with animations and effects
 */

$(document).ready(function() {
    // Initialize DOM manipulation features
    initDOMManipulation();
});

/**
 * Initialize all DOM manipulation features
 */
function initDOMManipulation() {
    // Smooth scrolling
    initSmoothScrolling();
    
    // Animated counters
    initAnimatedCounters();
    
    // Progress bars
    initProgressBars();
    
    // Dynamic charts
    initDynamicCharts();
    
    // Interactive forms
    initInteractiveForms();
    
    // Drag and drop
    initDragAndDrop();
    
    // Keyboard navigation
    initKeyboardNavigation();
    
    // Responsive utilities
    initResponsiveUtilities();
    
    // Animation utilities
    initAnimationUtilities();
}

/**
 * Smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 70
            }, 800, 'easeInOutQuart');
        }
    });
}

/**
 * Animated counters
 */
function initAnimatedCounters() {
    $('.counter').each(function() {
        var $counter = $(this);
        var target = parseInt($counter.data('target')) || 0;
        var duration = parseInt($counter.data('duration')) || 2000;
        var delay = parseInt($counter.data('delay')) || 0;
        
        setTimeout(function() {
            animateCounter($counter, target, duration);
        }, delay);
    });
}

/**
 * Animate counter value
 */
function animateCounter($element, target, duration) {
    var start = 0;
    var increment = target / (duration / 16);
    var current = start;
    
    var timer = setInterval(function() {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        
        $element.text(Math.floor(current).toLocaleString());
    }, 16);
}

/**
 * Progress bars with animations
 */
function initProgressBars() {
    $('.progress-bar').each(function() {
        var $bar = $(this);
        var target = parseInt($bar.data('target')) || 0;
        var duration = parseInt($bar.data('duration')) || 1000;
        var delay = parseInt($bar.data('delay')) || 0;
        
        $bar.css('width', '0%');
        
        setTimeout(function() {
            $bar.animate({
                width: target + '%'
            }, duration, 'easeInOutQuart');
        }, delay);
    });
}

/**
 * Dynamic charts
 */
function initDynamicCharts() {
    $('.chart-container').each(function() {
        var $container = $(this);
        var chartType = $container.data('chart-type');
        var dataSource = $container.data('source');
        
        if (chartType && dataSource) {
            loadChart($container, chartType, dataSource);
        }
    });
}

/**
 * Load chart data
 */
function loadChart($container, type, source) {
    $.ajax({
        url: source,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderChart($container, type, response.data);
            }
        }
    });
}

/**
 * Render chart
 */
function renderChart($container, type, data) {
    var canvas = $('<canvas></canvas>');
    $container.empty().append(canvas);
    
    var ctx = canvas[0].getContext('2d');
    
    // Simple chart rendering (you can replace with Chart.js or similar)
    if (type === 'line') {
        drawLineChart(ctx, data);
    } else if (type === 'bar') {
        drawBarChart(ctx, data);
    } else if (type === 'pie') {
        drawPieChart(ctx, data);
    }
}

/**
 * Interactive forms with validation
 */
function initInteractiveForms() {
    $('.interactive-form').each(function() {
        var $form = $(this);
        
        // Real-time validation
        $form.find('input, select, textarea').on('input blur', function() {
            validateField($(this));
        });
        
        // Form submission
        $form.on('submit', function(e) {
            if (!validateForm($form)) {
                e.preventDefault();
                showFormErrors($form);
            }
        });
        
        // Auto-save
        if ($form.hasClass('auto-save')) {
            initAutoSave($form);
        }
    });
}

/**
 * Enhanced drag and drop
 */
function initDragAndDrop() {
    $('.draggable').draggable({
        revert: 'invalid',
        helper: 'clone',
        opacity: 0.7,
        start: function(event, ui) {
            $(ui.helper).addClass('dragging');
        },
        stop: function(event, ui) {
            $(ui.helper).removeClass('dragging');
        }
    });
    
    $('.droppable').droppable({
        accept: '.draggable',
        hoverClass: 'drop-hover',
        drop: function(event, ui) {
            handleDrop($(this), ui.draggable);
        }
    });
}

/**
 * Handle drop event
 */
function handleDrop($dropZone, $draggable) {
    var itemId = $draggable.data('id');
    var zoneId = $dropZone.data('zone');
    
    // Add visual feedback
    $dropZone.addClass('drop-success');
    setTimeout(function() {
        $dropZone.removeClass('drop-success');
    }, 1000);
    
    // Process drop
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
                showNotification('Item moved successfully', 'success');
            } else {
                showNotification('Failed to move item', 'error');
            }
        }
    });
}

/**
 * Keyboard navigation
 */
function initKeyboardNavigation() {
    $(document).on('keydown', function(e) {
        // Escape key
        if (e.key === 'Escape') {
            closeAllModals();
        }
        
        // Ctrl/Cmd + S for save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveCurrentForm();
        }
        
        // Ctrl/Cmd + F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            focusSearchInput();
        }
        
        // Arrow keys for navigation
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            navigateList(e.key === 'ArrowDown' ? 1 : -1);
        }
        
        // Enter for selection
        if (e.key === 'Enter') {
            selectCurrentItem();
        }
    });
}

/**
 * Responsive utilities
 */
function initResponsiveUtilities() {
    // Handle window resize
    $(window).on('resize', debounce(function() {
        handleResize();
    }, 250));
    
    // Initialize responsive tables
    initResponsiveTables();
    
    // Initialize responsive navigation
    initResponsiveNav();
}

/**
 * Handle window resize
 */
function handleResize() {
    var width = $(window).width();
    
    // Adjust layout based on screen size
    if (width < 768) {
        $('body').addClass('mobile-view');
    } else {
        $('body').removeClass('mobile-view');
    }
    
    // Reinitialize components
    reinitializeComponents();
}

/**
 * Animation utilities
 */
function initAnimationUtilities() {
    // Intersection Observer for scroll animations
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('animate-in');
                }
            });
        });
        
        $('.animate-on-scroll').each(function() {
            observer.observe(this);
        });
    }
    
    // Hover effects
    $('.hover-effect').on('mouseenter', function() {
        $(this).addClass('hover-active');
    }).on('mouseleave', function() {
        $(this).removeClass('hover-active');
    });
    
    // Click effects
    $('.click-effect').on('click', function(e) {
        createRippleEffect(e, $(this));
    });
}

/**
 * Create ripple effect
 */
function createRippleEffect(e, $element) {
    var ripple = $('<span class="ripple"></span>');
    var rect = $element[0].getBoundingClientRect();
    var size = Math.max(rect.width, rect.height);
    var x = e.clientX - rect.left - size / 2;
    var y = e.clientY - rect.top - size / 2;
    
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
 * Advanced modal management
 */
var ModalManager = {
    stack: [],
    
    show: function(modalId, options) {
        options = options || {};
        
        var $modal = $('#' + modalId);
        if ($modal.length === 0) {
            console.error('Modal not found:', modalId);
            return;
        }
        
        // Add to stack
        this.stack.push(modalId);
        
        // Show modal
        $modal.modal('show');
        
        // Apply options
        if (options.backdrop) {
            $modal.data('bs.modal')._config.backdrop = options.backdrop;
        }
        
        if (options.keyboard === false) {
            $modal.data('bs.modal')._config.keyboard = false;
        }
        
        // Bind events
        $modal.on('hidden.bs.modal', function() {
            ModalManager.stack.pop();
        });
    },
    
    hide: function(modalId) {
        var $modal = $('#' + modalId);
        if ($modal.length) {
            $modal.modal('hide');
        }
    },
    
    hideAll: function() {
        $('.modal.show').modal('hide');
        this.stack = [];
    },
    
    getCurrent: function() {
        return this.stack[this.stack.length - 1];
    }
};

/**
 * Advanced table utilities
 */
var TableUtils = {
    sort: function($table, columnIndex, order) {
        var $tbody = $table.find('tbody');
        var rows = $tbody.find('tr').get();
        
        rows.sort(function(a, b) {
            var aValue = $(a).find('td').eq(columnIndex).text();
            var bValue = $(b).find('td').eq(columnIndex).text();
            
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
    },
    
    filter: function($table, searchTerm) {
        var $tbody = $table.find('tbody');
        var $rows = $tbody.find('tr');
        
        $rows.each(function() {
            var $row = $(this);
            var text = $row.text().toLowerCase();
            var matches = text.indexOf(searchTerm.toLowerCase()) !== -1;
            
            $row.toggle(matches);
        });
    },
    
    paginate: function($table, itemsPerPage) {
        var $tbody = $table.find('tbody');
        var $rows = $tbody.find('tr');
        var totalPages = Math.ceil($rows.length / itemsPerPage);
        var currentPage = 1;
        
        // Create pagination
        var $pagination = $('<div class="pagination"></div>');
        
        for (var i = 1; i <= totalPages; i++) {
            var $page = $('<button class="btn btn-outline-primary btn-sm me-1">' + i + '</button>');
            $page.on('click', function() {
                currentPage = parseInt($(this).text());
                showPage(currentPage);
            });
            $pagination.append($page);
        }
        
        $table.after($pagination);
        
        function showPage(page) {
            var start = (page - 1) * itemsPerPage;
            var end = start + itemsPerPage;
            
            $rows.hide();
            $rows.slice(start, end).show();
            
            // Update pagination buttons
            $pagination.find('button').removeClass('active');
            $pagination.find('button').eq(page - 1).addClass('active');
        }
        
        showPage(1);
    }
};

/**
 * Form utilities
 */
var FormUtils = {
    serialize: function($form) {
        return $form.serialize();
    },
    
    serializeObject: function($form) {
        var data = {};
        $form.serializeArray().map(function(x) {
            data[x.name] = x.value;
        });
        return data;
    },
    
    reset: function($form) {
        $form[0].reset();
        $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        $form.find('.valid-feedback, .invalid-feedback').remove();
    },
    
    validate: function($form) {
        var isValid = true;
        
        $form.find('input, select, textarea').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    autoSave: function($form, url, interval) {
        interval = interval || 30000; // 30 seconds default
        
        var saveTimer = setInterval(function() {
            var data = FormUtils.serializeObject($form);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
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
        }, interval);
        
        // Save on field change
        $form.find('input, select, textarea').on('change', function() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function() {
                var data = FormUtils.serializeObject($form);
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
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
            }, 1000);
        });
    }
};

/**
 * Animation utilities
 */
var AnimationUtils = {
    fadeIn: function($element, duration, callback) {
        duration = duration || 300;
        $element.css('opacity', 0).show().animate({ opacity: 1 }, duration, callback);
    },
    
    fadeOut: function($element, duration, callback) {
        duration = duration || 300;
        $element.animate({ opacity: 0 }, duration, function() {
            $(this).hide();
            if (callback) callback();
        });
    },
    
    slideIn: function($element, direction, duration, callback) {
        duration = duration || 300;
        var start = direction === 'up' ? '100%' : '-100%';
        $element.css({
            opacity: 0,
            transform: 'translateY(' + start + ')'
        }).show().animate({
            opacity: 1,
            transform: 'translateY(0)'
        }, duration, callback);
    },
    
    slideOut: function($element, direction, duration, callback) {
        duration = duration || 300;
        var end = direction === 'up' ? '-100%' : '100%';
        $element.animate({
            opacity: 0,
            transform: 'translateY(' + end + ')'
        }, duration, function() {
            $(this).hide();
            if (callback) callback();
        });
    },
    
    bounce: function($element, duration, callback) {
        duration = duration || 600;
        $element.addClass('animate-bounce');
        setTimeout(function() {
            $element.removeClass('animate-bounce');
            if (callback) callback();
        }, duration);
    },
    
    pulse: function($element, duration, callback) {
        duration = duration || 1000;
        $element.addClass('animate-pulse');
        setTimeout(function() {
            $element.removeClass('animate-pulse');
            if (callback) callback();
        }, duration);
    }
};

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

function validateField($field) {
    var value = $field.val();
    var type = $field.attr('type');
    var required = $field.prop('required');
    var isValid = true;
    
    $field.removeClass('is-valid is-invalid');
    $field.siblings('.valid-feedback, .invalid-feedback').remove();
    
    if (required && !value) {
        isValid = false;
    }
    
    if (type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
    }
    
    if (type === 'number' && value && !isValidNumber(value)) {
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

function isValidNumber(number) {
    return !isNaN(number) && !isNaN(parseFloat(number));
}

function closeAllModals() {
    $('.modal.show').modal('hide');
}

function saveCurrentForm() {
    var $form = $('.modal.show form').first();
    if ($form.length) {
        $form.submit();
    }
}

function focusSearchInput() {
    $('.search-input').first().focus();
}

function navigateList(direction) {
    var $items = $('.list-item');
    var $current = $items.filter('.active');
    var index = $items.index($current);
    var newIndex = index + direction;
    
    if (newIndex >= 0 && newIndex < $items.length) {
        $current.removeClass('active');
        $items.eq(newIndex).addClass('active');
    }
}

function selectCurrentItem() {
    $('.list-item.active').click();
}

function initResponsiveTables() {
    $('.responsive-table').each(function() {
        var $table = $(this);
        
        if ($(window).width() < 768) {
            convertToCards($table);
        }
    });
}

function convertToCards($table) {
    var $thead = $table.find('thead');
    var $tbody = $table.find('tbody');
    var headers = [];
    
    $thead.find('th').each(function() {
        headers.push($(this).text());
    });
    
    var $cards = $('<div class="row"></div>');
    
    $tbody.find('tr').each(function() {
        var $row = $(this);
        var $card = $('<div class="col-md-6 col-lg-4 mb-3"><div class="card"></div></div>');
        var $cardBody = $card.find('.card-body');
        
        $row.find('td').each(function(index) {
            var $cell = $(this);
            var label = headers[index];
            var value = $cell.html();
            
            $cardBody.append('<div class="row"><div class="col-6"><strong>' + label + ':</strong></div><div class="col-6">' + value + '</div></div>');
        });
        
        $cards.append($card);
    });
    
    $table.replaceWith($cards);
}

function initResponsiveNav() {
    $('.responsive-nav').each(function() {
        var $nav = $(this);
        var $toggle = $('<button class="btn btn-primary d-md-none">Menu</button>');
        
        $toggle.on('click', function() {
            $nav.toggleClass('show');
        });
        
        $nav.before($toggle);
    });
}

function reinitializeComponents() {
    // Reinitialize tooltips
    $('[data-toggle="tooltip"]').tooltip('dispose').tooltip();
    
    // Reinitialize popovers
    $('[data-toggle="popover"]').popover('dispose').popover();
    
    // Reinitialize dropdowns
    $('.dropdown-toggle').dropdown('dispose').dropdown();
}

// Export to global scope
window.ModalManager = ModalManager;
window.TableUtils = TableUtils;
window.FormUtils = FormUtils;
window.AnimationUtils = AnimationUtils;
window.initDOMManipulation = initDOMManipulation;
