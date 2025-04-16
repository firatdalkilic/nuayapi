<?php
require_once 'admin/config.php';

try {
    // Agents tablosunun varlığını kontrol et
    $tables_query = "SHOW TABLES LIKE 'agents'";
    $tables_result = $conn->query($tables_query);
    
    if ($tables_result->num_rows == 0) {
        echo "Agents tablosu bulunamadı!\n";
        exit;
    }

    // Danışman sayısını kontrol et
    $count_query = "SELECT COUNT(*) as count FROM agents";
    $count_result = $conn->query($count_query);
    
    if (!$count_result) {
        echo "Sorgu hatası: " . $conn->error . "\n";
        exit;
    }
    
    $row = $count_result->fetch_assoc();
    echo "Danışman sayısı: " . $row['count'] . "\n";

    // Tüm danışmanları listele
    $list_query = "SELECT agent_name, agent_title, agent_photo FROM agents";
    $list_result = $conn->query($list_query);
    
    if ($list_result->num_rows > 0) {
        echo "\nDanışman listesi:\n";
        while ($agent = $list_result->fetch_assoc()) {
            echo "- " . $agent['agent_name'] . " (" . $agent['agent_title'] . ")\n";
            echo "  Fotoğraf: " . ($agent['agent_photo'] ?? 'Yok') . "\n";
            
            if (!empty($agent['agent_photo'])) {
                $photo_path = 'admin/uploads/agents/' . $agent['agent_photo'];
                echo "  Fotoğraf yolu: " . $photo_path . "\n";
                echo "  Dosya var mı: " . (file_exists($photo_path) ? 'Evet' : 'Hayır') . "\n";
            }
            echo "\n";
        }
    }

    // Uploads klasörünü kontrol et
    $uploads_dir = 'admin/uploads/agents';
    echo "\nUploads klasörü kontrolü:\n";
    echo "Klasör var mı: " . (is_dir($uploads_dir) ? 'Evet' : 'Hayır') . "\n";
    if (is_dir($uploads_dir)) {
        echo "Klasör içeriği:\n";
        $files = scandir($uploads_dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "- " . $file . "\n";
            }
        }
    }

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?> 