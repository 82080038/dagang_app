<?php
/**
 * Bootstrap File
 * Initialize application
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . "/app.php";
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/constants.php";

// Load core files
require_once __DIR__ . "/../app/core/Autoloader.php";
require_once __DIR__ . "/../app/core/Router.php";

// Initialize autoloader
$autoloader = new Autoloader();

// Handle routing
$router = new Router();
$router->dispatch();
?>