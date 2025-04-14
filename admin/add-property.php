<?php
session_start();
require_once 'config.php';
checkLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini kontrol et
    $required_fields = [
        'title' => 'İlan Başlığı',
        'price' => 'Fiyat',
        'status' => 'Durum',
        'beds' => 'Oda Sayısı',
        'neighborhood' => 'Mahalle',
        'description' => 'Açıklama',
        'property_type' => 'Emlak Tipi',
        'living_room' => 'Salon Sayısı',
        'parking' => 'Otopark',
        'usage_status' => 'Kullanım Durumu',
        'video_call_available' => 'Görüntülü Arama'
    ];

    $errors = [];
    foreach ($required_fields as $field => $label) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $label . " alanı boş bırakılamaz.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Form verilerini al ve varsayılan değerler ata
    $title = trim($_POST['title']);
    
    // Fiyat alanını işle
    $price = trim($_POST['price']);
    if (!is_numeric($price) || $price <= 0) {
        $_SESSION['error'] = "Geçerli bir fiyat girmelisiniz.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    $price = (float)$price;
    
    $status = trim($_POST['status']);
    $beds = (int)$_POST['beds'];
    $location = 'Didim'; // Sabit değer
    $neighborhood = trim($_POST['neighborhood']);
    $description = trim($_POST['description']);
    $property_type = trim($_POST['property_type']);
    $gross_area = isset($_POST['gross_area']) ? (float)$_POST['gross_area'] : 0;
    $net_area = isset($_POST['net_area']) ? (float)$_POST['net_area'] : 0;
    $floor_location = isset($_POST['floor_location']) ? trim($_POST['floor_location']) : NULL;
    $total_floors = isset($_POST['total_floors']) ? (int)$_POST['total_floors'] : 0;
    $bathroom_count = isset($_POST['bathroom_count']) ? (int)$_POST['bathroom_count'] : 0;
    $balcony = isset($_POST['balcony']) ? trim($_POST['balcony']) : 'Yok';
    $site_status = isset($_POST['site_status']) ? trim($_POST['site_status']) : 'Hayır';
    $building_age = isset($_POST['building_age']) ? trim($_POST['building_age']) : NULL;
    $living_room = (int)$_POST['living_room'];
    $parking = trim($_POST['parking']);
    $usage_status = trim($_POST['usage_status']);
    $video_call_available = trim($_POST['video_call_available']);
    $eligible_for_credit = isset($_POST['eligible_for_credit']) ? trim($_POST['eligible_for_credit']) : 'Hayır';
    $heating = isset($_POST['heating']) ? trim($_POST['heating']) : NULL;

    // Resim kontrolü
    if (!isset($_FILES["images"]) || empty($_FILES["images"]["name"][0])) {
        $_SESSION['error'] = "En az bir resim yüklemelisiniz.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Veritabanına kaydet
    $sql = "INSERT INTO properties (
        title, description, price, location, neighborhood, property_type,
        status, net_area, room_count, beds, bathroom_count, balcony,
        parking, site, floor_location, total_floors, gross_area, living_room_count,
        building_age, eligible_for_credit, heating
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssssdsisssssidisss", 
            $title, $description, $price, $location, $neighborhood, $property_type,
            $status, $net_area, $beds, $beds, $bathroom_count, $balcony,
            $parking, $site_status, $floor_location, $total_floors, $gross_area, $living_room,
            $building_age, $eligible_for_credit, $heating
        );
        
        if ($stmt->execute()) {
            $property_id = $conn->insert_id;
            
            // Resimleri yükle
            $upload_success = false;
            if (isset($_FILES["images"]) && !empty($_FILES["images"]["name"][0])) {
                $target_dir = "../uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $total = count($_FILES["images"]["name"]);
                $first_image = true;
                
                for($i = 0; $i < $total; $i++) {
                    if($_FILES["images"]["error"][$i] == 0) {
                        $imageFileType = strtolower(pathinfo($_FILES["images"]["name"][$i], PATHINFO_EXTENSION));
                        
                        if($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png" || $imageFileType == "gif") {
                            $unique_name = time() . '_' . uniqid() . '.' . $imageFileType;
                            $target_file = $target_dir . $unique_name;
                            
                            $check = getimagesize($_FILES["images"]["tmp_name"][$i]);
                            if($check !== false && move_uploaded_file($_FILES["images"]["tmp_name"][$i], $target_file)) {
                                $is_featured = $first_image ? 1 : 0;
                                $img_stmt = $conn->prepare("INSERT INTO property_images (property_id, image_name, is_featured) VALUES (?, ?, ?)");
                                $img_stmt->bind_param("isi", $property_id, $unique_name, $is_featured);
                                if($img_stmt->execute()) {
                                    $upload_success = true;
                                    $first_image = false;
                                }
                            }
                        }
                    }
                }
            }
            
            // Video yükleme
            if (isset($_FILES["property_video"]) && !empty($_FILES["property_video"]["name"])) {
                $video_dir = "../uploads/videos/";
                if (!file_exists($video_dir)) {
                    mkdir($video_dir, 0777, true);
                }

                $video_name = time() . '_' . basename($_FILES["property_video"]["name"]);
                $video_target = $video_dir . $video_name;
                
                $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
                
                if (in_array($_FILES["property_video"]["type"], $allowed_types)) {
                    if (move_uploaded_file($_FILES["property_video"]["tmp_name"], $video_target)) {
                        $video_stmt = $conn->prepare("UPDATE properties SET video_file = ? WHERE id = ?");
                        $video_stmt->bind_param("si", $video_name, $property_id);
                        $video_stmt->execute();
                    }
                }
            }
            
            if ($upload_success) {
                $_SESSION['success'] = "İlan başarıyla eklendi.";
                header("Location: dashboard.php");
                exit;
            } else {
                // Resim yüklenemedi, ilanı sil
                $conn->query("DELETE FROM properties WHERE id = " . $property_id);
                $_SESSION['error'] = "Resimler yüklenirken bir hata oluştu. İlan eklenemedi.";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "İlan eklenirken bir hata oluştu: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İlan Ekle - Admin Panel</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        .back-button {
            position: fixed;
            top: 100px;
            left: 20px;
            z-index: 100;
            background: rgba(37, 99, 235, 0.9);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(4px);
        }

        .back-button:hover {
            background: rgba(29, 78, 216, 0.95);
            transform: translateX(-3px);
            color: white;
        }

        .back-button i {
            font-size: 24px;
        }

        @media (max-width: 768px) {
            .back-button {
                top: 80px;
                left: 15px;
            }
        }
    </style>
</head>
<body class="admin-dashboard">
    <a href="dashboard.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../assets/img/nua_logo.jpg" alt="Nua Logo" style="max-height: 40px; border-radius: 50%;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">İlanlar</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html" target="_blank">
                            <i class="bi bi-house"></i> Siteyi Görüntüle
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Yeni İlan Ekle</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['errors'])): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php 
                                    foreach ($_SESSION['errors'] as $error) {
                                        echo "<li>" . htmlspecialchars($error) . "</li>";
                                    }
                                    unset($_SESSION['errors']);
                                    ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" class="property-form" id="propertyForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">İlan Başlığı</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Fiyat (₺)</label>
                                    <input type="number" class="form-control" id="price" name="price" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Durum</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Kiralık">Kiralık</option>
                                        <option value="Satılık">Satılık</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="beds" class="form-label">Oda Sayısı</label>
                                    <input type="number" class="form-control" id="beds" name="beds" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="living_room" class="form-label">Salon Sayısı</label>
                                    <input type="number" class="form-control" id="living_room" name="living_room" min="0" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Konum</label>
                                <input type="text" class="form-control" id="location" name="location" value="Didim" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="neighborhood" class="form-label">Mahalle</label>
                                <select class="form-select" id="neighborhood" name="neighborhood" required>
                                    <option value="">Mahalle Seçiniz</option>
                                    <option value="Ak-yeniköy Mah.">Ak-yeniköy Mah.</option>
                                    <option value="Akbük Mah.">Akbük Mah.</option>
                                    <option value="Akköy Mah.">Akköy Mah.</option>
                                    <option value="Altınkum Mah.">Altınkum Mah.</option>
                                    <option value="Balat Mah.">Balat Mah.</option>
                                    <option value="Batıköy Mah.">Batıköy Mah.</option>
                                    <option value="Cumhuriyet Mah.">Cumhuriyet Mah.</option>
                                    <option value="Çamlık Mah.">Çamlık Mah.</option>
                                    <option value="Denizköy Mah.">Denizköy Mah.</option>
                                    <option value="Efeler Mah.">Efeler Mah.</option>
                                    <option value="Fevzipaşa Mah.">Fevzipaşa Mah.</option>
                                    <option value="Hisar Mah.">Hisar Mah.</option>
                                    <option value="Mavişehir Mah.">Mavişehir Mah.</option>
                                    <option value="Mersindere Mah.">Mersindere Mah.</option>
                                    <option value="Yalıköy Mah.">Yalıköy Mah.</option>
                                    <option value="Yeni Mah.">Yeni Mah.</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="property_type" class="form-label">Emlak Tipi</label>
                                    <select class="form-select" id="property_type" name="property_type" required>
                                        <option value="">Seçiniz</option>
                                        <option value="Daire">Daire</option>
                                        <option value="Villa">Villa</option>
                                        <option value="Müstakil Ev">Müstakil Ev</option>
                                        <option value="Arsa">Arsa</option>
                                        <option value="İş Yeri">İş Yeri</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="building_age" class="form-label">Bina Yaşı</label>
                                    <select class="form-select" id="building_age" name="building_age">
                                        <option value="">Seçiniz</option>
                                        <option value="0">0 (Yeni)</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11-15">11-15</option>
                                        <option value="16-20">16-20</option>
                                        <option value="21-25">21-25</option>
                                        <option value="26+">26+</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gross_area" class="form-label">m² (Brüt)</label>
                                    <input type="number" class="form-control" id="gross_area" name="gross_area">
                                </div>
                                <div class="col-md-6">
                                    <label for="net_area" class="form-label">m² (Net)</label>
                                    <input type="number" class="form-control" id="net_area" name="net_area">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="floor_location" class="form-label">Bulunduğu Kat</label>
                                    <select class="form-select" id="floor_location" name="floor_location">
                                        <option value="">Seçiniz</option>
                                        <option value="Bodrum Kat">Bodrum Kat</option>
                                        <option value="Yarı Bodrum Kat">Yarı Bodrum Kat</option>
                                        <option value="Zemin Kat">Zemin Kat</option>
                                        <option value="Bahçe Katı">Bahçe Katı</option>
                                        <option value="Yüksek Giriş">Yüksek Giriş</option>
                                        <option value="1. Kat">1. Kat</option>
                                        <option value="2. Kat">2. Kat</option>
                                        <option value="3. Kat">3. Kat</option>
                                        <option value="4. Kat">4. Kat</option>
                                        <option value="5. Kat">5. Kat</option>
                                        <option value="6. Kat">6. Kat</option>
                                        <option value="7. Kat">7. Kat</option>
                                        <option value="8. Kat">8. Kat</option>
                                        <option value="9. Kat">9. Kat</option>
                                        <option value="10. Kat">10. Kat</option>
                                        <option value="11. Kat">11. Kat</option>
                                        <option value="12. Kat ve üzeri">12. Kat ve üzeri</option>
                                        <option value="Çatı Katı">Çatı Katı</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="heating" class="form-label">Isıtma</label>
                                    <select class="form-select" id="heating" name="heating">
                                        <option value="">Seçiniz</option>
                                        <option value="Kombi (Doğalgaz)">Kombi (Doğalgaz)</option>
                                        <option value="Merkezi">Merkezi</option>
                                        <option value="Klima">Klima</option>
                                        <option value="Yerden Isıtma">Yerden Isıtma</option>
                                        <option value="Soba">Soba</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="total_floors" class="form-label">Kat Sayısı</label>
                                    <input type="number" class="form-control" id="total_floors" name="total_floors">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="bathroom_count" class="form-label">Banyo Sayısı</label>
                                    <input type="number" class="form-control" id="bathroom_count" name="bathroom_count">
                                </div>
                                <div class="col-md-4">
                                    <label for="balcony" class="form-label">Balkon</label>
                                    <select class="form-select" id="balcony" name="balcony">
                                        <option value="">Seçiniz</option>
                                        <option value="Var">Var</option>
                                        <option value="Yok">Yok</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="site_status" class="form-label">Site İçerisinde</label>
                                    <select class="form-select" id="site_status" name="site_status">
                                        <option value="">Seçiniz</option>
                                        <option value="Evet">Evet</option>
                                        <option value="Hayır">Hayır</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="eligible_for_credit" class="form-label">Krediye Uygun</label>
                                    <select class="form-select" id="eligible_for_credit" name="eligible_for_credit">
                                        <option value="Evet">Evet</option>
                                        <option value="Hayır">Hayır</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="parking" class="form-label">Otopark</label>
                                    <select class="form-select" id="parking" name="parking" required>
                                        <option value="Var">Var</option>
                                        <option value="Yok">Yok</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="usage_status" class="form-label">Kullanım Durumu</label>
                                    <select class="form-select" id="usage_status" name="usage_status" required>
                                        <option value="Boş">Boş</option>
                                        <option value="Kiracılı">Kiracılı</option>
                                        <option value="Mülk Sahibi">Mülk Sahibi</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="video_call_available" class="form-label">Görüntülü Arama</label>
                                    <select class="form-select" id="video_call_available" name="video_call_available" required>
                                        <option value="Evet">Evet</option>
                                        <option value="Hayır">Hayır</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="images" class="form-label">Fotoğraflar</label>
                                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple required>
                            </div>

                            <div class="mb-3">
                                <label for="property_video" class="form-label">Video</label>
                                <input type="file" class="form-control" id="property_video" name="property_video" accept="video/mp4,video/webm,video/ogg">
                                <small class="text-muted">Desteklenen formatlar: MP4, WebM, OGG</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">İlanı Kaydet</button>
                                <a href="dashboard.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submit öncesi fiyat alanını temizle
        document.querySelector('form').addEventListener('submit', function(e) {
            let priceInput = document.getElementById('price');
            // Sadece sayısal değer kalacak
            priceInput.value = parseFloat(priceInput.value) || 0;
        });
    </script>
</body>
</html> 