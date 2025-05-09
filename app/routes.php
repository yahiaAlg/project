<?php
/**
 * Application Routes
 * 
 * Define all routes for the application.
 * The router supports methods: get, post, put, delete
 * Route parameters can be defined using colon syntax: :id
 */

// Public routes
$router->get('/', 'BookController', 'index');
$router->get('/login', 'AuthController', 'loginForm');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'registerForm');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');

// Books
$router->get('/books', 'BookController', 'index');
$router->get('/books/search', 'BookController', 'search');
$router->get('/books/:id', 'BookController', 'show');

// Authenticated routes
// Books (Admin only)
$router->get('/books/create', 'BookController', 'create');
$router->post('/books', 'BookController', 'store');
$router->get('/books/:id/edit', 'BookController', 'edit');
$router->post('/books/:id', 'BookController', 'update');
$router->get('/books/:id/delete', 'BookController', 'delete');

// Members
$router->get('/members', 'MemberController', 'index');
$router->get('/members/profile', 'MemberController', 'profile');
$router->post('/members/profile', 'MemberController', 'updateProfile');
$router->get('/members/:id', 'MemberController', 'show');

// Members (Admin only)
$router->get('/members/create', 'MemberController', 'create');
$router->post('/members', 'MemberController', 'store');
$router->get('/members/:id/edit', 'MemberController', 'edit');
$router->post('/members/:id', 'MemberController', 'update');
$router->get('/members/:id/delete', 'MemberController', 'delete');

// Loans
$router->get('/loans', 'LoanController', 'index');
$router->get('/loans/history', 'LoanController', 'history');
$router->get('/loans/:id', 'LoanController', 'show');

// Loans (Admin only)
$router->get('/loans/create', 'LoanController', 'create');
$router->post('/loans', 'LoanController', 'store');
$router->get('/loans/:id/return', 'LoanController', 'returnBook');
$router->get('/loans/:id/renew', 'LoanController', 'renew');
$router->get('/loans/:id/delete', 'LoanController', 'delete');

// Dashboard
$router->get('/dashboard', 'DashboardController', 'index');

// Audit (Admin only)
$router->get('/audit', 'AuditController', 'index');
$router->get('/audit/export', 'AuditController', 'export');

// API routes (for AJAX requests)
$router->get('/api/books', 'ApiController', 'getBooks');
$router->get('/api/members', 'ApiController', 'getMembers');
$router->get('/api/stats', 'ApiController', 'getStats');