<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Book;
use App\Models\Member;
use App\Models\Loan;
use App\Models\Stats;

/**
 * Dashboard Controller
 * Handles dashboard-related operations
 */
class DashboardController extends Controller {
    /**
     * Display the appropriate dashboard based on user role
     */
    public function index() {
        $this->authenticate();
        
        if (is_admin()) {
            $this->adminDashboard();
        } else {
            $this->memberDashboard();
        }
    }
    
    /**
     * Display admin dashboard
     */
    protected function adminDashboard() {
        $bookModel = new Book();
        $memberModel = new Member();
        $loanModel = new Loan();
        $statsModel = new Stats();
        
        // Get counts
        $totalBooks = $bookModel->count();
        $availableBooks = $bookModel->count('available_copies > 0');
        $totalMembers = $memberModel->count();
        $activeMembers = $memberModel->count('status = "active"');
        
        // Get loan statistics
        $loanStats = $loanModel->getStats();
        
        // Get popular books
        $popularBooks = $bookModel->getPopularBooks(5);
        
        // Get recent books
        $recentBooks = $bookModel->getRecentBooks(5);
        
        // Get members with overdue books
        $overdueMembers = $memberModel->getMembersWithOverdueBooks();
        
        // Get system statistics
        $systemStats = $statsModel->getSystemStats();
        
        $this->render('dashboard/librarian', [
            'totalBooks' => $totalBooks,
            'availableBooks' => $availableBooks,
            'totalMembers' => $totalMembers,
            'activeMembers' => $activeMembers,
            'loanStats' => $loanStats,
            'popularBooks' => $popularBooks,
            'recentBooks' => $recentBooks,
            'overdueMembers' => $overdueMembers,
            'systemStats' => $systemStats
        ]);
    }
    
    /**
     * Display member dashboard
     */
    protected function memberDashboard() {
        $memberId = $_SESSION['user_id'];
        $memberModel = new Member();
        $loanModel = new Loan();
        $bookModel = new Book();
        
        // Get member information
        $member = $memberModel->find($memberId);
        
        // Get loan statistics
        $stats = $memberModel->getDashboardStats($memberId);
        
        // Get active loans
        $activeLoans = $loanModel->getLoansForMember($memberId, 1, 'active');
        
        // Get overdue loans
        $overdueLoans = $loanModel->getLoansForMember($memberId, 1, 'overdue');
        
        // Get recommended books (based on categories borrowed before)
        $query = "
            SELECT DISTINCT b.category
            FROM loans l
            JOIN books b ON l.book_id = b.id
            WHERE l.member_id = ?
            ORDER BY l.issue_date DESC
            LIMIT 3
        ";
        
        $categories = $memberModel->db->fetchAll($query, [$memberId]);
        $recommendedBooks = [];
        
        if (!empty($categories)) {
            $categoryList = [];
            foreach ($categories as $category) {
                $categoryList[] = $category['category'];
            }
            
            $whereClause = "category IN ('" . implode("','", $categoryList) . "') AND available_copies > 0";
            $recommendedBooks = $bookModel->db->fetchAll(
                "SELECT * FROM books WHERE {$whereClause} ORDER BY RAND() LIMIT 4"
            );
        } else {
            // If no categories found, recommend popular books
            $recommendedBooks = $bookModel->getPopularBooks(4);
        }
        
        $this->render('dashboard/member', [
            'member' => $member,
            'stats' => $stats,
            'activeLoans' => $activeLoans['data'],
            'overdueLoans' => $overdueLoans['data'],
            'recommendedBooks' => $recommendedBooks
        ]);
    }
}