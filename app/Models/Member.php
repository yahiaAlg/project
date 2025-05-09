<?php
namespace App\Models;

use Core\Model;

/**
 * Member Model
 * Handles database operations for members
 */
class Member extends Model {
    protected $table = 'members';
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address', 
        'role', 'status', 'notes'
    ];
    
    /**
     * Find a member by email
     * 
     * @param string $email The email to search for
     * @return array|false The member record or false if not found
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        return $this->db->fetchOne($query, [$email]);
    }
    
    /**
     * Check if an email address already exists
     * 
     * @param string $email The email to check
     * @return bool True if exists, false otherwise
     */
    public function emailExists($email) {
        $result = $this->findByEmail($email);
        return $result !== false;
    }
    
    /**
     * Get number of active loans for a member
     * 
     * @param int $memberId The member ID
     * @return int Number of active loans
     */
    public function getActiveLoansCount($memberId) {
        $query = "SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND returned_at IS NULL";
        $result = $this->db->fetchOne($query, [$memberId]);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get members with overdue books
     * 
     * @return array Members with overdue books
     */
    public function getMembersWithOverdueBooks() {
        $query = "
            SELECT m.*, COUNT(l.id) as overdue_count 
            FROM {$this->table} m
            JOIN loans l ON m.id = l.member_id
            WHERE l.returned_at IS NULL AND l.due_date < CURDATE()
            GROUP BY m.id
            ORDER BY overdue_count DESC
        ";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get active members (with at least one loan)
     * 
     * @param int $limit Limit results (default: 10)
     * @return array Active members
     */
    public function getActiveMembers($limit = 10) {
        $query = "
            SELECT m.*, COUNT(l.id) as loan_count 
            FROM {$this->table} m
            JOIN loans l ON m.id = l.member_id
            GROUP BY m.id
            ORDER BY loan_count DESC
            LIMIT {$limit}
        ";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Check if a member can borrow more books
     * 
     * @param int $memberId The member ID
     * @return bool True if allowed, false if limit reached
     */
    public function canBorrow($memberId) {
        $activeLoans = $this->getActiveLoansCount($memberId);
        return $activeLoans < MAX_LOANS_PER_MEMBER;
    }
    
    /**
     * Get member dashboard stats
     * 
     * @param int $memberId The member ID
     * @return array Statistics
     */
    public function getDashboardStats($memberId) {
        $stats = [];
        
        // Active loans
        $query = "SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND returned_at IS NULL";
        $result = $this->db->fetchOne($query, [$memberId]);
        $stats['active_loans'] = $result ? (int)$result['count'] : 0;
        
        // Overdue loans
        $query = "SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND returned_at IS NULL AND due_date < CURDATE()";
        $result = $this->db->fetchOne($query, [$memberId]);
        $stats['overdue_loans'] = $result ? (int)$result['count'] : 0;
        
        // Total loans history
        $query = "SELECT COUNT(*) as count FROM loans WHERE member_id = ?";
        $result = $this->db->fetchOne($query, [$memberId]);
        $stats['total_loans'] = $result ? (int)$result['count'] : 0;
        
        // Recently borrowed books
        $query = "
            SELECT b.title, l.issue_date, l.due_date, l.returned_at
            FROM loans l
            JOIN books b ON l.book_id = b.id
            WHERE l.member_id = ?
            ORDER BY l.issue_date DESC
            LIMIT 5
        ";
        $stats['recent_books'] = $this->db->fetchAll($query, [$memberId]);
        
        return $stats;
    }
    
    /**
     * Get all members with pagination and filters
     * 
     * @param int $page Current page
     * @param string $status Status filter (all, active, inactive)
     * @param string $query Search query
     * @return array Paginated result
     */
    public function getAllWithFilters($page = 1, $status = 'all', $query = '') {
        $where = '';
        $params = [];
        
        // Add status filter
        if ($status !== 'all') {
            $where = "status = ?";
            $params[] = $status;
        }
        
        // Add search query
        if (!empty($query)) {
            $searchWhere = "name LIKE ? OR email LIKE ? OR phone LIKE ?";
            $searchParams = ["%{$query}%", "%{$query}%", "%{$query}%"];
            
            $where = $where ? "({$where}) AND ({$searchWhere})" : $searchWhere;
            $params = array_merge($params, $searchParams);
        }
        
        return $this->paginate($page, ITEMS_PER_PAGE, $where, $params, 'created_at', 'DESC');
    }
}