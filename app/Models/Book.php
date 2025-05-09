<?php
namespace App\Models;

use Core\Model;

/**
 * Book Model
 * Handles database operations for books
 */
class Book extends Model {
    protected $table = 'books';
    protected $fillable = [
        'title', 'author', 'isbn', 'published_year', 'category',
        'description', 'shelf_location', 'cover_image', 'status',
        'total_copies', 'available_copies'
    ];
    
    /**
     * Search books by various fields
     * 
     * @param string $query The search query
     * @param int $page Current page
     * @param string $category Category filter (optional)
     * @param bool $availableOnly Show only available books
     * @return array Paginated search results
     */
    public function search($query, $page = 1, $category = '', $availableOnly = false) {
        $fields = ['title', 'author', 'isbn', 'description'];
        $conditions = [];
        $params = [];
        
        // Build search conditions
        foreach ($fields as $field) {
            $conditions[] = "{$field} LIKE ?";
            $params[] = "%{$query}%";
        }
        
        $where = '(' . implode(' OR ', $conditions) . ')';
        
        // Add category filter
        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        
        // Add availability filter
        if ($availableOnly) {
            $where .= " AND available_copies > 0";
        }
        
        $query = "SELECT * FROM {$this->table} WHERE {$where}";
        return $this->db->paginate($query, $page, ITEMS_PER_PAGE, $params);
    }
    
    /**
     * Get all categories
     * 
     * @return array List of categories
     */
    public function getAllCategories() {
        $query = "SELECT DISTINCT category FROM {$this->table} ORDER BY category";
        $result = $this->db->fetchAll($query);
        
        $categories = [];
        foreach ($result as $row) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }
    
    /**
     * Get popular books (most borrowed)
     * 
     * @param int $limit Number of books to return
     * @return array Popular books
     */
    public function getPopularBooks($limit = 5) {
        $query = "
            SELECT b.*, COUNT(l.id) as borrow_count 
            FROM {$this->table} b
            LEFT JOIN loans l ON b.id = l.book_id
            GROUP BY b.id
            ORDER BY borrow_count DESC
            LIMIT {$limit}
        ";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get recently added books
     * 
     * @param int $limit Number of books to return
     * @return array Recent books
     */
    public function getRecentBooks($limit = 5) {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT {$limit}";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Check if a book is available for loan
     * 
     * @param int $bookId The book ID
     * @return bool True if available, false otherwise
     */
    public function isAvailable($bookId) {
        $book = $this->find($bookId);
        return $book && $book['available_copies'] > 0;
    }
    
    /**
     * Update book availability when loaned or returned
     * 
     * @param int $bookId The book ID
     * @param string $action Either 'loan' or 'return'
     * @return bool Success or failure
     */
    public function updateAvailability($bookId, $action) {
        $book = $this->find($bookId);
        
        if (!$book) {
            return false;
        }
        
        if ($action === 'loan' && $book['available_copies'] > 0) {
            return $this->update($bookId, [
                'available_copies' => $book['available_copies'] - 1
            ]);
        } elseif ($action === 'return' && $book['available_copies'] < $book['total_copies']) {
            return $this->update($bookId, [
                'available_copies' => $book['available_copies'] + 1
            ]);
        }
        
        return false;
    }
    
    /**
     * Get books with pagination and filters
     * 
     * @param int $page Current page
     * @param string $category Category filter
     * @param string $query Search query
     * @param string $availability Availability filter (all, available, unavailable)
     * @return array Paginated result
     */
    public function getAllWithFilters($page = 1, $category = '', $query = '', $availability = 'all') {
        $where = '';
        $params = [];
        
        // Add category filter
        if (!empty($category)) {
            $where = "category = ?";
            $params[] = $category;
        }
        
        // Add availability filter
        if ($availability === 'available') {
            $availWhere = "available_copies > 0";
            $where = $where ? "({$where}) AND {$availWhere}" : $availWhere;
        } elseif ($availability === 'unavailable') {
            $availWhere = "available_copies = 0";
            $where = $where ? "({$where}) AND {$availWhere}" : $availWhere;
        }
        
        // Add search query
        if (!empty($query)) {
            $searchWhere = "title LIKE ? OR author LIKE ? OR isbn LIKE ?";
            $searchParams = ["%{$query}%", "%{$query}%", "%{$query}%"];
            
            $where = $where ? "({$where}) AND ({$searchWhere})" : $searchWhere;
            $params = array_merge($params, $searchParams);
        }
        
        return $this->paginate($page, ITEMS_PER_PAGE, $where, $params, 'title', 'ASC');
    }
}