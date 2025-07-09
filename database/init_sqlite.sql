-- Library Management System - Enhanced SQLite Database Schema
-- Version 2.0 - Improved Security, Performance, and Data Integrity
-- Compatible with SQLite 3.35+ and PHP 7.4+

-- Enable foreign key constraints
PRAGMA foreign_keys = ON;

-- Enable WAL mode for better concurrency
PRAGMA journal_mode = WAL;

-- Optimize SQLite settings
PRAGMA synchronous = NORMAL;
PRAGMA cache_size = 10000;
PRAGMA temp_store = MEMORY;

-- ============================================================================
-- ROLES TABLE - Enhanced role management
-- ============================================================================
CREATE TABLE roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions TEXT, -- JSON string for SQLite
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- USERS TABLE - Enhanced with security features
-- ============================================================================
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id INTEGER NOT NULL DEFAULT 2, -- Default to student role
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    student_id VARCHAR(50) UNIQUE,
    department VARCHAR(100),
    
    -- Security fields
    email_verified BOOLEAN DEFAULT 0,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    failed_login_attempts INTEGER DEFAULT 0,
    locked_until DATETIME,
    last_login DATETIME,
    last_login_ip VARCHAR(45),
    
    -- Status and metadata
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('active', 'inactive', 'suspended', 'pending')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- CATEGORIES TABLE - Enhanced categorization
-- ============================================================================
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    parent_id INTEGER,
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ============================================================================
-- PUBLISHERS TABLE - Separate publisher management
-- ============================================================================
CREATE TABLE publishers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(200) NOT NULL,
    address TEXT,
    website VARCHAR(255),
    email VARCHAR(100),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- AUTHORS TABLE - Separate author management
-- ============================================================================
CREATE TABLE authors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(200) GENERATED ALWAYS AS (first_name || ' ' || last_name) STORED,
    biography TEXT,
    birth_date DATE,
    death_date DATE,
    nationality VARCHAR(100),
    website VARCHAR(255),
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- BOOKS TABLE - Enhanced with better structure
-- ============================================================================
CREATE TABLE books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(500) NOT NULL,
    subtitle VARCHAR(500),
    category_id INTEGER NOT NULL,
    publisher_id INTEGER,
    isbn10 VARCHAR(10) UNIQUE,
    isbn13 VARCHAR(13) UNIQUE,
    edition VARCHAR(50),
    publication_year INTEGER NOT NULL,
    publication_month INTEGER NOT NULL CHECK (publication_month BETWEEN 1 AND 12),
    publication_day INTEGER CHECK (publication_day BETWEEN 1 AND 31),
    pages INTEGER CHECK (pages > 0),
    language VARCHAR(10) DEFAULT 'en',
    
    -- Physical properties
    total_copies INTEGER NOT NULL DEFAULT 1 CHECK (total_copies > 0),
    available_copies INTEGER NOT NULL DEFAULT 1 CHECK (available_copies >= 0),
    location VARCHAR(100), -- Shelf location
    
    -- Content
    description TEXT,
    summary TEXT,
    table_of_contents TEXT,
    cover_image_url VARCHAR(500),
    
    -- Pricing and acquisition
    purchase_price DECIMAL(10,2) CHECK (purchase_price >= 0),
    purchase_date DATE,
    supplier VARCHAR(200),
    
    -- Status and metadata
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'damaged', 'lost', 'archived')),
    condition_notes TEXT,
    last_inventory_check DATE,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- BOOK_AUTHORS TABLE - Many-to-many relationship
-- ============================================================================
CREATE TABLE book_authors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id INTEGER NOT NULL,
    author_id INTEGER NOT NULL,
    author_order INTEGER DEFAULT 1,
    role VARCHAR(20) DEFAULT 'author' CHECK (role IN ('author', 'co-author', 'editor', 'translator', 'illustrator')),
    
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE,
    
    UNIQUE(book_id, author_id, role)
);

