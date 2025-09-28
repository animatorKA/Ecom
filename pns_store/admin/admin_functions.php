<?php
// Include core dependencies
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/DatabaseHelper.php';

// Constants for admin pages
define('ADMIN_ROLES', ['admin']);

/**
 * Verify CSRF token
 * @return bool
 */
function verifyCSRFToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Get CSRF token input field
 * @return string HTML input field
 */
function getCSRFToken() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

/**
 * Validate and sanitize input
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input));
}

/**
 * Display error message
 * @param string $message
 * @return string HTML for error message
 */
function displayError($message) {
    return '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display success message
 * @param string $message
 * @return string HTML for success message
 */
function displaySuccess($message) {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

/**
 * Set success message in session
 * @param string $message
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Set error message in session
 * @param string $message
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Get success message from session and clear it
 * @return string|null
 */
function getSuccessMessage() {
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return $message;
    }
    return null;
}

/**
 * Get error message from session and clear it
 * @return string|null
 */
function getErrorMessage() {
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $message;
    }
    return null;
}

/**
 * Check admin permissions
 * @return bool
 */
function checkAdminPermissions() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ADMIN_ROLES);
}

/**
 * Get database helper instance
 * @return DatabaseHelper
 */
function getDB() {
    global $pdo;
    require_once __DIR__ . '/../includes/DatabaseHelper.php';
    static $db = null;
    if ($db === null) {
        $db = new DatabaseHelper($pdo);
    }
    return $db;
}

/**
 * Handle database operation with proper error handling
 * @param callable $operation The database operation to perform
 * @param string $successMessage Success message
 * @param string $errorContext Context for error logging
 * @param bool $useTransaction Whether to use transaction
 * @return mixed The result of the operation
 */
function handleDatabaseOperation($operation, $successMessage, $errorContext, $useTransaction = false) {
    $db = getDB();
    
    try {
        if ($useTransaction) {
            $db->beginTransaction();
        }
        
        $result = $operation($db);
        
        if ($useTransaction) {
            $db->commit();
        }
        
        if ($successMessage) {
            setSuccessMessage($successMessage);
        }
        
        return $result;
    } catch (Exception $e) {
        if ($useTransaction) {
            $db->rollBack();
        }
        
        error_log("{$errorContext}: " . $e->getMessage());
        throw new Exception($e->getMessage());
    }
}

/**
 * Get form validator instance
 * @param array $data Form data
 * @return FormValidator
 */
function getValidator($data = []) {
    require_once __DIR__ . '/../includes/FormValidator.php';
    return new FormValidator($data);
}

/**
 * Validate form data with error handling
 * @param array $data Form data
 * @param callable $rules Function that adds rules to validator
 * @param callable $onSuccess Callback when validation succeeds
 * @param string $redirectUrl URL to redirect on failure
 * @return mixed Result of onSuccess callback
 */
function validateForm($data, $rules, $onSuccess, $redirectUrl = null) {
    $validator = getValidator($data);
    $rules($validator);
    
    if (!$validator->validate()) {
        setErrorMessage($validator->getFirstError());
        if ($redirectUrl) {
            header('Location: ' . $redirectUrl);
            exit;
        }
        return false;
    }
    
    try {
        return $onSuccess($data, $validator);
    } catch (Exception $e) {
        setErrorMessage($e->getMessage());
        if ($redirectUrl) {
            header('Location: ' . $redirectUrl);
            exit;
        }
        return false;
    }
}

/**
 * Generate a CSRF token input field
 * @return string HTML for CSRF token input
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

/**
 * Display validation error for a field
 * @param string $field Field name
 * @param FormValidator $validator Validator instance
 * @return string HTML for error message
 */
function validationError($field, $validator) {
    if ($validator && $validator->hasError($field)) {
        return '<div class="invalid-feedback d-block">' . htmlspecialchars($validator->getError($field)) . '</div>';
    }
    return '';
}

// Verify admin permissions or redirect
if (!checkAdminPermissions()) {
    header('Location: /pns_store/login.php');
    exit;
}