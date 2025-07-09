-- Library Management System - Enhanced MySQL Database Schema
-- Version 2.0 - Improved Security, Performance, and Data Integrity
-- Compatible with PHP 7.4+ and MySQL 8.0+

-- Create database with proper character set and collation
CREATE DATABASE IF NOT EXISTS library_system 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE library_system;

-- Set SQL mode for strict data validation
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

-- ============================================================================
-- ROLES TABLE - Enhanced role management
-- ============================================================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- USERS TABLE - Enhanced with security features
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL DEFAULT 2, -- Default to student role
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    date_of_birth DATE NULL,
    student_id VARCHAR(50) UNIQUE NULL, -- For students
    department VARCHAR(100) NULL,
    
    -- Security fields
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255) NULL,
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires TIMESTAMP NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    
    -- Status and metadata
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role_id (role_id),
    INDEX idx_status (status),
    INDEX idx_student_id (student_id),
    INDEX idx_last_login (last_login),
    INDEX idx_email_verification_token (email_verification_token),
    INDEX idx_password_reset_token (password_reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CATEGORIES TABLE - Enhanced categorization
-- ============================================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    parent_id INT NULL, -- For hierarchical categories
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    
    INDEX idx_code (code),
    INDEX idx_parent_id (parent_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PUBLISHERS TABLE - Separate publisher management
-- ============================================================================
CREATE TABLE publishers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    address TEXT NULL,
    website VARCHAR(255) NULL,
    email VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- AUTHORS TABLE - Separate author management
-- ============================================================================
CREATE TABLE authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(200) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
    biography TEXT NULL,
    birth_date DATE NULL,
    death_date DATE NULL,
    nationality VARCHAR(100) NULL,
    website VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_full_name (full_name),
    INDEX idx_last_name (last_name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BOOKS TABLE - Enhanced with better structure
-- ============================================================================
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(500) NOT NULL, -- Increased for longer titles
    subtitle VARCHAR(500) NULL,
    category_id INT NOT NULL,
    publisher_id INT NULL,
    isbn10 VARCHAR(10) UNIQUE NULL,
    isbn13 VARCHAR(13) UNIQUE NULL,
    edition VARCHAR(50) NULL,
    publication_year YEAR NOT NULL,
    publication_month TINYINT NOT NULL CHECK (publication_month BETWEEN 1 AND 12),
    publication_day TINYINT NULL CHECK (publication_day BETWEEN 1 AND 31),
    pages INT NULL CHECK (pages > 0),
    language VARCHAR(10) DEFAULT 'en',
    
    -- Physical properties
    total_copies INT NOT NULL DEFAULT 1 CHECK (total_copies > 0),
    available_copies INT NOT NULL DEFAULT 1 CHECK (available_copies >= 0),
    location VARCHAR(100) NULL, -- Shelf location
    
    -- Content
    description TEXT NULL,
    summary TEXT NULL,
    table_of_contents TEXT NULL,
    cover_image_url VARCHAR(500) NULL,
    
    -- Pricing and acquisition
    purchase_price DECIMAL(10,2) NULL CHECK (purchase_price >= 0),
    purchase_date DATE NULL,
    supplier VARCHAR(200) NULL,
    
    -- Status and metadata
    status ENUM('active', 'inactive', 'damaged', 'lost', 'archived') DEFAULT 'active',
    condition_notes TEXT NULL,
    last_inventory_check DATE NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_book_id (book_id),
    INDEX idx_title (title(100)), -- Partial index for performance
    INDEX idx_category_id (category_id),
    INDEX idx_publisher_id (publisher_id),
    INDEX idx_isbn10 (isbn10),
    INDEX idx_isbn13 (isbn13),
    INDEX idx_status (status),
    INDEX idx_publication_year (publication_year),
    INDEX idx_available_copies (available_copies),
    INDEX idx_language (language),
    
    FULLTEXT idx_search (title, subtitle, description, summary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BOOK_AUTHORS TABLE - Many-to-many relationship
-- ============================================================================
CREATE TABLE book_authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    author_id INT NOT NULL,
    author_order TINYINT DEFAULT 1, -- For multiple authors
    role ENUM('author', 'co-author', 'editor', 'translator', 'illustrator') DEFAULT 'author',
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_book_author_role (book_id, author_id, role),
    INDEX idx_book_id (book_id),
    INDEX idx_author_id (author_id),
    INDEX idx_author_order (author_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BORROWINGS TABLE - Enhanced borrowing management
-- ============================================================================
CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    
    -- Borrowing details
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date TIMESTAMP NOT NULL,
    return_date TIMESTAMP NULL,
    renewed_count TINYINT DEFAULT 0 CHECK (renewed_count >= 0),
    max_renewals TINYINT DEFAULT 2 CHECK (max_renewals >= 0),
    
    -- Status and conditions
    status ENUM('active', 'returned', 'overdue', 'lost', 'damaged') DEFAULT 'active',
    condition_on_borrow ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    condition_on_return ENUM('excellent', 'good', 'fair', 'poor') NULL,
    damage_notes TEXT NULL,
    
    -- Financial
    fine_amount DECIMAL(10,2) DEFAULT 0.00 CHECK (fine_amount >= 0),
    fine_reason VARCHAR(200) NULL,
    fine_paid BOOLEAN DEFAULT FALSE,
    fine_paid_date TIMESTAMP NULL,
    fine_waived BOOLEAN DEFAULT FALSE,
    fine_waived_reason TEXT NULL,
    
    -- Metadata
    issued_by INT NULL, -- Staff member who issued the book
    returned_to INT NULL, -- Staff member who received the return
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (returned_to) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_book_id (book_id),
    INDEX idx_status (status),
    INDEX idx_borrow_date (borrow_date),
    INDEX idx_due_date (due_date),
    INDEX idx_return_date (return_date),
    INDEX idx_fine_amount (fine_amount),
    INDEX idx_issued_by (issued_by),
    INDEX idx_returned_to (returned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- RESERVATIONS TABLE - Book reservation system
-- ============================================================================
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP NOT NULL,
    status ENUM('active', 'fulfilled', 'expired', 'cancelled') DEFAULT 'active',
    priority INT DEFAULT 1,
    notified BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_book_id (book_id),
    INDEX idx_status (status),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- USER_ACTIVITY_LOG TABLE - Audit trail
-- ============================================================================
CREATE TABLE user_activity_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL, -- books, users, borrowings, etc.
    entity_id INT NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity_type (entity_type),
    INDEX idx_entity_id (entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SYSTEM_SETTINGS TABLE - Configuration management
-- ============================================================================
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE, -- Can be accessed by non-admin users
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICATIONS TABLE - User notifications
-- ============================================================================
CREATE TABLE notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- overdue, reservation_ready, etc.
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL, -- Additional data for the notification
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT DEFAULT DATA
-- ============================================================================

-- Insert default roles
INSERT INTO roles (name, display_name, description, permissions) VALUES
('admin', 'Administrator', 'Full system access', '["*"]'),
('librarian', 'Librarian', 'Library management access', '["books.*", "borrowings.*", "users.view", "users.edit", "reports.*"]'),
('student', 'Student', 'Basic library access', '["books.view", "borrowings.own", "profile.edit"]');

-- Insert default categories
INSERT INTO categories (code, name, description, sort_order) VALUES
('FIC', 'Fiction', 'Fiction books and novels', 1),
('NON', 'Non-Fiction', 'Non-fiction books', 2),
('SCI', 'Science & Technology', 'Science and technology books', 3),
('HIS', 'History', 'History books', 4),
('BIO', 'Biography', 'Biography and autobiography', 5),
('REF', 'Reference', 'Reference books and dictionaries', 6),
('EDU', 'Education', 'Educational and academic books', 7),
('ART', 'Arts & Literature', 'Arts and literature books', 8),
('MED', 'Medicine & Health', 'Medical and health books', 9),
('LAW', 'Law & Politics', 'Law and political science books', 10),
('BUS', 'Business & Economics', 'Business and economics books', 11),
('PHI', 'Philosophy & Religion', 'Philosophy and religious books', 12);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('library_name', 'Library Management System', 'string', 'Name of the library', TRUE),
('max_borrow_days', '14', 'integer', 'Default borrowing period in days', TRUE),
('max_renewals', '2', 'integer', 'Maximum number of renewals allowed', TRUE),
('fine_per_day', '1.00', 'string', 'Fine amount per day for overdue books', TRUE),
('max_books_per_user', '5', 'integer', 'Maximum books a user can borrow', TRUE),
('reservation_expiry_days', '3', 'integer', 'Days before reservation expires', TRUE),
('email_notifications', 'true', 'boolean', 'Enable email notifications', FALSE),
('auto_overdue_check', 'true', 'boolean', 'Automatically check for overdue books', FALSE),
('library_email', 'library@example.com', 'string', 'Library contact email', TRUE),
('library_phone', '+1-234-567-8900', 'string', 'Library contact phone', TRUE);

-- Insert default admin user (password: admin123 - CHANGE IMMEDIATELY)
INSERT INTO users (username, email, password_hash, role_id, full_name, status, email_verified) VALUES
('admin', 'admin@library.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj/VjWZpX3PO', 1, 'System Administrator', 'active', TRUE);

-- Insert sample publishers
INSERT INTO publishers (name, website) VALUES
('Penguin Random House', 'https://www.penguinrandomhouse.com'),
('HarperCollins', 'https://www.harpercollins.com'),
('Simon & Schuster', 'https://www.simonandschuster.com'),
('Macmillan Publishers', 'https://www.macmillan.com'),
('Hachette Book Group', 'https://www.hachettebookgroup.com'),
('Oxford University Press', 'https://global.oup.com'),
('Cambridge University Press', 'https://www.cambridge.org'),
('Pearson Education', 'https://www.pearson.com'),
('McGraw-Hill Education', 'https://www.mheducation.com'),
('Wiley', 'https://www.wiley.com');

-- Insert sample authors
INSERT INTO authors (first_name, last_name, nationality) VALUES
('Harper', 'Lee', 'American'),
('George', 'Orwell', 'British'),
('Jane', 'Austen', 'British'),
('F. Scott', 'Fitzgerald', 'American'),
('J.K.', 'Rowling', 'British'),
('J.R.R.', 'Tolkien', 'British'),
('Charles', 'Darwin', 'British'),
('Stephen', 'Hawking', 'British'),
('Sun', 'Tzu', 'Chinese'),
('Yuval Noah', 'Harari', 'Israeli'),
('J.D.', 'Salinger', 'American'),
('Aldous', 'Huxley', 'British'),
('Charlotte', 'Bronte', 'British'),
('Emily', 'Bronte', 'British'),
('Oscar', 'Wilde', 'Irish'),
('Bram', 'Stoker', 'Irish'),
('Mary', 'Shelley', 'British'),
('Mark', 'Twain', 'American'),
('Louisa May', 'Alcott', 'American'),
('Lewis', 'Carroll', 'British');

-- ============================================================================
-- VIEWS FOR REPORTING AND STATISTICS
-- ============================================================================

-- Book statistics view
CREATE VIEW v_book_statistics AS
SELECT 
    COUNT(*) as total_books,
    SUM(total_copies) as total_copies,
    SUM(available_copies) as available_copies,
    SUM(total_copies - available_copies) as borrowed_copies,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_books,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_books,
    SUM(CASE WHEN status = 'damaged' THEN 1 ELSE 0 END) as damaged_books,
    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_books,
    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_books
FROM books;

-- User statistics view
CREATE VIEW v_user_statistics AS
SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN r.name = 'admin' THEN 1 ELSE 0 END) as admin_users,
    SUM(CASE WHEN r.name = 'librarian' THEN 1 ELSE 0 END) as librarian_users,
    SUM(CASE WHEN r.name = 'student' THEN 1 ELSE 0 END) as student_users,
    SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN u.status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
    SUM(CASE WHEN u.status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
    SUM(CASE WHEN u.status = 'pending' THEN 1 ELSE 0 END) as pending_users
FROM users u
JOIN roles r ON u.role_id = r.id;

-- Borrowing statistics view
CREATE VIEW v_borrowing_statistics AS
SELECT 
    COUNT(*) as total_borrowings,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_borrowings,
    SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_borrowings,
    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_borrowings,
    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_borrowings,
    SUM(CASE WHEN status = 'damaged' THEN 1 ELSE 0 END) as damaged_borrowings,
    SUM(fine_amount) as total_fines,
    SUM(CASE WHEN fine_paid = TRUE THEN fine_amount ELSE 0 END) as paid_fines,
    SUM(CASE WHEN fine_waived = TRUE THEN fine_amount ELSE 0 END) as waived_fines,
    AVG(DATEDIFF(COALESCE(return_date, NOW()), borrow_date)) as avg_borrow_days
FROM borrowings;

-- Popular books view
CREATE VIEW v_popular_books AS
SELECT 
    b.id,
    b.book_id,
    b.title,
    GROUP_CONCAT(DISTINCT a.full_name ORDER BY ba.author_order SEPARATOR ', ') as authors,
    c.name as category,
    COUNT(br.id) as borrow_count,
    AVG(DATEDIFF(COALESCE(br.return_date, NOW()), br.borrow_date)) as avg_borrow_days
FROM books b
LEFT JOIN book_authors ba ON b.id = ba.book_id
LEFT JOIN authors a ON ba.author_id = a.id
LEFT JOIN categories c ON b.category_id = c.id
LEFT JOIN borrowings br ON b.id = br.book_id
WHERE b.status = 'active'
GROUP BY b.id, b.book_id, b.title, c.name
ORDER BY borrow_count DESC, b.title;

-- Overdue books view
CREATE VIEW v_overdue_books AS
SELECT 
    br.id as borrowing_id,
    br.user_id,
    u.username,
    u.full_name,
    u.email,
    br.book_id,
    b.book_id as book_code,
    b.title,
    GROUP_CONCAT(DISTINCT a.full_name ORDER BY ba.author_order SEPARATOR ', ') as authors,
    br.borrow_date,
    br.due_date,
    DATEDIFF(NOW(), br.due_date) as days_overdue,
    br.fine_amount,
    br.fine_paid
FROM borrowings br
JOIN users u ON br.user_id = u.id
JOIN books b ON br.book_id = b.id
LEFT JOIN book_authors ba ON b.id = ba.book_id
LEFT JOIN authors a ON ba.author_id = a.id
WHERE br.status IN ('active', 'overdue') 
AND br.due_date < NOW()
GROUP BY br.id, br.user_id, u.username, u.full_name, u.email, br.book_id, b.book_id, b.title, br.borrow_date, br.due_date, br.fine_amount, br.fine_paid
ORDER BY days_overdue DESC, br.due_date;

-- ============================================================================
-- STORED PROCEDURES
-- ============================================================================

DELIMITER //

-- Procedure to update overdue books and calculate fines
CREATE PROCEDURE UpdateOverdueBooks()
BEGIN
    DECLARE fine_per_day DECIMAL(10,2) DEFAULT 1.00;
    
    -- Get fine amount from settings
    SELECT CAST(setting_value AS DECIMAL(10,2)) INTO fine_per_day
    FROM system_settings 
    WHERE setting_key = 'fine_per_day' 
    LIMIT 1;
    
    -- Update overdue borrowings
    UPDATE borrowings 
    SET 
        status = 'overdue',
        fine_amount = GREATEST(DATEDIFF(NOW(), due_date) * fine_per_day, 0),
        fine_reason = CONCAT('Overdue fine: ', DATEDIFF(NOW(), due_date), ' days @ $', fine_per_day, '/day')
    WHERE status = 'active' 
    AND due_date < NOW()
    AND return_date IS NULL;
    
    -- Log the update
    INSERT INTO user_activity_log (action, entity_type, details)
    VALUES ('system_overdue_update', 'borrowings', JSON_OBJECT('updated_at', NOW()));
    
END//

-- Procedure to return a book
CREATE PROCEDURE ReturnBook(
    IN p_borrowing_id INT,
    IN p_returned_by INT,
    IN p_condition ENUM('excellent', 'good', 'fair', 'poor'),
    IN p_damage_notes TEXT
)
BEGIN
    DECLARE v_book_id INT;
    DECLARE v_user_id INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get book and user info
    SELECT book_id, user_id INTO v_book_id, v_user_id
    FROM borrowings 
    WHERE id = p_borrowing_id AND status IN ('active', 'overdue');
    
    IF v_book_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid borrowing record or book already returned';
    END IF;
    
    -- Update borrowing record
    UPDATE borrowings 
    SET 
        status = 'returned',
        return_date = NOW(),
        returned_to = p_returned_by,
        condition_on_return = p_condition,
        damage_notes = p_damage_notes
    WHERE id = p_borrowing_id;
    
    -- Update book availability
    UPDATE books 
    SET available_copies = available_copies + 1
    WHERE id = v_book_id;
    
    -- Log the activity
    INSERT INTO user_activity_log (user_id, action, entity_type, entity_id, details)
    VALUES (v_user_id, 'book_returned', 'borrowings', p_borrowing_id, 
            JSON_OBJECT('book_id', v_book_id, 'returned_by', p_returned_by, 'condition', p_condition));
    
    COMMIT;
END//

-- Procedure to borrow a book
CREATE PROCEDURE BorrowBook(
    IN p_user_id INT,
    IN p_book_id INT,
    IN p_issued_by INT,
    IN p_days INT
)
BEGIN
    DECLARE v_available_copies INT DEFAULT 0;
    DECLARE v_max_books INT DEFAULT 5;
    DECLARE v_current_books INT DEFAULT 0;
    DECLARE v_due_date TIMESTAMP;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get max books setting
    SELECT CAST(setting_value AS UNSIGNED) INTO v_max_books
    FROM system_settings 
    WHERE setting_key = 'max_books_per_user' 
    LIMIT 1;
    
    -- Check available copies
    SELECT available_copies INTO v_available_copies
    FROM books 
    WHERE id = p_book_id AND status = 'active';
    
    IF v_available_copies IS NULL OR v_available_copies <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Book not available for borrowing';
    END IF;
    
    -- Check user's current borrowings
    SELECT COUNT(*) INTO v_current_books
    FROM borrowings 
    WHERE user_id = p_user_id AND status IN ('active', 'overdue');
    
    IF v_current_books >= v_max_books THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User has reached maximum borrowing limit';
    END IF;
    
    -- Calculate due date
    SET v_due_date = DATE_ADD(NOW(), INTERVAL p_days DAY);
    
    -- Create borrowing record
    INSERT INTO borrowings (user_id, book_id, due_date, issued_by)
    VALUES (p_user_id, p_book_id, v_due_date, p_issued_by);
    
    -- Update book availability
    UPDATE books 
    SET available_copies = available_copies - 1
    WHERE id = p_book_id;
    
    -- Log the activity
    INSERT INTO user_activity_log (user_id, action, entity_type, entity_id, details)
    VALUES (p_user_id, 'book_borrowed', 'borrowings', LAST_INSERT_ID(), 
            JSON_OBJECT('book_id', p_book_id, 'issued_by', p_issued_by, 'due_date', v_due_date));
    
    COMMIT;
END//

DELIMITER ;

-- ============================================================================
-- TRIGGERS
-- ============================================================================

DELIMITER //

-- Trigger to update book availability when borrowing status changes
CREATE TRIGGER tr_borrowing_status_update
    AFTER UPDATE ON borrowings
    FOR EACH ROW
BEGIN
    -- If status changed from active/overdue to returned
    IF OLD.status IN ('active', 'overdue') AND NEW.status = 'returned' THEN
        UPDATE books 
        SET available_copies = available_copies + 1 
        WHERE id = NEW.book_id;
    END IF;
    
    -- If status changed from returned to active/overdue
    IF OLD.status = 'returned' AND NEW.status IN ('active', 'overdue') THEN
        UPDATE books 
        SET available_copies = available_copies - 1 
        WHERE id = NEW.book_id;
    END IF;
END//

-- Trigger to log user activity
CREATE TRIGGER tr_user_login_log
    AFTER UPDATE ON users
    FOR EACH ROW
BEGIN
    IF OLD.last_login != NEW.last_login THEN
        INSERT INTO user_activity_log (user_id, action, details)
        VALUES (NEW.id, 'user_login', JSON_OBJECT('ip_address', NEW.last_login_ip, 'timestamp', NEW.last_login));
    END IF;
END//

-- Trigger to validate book copies
CREATE TRIGGER tr_book_copies_validation
    BEFORE UPDATE ON books
    FOR EACH ROW
BEGIN
    IF NEW.available_copies > NEW.total_copies THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Available copies cannot exceed total copies';
    END IF;
    
    IF NEW.available_copies < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Available copies cannot be negative';
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- EVENTS (Optional - requires event scheduler to be enabled)
-- ============================================================================

-- Uncomment to enable automatic overdue checking
-- SET GLOBAL event_scheduler = ON;

-- CREATE EVENT ev_update_overdue_books
-- ON SCHEDULE EVERY 1 HOUR
-- STARTS CURRENT_TIMESTAMP
-- DO CALL UpdateOverdueBooks();

-- CREATE EVENT ev_cleanup_old_notifications
-- ON SCHEDULE EVERY 1 DAY
-- STARTS CURRENT_TIMESTAMP
-- DO DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND is_read = TRUE;

-- ============================================================================
-- FINAL VERIFICATION AND OPTIMIZATION
-- ============================================================================

-- Analyze tables for optimization
ANALYZE TABLE users, books, borrowings, categories, authors, book_authors, reservations;

-- Show final statistics
SELECT 'Enhanced Library Database Schema Created Successfully!' as status;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'library_system';
SELECT COUNT(*) as total_views FROM information_schema.views WHERE table_schema = 'library_system';
SELECT COUNT(*) as total_procedures FROM information_schema.routines WHERE routine_schema = 'library_system' AND routine_type = 'PROCEDURE';
SELECT COUNT(*) as total_triggers FROM information_schema.triggers WHERE trigger_schema = 'library_system';

-- Performance recommendations
-- 1. Enable query cache: SET GLOBAL query_cache_type = ON;
-- 2. Optimize innodb_buffer_pool_size based on available RAM
-- 3. Regular OPTIMIZE TABLE maintenance
-- 4. Monitor slow query log
-- 5. Consider partitioning for large tables (borrowings, user_activity_log)

-- Security recommendations
-- 1. Change default admin password immediately
-- 2. Create dedicated database users with minimal privileges
-- 3. Enable SSL connections
-- 4. Regular security updates
-- 5. Implement proper backup strategy

