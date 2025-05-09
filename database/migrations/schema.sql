-- Library Management System Database Schema

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS library_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_management;

-- Members table (users of the system)
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('member', 'librarian') NOT NULL DEFAULT 'member',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) NOT NULL,
    published_year INT,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    shelf_location VARCHAR(50),
    cover_image VARCHAR(255) DEFAULT '/assets/images/placeholder-cover.jpg',
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (category),
    INDEX (author),
    INDEX (isbn)
) ENGINE=InnoDB;

-- Loans table
CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    returned_at DATETIME NULL,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE RESTRICT,
    INDEX (book_id),
    INDEX (member_id),
    INDEX (issue_date),
    INDEX (due_date),
    INDEX (returned_at)
) ENGINE=InnoDB;

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (action),
    INDEX (created_at),
    FOREIGN KEY (user_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Create default admin user (password: admin123)
INSERT INTO members (name, email, password, role, status)
VALUES ('Admin', 'admin@library.com', '$2y$10$9XmzPQRJQJyJMH26YEIaFu2l90BPS0gVJ9TS6ljYHUgS4QgK2W0W.', 'librarian', 'active');

-- Create default categories
INSERT INTO books (title, author, isbn, published_year, category, total_copies, available_copies, status)
VALUES 
    ('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 1960, 'Fiction', 3, 3, 'available'),
    ('1984', 'George Orwell', '9780451524935', 1949, 'Science Fiction', 2, 2, 'available'),
    ('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 1925, 'Classic', 2, 2, 'available'),
    ('Pride and Prejudice', 'Jane Austen', '9780141439518', 1813, 'Romance', 1, 1, 'available'),
    ('The Catcher in the Rye', 'J.D. Salinger', '9780316769488', 1951, 'Fiction', 1, 1, 'available'),
    ('The Hobbit', 'J.R.R. Tolkien', '9780547928227', 1937, 'Fantasy', 2, 2, 'available'),
    ('Brave New World', 'Aldous Huxley', '9780060850524', 1932, 'Science Fiction', 1, 1, 'available'),
    ('The Lord of the Rings', 'J.R.R. Tolkien', '9780618640157', 1954, 'Fantasy', 3, 3, 'available'),
    ('Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', '9780590353427', 1997, 'Fantasy', 2, 2, 'available'),
    ('The Da Vinci Code', 'Dan Brown', '9780307474278', 2003, 'Mystery', 2, 2, 'available'),
    ('The Alchemist', 'Paulo Coelho', '9780062315007', 1988, 'Fiction', 1, 1, 'available'),
    ('The Hunger Games', 'Suzanne Collins', '9780439023481', 2008, 'Science Fiction', 3, 3, 'available');