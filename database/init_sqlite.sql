-- Library Management System SQLite Database Schema

-- Users table (Admin/Librarian and Students)
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'student' CHECK (role IN ('admin', 'student')),
    full_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1
);

-- Books table
CREATE TABLE books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    publication_year INTEGER NOT NULL,
    publication_month INTEGER NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'available' CHECK (status IN ('available', 'borrowed', 'archived')),
    date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Borrowing records table
CREATE TABLE borrowings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    book_id INTEGER NOT NULL,
    borrow_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    due_date DATETIME NOT NULL,
    return_date DATETIME NULL,
    status VARCHAR(20) DEFAULT 'borrowed' CHECK (status IN ('borrowed', 'returned', 'overdue')),
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    fine_paid BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Categories table (for reference)
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Insert default categories
INSERT INTO categories (code, name, description) VALUES
('FIC', 'Fiction', 'Fiction books and novels'),
('NON', 'Non-Fiction', 'Non-fiction books'),
('SCI', 'Science', 'Science and technology books'),
('HIS', 'History', 'History books'),
('BIO', 'Biography', 'Biography and autobiography'),
('REF', 'Reference', 'Reference books and dictionaries'),
('EDU', 'Education', 'Educational and academic books'),
('ART', 'Arts', 'Arts and literature books');

-- Insert default admin user (password: password)
INSERT INTO users (username, email, password_hash, role, full_name) VALUES
('admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator');

