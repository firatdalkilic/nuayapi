<?php
require_once 'config.php';

try {
    // Önce kolonun var olup olmadığını kontrol et
    $check_sql = "SHOW COLUMNS FROM property_images LIKE 'display_order'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        // Kolon yoksa ekle
        $sql = "ALTER TABLE property_images ADD COLUMN display_order INT DEFAULT NULL";
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
    } else {
        echo "display_order kolonu zaten mevcut.";
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
}

$conn->close();
?> 