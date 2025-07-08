# Library Management System - Enhanced Version

A modern, secure, and fully functional PHP-based library management system with enhanced features, improved security, and better user experience.

## 🚀 Features

### Core Functionality
- **User Management**: Multi-role system (Admin, Librarian, Student)
- **Book Management**: Add, edit, delete, and search books
- **Borrowing System**: Track book borrowings and returns
- **Advanced Search**: Search by title, author, ISBN, category
- **Real-time Availability**: Live book availability tracking
- **Overdue Management**: Automatic overdue detection and notifications

### Security Enhancements
- **CSRF Protection**: Cross-Site Request Forgery protection
- **SQL Injection Prevention**: Prepared statements and input validation
- **XSS Protection**: Output sanitization and Content Security Policy
- **Session Security**: Secure session management with timeout
- **Password Hashing**: Modern password hashing with Argon2ID
- **Rate Limiting**: Protection against brute force attacks
- **Input Validation**: Comprehensive server-side validation

### Modern Features
- **Responsive Design**: Mobile-first, accessible design
- **Progressive Enhancement**: Works without JavaScript
- **WCAG Compliance**: Accessibility standards compliance
- **Modern PHP**: PHP 7.4+ features and best practices
- **PDO Database**: Modern database abstraction layer
- **Error Handling**: Comprehensive error logging and handling
- **Performance Optimization**: Caching, compression, and optimization

## 📋 Requirements

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Storage**: Minimum 100MB free space
- **SSL Certificate**: Recommended for production

### PHP Extensions
- PDO MySQL
- MySQLi (for backward compatibility)
- Session
- Filter
- Hash
- JSON
- OpenSSL

## 🛠️ Installation

### 1. Download and Extract
```bash
# Extract the files to your web server directory
unzip library_system_revised.zip
cd library_system_revised
```

### 2. Database Setup
1. Create a MySQL database
2. Import the database schema (if provided)
3. Create a database user with appropriate privileges

### 3. Configuration
1. Copy `config_production.php` to `config.php` for production
2. Update database credentials in `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

### 4. File Permissions
```bash
# Set appropriate permissions
chmod 644 *.php
chmod 755 assets/
chmod 755 includes/
chmod 600 config.php
```

### 5. Web Server Configuration
- Ensure `.htaccess` is uploaded and mod_rewrite is enabled
- Configure SSL certificate (recommended)
- Set up proper error pages

## 🔧 Configuration Options

### Environment Detection
The system automatically detects development vs production environment:
- **Development**: `localhost` domain
- **Production**: Any other domain

### Security Settings
```php
// Session timeout (seconds)
define('SESSION_TIMEOUT', 3600);

// CSRF token name
define('CSRF_TOKEN_NAME', 'csrf_token');
```

### Application Settings
```php
define('SITE_NAME', 'Library Management System');
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
```

## 👥 User Roles and Permissions

### Admin
- Full system access
- User management
- System configuration
- All book and borrowing operations
- Reports and analytics

### Librarian
- Book management (add, edit, delete)
- Borrowing management
- User borrowing history
- Overdue book management
- Basic reports

### Student
- Browse and search books
- Borrow and return books
- View personal borrowing history
- Update personal profile

## 🔒 Security Features

### Authentication & Authorization
- Secure password hashing with Argon2ID
- Session-based authentication
- Role-based access control
- Session timeout and regeneration

### Input Protection
- CSRF token validation
- SQL injection prevention
- XSS protection with output encoding
- File upload validation
- Rate limiting for sensitive operations

### Server Security
- Security headers via .htaccess
- File access restrictions
- Directory browsing disabled
- Sensitive file protection
- Error information hiding in production

## 📱 Responsive Design

### Mobile-First Approach
- Responsive grid system
- Touch-friendly interface
- Optimized for all screen sizes
- Progressive enhancement

### Accessibility
- WCAG 2.1 AA compliance
- Screen reader support
- Keyboard navigation
- High contrast support
- Focus indicators

## 🚀 Performance

### Optimization Features
- Gzip compression
- Browser caching
- CSS and JavaScript minification
- Database query optimization
- Image optimization

### Caching Strategy
- Static asset caching
- Database query caching
- Session data optimization

## 🔍 Testing

### Test Page
Access `/test.php` to verify:
- Styling and components
- Form validation
- Responsive design
- Accessibility features
- JavaScript functionality

### Security Testing
- CSRF protection
- SQL injection prevention
- XSS protection
- Session security
- File upload security

## 📊 Monitoring and Maintenance

### Logging
- Error logging
- Security event logging
- User activity logging
- Performance monitoring

### Regular Maintenance
- Database optimization
- Log file rotation
- Security updates
- Backup verification

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Error
```
Database connection failed. Please contact the administrator.
```
**Solution**: Check database credentials and server status

#### Permission Denied
```
403 Forbidden
```
**Solution**: Check file permissions and .htaccess configuration

#### Session Issues
```
Session timeout or invalid session
```
**Solution**: Check session configuration and server time

#### White Screen
**Solution**: Check PHP error logs and enable error reporting in development

### Debug Mode
For development, enable debug mode in `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Performance Optimization

### Server-Level
- Enable OPcache
- Configure MySQL query cache
- Use SSD storage
- Optimize Apache/Nginx configuration

### Application-Level
- Database indexing
- Query optimization
- Image compression
- CSS/JS minification

## 🔄 Updates and Maintenance

### Regular Tasks
- **Daily**: Monitor error logs
- **Weekly**: Check security logs
- **Monthly**: Database optimization
- **Quarterly**: Security audit

### Backup Strategy
- Database backups
- File system backups
- Configuration backups
- Regular restore testing

## 📞 Support

### Documentation
- Code comments throughout
- Inline documentation
- Function documentation
- Security notes

### Getting Help
1. Check error logs
2. Review configuration
3. Test in development environment
4. Check server requirements

## 📄 License

This project is provided as-is for educational and commercial use. Please ensure compliance with any third-party libraries or dependencies.

## 🤝 Contributing

### Code Standards
- PSR-12 coding standards
- Comprehensive commenting
- Security-first approach
- Accessibility compliance

### Security
- Report security issues privately
- Follow responsible disclosure
- Test thoroughly before deployment

## 📝 Changelog

### Version 2.0 (Enhanced)
- ✅ Enhanced security features
- ✅ Modern PHP practices
- ✅ Responsive design
- ✅ Accessibility improvements
- ✅ Performance optimization
- ✅ Comprehensive error handling
- ✅ CSRF protection
- ✅ Input validation
- ✅ Session security
- ✅ Modern UI/UX

### Version 1.0 (Original)
- Basic library management
- User authentication
- Book borrowing system
- Simple admin interface

---

**Important**: Always test in a staging environment before deploying to production. Keep regular backups and monitor system logs.

For technical support or questions, please refer to the code documentation and comments within the files.