-- ============================================================================
-- BORROWINGS TABLE - Enhanced borrowing management
-- ============================================================================
CREATE TABLE borrowings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    book_id INTEGER NOT NULL,
    
    -- Borrowing details
    borrow_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    due_date DATETIME NOT NULL,
    return_date DATETIME,
    renewed_count INTEGER DEFAULT 0 CHECK (renewed_count >= 0),
    max_renewals INTEGER DEFAULT 2 CHECK (max_renewals >= 0),
    
    -- Status and conditions
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'returned', 'overdue', 'lost', 'damaged')),
    condition_on_borrow VARCHAR(20) DEFAULT 'good' CHECK (condition_on_borrow IN ('excellent', 'good', 'fair', 'poor')),
    condition_on_return VARCHAR(20) CHECK (condition_on_return IN ('excellent', 'good', 'fair', 'poor')),
    damage_notes TEXT,
    
    -- Financial
    fine_amount DECIMAL(10,2) DEFAULT 0.00 CHECK (fine_amount >= 0),
    fine_reason VARCHAR(200),
    fine_paid BOOLEAN DEFAULT 0,
    fine_paid_date DATETIME,
    fine_waived BOOLEAN DEFAULT 0,
    fine_waived_reason TEXT,
    
    -- Metadata
    issued_by INTEGER,
    returned_to INTEGER,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (returned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- RESERVATIONS TABLE - Book reservation system
-- ============================================================================
CREATE TABLE reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    book_id INTEGER NOT NULL,
    reservation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'fulfilled', 'expired', 'cancelled')),
    priority INTEGER DEFAULT 1,
    notified BOOLEAN DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- ============================================================================
-- USER_ACTIVITY_LOG TABLE - Audit trail
-- ============================================================================
CREATE TABLE user_activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INTEGER,
    details TEXT, -- JSON string for SQLite
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================================
-- SYSTEM_SETTINGS TABLE - Configuration management
-- ============================================================================
CREATE TABLE system_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string' CHECK (setting_type IN ('string', 'integer', 'boolean', 'json')),
    description TEXT,
    is_public BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- NOTIFICATIONS TABLE - User notifications
-- ============================================================================
CREATE TABLE notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    data TEXT, -- JSON string for SQLite
    is_read BOOLEAN DEFAULT 0,
    read_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================================================

-- Users table indexes
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_student_id ON users(student_id);
CREATE INDEX idx_users_last_login ON users(last_login);

-- Books table indexes
CREATE INDEX idx_books_book_id ON books(book_id);
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_category_id ON books(category_id);
CREATE INDEX idx_books_publisher_id ON books(publisher_id);
CREATE INDEX idx_books_status ON books(status);
CREATE INDEX idx_books_isbn10 ON books(isbn10);
CREATE INDEX idx_books_isbn13 ON books(isbn13);
CREATE INDEX idx_books_publication_year ON books(publication_year);
CREATE INDEX idx_books_available_copies ON books(available_copies);

-- Borrowings table indexes
CREATE INDEX idx_borrowings_user_id ON borrowings(user_id);
CREATE INDEX idx_borrowings_book_id ON borrowings(book_id);
CREATE INDEX idx_borrowings_status ON borrowings(status);
CREATE INDEX idx_borrowings_borrow_date ON borrowings(borrow_date);
CREATE INDEX idx_borrowings_due_date ON borrowings(due_date);
CREATE INDEX idx_borrowings_return_date ON borrowings(return_date);

