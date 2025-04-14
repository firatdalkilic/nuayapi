<?php
require_once 'config.php';

try {
    // Eksik kolonları ekle
    $alter_queries = [
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS location VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS video_file VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS parking VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS net_area DECIMAL(10,2) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS neighborhood VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS heating VARCHAR(100) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS site_name VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS beds INT DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS living_room INT DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS bathroom_count INT DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS building_age VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS floor_location VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS total_floors INT DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS balcony VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS furnished VARCHAR(50) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS site_status VARCHAR(50) DEFAULT NULL"
    ];

    foreach ($alter_queries as $query) {
        if ($conn->query($query)) {
            echo "Kolon başarıyla eklendi: " . $query . "<br>";
        } else {
            echo "Hata oluştu: " . $conn->error . "<br>";
        }
    }

    echo "<br>Tüm kolonlar başarıyla eklendi!";

} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
}

$conn->close();
?> 