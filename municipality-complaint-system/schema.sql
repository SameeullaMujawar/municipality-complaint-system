-- Municipality Complaint Tracking System
-- Run this in phpMyAdmin or MySQL CLI to set up the database

CREATE DATABASE IF NOT EXISTS municipality_system CHARACTER SET utf8 COLLATE utf8_general_ci;
USE municipality_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('citizen','staff','admin') NOT NULL DEFAULT 'citizen',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    department_id   INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
);

-- Complaints table
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    category        ENUM('Garbage','Road Damage','Water Leakage','Street Light','Other') NOT NULL,
    description     TEXT NOT NULL,
    location        VARCHAR(255) NOT NULL,
    image_path      VARCHAR(255) DEFAULT NULL,
    status          ENUM('Pending','In Progress','Resolved','Rejected') NOT NULL DEFAULT 'Pending',
    date_submitted  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Complaint assignments
CREATE TABLE IF NOT EXISTS complaint_assignments (
    assignment_id   INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id    INT NOT NULL,
    department_id   INT NOT NULL,
    assigned_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes           TEXT DEFAULT NULL,
    FOREIGN KEY (complaint_id)  REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
);

-- Seed departments
INSERT INTO departments (department_name) VALUES
    ('Sanitation & Garbage'),
    ('Roads & Infrastructure'),
    ('Water Supply'),
    ('Electrical & Street Lighting'),
    ('General Services');

-- Seed default admin account (password: Admin@123)
INSERT INTO users (name, email, password, role) VALUES
    ('Admin', 'admin@municipality.gov', '$2b$12$KDFxEj8vwbFvdHrw5pQYAuXJ07Pryptln232fYCxTMCs1/Qz6M1DW', 'admin');

-- Note: The hashed password above is bcrypt for 'Admin@123'
-- Change it immediately after first login.
