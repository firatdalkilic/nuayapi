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

error_log("[DEBUG] Retrieved property floor_location: " . (isset($property['floor_location']) ? $property['floor_location'] : 'not set'));
error_log("[DEBUG] Retrieved property floor_location type: " . (isset($property['floor_location']) ? gettype($property['floor_location']) : 'undefined'));

// Debug için POST verilerini kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));

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
    $net_area = isset($_POST['net_area']) ? (float)trim($_POST['net_area']) : 0;
    $gross_area = isset($_POST['gross_area']) ? (float)trim($_POST['gross_area']) : null;
    $building_age = isset($_POST['building_age']) ? trim($_POST['building_age']) : '';
    $living_room = isset($_POST['living_room']) ? trim($_POST['living_room']) : '';
    $eligible_for_credit = isset($_POST['eligible_for_credit']) ? trim($_POST['eligible_for_credit']) : 'Hayır';
    $floor_location = isset($_POST['floor_location']) ? trim($_POST['floor_location']) : '';
    error_log("[DEBUG] POST floor_location value: " . (isset($_POST['floor_location']) ? $_POST['floor_location'] : 'not set'));
    error_log("[DEBUG] floor_location after trim: " . $floor_location);
    error_log("[DEBUG] floor_location type: " . gettype($floor_location));
    error_log("[DEBUG] floor_location length: " . strlen($floor_location));
    error_log("[DEBUG] floor_location binary: " . bin2hex($floor_location));
    $total_floors = isset($_POST['total_floors']) ? (int)trim($_POST['total_floors']) : 0;
    $heating = isset($_POST['heating']) ? trim($_POST['heating']) : '';
    
    // İlanı güncelle
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
        
        // Debug için parametre değerlerini kontrol et
        $params = [
            $title,              /* 1 - s */
            $description,        /* 2 - s */
            $price,             /* 3 - s */
            $location,          /* 4 - s */
            $neighborhood,      /* 5 - s */
            $property_type,     /* 6 - s */
            $status,           /* 7 - s */
            $room_count,       /* 8 - i */
            $bathroom_count,   /* 9 - i */
            $net_area,        /* 10 - i */
            $gross_area,      /* 11 - d */
            $living_room,     /* 12 - s */
            $building_age,    /* 13 - s */
            $eligible_for_credit, /* 14 - s */
            $floor_location,  /* 15 - s */
            $total_floors,    /* 16 - i */
            $heating,         /* 17 - s */
            $id,             /* 18 - i */
            $agent_id        /* 19 - i */
        ];
        error_log("Agent SQL - Number of parameters: " . count($params));
        error_log("Agent SQL - Parameters: " . print_r($params, true));
        
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
        
        // Debug için parametre değerlerini kontrol et
        $params = [
            $title,              /* 1 - s */
            $description,        /* 2 - s */
            $price,             /* 3 - s */
            $location,          /* 4 - s */
            $neighborhood,      /* 5 - s */
            $property_type,     /* 6 - s */
            $status,           /* 7 - s */
            $room_count,       /* 8 - i */
            $bathroom_count,   /* 9 - i */
            $net_area,        /* 10 - i */
            $gross_area,      /* 11 - d */
            $living_room,     /* 12 - s */
            $building_age,    /* 13 - s */
            $eligible_for_credit, /* 14 - s */
            $floor_location,  /* 15 - s */
            $total_floors,    /* 16 - i */
            $heating,         /* 17 - s */
            $id              /* 18 - i */
        ];
        error_log("Non-agent SQL - Number of parameters: " . count($params));
        error_log("Non-agent SQL - Parameters: " . print_r($params, true));
        
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
    
    if ($stmt->execute()) {
        error_log("[DEBUG] SQL executed successfully");
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
        error_log("[DEBUG] SQL execution failed: " . $stmt->error);
        $_SESSION['error'] = "İlan güncellenirken bir hata oluştu.";
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
                                    <label for="net_area" class="form-label">Alan (m²)</label>
                                    <input type="number" class="form-control" id="net_area" name="net_area" value="<?php echo htmlspecialchars($property['net_area']); ?>" required>
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
                                        <?php
                                        // Debug: Mevcut floor_location değerini kontrol et
                                        error_log("Current floor_location value: " . print_r($property['floor_location'], true));
                                        error_log("Current floor_location type: " . gettype($property['floor_location']));
                                        ?>
                                        <select class="form-select" id="floor_location" name="floor_location" onchange="console.log('Selected floor:', this.value);">
                                            <option value="">Seçiniz</option>
                                            <?php
                                            foreach ($floor_options as $option) {
                                                $selected = trim($property['floor_location']) === trim($option) ? 'selected' : '';
                                                echo "<option value=\"$option\" $selected>$option</option>";
                                                error_log("[DEBUG] Comparing: DB value=[" . trim($property['floor_location']) . "] with Option=[" . trim($option) . "] Selected=$selected");
                                            }
                                            ?>
                                        </select>
                                        <?php
                                        // Debug: Seçili option'ı kontrol et
                                        error_log("Selected option check:");
                                        foreach ($property as $key => $value) {
                                            if ($key == 'floor_location') {
                                                error_log("Found floor_location in property array:");
                                                error_log(" - Key: " . $key);
                                                error_log(" - Value: " . $value);
                                                error_log(" - Type: " . gettype($value));
                                            }
                                        }
                                        ?>
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
                                    <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="neighborhood" class="form-label">Mahalle</label>
                                    <input type="text" class="form-control" id="neighborhood" name="neighborhood" value="<?php echo htmlspecialchars($property['neighborhood']); ?>" required>
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
        // Form submit olayını dinle
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            
            let priceInput = document.getElementById('price');
            // Fiyat değerini temizle
            let cleanPrice = priceInput.value.replace(/\./g, '');
            console.log('Cleaned price:', cleanPrice);
            priceInput.value = cleanPrice;
            
            // Form verilerini kontrol et
            let formData = new FormData(this);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
        });

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

        // Emlak tipine göre form alanlarını göster/gizle
        function togglePropertyFields() {
            const propertyType = document.getElementById('property_type').value;
            const landFields = document.getElementById('landFields');
            const residentialFields = document.getElementById('residentialFields');
            const zoningStatus = document.getElementById('zoning_status');

            if (propertyType === 'Arsa') {
                landFields.style.display = 'block';
                residentialFields.style.display = 'none';
                // Arsa için zorunlu alanları etkinleştir
                zoningStatus.required = true;
                // Konut alanlarının required özelliğini kaldır
                document.getElementById('room_count').required = false;
                document.getElementById('living_room').required = false;
                document.getElementById('bathroom_count').required = false;
            } else {
                landFields.style.display = 'none';
                residentialFields.style.display = 'block';
                // Arsa alanlarının required özelliğini kaldır
                zoningStatus.required = false;
                // Konut için zorunlu alanları etkinleştir
                document.getElementById('room_count').required = true;
                document.getElementById('living_room').required = true;
                document.getElementById('bathroom_count').required = true;
            }
        }

        // Sayfa yüklendiğinde ve emlak tipi değiştiğinde form alanlarını düzenle
        document.addEventListener('DOMContentLoaded', function() {
            togglePropertyFields();
            document.getElementById('property_type').addEventListener('change', togglePropertyFields);
        });
    </script>
</body>
</html> 