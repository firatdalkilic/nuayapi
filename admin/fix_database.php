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

    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // Drop foreign key constraints first
    $sql = "SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_NAME = 'properties' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND TABLE_SCHEMA = DATABASE()";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $constraint = $row['CONSTRAINT_NAME'];
            $conn->query("ALTER TABLE properties DROP FOREIGN KEY " . $constraint);
        }
    }

    // Create backup table
    $sql = "CREATE TABLE properties_backup LIKE properties";
    if (!$conn->query($sql)) {
        die("Hata oluştu: " . $conn->error);
    }

    $sql = "INSERT INTO properties_backup SELECT * FROM properties";
    if (!$conn->query($sql)) {
        die("Hata oluştu: " . $conn->error);
    }

    // Drop existing table
    $sql = "DROP TABLE properties";
    if (!$conn->query($sql)) {
        die("Hata oluştu: " . $conn->error);
    }

    // Create new table
    $sql = "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2),
        location VARCHAR(255),
        property_type VARCHAR(50),
        status VARCHAR(50),
        room_count INT,
        living_room INT,
        gross_sqm DECIMAL(10,2),
        net_sqm DECIMAL(10,2),
        building_age INT,
        floor_location INT,
        total_floors INT,
        heating VARCHAR(50),
        bathroom_count INT,
        balcony BOOLEAN,
        furnished BOOLEAN,
        building_complex BOOLEAN,
        using_status VARCHAR(50),
        dues DECIMAL(10,2),
        swap BOOLEAN,
        front VARCHAR(50),
        rental_income DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        agent_id INT,
        video_call BOOLEAN DEFAULT 0,
        parking BOOLEAN DEFAULT 0,
        building_complex_name VARCHAR(255),
        CONSTRAINT fk_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if (!$conn->query($sql)) {
        die("Hata oluştu: " . $conn->error);
    }

    // Restore data
    $sql = "INSERT INTO properties SELECT * FROM properties_backup";
    if (!$conn->query($sql)) {
        die("Hata oluştu: " . $conn->error);
    }

    // Drop backup table
    $sql = "DROP TABLE properties_backup";
    if (!$conn->query($sql)) {
        die("Hata oluştu: " . $conn->error);
    }

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");

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