-- Insert sample books (minimum 50 books requirement)
INSERT INTO books (book_id, title, author, category, publication_year, publication_month, description) VALUES
('TOFEB102022-FIC00001', 'To Kill a Mockingbird', 'Harper Lee', 'FIC', 2022, 2, 'A classic American novel'),
('NIFEB102022-FIC00002', '1984', 'George Orwell', 'FIC', 2022, 2, 'Dystopian social science fiction novel'),
('PRFEB102022-FIC00003', 'Pride and Prejudice', 'Jane Austen', 'FIC', 2022, 2, 'Romantic novel'),
('THFEB102022-FIC00004', 'The Great Gatsby', 'F. Scott Fitzgerald', 'FIC', 2022, 2, 'American classic'),
('HAFEB102022-FIC00005', 'Harry Potter and the Sorcerer Stone', 'J.K. Rowling', 'FIC', 2022, 2, 'Fantasy novel'),
('LOFEB102022-FIC00006', 'Lord of the Rings', 'J.R.R. Tolkien', 'FIC', 2022, 2, 'Epic fantasy'),
('THFEB102022-SCI00007', 'The Origin of Species', 'Charles Darwin', 'SCI', 2022, 2, 'Scientific work on evolution'),
('AFEB102022-SCI00008', 'A Brief History of Time', 'Stephen Hawking', 'SCI', 2022, 2, 'Popular science book'),
('THFEB102022-HIS00009', 'The Art of War', 'Sun Tzu', 'HIS', 2022, 2, 'Ancient Chinese military treatise'),
('SAFEB102022-NON00010', 'Sapiens', 'Yuval Noah Harari', 'NON', 2022, 2, 'History of humankind'),
('THFEB102022-FIC00011', 'The Catcher in the Rye', 'J.D. Salinger', 'FIC', 2022, 2, 'Coming-of-age story'),
('ANFEB102022-FIC00012', 'Animal Farm', 'George Orwell', 'FIC', 2022, 2, 'Political allegory'),
('BRFEB102022-FIC00013', 'Brave New World', 'Aldous Huxley', 'FIC', 2022, 2, 'Dystopian novel'),
('THFEB102022-FIC00014', 'The Hobbit', 'J.R.R. Tolkien', 'FIC', 2022, 2, 'Fantasy adventure'),
('JAFEB102022-FIC00015', 'Jane Eyre', 'Charlotte Bronte', 'FIC', 2022, 2, 'Gothic romance'),
('WUFEB102022-FIC00016', 'Wuthering Heights', 'Emily Bronte', 'FIC', 2022, 2, 'Gothic novel'),
('THFEB102022-FIC00017', 'The Picture of Dorian Gray', 'Oscar Wilde', 'FIC', 2022, 2, 'Philosophical novel'),
('DRFEB102022-FIC00018', 'Dracula', 'Bram Stoker', 'FIC', 2022, 2, 'Gothic horror'),
('FRFEB102022-FIC00019', 'Frankenstein', 'Mary Shelley', 'FIC', 2022, 2, 'Science fiction horror'),
('THFEB102022-FIC00020', 'The Adventures of Tom Sawyer', 'Mark Twain', 'FIC', 2022, 2, 'Adventure novel'),
('ADFEB102022-FIC00021', 'Adventures of Huckleberry Finn', 'Mark Twain', 'FIC', 2022, 2, 'Adventure novel'),
('LIFEB102022-FIC00022', 'Little Women', 'Louisa May Alcott', 'FIC', 2022, 2, 'Coming-of-age novel'),
('THFEB102022-FIC00023', 'The Secret Garden', 'Frances Hodgson Burnett', 'FIC', 2022, 2, 'Children literature'),
('ALFEB102022-FIC00024', 'Alice Adventures in Wonderland', 'Lewis Carroll', 'FIC', 2022, 2, 'Fantasy novel'),
('THFEB102022-FIC00025', 'The Lion, the Witch and the Wardrobe', 'C.S. Lewis', 'FIC', 2022, 2, 'Fantasy novel'),
('CHFEB102022-FIC00026', 'Charlotte Web', 'E.B. White', 'FIC', 2022, 2, 'Children literature'),
('WHFEB102022-FIC00027', 'Where the Red Fern Grows', 'Wilson Rawls', 'FIC', 2022, 2, 'Adventure novel'),
('THFEB102022-FIC00028', 'The Giver', 'Lois Lowry', 'FIC', 2022, 2, 'Dystopian novel'),
('HOFEB102022-FIC00029', 'Holes', 'Louis Sachar', 'FIC', 2022, 2, 'Adventure novel'),
('BRFEB102022-FIC00030', 'Bridge to Terabithia', 'Katherine Paterson', 'FIC', 2022, 2, 'Children literature'),
('THFEB102022-SCI00031', 'The Selfish Gene', 'Richard Dawkins', 'SCI', 2022, 2, 'Evolutionary biology'),
('COFEB102022-SCI00032', 'Cosmos', 'Carl Sagan', 'SCI', 2022, 2, 'Popular science'),
('SIFEB102022-SCI00033', 'Silent Spring', 'Rachel Carson', 'SCI', 2022, 2, 'Environmental science'),
('THFEB102022-SCI00034', 'The Double Helix', 'James Watson', 'SCI', 2022, 2, 'Molecular biology'),
('ONFEB102022-SCI00035', 'On the Origin of Species', 'Charles Darwin', 'SCI', 2022, 2, 'Evolution'),
('THFEB102022-HIS00036', 'The Diary of a Young Girl', 'Anne Frank', 'HIS', 2022, 2, 'Historical memoir'),
('NIFEB102022-HIS00037', 'Night', 'Elie Wiesel', 'HIS', 2022, 2, 'Holocaust memoir'),
('THFEB102022-HIS00038', 'The Guns of August', 'Barbara Tuchman', 'HIS', 2022, 2, 'World War I history'),
('AFEB102022-HIS00039', 'A People History of the United States', 'Howard Zinn', 'HIS', 2022, 2, 'American history'),
('THFEB102022-HIS00040', 'The Rise and Fall of the Third Reich', 'William Shirer', 'HIS', 2022, 2, 'Nazi Germany history'),
('AUFEB102022-BIO00041', 'Autobiography of Malcolm X', 'Malcolm X', 'BIO', 2022, 2, 'Autobiography'),
('LOFEB102022-BIO00042', 'Long Walk to Freedom', 'Nelson Mandela', 'BIO', 2022, 2, 'Autobiography'),
('STFEB102022-BIO00043', 'Steve Jobs', 'Walter Isaacson', 'BIO', 2022, 2, 'Biography'),
('EIFEB102022-BIO00044', 'Einstein: His Life and Universe', 'Walter Isaacson', 'BIO', 2022, 2, 'Biography'),
('BEFEB102022-BIO00045', 'Benjamin Franklin: An American Life', 'Walter Isaacson', 'BIO', 2022, 2, 'Biography'),
('OXFEB102022-REF00046', 'Oxford English Dictionary', 'Oxford University Press', 'REF', 2022, 2, 'Dictionary'),
('ENFEB102022-REF00047', 'Encyclopedia Britannica', 'Britannica', 'REF', 2022, 2, 'Encyclopedia'),
('WEBFEB102022-REF00048', 'Webster Dictionary', 'Merriam-Webster', 'REF', 2022, 2, 'Dictionary'),
('THFEB102022-REF00049', 'The Elements of Style', 'Strunk and White', 'REF', 2022, 2, 'Writing guide'),
('CHFEB102022-REF00050', 'Chicago Manual of Style', 'University of Chicago Press', 'REF', 2022, 2, 'Style guide');

-- Create indexes for better performance
CREATE INDEX idx_books_status ON books(status);
CREATE INDEX idx_books_category ON books(category);
CREATE INDEX idx_borrowings_user ON borrowings(user_id);
CREATE INDEX idx_borrowings_book ON borrowings(book_id);
CREATE INDEX idx_borrowings_status ON borrowings(status);
CREATE INDEX idx_borrowings_due_date ON borrowings(due_date);

