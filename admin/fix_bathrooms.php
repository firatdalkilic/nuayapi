<?php
require_once 'config.php';

// Önce bathroom_count sütununun var olup olmadığını kontrol et
$check_column = "SHOW COLUMNS FROM properties LIKE 'bathroom_count'";
$column_exists = $conn->query($check_column)->num_rows > 0;

if (!$column_exists) {
    // Önce bathrooms sütununun var olup olmadığını kontrol et
    $check_old_column = "SHOW COLUMNS FROM properties LIKE 'bathrooms'";
    $old_column_exists = $conn->query($check_old_column)->num_rows > 0;

    if ($old_column_exists) {
        // bathrooms sütunu varsa, yeni sütunu ekle ve verileri kopyala
        $alter_table = "ALTER TABLE properties ADD COLUMN bathroom_count INT DEFAULT 0";
        if ($conn->query($alter_table)) {
            echo "bathroom_count sütunu eklendi.<br>";
            
            // Verileri kopyala
            $copy_data = "UPDATE properties SET bathroom_count = bathrooms";
            if ($conn->query($copy_data)) {
                echo "Veriler bathrooms sütunundan bathroom_count sütununa kopyalandı.<br>";
                
                // Eski sütunu sil
                $drop_column = "ALTER TABLE properties DROP COLUMN bathrooms";
                if ($conn->query($drop_column)) {
                    echo "bathrooms sütunu silindi.<br>";
                } else {
                    echo "Hata: bathrooms sütunu silinemedi. " . $conn->error . "<br>";
                }
            } else {
                echo "Hata: Veriler kopyalanamadı. " . $conn->error . "<br>";
            }
        } else {
            echo "Hata: bathroom_count sütunu eklenemedi. " . $conn->error . "<br>";
        }
    } else {
        // bathrooms sütunu yoksa, direkt yeni sütunu ekle
        $add_column = "ALTER TABLE properties ADD COLUMN bathroom_count INT DEFAULT 0";
        if ($conn->query($add_column)) {
            echo "bathroom_count sütunu eklendi.<br>";
        } else {
            echo "Hata: bathroom_count sütunu eklenemedi. " . $conn->error . "<br>";
        }
    }
} else {
    echo "bathroom_count sütunu zaten mevcut.<br>";
}

$conn->close();
echo "İşlem tamamlandı.<br>";
?> 