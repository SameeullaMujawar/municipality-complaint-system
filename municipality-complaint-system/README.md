# Municipality Public Complaint Tracking System

A full-featured PHP/MySQL web application for managing citizen complaints.

---

## Requirements

- PHP 7.4+ (PDO + PDO_MySQL extension enabled)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` (XAMPP recommended)

---

## Setup Instructions

### 1. Place the project
Copy the `municipality-complaint-system` folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\municipality-complaint-system\
```

### 2. Create the database
- Open **phpMyAdmin** → http://localhost/phpmyadmin
- Click **Import** → choose `schema.sql` → click **Go**

This creates the database, all tables, default departments, and a default admin account.

### 3. Configure database credentials (if needed)
Edit `config/db.php` if your MySQL credentials differ from the defaults:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // your MySQL password
define('DB_NAME', 'municipality_system');
```

### 4. Set uploads folder permissions
Ensure the `uploads/` folder is writable:
- On Linux/Mac: `chmod 775 uploads/`
- On Windows (XAMPP): it should be writable by default

### 5. Open in browser
Visit: http://localhost/municipality-complaint-system/

---

## Default Admin Credentials

| Field    | Value                      |
|----------|----------------------------|
| Email    | admin@municipality.gov     |
| Password | Admin@123                  |

> ⚠️ Change the admin password after first login via phpMyAdmin or by adding a profile page.

---

## Features

### Citizen
- Register & login
- Submit complaints with photo upload (JPG/PNG/GIF/WEBP, max 2 MB)
- View complaint history with status filter
- Track complaint status and department assignment

### Staff / Admin
- View all complaints with status and category filters
- Assign complaints to departments
- Update complaint status (Pending → In Progress → Resolved / Rejected)
- Add internal notes
- View reports: by status, category, department
- Admin only: manage user roles and delete users

---

## Folder Structure

```
municipality-complaint-system/
├── index.php               Landing page
├── login.php               Login
├── register.php            Register
├── logout.php              Logout
├── dashboard.php           Citizen dashboard
├── submit_complaint.php    Submit a complaint
├── view_complaints.php     View / track complaints
├── schema.sql              Database setup script
│
├── config/
│   ├── db.php              PDO database connection
│   └── session.php         Session helpers & auth guards
│
├── components/
│   ├── head.php            HTML <head> partial
│   ├── navbar.php          Navbar with flash messages
│   └── footer.php          Footer + Bootstrap JS
│
├── admin/
│   ├── admin_dashboard.php Complaint management table
│   ├── update_complaint.php Update status & assignment
│   ├── report.php          Reports overview
│   └── manage_users.php    User management (admin only)
│
├── assets/
│   ├── css/style.css       Custom styles
│   └── js/script.js        Image preview, confirm dialogs
│
└── uploads/                Uploaded complaint images (auto-created)
```

---

## Security Notes

- All database queries use **PDO prepared statements** (no SQL injection)
- Passwords hashed with **bcrypt** via `password_hash()`
- File uploads validated by **MIME type + size**
- Role-based access control on all admin pages
- Output escaped with `htmlspecialchars()` throughout

---

## Tech Stack

| Layer    | Technology              |
|----------|-------------------------|
| Backend  | PHP 7.4+ (PDO)          |
| Frontend | Bootstrap 5.3, Bootstrap Icons |
| Database | MySQL / MariaDB         |
| Server   | Apache (XAMPP)          |
