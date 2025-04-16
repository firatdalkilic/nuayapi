<?php
require_once 'config.php';

try {
    // Agents tablosunu yeniden oluştur
    $sql = "DROP TABLE IF EXISTS agents";
    $conn->query($sql);

    $sql = "CREATE TABLE agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        agent_name VARCHAR(255) NOT NULL,
        username_panel VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        about TEXT,
        image VARCHAR(255),
        sahibinden_link VARCHAR(255),
        emlakjet_link VARCHAR(255),
        facebook_link VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "Agents tablosu başarıyla yeniden oluşturuldu.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Properties tablosunu düzelt
    $alter_queries = [
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS beds INT DEFAULT 0",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS bathrooms INT DEFAULT 0",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS living_room INT DEFAULT 0",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS net_area DECIMAL(10,2) DEFAULT 0",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS gross_area DECIMAL(10,2) DEFAULT 0",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS building_age VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS floor_location VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS total_floors INT DEFAULT 0",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS heating VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS balcony VARCHAR(10)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS furnished VARCHAR(10)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS site_status VARCHAR(10)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS eligible_for_credit VARCHAR(10)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS usage_status VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS video_call_available VARCHAR(10)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS zoning_status VARCHAR(100)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS block_no VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS parcel_no VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS sheet_no VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS floor_area_ratio VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS height_limit VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS deed_status VARCHAR(50)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS video_file VARCHAR(255)",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS price_per_sqm DECIMAL(10,2) DEFAULT 0"
    ];

    foreach ($alter_queries as $query) {
        try {
            if ($conn->query($query)) {
                echo "Başarılı: " . $query . "<br>";
            } else {
                echo "Hata: " . $query . " - " . $conn->error . "<br>";
            }
        } catch (Exception $e) {
            echo "Hata: " . $query . " - " . $e->getMessage() . "<br>";
        }
    }

    // Property_images tablosunu oluştur
    $sql = "CREATE TABLE IF NOT EXISTS property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        image_name VARCHAR(255) NOT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "Property_images tablosu başarıyla oluşturuldu/güncellendi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Örnek admin kullanıcısı oluştur
    $admin_username = "admin";
    $admin_password = password_hash("admin123", PASSWORD_BCRYPT);
    $admin_name = "Admin";
    $admin_email = "admin@example.com";
    
    $sql = "INSERT INTO agents (agent_name, username_panel, password, email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $admin_name, $admin_username, $admin_password, $admin_email);
    
    if ($stmt->execute()) {
        echo "Örnek admin kullanıcısı oluşturuldu.<br>";
    } else {
        echo "Admin kullanıcısı zaten mevcut olabilir.<br>";
    }

    echo "Tüm veritabanı güncellemeleri başarıyla tamamlandı!";

} catch(Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 