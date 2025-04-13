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
        'area' => 'm²',
        'zoning_status' => 'İmar Durumu',
        'block_no' => 'Ada No',
        'parcel_no' => 'Parsel No',
        'floor_area_ratio' => 'Kaks (Emsal)',
        'height_limit' => 'Gabari',
        'credit_status' => 'Krediye Uygunluk',
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
    $price = str_replace(['.', ','], '', $_POST['price']);
    $price = (float)$price;
    $status = trim($_POST['status']);
    $location = 'Didim'; // Sabit değer
    $area = (float)$_POST['area'];
    $price_per_sqm = $area > 0 ? $price / $area : 0;
    $zoning_status = trim($_POST['zoning_status']);
    $block_no = trim($_POST['block_no']);
    $parcel_no = trim($_POST['parcel_no']);
    $floor_area_ratio = trim($_POST['floor_area_ratio']);
    $height_limit = trim($_POST['height_limit']);
    $credit_status = trim($_POST['credit_status']);
    $deed_status = trim($_POST['deed_status']);
    $description = trim($_POST['description']);
    $property_type = 'Arsa'; // Sabit değer
    $neighborhood = trim($_POST['neighborhood']);
    $sheet_no = trim($_POST['sheet_no']);

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
        deed_status, neighborhood, price_per_sqm
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssdssssssssd", 
            $title, $price, $status, $location, $description, $property_type,
            $area, $zoning_status, $block_no, $parcel_no, $sheet_no,
            $floor_area_ratio, $height_limit, $credit_status,
            $deed_status, $neighborhood, $price_per_sqm
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
                $_SESSION['success'] = "Arsa ilanı başarıyla eklendi.";
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
                                    <label for="price" class="form-label">Fiyat (₺)</label>
                                    <input type="number" class="form-control" id="price" name="price" required onchange="calculatePricePerSqm()">
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Durum</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Satılık">Satılık</option>
                                        <option value="Kiralık">Kiralık</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Konum</label>
                                <input type="text" class="form-control" id="location" value="Didim" readonly>
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

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="area" class="form-label">m²</label>
                                    <input type="number" class="form-control" id="area" name="area" required onchange="calculatePricePerSqm()">
                                </div>
                                <div class="col-md-6">
                                    <label for="price_per_sqm" class="form-label">m² Fiyatı</label>
                                    <input type="text" class="form-control" id="price_per_sqm" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="block_no" class="form-label">Ada No</label>
                                    <input type="text" class="form-control" id="block_no" name="block_no" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="parcel_no" class="form-label">Parsel No</label>
                                    <input type="text" class="form-control" id="parcel_no" name="parcel_no" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="sheet_no" class="form-label">Pafta No</label>
                                    <input type="text" class="form-control" id="sheet_no" name="sheet_no" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="floor_area_ratio" class="form-label">Kaks (Emsal)</label>
                                    <input type="text" class="form-control" id="floor_area_ratio" name="floor_area_ratio" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="height_limit" class="form-label">Gabari</label>
                                    <input type="text" class="form-control" id="height_limit" name="height_limit" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="credit_status" class="form-label">Krediye Uygunluk</label>
                                    <select class="form-select" id="credit_status" name="credit_status" required>
                                        <option value="Evet">Evet</option>
                                        <option value="Hayır">Hayır</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="deed_status" class="form-label">Tapu Durumu</label>
                                    <select class="form-select" id="deed_status" name="deed_status" required>
                                        <option value="Müstakil Parsel">Müstakil Parsel</option>
                                        <option value="Hisseli Parsel">Hisseli Parsel</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">İlan Açıklaması</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="images" class="form-label">İlan Fotoğrafları</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" required>
                                <small class="text-muted">Birden fazla fotoğraf seçebilirsiniz. İlk fotoğraf vitrin fotoğrafı olarak kullanılacaktır.</small>
                            </div>

                            <div class="mb-3">
                                <label for="property_video" class="form-label">İlan Videosu</label>
                                <input type="file" class="form-control" id="property_video" name="property_video" accept="video/*">
                                <small class="text-muted">Video yüklemek isteğe bağlıdır.</small>
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
    function formatPrice(number) {
        // Önce sayıyı tam sayıya yuvarla
        const roundedNumber = Math.round(number);
        
        // Türkçe formatta binlik ayracı ile formatla
        const formattedNumber = new Intl.NumberFormat('tr-TR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(roundedNumber);
        
        return formattedNumber + ',00';
    }

    function calculatePricePerSqm() {
        const price = document.getElementById('price').value;
        const area = document.getElementById('area').value;
        const pricePerSqm = document.getElementById('price_per_sqm');
        
        if (price && area && area > 0) {
            const result = (price / area);
            pricePerSqm.value = formatPrice(result) + ' ₺/m²';
        } else {
            pricePerSqm.value = '';
        }
    }
    </script>
</body>
</html> 