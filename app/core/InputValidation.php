<?php
/**
 * Input Validation Class
 * 
 * Comprehensive input validation based on OWASP best practices
 */

class InputValidation {
    
    /**
     * Validate and sanitize input data
     */
    public static function validate($data, $rules = []) {
        $errors = [];
        $sanitized = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            // Apply validation rules
            foreach ($fieldRules as $rule => $params) {
                $result = self::applyRule($field, $value, $rule, $params);
                
                if (!$result['valid']) {
                    $errors[$field] = $result['message'];
                    break; // Stop on first error for this field
                }
            }
            
            // Sanitize value if no errors
            if (!isset($errors[$field])) {
                $sanitized[$field] = self::sanitize($value, $fieldRules);
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized
        ];
    }
    
    /**
     * Apply individual validation rule
     */
    private static function applyRule($field, $value, $rule, $params) {
        switch ($rule) {
            case 'required':
                return self::validateRequired($field, $value);
                
            case 'email':
                return self::validateEmail($field, $value);
                
            case 'min_length':
                return self::validateMinLength($field, $value, $params);
                
            case 'max_length':
                return self::validateMaxLength($field, $value, $params);
                
            case 'numeric':
                return self::validateNumeric($field, $value);
                
            case 'alpha':
                return self::validateAlpha($field, $value);
                
            case 'alpha_num':
                return self::validateAlphaNum($field, $value);
                
            case 'regex':
                return self::validateRegex($field, $value, $params);
                
            case 'in':
                return self::validateIn($field, $value, $params);
                
            case 'unique':
                return self::validateUnique($field, $value, $params);
                
            case 'password':
                return self::validatePassword($field, $value);
                
            case 'phone':
                return self::validatePhone($field, $value);
                
            case 'url':
                return self::validateUrl($field, $value);
                
            case 'date':
                return self::validateDate($field, $value);
                
            default:
                return ['valid' => true, 'message' => ''];
        }
    }
    
    /**
     * Validate required field
     */
    private static function validateRequired($field, $value) {
        if (is_null($value) || $value === '') {
            return [
                'valid' => false,
                'message' => "Field {$field} is required"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate email format
     */
    private static function validateEmail($field, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'message' => "Field {$field} must be a valid email address"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate minimum length
     */
    private static function validateMinLength($field, $value, $minLength) {
        if (!empty($value) && strlen($value) < $minLength) {
            return [
                'valid' => false,
                'message' => "Field {$field} must be at least {$minLength} characters"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate maximum length
     */
    private static function validateMaxLength($field, $value, $maxLength) {
        if (!empty($value) && strlen($value) > $maxLength) {
            return [
                'valid' => false,
                'message' => "Field {$field} must not exceed {$maxLength} characters"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate numeric value
     */
    private static function validateNumeric($field, $value) {
        if (!empty($value) && !is_numeric($value)) {
            return [
                'valid' => false,
                'message' => "Field {$field} must be numeric"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate alphabetic characters only
     */
    private static function validateAlpha($field, $value) {
        if (!empty($value) && !preg_match('/^[a-zA-Z]+$/', $value)) {
            return [
                'valid' => false,
                'message' => "Field {$field} must contain only letters"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate alphanumeric characters only
     */
    private static function validateAlphaNum($field, $value) {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            return [
                'valid' => false,
                'message' => "Field {$field} must contain only letters and numbers"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate using regex pattern
     */
    private static function validateRegex($field, $value, $pattern) {
        if (!empty($value) && !preg_match($pattern, $value)) {
            return [
                'valid' => false,
                'message' => "Field {$field} format is invalid"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate value in allowed list
     */
    private static function validateIn($field, $value, $allowedValues) {
        if (!empty($value) && !in_array($value, $allowedValues)) {
            return [
                'valid' => false,
                'message' => "Field {$field} must be one of: " . implode(', ', $allowedValues)
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate unique value in database
     */
    private static function validateUnique($field, $value, $params) {
        // This would need database connection implementation
        // For now, just return true
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate password strength
     */
    private static function validatePassword($field, $value) {
        if (!empty($value)) {
            // Minimum 8 characters
            if (strlen($value) < 8) {
                return [
                    'valid' => false,
                    'message' => "Password must be at least 8 characters long"
                ];
            }
            
            // Must contain at least one uppercase letter
            if (!preg_match('/[A-Z]/', $value)) {
                return [
                    'valid' => false,
                    'message' => "Password must contain at least one uppercase letter"
                ];
            }
            
            // Must contain at least one lowercase letter
            if (!preg_match('/[a-z]/', $value)) {
                return [
                    'valid' => false,
                    'message' => "Password must contain at least one lowercase letter"
                ];
            }
            
            // Must contain at least one number
            if (!preg_match('/[0-9]/', $value)) {
                return [
                    'valid' => false,
                    'message' => "Password must contain at least one number"
                ];
            }
            
            // Must contain at least one special character
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
                return [
                    'valid' => false,
                    'message' => "Password must contain at least one special character"
                ];
            }
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate phone number (Indonesian format)
     */
    private static function validatePhone($field, $value) {
        if (!empty($value)) {
            // Indonesian phone number regex
            $pattern = '/^(\+62|62|0)[0-9]{9,13}$/';
            if (!preg_match($pattern, preg_replace('/[\s-]/', '', $value))) {
                return [
                    'valid' => false,
                    'message' => "Field {$field} must be a valid Indonesian phone number"
                ];
            }
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate URL
     */
    private static function validateUrl($field, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            return [
                'valid' => false,
                'message' => "Field {$field} must be a valid URL"
            ];
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate date
     */
    private static function validateDate($field, $value) {
        if (!empty($value)) {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                return [
                    'valid' => false,
                    'message' => "Field {$field} must be a valid date (YYYY-MM-DD)"
                ];
            }
        }
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Sanitize input value
     */
    private static function sanitize($value, $rules) {
        if (is_null($value)) {
            return null;
        }
        
        // Trim whitespace
        $value = trim($value);
        
        // Remove HTML tags if not explicitly allowed
        if (!isset($rules['allow_html'])) {
            $value = strip_tags($value);
        }
        
        // Convert special characters to HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
    
    /**
     * Quick validation for common patterns
     */
    public static function quickValidate($type, $value) {
        switch ($type) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
                
            case 'ip':
                return filter_var($value, FILTER_VALIDATE_IP) !== false;
                
            case 'int':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;
                
            case 'float':
                return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
                
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) !== false;
                
            default:
                return false;
        }
    }
}
?>
