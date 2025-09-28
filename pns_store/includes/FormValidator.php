<?php
/**
 * Form Validation Helper Class
 * Provides standardized form validation and sanitization
 */
class FormValidator {
    private $errors = [];
    private $data = [];
    private $rules = [];
    
    /**
     * Constructor
     * @param array $data Form data to validate
     */
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Add a validation rule
     * @param string $field Field name
     * @param string $label Human-readable field label
     * @param array $rules Array of rules
     * @return self
     */
    public function addRule($field, $label, $rules) {
        $this->rules[$field] = [
            'label' => $label,
            'rules' => $rules
        ];
        return $this;
    }
    
    /**
     * Run validation
     * @return bool Whether validation passed
     */
    public function validate() {
        $this->errors = [];
        
        foreach ($this->rules as $field => $config) {
            $value = $this->data[$field] ?? null;
            $label = $config['label'];
            
            foreach ($config['rules'] as $rule) {
                $result = $this->applyRule($rule, $field, $value, $label);
                if ($result !== true) {
                    $this->errors[$field] = $result;
                    break;
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Apply a validation rule
     * @param string|array $rule Rule to apply
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $label Field label
     * @return bool|string True if valid, error message if not
     */
    private function applyRule($rule, $field, $value, $label) {
        if (is_string($rule)) {
            switch ($rule) {
                case 'required':
                    return empty($value) && $value !== '0' ? "{$label} is required." : true;
                
                case 'email':
                    return !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL) 
                        ? "{$label} must be a valid email address." : true;
                
                case 'numeric':
                    return !empty($value) && !is_numeric($value) 
                        ? "{$label} must be a number." : true;
                
                case 'integer':
                    return !empty($value) && !filter_var($value, FILTER_VALIDATE_INT) 
                        ? "{$label} must be an integer." : true;
                
                case 'float':
                    return !empty($value) && !filter_var($value, FILTER_VALIDATE_FLOAT) 
                        ? "{$label} must be a decimal number." : true;
                
                case 'url':
                    return !empty($value) && !filter_var($value, FILTER_VALIDATE_URL) 
                        ? "{$label} must be a valid URL." : true;
            }
        }
        
        if (is_array($rule)) {
            $type = key($rule);
            $param = current($rule);
            
            switch ($type) {
                case 'min':
                    return strlen($value) < $param 
                        ? "{$label} must be at least {$param} characters." : true;
                
                case 'max':
                    return strlen($value) > $param 
                        ? "{$label} must not exceed {$param} characters." : true;
                
                case 'min_val':
                    return $value < $param 
                        ? "{$label} must be at least {$param}." : true;
                
                case 'max_val':
                    return $value > $param 
                        ? "{$label} must not exceed {$param}." : true;
                
                case 'matches':
                    return $value !== ($this->data[$param] ?? null)
                        ? "{$label} must match {$param}." : true;
                
                case 'in':
                    return !in_array($value, $param)
                        ? "{$label} must be one of: " . implode(', ', $param) : true;
            }
        }
        
        if (is_callable($rule)) {
            $result = $rule($value, $this->data);
            return $result === true ? true : ($result ?: "{$label} is invalid.");
        }
        
        return true;
    }
    
    /**
     * Get validation errors
     * @return array Array of errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error message
     * @return string|null First error message or null if none
     */
    public function getFirstError() {
        return reset($this->errors) ?: null;
    }
    
    /**
     * Check if a field has an error
     * @param string $field Field name
     * @return bool Whether the field has an error
     */
    public function hasError($field) {
        return isset($this->errors[$field]);
    }
    
    /**
     * Get error for a field
     * @param string $field Field name
     * @return string|null Error message or null if none
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Sanitize a value
     * @param mixed $value Value to sanitize
     * @param string $type Type of sanitization
     * @return mixed Sanitized value
     */
    public static function sanitize($value, $type = 'string') {
        switch ($type) {
            case 'string':
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
            
            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);
            
            case 'int':
                return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            
            case 'float':
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            case 'html':
                return strip_tags($value, '<p><a><b><i><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6>');
            
            default:
                return $value;
        }
    }
}