<?php
require_once 'admin/config.php';

try {
    // Agents tablosuna platform alanlarını ekle
    $alter_queries = [
        "ALTER TABLE agents ADD COLUMN IF NOT EXISTS sahibinden_store VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE agents ADD COLUMN IF NOT EXISTS emlakjet_profile VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE agents ADD COLUMN IF NOT EXISTS facebook_username VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS room_count VARCHAR(50) DEFAULT NULL AFTER net_area",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS living_room_count VARCHAR(50) DEFAULT NULL AFTER room_count"
    ];

    foreach ($alter_queries as $query) {
        try {
            if ($conn->query($query)) {
                echo "Sorgu başarıyla çalıştırıldı: " . $query . "<br>";
            }
        } catch (Exception $e) {
            echo "Sorgu hatası (" . $query . "): " . $e->getMessage() . "<br>";
            error_log("SQL Error: " . $e->getMessage() . " - Query: " . $query);
        }
    }
} catch (Exception $e) {
    echo "Genel hata: " . $e->getMessage() . "<br>";
    error_log("General Error: " . $e->getMessage());
}

$conn->close();
?> 