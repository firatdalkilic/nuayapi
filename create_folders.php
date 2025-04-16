<?php
// Klasör yolları
$uploads_dir = 'admin/uploads';
$agents_dir = $uploads_dir . '/agents';

// uploads klasörünü oluştur
if (!is_dir($uploads_dir)) {
    if (!mkdir($uploads_dir, 0755, true)) {
        die("uploads klasörü oluşturulamadı");
    }
    echo "uploads klasörü oluşturuldu\n";
}

// agents klasörünü oluştur
if (!is_dir($agents_dir)) {
    if (!mkdir($agents_dir, 0755, true)) {
        die("agents klasörü oluşturulamadı");
    }
    echo "agents klasörü oluşturuldu\n";
}

// .htaccess dosyası oluştur (güvenlik için)
$htaccess_content = "Options -Indexes\nallow from all";
file_put_contents($uploads_dir . '/.htaccess', $htaccess_content);
file_put_contents($agents_dir . '/.htaccess', $htaccess_content);

echo "Klasörler başarıyla oluşturuldu ve yapılandırıldı\n";
?> 