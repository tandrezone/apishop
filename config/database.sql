-- Database Schema for API Shop

CREATE DATABASE IF NOT EXISTS apishop;
USE apishop;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    items JSON NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample admin user (password: admin123)
INSERT INTO users (email, password_hash, name, role, created_at) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin', NOW());

-- Insert sample regular user (password: user123)
INSERT INTO users (email, password_hash, name, role, created_at) VALUES
('user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Regular User', 'user', NOW());

-- Insert sample products
INSERT INTO products (name, description, price, stock, created_at) VALUES
('Laptop', 'High-performance laptop with 16GB RAM', 999.99, 50, NOW()),
('Smartphone', 'Latest smartphone with 128GB storage', 699.99, 100, NOW()),
('Headphones', 'Wireless noise-cancelling headphones', 199.99, 75, NOW()),
('Keyboard', 'Mechanical gaming keyboard', 129.99, 30, NOW()),
('Mouse', 'Ergonomic wireless mouse', 49.99, 150, NOW());

-- Insert sample orders
INSERT INTO orders (user_id, total_amount, status, items, created_at) VALUES
(2, 1199.98, 'completed', '[{"product_id": 1, "quantity": 1, "price": 999.99}, {"product_id": 5, "quantity": 4, "price": 49.99}]', NOW()),
(2, 699.99, 'pending', '[{"product_id": 2, "quantity": 1, "price": 699.99}]', NOW());
