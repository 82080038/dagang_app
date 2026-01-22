<?php

class HealthCheck {
    public static function run() {
        if (defined('IS_LINUX') && IS_LINUX) {
            self::checkExtensions();
            self::checkDirectories();
        }
    }
    
    private static function checkExtensions() {
        $required = ['pdo_mysql','json','mbstring','openssl','curl'];
        $missing = [];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        if (!empty($missing)) {
            logMessage('ERROR', 'Missing PHP extensions: ' . implode(',', $missing));
        } else {
            logMessage('INFO', 'All required PHP extensions present');
        }
    }
    
    private static function checkDirectories() {
        $dirs = [LOGS_PATH, CACHE_PATH, UPLOADS_PATH, TEMP_PATH];
        foreach ($dirs as $dir) {
            ensureDirectoryExists($dir);
            if (!is_writable($dir)) {
                @chmod($dir, DEFAULT_DIR_PERMISSIONS);
            }
            if (!is_writable($dir)) {
                logMessage('ERROR', 'Directory not writable: ' . $dir);
            } else {
                logMessage('INFO', 'Directory writable: ' . $dir);
            }
        }
    }
}
?> 
