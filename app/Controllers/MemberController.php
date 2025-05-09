<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Member;
use App\Models\Loan;

/**
 * Member Controller
 * Handles member-related operations
 */
class MemberController extends Controller {
    protected $memberModel;
    protected $loanModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->memberModel = new Member();
        $this->loanModel = new Loan();
    }
    
    /**
     * Display a listing of members (admin only)
     */
    public function index() {
        $this->authorizeAdmin();
        
        $page = (int)$this->input('page', 1);
        $status = $this->input('status', 'all');
        $query = $this->input('query', '');
        
        $result = $this->memberModel->getAllWithFilters($page, $status, $query);
        
        $this->render('members/index', [
            'members' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['total_pages'],
                'total_records' => $result['total_records']
            ],
            'filters' => [
                'status' => $status,
                'query' => $query
            ]
        ]);
    }
    
    /**
     * Display member profile
     */
    public function profile() {
        $this->authenticate();
        
        $memberId = $_SESSION['user_id'];
        $member = $this->memberModel->find($memberId);
        
        if (!$member) {
            $this->flash('error', 'Member not found.');
            redirect('/dashboard');
        }
        
        // Get loan statistics
        $stats = $this->memberModel->getDashboardStats($memberId);
        
        $this->render('members/profile', [
            'member' => $member,
            'stats' => $stats
        ]);
    }
    
    /**
     * Update member profile
     */
    public function updateProfile() {
        $this->authenticate();
        
        $memberId = $_SESSION['user_id'];
        $member = $this->memberModel->find($memberId);
        
        if (!$member) {
            $this->flash('error', 'Member not found.');
            redirect('/dashboard');
        }
        
        // Validate form data
        $validation = $this->validate($_POST, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required'
        ]);
        
        if (!$validation['is_valid']) {
            $stats = $this->memberModel->getDashboardStats($memberId);
            
            $this->render('members/profile', [
                'errors' => $validation['errors'],
                'member' => array_merge($member, $_POST),
                'stats' => $stats
            ]);
            return;
        }
        
        // Check if email is changed and already exists
        $newEmail = $this->input('email');
        if ($newEmail !== $member['email'] && $this->memberModel->emailExists($newEmail)) {
            $this->flash('error', 'Email address is already in use.');
            
            $stats = $this->memberModel->getDashboardStats($memberId);
            
            $this->render('members/profile', [
                'member' => array_merge($member, $_POST),
                'stats' => $stats
            ]);
            return;
        }
        
        // Update password if provided
        $memberData = [
            'name' => $this->input('name'),
            'email' => $newEmail,
            'phone' => $this->input('phone'),
            'address' => $this->input('address')
        ];
        
        $password = $this->input('password');
        $passwordConfirm = $this->input('password_confirm');
        
        if (!empty($password)) {
            // Validate password
            if (strlen($password) < 6) {
                $this->flash('error', 'Password must be at least 6 characters.');
                
                $stats = $this->memberModel->getDashboardStats($memberId);
                
                $this->render('members/profile', [
                    'member' => array_merge($member, $_POST),
                    'stats' => $stats
                ]);
                return;
            }
            
            if ($password !== $passwordConfirm) {
                $this->flash('error', 'Passwords do not match.');
                
                $stats = $this->memberModel->getDashboardStats($memberId);
                
                $this->render('members/profile', [
                    'member' => array_merge($member, $_POST),
                    'stats' => $stats
                ]);
                return;
            }
            
            $memberData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // Update the member
        $success = $this->memberModel->update($memberId, $memberData);
        
        if (!$success) {
            $this->flash('error', 'Failed to update profile. Please try again.');
            
            $stats = $this->memberModel->getDashboardStats($memberId);
            
            $this->render('members/profile', [
                'member' => array_merge($member, $_POST),
                'stats' => $stats
            ]);
            return;
        }
        
        // Update session information
        $_SESSION['user_name'] = $memberData['name'];
        $_SESSION['user_email'] = $memberData['email'];
        
        // Log the action
        log_audit('Profile Updated', "Member updated their profile");
        
        $this->flash('success', 'Profile updated successfully.');
        redirect('/members/profile');
    }
    
    /**
     * Display a specific member (admin only)
     * 
     * @param int $id Member ID
     */
    public function show($id) {
        $this->authorizeAdmin();
        
        $member = $this->memberModel->find($id);
        
        if (!$member) {
            $this->flash('error', 'Member not found.');
            redirect('/members');
        }
        
        // Get active loans
        $result = $this->loanModel->getLoansForMember($id, 1, 'active');
        $activeLoans = $result['data'];
        
        // Get loan history
        $result = $this->loanModel->getLoansForMember($id, 1, 'returned');
        $loanHistory = $result['data'];
        
        // Get loan statistics
        $stats = $this->memberModel->getDashboardStats($id);
        
        $this->render('members/show', [
            'member' => $member,
            'activeLoans' => $activeLoans,
            'loanHistory' => $loanHistory,
            'stats' => $stats
        ]);
    }
    
    /**
     * Show form to create a new member (admin only)
     */
    public function create() {
        $this->authorizeAdmin();
        
        $this->render('members/create');
    }
    
    /**
     * Store a new member (admin only)
     */
    public function store() {
        $this->authorizeAdmin();
        
        // Validate form data
        $validation = $this->validate($_POST, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'phone' => 'required',
            'address' => 'required',
            'role' => 'required'
        ]);
        
        if (!$validation['is_valid']) {
            $this->render('members/create', [
                'errors' => $validation['errors'],
                'member' => $_POST
            ]);
            return;
        }
        
        $email = $this->input('email');
        
        // Check if email already exists
        if ($this->memberModel->emailExists($email)) {
            $this->flash('error', 'Email address is already registered.');
            $this->render('members/create', [
                'member' => $_POST
            ]);
            return;
        }
        
        // Create new member
        $memberData = [
            'name' => $this->input('name'),
            'email' => $email,
            'password' => password_hash($this->input('password'), PASSWORD_DEFAULT),
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
            'role' => $this->input('role'),
            'status' => 'active',
            'notes' => $this->input('notes', '')
        ];
        
        $memberId = $this->memberModel->create($memberData);
        
        if (!$memberId) {
            $this->flash('error', 'Failed to create member. Please try again.');
            $this->render('members/create', [
                'member' => $_POST
            ]);
            return;
        }
        
        // Log the action
        log_audit('Member Created', "Created member: {$memberData['name']} (ID: {$memberId})");
        
        $this->flash('success', 'Member created successfully.');
        redirect('/members/' . $memberId);
    }
    
    /**
     * Show form to edit a member (admin only)
     * 
     * @param int $id Member ID
     */
    public function edit($id) {
        $this->authorizeAdmin();
        
        $member = $this->memberModel->find($id);
        
        if (!$member) {
            $this->flash('error', 'Member not found.');
            redirect('/members');
        }
        
        $this->render('members/edit', [
            'member' => $member
        ]);
    }
    
    /**
     * Update a member (admin only)
     * 
     * @param int $id Member ID
     */
    public function update($id) {
        $this->authorizeAdmin();
        
        $member = $this->memberModel->find($id);
        
        if (!$member) {
            $this->flash('error', 'Member not found.');
            redirect('/members');
        }
        
        // Validate form data
        $validation = $this->validate($_POST, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
            'role' => 'required',
            'status' => 'required'
        ]);
        
        if (!$validation['is_valid']) {
            $this->render('members/edit', [
                'errors' => $validation['errors'],
                'member' => array_merge($member, $_POST)
            ]);
            return;
        }
        
        // Check if email is changed and already exists
        $newEmail = $this->input('email');
        if ($newEmail !== $member['email'] && $this->memberModel->emailExists($newEmail)) {
            $this->flash('error', 'Email address is already in use.');
            $this->render('members/edit', [
                'member' => array_merge($member, $_POST)
            ]);
            return;
        }
        
        // Prepare member data
        $memberData = [
            'name' => $this->input('name'),
            'email' => $newEmail,
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
            'role' => $this->input('role'),
            'status' => $this->input('status'),
            'notes' => $this->input('notes', '')
        ];
        
        // Update password if provided
        $password = $this->input('password');
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $this->flash('error', 'Password must be at least 6 characters.');
                $this->render('members/edit', [
                    'member' => array_merge($member, $_POST)
                ]);
                return;
            }
            
            $memberData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // Update the member
        $success = $this->memberModel->update($id, $memberData);
        
        if (!$success) {
            $this->flash('error', 'Failed to update member. Please try again.');
            $this->render('members/edit', [
                'member' => array_merge($member, $_POST)
            ]);
            return;
        }
        
        // Log the action
        log_audit('Member Updated', "Updated member: {$memberData['name']} (ID: {$id})");
        
        $this->flash('success', 'Member updated successfully.');
        redirect('/members/' . $id);
    }
    
    /**
     * Show confirmation page for deleting a member (admin only)
     * 
     * @param int $id Member ID
     */
    public function delete($id) {
        $this->authorizeAdmin();
        
        $member = $this->memberModel->find($id);
        
        if (!$member) {
            $this->flash('error', 'Member not found.');
            redirect('/members');
        }
        
        // Check if member has active loans
        $activeLoans = $this->memberModel->getActiveLoansCount($id);
        
        $this->render('members/delete', [
            'member' => $member,
            'activeLoans' => $activeLoans
        ]);
    }
    
    /**
     * Delete a member (admin only)
     * 
     * @param int $id Member ID
     */
    public function destroy($id) {
        $this->authorizeAdmin();
        
        $member = $this->memberModel->find($id);
        
        if (!$member) {
            $this->flash('error', 'Member not found.');
            redirect('/members');
        }
        
        // Check if member has active loans
        $activeLoans = $this->memberModel->getActiveLoansCount($id);
        
        if ($activeLoans > 0) {
            $this->flash('error', 'Cannot delete a member with active loans.');
            redirect('/members/' . $id);
            return;
        }
        
        // Delete the member
        $success = $this->memberModel->delete($id);
        
        if (!$success) {
            $this->flash('error', 'Failed to delete member. Please try again.');
            redirect('/members/' . $id);
            return;
        }
        
        // Log the action
        log_audit('Member Deleted', "Deleted member: {$member['name']} (ID: {$id})");
        
        $this->flash('success', 'Member deleted successfully.');
        redirect('/members');
    }
}