<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
checkLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini kontrol et
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
        'description' => 'Açıklama'
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

    // Form verilerini al
    $title = trim($_POST['title']);
    $price = str_replace('.', '', $_POST['price']); // Noktalı sayı formatını temizle
    $price = str_replace(',', '.', $price); // Virgülü nokta ile değiştir
    $status = trim($_POST['status']);
    $location = 'Didim';
    $description = trim($_POST['description']);
    $property_type = 'Arsa';
    $net_area = floatval($_POST['net_area']);
    $zoning_status = trim($_POST['zoning_status']);
    $block_no = trim($_POST['block_no']);
    $parcel_no = trim($_POST['parcel_no']);
    $sheet_no = trim($_POST['sheet_no']);
    $floor_area_ratio = trim($_POST['floor_area_ratio']);
    $height_limit = trim($_POST['height_limit']);
    $eligible_for_credit = isset($_POST['eligible_for_credit']) ? $_POST['eligible_for_credit'] : 'Hayır';
    $deed_status = trim($_POST['deed_status']);
    $neighborhood = trim($_POST['neighborhood']);
    $usage_status = NULL; // Arsa ilanları için NULL
    $video_call_available = isset($_POST['video_call_available']) ? $_POST['video_call_available'] : 'Hayır';
    $video_file = '';

    // Metrekare başına fiyatı hesapla
    $price_per_sqm = $net_area > 0 ? floatval($price) / floatval($net_area) : 0;

    // Resim kontrolü
    if (!isset($_FILES["images"]) || empty($_FILES["images"]["name"][0])) {
        $_SESSION['error'] = "En az bir resim yüklemelisiniz.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Veritabanına kaydet
    $sql = "INSERT INTO properties (
        title, price, status, location, description, property_type,
        net_area, zoning_status, block_no, parcel_no, sheet_no,
        floor_area_ratio, height_limit, eligible_for_credit,
        deed_status, neighborhood, price_per_sqm, usage_status,
        video_call_available, video_file
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Parametre tiplerini ve değerlerini düzenle
        if (!$stmt->bind_param("sdssssdssssssssdssss", 
            $title,         // s (string)
            $price,         // d (decimal)
            $status,        // s (string)
            $location,      // s (string)
            $description,   // s (string)
            $property_type, // s (string)
            $net_area,      // d (decimal)
            $zoning_status, // s (string)
            $block_no,      // s (string)
            $parcel_no,     // s (string)
            $sheet_no,      // s (string)
            $floor_area_ratio, // s (string)
            $height_limit,  // s (string)
            $eligible_for_credit, // s (string)
            $deed_status,   // s (string)
            $neighborhood,  // s (string)
            $price_per_sqm, // d (decimal)
            $usage_status,  // s (string)
            $video_call_available, // s (string)
            $video_file    // s (string)
        )) {
            throw new Exception("Binding parameters failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $property_id = $conn->insert_id;

        // Resim yükleme işlemleri...
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

        if ($stmt->affected_rows > 0 && $upload_success) {
            $_SESSION['success'] = "İlan başarıyla eklendi.";
            header("Location: properties.php");
            exit;
        } else {
            throw new Exception("İlan eklenirken bir hata oluştu.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsa İlanı Ekle - Admin Panel</title>
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
                        <h3 class="mb-0">Arsa İlanı Ekle</h3>
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

                        <form method="POST" action="" enctype="multipart/form-data" class="property-form" id="landForm">
                            <div class="mb-3">
                                <label for="property_type" class="form-label">Emlak Tipi</label>
                                <input type="text" class="form-control" id="property_type" value="Arsa" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="zoning_status" class="form-label">İmar Durumu</label>
                                <select class="form-select" id="zoning_status" name="zoning_status" required>
                                    <option value="">Seçiniz</option>
                                    <option value="Konut İmarlı">Konut İmarlı</option>
                                    <option value="Ticari İmarlı">Ticari İmarlı</option>
                                    <option value="Turizm İmarlı">Turizm İmarlı</option>
                                    <option value="Sanayi İmarlı">Sanayi İmarlı</option>
                                    <option value="Tarla">Tarla</option>
                                    <option value="Bağ & Bahçe">Bağ & Bahçe</option>
                                    <option value="Zeytinlik">Zeytinlik</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="title" class="form-label">İlan Başlığı</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Fiyat (TL)</label>
                                    <input type="text" class="form-control" id="price" name="price" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="net_area" class="form-label">Alan (m²)</label>
                                    <input type="number" class="form-control" id="net_area" name="net_area" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="price_per_sqm" class="form-label">m² Birim Fiyatı (TL)</label>
                                    <input type="text" class="form-control" id="price_per_sqm" readonly>
                                    <input type="hidden" name="price_per_sqm" id="price_per_sqm_hidden">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Durum</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Satılık">Satılık</option>
                                        <option value="Kiralık">Kiralık</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Konum</label>
                                    <input type="text" class="form-control" id="location" value="Didim" readonly>
                                </div>
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
                                <label for="description" class="form-label">İlan Açıklaması</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="deed_status" class="form-label">Tapu Durumu</label>
                                    <select class="form-select" id="deed_status" name="deed_status" required>
                                        <option value="">Seçiniz</option>
                                        <option value="Müstakil">Müstakil</option>
                                        <option value="Hisseli">Hisseli</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="eligible_for_credit" class="form-label">Krediye Uygunluk</label>
                                    <select class="form-select" id="eligible_for_credit" name="eligible_for_credit">
                                        <option value="Evet">Evet</option>
                                        <option value="Hayır">Hayır</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="block_no" class="form-label">Ada No</label>
                                    <input type="text" class="form-control" id="block_no" name="block_no">
                                </div>
                                <div class="col-md-4">
                                    <label for="parcel_no" class="form-label">Parsel No</label>
                                    <input type="text" class="form-control" id="parcel_no" name="parcel_no">
                                </div>
                                <div class="col-md-4">
                                    <label for="sheet_no" class="form-label">Pafta No</label>
                                    <input type="text" class="form-control" id="sheet_no" name="sheet_no">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="floor_area_ratio" class="form-label">Kaks (Emsal)</label>
                                    <input type="text" class="form-control" id="floor_area_ratio" name="floor_area_ratio">
                                </div>
                                <div class="col-md-6">
                                    <label for="height_limit" class="form-label">Gabari</label>
                                    <input type="text" class="form-control" id="height_limit" name="height_limit">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="video_call_available" class="form-label">Görüntülü Arama ile Gezilebilir</label>
                                <select class="form-select" id="video_call_available" name="video_call_available">
                                    <option value="Evet">Evet</option>
                                    <option value="Hayır">Hayır</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="images" class="form-label">İlan Fotoğrafları</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" required>
                                <small class="text-muted">Birden fazla fotoğraf seçebilirsiniz. İlk fotoğraf vitrin fotoğrafı olarak kullanılacaktır.</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">İlanı Yayınla</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const priceInput = document.getElementById('price');
        const areaInput = document.getElementById('net_area');
        const pricePerSqmInput = document.getElementById('price_per_sqm');
        const pricePerSqmHidden = document.getElementById('price_per_sqm_hidden');

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
                pricePerSqmHidden.value = pricePerSqm.toFixed(2);
            } else {
                pricePerSqmInput.value = '';
                pricePerSqmHidden.value = '';
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