<?php
/**
 * Cross-Platform Compatibility Configuration
 * 
 * This file ensures that the application works correctly on both Windows and Linux
 * while being deployed on Linux servers.
 * 
 * Key Principles:
 * 1. Use cross-platform file paths
 * 2. Use Linux-compatible line endings
 * 3. Use case-sensitive file names
 * 4. Use Linux permissions model
 * 5. Use cross-platform directory separators
 */

// Platform Detection (hanya jika belum didefinisikan)
if (!defined('IS_WINDOWS')) {
    define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
}
if (!defined('IS_LINUX')) {
    define('IS_LINUX', strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX');
}
if (!defined('IS_MAC')) {
    define('IS_MAC', strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN');
}

// Cross-Platform File Path Constants (hanya jika belum didefinisikan)
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR); // Use DIRECTORY_SEPARATOR instead of hardcoded '/' or '\'
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . DS . 'app');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', APP_PATH . DS . 'config');
}
if (!defined('CORE_PATH')) {
    define('CORE_PATH', APP_PATH . DS . 'core');
}
if (!defined('MODELS_PATH')) {
    define('MODELS_PATH', APP_PATH . DS . 'models');
}
if (!defined('CONTROLLERS_PATH')) {
    define('CONTROLLERS_PATH', APP_PATH . DS . 'controllers');
}
if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', APP_PATH . DS . 'views');
}
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', PUBLIC_PATH . DS . 'assets');
}
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', PUBLIC_PATH . DS . 'uploads');
}
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', ROOT_PATH . DS . 'logs');
}
if (!defined('CACHE_PATH')) {
    define('CACHE_PATH', ROOT_PATH . DS . 'cache');
}
if (!defined('TEMP_PATH')) {
    define('TEMP_PATH', ROOT_PATH . DS . 'temp');
}

// File Permissions (Linux-style)
define('DEFAULT_DIR_PERMISSIONS', 0755); // rwxr-xr-x
define('DEFAULT_FILE_PERMISSIONS', 0644); // rw-r--r--
define('UPLOAD_DIR_PERMISSIONS', 0755); // rwxr-xr-x
define('UPLOAD_FILE_PERMISSIONS', 0644); // rw-r--r--
define('CONFIG_FILE_PERMISSIONS', 0644); // rw-r--r--
define('LOG_FILE_PERMISSIONS', 0644); // rw-r--r--
define('CACHE_FILE_PERMISSIONS', 0644); // rw-r--r--

// Cross-Platform Path Helper Functions
function normalizePath($path) {
    // Convert backslashes to forward slashes
    $path = str_replace('\\', '/', $path);
    
    // Remove duplicate slashes
    $path = preg_replace('/\/+/', '/', $path);
    
    // Remove trailing slash (except for root)
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    
    return $path;
}

function joinPath(...$parts) {
    return normalizePath(implode(DS, $parts));
}

function getRelativePath($from, $to) {
    $from = normalizePath($from);
    $to = normalizePath($to);
    
    $fromParts = explode('/', $from);
    $toParts = explode('/', $to);
    
    // Find common base path
    $commonLength = 0;
    $minLength = min(count($fromParts), count($toParts));
    
    for ($i = 0; $i < $minLength; $i++) {
        if ($fromParts[$i] === $toParts[$i]) {
            $commonLength++;
        } else {
            break;
        }
    }
    
    // Calculate relative path
    $relativeParts = [];
    
    // Add ".." for remaining from parts
    for ($i = $commonLength; $i < count($fromParts); $i++) {
        $relativeParts[] = '..';
    }
    
    // Add remaining to parts
    for ($i = $commonLength; $i < count($toParts); $i++) {
        $relativeParts[] = $toParts[$i];
    }
    
    return implode('/', $relativeParts);
}

