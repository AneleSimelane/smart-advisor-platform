-- SMART ADVISOR AND MONITORING SYSTEM FOR FARMERS IN ESWATINI
-- Database Schema

CREATE DATABASE IF NOT EXISTS smart_advisor;
USE smart_advisor;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('farmer', 'officer', 'admin') DEFAULT 'farmer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Regions in Eswatini
CREATE TABLE regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Chiefdoms/Communities
CREATE TABLE chiefdoms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
);

-- Farms
CREATE TABLE farms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    chiefdom_id INT,
    size_hectares DECIMAL(10, 2),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chiefdom_id) REFERENCES chiefdoms(id) ON DELETE SET NULL
);

-- Crop Types
CREATE TABLE crop_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    scientific_name VARCHAR(100),
    typical_duration_days INT,
    description TEXT
);

-- Crops Grown on Farms
CREATE TABLE crops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    crop_type_id INT NOT NULL,
    status ENUM('active', 'harvested', 'failed') DEFAULT 'active',
    planted_date DATE NOT NULL,
    expected_harvest_date DATE,
    area_size DECIMAL(10, 2), -- Size within the farm dedicated to this crop
    farming_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_type_id) REFERENCES crop_types(id) ON DELETE CASCADE
);

-- Crop Activities (Irrigation, Fertilizer, etc.)
CREATE TABLE crop_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    activity_type ENUM('irrigation', 'fertilizer', 'pesticide', 'harvest', 'weeding', 'other') NOT NULL,
    description TEXT,
    activity_date DATE NOT NULL,
    cost DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE
);

-- AI Disease Diagnoses
CREATE TABLE disease_diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT,
    image_path VARCHAR(255) NOT NULL,
    predicted_disease VARCHAR(100),
    confidence_score DECIMAL(5, 2),
    status ENUM('pending', 'analyzed', 'reviewed') DEFAULT 'pending',
    symptoms TEXT,
    recommended_treatment TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE SET NULL
);

-- Recommendations from Advisory Engine
CREATE TABLE recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_id INT,
    issue_type VARCHAR(100),
    recommendation_text TEXT NOT NULL,
    reason TEXT,
    urgency ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    is_read BOOLEAN DEFAULT FALSE,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE SET NULL
);

-- Financial Records (Expenses & Income)
CREATE TABLE financial_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    crop_id INT, -- Optional, if expense is tied to a specific crop
    type ENUM('expense', 'income') NOT NULL,
    category VARCHAR(50) NOT NULL, -- e.g., Seeds, Fertilizer, Labour, Sales
    amount_szl DECIMAL(12, 2) NOT NULL,
    description TEXT,
    record_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE SET NULL
);

-- Agricultural Services (for Maps)
CREATE TABLE agricultural_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('veterinary', 'agro_dealer', 'office', 'supplier') NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- News and Announcements
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    publish_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sensor Devices (Manual/Simulated for now)
CREATE TABLE sensor_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    device_identifier VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
);

-- Sensor Readings
CREATE TABLE sensor_readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    temperature DECIMAL(5, 2),
    humidity DECIMAL(5, 2),
    soil_moisture DECIMAL(5, 2),
    light_intensity DECIMAL(8, 2),
    reading_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES sensor_devices(id) ON DELETE CASCADE
);

-- Farmer Marketplace Listings Table
CREATE TABLE IF NOT EXISTS marketplace_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    category ENUM('vegetables', 'fruits', 'grains', 'livestock', 'seeds_inputs', 'equipment', 'other') DEFAULT 'vegetables',
    price_szl DECIMAL(10, 2) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(30) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'sold') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Community Social Feed & Ads Table
CREATE TABLE IF NOT EXISTS community_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_type ENUM('general', 'question', 'tip', 'ad') DEFAULT 'general',
    content TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Post Likes Table
CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Post Comments Table
CREATE TABLE IF NOT EXISTS post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Initial Eswatini Data

INSERT INTO regions (name) VALUES 
('Hhohho'), ('Manzini'), ('Shiselweni'), ('Lubombo');

INSERT INTO crop_types (name, scientific_name, typical_duration_days) VALUES 
('Maize', 'Zea mays', 120),
('Beans', 'Phaseolus vulgaris', 90),
('Tomatoes', 'Solanum lycopersicum', 100),
('Cabbage', 'Brassica oleracea', 80),
('Carrots', 'Daucus carota', 75),
('Spinach', 'Spinacia oleracea', 45),
('Potatoes', 'Solanum tuberosum', 110),
('Onions', 'Allium cepa', 150);

-- Default Admin Account (password is 'admin123')
INSERT INTO users (name, email, phone, password_hash, role) VALUES 
('System Admin', 'admin@smartadvisor.sz', '26870000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
