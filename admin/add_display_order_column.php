<?php
require_once 'config.php';

try {
    // display_order kolonunu ekle
    $sql = "ALTER TABLE property_images ADD COLUMN IF NOT EXISTS display_order INT DEFAULT NULL";
    
    if ($conn->query($sql)) {
        echo "display_order kolonu başarıyla eklendi.";
        
        // Mevcut resimlere sıra numarası ver
        $update_sql = "UPDATE property_images SET display_order = id WHERE display_order IS NULL";
        if ($conn->query($update_sql)) {
            echo "<br>Mevcut resimlere sıra numarası verildi.";
        } else {
            echo "<br>Sıra numarası güncellenirken hata oluştu: " . $conn->error;
        }
    } else {
        echo "Hata oluştu: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
}

$conn->close();
?> 