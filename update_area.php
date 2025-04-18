<?php
require_once 'admin/config.php';

try {
    // İş yeri ilanlarını güncelle
    $sql = "UPDATE properties 
            SET square_meters = net_area 
            WHERE property_type = 'İş Yeri' 
            AND net_area IS NOT NULL 
            AND (square_meters IS NULL OR square_meters = 0)";
    
    $result = $conn->query($sql);
    
    if ($result) {
        echo "Güncelleme başarılı! Etkilenen kayıt sayısı: " . $conn->affected_rows;
        
        // Güncellenmiş kaydı göster
        $check_sql = "SELECT id, property_type, net_area, square_meters 
                     FROM properties 
                     WHERE id = 50";
        $check_result = $conn->query($check_sql);
        $property = $check_result->fetch_assoc();
        
        echo "<br><br>Güncellenmiş Kayıt:<br>";
        echo "ID: " . $property['id'] . "<br>";
        echo "Tip: " . $property['property_type'] . "<br>";
        echo "Net Alan: " . $property['net_area'] . "<br>";
        echo "m²: " . $property['square_meters'] . "<br>";
    } else {
        echo "Güncelleme sırasında bir hata oluştu: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    $conn->close();
} 