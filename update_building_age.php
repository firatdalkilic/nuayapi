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
    "UPDATE properties SET floor_location = 'Bodrum KAT' WHERE floor_location IN ('-1', 'Bodrum Kat', 'Bodrum kat')",
    "UPDATE properties SET floor_location = 'Zemin KAT' WHERE floor_location IN ('0', 'Zemin Kat', 'Zemin kat')",
    "UPDATE properties SET floor_location = '1. KAT' WHERE floor_location IN ('1', '1. Kat', '1. kat')",
    "UPDATE properties SET floor_location = '2. KAT' WHERE floor_location IN ('2', '2. Kat', '2. kat')",
    "UPDATE properties SET floor_location = '3. KAT' WHERE floor_location IN ('3', '3. Kat', '3. kat')",
    "UPDATE properties SET floor_location = '4. KAT' WHERE floor_location IN ('4', '4. Kat', '4. kat')",
    "UPDATE properties SET floor_location = '5. KAT' WHERE floor_location IN ('5', '5. Kat', '5. kat')",
    "UPDATE properties SET floor_location = '6. KAT' WHERE floor_location IN ('6', '6. Kat', '6. kat')",
    "UPDATE properties SET floor_location = '7. KAT' WHERE floor_location IN ('7', '7. Kat', '7. kat')",
    "UPDATE properties SET floor_location = '8. KAT' WHERE floor_location IN ('8', '8. Kat', '8. kat')",
    "UPDATE properties SET floor_location = '9. KAT' WHERE floor_location IN ('9', '9. Kat', '9. kat')",
    "UPDATE properties SET floor_location = '10. KAT' WHERE floor_location IN ('10', '10. Kat', '10. kat')"
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