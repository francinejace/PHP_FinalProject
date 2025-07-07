# Library Management System - Deployment Guide

This guide will help you deploy the PHP Library Management System to a web hosting provider and set up GitHub backup.

## 🚀 Pre-Deployment Checklist

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache or Nginx
- **Storage**: At least 100MB free space
- **SSL Certificate**: Recommended for production

### Files to Prepare
- [ ] All PHP files
- [ ] Database schema (MySQL version)
- [ ] Production configuration
- [ ] .htaccess file for Apache
- [ ] Documentation

## 📁 File Structure for Hosting

```
public_html/ (or your domain folder)
├── admin/
│   ├── dashboard.php
│   ├── add.php
│   ├── edit.php
│   ├── manage.php
│   ├── users.php
│   └── archive.php
├── assets/
│   ├── style.css
│   └── script.js
├── database/
│   └── library_mysql.sql
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── student/
│   ├── dashboard.php
│   ├── browse.php
│   ├── borrow.php
│   └── return.php
├── user/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config.php
├── functions.php
├── index.php
├── .htaccess
└── README.md
```

## 🗄️ Database Setup

### Step 1: Create MySQL Database
1. Log into your hosting control panel (cPanel, Plesk, etc.)
2. Go to MySQL Databases
3. Create a new database (e.g., `yourusername_library`)
4. Create a database user with full privileges
5. Note down the database credentials

### Step 2: Import Database Schema
1. Go to phpMyAdmin in your hosting control panel
2. Select your database
3. Click "Import" tab
4. Upload `database/library_mysql.sql`
5. Click "Go" to execute

### Step 3: Verify Database
Check that these tables were created:
- `users` (with default admin account)
- `books` (with 50 sample books)
- `borrowings` (empty initially)
- `categories` (with book categories)

## ⚙️ Configuration

### Step 1: Update Configuration
1. Rename `config_production.php` to `config.php`
2. Edit `config.php` with your database credentials:

```php
define('DB_HOST', 'localhost'); // Usually localhost
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
```

### Step 2: Create .htaccess File
Create `.htaccess` in your root directory:

```apache
# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Hide sensitive files
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "functions.php">
    Order allow,deny
    Deny from all
</Files>

# Redirect to HTTPS (if SSL is available)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Pretty URLs (optional)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)/?$ index.php?page=$1 [L,QSA]

# Prevent directory browsing
Options -Indexes

# Set default timezone
php_value date.timezone "Asia/Manila"

# Increase upload limits (if needed)
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

## 📤 File Upload Process

### Method 1: FTP/SFTP Upload
1. Connect to your hosting via FTP client (FileZilla, WinSCP)
2. Upload all files to your domain's public folder
3. Set proper file permissions (644 for files, 755 for folders)
4. Test the website

### Method 2: File Manager Upload
1. Use your hosting control panel's File Manager
2. Upload files directly through the web interface
3. Extract if uploaded as ZIP
4. Set proper permissions

### Method 3: Git Deployment (Advanced)
1. Set up Git repository on your hosting
2. Clone from GitHub
3. Set up automatic deployment hooks

## 🔧 Post-Deployment Configuration

### Step 1: Test Database Connection
1. Visit your website
2. Check if it redirects to login page
3. Try logging in with admin credentials:
   - Username: `admin`
   - Password: `password`

### Step 2: Change Default Passwords
1. Log in as admin
2. Go to Users management
3. Change the default admin password
4. Create additional admin accounts if needed

### Step 3: Configure Email (Optional)
If your hosting supports email:
1. Update `ADMIN_EMAIL` in config.php
2. Set up SMTP if needed for notifications

### Step 4: SSL Certificate
1. Install SSL certificate through your hosting provider
2. Update `SITE_URL` to use `https://`
3. Test secure connection

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Error
- Check database credentials in `config.php`
- Verify database server is running
- Check if database user has proper privileges

#### Permission Denied Errors
- Set file permissions: `chmod 644` for files
- Set folder permissions: `chmod 755` for directories
- Check if web server can read files

#### Page Not Found (404)
- Check if `.htaccess` is uploaded
- Verify mod_rewrite is enabled
- Check file paths and names

#### Blank White Page
- Enable error reporting temporarily
- Check PHP error logs
- Verify all required PHP extensions are installed

### Performance Optimization

#### Enable Caching
Add to `.htaccess`:
```apache
# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
</IfModule>
```

#### Compress Files
```apache
# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

## 📱 GitHub Backup Setup

### Step 1: Initialize Git Repository
```bash
cd /path/to/your/project
git init
git add .
git commit -m "Initial commit: Library Management System"
```

### Step 2: Create GitHub Repository
1. Go to GitHub.com
2. Click "New Repository"
3. Name it `library-management-system`
4. Don't initialize with README (you already have one)
5. Copy the repository URL

### Step 3: Connect and Push
```bash
git remote add origin https://github.com/yourusername/library-management-system.git
git branch -M main
git push -u origin main
```

### Step 4: Set Up Automatic Backups
Create a script for regular backups:

```bash
#!/bin/bash
# backup.sh - Run this script regularly to backup your code

cd /path/to/your/project
git add .
git commit -m "Automated backup - $(date)"
git push origin main
```

### Step 5: Database Backup
Create a script to backup your database:

```bash
#!/bin/bash
# db_backup.sh - Backup database

mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

## 🔒 Security Considerations

### Essential Security Measures
1. **Change Default Passwords**: Never use default credentials in production
2. **Regular Updates**: Keep PHP and database updated
3. **File Permissions**: Set restrictive permissions (644/755)
4. **SSL Certificate**: Always use HTTPS in production
5. **Database Security**: Use strong database passwords
6. **Backup Strategy**: Regular automated backups
7. **Error Logging**: Monitor error logs regularly

### Additional Security
1. **Firewall**: Configure server firewall
2. **Monitoring**: Set up uptime monitoring
3. **Regular Audits**: Review user accounts and permissions
4. **Input Validation**: Already implemented in the code
5. **SQL Injection Protection**: Using prepared statements

## 📊 Monitoring and Maintenance

### Regular Tasks
- [ ] Monitor disk space usage
- [ ] Check error logs weekly
- [ ] Backup database monthly
- [ ] Update admin passwords quarterly
- [ ] Review user accounts monthly
- [ ] Monitor overdue books daily

### Performance Monitoring
- [ ] Page load times
- [ ] Database query performance
- [ ] Server resource usage
- [ ] User activity logs

## 🆘 Support and Maintenance

### Getting Help
1. Check the troubleshooting section
2. Review PHP and MySQL error logs
3. Contact your hosting provider for server issues
4. Refer to the main README.md for feature documentation

### Maintenance Schedule
- **Daily**: Check overdue books, monitor system
- **Weekly**: Review error logs, check backups
- **Monthly**: Update passwords, review users
- **Quarterly**: Security audit, performance review

## 📞 Contact Information

For technical support or questions about deployment:
- Check the main README.md file
- Review the code comments
- Test in a staging environment first

---

**Important**: Always test the deployment in a staging environment before going live. Keep backups of both files and database before making any changes.