function ensureDirectoryExists($path, $permissions = DEFAULT_DIR_PERMISSIONS) {
    $path = normalizePath($path);
    
    if (!file_exists($path)) {
        mkdir($path, $permissions, true);
    }
    
    return $path;
}

function ensureFileExists($path, $content = '', $permissions = DEFAULT_FILE_PERMISSIONS) {
    $path = normalizePath($path);
    
    if (!file_exists($path)) {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            ensureDirectoryExists($dir);
        }
        file_put_contents($path, $content);
        chmod($path, $permissions);
    }
    
    return $path;
}

// Cross-Platform File Operations
function createDirectory($path, $permissions = DEFAULT_DIR_PERMISSIONS) {
    return ensureDirectoryExists($path, $permissions);
}

function createFile($path, $content = '', $permissions = DEFAULT_FILE_PERMISSIONS) {
    return ensureFileExists($path, $content, $permissions);
}

function deleteDirectory($path, $recursive = false) {
    $path = normalizePath($path);
    
    if (!file_exists($path)) {
        return true;
    }
    
    if ($recursive) {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $item = $path . DS . $file;
            if (is_dir($item)) {
                deleteDirectory($item, true);
            } else {
                unlink($item);
            }
        }
    }
    
    return rmdir($path);
}

function copyDirectory($source, $destination) {
    $source = normalizePath($source);
    $destination = normalizePath($destination);
    
    if (!file_exists($source)) {
        throw new Exception("Source directory does not exist: $source");
    }
    
    if (!file_exists($destination)) {
        mkdir($destination, DEFAULT_DIR_PERMISSIONS, true);
    }
    
    $files = array_diff(scandir($source), ['.', '..']);
    foreach ($files as $file) {
        $sourceItem = $source . DS . $file;
        $destItem = $destination . DS . $file;
        
        if (is_dir($sourceItem)) {
            copyDirectory($sourceItem, $destItem);
        } else {
            copy($sourceItem, $destItem);
            chmod($destItem, DEFAULT_FILE_PERMISSIONS);
        }
    }
}

// Cross-Platform Command Execution
function executeCommand($command, $args = [], $cwd = null) {
    $command = normalizePath($command);
    
    // Add arguments
    if (!empty($args)) {
        $command .= ' ' . implode(' ', array_map('escapeshellarg', $args));
    }
    
    // Set working directory
    if ($cwd === null) {
        $cwd = ROOT_PATH;
    }
    
    // Execute command
    $output = [];
    $return_var = 0;
    
    if (IS_WINDOWS) {
        // Windows execution
        $command = 'cmd /c ' . $command;
        exec($command, $output, $return_var);
    } else {
        // Linux/Mac execution
        $output = shell_exec($command);
        $return_var = 0; // shell_exec doesn't return exit code
    }
    
    return [
        'output' => $output,
        'return_var' => $return_var,
        'command' => $command
    ];
}

// Cross-Platform File Permissions
function setPermissions($path, $permissions) {
    if (IS_WINDOWS) {
        // Windows doesn't support chmod in the same way
        return true;
    }
    
    return chmod($path, $permissions);
}

function checkPermissions($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    if (IS_WINDOWS) {
        // Windows doesn't have the same permission model
        return is_readable($path) && is_writable($path);
    }
    
    return fileperms($path);
}

// Cross-Platform Line Ending Handling
function normalizeLineEndings($content) {
    // Convert all line endings to LF (Linux standard)
    return str_replace(["\r\n", "\r"], "\n", $content);
}

function ensureLineEndings($file) {
    if (!file_exists($file)) {
        return false;
    }
    
    $content = file_get_contents($file);
    $normalized = normalizeLineEndings($content);
    
    if ($content !== $normalized) {
        file_put_contents($file, $normalized);
    }
    
    return true;
}

// Cross-Platform Environment Variables
function getEnvironmentVariable($name, $default = null) {
    $value = getenv($name);
    
    if ($value === false) {
        return $default;
    }
    
    return $value;
}

