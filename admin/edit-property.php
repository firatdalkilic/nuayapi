<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
checkLogin();

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int)$_GET['id'];

// Mevcut ilan bilgilerini getir
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $title = $_POST['title'];
    $price = str_replace(['.', ','], '', $_POST['price']);
    $price = (float)$price;
    $status = $_POST['status'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $property_type = $_POST['property_type'];
    $gross_area = $_POST['gross_area'];
    $eligible_for_credit = $_POST['eligible_for_credit'];
    $usage_status = $_POST['usage_status'];
    $video_call_available = $_POST['video_call_available'];

    // Video işlemleri için video_file değişkenini hazırla
    $video_file = $property['video_file']; // Mevcut video dosyasını al

    // Video silme işlemi
    if (isset($_POST['delete_video']) && $_POST['delete_video'] == '1') {
        if (!empty($video_file)) {
            $video_path = "../uploads/videos/" . $video_file;
            if (file_exists($video_path)) {
                unlink($video_path);
            }
            $video_file = null;
        }
    }

    // Yeni video yükleme
    if (isset($_FILES["property_video"]) && !empty($_FILES["property_video"]["name"])) {
        $video_dir = "../uploads/videos/";
        if (!file_exists($video_dir)) {
            mkdir($video_dir, 0777, true);
        }

        // Eski videoyu sil
        if (!empty($video_file)) {
            $old_video_path = $video_dir . $video_file;
            if (file_exists($old_video_path)) {
                unlink($old_video_path);
            }
        }

        $video_name = time() . '_' . basename($_FILES["property_video"]["name"]);
        $video_target = $video_dir . $video_name;
        
        $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
        
        if (in_array($_FILES["property_video"]["type"], $allowed_types)) {
            if (move_uploaded_file($_FILES["property_video"]["tmp_name"], $video_target)) {
                $video_file = $video_name;
            }
        }
    }

    // Emlak tipine göre farklı alanları işle
    if ($property_type === 'Arsa') {
        // Arsa özellikleri
        $zoning_status = $_POST['zoning_status'];
        $block_no = $_POST['block_no'];
        $parcel_no = $_POST['parcel_no'];
        $floor_area_ratio = $_POST['floor_area_ratio'];
        $height_limit = $_POST['height_limit'];
        $deed_status = $_POST['deed_status'];
        $neighborhood = $_POST['neighborhood'];
        $sheet_no = $_POST['sheet_no'];

        // Arsa için SQL sorgusu
        $sql = "UPDATE properties SET 
            title = ?,
            price = ?,
            status = ?,
            location = ?,
            description = ?,
            property_type = ?,
            gross_area = ?,
            zoning_status = ?,
            block_no = ?,
            parcel_no = ?,
            floor_area_ratio = ?,
            height_limit = ?,
            eligible_for_credit = ?,
            deed_status = ?,
            neighborhood = ?,
            usage_status = ?,
            video_call_available = ?,
            video_file = ?,
            sheet_no = ?
            WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssdssssssssssssi", 
            $title,
            $price,
            $status,
            $location,
            $description,
            $property_type,
            $gross_area,
            $zoning_status,
            $block_no,
            $parcel_no,
            $floor_area_ratio,
            $height_limit,
            $eligible_for_credit,
            $deed_status,
            $neighborhood,
            $usage_status,
            $video_call_available,
            $video_file,
            $sheet_no,
            $id
        );
    } else {
        // Konut özellikleri
        $beds = $_POST['beds'];
        $living_room = $_POST['living_room'];
        $bathroom_count = $_POST['bathroom_count'];
        $building_age = isset($_POST['building_age']) ? trim($_POST['building_age']) : NULL;
        $floor_location = isset($_POST['floor_location']) ? trim($_POST['floor_location']) : NULL;
        $total_floors = $_POST['total_floors'];
        $heating = trim($_POST['heating']);
        $balcony = $_POST['balcony'];
        $furnished = $_POST['furnished'];
        $site_status = $_POST['site_status'];
        $parking = $_POST['parking'];
        $net_area = $_POST['net_area'];
        $neighborhood = $_POST['neighborhood'];

        // Konut için SQL sorgusu
        $sql = "UPDATE properties SET 
            title = ?,
            price = ?,
            status = ?,
            beds = ?,
            location = ?,
            neighborhood = ?,
            description = ?,
            property_type = ?,
            gross_area = ?,
            net_area = ?,
            floor_location = ?,
            total_floors = ?,
            heating = ?,
            bathroom_count = ?,
            balcony = ?,
            furnished = ?,
            site_status = ?,
            eligible_for_credit = ?,
            building_age = ?,
            living_room = ?,
            parking = ?,
            usage_status = ?,
            video_call_available = ?,
            video_file = ?
            WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsissssddsisissssisssssi", 
            $title,
            $price,
            $status,
            $beds,
            $location,
            $neighborhood,
            $description,
            $property_type,
            $gross_area,
            $net_area,
            $floor_location,
            $total_floors,
            $heating,
            $bathroom_count,
            $balcony,
            $furnished,
            $site_status,
            $eligible_for_credit,
            $building_age,
            $living_room,
            $parking,
            $usage_status,
            $video_call_available,
            $video_file,
            $id
        );
    }

    if ($stmt->execute()) {
        // Yeni resimler yüklendiyse ekle
        if (isset($_FILES["images"]) && !empty($_FILES["images"]["name"][0])) {
            $target_dir = "../uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $total = count($_FILES["images"]["name"]);
            
            for($i = 0; $i < $total; $i++) {
                if($_FILES["images"]["error"][$i] == 0) {
                    $imageFileType = strtolower(pathinfo($_FILES["images"]["name"][$i], PATHINFO_EXTENSION));
                    
                    if($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png" || $imageFileType == "gif") {
                        $unique_name = time() . '_' . uniqid() . '.' . $imageFileType;
                        $target_file = $target_dir . $unique_name;
                        
                        $check = getimagesize($_FILES["images"]["tmp_name"][$i]);
                        if($check !== false && move_uploaded_file($_FILES["images"]["tmp_name"][$i], $target_file)) {
                            $img_stmt = $conn->prepare("INSERT INTO property_images (property_id, image_name) VALUES (?, ?)");
                            $img_stmt->bind_param("is", $id, $unique_name);
                            $img_stmt->execute();
                        }
                    }
                }
            }
        }
        
        // Vitrin fotoğrafını güncelle
        if (isset($_POST['featured_image'])) {
            $reset_stmt = $conn->prepare("UPDATE property_images SET is_featured = FALSE WHERE property_id = ?");
            $reset_stmt->bind_param("i", $id);
            $reset_stmt->execute();
            
            $featured_image_id = $_POST['featured_image'];
            $feature_stmt = $conn->prepare("UPDATE property_images SET is_featured = TRUE WHERE id = ? AND property_id = ?");
            $feature_stmt->bind_param("ii", $featured_image_id, $id);
            $feature_stmt->execute();
        }

        $_SESSION['success'] = "İlan başarıyla güncellendi.";
        header("Location: edit-property.php?id=" . $id);
        exit;
    } else {
        $error = "İlan güncellenirken bir hata oluştu.";
    }
}

