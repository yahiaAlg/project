<?php
/**
 * Helper functions for the application
 */

/**
 * Escape HTML output
 * 
 * @param string $text The text to escape
 * @return string The escaped text
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Get the current URL
 * 
 * @return string The current URL
 */
function current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Generate a CSRF token
 * 
 * @return string The CSRF token
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Check if the CSRF token is valid
 * 
 * @param string $token The token to check
 * @return bool True if valid, false otherwise
 */
function csrf_check($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Flash message system
 * 
 * @param string $type The type of message (success, error, info, warning)
 * @param string $message The message to display
 * @return void
 */
function flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get flash message and clear it
 * 
 * @return array|null The flash message array or null if none
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format a date
 * 
 * @param string $date The date to format
 * @param string $format The format (default: Y-m-d)
 * @return string The formatted date
 */
function format_date($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin/librarian
 * 
 * @return bool True if admin, false otherwise
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'librarian';
}

/**
 * Log an audit event
 * 
 * @param string $action The action performed
 * @param string $details Details about the action
 * @param int $user_id The user ID who performed the action
 * @return void
 */
function log_audit($action, $details, $user_id = null) {
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    $log_entry = date('Y-m-d H:i:s') . ' | User ID: ' . ($user_id ?? 'Guest') . 
                 ' | Action: ' . $action . ' | Details: ' . $details . PHP_EOL;
    
    file_put_contents(AUDIT_LOG, $log_entry, FILE_APPEND);
}

/**
 * Calculate due date for a loan
 * 
 * @param int $days Number of days for the loan
 * @return string The due date (Y-m-d format)
 */
function calculate_due_date($days = DEFAULT_LOAN_DAYS) {
    return date('Y-m-d', strtotime("+{$days} days"));
}

/**
 * Calculate overdue fine
 * 
 * @param string $due_date The due date
 * @return float The fine amount
 */
function calculate_fine($due_date) {
    $due = new DateTime($due_date);
    $today = new DateTime();
    
    if ($today <= $due) {
        return 0;
    }
    
    $days_overdue = $today->diff($due)->days;
    return $days_overdue * FINE_RATE_PER_DAY;
}

/**
 * Format currency
 * 
 * @param float $amount The amount to format
 * @return string The formatted amount
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Generate a pagination
 * 
 * @param int $current_page Current page number
 * @param int $total_pages Total number of pages
 * @param string $url_pattern URL pattern with :page placeholder
 * @return string HTML for pagination
 */
function paginate($current_page, $total_pages, $url_pattern) {
    $pagination = '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_url = str_replace(':page', $current_page - 1, $url_pattern);
        $pagination .= '<a href="' . $prev_url . '" class="page-link prev">&laquo; Previous</a>';
    } else {
        $pagination .= '<span class="page-link prev disabled">&laquo; Previous</span>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $page_url = str_replace(':page', $i, $url_pattern);
        if ($i == $current_page) {
            $pagination .= '<span class="page-link current">' . $i . '</span>';
        } else {
            $pagination .= '<a href="' . $page_url . '" class="page-link">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $next_url = str_replace(':page', $current_page + 1, $url_pattern);
        $pagination .= '<a href="' . $next_url . '" class="page-link next">Next &raquo;</a>';
    } else {
        $pagination .= '<span class="page-link next disabled">Next &raquo;</span>';
    }
    
    $pagination .= '</div>';
    
    return $pagination;
}