<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Loan;
use App\Models\Book;
use App\Models\Member;

/**
 * Loan Controller
 * Handles loan-related operations
 */
class LoanController extends Controller {
    protected $loanModel;
    protected $bookModel;
    protected $memberModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->loanModel = new Loan();
        $this->bookModel = new Book();
        $this->memberModel = new Member();
    }
    
    /**
     * Display a listing of loans
     */
    public function index() {
        $this->authenticate();
        
        $page = (int)$this->input('page', 1);
        $status = $this->input('status', 'all');
        
        if (is_admin()) {
            // Admin/Librarian view - show all loans
            $result = $this->loanModel->getAllWithDetails($page, $status);
            
            $this->render('loans/index', [
                'loans' => $result['data'],
                'pagination' => [
                    'current_page' => $result['current_page'],
                    'total_pages' => $result['total_pages'],
                    'total_records' => $result['total_records']
                ],
                'status' => $status
            ]);
        } else {
            // Member view - show only their loans
            $memberId = $_SESSION['user_id'];
            $result = $this->loanModel->getLoansForMember($memberId, $page, $status);
            
            $this->render('loans/member_loans', [
                'loans' => $result['data'],
                'pagination' => [
                    'current_page' => $result['current_page'],
                    'total_pages' => $result['total_pages'],
                    'total_records' => $result['total_records']
                ],
                'status' => $status
            ]);
        }
    }
    
    /**
     * Show loan history for the current member
     */
    public function history() {
        $this->authenticate();
        
        $page = (int)$this->input('page', 1);
        $memberId = $_SESSION['user_id'];
        
        $result = $this->loanModel->getLoansForMember($memberId, $page, 'returned');
        
        $this->render('loans/history', [
            'loans' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['total_pages'],
                'total_records' => $result['total_records']
            ]
        ]);
    }
    
    /**
     * Display a specific loan
     * 
     * @param int $id Loan ID
     */
    public function show($id) {
        $this->authenticate();
        
        $loan = $this->loanModel->getLoanWithDetails($id);
        
        if (!$loan) {
            $this->flash('error', 'Loan not found.');
            redirect('/loans');
        }
        
        // Check if the loan belongs to the current member (if not admin)
        if (!is_admin() && $loan['member_id'] != $_SESSION['user_id']) {
            $this->flash('error', 'You do not have permission to view this loan.');
            redirect('/loans');
        }
        
        // Calculate fine if overdue
        $fine = 0;
        if (!$loan['returned_at'] && strtotime($loan['due_date']) < time()) {
            $fine = calculate_fine($loan['due_date']);
        }
        
        $this->render('loans/show', [
            'loan' => $loan,
            'fine' => $fine
        ]);
    }
    
    /**
     * Show form to create a new loan
     */
    public function create() {
        $this->authorizeAdmin();
        
        $books = $this->bookModel->db->fetchAll("
            SELECT id, title, author, available_copies 
            FROM books 
            WHERE available_copies > 0
            ORDER BY title ASC
        ");
        
        $members = $this->memberModel->db->fetchAll("
            SELECT id, name, email 
            FROM members 
            WHERE status = 'active'
            ORDER BY name ASC
        ");
        
        $this->render('loans/create', [
            'books' => $books,
            'members' => $members,
            'default_due_date' => calculate_due_date()
        ]);
    }
    
    /**
     * Store a new loan
     */
    public function store() {
        $this->authorizeAdmin();
        
        // Validate form data
        $validation = $this->validate($_POST, [
            'book_id' => 'required|numeric',
            'member_id' => 'required|numeric',
            'due_date' => 'required|date'
        ]);
        
        if (!$validation['is_valid']) {
            $books = $this->bookModel->db->fetchAll("
                SELECT id, title, author, available_copies 
                FROM books 
                WHERE available_copies > 0
                ORDER BY title ASC
            ");
            
            $members = $this->memberModel->db->fetchAll("
                SELECT id, name, email 
                FROM members 
                WHERE status = 'active'
                ORDER BY name ASC
            ");
            
            $this->render('loans/create', [
                'errors' => $validation['errors'],
                'books' => $books,
                'members' => $members,
                'loan' => $_POST,
                'default_due_date' => calculate_due_date()
            ]);
            return;
        }
        
        $bookId = (int)$this->input('book_id');
        $memberId = (int)$this->input('member_id');
        $dueDate = $this->input('due_date');
        $notes = $this->input('notes', '');
        
        // Create the loan
        $loanId = $this->loanModel->createLoan($bookId, $memberId, $dueDate, $notes);
        
        if (!$loanId) {
            $this->flash('error', 'Failed to create loan. Please check book availability and member loan limit.');
            
            $books = $this->bookModel->db->fetchAll("
                SELECT id, title, author, available_copies 
                FROM books 
                WHERE available_copies > 0
                ORDER BY title ASC
            ");
            
            $members = $this->memberModel->db->fetchAll("
                SELECT id, name, email 
                FROM members 
                WHERE status = 'active'
                ORDER BY name ASC
            ");
            
            $this->render('loans/create', [
                'books' => $books,
                'members' => $members,
                'loan' => $_POST,
                'default_due_date' => calculate_due_date()
            ]);
            return;
        }
        
        // Get book and member details for logging
        $book = $this->bookModel->find($bookId);
        $member = $this->memberModel->find($memberId);
        
        // Log the action
        log_audit(
            'Loan Created', 
            "Book: {$book['title']} loaned to {$member['name']} (Due: {$dueDate})"
        );
        
        $this->flash('success', 'Loan created successfully.');
        redirect('/loans/' . $loanId);
    }
    
    /**
     * Process book return
     * 
     * @param int $id Loan ID
     */
    public function returnBook($id) {
        $this->authorizeAdmin();
        
        $loan = $this->loanModel->getLoanWithDetails($id);
        
        if (!$loan || $loan['returned_at']) {
            $this->flash('error', 'Invalid loan or book already returned.');
            redirect('/loans');
        }
        
        // Calculate fine if overdue
        $fine = 0;
        if (strtotime($loan['due_date']) < time()) {
            $fine = calculate_fine($loan['due_date']);
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fineAmount = (float)$this->input('fine_amount', 0);
            $notes = $this->input('notes', '');
            
            // Return the book
            $success = $this->loanModel->returnBook($id, $fineAmount, $notes);
            
            if (!$success) {
                $this->flash('error', 'Failed to return book. Please try again.');
                redirect('/loans/' . $id);
                return;
            }
            
            // Log the action
            log_audit(
                'Book Returned', 
                "Book: {$loan['book_title']} returned by {$loan['member_name']} (Fine: {$fineAmount})"
            );
            
            $this->flash('success', 'Book returned successfully.');
            redirect('/loans');
        } else {
            // Show the return form
            $this->render('loans/return', [
                'loan' => $loan,
                'fine' => $fine
            ]);
        }
    }
    
    /**
     * Renew a loan
     * 
     * @param int $id Loan ID
     */
    public function renew($id) {
        $this->authenticate();
        
        $loan = $this->loanModel->find($id);
        
        if (!$loan || $loan['returned_at']) {
            $this->flash('error', 'Invalid loan or book already returned.');
            redirect('/loans');
        }
        
        // Check if the loan belongs to the current member (if not admin)
        if (!is_admin() && $loan['member_id'] != $_SESSION['user_id']) {
            $this->flash('error', 'You do not have permission to renew this loan.');
            redirect('/loans');
        }
        
        // Check if loan is overdue (only admin can renew overdue loans)
        if (!is_admin() && strtotime($loan['due_date']) < time()) {
            $this->flash('error', 'This loan is overdue and cannot be renewed. Please contact the librarian.');
            redirect('/loans/' . $id);
        }
        
        // Renew the loan
        $success = $this->loanModel->renewLoan($id);
        
        if (!$success) {
            $this->flash('error', 'Failed to renew loan. Please try again.');
            redirect('/loans/' . $id);
            return;
        }
        
        // Get updated loan details for logging
        $updatedLoan = $this->loanModel->getLoanWithDetails($id);
        
        // Log the action
        log_audit(
            'Loan Renewed', 
            "Book: {$updatedLoan['book_title']} renewed until {$updatedLoan['due_date']}"
        );
        
        $this->flash('success', 'Loan renewed successfully. New due date: ' . format_date($updatedLoan['due_date']));
        redirect('/loans/' . $id);
    }
    
    /**
     * Show confirmation for deleting a loan
     * 
     * @param int $id Loan ID
     */
    public function delete($id) {
        $this->authorizeAdmin();
        
        $loan = $this->loanModel->getLoanWithDetails($id);
        
        if (!$loan) {
            $this->flash('error', 'Loan not found.');
            redirect('/loans');
        }
        
        $this->render('loans/delete', [
            'loan' => $loan
        ]);
    }
    
    /**
     * Delete a loan
     * 
     * @param int $id Loan ID
     */
    public function destroy($id) {
        $this->authorizeAdmin();
        
        $loan = $this->loanModel->getLoanWithDetails($id);
        
        if (!$loan) {
            $this->flash('error', 'Loan not found.');
            redirect('/loans');
        }
        
        // If loan is active, update book availability
        if (!$loan['returned_at']) {
            $this->bookModel->updateAvailability($loan['book_id'], 'return');
        }
        
        // Delete the loan
        $success = $this->loanModel->delete($id);
        
        if (!$success) {
            $this->flash('error', 'Failed to delete loan. Please try again.');
            redirect('/loans/' . $id);
            return;
        }
        
        // Log the action
        log_audit(
            'Loan Deleted', 
            "Deleted loan for book: {$loan['book_title']} and member: {$loan['member_name']}"
        );
        
        $this->flash('success', 'Loan deleted successfully.');
        redirect('/loans');
    }
}