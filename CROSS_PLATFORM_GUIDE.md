# CROSS-PLATFORM COMPATIBILITY GUIDE

## 🎯 Objective
Ensure the application works correctly on both Windows (development) and Linux (deployment) while maintaining Linux compatibility standards.

## 📋 Platform Detection

### Supported Platforms:
- **Windows** - Development environment
- **Linux** - Production server
- **macOS** - Optional development environment

### Detection Constants:
```php
define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
define('IS_LINUX', strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX');
define('IS_MAC', strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN');
```

## 📁 File Path Standards

### Cross-Platform Path Constants:
```php
define('DS', DIRECTORY_SEPARATOR); // Use instead of '/' or '\'
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . DS . 'app');
define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
define('CONFIG_PATH', APP_PATH . DS . 'config');
define('CORE_PATH', APP_PATH . DS . 'core');
define('MODELS_PATH', APP_PATH . DS . 'models');
define('CONTROLLERS_PATH', APP_PATH . DS . 'controllers');
define('VIEWS_PATH', APP_PATH . DS . 'views');
define('ASSETS_PATH', PUBLIC_PATH . DS . 'assets');
define('UPLOADS_PATH', PUBLIC_PATH . DS . 'uploads');
define('LOGS_PATH', ROOT_PATH . DS . 'logs');
define('CACHE_PATH', ROOT_PATH . DS . 'cache');
define('TEMP_PATH', ROOT_PATH . DS . 'temp');
```

### Path Helper Functions:
```php
// Normalize path separators
function normalizePath($path) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('/\/+/', '/', $path);
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    return $path;
}

// Join path parts
function joinPath(...$parts) {
    return normalizePath(implode(DS, $parts));
}

// Get relative path
function getRelativePath($from, $to) {
    // Implementation for cross-platform relative paths
}
```

## 🔐 File Permissions (Linux Standard)

### Permission Constants:
```php
define('DEFAULT_DIR_PERMISSIONS', 0755); // rwxr-xr-x
define('DEFAULT_FILE_PERMISSIONS', 0644); // rw-r--r--
define('UPLOAD_DIR_PERMISSIONS', 0755); // rwxr-xr-x
define('UPLOAD_FILE_PERMISSIONS', 0644); // rw-r--r--
define('CONFIG_FILE_PERMISSIONS', 0644); // rw-r--r--
define('LOG_FILE_PERMISSIONS', 0644); // rw-r--r--
define('CACHE_FILE_PERMISSIONS', 0644); // rw-r--r--
```

### Directory Creation:
```php
function ensureDirectoryExists($path, $permissions = DEFAULT_DIR_PERMISSIONS) {
    $path = normalizePath($path);
    if (!file_exists($path)) {
        mkdir($path, $permissions, true);
    }
    return $path;
}

function createDirectory($path, $permissions = DEFAULT_DIR_PERMISSIONS) {
    return ensureDirectoryExists($path, $permissions);
}
```

## 📝 File Operations

### Cross-Platform File Functions:
```php
// Create file with proper permissions
function createFile($path, $content = '', $permissions = DEFAULT_FILE_PERMISSIONS) {
    return ensureFileExists($path, $content, $permissions);
}

// Copy directory recursively
function copyDirectory($source, $destination) {
    // Cross-platform directory copying implementation
}

// Delete directory recursively
function deleteDirectory($path, $recursive = false) {
    // Cross-platform directory deletion implementation
}
```

## 🔧 Command Execution

### Cross-Platform Command Execution:
```php
function executeCommand($command, $args = [], $cwd = null) {
    $command = normalizePath($command);
    
    if (!empty($args)) {
        $command .= ' ' . implode(' ', array_map('escapeshellarg', $args));
    }
    
    if ($cwd === null) {
        $cwd = ROOT_PATH;
    }
    
    $output = [];
    $return_var = 0;
    
    if (IS_WINDOWS) {
        // Windows execution
        $command = 'cmd /c ' . $command;
        exec($command, $output, $return_var);
    } else {
        // Linux/Mac execution
        $output = shell_exec($command);
        $return_var = 0;
    }
    
    return [
        'output' => $output,
        'return_var' => $return_var,
        'command' => $command
    ];
}
```

## 📄 Line Ending Handling

### Linux Standard Line Endings:
```php
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
```

## 🔧 Environment Variables

