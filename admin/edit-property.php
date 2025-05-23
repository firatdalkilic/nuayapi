<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');

require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// İlan ID'sini al
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// İlanı getir
if (isAgent()) {
    $sql = "SELECT * FROM properties WHERE id = ? AND agent_id = ?";
    $stmt = $conn->prepare($sql);
    $agent_id = getAgentId();
    $stmt->bind_param("ii", $id, $agent_id);
} else {
    $sql = "SELECT * FROM properties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "İlan bulunamadı veya bu ilana erişim yetkiniz yok.";
    header("Location: dashboard.php");
    exit;
}

$property = $result->fetch_assoc();

// Debug bilgisi ekle
error_log("[DEBUG] Retrieved property data:");
error_log(" - floor_location: " . (isset($property['floor_location']) ? $property['floor_location'] : 'not set'));
error_log(" - building_age: " . (isset($property['building_age']) ? $property['building_age'] : 'not set'));
error_log(" - total_floors: " . (isset($property['total_floors']) ? $property['total_floors'] : 'not set'));
error_log(" - gross_area: " . (isset($property['gross_area']) ? $property['gross_area'] : 'not set'));

// Debug için POST verilerini kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    // Form verilerini al ve varsayılan değerler ata
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? str_replace('.', '', trim($_POST['price'])) : 0;
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $neighborhood = isset($_POST['neighborhood']) ? trim($_POST['neighborhood']) : '';
    $property_type = isset($_POST['property_type']) ? trim($_POST['property_type']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $room_count = isset($_POST['room_count']) ? (int)trim($_POST['room_count']) : 0;
    $bathroom_count = isset($_POST['bathroom_count']) ? (int)trim($_POST['bathroom_count']) : 0;
    $square_meters = isset($_POST['square_meters']) ? (float)trim($_POST['square_meters']) : 0;
    $net_area = isset($_POST['net_area']) ? (float)trim($_POST['net_area']) : 0;
    $gross_area = isset($_POST['gross_area']) && trim($_POST['gross_area']) !== '' ? (float)trim($_POST['gross_area']) : null;
    $building_age = isset($_POST['building_age']) && trim($_POST['building_age']) !== '' ? trim($_POST['building_age']) : null;
    $living_room = isset($_POST['living_room']) ? trim($_POST['living_room']) : '';
    $eligible_for_credit = isset($_POST['eligible_for_credit']) ? trim($_POST['eligible_for_credit']) : 'Hayır';

    // Arsa özellikleri
    $zoning_status = isset($_POST['zoning_status']) ? trim($_POST['zoning_status']) : '';
    $block_no = isset($_POST['block_no']) ? trim($_POST['block_no']) : '';
    $parcel_no = isset($_POST['parcel_no']) ? trim($_POST['parcel_no']) : '';
    $sheet_no = isset($_POST['sheet_no']) ? trim($_POST['sheet_no']) : '';
    $floor_area_ratio = isset($_POST['floor_area_ratio']) ? trim($_POST['floor_area_ratio']) : '';
    $height_limit = isset($_POST['height_limit']) ? trim($_POST['height_limit']) : '';
    $deed_status = isset($_POST['deed_status']) ? trim($_POST['deed_status']) : '';

    // floor_location için özel işlem
    $floor_location = isset($_POST['floor_location']) && trim($_POST['floor_location']) !== '' ? trim($_POST['floor_location']) : null;
    if ($floor_location !== null) {
        // "KAT" kelimesinin büyük harfle olduğundan emin ol
        $floor_location = str_replace(' Kat', ' KAT', $floor_location);
        $floor_location = str_replace(' kat', ' KAT', $floor_location);
    }

    $total_floors = isset($_POST['total_floors']) && trim($_POST['total_floors']) !== '' ? (int)trim($_POST['total_floors']) : null;
    $heating = isset($_POST['heating']) ? trim($_POST['heating']) : '';

    // Debug bilgisi ekle
    error_log("[DEBUG] POST data processed:");
    error_log(" - floor_location: " . ($floor_location ?? 'null'));
    error_log(" - building_age: " . ($building_age ?? 'null'));
    error_log(" - total_floors: " . ($total_floors ?? 'null'));
    error_log(" - gross_area: " . ($gross_area ?? 'null'));

    try {
        if ($property_type === 'Arsa') {
            // Arsa ilanı güncelleme sorgusu
            $sql = "UPDATE properties SET 
                title = ?, 
                description = ?, 
                price = ?, 
                location = ?, 
                neighborhood = ?, 
                property_type = ?,
                status = ?, 
                net_area = ?, 
                zoning_status = ?, 
                block_no = ?, 
                parcel_no = ?, 
                sheet_no = ?, 
                floor_area_ratio = ?, 
                height_limit = ?, 
                eligible_for_credit = ?, 
                deed_status = ?,
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssdssssdssssssssi",
                $title, 
                $description, 
                $price, 
                $location,
                $neighborhood, 
                $property_type,
                $status, 
                $net_area,
                $zoning_status, 
                $block_no, 
                $parcel_no,
                $sheet_no, 
                $floor_area_ratio, 
                $height_limit,
                $eligible_for_credit, 
                $deed_status, 
                $id
            );
        } else if ($property_type === 'İş Yeri') {
            // İş yeri ilanı güncelleme sorgusu
            $sql = "UPDATE properties SET 
                title = ?, 
                description = ?, 
                price = ?, 
                location = ?, 
                neighborhood = ?, 
                property_type = ?,
                status = ?, 
                net_area = ?, 
                floor_location = ?, 
                building_age = ?,
                room_count = ?, 
                heating = ?, 
                eligible_for_credit = ?, 
                deed_status = ?,
                updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssdsssssssisssi",
                $title, 
                $description, 
                $price, 
                $location,
                $neighborhood, 
                $property_type,
                $status, 
                $net_area,
                $floor_location, 
                $building_age,
                $room_count, 
                $heating,
                $eligible_for_credit, 
                $deed_status,
                $id
            );
        } else {
            // Diğer ilan tipleri için mevcut güncelleme sorgusu
            if (isAgent()) {
                $sql = "UPDATE properties SET 
                        title=?,             /* 1 */
                        description=?,       /* 2 */
                        price=?,            /* 3 */
                        location=?,         /* 4 */
                        neighborhood=?,     /* 5 */
                        property_type=?,    /* 6 */
                        status=?,          /* 7 */
                        room_count=?,      /* 8 */
                        bathroom_count=?,   /* 9 */
                        net_area=?,        /* 10 */
                        gross_area=?,      /* 11 */
                        living_room=?,     /* 12 */
                        building_age=?,    /* 13 */
                        eligible_for_credit=?, /* 14 */
                        floor_location=?,  /* 15 */
                        total_floors=?,    /* 16 */
                        heating=?,         /* 17 */
                        updated_at=NOW() 
                        WHERE id=? AND agent_id=?"; /* 18, 19 */
                
                $stmt = $conn->prepare($sql);
                $agent_id = getAgentId();
                
                $stmt->bind_param("sssssssiiidssssissii", 
                    $title, 
                    $description, 
                    $price, 
                    $location, 
                    $neighborhood, 
                    $property_type, 
                    $status, 
                    $room_count, 
                    $bathroom_count, 
                    $net_area, 
                    $gross_area,
                    $living_room,
                    $building_age,
                    $eligible_for_credit,
                    $floor_location,
                    $total_floors,
                    $heating,
                    $id, 
                    $agent_id
                );
            } else {
                $sql = "UPDATE properties SET 
                        title=?,             /* 1 */
                        description=?,       /* 2 */
                        price=?,            /* 3 */
                        location=?,         /* 4 */
                        neighborhood=?,     /* 5 */
                        property_type=?,    /* 6 */
                        status=?,          /* 7 */
                        room_count=?,      /* 8 */
                        bathroom_count=?,   /* 9 */
                        net_area=?,        /* 10 */
                        gross_area=?,      /* 11 */
                        living_room=?,     /* 12 */
                        building_age=?,    /* 13 */
                        eligible_for_credit=?, /* 14 */
                        floor_location=?,  /* 15 */
                        total_floors=?,    /* 16 */
                        heating=?,         /* 17 */
                        updated_at=NOW() 
                        WHERE id=?";       /* 18 */
                
                $stmt = $conn->prepare($sql);
                
                $stmt->bind_param("sssssssiidssssissi", 
                    $title, 
                    $description, 
                    $price, 
                    $location, 
                    $neighborhood, 
                    $property_type, 
                    $status, 
                    $room_count, 
                    $bathroom_count, 
                    $net_area, 
                    $gross_area,
                    $living_room,
                    $building_age,
                    $eligible_for_credit,
                    $floor_location,
                    $total_floors,
                    $heating,
                    $id
                );
            }
        }
        
        if ($stmt->execute()) {
            error_log("[DEBUG] SQL executed successfully");
            error_log("[DEBUG] Affected rows: " . $stmt->affected_rows);
            error_log("[DEBUG] SQL error (if any): " . $stmt->error);
            error_log("[DEBUG] Last floor_location value before save: " . $floor_location);
            error_log("[DEBUG] Last floor_location type before save: " . gettype($floor_location));
            error_log("[DEBUG] Last floor_location length before save: " . strlen($floor_location));
            error_log("[DEBUG] Last floor_location binary before save: " . bin2hex($floor_location));
            // Önce tüm resimlerin vitrin durumunu false yap
            $reset_featured = $conn->prepare("UPDATE property_images SET is_featured = 0 WHERE property_id = ?");
            $reset_featured->bind_param("i", $id);
            $reset_featured->execute();

            // Seçilen resmin vitrin durumunu true yap
            if (isset($_POST['featured_image'])) {
                $featured_id = $_POST['featured_image'];
                $update_featured = $conn->prepare("UPDATE property_images SET is_featured = 1 WHERE id = ? AND property_id = ?");
                $update_featured->bind_param("ii", $featured_id, $id);
                $update_featured->execute();
            }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $fileName = time() . '_' . $_FILES['images']['name'][$key];
                    $targetFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($tmp_name, $targetFile)) {
                        // Eğer hiç vitrin fotoğrafı yoksa ilk yüklenen fotoğrafı vitrin yap
                        $check_featured = $conn->prepare("SELECT COUNT(*) as count FROM property_images WHERE property_id = ? AND is_featured = 1");
                        $check_featured->bind_param("i", $id);
                        $check_featured->execute();
                        $result = $check_featured->get_result();
                        $row = $result->fetch_assoc();
                        $is_featured = ($row['count'] == 0) ? 1 : 0;
                        
                        $sql = "INSERT INTO property_images (property_id, image_name, is_featured) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("isi", $id, $fileName, $is_featured);
                        $stmt->execute();
                    }
                }
            }
            
            $_SESSION['success'] = "İlan başarıyla güncellendi.";
            header("Location: dashboard.php");
            exit;
        } else {
            error_log("[ERROR] SQL execution failed");
            error_log("[ERROR] SQL error: " . $stmt->error);
            error_log("[ERROR] SQL errno: " . $stmt->errno);
            throw new Exception("İlan güncellenirken bir hata oluştu: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("[ERROR] Exception caught: " . $e->getMessage());
        error_log("[ERROR] Exception trace: " . $e->getTraceAsString());
        $_SESSION['error'] = $e->getMessage();
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

$required_fields = [
    'title' => 'İlan Başlığı',
    'price' => 'Fiyat',
    'status' => 'Durum',
    'net_area' => 'Alan (m²)',
    'zoning_status' => 'İmar Durumu',
    'block_no' => 'Ada No',
    'parcel_no' => 'Parsel No',
    'sheet_no' => 'Pafta No',
    'floor_area_ratio' => 'Kaks (Emsal)',
    'height_limit' => 'Gabari',
    'eligible_for_credit' => 'Krediye Uygunluk',
    'deed_status' => 'Tapu Durumu',
    'description' => 'Açıklama',
    'neighborhood' => 'Mahalle'
];

$floor_options = [
    'Bodrum KAT', 'Yarı Bodrum KAT', 'Zemin KAT', 'Bahçe KAT', 'Yüksek Giriş',
    '1. KAT', '2. KAT', '3. KAT', '4. KAT', '5. KAT', '6. KAT', '7. KAT', '8. KAT',
    '9. KAT', '10. KAT', '11. KAT', '12. KAT ve üzeri', 'Çatı KAT'
];

// Form verilerini al
$floor_location = isset($_POST['floor_location']) ? trim($_POST['floor_location']) : '';

// Sadece geçerli floor_options değerlerini kabul et
if (!empty($floor_location) && !in_array($floor_location, $floor_options, true)) {
    $floor_location = ''; // Geçersiz değeri temizle
}

error_log('Floor Location (Kaydetmeden önce): ' . $floor_location);
error_log('Floor Location Tipi: ' . gettype($floor_location));

// Hosting URL'sini tanımla
define('HOSTING_URL', 'https://nuayapi.com.tr');

// Upload dizinini tanımla
$uploadDir = dirname(__DIR__) . "/uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
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
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id; ?>" enctype="multipart/form-data" class="property-form">
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
                                    <label for="net_area" class="form-label">Net Metrekare (m²)</label>
                                    <input type="number" class="form-control" id="net_area" name="net_area" 
                                           min="0" step="0.1" required 
                                           value="<?php echo isset($property['net_area']) && $property['net_area'] > 0 ? number_format((float)$property['net_area'], 1, '.', '') : ''; ?>"
                                           placeholder="Örn: 85.5">
                                    <small class="text-muted">Net kullanım alanını giriniz</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Durum</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="rent" <?php echo $property['status'] == 'rent' ? 'selected' : ''; ?>>Kiralık</option>
                                        <option value="sale" <?php echo $property['status'] == 'sale' ? 'selected' : ''; ?>>Satılık</option>
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
                            </div>

                            <!-- Arsa özellikleri -->
                            <div id="landFields" style="display: <?php echo $property['property_type'] === 'Arsa' ? 'block' : 'none'; ?>">
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

                            <!-- İş yeri özellikleri -->
                            <div id="workplaceFields" style="display: <?php echo $property['property_type'] === 'İş Yeri' ? 'block' : 'none'; ?>">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="room_count" class="form-label">Bölüm & Oda Sayısı</label>
                                        <input type="number" class="form-control" id="room_count" name="room_count" value="<?php echo htmlspecialchars($property['room_count']); ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="floor_location" class="form-label">Bulunduğu Kat</label>
                                        <select class="form-select" id="floor_location" name="floor_location">
                                            <option value="">Seçiniz...</option>
                                            <?php
                                            foreach ($floor_options as $option) {
                                                $selected = isset($property['floor_location']) && trim($property['floor_location']) === $option ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="building_age" class="form-label">Bina Yaşı</label>
                                        <select class="form-select" id="building_age" name="building_age">
                                            <option value="">Seçiniz...</option>
                                            <option value="0" <?php echo ($property['building_age'] === '0') ? 'selected' : ''; ?>>0 (Yeni)</option>
                                            <option value="1" <?php echo ($property['building_age'] === '1') ? 'selected' : ''; ?>>1</option>
                                            <option value="2" <?php echo ($property['building_age'] === '2') ? 'selected' : ''; ?>>2</option>
                                            <option value="3" <?php echo ($property['building_age'] === '3') ? 'selected' : ''; ?>>3</option>
                                            <option value="4" <?php echo ($property['building_age'] === '4') ? 'selected' : ''; ?>>4</option>
                                            <option value="5" <?php echo ($property['building_age'] === '5') ? 'selected' : ''; ?>>5</option>
                                            <option value="6" <?php echo ($property['building_age'] === '6') ? 'selected' : ''; ?>>6</option>
                                            <option value="7" <?php echo ($property['building_age'] === '7') ? 'selected' : ''; ?>>7</option>
                                            <option value="8" <?php echo ($property['building_age'] === '8') ? 'selected' : ''; ?>>8</option>
                                            <option value="9" <?php echo ($property['building_age'] === '9') ? 'selected' : ''; ?>>9</option>
                                            <option value="10" <?php echo ($property['building_age'] === '10') ? 'selected' : ''; ?>>10</option>
                                            <option value="11-15" <?php echo ($property['building_age'] === '11-15') ? 'selected' : ''; ?>>11-15</option>
                                            <option value="16-20" <?php echo ($property['building_age'] === '16-20') ? 'selected' : ''; ?>>16-20</option>
                                            <option value="21-25" <?php echo ($property['building_age'] === '21-25') ? 'selected' : ''; ?>>21-25</option>
                                            <option value="26+" <?php echo ($property['building_age'] === '26+') ? 'selected' : ''; ?>>26+</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="heating" class="form-label">Isıtma</label>
                                        <select class="form-select" id="heating" name="heating">
                                            <option value="">Seçiniz...</option>
                                            <?php
                                            $heating_types = ["Doğalgaz", "Merkezi", "Klima", "Soba", "Yok"];
                                            foreach ($heating_types as $type) {
                                                $selected = ($property['heating'] === $type) ? 'selected' : '';
                                                echo "<option value=\"$type\" $selected>$type</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="deed_status" class="form-label">Tapu Durumu</label>
                                        <select class="form-select" id="deed_status" name="deed_status">
                                            <option value="">Seçiniz...</option>
                                            <option value="Kat Mülkiyetli" <?php echo ($property['deed_status'] === 'Kat Mülkiyetli') ? 'selected' : ''; ?>>Kat Mülkiyetli</option>
                                            <option value="Kat İrtifaklı" <?php echo ($property['deed_status'] === 'Kat İrtifaklı') ? 'selected' : ''; ?>>Kat İrtifaklı</option>
                                            <option value="Müstakil Tapulu" <?php echo ($property['deed_status'] === 'Müstakil Tapulu') ? 'selected' : ''; ?>>Müstakil Tapulu</option>
                                            <option value="Hisseli Tapulu" <?php echo ($property['deed_status'] === 'Hisseli Tapulu') ? 'selected' : ''; ?>>Hisseli Tapulu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Konut özellikleri -->
                            <div id="residentialFields">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="room_count" class="form-label">Oda Sayısı</label>
                                        <input type="number" class="form-control" id="room_count" name="room_count" value="<?php echo htmlspecialchars($property['room_count']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bathroom_count" class="form-label">Banyo Sayısı</label>
                                        <input type="number" class="form-control" id="bathroom_count" name="bathroom_count" value="<?php echo htmlspecialchars($property['bathroom_count']); ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="living_room" class="form-label">Salon Sayısı</label>
                                        <input type="number" class="form-control" id="living_room" name="living_room" min="0" value="<?php echo isset($property['living_room']) ? htmlspecialchars($property['living_room']) : ''; ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
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
                                            <?php
                                            foreach ($floor_options as $option) {
                                                $selected = isset($property['floor_location']) && trim($property['floor_location']) === $option ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars($option) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="total_floors" class="form-label">Kat Sayısı</label>
                                        <input type="number" class="form-control" id="total_floors" name="total_floors" 
                                               value="<?php echo isset($property['total_floors']) ? htmlspecialchars($property['total_floors']) : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="heating" class="form-label">Isıtma</label>
                                        <select class="form-select" id="heating" name="heating" required>
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
                                <div class="col-md-6">
                                    <label for="eligible_for_credit" class="form-label">Krediye Uygun</label>
                                    <select class="form-select" id="eligible_for_credit" name="eligible_for_credit">
                                        <option value="Evet" <?php echo $property['eligible_for_credit'] == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                                        <option value="Hayır" <?php echo $property['eligible_for_credit'] == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                                    </select>
                                </div>
                                <?php if ($property['property_type'] !== 'Arsa'): ?>
                                <div class="col-md-6">
                                    <label for="usage_status" class="form-label">Kullanım Durumu</label>
                                    <select class="form-select" id="usage_status" name="usage_status" required>
                                        <option value="Boş" <?php echo ($property['usage_status'] == 'Boş') ? 'selected' : ''; ?>>Boş</option>
                                        <option value="Kiracılı" <?php echo ($property['usage_status'] == 'Kiracılı') ? 'selected' : ''; ?>>Kiracılı</option>
                                        <option value="Mülk Sahibi" <?php echo ($property['usage_status'] == 'Mülk Sahibi') ? 'selected' : ''; ?>>Mülk Sahibi</option>
                                    </select>
                                </div>
                                <?php else: ?>
                                <input type="hidden" name="usage_status" value="NULL">
                                <?php endif; ?>
                            </div>

                            <?php if ($property['property_type'] !== 'Arsa'): ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="video_call_available" class="form-label">Görüntülü Arama</label>
                                    <select class="form-select" id="video_call_available" name="video_call_available" required>
                                        <option value="Evet" <?php echo ($property['video_call_available'] == 'Evet') ? 'selected' : ''; ?>>Evet</option>
                                        <option value="Hayır" <?php echo ($property['video_call_available'] == 'Hayır') ? 'selected' : ''; ?>>Hayır</option>
                                    </select>
                                </div>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="video_call_available" value="Hayır">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                            </div>

                            <!-- Mevcut Resimler -->
                            <div class="mb-3">
                                <label class="form-label">Mevcut Resimler</label>
                                <div class="row">
                                    <?php foreach ($images as $image): ?>
                                    <div class="col-md-3 mb-2 image-container">
                                        <div class="position-relative">
                                            <img src="../uploads/<?php echo htmlspecialchars($image['image_name']); ?>" 
                                                 class="img-thumbnail" 
                                                 alt="Property Image">
                                            <div class="position-absolute top-0 end-0 d-flex gap-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="featured_image" 
                                                           value="<?php echo $image['id']; ?>" 
                                                           <?php echo $image['is_featured'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label small text-white">
                                                        Vitrin
                                                    </label>
                                                </div>
                                                <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                                        data-image-id="<?php echo $image['id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Yeni Resimler -->
                            <div class="mb-3 image-upload-container">
                                <label for="images" class="form-label">Yeni Resimler</label>
                                <input type="file" class="form-control property-image-input" id="images" 
                                       name="images[]" accept="image/*" multiple>
                                <div class="image-preview-container mt-2 d-flex flex-wrap"></div>
                                <small class="text-muted">Birden fazla resim seçebilirsiniz</small>
                            </div>

                            <div class="mb-4">
                                <?php if (!empty($property['video_file'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">Mevcut Video</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <video width="320" height="240" controls>
                                            <source src="<?php echo HOSTING_URL; ?>/uploads/videos/<?php echo htmlspecialchars($property['video_file']); ?>" type="video/mp4">
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
    <script src="assets/js/main.js"></script>
    <script src="assets/js/image-preview.js"></script>
    <script>
        console.log('JavaScript yüklendi');
        
        // Form elementini bul
        const form = document.querySelector('form');
        console.log('Form elementi:', form);
        
        // Emlak tipine göre form alanlarını göster/gizle
        function togglePropertyFields() {
            const propertyType = document.getElementById('property_type').value;
            const landFields = document.getElementById('landFields');
            const residentialFields = document.getElementById('residentialFields');
            const workplaceFields = document.getElementById('workplaceFields');
            const zoningStatus = document.getElementById('zoning_status');

            console.log('Seçilen emlak tipi:', propertyType);

            // Tüm alanları gizle
            if (residentialFields) residentialFields.style.display = 'none';
            if (landFields) landFields.style.display = 'none';
            if (workplaceFields) workplaceFields.style.display = 'none';

            // Seçilen tipe göre ilgili alanları göster
            if (propertyType === 'Arsa') {
                if (landFields) landFields.style.display = 'block';
                if (zoningStatus) zoningStatus.required = true;
            } else {
                if (zoningStatus) zoningStatus.required = false;
                if (propertyType === 'İş Yeri') {
                    if (workplaceFields) workplaceFields.style.display = 'block';
                } else {
                    if (residentialFields) residentialFields.style.display = 'block';
                }
            }

            // Gizli alanların required özelliğini kaldır
            if (landFields && landFields.style.display === 'none') {
                landFields.querySelectorAll('[required]').forEach(el => el.required = false);
            }
            if (workplaceFields && workplaceFields.style.display === 'none') {
                workplaceFields.querySelectorAll('[required]').forEach(el => el.required = false);
            }
            if (residentialFields && residentialFields.style.display === 'none') {
                residentialFields.querySelectorAll('[required]').forEach(el => el.required = false);
            }
        }

        if (form) {
            // Submit olayını dinle
            form.addEventListener('submit', function(e) {
                console.log('Form submit olayı tetiklendi');
                
                // Form verilerini al
                const formData = new FormData(this);
                
                // Fiyat alanını temizle
                let priceInput = document.getElementById('price');
                if (priceInput) {
                    let cleanPrice = priceInput.value.replace(/\./g, '');
                    formData.set('price', cleanPrice);
                }
                
                // Form verilerini konsola yazdır
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
            });
        } else {
            console.error('Form elementi bulunamadı');
        }

        // Fiyat formatı için yardımcı fonksiyon
        function formatPrice(price) {
            return new Intl.NumberFormat('tr-TR').format(price);
        }

        // Fiyat alanını formatla
        const priceInput = document.getElementById('price');
        if (priceInput) {
            priceInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                if (value !== '') {
                    this.value = formatPrice(parseInt(value));
                }
            });

            // Sayfa yüklendiğinde fiyatı formatla
            if (priceInput.value) {
                priceInput.value = formatPrice(parseInt(priceInput.value.replace(/\./g, '')));
            }
        }

        // Sayfa yüklendiğinde ve property_type değeri değiştiğinde çalıştır
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Sayfa yüklendi');
            togglePropertyFields();
            
            const propertyTypeSelect = document.getElementById('property_type');
            if (propertyTypeSelect) {
                propertyTypeSelect.addEventListener('change', togglePropertyFields);
            }
        });
    </script>
</body>
</html> 