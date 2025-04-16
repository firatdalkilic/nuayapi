<?php
// Hedef klasör
$target_dir = "admin/uploads/agents/";

// Klasörün varlığını kontrol et
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0755, true)) {
        die("Klasör oluşturulamadı: " . $target_dir);
    }
}

// Fotoğrafları kopyala
$photos = [
    'firat.jpg' => 'assets/img/team/firat.jpg',
    'aysenur.jpg' => 'assets/img/team/aysenur.jpg'
];

foreach ($photos as $target => $source) {
    $target_path = $target_dir . $target;
    
    // Kaynak dosyanın varlığını kontrol et
    if (!file_exists($source)) {
        echo "Kaynak dosya bulunamadı: " . $source . "\n";
        continue;
    }
    
    // Dosyayı kopyala
    if (copy($source, $target_path)) {
        echo $target . " başarıyla yüklendi\n";
        
        // Dosya izinlerini ayarla
        chmod($target_path, 0644);
    } else {
        echo $target . " yüklenemedi\n";
    }
}

// Klasör içeriğini listele
echo "\nKlasör içeriği (" . $target_dir . "):\n";
$files = scandir($target_dir);
foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        echo "- " . $file . "\n";
    }
}
?> 