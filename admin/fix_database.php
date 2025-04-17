<?php
require_once 'config.php';

try {
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // Tabloları sil
    $tables = ['images', 'property_images', 'properties', 'agents'];
    foreach ($tables as $table) {
        $sql = "DROP TABLE IF EXISTS $table";
        $conn->query($sql);
        echo "$table tablosu silindi (varsa).<br>";
    }

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

    // Properties tablosunu oluştur
    $sql = "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2),
        location VARCHAR(255),
        neighborhood VARCHAR(255),
        property_type VARCHAR(50),
        status VARCHAR(50),
        net_area DECIMAL(10,2) DEFAULT 0,
        room_count INT DEFAULT 0,
        bathroom_count INT DEFAULT 0,
        balcony ENUM('Var', 'Yok') DEFAULT 'Yok',
        parking ENUM('Var', 'Yok') DEFAULT 'Yok',
        site ENUM('Evet', 'Hayır') DEFAULT 'Hayır',
        floor_location VARCHAR(50) DEFAULT NULL,
        total_floors INT DEFAULT 0,
        gross_area DECIMAL(10,2) DEFAULT 0,
        living_room INT DEFAULT 0,
        building_age VARCHAR(20) DEFAULT NULL,
        eligible_for_credit ENUM('Evet', 'Hayır') DEFAULT 'Hayır',
        heating VARCHAR(50) DEFAULT NULL,
        furnished ENUM('Evet', 'Hayır') DEFAULT 'Hayır',
        video_call_available ENUM('Evet', 'Hayır') DEFAULT 'Hayır',
        usage_status ENUM('Boş', 'Kiracılı', 'Mülk Sahibi') DEFAULT 'Boş',
        agent_id INT,
        agent_name VARCHAR(255),
        agent_phone VARCHAR(20),
        agent_email VARCHAR(255),
        video_file VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        echo "Properties tablosu başarıyla oluşturuldu.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // site_status sütununu ekle
    $sql = "ALTER TABLE properties ADD COLUMN site_status VARCHAR(50) DEFAULT NULL AFTER status";
    try {
        if ($conn->query($sql) === TRUE) {
            echo "site_status sütunu başarıyla eklendi.<br>";
        }
    } catch (Exception $e) {
        // Sütun zaten varsa hata mesajını görmezden gel
        if (!strpos($e->getMessage(), "Duplicate column name")) {
            echo "Hata: " . $e->getMessage() . "<br>";
        }
    }

    // Property_images tablosunu oluştur
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
        echo "Admin kullanıcısı oluşturulurken hata: " . $stmt->error . "<br>";
    }

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");

    echo "Tüm veritabanı güncellemeleri başarıyla tamamlandı!";

} catch(Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 