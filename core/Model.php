<?php
namespace Core;

/**
 * Base Model Class
 * Provides core functionality for models
 */
abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find a record by ID
     * 
     * @param int $id The ID to find
     * @return array|false The record or false if not found
     */
    public function find($id) {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    /**
     * Get all records
     * 
     * @param string $orderBy Column to order by
     * @param string $direction Sort direction (ASC or DESC)
     * @return array The records
     */
    public function all($orderBy = null, $direction = 'ASC') {
        $query = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy} {$direction}";
        }
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Find records by a field value
     * 
     * @param string $field The field name
     * @param mixed $value The value to search for
     * @return array The matching records
     */
    public function findBy($field, $value) {
        $query = "SELECT * FROM {$this->table} WHERE {$field} = ?";
        return $this->db->fetchAll($query, [$value]);
    }
    
    /**
     * Create a new record
     * 
     * @param array $data The data to insert
     * @return int|false The new ID or false on failure
     */
    public function create($data) {
        // Filter data to only include fillable fields
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        
        // Add timestamps if enabled
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $filtered['created_at'] = $now;
            $filtered['updated_at'] = $now;
        }
        
        return $this->db->insert($this->table, $filtered);
    }
    
    /**
     * Update a record
     * 
     * @param int $id The ID to update
     * @param array $data The data to update
     * @return bool Success or failure
     */
    public function update($id, $data) {
        // Filter data to only include fillable fields
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        
        // Add updated timestamp if enabled
        if ($this->timestamps) {
            $filtered['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $result = $this->db->update(
            $this->table, 
            $filtered, 
            "{$this->primaryKey} = ?", 
            [$id]
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a record
     * 
     * @param int $id The ID to delete
     * @return bool Success or failure
     */
    public function delete($id) {
        $result = $this->db->delete(
            $this->table, 
            "{$this->primaryKey} = ?", 
            [$id]
        );
        
        return $result !== false;
    }
    
    /**
     * Count all records
     * 
     * @param string $where Optional WHERE clause
     * @param array $params Parameters for the WHERE clause
     * @return int The count
     */
    public function count($where = '', $params = []) {
        return $this->db->count($this->table, $where, $params);
    }
    
    /**
     * Paginate results
     * 
     * @param int $page The current page
     * @param int $perPage Items per page
     * @param string $where Optional WHERE clause
     * @param array $params Parameters for the WHERE clause
     * @param string $orderBy Column to order by
     * @param string $direction Sort direction (ASC or DESC)
     * @return array Paginated results
     */
    public function paginate(
        $page = 1, 
        $perPage = ITEMS_PER_PAGE, 
        $where = '', 
        $params = [], 
        $orderBy = null, 
        $direction = 'ASC'
    ) {
        $query = "SELECT * FROM {$this->table}";
        
        if ($where) {
            $query .= " WHERE {$where}";
        }
        
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy} {$direction}";
        }
        
        return $this->db->paginate($query, $page, $perPage, $params);
    }
    
    /**
     * Search records by multiple fields
     * 
     * @param array $fields Fields to search in
     * @param string $keyword Keyword to search for
     * @param int $page The current page
     * @param int $perPage Items per page
     * @return array Paginated search results
     */
    public function search($fields, $keyword, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $conditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $conditions[] = "{$field} LIKE ?";
            $params[] = "%{$keyword}%";
        }
        
        $where = implode(' OR ', $conditions);
        $query = "SELECT * FROM {$this->table} WHERE {$where}";
        
        return $this->db->paginate($query, $page, $perPage, $params);
    }
}