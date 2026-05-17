-- Database Setup Script for Auth App
-- Run this script in your MySQL client to set up the database

CREATE DATABASE IF NOT EXISTS auth_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE auth_app;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample user (password: Password123!)
-- INSERT INTO users (name, email, password) VALUES ('John Doe', 'john@example.com', '$2y$12$...');
