<?php
require_once '../admin/config.php';

// Agents tablosuna yeni alanlar ekle
$sql = "ALTER TABLE agents 
        ADD COLUMN sahibinden_store VARCHAR(255) NULL,
        ADD COLUMN emlakjet_profile VARCHAR(255) NULL,
        ADD COLUMN facebook_username VARCHAR(255) NULL";

if ($conn->query($sql) === TRUE) {
    echo "Agents tablosuna yeni alanlar eklendi.\n";
} else {
    echo "Hata: " . $conn->error . "\n";
}

$conn->close();
?> 