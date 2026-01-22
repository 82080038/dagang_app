<?php
/**
 * Browser Detection and Support System
 * Ensures compatibility across all browsers
 */

class BrowserDetector {
    private static $supportedBrowsers = [
        'chrome' => ['min_version' => 60, 'name' => 'Google Chrome'],
        'firefox' => ['min_version' => 55, 'name' => 'Mozilla Firefox'],
        'safari' => ['min_version' => 12, 'name' => 'Safari'],
        'edge' => ['min_version' => 79, 'name' => 'Microsoft Edge'],
        'opera' => ['min_version' => 47, 'name' => 'Opera'],
        'ie' => ['min_version' => 11, 'name' => 'Internet Explorer']
    ];
    
    private static $deprecatedBrowsers = [
        'ie' => ['max_version' => 10, 'name' => 'Internet Explorer'],
        'netscape' => ['max_version' => 9, 'name' => 'Netscape Navigator'],
        'mozilla' => ['max_version' => 1.7, 'name' => 'Mozilla Suite']
    ];
    
    public static function getBrowserInfo() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $browser = 'unknown';
        $version = 0;
        
        // Detect browser
        if (preg_match('/MSIE\s+([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'ie';
            $version = floatval($matches[1]);
        } elseif (preg_match('/Trident\/7\.0.*rv:([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'ie';
            $version = floatval($matches[1]);
        } elseif (preg_match('/Edge\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'edge';
            $version = floatval($matches[1]);
        } elseif (preg_match('/Edg\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'edge';
            $version = floatval($matches[1]);
        } elseif (preg_match('/Chrome\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'chrome';
            $version = floatval($matches[1]);
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'firefox';
            $version = floatval($matches[1]);
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'safari';
            $version = floatval($matches[1]);
        } elseif (preg_match('/Opera\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'opera';
            $version = floatval($matches[1]);
        }
        
        return [
            'browser' => $browser,
            'version' => $version,
            'user_agent' => $userAgent
        ];
    }
    
    public static function isSupported() {
        $info = self::getBrowserInfo();
        $browser = $info['browser'];
        $version = $info['version'];
        
        // Check if browser is deprecated
        if (isset(self::$deprecatedBrowsers[$browser])) {
            $maxVersion = self::$deprecatedBrowsers[$browser]['max_version'];
            if ($version <= $maxVersion) {
                return [
                    'supported' => false,
                    'reason' => 'deprecated',
                    'browser_name' => self::$deprecatedBrowsers[$browser]['name'],
                    'browser_version' => $version,
                    'message' => 'Browser ini tidak lagi didukung. Silakan gunakan browser modern.'
                ];
            }
        }
        
        // Check if browser is supported
        if (isset(self::$supportedBrowsers[$browser])) {
            $minVersion = self::$supportedBrowsers[$browser]['min_version'];
            if ($version >= $minVersion) {
                return [
                    'supported' => true,
                    'browser_name' => self::$supportedBrowsers[$browser]['name'],
                    'browser_version' => $version
                ];
            } else {
                return [
                    'supported' => false,
                    'reason' => 'outdated',
                    'browser_name' => self::$supportedBrowsers[$browser]['name'],
                    'browser_version' => $version,
                    'min_version' => $minVersion,
                    'message' => 'Versi browser terlalu lama. Silakan update ke versi ' . $minVersion . ' atau lebih tinggi.'
                ];
            }
        }
        
        // Unknown browser - assume it's supported if it's a modern browser
        return [
            'supported' => true,
            'browser_name' => 'Unknown Browser',
            'browser_version' => $version,
            'message' => 'Browser tidak dikenal, namun kami berusaha untuk memberikan dukungan terbaik.'
        ];
    }
    
    public static function getBrowserSupportData() {
        $info = self::getBrowserInfo();
        $support = self::isSupported();
        
        return [
            'current_browser' => $info,
            'support_status' => $support,
            'supported_browsers' => self::$supportedBrowsers,
            'deprecated_browsers' => self::$deprecatedBrowsers
        ];
    }
}
?>
