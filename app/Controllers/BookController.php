<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Book;

/**
 * Book Controller
 * Handles book-related operations
 */
class BookController extends Controller {
    protected $bookModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->bookModel = new Book();
    }
    
    /**
     * Display a listing of books
     */
    public function index() {
        $page = (int)$this->input('page', 1);
        $category = $this->input('category', '');
        $query = $this->input('query', '');
        $availability = $this->input('availability', 'all');
        
        $result = $this->bookModel->getAllWithFilters(
            $page, 
            $category, 
            $query, 
            $availability
        );
        
        $categories = $this->bookModel->getAllCategories();
        
        $this->render('books/index', [
            'books' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['total_pages'],
                'total_records' => $result['total_records']
            ],
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'query' => $query,
                'availability' => $availability
            ],
            /*added styles*/
            'styles' => [
                'book.css'
            ]
        ]);
    }
    
    /**
     * Search for books
     */
    public function search() {
        $query = $this->input('query', '');
        $page = (int)$this->input('page', 1);
        $category = $this->input('category', '');
        $availableOnly = (bool)$this->input('available_only', false);
        
        if (empty($query)) {
            redirect('/books');
        }
        
        $result = $this->bookModel->search($query, $page, $category, $availableOnly);
        $categories = $this->bookModel->getAllCategories();
        
        $this->render('books/search', [
            'books' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['total_pages'],
                'total_records' => $result['total_records']
            ],
            'categories' => $categories,
            'query' => $query,
            'category' => $category,
            'available_only' => $availableOnly
        ]);
    }
    
    /**
     * Display a specific book
     * 
     * @param int $id Book ID
     */
    public function show($id) {
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            $this->flash('error', 'Book not found.');
            redirect('/books');
        }
        
        // Get related books in the same category
        $related = $this->bookModel->findBy('category', $book['category']);
        
        // Remove current book from related books
        $related = array_filter($related, function($item) use ($id) {
            return $item['id'] != $id;
        });
        
        // Limit to 4 related books
        $related = array_slice($related, 0, 4);
        
        $this->render('books/show', [
            'book' => $book,
            'related' => $related
        ]);
    }
    
    /**
     * Show form to create a new book
     */
    public function create() {
        $this->authorizeAdmin();
        
        $categories = $this->bookModel->getAllCategories();
        
        $this->render('books/create', [
            'categories' => $categories
        ]);
    }
    
    /**
     * Store a new book
     */
    public function store() {
        $this->authorizeAdmin();
        
        // Validate form data
        $validation = $this->validate($_POST, [
            'title' => 'required|min:3',
            'author' => 'required',
            'isbn' => 'required',
            'published_year' => 'required|numeric',
            'category' => 'required',
            'total_copies' => 'required|numeric'
        ]);
        
        if (!$validation['is_valid']) {
            $this->render('books/create', [
                'errors' => $validation['errors'],
                'book' => $_POST,
                'categories' => $this->bookModel->getAllCategories()
            ]);
            return;
        }
        
        // Handle cover image upload
        $coverImage = '/assets/images/placeholder-cover.jpg'; // Default image
        
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadedFile = $_FILES['cover_image'];
            
            // Validate file type
            $fileType = $uploadedFile['type'];
            if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
                $this->flash('error', 'Invalid image format. Please upload JPG, PNG or GIF.');
                $this->render('books/create', [
                    'book' => $_POST,
                    'categories' => $this->bookModel->getAllCategories()
                ]);
                return;
            }
            
            // Validate file size
            if ($uploadedFile['size'] > MAX_IMAGE_SIZE) {
                $this->flash('error', 'Image size exceeds the maximum allowed size (2MB).');
                $this->render('books/create', [
                    'book' => $_POST,
                    'categories' => $this->bookModel->getAllCategories()
                ]);
                return;
            }
            
            // Generate unique filename
            $fileName = 'book_' . time() . '_' . uniqid() . '.' . pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $uploadPath = APP_ROOT . '/public/assets/images/covers/' . $fileName;
            
            // Create directory if it doesn't exist
            $dir = dirname($uploadPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Move the uploaded file
            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                $coverImage = '/assets/images/covers/' . $fileName;
            }
        }
        
        // Prepare book data
        $bookData = [
            'title' => $this->input('title'),
            'author' => $this->input('author'),
            'isbn' => $this->input('isbn'),
            'published_year' => $this->input('published_year'),
            'category' => $this->input('category'),
            'description' => $this->input('description'),
            'shelf_location' => $this->input('shelf_location'),
            'cover_image' => $coverImage,
            'total_copies' => $this->input('total_copies'),
            'available_copies' => $this->input('total_copies'), // Initially all copies are available
            'status' => 'available'
        ];
        
        // Create the book
        $bookId = $this->bookModel->create($bookData);
        
        if (!$bookId) {
            $this->flash('error', 'Failed to add book. Please try again.');
            $this->render('books/create', [
                'book' => $_POST,
                'categories' => $this->bookModel->getAllCategories()
            ]);
            return;
        }
        
        // Log the action
        log_audit('Book Added', "Added book: {$bookData['title']} (ID: {$bookId})");
        
        $this->flash('success', 'Book added successfully.');
        redirect('/books/' . $bookId);
    }
    
    /**
     * Show form to edit a book
     * 
     * @param int $id Book ID
     */
    public function edit($id) {
        $this->authorizeAdmin();
        
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            $this->flash('error', 'Book not found.');
            redirect('/books');
        }
        
        $categories = $this->bookModel->getAllCategories();
        
        $this->render('books/edit', [
            'book' => $book,
            'categories' => $categories
        ]);
    }
    
    /**
     * Update a book
     * 
     * @param int $id Book ID
     */
    public function update($id) {
        $this->authorizeAdmin();
        
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            $this->flash('error', 'Book not found.');
            redirect('/books');
        }
        
        // Validate form data
        $validation = $this->validate($_POST, [
            'title' => 'required|min:3',
            'author' => 'required',
            'isbn' => 'required',
            'published_year' => 'required|numeric',
            'category' => 'required',
            'total_copies' => 'required|numeric'
        ]);
        
        if (!$validation['is_valid']) {
            $this->render('books/edit', [
                'errors' => $validation['errors'],
                'book' => array_merge($book, $_POST),
                'categories' => $this->bookModel->getAllCategories()
            ]);
            return;
        }
        
        // Handle cover image upload
        $coverImage = $book['cover_image']; // Keep existing image by default
        
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadedFile = $_FILES['cover_image'];
            
            // Validate file type
            $fileType = $uploadedFile['type'];
            if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
                $this->flash('error', 'Invalid image format. Please upload JPG, PNG or GIF.');
                $this->render('books/edit', [
                    'book' => array_merge($book, $_POST),
                    'categories' => $this->bookModel->getAllCategories()
                ]);
                return;
            }
            
            // Validate file size
            if ($uploadedFile['size'] > MAX_IMAGE_SIZE) {
                $this->flash('error', 'Image size exceeds the maximum allowed size (2MB).');
                $this->render('books/edit', [
                    'book' => array_merge($book, $_POST),
                    'categories' => $this->bookModel->getAllCategories()
                ]);
                return;
            }
            
            // Generate unique filename
            $fileName = 'book_' . time() . '_' . uniqid() . '.' . pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $uploadPath = APP_ROOT . '/public/assets/images/covers/' . $fileName;
            
            // Create directory if it doesn't exist
            $dir = dirname($uploadPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Move the uploaded file
            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                $coverImage = '/assets/images/covers/' . $fileName;
                
                // Delete old image if it's not the default
                if ($book['cover_image'] !== '/assets/images/placeholder-cover.jpg') {
                    $oldImagePath = APP_ROOT . '/public' . $book['cover_image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }
        }
        
        // Calculate available copies adjustment
        $totalCopies = (int)$this->input('total_copies');
        $availableCopiesAdjustment = $totalCopies - $book['total_copies'];
        $newAvailableCopies = max(0, $book['available_copies'] + $availableCopiesAdjustment);
        
        // Prepare book data
        $bookData = [
            'title' => $this->input('title'),
            'author' => $this->input('author'),
            'isbn' => $this->input('isbn'),
            'published_year' => $this->input('published_year'),
            'category' => $this->input('category'),
            'description' => $this->input('description'),
            'shelf_location' => $this->input('shelf_location'),
            'cover_image' => $coverImage,
            'total_copies' => $totalCopies,
            'available_copies' => $newAvailableCopies,
            'status' => $newAvailableCopies > 0 ? 'available' : 'unavailable'
        ];
        
        // Update the book
        $success = $this->bookModel->update($id, $bookData);
        
        if (!$success) {
            $this->flash('error', 'Failed to update book. Please try again.');
            $this->render('books/edit', [
                'book' => array_merge($book, $_POST),
                'categories' => $this->bookModel->getAllCategories()
            ]);
            return;
        }
        
        // Log the action
        log_audit('Book Updated', "Updated book: {$bookData['title']} (ID: {$id})");
        
        $this->flash('success', 'Book updated successfully.');
        redirect('/books/' . $id);
    }
    
    /**
     * Show confirmation page for deleting a book
     * 
     * @param int $id Book ID
     */
    public function delete($id) {
        $this->authorizeAdmin();
        
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            $this->flash('error', 'Book not found.');
            redirect('/books');
        }
        
        // Check if book has active loans
        $query = "SELECT COUNT(*) as count FROM loans WHERE book_id = ? AND returned_at IS NULL";
        $result = $this->bookModel->db->fetchOne($query, [$id]);
        $activeLoans = $result ? (int)$result['count'] : 0;
        
        $this->render('books/delete', [
            'book' => $book,
            'activeLoans' => $activeLoans
        ]);
    }
    
    /**
     * Delete a book
     * 
     * @param int $id Book ID
     */
    public function destroy($id) {
        $this->authorizeAdmin();
        
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            $this->flash('error', 'Book not found.');
            redirect('/books');
        }
        
        // Check if book has active loans
        $query = "SELECT COUNT(*) as count FROM loans WHERE book_id = ? AND returned_at IS NULL";
        $result = $this->bookModel->db->fetchOne($query, [$id]);
        $activeLoans = $result ? (int)$result['count'] : 0;
        
        if ($activeLoans > 0) {
            $this->flash('error', 'Cannot delete a book with active loans.');
            redirect('/books/' . $id);
            return;
        }
        
        // Delete the book
        $success = $this->bookModel->delete($id);
        
        if (!$success) {
            $this->flash('error', 'Failed to delete book. Please try again.');
            redirect('/books/' . $id);
            return;
        }
        
        // Delete cover image if it's not the default
        if ($book['cover_image'] !== '/assets/images/placeholder-cover.jpg') {
            $imagePath = APP_ROOT . '/public' . $book['cover_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Log the action
        log_audit('Book Deleted', "Deleted book: {$book['title']} (ID: {$id})");
        
        $this->flash('success', 'Book deleted successfully.');
        redirect('/books');
    }
}