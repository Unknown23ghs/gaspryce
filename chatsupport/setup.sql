-- Create database if not exists
CREATE DATABASE IF NOT EXISTS pryce_support;
USE pryce_support;

-- Create support_messages table
CREATE TABLE IF NOT EXISTS support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_admin BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 