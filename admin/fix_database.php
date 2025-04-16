<?php
require_once 'config.php';

try {
    // Agents tablosunu düzelt
    $sql = "ALTER TABLE agents 
            CHANGE COLUMN name agent_name VARCHAR(255) NOT NULL,
            MODIFY COLUMN email VARCHAR(255) NOT NULL,
            MODIFY COLUMN phone VARCHAR(20),
            ADD COLUMN IF NOT EXISTS about TEXT,
            ADD COLUMN IF NOT EXISTS image VARCHAR(255),
            ADD COLUMN IF NOT EXISTS sahibinden_link VARCHAR(255),
            ADD COLUMN IF NOT EXISTS emlakjet_link VARCHAR(255),
            ADD COLUMN IF NOT EXISTS facebook_link VARCHAR(255)";
    
    if ($conn->query($sql)) {
        echo "Agents tablosu başarıyla güncellendi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Properties tablosunu düzelt
    $sql = "ALTER TABLE properties 
            ADD COLUMN IF NOT EXISTS beds INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS bathrooms INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS living_room INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS net_area DECIMAL(10,2) DEFAULT 0,
            ADD COLUMN IF NOT EXISTS gross_area DECIMAL(10,2) DEFAULT 0,
            ADD COLUMN IF NOT EXISTS building_age VARCHAR(50),
            ADD COLUMN IF NOT EXISTS floor_location VARCHAR(50),
            ADD COLUMN IF NOT EXISTS total_floors INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS heating VARCHAR(50),
            ADD COLUMN IF NOT EXISTS balcony VARCHAR(10),
            ADD COLUMN IF NOT EXISTS furnished VARCHAR(10),
            ADD COLUMN IF NOT EXISTS site_status VARCHAR(10),
            ADD COLUMN IF NOT EXISTS eligible_for_credit VARCHAR(10),
            ADD COLUMN IF NOT EXISTS usage_status VARCHAR(50),
            ADD COLUMN IF NOT EXISTS video_call_available VARCHAR(10),
            ADD COLUMN IF NOT EXISTS zoning_status VARCHAR(100),
            ADD COLUMN IF NOT EXISTS block_no VARCHAR(50),
            ADD COLUMN IF NOT EXISTS parcel_no VARCHAR(50),
            ADD COLUMN IF NOT EXISTS sheet_no VARCHAR(50),
            ADD COLUMN IF NOT EXISTS floor_area_ratio VARCHAR(50),
            ADD COLUMN IF NOT EXISTS height_limit VARCHAR(50),
            ADD COLUMN IF NOT EXISTS deed_status VARCHAR(50),
            ADD COLUMN IF NOT EXISTS video_file VARCHAR(255),
            ADD COLUMN IF NOT EXISTS price_per_sqm DECIMAL(10,2) DEFAULT 0";
    
    if ($conn->query($sql)) {
        echo "Properties tablosu başarıyla güncellendi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Property_images tablosunu oluştur
    $sql = "CREATE TABLE IF NOT EXISTS property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        image_name VARCHAR(255) NOT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql)) {
        echo "Property_images tablosu başarıyla oluşturuldu/güncellendi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    echo "Tüm veritabanı güncellemeleri başarıyla tamamlandı!";

} catch(Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 