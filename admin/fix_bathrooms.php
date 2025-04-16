<?php
require_once 'config.php';

try {
    // Önce bathrooms sütununun var olup olmadığını kontrol et
    $check_query = "SHOW COLUMNS FROM properties LIKE 'bathrooms'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows > 0) {
        // bathrooms sütunu varsa, verileri bathroom_count'a taşı
        $conn->query("UPDATE properties SET bathroom_count = bathrooms WHERE bathroom_count IS NULL");
        
        // Eski sütunu sil
        $conn->query("ALTER TABLE properties DROP COLUMN bathrooms");
        echo "Bathrooms sütunu başarıyla bathroom_count'a dönüştürüldü.";
    } else {
        echo "Bathrooms sütunu zaten mevcut değil.";
    }
    
    // bathroom_count sütununun varlığını kontrol et, yoksa oluştur
    $check_bathroom_count = "SHOW COLUMNS FROM properties LIKE 'bathroom_count'";
    $result = $conn->query($check_bathroom_count);
    
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE properties ADD COLUMN bathroom_count INT DEFAULT NULL");
        echo "bathroom_count sütunu oluşturuldu.";
    }

} catch(Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}

$conn->close();
?> 