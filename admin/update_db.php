<?php
require_once 'config.php';

try {
    // price_per_sqm sütununu güncelle
    $sql = "ALTER TABLE properties MODIFY COLUMN price_per_sqm DECIMAL(12,2)";
    if ($conn->query($sql)) {
        echo "price_per_sqm sütunu başarıyla güncellendi.";
    } else {
        echo "Hata: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 