<?php
require_once 'config.php';

try {
    // Önce images tablosunu sil
    $sql = "DROP TABLE IF EXISTS images";
    $conn->query($sql);
    
    // Sonra property_images tablosunu sil
    $sql = "DROP TABLE IF EXISTS property_images";
    $conn->query($sql);
    
    // Sonra properties tablosunu sil
    $sql = "DROP TABLE IF EXISTS properties";
    $conn->query($sql);
    
    // En son agents tablosunu sil
    $sql = "DROP TABLE IF EXISTS agents";
    $conn->query($sql);

    // Şimdi tabloları doğru sırayla oluştur
    
    // Önce agents tablosunu oluştur
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
        echo "Agents tablosu başarıyla oluşturuldu.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Sonra properties tablosunu oluştur
    $sql = "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(15,2),
        location VARCHAR(255),
        neighborhood VARCHAR(255),
        status VARCHAR(50),
        property_type VARCHAR(50),
        room_count INT DEFAULT 0,
        bathrooms INT DEFAULT 0,
        living_room INT DEFAULT 0,
        net_area DECIMAL(10,2) DEFAULT 0,
        gross_area DECIMAL(10,2) DEFAULT 0,
        building_age VARCHAR(50),
        floor_location VARCHAR(50),
        total_floors INT DEFAULT 0,
        heating VARCHAR(50),
        balcony VARCHAR(10),
        furnished VARCHAR(10),
        site_status VARCHAR(10),
        eligible_for_credit VARCHAR(10),
        usage_status VARCHAR(50),
        video_call_available VARCHAR(10),
        zoning_status VARCHAR(100),
        block_no VARCHAR(50),
        parcel_no VARCHAR(50),
        sheet_no VARCHAR(50),
        floor_area_ratio VARCHAR(50),
        height_limit VARCHAR(50),
        deed_status VARCHAR(50),
        video_file VARCHAR(255),
        price_per_sqm DECIMAL(10,2) DEFAULT 0,
        agent_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "Properties tablosu başarıyla oluşturuldu.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // En son property_images tablosunu oluştur
    $sql = "CREATE TABLE property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        image_name VARCHAR(255) NOT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "Property_images tablosu başarıyla oluşturuldu.<br>";
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