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

// Tüm kat numaralarını güncelle
$floor_updates = [
    "UPDATE properties SET floor_location = 'Bodrum Kat' WHERE floor_location = '-1'",
    "UPDATE properties SET floor_location = 'Zemin Kat' WHERE floor_location = '0'",
    "UPDATE properties SET floor_location = '1. Kat' WHERE floor_location = '1'",
    "UPDATE properties SET floor_location = '2. Kat' WHERE floor_location = '2'",
    "UPDATE properties SET floor_location = '3. Kat' WHERE floor_location = '3'",
    "UPDATE properties SET floor_location = '4. Kat' WHERE floor_location = '4'",
    "UPDATE properties SET floor_location = '5. Kat' WHERE floor_location = '5'",
    "UPDATE properties SET floor_location = '6. Kat' WHERE floor_location = '6'",
    "UPDATE properties SET floor_location = '7. Kat' WHERE floor_location = '7'",
    "UPDATE properties SET floor_location = '8. Kat' WHERE floor_location = '8'",
    "UPDATE properties SET floor_location = '9. Kat' WHERE floor_location = '9'",
    "UPDATE properties SET floor_location = '10. Kat' WHERE floor_location = '10'"
];

foreach ($floor_updates as $sql) {
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        echo "Kat bilgisi güncellendi: " . $sql . "<br>";
    } else {
        echo "Güncelleme sırasında bir hata oluştu: " . $stmt->error . "<br>";
    }
}

// Debug: Mevcut floor_location değerlerini kontrol et
$debug_sql = "SELECT id, title, floor_location FROM properties WHERE floor_location IS NOT NULL";
$debug_result = $conn->query($debug_sql);
if ($debug_result) {
    echo "<h3>Mevcut floor_location değerleri:</h3>";
    echo "<pre>";
    while ($row = $debug_result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
}
?> 