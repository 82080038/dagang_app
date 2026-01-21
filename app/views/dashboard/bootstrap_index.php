<?php
/**
 * Bootstrap Dashboard View
 * Enhanced with Bootstrap 5 and Icons
 */

// Get current layout (bootstrap or main)
$layout = 'bootstrap'; // Change to 'main' for original layout
$layoutFile = $layout === 'bootstrap' ? 'layouts/bootstrap' : 'layouts/main';

// Load the layout
include __DIR__ . '/' . $layoutFile . '.php';
?>
