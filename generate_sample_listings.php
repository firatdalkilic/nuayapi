<?php
require_once 'admin/config.php';

// Hata raporlamayı aktifleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// İlanları silme işlemi
if (isset($_POST['delete'])) {
    // Önce property_images tablosundaki ilişkili kayıtları sil
    $sql = "DELETE FROM property_images WHERE property_id IN (SELECT id FROM properties)";
    $conn->query($sql);
    
    // Sonra properties tablosundaki tüm kayıtları sil
    $sql = "DELETE FROM properties";
    if ($conn->query($sql)) {
        $delete_message = "Tüm ilanlar başarıyla silindi!";
    } else {
        $delete_message = "İlanlar silinirken bir hata oluştu: " . $conn->error;
    }
}

// Form gönderildi mi kontrol et
if (isset($_POST['generate'])) {
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
    $agent_query = "SELECT id FROM agents";
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
    function generatePrice($type, $status) {
        switch ($type) {
            case 'Konut':
                return $status == 'sale' ? rand(2000000, 8000000) : rand(5000, 20000);
            case 'İş Yeri':
                return $status == 'sale' ? rand(3000000, 15000000) : rand(10000, 50000);
            case 'Arsa':
                return rand(1000000, 5000000); // Arsa sadece satılık
            default:
                return rand(1000000, 10000000);
        }
    }

    // Her tip için 8 ilan oluştur
    $property_types = ['Konut', 'İş Yeri', 'Arsa'];
    $created_count = 0;

    foreach ($property_types as $type) {
        for ($i = 0; $i < 8; $i++) {
            $neighborhood = $neighborhoods[array_rand($neighborhoods)];
            $street = $streets[array_rand($streets)];
            $agent_id = $agent_ids[array_rand($agent_ids)];
            $status = $type == 'Arsa' ? 'sale' : (rand(0, 1) ? 'sale' : 'rent');
            
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
            $price = generatePrice($type, $status);
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
                $created_count++;
                
                // Varsayılan resmi ekle
                $image_name = "property-default.jpg";
                
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
    $success_message = "Toplam $created_count adet ilan başarıyla oluşturuldu!";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Örnek İlan Oluşturucu</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-generate {
            background-color: #002e5c;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-bottom: 10px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn-generate:hover {
            background-color: #001f3f;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .alert {
            margin-top: 20px;
        }
        .buttons-container {
            display: flex;
            gap: 10px;
        }
        .button-wrapper {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Örnek İlan Oluşturucu</h2>
        <p class="text-center mb-4">
            Bu araç her kategoriden (Konut, İş Yeri, Arsa) 8'er adet olmak üzere toplam 24 örnek ilan oluşturacaktır.
            <br><br>
            <strong>Not:</strong> Çalıştırmadan önce örnek resimlerin yüklendiğinden emin olun:
            <br>
            - sample_konut_1.jpg - sample_konut_5.jpg<br>
            - sample_is_yeri_1.jpg - sample_is_yeri_5.jpg<br>
            - sample_arsa_1.jpg - sample_arsa_5.jpg
        </p>
        
        <div class="buttons-container">
            <div class="button-wrapper">
                <form method="post" action="">
                    <button type="submit" name="generate" class="btn-generate">
                        Örnek İlanları Oluştur
                    </button>
                </form>
            </div>
            <div class="button-wrapper">
                <form method="post" action="" onsubmit="return confirm('Tüm ilanlar silinecek. Emin misiniz?');">
                    <button type="submit" name="delete" class="btn-delete">
                        Tüm İlanları Sil
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success text-center mt-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($delete_message)): ?>
            <div class="alert <?php echo strpos($delete_message, 'hata') ? 'alert-danger' : 'alert-success'; ?> text-center mt-4">
                <?php echo $delete_message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 