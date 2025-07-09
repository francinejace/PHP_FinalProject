<a name="readme-top">

<br />

<div align="center">
  <a href="https://github.com/francinejace/PHP_FinalProject">
    <img src="././assets/img/mochi-mochi.png" alt="mochi-mochi" width="130" height="100">
  </a>
  <h3 align="center">Library Management System</h3>
</div>

<div align="center">
  A final requirement for <strong>CCS0043 – Application Development and Emerging Technologies</strong>. This PHP project demonstrates a simple yet secure library management system with role-based access, dynamic UI, and MySQL integration.
</div>

<br />

![](https://visit-counter.vercel.app/counter.png?page=francinejace/PHP_FinalProject)

---

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#overview">Overview</a>
      <ol>
        <li><a href="#key-components">Key Components</a></li>
        <li><a href="#technology">Technology</a></li>
      </ol>
    </li>
    <li><a href="#rules-practices-and-standards">Rules, Practices and Standards</a></li>
    <li><a href="#resources">Resources</a></li>
  </ol>
</details>

---

## 📖 Overview

This web application allows users to manage library activities like book searching, borrowing, and returning. Admin and student dashboards are built with PHP and styled for accessibility and usability.

### 🔑 Key Components

- Admin & Student Dashboards
- User Login and Registration
- Book Management (via MySQL)
- Modular Includes (Header, Footer, Navbar)
- Demo login/register files
- SQLite and MySQL compatibility (SQL scripts provided)

### ⚙️ Technology

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML](https://img.shields.io/badge/HTML-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JS](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

---

## ✅ Rules, Practices and Standards

1. `config.php` is used for local, `config_production.php` for deployment.
2. Pages are categorized by role: `admin/`, `student/`, `user/`.
3. Reusable layouts in `includes/`.
4. Only `index.php` is at root for entry point.
5. SQL files: 
   - `library.sql` for MySQL 
   - `init_sqlite.sql` for optional SQLite testing
6. Use `.htaccess` to enable clean URLs and security headers.
7. File naming follows camelCase or snake_case.

### 📁 File Structure

PHP_FINALPROJECT <br>
├─ admin <br>
│ └─ dashboard.php <br>
├─ assets <br>
│ ├─ script.js <br>
│ └─ style.css <br>
├─ database <br>
│ ├─ init_sqlite.sql <br>
│ ├─ library_mysql.sql <br>
│ ├─ library.db <br>
│ └─ library.sql <br>
├─ includes <br>
│ ├─ footer.php <br>
│ ├─ header.php <br>
│ └─ navbar.php <br>
├─ student <br>
│ └─ dashboard.php <br>
├─ user <br>
│ ├─ login.php <br>
│ ├─ login_demo.php <br>
│ ├─ logout.php <br>
│ ├─ register.php <br>
│ └─ register_demo.php <br>
├─ .htaccess <br>
├─ config.php <br>
├─ config_production.php <br>
├─ DEPLOYMENT.md <br>
├─ functions.php <br>
├─ index.php <br>
├─ test.php <br>
└─ README.md <br>


---

## 📚 Resources

| Title | Purpose | Link |
|-------|---------|------|
| PHP Manual | Language reference | https://www.php.net |
| MySQL Docs | DB reference | https://dev.mysql.com/doc/ |
| InfinityFree | Hosting | https://infinityfree.net |
| W3Schools | Web dev help | https://www.w3schools.com |
| Color Hunt | UI palette inspiration | https://colorhunt.co |
