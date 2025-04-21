<?php
require_once(__DIR__ . '/../config/db.php');

// Sample images from the uploads directory
$images = [
    "uploads/sample_arsa_1.jpg",
    "uploads/sample_arsa_2.jpg",
    "uploads/sample_arsa_3.jpg"
];

// Sample locations
$locations = [
    "Çanakkale, Merkez",
    "Çanakkale, Ayvacık",
    "Çanakkale, Ezine"
];

// Sample titles
$titles = [
    "Çanakkale Merkezde Satılık İmarlı Arsa",
    "Ayvacık'ta Deniz Manzaralı Yatırımlık Arsa",
    "Ezine'de Ana Yola Yakın Satılık Arsa"
];

// Sample descriptions
$descriptions = [
    "Çanakkale merkeze yakın, tüm altyapısı hazır, konut imarlı arsa. Merkezi konumu ve ulaşım kolaylığı ile öne çıkan arsanın çevresinde modern konut projeleri bulunmaktadır.",
    "Deniz manzaralı, yatırıma uygun arsa. Bölgenin gelişen yapısı ve turistik potansiyeli ile değer kazanmaya devam eden lokasyonda bulunmaktadır.",
    "Ana yola yakın, düz ve kullanışlı arsa. Tarımsal faaliyetler ve yatırım için ideal konumda yer almaktadır. Tüm tapu işlemleri hazırdır."
];

try {
    for ($i = 0; $i < 3; $i++) {
        // Generate random area between 500m² and 2000m²
        $area = rand(500, 2000);
        
        // Generate price based on area (example: 1000 TL per m²)
        $price = $area * 1000;
        
        $sql = "INSERT INTO properties (title, price, net_area, location, description, status, type, image_name, created_at) 
                VALUES (?, ?, ?, ?, ?, 'sale', 'land', ?, NOW())";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssss", 
            $titles[$i],
            $price,
            $area,
            $locations[$i],
            $descriptions[$i],
            $images[$i]
        );
        
        if ($stmt->execute()) {
            echo "Sample land property " . ($i + 1) . " created successfully\n";
        } else {
            echo "Error creating sample land property " . ($i + 1) . ": " . $stmt->error . "\n";
        }
    }
    
    echo "All sample land properties have been generated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} 