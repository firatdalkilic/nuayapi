<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'config.php';
    
    if (!isset($conn) || !$conn) {
        throw new Exception("Veritabanı bağlantısı kurulamadı.");
    }

    // display_order sütununu ekle
    $sql = "ALTER TABLE property_images ADD COLUMN display_order INT DEFAULT 0";
    
    if ($conn->query($sql)) {
        echo "display_order sütunu başarıyla eklendi!";
        
        // Mevcut resimlere sıra numarası ver
        $update_sql = "UPDATE property_images SET display_order = id WHERE display_order = 0";
        if ($conn->query($update_sql)) {
            echo "<br>Mevcut resimlere sıra numarası verildi!";
        } else {
            throw new Exception("Sıra numarası güncellenirken hata oluştu: " . $conn->error);
        }
    } else {
        throw new Exception("Sütun eklenirken hata oluştu: " . $conn->error);
    }

} catch (Exception $e) {
    echo "Bir hata oluştu: " . htmlspecialchars($e->getMessage());
}
?> 