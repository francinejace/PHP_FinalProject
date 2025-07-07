# Library Management System

A comprehensive PHP-based library management system with a beautiful brown & beige theme, designed for efficient book management and student borrowing.

## Features

### 🔐 User Management
- **Admin/Librarian Account**: Full system access for book and user management
- **Student Account**: Browse and borrow books with borrowing history
- Secure authentication with password hashing
- Role-based access control

### 📚 Book Management
- **Automatic Book ID Generation**: Format `TTMMMDDYYYY-CCC#####`  
  *(Note: TTMMMDDYYYY and CCC##### are format placeholders, not literal words)*
  - TT: First 2 letters of title
  - MMM: Publication month (JAN, FEB, etc.)
  - DD: Day (fixed as 10)
  - YYYY: Publication year
  - CCC: Category code (FIC, NON, SCI, etc.)
  - #####: Sequential number (00001, 00002, etc.)
- Add, edit, archive, and delete books
- Search and filter by title, author, category, or status
- Minimum 50 books requirement enforced

### 📖 Borrowing System
- **Borrowing Rules**:
  - Maximum 2 books per student
  - 7-day borrowing period (including weekends)
  - ₱10.00 fine per day for overdue books
- Real-time status tracking (available, borrowed, overdue, archived)
- Automatic overdue detection and fine calculation
- Cannot borrow archived books
- Cannot delete books with active borrowings

### 🎨 Design Features
- **Brown & Beige Theme**: Professional and warm color scheme
- Responsive design for desktop and mobile
- Smooth animations and hover effects
- Interactive dashboard with statistics
- Real-time clock and status updates

## Technical Specifications

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Database Schema
- **users**: User accounts with roles (admin/student)
- **books**: Book catalog with generated IDs
- **borrowings**: Borrowing records with due dates and fines
- **categories**: Book categories for reference

### Security Features
- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication
- Role-based access control

## Installation

### 1. Database Setup
```sql
-- Import the database schema
mysql -u root -p < database/library.sql
```

### 2. Configuration
Edit `config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'library_system');
```

### 3. Web Server Setup
- Place files in your web server document root
- Ensure PHP has write permissions for session handling
- Configure virtual host if needed

### 4. Default Admin Account
- **Username**: admin
- **Password**: password
- **Role**: Administrator

## File Structure

```
php_library_system/
├── admin/                  # Admin pages
│   ├── dashboard.php      # Admin dashboard
│   ├── add.php           # Add new books
│   ├── edit.php          # Edit books
│   ├── manage.php        # Manage books and borrowings
│   ├── users.php         # User management
│   └── archive.php       # Archived books
├── assets/               # Static assets
│   ├── style.css         # Brown & beige theme CSS
│   └── script.js         # Interactive JavaScript
├── database/             # Database files
│   └── library.sql       # Database schema and sample data
├── includes/             # Reusable components
│   ├── header.php        # Page header
│   ├── footer.php        # Page footer
│   └── navbar.php        # Navigation bar
├── student/              # Student pages
│   ├── dashboard.php     # Student dashboard
│   ├── browse.php        # Browse books
│   ├── borrow.php        # Borrow books
│   └── return.php        # Return books
├── user/                 # Authentication pages
│   ├── login.php         # User login
│   ├── register.php      # User registration
│   └── logout.php        # User logout
├── config.php            # Database configuration
├── functions.php         # Core functions
├── index.php            # Main entry point
└── README.md            # This file
```

## Usage

### For Administrators
1. Login with admin credentials
2. View dashboard with library statistics
3. Add new books with automatic ID generation
4. Manage existing books (edit, archive, delete)
5. Monitor borrowings and overdue books
6. Manage user accounts

### For Students
1. Register for a new account
2. Browse available books
3. Borrow up to 2 books at a time
4. View borrowing history and due dates
5. Return books before due date to avoid fines

## Book ID Generation Example

For a book titled "The Great Gatsby" in Fiction category, published in February 2022:
- Title prefix: TH (first 2 letters)
- Month: FEB (February)
- Day: 10 (fixed)
- Year: 2022
- Category: FIC (Fiction)
- **Result**: `THFEB102022-FIC00001`  
  *(Note: THFEB102022 is a sample generated ID, not a literal word)*
- **Result**: `THFEB102022-FIC00001`

## Deployment

### For Web Hosting
1. Upload all files to your hosting account
2. Create MySQL database and import schema
3. Update `config.php` with hosting database credentials
4. Set appropriate file permissions
5. Test the application

### For GitHub Backup
```bash
git init
git add .
git commit -m "Initial commit: Library Management System"
git remote add origin https://github.com/yourusername/library-system.git
git push -u origin main
```

## Customization

### Theme Colors
Edit `assets/style.css` to modify the color scheme:
```css
:root {
    --primary-brown: #8B4513;
    --dark-brown: #654321;
    --light-brown: #A0522D;
    --beige: #F5F5DC;
    --light-beige: #FAEBD7;
    --cream: #FFF8DC;
}
```

### Library Rules
Modify borrowing rules in `functions.php`:
```php
// Change maximum books per user
return $active_borrowings < 2; // Change 2 to desired limit
$due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // Change +7 days
// Note: strtotime is a standard PHP function for date/time manipulation
// Change borrowing period
$due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // Change +7 days

// Change fine amount
$fine_amount = $days_overdue * 10.00; // Change 10.00 to desired amount
```

## Support

For issues or questions:
1. Check the database connection in `config.php`
2. Verify file permissions for web server
3. Check PHP error logs for debugging
4. Ensure all required PHP extensions are installed

## License

This project is open source and available under the MIT License.

## Credits

Developed with ❤️ for efficient library management
- Beautiful brown & beige theme design
- Responsive and interactive user interface
- Comprehensive book and user management
- Automated fine calculation system