-- Other table indexes
CREATE INDEX idx_categories_code ON categories(code);
CREATE INDEX idx_categories_parent_id ON categories(parent_id);
CREATE INDEX idx_authors_full_name ON authors(full_name);
CREATE INDEX idx_authors_last_name ON authors(last_name);
CREATE INDEX idx_book_authors_book_id ON book_authors(book_id);
CREATE INDEX idx_book_authors_author_id ON book_authors(author_id);
CREATE INDEX idx_reservations_user_id ON reservations(user_id);
CREATE INDEX idx_reservations_book_id ON reservations(book_id);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_user_activity_log_user_id ON user_activity_log(user_id);
CREATE INDEX idx_user_activity_log_action ON user_activity_log(action);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);

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
('library_name', 'Library Management System', 'string', 'Name of the library', 1),
('max_borrow_days', '14', 'integer', 'Default borrowing period in days', 1),
('max_renewals', '2', 'integer', 'Maximum number of renewals allowed', 1),
('fine_per_day', '1.00', 'string', 'Fine amount per day for overdue books', 1),
('max_books_per_user', '5', 'integer', 'Maximum books a user can borrow', 1),
('reservation_expiry_days', '3', 'integer', 'Days before reservation expires', 1),
('email_notifications', 'true', 'boolean', 'Enable email notifications', 0),
('auto_overdue_check', 'true', 'boolean', 'Automatically check for overdue books', 0),
('library_email', 'library@example.com', 'string', 'Library contact email', 1),
('library_phone', '+1-234-567-8900', 'string', 'Library contact phone', 1);

-- Insert default admin user (password: admin123 - CHANGE IMMEDIATELY)
INSERT INTO users (username, email, password_hash, role_id, full_name, status, email_verified) VALUES
('admin', 'admin@library.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj/VjWZpX3PO', 1, 'System Administrator', 'active', 1);

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

-- Insert sample books with proper relationships
INSERT INTO books (book_id, title, category_id, publisher_id, isbn13, publication_year, publication_month, description, total_copies, available_copies) VALUES
('TOFEB102022-FIC00001', 'To Kill a Mockingbird', 1, 1, '9780061120084', 2022, 2, 'A classic American novel', 3, 3),
('NIFEB102022-FIC00002', '1984', 1, 2, '9780451524935', 2022, 2, 'Dystopian social science fiction novel', 2, 2),
('PRFEB102022-FIC00003', 'Pride and Prejudice', 1, 1, '9780141439518', 2022, 2, 'Romantic novel', 2, 2),
('THFEB102022-FIC00004', 'The Great Gatsby', 1, 3, '9780743273565', 2022, 2, 'American classic', 2, 2),
('HAFEB102022-FIC00005', 'Harry Potter and the Sorcerer Stone', 1, 4, '9780439708180', 2022, 2, 'Fantasy novel', 5, 5),
('LOFEB102022-FIC00006', 'Lord of the Rings', 1, 5, '9780544003415', 2022, 2, 'Epic fantasy', 3, 3),
('THFEB102022-SCI00007', 'The Origin of Species', 3, 6, '9780486450063', 2022, 2, 'Scientific work on evolution', 2, 2),
('AFEB102022-SCI00008', 'A Brief History of Time', 3, 7, '9780553380163', 2022, 2, 'Popular science book', 2, 2),
('THFEB102022-HIS00009', 'The Art of War', 4, 8, '9781599869773', 2022, 2, 'Ancient Chinese military treatise', 2, 2),
('SAFEB102022-NON00010', 'Sapiens', 2, 9, '9780062316097', 2022, 2, 'History of humankind', 3, 3);

-- Link books with authors
INSERT INTO book_authors (book_id, author_id, author_order) VALUES
(1, 1, 1), -- To Kill a Mockingbird - Harper Lee
(2, 2, 1), -- 1984 - George Orwell
(3, 3, 1), -- Pride and Prejudice - Jane Austen
(4, 4, 1), -- The Great Gatsby - F. Scott Fitzgerald
(5, 5, 1), -- Harry Potter - J.K. Rowling
(6, 6, 1), -- Lord of the Rings - J.R.R. Tolkien
(7, 7, 1), -- The Origin of Species - Charles Darwin
(8, 8, 1), -- A Brief History of Time - Stephen Hawking
(9, 9, 1), -- The Art of War - Sun Tzu
(10, 10, 1); -- Sapiens - Yuval Noah Harari

