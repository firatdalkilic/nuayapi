<?php
require_once 'config.php';

// Hata raporlamayı aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Doğru kat formatları
$floor_options = [
    'Bodrum KAT',
    'Yarı Bodrum KAT',
    'Zemin KAT',
    'Bahçe KAT',
    'Yüksek Giriş',
    '1. KAT',
    '2. KAT',
    '3. KAT',
    '4. KAT',
    '5. KAT',
    '6. KAT',
    '7. KAT',
    '8. KAT',
    '9. KAT',
    '10. KAT',
    '11. KAT',
    '12. KAT ve üzeri',
    'Çatı KAT'
];

// Tüm floor_location değerlerini al
$sql = "SELECT id, floor_location FROM properties WHERE floor_location IS NOT NULL";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $current_floor = $row['floor_location'];
        
        // Debug bilgisi
        echo "İlan ID: $id, Mevcut değer: [$current_floor]<br>";
        
        // Sadece sayı olan değerleri düzelt
        if (is_numeric($current_floor)) {
            $new_floor = $current_floor . '. KAT';
            
            // 12 ve üzeri için özel durum
            if ((int)$current_floor >= 12) {
                $new_floor = '12. KAT ve üzeri';
            }
            
            // Güncelleme yap
            $update_sql = "UPDATE properties SET floor_location = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $new_floor, $id);
            
            if ($stmt->execute()) {
                echo "Güncellendi: $current_floor -> $new_floor<br>";
            } else {
                echo "Hata: " . $stmt->error . "<br>";
            }
            
            $stmt->close();
        }
    }
    echo "İşlem tamamlandı.";
} else {
    echo "Sorgu hatası: " . $conn->error;
}

$conn->close();
?> 