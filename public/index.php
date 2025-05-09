<?php
/**
 * Front Controller
 * All requests are routed through this file
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Set up autoloading
require_once APP_ROOT . '/vendor/autoload.php';

// Create router
$router = new Core\Router();

// Load routes
$router->loadRoutes(APP_ROOT . '/app/routes.php');

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Handle POST method overrides (for PUT, DELETE methods)
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

// Dispatch the request
$router->dispatch($method, $uri);