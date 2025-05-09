<?php
/**
 * Main configuration file
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Library Management System');
define('APP_URL', 'http://localhost:8000');
define('APP_ROOT', dirname(__DIR__));

// Session configuration
define('SESSION_NAME', 'library_session');
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false);
define('SESSION_HTTP_ONLY', true);

// Error reporting
define('DISPLAY_ERRORS', true);
ini_set('display_errors', DISPLAY_ERRORS);
error_reporting(E_ALL);

// Logging configuration
define('LOG_ERRORS', true);
define('ERROR_LOG', APP_ROOT . '/logs/error.log');
define('AUDIT_LOG', APP_ROOT . '/logs/audit.log');

// Authentication
define('AUTH_SALT', 'change_this_to_a_random_string');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Allowed image types for book covers
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024); // 2MB

// Loan settings
define('DEFAULT_LOAN_DAYS', 14);
define('MAX_LOANS_PER_MEMBER', 5);
define('FINE_RATE_PER_DAY', 0.50); // $0.50 per day