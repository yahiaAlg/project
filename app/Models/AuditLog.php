<?php
namespace App\Models;

use Core\Model;

/**
 * AuditLog Model
 * Handles audit log operations
 */
class AuditLog extends Model {
    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id', 'action', 'details', 'ip_address', 'user_agent'
    ];
    
    /**
     * Log an audit event to the database
     * 
     * @param string $action The action performed
     * @param string $details Details about the action
     * @param int|null $userId The user ID who performed the action
     * @return int|false The audit log ID or false on failure
     */
    public function logAction($action, $details, $userId = null) {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Also log to file
        $this->logToFile($data);
        
        return $this->create($data);
    }
    
    /**
     * Log to file
     * 
     * @param array $data Log data
     * @return void
     */
    protected function logToFile($data) {
        $logEntry = sprintf(
            "[%s] User: %s | Action: %s | Details: %s | IP: %s\n",
            $data['created_at'],
            $data['user_id'] ?? 'Guest',
            $data['action'],
            $data['details'],
            $data['ip_address'] ?? 'Unknown'
        );
        
        file_put_contents(AUDIT_LOG, $logEntry, FILE_APPEND);
    }
    
    /**
     * Get audit logs with pagination and filters
     * 
     * @param int $page Current page
     * @param string $action Action filter
     * @param string $startDate Start date filter
     * @param string $endDate End date filter
     * @param int $userId User ID filter
     * @return array Paginated result
     */
    public function getLogsWithFilters(
        $page = 1, 
        $action = '', 
        $startDate = '', 
        $endDate = '', 
        $userId = null
    ) {
        $where = '';
        $params = [];
        
        // Add action filter
        if (!empty($action)) {
            $where = "action = ?";
            $params[] = $action;
        }
        
        // Add date range filter
        if (!empty($startDate)) {
            $dateCond = "DATE(created_at) >= ?";
            $where = $where ? "({$where}) AND {$dateCond}" : $dateCond;
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $dateCond = "DATE(created_at) <= ?";
            $where = $where ? "({$where}) AND {$dateCond}" : $dateCond;
            $params[] = $endDate;
        }
        
        // Add user filter
        if ($userId !== null) {
            $userCond = "user_id = ?";
            $where = $where ? "({$where}) AND {$userCond}" : $userCond;
            $params[] = $userId;
        }
        
        // Build query with user info
        $query = "
            SELECT al.*, m.name as user_name, m.email as user_email
            FROM {$this->table} al
            LEFT JOIN members m ON al.user_id = m.id
        ";
        
        if ($where) {
            $query .= " WHERE {$where}";
        }
        
        $query .= " ORDER BY al.created_at DESC";
        
        return $this->db->paginate($query, $page, ITEMS_PER_PAGE, $params);
    }
    
    /**
     * Get distinct actions for filtering
     * 
     * @return array List of distinct actions
     */
    public function getDistinctActions() {
        $query = "SELECT DISTINCT action FROM {$this->table} ORDER BY action";
        $result = $this->db->fetchAll($query);
        
        $actions = [];
        foreach ($result as $row) {
            $actions[] = $row['action'];
        }
        
        return $actions;
    }
    
    /**
     * Export logs to CSV
     * 
     * @param string $action Action filter
     * @param string $startDate Start date filter
     * @param string $endDate End date filter
     * @param int $userId User ID filter
     * @return string CSV content
     */
    public function exportToCSV($action = '', $startDate = '', $endDate = '', $userId = null) {
        $where = '';
        $params = [];
        
        // Add action filter
        if (!empty($action)) {
            $where = "action = ?";
            $params[] = $action;
        }
        
        // Add date range filter
        if (!empty($startDate)) {
            $dateCond = "DATE(created_at) >= ?";
            $where = $where ? "({$where}) AND {$dateCond}" : $dateCond;
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $dateCond = "DATE(created_at) <= ?";
            $where = $where ? "({$where}) AND {$dateCond}" : $dateCond;
            $params[] = $endDate;
        }
        
        // Add user filter
        if ($userId !== null) {
            $userCond = "user_id = ?";
            $where = $where ? "({$where}) AND {$userCond}" : $userCond;
            $params[] = $userId;
        }
        
        // Build query with user info
        $query = "
            SELECT 
                al.id, 
                al.created_at, 
                COALESCE(m.name, 'Guest') as user_name, 
                COALESCE(m.email, '') as user_email,
                al.action, 
                al.details, 
                al.ip_address
            FROM {$this->table} al
            LEFT JOIN members m ON al.user_id = m.id
        ";
        
        if ($where) {
            $query .= " WHERE {$where}";
        }
        
        $query .= " ORDER BY al.created_at DESC";
        
        $logs = $this->db->fetchAll($query, $params);
        
        // Create CSV content
        $csv = "ID,Timestamp,User,Email,Action,Details,IP Address\n";
        
        foreach ($logs as $log) {
            // Escape fields for CSV
            $details = str_replace('"', '""', $log['details']);
            
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $log['id'],
                $log['created_at'],
                $log['user_name'],
                $log['user_email'],
                $log['action'],
                $details,
                $log['ip_address']
            );
        }
        
        return $csv;
    }
}