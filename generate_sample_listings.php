<?php
require_once 'admin/config.php';

// Hata raporlamayı aktifleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Didim'deki mahalleler
$neighborhoods = [
    'Altınkum Mahallesi',
    'Çamlık Mahallesi',
    'Efeler Mahallesi',
    'Hisar Mahallesi',
    'Mavişehir Mahallesi',
    'Yeni Mahalle',
    'Cumhuriyet Mahallesi',
    'Camlik Mahallesi',
];

// Sokak isimleri
$streets = [
    'Atatürk Bulvarı',
    'Cumhuriyet Caddesi',
    'Sahil Caddesi',
    'Plaj Caddesi',
    'Altınkum Caddesi',
    'Marina Sokak',
    'Deniz Caddesi',
    'Palmiye Sokak',
    'Çamlık Caddesi',
    'Akdeniz Caddesi',
];

// Konut özellikleri
$house_features = [
    'Deniz Manzaralı',
    'Site İçerisinde',
    'Havuzlu',
    'Güvenlikli',
    'Asansörlü',
    'Otoparkli',
    'Eşyalı',
    'Bahçeli',
    'Balkonlu',
    'Merkezi Konumda'
];

// İş yeri türleri
$workplace_types = [
    'Dükkan',
    'Mağaza',
    'Ofis',
    'Restaurant',
    'Cafe',
    'Market',
    'Depo',
    'Showroom'
];

// Arsa özellikleri
$land_features = [
    'Deniz Manzaralı',
    'Yola Cepheli',
    'Köşe Parsel',
    'İmarlı',
    'Elektrik',
    'Su',
    'Yola Yakın',
    'Toplu Taşımaya Yakın'
];

// Mevcut danışmanları al
$agent_query = "SELECT id FROM agents WHERE status = 1";
$agent_result = $conn->query($agent_query);
$agent_ids = [];
while ($row = $agent_result->fetch_assoc()) {
    $agent_ids[] = $row['id'];
}

// Rastgele başlık oluştur
function generateTitle($type, $neighborhood, $features) {
    $feature = $features[array_rand($features)];
    switch ($type) {
        case 'Konut':
            return "Didim $neighborhood'de $feature " . rand(2, 5) . "+1 Daire";
        case 'İş Yeri':
            $workplace = $features[array_rand($features)];
            return "Didim $neighborhood'de Kiralık/Satılık $workplace";
        case 'Arsa':
            return "Didim $neighborhood'de $feature " . rand(300, 1000) . "m² Arsa";
        default:
            return "Didim'de Satılık Mülk";
    }
}

// Rastgele açıklama oluştur
function generateDescription($type, $features) {
    $selected_features = array_rand(array_flip($features), 3);
    $desc = "Didim'in en güzel lokasyonlarından birinde yer alan ";
    
    switch ($type) {
        case 'Konut':
            $desc .= "dairemiz $selected_features[0], $selected_features[1] ve $selected_features[2] özellikleriyle sizleri bekliyor. ";
            $desc .= "Merkezi konumu ile ulaşım açısından çok avantajlı bir noktada yer almaktadır.";
            break;
        case 'İş Yeri':
            $desc .= "iş yerimiz yüksek cirolu bölgede, yoğun yaya trafiğinin olduğu noktada konumlanmıştır. ";
            $desc .= "İş yerimiz $selected_features[0] olarak kullanıma çok uygundur.";
            break;
        case 'Arsa':
            $desc .= "arsamız $selected_features[0], $selected_features[1] ve $selected_features[2] özellikleriyle yatırım için ideal bir seçenektir. ";
            $desc .= "Gelişmekte olan bölgede değeri her geçen gün artmaktadır.";
            break;
    }
    
    return $desc;
}

// Rastgele fiyat oluştur
function generatePrice($type) {
    switch ($type) {
        case 'Konut':
            return rand(2000000, 8000000);
        case 'İş Yeri':
            return rand(3000000, 15000000);
        case 'Arsa':
            return rand(1000000, 5000000);
        default:
            return rand(1000000, 10000000);
    }
}

// Her tip için 15 ilan oluştur
$property_types = ['Konut', 'İş Yeri', 'Arsa'];

foreach ($property_types as $type) {
    for ($i = 0; $i < 15; $i++) {
        $neighborhood = $neighborhoods[array_rand($neighborhoods)];
        $street = $streets[array_rand($streets)];
        $agent_id = $agent_ids[array_rand($agent_ids)];
        $status = rand(0, 1) ? 'sale' : 'rent';
        
        // Tip'e göre özellikler seç
        switch ($type) {
            case 'Konut':
                $features = $house_features;
                $room_count = rand(2, 5);
                $living_room = 1;
                $square_meters = rand(90, 200);
                $floor = rand(1, 5);
                $building_age = rand(0, 15);
                break;
            case 'İş Yeri':
                $features = $workplace_types;
                $room_count = rand(1, 3);
                $living_room = 0;
                $square_meters = rand(50, 500);
                $floor = rand(0, 2);
                $building_age = rand(0, 20);
                break;
            case 'Arsa':
                $features = $land_features;
                $room_count = 0;
                $living_room = 0;
                $square_meters = rand(300, 1000);
                $floor = 0;
                $building_age = 0;
                break;
        }

        $title = generateTitle($type, $neighborhood, $features);
        $description = generateDescription($type, $features);
        $price = generatePrice($type);
        $location = "Didim, $neighborhood, $street No:" . rand(1, 100);
        
        // İlanı veritabanına ekle
        $sql = "INSERT INTO properties (
            title, description, price, location, neighborhood,
            property_type, status, room_count, living_room,
            square_meters, floor, building_age, agent_id,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            NOW(), NOW()
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssdssssiiiiis",
            $title, $description, $price, $location, $neighborhood,
            $type, $status, $room_count, $living_room,
            $square_meters, $floor, $building_age, $agent_id
        );
        
        if ($stmt->execute()) {
            $property_id = $stmt->insert_id;
            
            // Örnek resim ekle
            $image_number = rand(1, 5); // 1-5 arası rastgele bir resim seç
            $image_name = "sample_" . strtolower(str_replace(' ', '_', $type)) . "_$image_number.jpg";
            
            $sql = "INSERT INTO property_images (property_id, image_name, is_featured, created_at) 
                   VALUES (?, ?, 1, NOW())";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("is", $property_id, $image_name);
            $stmt2->execute();
            $stmt2->close();
        }
        
        $stmt->close();
    }
}

$conn->close();

echo "Toplam " . (count($property_types) * 15) . " adet ilan başarıyla oluşturuldu!"; 