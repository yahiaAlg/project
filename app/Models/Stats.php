<?php
namespace App\Models;

use Core\Model;

/**
 * Stats Model
 * Handles statistics calculations
 */
class Stats extends Model {
    /**
     * Get system statistics
     * 
     * @return array System statistics
     */
    public function getSystemStats() {
        $stats = [];
        $db = $this->db;
        
        // Books by category
        $query = "
            SELECT category, COUNT(*) as count
            FROM books
            GROUP BY category
            ORDER BY count DESC
        ";
        $stats['books_by_category'] = $db->fetchAll($query);
        
        // Loans by month (last 12 months)
        $query = "
            SELECT 
                DATE_FORMAT(issue_date, '%Y-%m') as month,
                COUNT(*) as loan_count
            FROM loans
            WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";
        $stats['loans_by_month'] = $db->fetchAll($query);
        
        // Most active borrowers
        $query = "
            SELECT m.id, m.name, COUNT(l.id) as loan_count
            FROM members m
            JOIN loans l ON m.id = l.member_id
            GROUP BY m.id
            ORDER BY loan_count DESC
            LIMIT 10
        ";
        $stats['active_borrowers'] = $db->fetchAll($query);
        
        // Fines collected
        $query = "
            SELECT 
                DATE_FORMAT(returned_at, '%Y-%m') as month,
                SUM(fine_amount) as fine_amount
            FROM loans
            WHERE 
                returned_at IS NOT NULL AND 
                fine_amount > 0 AND
                returned_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";
        $stats['fines_by_month'] = $db->fetchAll($query);
        
        // Total statistics
        $stats['totals'] = [
            'books' => $db->count('books'),
            'members' => $db->count('members'),
            'loans' => $db->count('loans'),
            'active_loans' => $db->count('loans', 'returned_at IS NULL'),
            'overdue_loans' => $db->count('loans', 'returned_at IS NULL AND due_date < CURDATE()'),
            'fines_collected' => $this->getTotalFinesCollected()
        ];
        
        return $stats;
    }
    
    /**
     * Get total fines collected
     * 
     * @return float Total fines collected
     */
    protected function getTotalFinesCollected() {
        $query = "SELECT SUM(fine_amount) as total FROM loans WHERE fine_amount > 0";
        $result = $this->db->fetchOne($query);
        return $result ? (float)$result['total'] : 0;
    }
    
    /**
     * Get book loan history for chart
     * 
     * @param int $bookId Book ID
     * @return array Loan history by month
     */
    public function getBookLoanHistory($bookId) {
        $query = "
            SELECT 
                DATE_FORMAT(issue_date, '%Y-%m') as month,
                COUNT(*) as loan_count
            FROM loans
            WHERE 
                book_id = ? AND
                issue_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";
        
        return $this->db->fetchAll($query, [$bookId]);
    }
    
    /**
     * Get member loan history for chart
     * 
     * @param int $memberId Member ID
     * @return array Loan history by month
     */
    public function getMemberLoanHistory($memberId) {
        $query = "
            SELECT 
                DATE_FORMAT(issue_date, '%Y-%m') as month,
                COUNT(*) as loan_count
            FROM loans
            WHERE 
                member_id = ? AND
                issue_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";
        
        return $this->db->fetchAll($query, [$memberId]);
    }
    
    /**
     * Get popular categories
     * 
     * @param int $limit Limit results
     * @return array Popular categories
     */
    public function getPopularCategories($limit = 5) {
        $query = "
            SELECT b.category, COUNT(l.id) as loan_count
            FROM loans l
            JOIN books b ON l.book_id = b.id
            GROUP BY b.category
            ORDER BY loan_count DESC
            LIMIT {$limit}
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get book availability statistics
     * 
     * @return array Availability statistics
     */
    public function getBookAvailabilityStats() {
        $query = "
            SELECT 
                SUM(total_copies) as total_copies,
                SUM(available_copies) as available_copies,
                SUM(total_copies - available_copies) as borrowed_copies
            FROM books
        ";
        
        return $this->db->fetchOne($query);
    }
}