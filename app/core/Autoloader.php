<?php
/**
 * Simple Autoloader
 * 
 * Menggantikan require manual untuk class loading
 */

spl_autoload_register(function ($class) {
    // Define directories to search
    // Order matters: check core first, then models, then controllers
    $directories = [
        __DIR__ . '/',              // app/core/
        __DIR__ . '/../controllers/', // app/controllers/
        __DIR__ . '/../models/',      // app/models/
        __DIR__ . '/../utils/',       // app/utils/
        __DIR__ . '/../config/'       // app/config/
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
