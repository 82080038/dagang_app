<?php
/**
 * Security Audit Tool
 * 
 * Comprehensive security audit based on OWASP 2025 best practices
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Controller.php';

class SecurityAudit {
    private $issues = [];
    private $recommendations = [];
    private $score = 100;
    
    public function __construct() {
        echo "=== SECURITY AUDIT REPORT ===\n";
        echo "Generated: " . date('Y-m-d H:i:s') . "\n";
        echo "Based on OWASP Top 10 2025\n\n";
    }
    
    /**
     * Run complete security audit
     */
    public function runAudit() {
        $this->checkDatabaseSecurity();
        $this->checkFilePermissions();
        $this->checkSessionSecurity();
        $this->checkCSRFProtection();
        $this->checkXSSProtection();
        $this->checkPasswordSecurity();
        $this->checkInputValidation();
        $this->checkErrorHandling();
        $this->checkHTTPS();
        $this->checkFileUploadSecurity();
        
        $this->generateReport();
    }
    
    /**
     * Check database security
     */
    private function checkDatabaseSecurity() {
        echo "Checking Database Security...\n";
        
        // Check if using prepared statements
        $controllerFiles = glob(__DIR__ . '/../app/controllers/*.php');
        $modelFiles = glob(__DIR__ . '/../app/models/*.php');
        
        $allFiles = array_merge($controllerFiles, $modelFiles);
        $vulnerableFiles = [];
        
        foreach ($allFiles as $file) {
            $content = file_get_contents($file);
            
            // Check for direct SQL injection patterns
            if (preg_match('/\$\w+\s*=\s*["\'].*?\$\w+.*?["\']/', $content) ||
                preg_match('/mysql_query|mysqli_query.*\$/', $content) ||
                preg_match('/SELECT.*FROM.*WHERE.*\$/', $content)) {
                
                $vulnerableFiles[] = basename($file);
            }
        }
        
        if (!empty($vulnerableFiles)) {
            $this->issues[] = "Potential SQL injection vulnerabilities found in: " . implode(', ', $vulnerableFiles);
            $this->recommendations[] = "Use PDO prepared statements with parameter binding";
            $this->score -= 20;
        } else {
            echo "✓ Database security looks good\n";
        }
    }
    
    /**
     * Check file permissions
     */
    private function checkFilePermissions() {
        echo "Checking File Permissions...\n";
        
        $criticalDirs = [
            __DIR__ . '/../app/config',
            __DIR__ . '/../uploads',
            __DIR__ . '/../logs',
            __DIR__ . '/../cache'
        ];
        
        foreach ($criticalDirs as $dir) {
            if (is_dir($dir)) {
                $perms = fileperms($dir);
                $octal = substr(sprintf('%o', $perms), -4);
                
                // Check if directory is writable by others
                if ($octal[3] > 6) {
                    $this->issues[] = "Insecure permissions on {$dir}: {$octal}";
                    $this->recommendations[] = "Set permissions to 755 for directories, 644 for files";
                    $this->score -= 10;
                }
            }
        }
        
        if (empty($this->issues)) {
            echo "✓ File permissions are secure\n";
        }
    }
    
    /**
     * Check session security
     */
    private function checkSessionSecurity() {
        echo "Checking Session Security...\n";
        
        $configFile = __DIR__ . '/../app/config/config.php';
        $configContent = file_get_contents($configFile);
        
        $issues = [];
        
        // Check for secure session settings
        if (!strpos($configContent, 'session.cookie_httponly')) {
            $issues[] = "HTTP-only cookies not configured";
        }
        
        if (!strpos($configContent, 'session.cookie_secure')) {
            $issues[] = "Secure cookies not configured";
        }
        
        if (!strpos($configContent, 'session.use_strict_mode')) {
            $issues[] = "Strict session mode not enabled";
        }
        
        if (!empty($issues)) {
            $this->issues[] = "Session security issues: " . implode(', ', $issues);
            $this->recommendations[] = "Configure secure session settings: httponly, secure, strict_mode";
            $this->score -= 15;
        } else {
            echo "✓ Session security is properly configured\n";
        }
    }
    
    /**
     * Check CSRF protection
     */
    private function checkCSRFProtection() {
        echo "Checking CSRF Protection...\n";
        
        $csrfFile = __DIR__ . '/../app/core/Csrf.php';
        
        if (!file_exists($csrfFile)) {
            $this->issues[] = "CSRF protection class not found";
            $this->recommendations[] = "Implement CSRF token generation and validation";
            $this->score -= 25;
        } else {
            // Check if CSRF is being used in forms
            $viewFiles = glob(__DIR__ . '/../app/views/**/*.php');
            $csrfUsage = false;
            
            foreach ($viewFiles as $file) {
                $content = file_get_contents($file);
                if (strpos($content, 'csrf_token') !== false) {
                    $csrfUsage = true;
                    break;
                }
            }
            
            if (!$csrfUsage) {
                $this->issues[] = "CSRF tokens not implemented in forms";
                $this->recommendations[] = "Add CSRF tokens to all forms";
                $this->score -= 15;
            } else {
                echo "✓ CSRF protection is implemented\n";
            }
        }
    }
    
    /**
     * Check XSS protection
     */
    private function checkXSSProtection() {
        echo "Checking XSS Protection...\n";
        
        $viewFiles = glob(__DIR__ . '/../app/views/**/*.php');
        $vulnerableFiles = [];
        
        foreach ($viewFiles as $file) {
            $content = file_get_contents($file);
            
            // Check for unescaped output
            if (preg_match('/echo\s+\$[^;]+;/', $content) &&
                !preg_match('/echo\s+htmlspecialchars/', $content)) {
                $vulnerableFiles[] = basename($file);
            }
        }
        
        if (!empty($vulnerableFiles)) {
            $this->issues[] = "Potential XSS vulnerabilities in: " . implode(', ', $vulnerableFiles);
            $this->recommendations[] = "Use htmlspecialchars() for all output";
            $this->score -= 20;
        } else {
            echo "✓ XSS protection appears to be implemented\n";
        }
    }
    
    /**
     * Check password security
     */
    private function checkPasswordSecurity() {
        echo "Checking Password Security...\n";
        
        $authFile = __DIR__ . '/../app/controllers/AuthController.php';
        $content = file_get_contents($authFile);
        
        if (strpos($content, 'password_hash') !== false) {
            echo "✓ Modern password hashing implemented\n";
        } else {
            $this->issues[] = "Modern password hashing not found";
            $this->recommendations[] = "Use password_hash() with bcrypt or Argon2";
            $this->score -= 20;
        }
    }
    
    /**
     * Check input validation
     */
    private function checkInputValidation() {
        echo "Checking Input Validation...\n";
        
        $controllerFiles = glob(__DIR__ . '/../app/controllers/*.php');
        $validationFound = false;
        
        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'filter_var') !== false ||
                strpos($content, 'preg_match') !== false ||
                strpos($content, 'validate') !== false) {
                $validationFound = true;
                break;
            }
        }
        
        if (!$validationFound) {
            $this->issues[] = "Input validation not consistently implemented";
            $this->recommendations[] = "Implement server-side input validation";
            $this->score -= 15;
        } else {
            echo "✓ Input validation found\n";
        }
    }
    
    /**
     * Check error handling
     */
    private function checkErrorHandling() {
        echo "Checking Error Handling...\n";
        
        $configFile = __DIR__ . '/../app/config/config.php';
        $content = file_get_contents($configFile);
        
        if (strpos($content, 'APP_DEBUG') !== false) {
            if (strpos($content, "define('APP_DEBUG', false)") !== false) {
                echo "✓ Debug mode disabled in production\n";
            } else {
                $this->issues[] = "Debug mode may be enabled";
                $this->recommendations[] = "Disable debug mode in production";
                $this->score -= 10;
            }
        }
    }
    
    /**
     * Check HTTPS implementation
     */
    private function checkHTTPS() {
        echo "Checking HTTPS Configuration...\n";
        
        $configFile = __DIR__ . '/../app/config/config.php';
        $content = file_get_contents($configFile);
        
        if (strpos($content, 'HTTPS') !== false) {
            echo "✓ HTTPS configuration found\n";
        } else {
            $this->issues[] = "HTTPS enforcement not implemented";
            $this->recommendations[] = "Implement HTTPS redirect and secure cookies";
            $this->score -= 15;
        }
    }
    
    /**
     * Check file upload security
     */
    private function checkFileUploadSecurity() {
        echo "Checking File Upload Security...\n";
        
        $uploadDirs = [
            __DIR__ . '/../uploads',
            __DIR__ . '/../uploads/products',
            __DIR__ . '/../uploads/documents'
        ];
        
        foreach ($uploadDirs as $dir) {
            if (is_dir($dir)) {
                $htaccess = $dir . '/.htaccess';
                if (!file_exists($htaccess)) {
                    $this->issues[] = "No .htaccess protection for upload directory: {$dir}";
                    $this->recommendations[] = "Add .htaccess to prevent direct file access";
                    $this->score -= 10;
                }
            }
        }
        
        if (empty($this->issues)) {
            echo "✓ File upload security appears adequate\n";
        }
    }
    
    /**
     * Generate security report
     */
    private function generateReport() {
        echo "\n=== SECURITY AUDIT RESULTS ===\n";
        echo "Security Score: {$this->score}/100\n\n";
        
        if (!empty($this->issues)) {
            echo "ISSUES FOUND:\n";
            foreach ($this->issues as $i => $issue) {
                echo ($i + 1) . ". {$issue}\n";
            }
            echo "\n";
        }
        
        if (!empty($this->recommendations)) {
            echo "RECOMMENDATIONS:\n";
            foreach ($this->recommendations as $i => $rec) {
                echo ($i + 1) . ". {$rec}\n";
            }
            echo "\n";
        }
        
        // Risk level assessment
        if ($this->score >= 80) {
            echo "RISK LEVEL: LOW\n";
        } elseif ($this->score >= 60) {
            echo "RISK LEVEL: MEDIUM\n";
        } elseif ($this->score >= 40) {
            echo "RISK LEVEL: HIGH\n";
        } else {
            echo "RISK LEVEL: CRITICAL\n";
        }
        
        echo "\n=== AUDIT COMPLETE ===\n";
    }
}

// Run the audit
$audit = new SecurityAudit();
$audit->runAudit();
?>
