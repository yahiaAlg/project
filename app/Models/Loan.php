<?php
namespace App\Models;

use Core\Model;

/**
 * Loan Model
 * Handles database operations for book loans
 */
class Loan extends Model {
    protected $table = 'loans';
    protected $fillable = [
        'book_id', 'member_id', 'issue_date', 'due_date', 
        'returned_at', 'fine_amount', 'notes'
    ];
    
    /**
     * Get all loans with book and member info
     * 
     * @param int $page Current page
     * @param string $status Loan status (all, active, returned, overdue)
     * @return array Paginated result
     */
    public function getAllWithDetails($page = 1, $status = 'all') {
        $query = "
            SELECT l.*, b.title as book_title, b.author as book_author, 
                   m.name as member_name, m.email as member_email
            FROM {$this->table} l
            JOIN books b ON l.book_id = b.id
            JOIN members m ON l.member_id = m.id
        ";
        
        $where = '';
        $params = [];
        
        // Add status filter
        if ($status === 'active') {
            $where = "l.returned_at IS NULL";
        } elseif ($status === 'returned') {
            $where = "l.returned_at IS NOT NULL";
        } elseif ($status === 'overdue') {
            $where = "l.returned_at IS NULL AND l.due_date < CURDATE()";
        }
        
        if ($where) {
            $query .= " WHERE {$where}";
        }
        
        $query .= " ORDER BY l.issue_date DESC";
        
        return $this->db->paginate($query, $page, ITEMS_PER_PAGE, $params);
    }
    
    /**
     * Get loans for a specific member
     * 
     * @param int $memberId The member ID
     * @param int $page Current page
     * @param string $status Loan status (all, active, returned, overdue)
     * @return array Paginated result
     */
    public function getLoansForMember($memberId, $page = 1, $status = 'all') {
        $query = "
            SELECT l.*, b.title as book_title, b.author as book_author, 
                   b.cover_image as book_cover
            FROM {$this->table} l
            JOIN books b ON l.book_id = b.id
            WHERE l.member_id = ?
        ";
        
        $params = [$memberId];
        
        // Add status filter
        if ($status === 'active') {
            $query .= " AND l.returned_at IS NULL";
        } elseif ($status === 'returned') {
            $query .= " AND l.returned_at IS NOT NULL";
        } elseif ($status === 'overdue') {
            $query .= " AND l.returned_at IS NULL AND l.due_date < CURDATE()";
        }
        
        $query .= " ORDER BY l.issue_date DESC";
        
        return $this->db->paginate($query, $page, ITEMS_PER_PAGE, $params);
    }
    
    /**
     * Get loan with book and member details
     * 
     * @param int $loanId The loan ID
     * @return array|false The loan or false if not found
     */
    public function getLoanWithDetails($loanId) {
        $query = "
            SELECT l.*, b.title as book_title, b.author as book_author, 
                   b.isbn as book_isbn, b.cover_image as book_cover,
                   m.name as member_name, m.email as member_email
            FROM {$this->table} l
            JOIN books b ON l.book_id = b.id
            JOIN members m ON l.member_id = m.id
            WHERE l.id = ?
            LIMIT 1
        ";
        
        return $this->db->fetchOne($query, [$loanId]);
    }
    
    /**
     * Get overdue loans
     * 
     * @param int $page Current page
     * @return array Paginated result
     */
    public function getOverdueLoans($page = 1) {
        $query = "
            SELECT l.*, b.title as book_title, b.author as book_author, 
                   m.name as member_name, m.email as member_email,
                   DATEDIFF(CURDATE(), l.due_date) as days_overdue
            FROM {$this->table} l
            JOIN books b ON l.book_id = b.id
            JOIN members m ON l.member_id = m.id
            WHERE l.returned_at IS NULL AND l.due_date < CURDATE()
            ORDER BY l.due_date ASC
        ";
        
        return $this->db->paginate($query, $page, ITEMS_PER_PAGE);
    }
    
    /**
     * Return a book
     * 
     * @param int $loanId The loan ID
     * @param float $fineAmount Fine amount if any
     * @param string $notes Additional notes
     * @return bool Success or failure
     */
    public function returnBook($loanId, $fineAmount = 0, $notes = '') {
        $loan = $this->find($loanId);
        
        if (!$loan || $loan['returned_at']) {
            return false;
        }
        
        $bookModel = new \App\Models\Book();
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update loan record
            $this->update($loanId, [
                'returned_at' => date('Y-m-d H:i:s'),
                'fine_amount' => $fineAmount,
                'notes' => $notes
            ]);
            
            // Update book availability
            $bookModel->updateAvailability($loan['book_id'], 'return');
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            error_log('Error returning book: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Renew a loan
     * 
     * @param int $loanId The loan ID
     * @param int $days Number of days to extend
     * @return bool Success or failure
     */
    public function renewLoan($loanId, $days = DEFAULT_LOAN_DAYS) {
        $loan = $this->find($loanId);
        
        if (!$loan || $loan['returned_at']) {
            return false;
        }
        
        // Calculate new due date
        $dueDate = date('Y-m-d', strtotime($loan['due_date'] . " +{$days} days"));
        
        return $this->update($loanId, [
            'due_date' => $dueDate,
            'notes' => trim($loan['notes'] . ' Renewed on ' . date('Y-m-d') . '.')
        ]);
    }
    
    /**
     * Create a new loan
     * 
     * @param int $bookId The book ID
     * @param int $memberId The member ID
     * @param string $dueDate Due date
     * @param string $notes Additional notes
     * @return int|false The new loan ID or false on failure
     */
    public function createLoan($bookId, $memberId, $dueDate = null, $notes = '') {
        if ($dueDate === null) {
            $dueDate = calculate_due_date();
        }
        
        $bookModel = new \App\Models\Book();
        $memberModel = new \App\Models\Member();
        
        // Check if book is available
        if (!$bookModel->isAvailable($bookId)) {
            return false;
        }
        
        // Check if member can borrow more books
        if (!$memberModel->canBorrow($memberId)) {
            return false;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Create loan record
            $loanId = $this->create([
                'book_id' => $bookId,
                'member_id' => $memberId,
                'issue_date' => date('Y-m-d'),
                'due_date' => $dueDate,
                'notes' => $notes
            ]);
            
            // Update book availability
            $bookModel->updateAvailability($bookId, 'loan');
            
            // Commit transaction
            $this->db->commit();
            return $loanId;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            error_log('Error creating loan: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get loan statistics
     * 
     * @return array Statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total active loans
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE returned_at IS NULL";
        $result = $this->db->fetchOne($query);
        $stats['active_loans'] = $result ? (int)$result['count'] : 0;
        
        // Total overdue loans
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE returned_at IS NULL AND due_date < CURDATE()";
        $result = $this->db->fetchOne($query);
        $stats['overdue_loans'] = $result ? (int)$result['count'] : 0;
        
        // Total loans this month
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE MONTH(issue_date) = MONTH(CURDATE()) AND YEAR(issue_date) = YEAR(CURDATE())";
        $result = $this->db->fetchOne($query);
        $stats['loans_this_month'] = $result ? (int)$result['count'] : 0;
        
        // Loans by month (last 6 months)
        $query = "
            SELECT 
                DATE_FORMAT(issue_date, '%Y-%m') as month,
                COUNT(*) as loan_count
            FROM {$this->table}
            WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";
        $stats['loans_by_month'] = $this->db->fetchAll($query);
        
        return $stats;
    }
}