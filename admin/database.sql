-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS emlak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE emlak;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- İlanlar tablosu
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    room_count INT,
    location VARCHAR(255) NOT NULL,
    neighborhood VARCHAR(255),
    description TEXT NOT NULL,
    property_type VARCHAR(50),
    gross_area INT,
    net_area DECIMAL(10,2),
    floor_location TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    total_floors INT,
    heating VARCHAR(50),
    bathroom_count INT,
    balcony VARCHAR(10),
    furnished VARCHAR(10),
    site_status VARCHAR(10),
    eligible_for_credit VARCHAR(10),
    building_age VARCHAR(50),
    living_room INT,
    parking VARCHAR(10),
    usage_status VARCHAR(50),
    video_call_available VARCHAR(10),
    video_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan admin kullanıcısı oluştur (şifre: admin123)
INSERT INTO users (username, password) VALUES ('admin', MD5('admin123')); 