### Cross-Platform Environment Handling:
```php
function getEnvironmentVariable($name, $default = null) {
    $value = getenv($name);
    return $value === false ? $default : $value;
}

function setEnvironmentVariable($name, $value) {
    return putenv("$name=$value");
}
```

## 📁 Temporary Files

### Cross-Platform Temporary Directory:
```php
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
```

## 📝 Logging

### Cross-Platform Logging:
```php
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
```

## ⚙️ Configuration Management

### Cross-Platform Configuration Class:
```php
class CrossPlatformConfig {
    private static $config = [];
    
    public static function load($file) {
        // Load and parse configuration file
    }
    
    public static function get($key, $default = null) {
        // Get configuration value
    }
    
    public static function set($key, $value) {
        // Set configuration value
    }
    
    public static function save($file) {
        // Save configuration to file
    }
}
```

## 🔧 Platform-Specific Optimizations

### Linux Optimizations:
```php
if (IS_LINUX) {
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . DS . 'php_errors.log');
    
    // Set proper permissions for log directory
    if (file_exists(LOGS_PATH)) {
        setPermissions(LOGS_PATH, DEFAULT_DIR_PERMISSIONS);
    }
}
```

### Windows Optimizations:
```php
if (IS_WINDOWS) {
    ini_set('log_errors', 0); // Windows handles errors differently
}
```

## 📋 Required Directories

### Auto-Created Directories:
```php
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
```

## 🚀 Implementation Guidelines

### Development (Windows):
1. Use `DIRECTORY_SEPARATOR` instead of hardcoded `/` or `\`
2. Test on Windows before deployment
3. Use cross-platform helper functions
4. Ensure line endings are LF (Linux standard)
5. Use relative paths when possible

### Deployment (Linux):
1. Ensure all file permissions are set correctly
2. Use absolute paths for system files
3. Configure proper logging
4. Set up proper directory structure
5. Test all file operations

### File Naming Conventions:
1. Use lowercase letters for file names
2. Use underscores instead of spaces
3. Avoid special characters
4. Use descriptive names
5. Keep names short but meaningful

### Code Standards:
1. Use `DIRECTORY_SEPARATOR` for path separators
2. Use cross-platform helper functions
3. Normalize all file paths
4. Handle line endings consistently
5. Use Linux permission model

## 🔍 Testing Checklist

### Windows Testing:
- [ ] File operations work correctly
- [ ] Path handling is consistent
- [ ] Permissions are set correctly
- [ ] Commands execute properly
- [ ] Logging works as expected

### Linux Testing:
- [ ] File permissions are correct
- [ ] Directory structure is proper
- [ ] Logging is configured
- [ ] Commands execute properly
- [ ] Performance is optimized

### Cross-Platform Testing:
- [ ] Configuration files work on both platforms
- [ ] File transfers work correctly
- [ ] Permissions are maintained
- [ ] Line endings are consistent
- [ ] Application runs without errors

## 📚 Best Practices

### File Operations:
1. Always use `DIRECTORY_SEPARATOR`
2. Normalize paths before use
3. Handle permissions properly
4. Use cross-platform helper functions
5. Test on both platforms

### Code Development:
1. Write platform-agnostic code
2. Use cross-platform libraries
3. Avoid platform-specific functions
4. Test regularly on both platforms
5. Document platform-specific behavior

### Deployment:
1. Use Linux as the target platform
2. Ensure proper file permissions
3. Configure logging appropriately
4. Test all functionality
5. Monitor platform-specific issues

## 🚨 Troubleshooting

### Common Issues:
1. **Path separators** - Use `DIRECTORY_SEPARATOR`
2. **File permissions** - Use Linux standard
3. **Line endings** - Normalize to LF
4. **Command execution** - Use cross-platform functions
5. **Configuration files** - Use JSON format

### Debugging:
1. Check platform detection
2. Verify path normalization
3. Test file permissions
4. Validate command execution
5. Review error logs

## 📞 Support

For cross-platform compatibility issues:
1. Check platform detection constants
2. Verify file path normalization
3. Test helper functions
4. Review error logs
5. Consult this documentation

## 🔄 Maintenance

### Regular Tasks:
1. Test on both platforms
2. Update helper functions
3. Review file permissions
4. Check configuration files
5. Monitor performance

### Updates:
1. Update platform detection
2. Add new helper functions
3. Improve error handling
4. Optimize performance
5. Update documentation