// Mevcut resimleri getir
$images_stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY id ASC");
if (!$images_stmt) {
    throw new Exception("Fotoğraf sorgusu hazırlanamadı: " . $conn->error);
}
$images_stmt->bind_param("i", $id);
$images_stmt->execute();
$images = $images_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Düzenle - Admin Panel</title>
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
                        <h3 class="mb-0">İlan Düzenle</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" class="property-form">
                            <div class="mb-3">
                                <label for="title" class="form-label">İlan Başlığı</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Fiyat (TL)</label>
                                    <input type="text" class="form-control" id="price" name="price" value="<?php echo number_format($property['price'], 0, ',', '.'); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="net_area" class="form-label">Alan (m²)</label>
                                    <input type="number" class="form-control" id="net_area" name="net_area" value="<?php echo htmlspecialchars($property['net_area']); ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="price_per_sqm" class="form-label">m² Birim Fiyatı (TL)</label>
                                    <input type="text" class="form-control" id="price_per_sqm" name="price_per_sqm" value="<?php echo !empty($property['price_per_sqm']) ? number_format($property['price_per_sqm'], 2, ',', '.') : ''; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Durum</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Kiralık" <?php echo $property['status'] == 'Kiralık' ? 'selected' : ''; ?>>Kiralık</option>
                                        <option value="Satılık" <?php echo $property['status'] == 'Satılık' ? 'selected' : ''; ?>>Satılık</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="property_type" class="form-label">Emlak Tipi</label>
                                    <select class="form-select" id="property_type" name="property_type" required onchange="togglePropertyFields()">
                                        <option value="">Seçiniz</option>
                                        <option value="Daire" <?php echo $property['property_type'] == 'Daire' ? 'selected' : ''; ?>>Daire</option>
                                        <option value="Villa" <?php echo $property['property_type'] == 'Villa' ? 'selected' : ''; ?>>Villa</option>
                                        <option value="Müstakil Ev" <?php echo $property['property_type'] == 'Müstakil Ev' ? 'selected' : ''; ?>>Müstakil Ev</option>
                                        <option value="Arsa" <?php echo $property['property_type'] == 'Arsa' ? 'selected' : ''; ?>>Arsa</option>
                                        <option value="İş Yeri" <?php echo $property['property_type'] == 'İş Yeri' ? 'selected' : ''; ?>>İş Yeri</option>
                                    </select>
                                </div>
                                <?php if ($property['property_type'] !== 'Arsa'): ?>
                                <div class="col-md-6">
                                    <label for="gross_area" class="form-label">Alan (m²) (Brüt)</label>
                                    <input type="number" class="form-control" id="gross_area" name="gross_area" value="<?php echo htmlspecialchars($property['gross_area']); ?>">
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Arsa özellikleri -->
                            <div id="landFields" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="zoning_status" class="form-label">İmar Durumu</label>
                                        <select class="form-select" id="zoning_status" name="zoning_status" required>
                                            <option value="">Seçiniz</option>
                                            <option value="Konut İmarlı" <?php echo $property['zoning_status'] == 'Konut İmarlı' ? 'selected' : ''; ?>>Konut İmarlı</option>
                                            <option value="Ticari İmarlı" <?php echo $property['zoning_status'] == 'Ticari İmarlı' ? 'selected' : ''; ?>>Ticari İmarlı</option>
                                            <option value="Turizm İmarlı" <?php echo $property['zoning_status'] == 'Turizm İmarlı' ? 'selected' : ''; ?>>Turizm İmarlı</option>
                                            <option value="Sanayi İmarlı" <?php echo $property['zoning_status'] == 'Sanayi İmarlı' ? 'selected' : ''; ?>>Sanayi İmarlı</option>
                                            <option value="Tarla" <?php echo $property['zoning_status'] == 'Tarla' ? 'selected' : ''; ?>>Tarla</option>
                                            <option value="Bağ & Bahçe" <?php echo $property['zoning_status'] == 'Bağ & Bahçe' ? 'selected' : ''; ?>>Bağ & Bahçe</option>
                                            <option value="Zeytinlik" <?php echo $property['zoning_status'] == 'Zeytinlik' ? 'selected' : ''; ?>>Zeytinlik</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="block_no" class="form-label">Ada No</label>
                                        <input type="text" class="form-control" id="block_no" name="block_no" value="<?php echo htmlspecialchars($property['block_no']); ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="parcel_no" class="form-label">Parsel No</label>
                                        <input type="text" class="form-control" id="parcel_no" name="parcel_no" value="<?php echo htmlspecialchars($property['parcel_no']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sheet_no" class="form-label">Pafta No</label>
                                        <input type="text" class="form-control" id="sheet_no" name="sheet_no" value="<?php echo htmlspecialchars($property['sheet_no']); ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="floor_area_ratio" class="form-label">Kaks (Emsal)</label>
                                        <input type="text" class="form-control" id="floor_area_ratio" name="floor_area_ratio" value="<?php echo htmlspecialchars($property['floor_area_ratio']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="height_limit" class="form-label">Gabari</label>
                                        <input type="text" class="form-control" id="height_limit" name="height_limit" value="<?php echo htmlspecialchars($property['height_limit']); ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="deed_status" class="form-label">Tapu Durumu</label>
                                        <select class="form-select" id="deed_status" name="deed_status">
                                            <option value="">Seçiniz</option>
                                            <option value="Müstakil" <?php echo $property['deed_status'] == 'Müstakil' ? 'selected' : ''; ?>>Müstakil</option>
                                            <option value="Hisseli" <?php echo $property['deed_status'] == 'Hisseli' ? 'selected' : ''; ?>>Hisseli</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Konut özellikleri -->
                            <div id="residentialFields">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="beds" class="form-label">Oda Sayısı</label>
                                        <input type="number" class="form-control" id="beds" name="beds" min="0" value="<?php echo htmlspecialchars($property['beds']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="living_room" class="form-label">Salon Sayısı</label>
                                        <input type="number" class="form-control" id="living_room" name="living_room" min="0" value="<?php echo isset($property['living_room']) ? htmlspecialchars($property['living_room']) : ''; ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="bathroom_count" class="form-label">Banyo Sayısı</label>
                                        <input type="number" class="form-control" id="bathroom_count" name="bathroom_count" value="<?php echo htmlspecialchars($property['bathroom_count']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="building_age" class="form-label">Bina Yaşı</label>
                                        <select class="form-select" id="building_age" name="building_age">
                                            <option value="">Seçiniz</option>
                                            <option value="0" <?php echo ($property['building_age'] === '0' || $property['building_age'] === 0) ? 'selected' : ''; ?>>0 (Yeni)</option>
                                            <option value="1" <?php echo $property['building_age'] == '1' ? 'selected' : ''; ?>>1</option>
                                            <option value="2" <?php echo $property['building_age'] == '2' ? 'selected' : ''; ?>>2</option>
                                            <option value="3" <?php echo $property['building_age'] == '3' ? 'selected' : ''; ?>>3</option>
                                            <option value="4" <?php echo $property['building_age'] == '4' ? 'selected' : ''; ?>>4</option>
                                            <option value="5" <?php echo $property['building_age'] == '5' ? 'selected' : ''; ?>>5</option>
                                            <option value="6" <?php echo $property['building_age'] == '6' ? 'selected' : ''; ?>>6</option>
                                            <option value="7" <?php echo $property['building_age'] == '7' ? 'selected' : ''; ?>>7</option>
                                            <option value="8" <?php echo $property['building_age'] == '8' ? 'selected' : ''; ?>>8</option>
                                            <option value="9" <?php echo $property['building_age'] == '9' ? 'selected' : ''; ?>>9</option>
                                            <option value="10" <?php echo $property['building_age'] == '10' ? 'selected' : ''; ?>>10</option>
                                            <option value="11-15" <?php echo ($property['building_age'] == '11-15' || ($property['building_age'] >= 11 && $property['building_age'] <= 15)) ? 'selected' : ''; ?>>11-15</option>
                                            <option value="16-20" <?php echo ($property['building_age'] == '16-20' || ($property['building_age'] >= 16 && $property['building_age'] <= 20)) ? 'selected' : ''; ?>>16-20</option>
                                            <option value="21-25" <?php echo ($property['building_age'] == '21-25' || ($property['building_age'] >= 21 && $property['building_age'] <= 25)) ? 'selected' : ''; ?>>21-25</option>
                                            <option value="26+" <?php echo ($property['building_age'] == '26+' || $property['building_age'] >= 26) ? 'selected' : ''; ?>>26+</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="floor_location" class="form-label">Bulunduğu Kat</label>
                                        <select class="form-select" id="floor_location" name="floor_location">
                                            <option value="">Seçiniz</option>
                                            <option value="Bodrum Kat" <?php echo $property['floor_location'] == 'Bodrum Kat' ? 'selected' : ''; ?>>Bodrum Kat</option>
                                            <option value="Yarı Bodrum Kat" <?php echo $property['floor_location'] == 'Yarı Bodrum Kat' ? 'selected' : ''; ?>>Yarı Bodrum Kat</option>
                                            <option value="Zemin Kat" <?php echo $property['floor_location'] == 'Zemin Kat' ? 'selected' : ''; ?>>Zemin Kat</option>
                                            <option value="Bahçe Katı" <?php echo $property['floor_location'] == 'Bahçe Katı' ? 'selected' : ''; ?>>Bahçe Katı</option>
                                            <option value="Yüksek Giriş" <?php echo $property['floor_location'] == 'Yüksek Giriş' ? 'selected' : ''; ?>>Yüksek Giriş</option>
                                            <option value="1. Kat" <?php echo $property['floor_location'] == '1. Kat' ? 'selected' : ''; ?>>1. Kat</option>
                                            <option value="2. Kat" <?php echo $property['floor_location'] == '2. Kat' ? 'selected' : ''; ?>>2. Kat</option>
                                            <option value="3. Kat" <?php echo $property['floor_location'] == '3. Kat' ? 'selected' : ''; ?>>3. Kat</option>
                                            <option value="4. Kat" <?php echo $property['floor_location'] == '4. Kat' ? 'selected' : ''; ?>>4. Kat</option>
                                            <option value="5. Kat" <?php echo $property['floor_location'] == '5. Kat' ? 'selected' : ''; ?>>5. Kat</option>
                                            <option value="6. Kat" <?php echo $property['floor_location'] == '6. Kat' ? 'selected' : ''; ?>>6. Kat</option>
                                            <option value="7. Kat" <?php echo $property['floor_location'] == '7. Kat' ? 'selected' : ''; ?>>7. Kat</option>
                                            <option value="8. Kat" <?php echo $property['floor_location'] == '8. Kat' ? 'selected' : ''; ?>>8. Kat</option>
                                            <option value="9. Kat" <?php echo $property['floor_location'] == '9. Kat' ? 'selected' : ''; ?>>9. Kat</option>
                                            <option value="10. Kat" <?php echo $property['floor_location'] == '10. Kat' ? 'selected' : ''; ?>>10. Kat</option>
                                            <option value="11. Kat" <?php echo $property['floor_location'] == '11. Kat' ? 'selected' : ''; ?>>11. Kat</option>
                                            <option value="12. Kat ve üzeri" <?php echo $property['floor_location'] == '12. Kat ve üzeri' ? 'selected' : ''; ?>>12. Kat ve üzeri</option>
                                            <option value="Çatı Katı" <?php echo $property['floor_location'] == 'Çatı Katı' ? 'selected' : ''; ?>>Çatı Katı</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="total_floors" class="form-label">Kat Sayısı</label>
                                        <input type="number" class="form-control" id="total_floors" name="total_floors" value="<?php echo $property['total_floors']; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="heating" class="form-label">Isıtma</label>
                                        <select class="form-select" id="heating" name="heating">
                                            <option value="">Seçiniz</option>
                                            <option value="Kombi (Doğalgaz)" <?php echo ($property['heating'] == 'Kombi (Doğalgaz)') ? 'selected' : ''; ?>>Kombi (Doğalgaz)</option>
                                            <option value="Merkezi" <?php echo ($property['heating'] == 'Merkezi') ? 'selected' : ''; ?>>Merkezi</option>
                                            <option value="Klima" <?php echo ($property['heating'] == 'Klima') ? 'selected' : ''; ?>>Klima</option>
                                            <option value="Yerden Isıtma" <?php echo ($property['heating'] == 'Yerden Isıtma') ? 'selected' : ''; ?>>Yerden Isıtma</option>
                                            <option value="Soba" <?php echo ($property['heating'] == 'Soba') ? 'selected' : ''; ?>>Soba</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="balcony" class="form-label">Balkon</label>
                                        <select class="form-select" id="balcony" name="balcony">
                                            <option value="Var" <?php echo $property['balcony'] == 'Var' ? 'selected' : ''; ?>>Var</option>
                                            <option value="Yok" <?php echo $property['balcony'] == 'Yok' ? 'selected' : ''; ?>>Yok</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="furnished" class="form-label">Eşyalı</label>
                                        <select class="form-select" id="furnished" name="furnished">
                                            <option value="">Seçiniz</option>
                                            <option value="Evet" <?php echo isset($property['furnished']) && $property['furnished'] == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                                            <option value="Hayır" <?php echo isset($property['furnished']) && $property['furnished'] == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="site_status" class="form-label">Site İçerisinde</label>
                                        <select class="form-select" id="site_status" name="site_status">
                                            <option value="">Seçiniz</option>
                                            <option value="Evet" <?php echo isset($property['site_status']) && $property['site_status'] == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                                            <option value="Hayır" <?php echo isset($property['site_status']) && $property['site_status'] == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Konum</label>
                                    <input type="text" class="form-control" id="location" name="location" value="Didim" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="neighborhood" class="form-label">Mahalle</label>
                                    <select class="form-select" id="neighborhood" name="neighborhood" required>
                                        <option value="">Mahalle Seçiniz</option>
                                        <option value="Ak-yeniköy Mah." <?php echo $property['neighborhood'] == 'Ak-yeniköy Mah.' ? 'selected' : ''; ?>>Ak-yeniköy Mah.</option>
                                        <option value="Akbük Mah." <?php echo $property['neighborhood'] == 'Akbük Mah.' ? 'selected' : ''; ?>>Akbük Mah.</option>
                                        <option value="Akköy Mah." <?php echo $property['neighborhood'] == 'Akköy Mah.' ? 'selected' : ''; ?>>Akköy Mah.</option>
                                        <option value="Altınkum Mah." <?php echo $property['neighborhood'] == 'Altınkum Mah.' ? 'selected' : ''; ?>>Altınkum Mah.</option>
                                        <option value="Balat Mah." <?php echo $property['neighborhood'] == 'Balat Mah.' ? 'selected' : ''; ?>>Balat Mah.</option>
                                        <option value="Batıköy Mah." <?php echo $property['neighborhood'] == 'Batıköy Mah.' ? 'selected' : ''; ?>>Batıköy Mah.</option>
                                        <option value="Cumhuriyet Mah." <?php echo $property['neighborhood'] == 'Cumhuriyet Mah.' ? 'selected' : ''; ?>>Cumhuriyet Mah.</option>
                                        <option value="Çamlık Mah." <?php echo $property['neighborhood'] == 'Çamlık Mah.' ? 'selected' : ''; ?>>Çamlık Mah.</option>
                                        <option value="Denizköy Mah." <?php echo $property['neighborhood'] == 'Denizköy Mah.' ? 'selected' : ''; ?>>Denizköy Mah.</option>
                                        <option value="Efeler Mah." <?php echo $property['neighborhood'] == 'Efeler Mah.' ? 'selected' : ''; ?>>Efeler Mah.</option>
                                        <option value="Fevzipaşa Mah." <?php echo $property['neighborhood'] == 'Fevzipaşa Mah.' ? 'selected' : ''; ?>>Fevzipaşa Mah.</option>
                                        <option value="Hisar Mah." <?php echo $property['neighborhood'] == 'Hisar Mah.' ? 'selected' : ''; ?>>Hisar Mah.</option>
                                        <option value="Mavişehir Mah." <?php echo $property['neighborhood'] == 'Mavişehir Mah.' ? 'selected' : ''; ?>>Mavişehir Mah.</option>
                                        <option value="Mersindere Mah." <?php echo $property['neighborhood'] == 'Mersindere Mah.' ? 'selected' : ''; ?>>Mersindere Mah.</option>
                                        <option value="Yalıköy Mah." <?php echo $property['neighborhood'] == 'Yalıköy Mah.' ? 'selected' : ''; ?>>Yalıköy Mah.</option>
                                        <option value="Yeni Mah." <?php echo $property['neighborhood'] == 'Yeni Mah.' ? 'selected' : ''; ?>>Yeni Mah.</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="eligible_for_credit" class="form-label">Krediye Uygun</label>
                                    <select class="form-select" id="eligible_for_credit" name="eligible_for_credit">
                                        <option value="Evet" <?php echo $property['eligible_for_credit'] == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                                        <option value="Hayır" <?php echo $property['eligible_for_credit'] == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="usage_status" class="form-label">Kullanım Durumu</label>
                                    <select class="form-select" id="usage_status" name="usage_status" required>
                                        <option value="Boş" <?php echo ($property['usage_status'] == 'Boş') ? 'selected' : ''; ?>>Boş</option>
                                        <option value="Kiracılı" <?php echo ($property['usage_status'] == 'Kiracılı') ? 'selected' : ''; ?>>Kiracılı</option>
                                        <option value="Mülk Sahibi" <?php echo ($property['usage_status'] == 'Mülk Sahibi') ? 'selected' : ''; ?>>Mülk Sahibi</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="video_call_available" class="form-label">Görüntülü Arama</label>
                                    <select class="form-select" id="video_call_available" name="video_call_available" required>
                                        <option value="Evet" <?php echo ($property['video_call_available'] == 'Evet') ? 'selected' : ''; ?>>Evet</option>
                                        <option value="Hayır" <?php echo ($property['video_call_available'] == 'Hayır') ? 'selected' : ''; ?>>Hayır</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                            </div>

                            <!-- Mevcut Resimler -->
                            <?php if (!empty($images)): ?>
                            <div class="mb-3">
                                <label class="form-label">Mevcut Resimler</label>
                                <div class="row">
                                    <?php foreach ($images as $image): ?>
                                    <div class="col-md-3 mb-2">
                                        <div class="position-relative">
                                            <img src="../uploads/<?php echo htmlspecialchars($image['image_name']); ?>" 
                                                 class="img-thumbnail" 
                                                 alt="Property Image">
                                            <div class="d-flex position-absolute top-0 end-0">
                                                <a href="delete-image.php?id=<?php echo $image['id']; ?>&property_id=<?php echo $id; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Bu resmi silmek istediğinizden emin misiniz?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                            <div class="mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                           name="featured_image" 
                                                           value="<?php echo $image['id']; ?>" 
                                                           id="featured_<?php echo $image['id']; ?>"
                                                           <?php echo $image['is_featured'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="featured_<?php echo $image['id']; ?>">
                                                        Vitrin Fotoğrafı
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="images" class="form-label">Yeni Resimler</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                <small class="text-muted">Birden fazla resim seçebilirsiniz</small>
                            </div>

                            <div class="mb-4">
                                <?php if (!empty($property['video_file'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">Mevcut Video</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <video width="320" height="240" controls>
                                            <source src="../uploads/videos/<?php echo htmlspecialchars($property['video_file']); ?>" type="video/mp4">
                                            Tarayıcınız video oynatmayı desteklemiyor.
                                        </video>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_video" value="1" id="delete_video">
                                            <label class="form-check-label" for="delete_video">
                                                Videoyu Sil
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <label for="property_video" class="form-label">Yeni Video Yükle</label>
                                <input type="file" class="form-control" id="property_video" name="property_video" accept="video/mp4,video/webm,video/ogg">
                                <small class="text-muted">Desteklenen formatlar: MP4, WebM, OGG</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Değişiklikleri Kaydet</button>
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
        // Fiyat formatla
        document.getElementById('price').addEventListener('input', function(e) {
            // Sadece sayıları al
            let value = this.value.replace(/\D/g, '');
            
            // Sayıyı formatla
            if (value !== '') {
                value = parseInt(value).toLocaleString('tr-TR');
            }
            
            // Input değerini güncelle
            this.value = value;
        });

        // Form gönderilmeden önce fiyatı temizle
        document.querySelector('form').addEventListener('submit', function(e) {
            let priceInput = document.getElementById('price');
            priceInput.value = priceInput.value.replace(/\D/g, '');
        });

        // Emlak tipine göre form alanlarını göster/gizle
        function togglePropertyFields() {
            const propertyType = document.getElementById('property_type').value;
            const landFields = document.getElementById('landFields');
            const residentialFields = document.getElementById('residentialFields');

            if (propertyType === 'Arsa') {
                landFields.style.display = 'block';
                residentialFields.style.display = 'none';
            } else {
                landFields.style.display = 'none';
                residentialFields.style.display = 'block';
            }
        }

        // Sayfa yüklendiğinde form alanlarını düzenle
        document.addEventListener('DOMContentLoaded', function() {
            togglePropertyFields();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('price');
            const areaInput = document.getElementById('net_area');
            const pricePerSqmInput = document.getElementById('price_per_sqm');

            // Fiyat formatı için yardımcı fonksiyon
            function formatPrice(price) {
                return new Intl.NumberFormat('tr-TR').format(price);
            }

            // String formatındaki fiyatı sayıya çevirme
            function parseFormattedPrice(formattedPrice) {
                return parseFloat(formattedPrice.replace(/\./g, '').replace(',', '.'));
            }

            // Metrekare başına fiyatı hesapla
            function calculatePricePerSqm() {
                const price = parseFormattedPrice(priceInput.value);
                const area = parseFloat(areaInput.value);
                
                if (!isNaN(price) && !isNaN(area) && area > 0) {
                    const pricePerSqm = price / area;
                    pricePerSqmInput.value = formatPrice(pricePerSqm.toFixed(2));
                } else {
                    pricePerSqmInput.value = '';
                }
            }

            // Fiyat alanı için olay dinleyicisi
            priceInput.addEventListener('input', function(e) {
                // Sadece sayı ve virgül girişine izin ver
                let value = e.target.value.replace(/[^\d,]/g, '');
                
                // Sayıyı formatla
                const number = parseFloat(value.replace(/,/g, ''));
                if (!isNaN(number)) {
                    e.target.value = formatPrice(number);
                }
                
                calculatePricePerSqm();
            });

            // Alan alanı için olay dinleyicisi
            areaInput.addEventListener('input', calculatePricePerSqm);
        });
    </script>
</body>
</html> 