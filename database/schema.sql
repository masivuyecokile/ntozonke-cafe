CREATE DATABASE IF NOT EXISTS ntozonke_cafe;
USE ntozonke_cafe;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin','cashier') DEFAULT 'admin',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pcs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_name VARCHAR(50) NOT NULL,
    ip_address VARCHAR(50) NULL,
    mac_address VARCHAR(100) NULL,
    status ENUM('locked','active','offline','maintenance','ending_soon') DEFAULT 'locked',
    last_heartbeat DATETIME NULL,
    sync_status ENUM('pending','synced','failed') DEFAULT 'pending',
    synced_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    customer_name VARCHAR(100) DEFAULT 'Walk-in Customer',
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    actual_end_time DATETIME NULL,
    minutes_purchased INT NOT NULL,
    extended_minutes INT DEFAULT 0,
    rate_per_minute DECIMAL(10,2) DEFAULT 0.50,
    amount_due DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active','ended','cancelled') DEFAULT 'active',
    created_by INT NULL,
    sync_status ENUM('pending','synced','failed') DEFAULT 'pending',
    synced_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pc_id) REFERENCES pcs(id)
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NULL,
    sale_type ENUM('internet','printing','service','other') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','card','eft','free') DEFAULT 'cash',
    created_by INT NULL,
    sync_status ENUM('pending','synced','failed') DEFAULT 'pending',
    synced_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE print_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NULL,
    session_id INT NULL,
    source ENUM('pc_client','admin_direct','manual') DEFAULT 'pc_client',
    document_name VARCHAR(255),
    pages INT DEFAULT 0,
    copies INT DEFAULT 1,
    print_type ENUM('black_white','colour') DEFAULT 'black_white',
    amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending','approved','printed','rejected','held') DEFAULT 'pending',
    approved_by INT NULL,
    printed_at DATETIME NULL,
    sync_status ENUM('pending','synced','failed') DEFAULT 'pending',
    synced_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(50) NULL,
    sync_status ENUM('pending','synced','failed') DEFAULT 'pending',
    synced_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT
);