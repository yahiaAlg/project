<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Member;

/**
 * Auth Controller
 * Handles user authentication
 */
class AuthController extends Controller {
    /**
     * Show login form
     */
    public function loginForm() {
        // Redirect if already logged in
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        
        $this->render('auth/login');
    }
    
    /**
     * Process login
     */
    public function login() {
        // Validate form data
        $validation = $this->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!$validation['is_valid']) {
            $this->flash('error', 'Invalid email or password.');
            $this->render('auth/login', [
                'errors' => $validation['errors'],
                'email' => $this->input('email')
            ]);
            return;
        }
        
        $email = $this->input('email');
        $password = $this->input('password');
        
        // Attempt to find user
        $memberModel = new Member();
        $user = $memberModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->flash('error', 'Invalid email or password.');
            $this->render('auth/login', [
                'email' => $email
            ]);
            return;
        }
        
        // Set up session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Log the login
        log_audit('Login', 'User logged in', $user['id']);
        
        // Redirect based on role
        $this->flash('success', 'Login successful. Welcome back, ' . $user['name'] . '!');
        redirect('/dashboard');
    }
    
    /**
     * Show registration form
     */
    public function registerForm() {
        // Redirect if already logged in
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        
        $this->render('auth/register');
    }
    
    /**
     * Process registration
     */
    public function register() {
        // Validate form data
        $validation = $this->validate($_POST, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirm' => 'required|equals:password'
        ]);
        
        if (!$validation['is_valid']) {
            $this->render('auth/register', [
                'errors' => $validation['errors'],
                'name' => $this->input('name'),
                'email' => $this->input('email')
            ]);
            return;
        }
        
        $name = $this->input('name');
        $email = $this->input('email');
        $password = $this->input('password');
        
        // Check if email already exists
        $memberModel = new Member();
        if ($memberModel->emailExists($email)) {
            $this->flash('error', 'Email address is already registered.');
            $this->render('auth/register', [
                'name' => $name,
                'email' => $email
            ]);
            return;
        }
        
        // Create new member
        $memberData = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'member', // Default role
            'status' => 'active'
        ];
        
        $userId = $memberModel->create($memberData);
        
        if (!$userId) {
            $this->flash('error', 'Registration failed. Please try again.');
            $this->render('auth/register', [
                'name' => $name,
                'email' => $email
            ]);
            return;
        }
        
        // Log the registration
        log_audit('Registration', 'New member registered', $userId);
        
        // Set up session
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'member';
        
        $this->flash('success', 'Registration successful. Welcome, ' . $name . '!');
        redirect('/dashboard');
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Log the logout
        if (isset($_SESSION['user_id'])) {
            log_audit('Logout', 'User logged out', $_SESSION['user_id']);
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        $this->flash('success', 'You have been logged out successfully.');
        redirect('/login');
    }
}