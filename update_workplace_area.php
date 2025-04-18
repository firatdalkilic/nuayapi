<?php
require_once 'admin/config.php';

try {
    // Debug için hata raporlamayı aç
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "Bağlantı durumu: " . ($conn->connect_errno ? "Hata: " . $conn->connect_error : "Başarılı") . "<br><br>";
    
    // Önce square_meters sütununu kontrol edelim
    $check_column = "SHOW COLUMNS FROM properties LIKE 'square_meters'";
    $result = $conn->query($check_column);
    
    if ($result->num_rows == 0) {
        // square_meters sütunu yoksa ekleyelim
        echo "square_meters sütunu ekleniyor...<br>";
        $add_column = "ALTER TABLE properties ADD COLUMN square_meters DECIMAL(10,2) AFTER net_area";
        if ($conn->query($add_column)) {
            echo "square_meters sütunu başarıyla eklendi.<br>";
        } else {
            throw new Exception("Sütun eklenirken hata: " . $conn->error);
        }
    } else {
        echo "square_meters sütunu zaten mevcut.<br>";
    }
    
    // Mevcut iş yeri ilanlarını listele
    echo "<br>Mevcut iş yeri ilanları:<br>";
    $list_sql = "SELECT id, property_type, net_area, square_meters FROM properties WHERE property_type = 'İş Yeri'";
    $list_result = $conn->query($list_sql);
    
    if ($list_result->num_rows > 0) {
        while ($row = $list_result->fetch_assoc()) {
            echo "ID: " . $row['id'] . 
                 ", Property Type: " . $row['property_type'] . 
                 ", Net Area: " . ($row['net_area'] ?? 'NULL') . 
                 ", Square Meters: " . ($row['square_meters'] ?? 'NULL') . "<br>";
        }
    } else {
        echo "Hiç iş yeri ilanı bulunamadı.<br>";
    }
    
    // Güncelleme sorgusunu çalıştır
    echo "<br>Güncelleme yapılıyor...<br>";
    $update_sql = "UPDATE properties 
                   SET square_meters = net_area 
                   WHERE property_type = 'İş Yeri' 
                   AND net_area IS NOT NULL 
                   AND (square_meters IS NULL OR square_meters = 0)";
    
    if ($conn->query($update_sql)) {
        echo "Güncelleme başarılı! Etkilenen kayıt sayısı: " . $conn->affected_rows . "<br>";
        
        // Güncellemeden sonraki durumu kontrol et
        echo "<br>Güncellemeden sonraki durum:<br>";
        $check_result = $conn->query($list_sql);
        while ($row = $check_result->fetch_assoc()) {
            echo "ID: " . $row['id'] . 
                 ", Property Type: " . $row['property_type'] . 
                 ", Net Area: " . ($row['net_area'] ?? 'NULL') . 
                 ", Square Meters: " . ($row['square_meters'] ?? 'NULL') . "<br>";
        }
    } else {
        throw new Exception("Güncelleme sırasında hata: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 