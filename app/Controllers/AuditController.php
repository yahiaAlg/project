<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\AuditLog;
use App\Models\Member;

/**
 * Audit Controller
 * Handles audit log operations
 */
class AuditController extends Controller {
    protected $auditLogModel;
    protected $memberModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->auditLogModel = new AuditLog();
        $this->memberModel = new Member();
    }
    
    /**
     * Display audit logs with filters
     */
    public function index() {
        $this->authorizeAdmin();
        
        $page = (int)$this->input('page', 1);
        $action = $this->input('action', '');
        $startDate = $this->input('start_date', '');
        $endDate = $this->input('end_date', '');
        $userId = $this->input('user_id', '');
        
        // Convert user ID to integer or null
        $userId = $userId !== '' ? (int)$userId : null;
        
        $result = $this->auditLogModel->getLogsWithFilters(
            $page, 
            $action, 
            $startDate, 
            $endDate, 
            $userId
        );
        
        // Get distinct actions for filter
        $actions = $this->auditLogModel->getDistinctActions();
        
        // Get members for filter
        $members = $this->memberModel->all('name', 'ASC');
        
        $this->render('audit/index', [
            'logs' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['total_pages'],
                'total_records' => $result['total_records']
            ],
            'filters' => [
                'action' => $action,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_id' => $userId
            ],
            'actions' => $actions,
            'members' => $members
        ]);
    }
    
    /**
     * Export audit logs to CSV
     */
    public function export() {
        $this->authorizeAdmin();
        
        $action = $this->input('action', '');
        $startDate = $this->input('start_date', '');
        $endDate = $this->input('end_date', '');
        $userId = $this->input('user_id', '');
        
        // Convert user ID to integer or null
        $userId = $userId !== '' ? (int)$userId : null;
        
        // Generate CSV content
        $csv = $this->auditLogModel->exportToCSV(
            $action, 
            $startDate, 
            $endDate, 
            $userId
        );
        
        // Generate filename
        $filename = 'audit_log_export_' . date('Y-m-d') . '.csv';
        
        // Set headers for file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $csv;
        exit;
    }
    
    /**
     * View details of a specific log entry
     * 
     * @param int $id Log ID
     */
    public function show($id) {
        $this->authorizeAdmin();
        
        $log = $this->auditLogModel->find($id);
        
        if (!$log) {
            $this->flash('error', 'Log entry not found.');
            redirect('/audit');
        }
        
        // Get user details if available
        $user = null;
        if ($log['user_id']) {
            $user = $this->memberModel->find($log['user_id']);
        }
        
        $this->render('audit/show', [
            'log' => $log,
            'user' => $user
        ]);
    }
}