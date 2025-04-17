<?php
require_once 'config.php';

// Debug modunu aç
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');

// Doğru format listesi
$correct_floor_options = [
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

try {
    // Tüm floor_location değerlerini al
    $sql = "SELECT id, floor_location FROM properties WHERE floor_location IS NOT NULL";
    $result = $conn->query($sql);

    if ($result) {
        echo "Toplam kayıt sayısı: " . $result->num_rows . "<br>";
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $current_floor = trim($row['floor_location']);
            
            echo "ID: $id, Mevcut değer: '$current_floor'<br>";
            
            // Değer zaten doğru formatta mı kontrol et
            if (!in_array($current_floor, $correct_floor_options)) {
                // Düzeltilmiş değeri bul
                $corrected_value = null;
                
                // Özel düzeltmeler
                $current_floor_upper = mb_strtoupper($current_floor, 'UTF-8');
                
                foreach ($correct_floor_options as $option) {
                    if (mb_strtoupper($option, 'UTF-8') === $current_floor_upper) {
                        $corrected_value = $option;
                        break;
                    }
                }
                
                if ($corrected_value) {
                    // Değeri güncelle
                    $update_sql = "UPDATE properties SET floor_location = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("si", $corrected_value, $id);
                    
                    if ($stmt->execute()) {
                        echo "ID: $id - '$current_floor' değeri '$corrected_value' olarak güncellendi<br>";
                    } else {
                        echo "HATA: ID: $id güncellenemedi - " . $stmt->error . "<br>";
                    }
                } else {
                    echo "UYARI: ID: $id - '$current_floor' için eşleşme bulunamadı<br>";
                }
            }
        }
        echo "İşlem tamamlandı.<br>";
    } else {
        throw new Exception("Sorgu çalıştırılamadı: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
} 