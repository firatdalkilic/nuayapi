<?php
require_once '../admin/config.php';

// Agents tablosuna platform alanlarını ekle
$alter_agents_table = "ALTER TABLE agents 
    ADD COLUMN sahibinden_store VARCHAR(255) DEFAULT NULL,
    ADD COLUMN emlakjet_profile VARCHAR(255) DEFAULT NULL,
    ADD COLUMN facebook_username VARCHAR(255) DEFAULT NULL";

try {
    if ($conn->query($alter_agents_table)) {
        echo "Agents tablosuna platform alanları başarıyla eklendi.<br>";
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "<br>";
}

$conn->close();
?> 