-- ============================================================================
-- VIEWS FOR REPORTING (SQLite compatible)
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
    SUM(CASE WHEN fine_paid = 1 THEN fine_amount ELSE 0 END) as paid_fines,
    SUM(CASE WHEN fine_waived = 1 THEN fine_amount ELSE 0 END) as waived_fines
FROM borrowings;

-- Popular books view
CREATE VIEW v_popular_books AS
SELECT 
    b.id,
    b.book_id,
    b.title,
    GROUP_CONCAT(a.full_name, ', ') as authors,
    c.name as category,
    COUNT(br.id) as borrow_count
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
    GROUP_CONCAT(a.full_name, ', ') as authors,
    br.borrow_date,
    br.due_date,
    CAST((julianday('now') - julianday(br.due_date)) AS INTEGER) as days_overdue,
    br.fine_amount,
    br.fine_paid
FROM borrowings br
JOIN users u ON br.user_id = u.id
JOIN books b ON br.book_id = b.id
LEFT JOIN book_authors ba ON b.id = ba.book_id
LEFT JOIN authors a ON ba.author_id = a.id
WHERE br.status IN ('active', 'overdue') 
AND br.due_date < datetime('now')
GROUP BY br.id, br.user_id, u.username, u.full_name, u.email, br.book_id, b.book_id, b.title, br.borrow_date, br.due_date, br.fine_amount, br.fine_paid
ORDER BY days_overdue DESC, br.due_date;

-- ============================================================================
-- TRIGGERS FOR DATA INTEGRITY
-- ============================================================================

-- Trigger to update timestamps
CREATE TRIGGER tr_users_updated_at
    AFTER UPDATE ON users
    FOR EACH ROW
BEGIN
    UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER tr_books_updated_at
    AFTER UPDATE ON books
    FOR EACH ROW
BEGIN
    UPDATE books SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER tr_borrowings_updated_at
    AFTER UPDATE ON borrowings
    FOR EACH ROW
BEGIN
    UPDATE borrowings SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Trigger to validate book copies
CREATE TRIGGER tr_book_copies_validation
    BEFORE UPDATE ON books
    FOR EACH ROW
    WHEN NEW.available_copies > NEW.total_copies OR NEW.available_copies < 0
BEGIN
    SELECT RAISE(ABORT, 'Available copies must be between 0 and total copies');
END;

-- Trigger to update book availability when borrowing
CREATE TRIGGER tr_borrowing_insert
    AFTER INSERT ON borrowings
    FOR EACH ROW
BEGIN
    UPDATE books 
    SET available_copies = available_copies - 1 
    WHERE id = NEW.book_id;
END;

-- Trigger to update book availability when returning
CREATE TRIGGER tr_borrowing_return
    AFTER UPDATE ON borrowings
    FOR EACH ROW
    WHEN OLD.status IN ('active', 'overdue') AND NEW.status = 'returned'
BEGIN
    UPDATE books 
    SET available_copies = available_copies + 1 
    WHERE id = NEW.book_id;
END;

-- ============================================================================
-- FINAL OPTIMIZATION
-- ============================================================================

-- Analyze tables for query optimization
ANALYZE;

-- Vacuum to optimize database file
VACUUM;

-- Final verification
SELECT 'Enhanced SQLite Library Database Schema Created Successfully!' as status;
SELECT COUNT(*) as total_tables FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%';
SELECT COUNT(*) as total_views FROM sqlite_master WHERE type = 'view';
SELECT COUNT(*) as total_triggers FROM sqlite_master WHERE type = 'trigger';
SELECT COUNT(*) as total_indexes FROM sqlite_master WHERE type = 'index' AND name NOT LIKE 'sqlite_%';

-- Performance and security notes:
-- 1. Change default admin password immediately after setup
-- 2. Regular VACUUM and ANALYZE for maintenance
-- 3. Consider using WAL mode for better concurrency
-- 4. Implement proper backup strategy
-- 5. Use prepared statements in PHP to prevent SQL injection

