-- Aviz Academy Database Setup
-- Author: Avinash Reddy Thipparthi
-- This script sets up the database schema for the capstone project

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS capstone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE capstone;

-- Create customers table with improved structure
CREATE TABLE IF NOT EXISTS customers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    PRIMARY KEY (id),
    INDEX idx_email (email),
    INDEX idx_name (name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user sessions table for better session management
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    customer_id INT(11) NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_customer_id (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create course progress table for tracking learning
CREATE TABLE IF NOT EXISTS course_progress (
    id INT(11) NOT NULL AUTO_INCREMENT,
    customer_id INT(11) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_course (customer_id, course_name),
    INDEX idx_customer_id (customer_id),
    INDEX idx_course_name (course_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create application user with limited privileges
CREATE USER IF NOT EXISTS 'capstoneuser'@'%' IDENTIFIED BY 'Avinash12345';

-- Grant specific privileges (principle of least privilege)
GRANT SELECT, INSERT, UPDATE ON capstone.customers TO 'capstoneuser'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON capstone.user_sessions TO 'capstoneuser'@'%';
GRANT SELECT, INSERT, UPDATE ON capstone.course_progress TO 'capstoneuser'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Insert sample data for testing
INSERT IGNORE INTO customers (name, gender, email, phone) VALUES
('urs amigo', 'male', 'amigo@avizacademy.com', '+91-9876543210'),
('Demo User', 'other', 'ursamigo@avizacademy.com', '+91-9876543211');

-- Insert sample course progress
INSERT IGNORE INTO course_progress (customer_id, course_name, progress_percentage) VALUES
(1, 'AWS Fundamentals', 85.50),
(1, 'DevOps & CI/CD', 92.00),
(2, 'AWS Fundamentals', 25.00);

-- Show table structure for verification
DESCRIBE customers;
DESCRIBE user_sessions;
DESCRIBE course_progress;

-- Show sample data
SELECT 'Customers Table:' as Info;
SELECT * FROM customers LIMIT 5;

SELECT 'Course Progress Table:' as Info;
SELECT c.name, cp.course_name, cp.progress_percentage, cp.completed 
FROM customers c 
JOIN course_progress cp ON c.id = cp.customer_id 
LIMIT 5;