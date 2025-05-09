<?php
namespace Core;

/**
 * Base Controller Class
 * Provides core functionality for controllers
 */
abstract class Controller {
    protected $view;
    protected $request;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->view = new View();
        $this->request = $_REQUEST;
        
        // Check CSRF token for POST, PUT, DELETE requests
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            $this->validateCsrfToken();
        }
    }
    
    /**
     * Get a request parameter
     * 
     * @param string $key The parameter key
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed The parameter value
     */
    protected function input($key, $default = null) {
        return isset($this->request[$key]) ? $this->request[$key] : $default;
    }
    
    /**
     * Validate CSRF token
     * 
     * @return void
     */
    protected function validateCsrfToken() {
        $token = $this->input('csrf_token');
        
        if (!$token || !csrf_check($token)) {
            http_response_code(403);
            die('CSRF token validation failed.');
        }
    }
    
    /**
     * Check if user is authenticated
     * 
     * @param bool $redirect Whether to redirect to login page if not authenticated
     * @return bool Authentication status
     */
    protected function authenticate($redirect = true) {
        if (!is_logged_in()) {
            if ($redirect) {
                flash('warning', 'Please log in to continue.');
                redirect('/login');
            }
            return false;
        }
        return true;
    }
    
    /**
     * Check if user is an admin/librarian
     * 
     * @param bool $redirect Whether to redirect if not admin
     * @return bool Admin status
     */
    protected function authorizeAdmin($redirect = true) {
        $this->authenticate();
        
        if (!is_admin()) {
            if ($redirect) {
                flash('error', 'You do not have permission to access this resource.');
                redirect('/');
            }
            return false;
        }
        return true;
    }
    
    /**
     * Render a view
     * 
     * @param string $view The view to render
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function render($view, $data = []) {
        $this->view->render($view, $data);
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url The URL to redirect to
     * @return void
     */
    protected function redirect($url) {
        redirect($url);
    }
    
    /**
     * Set a flash message
     * 
     * @param string $type The message type
     * @param string $message The message
     * @return void
     */
    protected function flash($type, $message) {
        flash($type, $message);
    }
    
    /**
     * Return JSON response
     * 
     * @param array $data The data to return
     * @param int $code HTTP status code
     * @return void
     */
    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Validate input data
     * 
     * @param array $data The data to validate
     * @param array $rules The validation rules
     * @return array [is_valid, errors]
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $fieldErrors = [];
            
            // Process each rule for the field
            $ruleSet = explode('|', $rule);
            foreach ($ruleSet as $singleRule) {
                if (strpos($singleRule, ':') !== false) {
                    list($ruleName, $ruleParam) = explode(':', $singleRule, 2);
                } else {
                    $ruleName = $singleRule;
                    $ruleParam = null;
                }
                
                // Apply the rule
                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $fieldErrors[] = "The {$field} field is required.";
                        }
                        break;
                        
                    case 'min':
                        if (strlen($value) < (int)$ruleParam) {
                            $fieldErrors[] = "The {$field} must be at least {$ruleParam} characters.";
                        }
                        break;
                        
                    case 'max':
                        if (strlen($value) > (int)$ruleParam) {
                            $fieldErrors[] = "The {$field} must not exceed {$ruleParam} characters.";
                        }
                        break;
                        
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fieldErrors[] = "The {$field} must be a valid email address.";
                        }
                        break;
                        
                    case 'numeric':
                        if (!is_numeric($value)) {
                            $fieldErrors[] = "The {$field} must be a number.";
                        }
                        break;
                        
                    case 'date':
                        if (!strtotime($value)) {
                            $fieldErrors[] = "The {$field} must be a valid date.";
                        }
                        break;
                        
                    case 'equals':
                        if ($value !== $data[$ruleParam]) {
                            $fieldErrors[] = "The {$field} must match the {$ruleParam} field.";
                        }
                        break;
                }
            }
            
            // Add errors for this field
            if (!empty($fieldErrors)) {
                $errors[$field] = $fieldErrors;
            }
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }
}