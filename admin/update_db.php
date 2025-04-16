<?php
require_once 'config.php';

try {
    // Önce agent_id sütununun var olup olmadığını kontrol edelim
    $check_column = $conn->query("SHOW COLUMNS FROM properties LIKE 'agent_id'");
    if ($check_column->num_rows == 0) {
        // Sütun yoksa ekleyelim
        $sql = "ALTER TABLE properties ADD COLUMN agent_id INT";
        if ($conn->query($sql)) {
            echo "agent_id sütunu başarıyla eklendi.<br>";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "agent_id sütunu zaten mevcut.<br>";
    }

    // Önce mevcut foreign key'i kaldıralım
    $sql = "ALTER TABLE properties DROP FOREIGN KEY properties_ibfk_1";
    if ($conn->query($sql)) {
        echo "Eski foreign key başarıyla kaldırıldı.<br>";
    } else {
        echo "Foreign key zaten kaldırılmış veya mevcut değil.<br>";
    }

    // Agents tablosunu yeniden oluşturalım
    $sql = "DROP TABLE IF EXISTS agents";
    if ($conn->query($sql)) {
        echo "Eski agents tablosu silindi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Yeni agents tablosunu oluşturalım
    $sql = "CREATE TABLE agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        username_panel VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) {
        echo "Yeni agents tablosu başarıyla oluşturuldu.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Properties tablosuna foreign key ekleyelim
    $sql = "ALTER TABLE properties ADD FOREIGN KEY (agent_id) REFERENCES agents(id)";
    if ($conn->query($sql)) {
        echo "Foreign key başarıyla eklendi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Yeni sütunları ekle
    $alter_queries = [
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS beds INT DEFAULT 0",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS bathroom_count INT DEFAULT 0",
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

    echo "Tüm işlemler başarıyla tamamlandı!";
} catch(Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 