<?php
/**
 * Security Headers Manager
 * 
 * Implements OWASP recommended security headers
 */

class SecurityHeaders {
    
    /**
     * Send all security headers
     */
    public static function sendHeaders() {
        // Check if security headers are enabled
        if (!defined('SECURITY_HEADERS') || !SECURITY_HEADERS) {
            return;
        }
        
        // Only send headers if not already sent
        if (headers_sent()) {
            return;
        }
        
        self::sendFrameOptions();
        self::sendContentTypeOptions();
        self::sendXSSProtection();
        self::sendHSTS();
        self::sendCSP();
        self::sendReferrerPolicy();
        self::sendPermissionsPolicy();
    }
    
    /**
     * X-Frame-Options header
     * Prevents clickjacking attacks
     */
    private static function sendFrameOptions() {
        if (defined('X_FRAME_OPTIONS')) {
            header('X-Frame-Options: ' . X_FRAME_OPTIONS);
        }
    }
    
    /**
     * X-Content-Type-Options header
     * Prevents MIME-type sniffing
     */
    private static function sendContentTypeOptions() {
        if (defined('X_CONTENT_TYPE_OPTIONS')) {
            header('X-Content-Type-Options: ' . X_CONTENT_TYPE_OPTIONS);
        }
    }
    
    /**
     * X-XSS-Protection header
     * Enables XSS filtering in browsers
     */
    private static function sendXSSProtection() {
        if (defined('X_XSS_PROTECTION')) {
            header('X-XSS-Protection: ' . X_XSS_PROTECTION);
        }
    }
    
    /**
     * Strict-Transport-Security header
     * Enforces HTTPS connections
     */
    private static function sendHSTS() {
        // Only send HSTS on HTTPS connections
        if (defined('STRICT_TRANSPORT_SECURITY') && 
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: ' . STRICT_TRANSPORT_SECURITY);
        }
    }
    
    /**
     * Content-Security-Policy header
     * Send Content-Security-Policy header
     */
    private static function sendCSP() {
        // Only send CSP if enabled and defined
        if (defined('CONTENT_SECURITY_POLICY') && CONTENT_SECURITY_POLICY !== false) {
            header('Content-Security-Policy: ' . CONTENT_SECURITY_POLICY);
        }
    }
    
    /**
     * Referrer-Policy header
     * Controls referrer information
     */
    private static function sendReferrerPolicy() {
        // Default to strict-origin-when-cross-origin
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    
    /**
     * Permissions-Policy header
     * Controls browser features
     */
    private static function sendPermissionsPolicy() {
        $policies = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
            'ambient-light-sensor=()',
            'autoplay=()',
            'encrypted-media=()',
            'fullscreen=()',
            'picture-in-picture=()'
        ];
        
        header('Permissions-Policy: ' . implode(', ', $policies));
    }
    
    /**
     * Remove sensitive information from error messages
     */
    public static function sanitizeErrorReporting() {
        // Only sanitize if security headers are enabled
        if (!defined('SECURITY_HEADERS') || !SECURITY_HEADERS) {
            return;
        }
        
        if (!APP_DEBUG) {
            // Hide PHP version
            header_remove('X-Powered-By');
            
            // Hide server information
            header_remove('Server');
            
            // Custom server header
            header('Server: SecureServer');
        }
    }
    
    /**
     * Set CORS headers if needed
     */
    public static function setCORS($allowedOrigins = []) {
        if (empty($allowedOrigins)) {
            // Default to same origin
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            $allowedOrigins = [BASE_URL];
        }
        
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 hours
    }
}
?>