function setEnvironmentVariable($name, $value) {
    return putenv("$name=$value");
}

// Cross-Platform Temporary Files
function getTempDirectory() {
    if (IS_WINDOWS) {
        return sys_get_temp_dir();
    }
    
    return '/tmp';
}

function createTempFile($prefix = 'temp_', $suffix = '.tmp') {
    $tempDir = getTempDirectory();
    $filename = $prefix . uniqid() . $suffix;
    $filepath = $tempDir . DS . $filename;
    
    touch($filepath);
    chmod($filepath, DEFAULT_FILE_PERMISSIONS);
    
    return $filepath;
}

// Cross-Platform Logging
function logMessage($level, $message, $file = null) {
    if ($file === null) {
        $file = LOGS_PATH . DS . 'app.log';
    }
    
    ensureDirectoryExists(dirname($file));
    
    $timestamp = date('Y-m-d H:i:s');
    $platform = IS_WINDOWS ? 'Windows' : (IS_LINUX ? 'Linux' : 'Mac');
    $logEntry = "[$timestamp] [$platform] [$level] $message" . PHP_EOL;
    
    file_put_contents($file, $logEntry, FILE_APPEND | LOCK_EX);
}

// Cross-Platform Configuration
class CrossPlatformConfig {
    private static $config = [];
    
    public static function load($file) {
        $file = normalizePath($file);
        
        if (!file_exists($file)) {
            throw new Exception("Configuration file not found: $file");
        }
        
        $content = file_get_contents($file);
        $content = normalizeLineEndings($content);
        
        // Parse configuration (JSON format)
        self::$config = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in configuration file: $file");
        }
        
        return self::$config;
    }
    
    public static function get($key, $default = null) {
        if (empty(self::$config)) {
            return $default;
        }
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    public static function set($key, $value) {
        if (empty(self::$config)) {
            self::$config = [];
        }
        
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }
    
    public static function save($file) {
        $file = normalizePath($file);
        
        ensureDirectoryExists(dirname($file));
        
        $content = json_encode(self::$config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $content = normalizeLineEndings($content);
        
        $result = file_put_contents($file, $content, LOCK_EX);
        
        if ($result === false) {
            throw new Exception("Failed to save configuration file: $file");
        }
        
        setPermissions($file, DEFAULT_FILE_PERMISSIONS);
        
        return true;
    }
}

// Platform-specific optimizations
if (IS_LINUX) {
    // Linux-specific optimizations
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . DS . 'php_errors.log');
    
    // Set proper permissions for log directory
    if (file_exists(LOGS_PATH)) {
        setPermissions(LOGS_PATH, DEFAULT_DIR_PERMISSIONS);
    }
} elseif (IS_WINDOWS) {
    // Windows-specific optimizations
    ini_set('log_errors', 0); // Windows handles errors differently
}

// Initialize cross-platform environment
error_log("Cross-platform environment initialized:");
error_log("Platform: " . (IS_WINDOWS ? 'Windows' : (IS_LINUX ? 'Linux' : 'Mac')));
error_log("Root Path: " . ROOT_PATH);
error_log("Public Path: " . PUBLIC_PATH);
error_log("Temp Directory: " . getTempDirectory());

// Auto-create necessary directories
$requiredDirectories = [
    LOGS_PATH,
    CACHE_PATH,
    TEMP_PATH,
    UPLOADS_PATH,
    ASSETS_PATH . DS . 'css',
    ASSETS_PATH . DS . 'js',
    ASSETS_PATH . DS . 'images',
    ASSETS_PATH . DS . 'uploads'
];

foreach ($requiredDirectories as $dir) {
    ensureDirectoryExists($dir);
}

// Set proper permissions for created directories
if (IS_LINUX) {
    foreach ($requiredDirectories as $dir) {
        setPermissions($dir, DEFAULT_DIR_PERMISSIONS);
    }
}

?>
