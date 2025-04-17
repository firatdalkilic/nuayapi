<?php
require_once 'admin/config.php';

// İlan ID'sini buraya yazın
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id > 0) {
    // Bina yaşını '0' olarak güncelle
    $stmt = $conn->prepare("UPDATE properties SET building_age = '0' WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    
    if ($stmt->execute()) {
        echo "Bina yaşı başarıyla güncellendi.";
        
        // Debug bilgisi
        $check_stmt = $conn->prepare("SELECT id, title, building_age FROM properties WHERE id = ?");
        $check_stmt->bind_param("i", $property_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $property = $result->fetch_assoc();
        
        echo "<pre>";
        print_r($property);
        echo "</pre>";
    } else {
        echo "Güncelleme hatası: " . $stmt->error;
    }
} else {
    echo "Geçerli bir ilan ID'si belirtilmedi.";
}

// floor_location değerini güncelle
$sql = "UPDATE properties SET floor_location = '3. Kat' WHERE floor_location = '3'";
$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    echo "floor_location değeri başarıyla güncellendi.";
} else {
    echo "Güncelleme sırasında bir hata oluştu: " . $stmt->error;
}
?> 