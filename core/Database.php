<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Database Class
 * Handles database connections and provides utility methods for database operations
 */
class Database {
    protected $connection;
    protected static $instance = null;
    
    /**
     * Private constructor to prevent direct creation
     */
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please check the configuration.');
        }
    }
    
    /**
     * Get the database instance (Singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prepare and execute a query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the prepared statement
     * @return \PDOStatement|false
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database Query Error: ' . $e->getMessage() . ' | Query: ' . $query);
            return false;
        }
    }
    
    /**
     * Fetch a single record
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the prepared statement
     * @return array|false The record or false if not found
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetch() : false;
    }
    
    /**
     * Fetch all records
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters for the prepared statement
     * @return array The records
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    /**
     * Insert a record
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int|false The last insert ID or false on failure
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->query($query, array_values($data));
        return $stmt ? $this->connection->lastInsertId() : false;
    }
    
    /**
     * Update a record
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value to update
     * @param string $where WHERE clause
     * @param array $params Parameters for the WHERE clause
     * @return int|false Number of affected rows or false on failure
     */
    public function update($table, $data, $where, $params = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }
        $set = implode(', ', $set);
        
        $query = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $stmt = $this->query($query, array_merge(array_values($data), $params));
        return $stmt ? $stmt->rowCount() : false;
    }
    
    /**
     * Delete a record
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for the WHERE clause
     * @return int|false Number of affected rows or false on failure
     */
    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->rowCount() : false;
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Count records in a table
     * 
     * @param string $table Table name
     * @param string $where Optional WHERE clause
     * @param array $params Parameters for the WHERE clause
     * @return int The count
     */
    public function count($table, $where = '', $params = []) {
        $query = "SELECT COUNT(*) AS count FROM {$table}";
        if ($where) {
            $query .= " WHERE {$where}";
        }
        
        $result = $this->fetchOne($query, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Paginate query results
     * 
     * @param string $query Base SQL query
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @param array $params Query parameters
     * @return array [data, total_pages, total_records]
     */
    public function paginate($query, $page = 1, $perPage = ITEMS_PER_PAGE, $params = []) {
        // Count total records
        $countQuery = "SELECT COUNT(*) as count FROM ({$query}) as counted";
        $countResult = $this->fetchOne($countQuery, $params);
        $totalRecords = $countResult ? (int)$countResult['count'] : 0;
        $totalPages = ceil($totalRecords / $perPage);
        
        // Adjust page if out of bounds
        $page = max(1, min($page, $totalPages));
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $paginatedQuery = "{$query} LIMIT {$offset}, {$perPage}";
        $data = $this->fetchAll($paginatedQuery, $params);
        
        return [
            'data' => $data,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'current_page' => $page
        ];
    }
}