<?php
require_once 'config.php';

// Hata raporlamayı aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Doğru kat formatları
$floor_options = [
    'Bodrum Kat',
    'Yarı Bodrum Kat',
    'Zemin Kat',
    'Bahçe Kat',
    'Yüksek Giriş',
    '1. Kat',
    '2. Kat',
    '3. Kat',
    '4. Kat',
    '5. Kat',
    '6. Kat',
    '7. Kat',
    '8. Kat',
    '9. Kat',
    '10. Kat',
    '11. Kat',
    '12. Kat ve üzeri',
    'Çatı Kat'
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
            $new_floor = $current_floor . '. Kat';
            
            // 12 ve üzeri için özel durum
            if ((int)$current_floor >= 12) {
                $new_floor = '12. Kat ve üzeri';
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
        } else {
            // KAT kelimesini Kat olarak değiştir
            if (strpos($current_floor, 'KAT') !== false) {
                $new_floor = str_replace('KAT', 'Kat', $current_floor);
                
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
    }
    echo "İşlem tamamlandı.";
} else {
    echo "Sorgu hatası: " . $conn->error;
}

$conn->close();
?> 