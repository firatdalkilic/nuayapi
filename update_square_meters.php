<?php
require_once 'admin/config.php';

try {
    // Debug için SQL sorgusunu yazdıralım
    echo "Güncelleme başlıyor...<br>";
    
    // Önce mevcut durumu kontrol edelim
    $check_sql = "SELECT id, property_type, net_area, square_meters FROM properties WHERE property_type = 'İş Yeri'";
    $check_result = $conn->query($check_sql);
    
    echo "Mevcut iş yeri ilanları:<br>";
    while ($row = $check_result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", net_area: " . $row['net_area'] . ", square_meters: " . $row['square_meters'] . "<br>";
    }

    // Güncelleme sorgusunu çalıştır
    $sql = "UPDATE properties 
            SET square_meters = net_area 
            WHERE property_type = 'İş Yeri' AND net_area IS NOT NULL";
    
    if ($conn->query($sql)) {
        echo "<br>Güncelleme başarılı!<br>";
        
        // Güncellemeden sonraki durumu kontrol edelim
        $after_check = $conn->query($check_sql);
        echo "<br>Güncellemeden sonraki durum:<br>";
        while ($row = $after_check->fetch_assoc()) {
            echo "ID: " . $row['id'] . ", net_area: " . $row['net_area'] . ", square_meters: " . $row['square_meters'] . "<br>";
        }
        
        echo "<br>Etkilenen kayıt sayısı: " . $conn->affected_rows;
    } else {
        echo "Güncelleme sırasında bir hata oluştu: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 