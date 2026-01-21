<?php
/**
 * View Class
 * Native PHP MVC Pattern
 */

class View {
    protected $viewPath;
    
    public function __construct() {
        $this->viewPath = APP_PATH . '/views';
    }
    
    /**
     * Render View
     */
    public function render($view, $data = [], $return = false) {
        $viewFile = $this->viewPath . '/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$view}");
        }
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        include $viewFile;
        
        // Get buffer contents
        $content = ob_get_clean();
        
        if ($return) {
            return $content;
        } else {
            echo $content;
            return true;
        }
    }
    
    /**
     * Include Partial View
     */
    public function partial($partial, $data = []) {
        return $this->render('partials/' . $partial, $data, true);
    }
    
    /**
     * Escape HTML
     */
    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format Currency
     */
    public function formatCurrency($amount) {
        return CURRENCY_SYMBOL . ' ' . number_format($amount, DECIMAL_PLACES, DECIMAL_SEPARATOR, THOUSANDS_SEPARATOR);
    }
    
    /**
     * Format Date
     */
    public function formatDate($date, $format = null) {
        if ($format === null) {
            $format = DATE_FORMAT;
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Generate URL
     */
    public function url($path = '') {
        return BASE_URL . '/' . ltrim($path, '/');
    }
    
    /**
     * Generate Asset URL
     */
    public function asset($path) {
        return ASSETS_URL . '/' . ltrim($path, '/');
    }
    
    /**
     * Generate CSRF Token Field
     */
    public function csrfField() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
    
    /**
     * Get CSRF Token
     */
    public function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Check if Current Page
     */
    public function isActive($path) {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $currentPath === $path;
    }
    
    /**
     * Check if Current Menu Active
     */
    public function isMenuActive($menu) {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return strpos($currentPath, $menu) === 0;
    }
    
    /**
     * Flash Message
     */
    public function flash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get Flash Messages
     */
    public function getFlash($type = null) {
        if ($type === null) {
            $messages = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
            unset($_SESSION['flash']);
            return $messages;
        }
        
        $message = isset($_SESSION['flash'][$type]) ? $_SESSION['flash'][$type] : null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    
    /**
     * Display Flash Messages
     */
    public function displayFlash() {
        $messages = $this->getFlash();
        
        if (empty($messages)) {
            return '';
        }
        
        $html = '';
        foreach ($messages as $type => $message) {
            $alertClass = $type === 'success' ? 'alert-success' : 
                         ($type === 'error' ? 'alert-danger' : 'alert-info');
            
            $html .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
            $html .= $message;
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Pagination Links
     */
    public function pagination($currentPage, $totalPages, $baseUrl, $queryParams = []) {
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="Page navigation"><ul class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $url = $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => $prevPage]));
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }
        
        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = $i == $currentPage ? 'active' : '';
            $url = $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => $i]));
            
            if ($i == $currentPage) {
                $html .= '<li class="page-item ' . $activeClass . '"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">' . $i . '</a></li>';
            }
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $nextPage = $currentPage + 1;
            $url = $this->buildUrl($baseUrl, array_merge($queryParams, ['page' => $nextPage]));
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
    
    /**
     * Build URL with Query Parameters
     */
    private function buildUrl($baseUrl, $queryParams) {
        if (empty($queryParams)) {
            return $baseUrl;
        }
        
        return $baseUrl . '?' . http_build_query($queryParams);
    }
    
    /**
     * Convert Array to Options
     */
    public function options($array, $selected = '', $default = '') {
        $html = '';
        
        if ($default !== '') {
            $html .= '<option value="">' . $default . '</option>';
        }
        
        foreach ($array as $value => $label) {
            $selectedAttr = $value == $selected ? ' selected' : '';
            $html .= '<option value="' . $value . '"' . $selectedAttr . '>' . $label . '</option>';
        }
        
        return $html;
    }
    
    /**
     * Convert Array to Checkboxes
     */
    public function checkboxes($name, $array, $selected = []) {
        $html = '';
        
        foreach ($array as $value => $label) {
            $checked = in_array($value, $selected) ? ' checked' : '';
            $html .= '<div class="form-check">';
            $html .= '<input class="form-check-input" type="checkbox" name="' . $name . '[]" value="' . $value . '" id="' . $name . '_' . $value . '"' . $checked . '>';
            $html .= '<label class="form-check-label" for="' . $name . '_' . $value . '">' . $label . '</label>';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Convert Array to Radio Buttons
     */
    public function radio($name, $array, $selected = '') {
        $html = '';
        
        foreach ($array as $value => $label) {
            $checked = $value == $selected ? ' checked' : '';
            $html .= '<div class="form-check">';
            $html .= '<input class="form-check-input" type="radio" name="' . $name . '" value="' . $value . '" id="' . $name . '_' . $value . '"' . $checked . '>';
            $html .= '<label class="form-check-label" for="' . $name . '_' . $value . '">' . $label . '</label>';
            $html .= '</div>';
        }
        
        return $html;
    }
}
?>
