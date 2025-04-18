<?php
session_start();
require_once 'config.php';
require_once 'check_login.php';
require_once '../includes/functions.php';

// Hata ayıklama ayarları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug fonksiyonu
function debug($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

// POST verilerini debug et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST Verileri:<br>";
    debug($_POST);
    
    echo "Temizlenmiş Veriler:<br>";
    $clean_data = array_map('sanitize_input', $_POST);
    debug($clean_data);
}

// Dosya yükleme bilgilerini debug et
if (!empty($_FILES)) {
    echo "Dosya Yükleme Bilgileri:<br>";
    debug($_FILES);
}

// Initialize variables
$title = $price = $status = $neighborhood = $square_meters = $floor = $floor_location = '';
$building_age = $room_count = $heating = $credit_eligible = $deed_status = $description = '';
$success_message = $error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $title = sanitize_input($_POST['title'] ?? '');
        $price = str_replace('.', '', sanitize_input($_POST['price'] ?? '')); // Remove thousand separators
        $status = sanitize_input($_POST['status'] ?? '');
        $neighborhood = sanitize_input($_POST['neighborhood'] ?? '');
        $square_meters = sanitize_input($_POST['square_meters'] ?? '');
        $floor = sanitize_input($_POST['floor'] ?? '');
        $floor_location = sanitize_input($_POST['floor_location'] ?? '');
        $building_age = sanitize_input($_POST['building_age'] ?? '');
        $room_count = sanitize_input($_POST['room_count'] ?? '');
        $heating = sanitize_input($_POST['heating'] ?? '');
        $credit_eligible = sanitize_input($_POST['credit_eligible'] ?? '');
        $deed_status = sanitize_input($_POST['deed_status'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');

        // Debug temizlenmiş değişkenleri
        echo "Temizlenmiş ve İşlenmiş Değişkenler:<br>";
        $debug_vars = compact('title', 'price', 'status', 'neighborhood', 'square_meters', 'floor', 
                            'floor_location', 'building_age', 'room_count', 'heating', 'credit_eligible', 
                            'deed_status', 'description');
        debug($debug_vars);

        // Validate required fields
        if (empty($title) || empty($price) || empty($status) || empty($neighborhood)) {
            throw new Exception("Lütfen zorunlu alanları doldurun.");
        }

        // Veritabanı işlemlerini başlat
        $conn->begin_transaction();

        // SQL sorgusunu debug et
        $sql = "INSERT INTO properties (
            title, price, status, location, neighborhood, property_type,
            square_meters, floor, floor_location, building_age,
            room_count, heating, credit_eligible, deed_status,
            description, agent_id, created_at
        ) VALUES (
            ?, ?, ?, 'Didim', ?, 'İş Yeri',
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, NOW()
        )";
        echo "SQL Sorgusu:<br>";
        echo $sql . "<br>";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare hatası: " . $conn->error);
        }

        $agent_id = $_SESSION['agent_id'] ?? null;
        echo "Agent ID: " . ($agent_id ?? 'NULL') . "<br>";

        // Bind parametrelerini debug et
        echo "Bind Parametreleri:<br>";
        $bind_params = [
            "sdsssisssssssi",
            $title, $price, $status, $neighborhood,
            $square_meters, $floor, $floor_location, $building_age,
            $room_count, $heating, $credit_eligible, $deed_status,
            $description, $agent_id
        ];
        debug($bind_params);

        // Parametre bağlama
        $stmt->bind_param(...$bind_params);
        
        // Execute sorgusunu debug et
        if (!$stmt->execute()) {
            throw new Exception("Execute hatası: " . $stmt->error);
        }

        $property_id = $conn->insert_id;
        echo "Eklenen İlan ID: " . $property_id . "<br>";

        // Resim yükleme işlemlerini optimize et
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = dirname(__DIR__) . "/uploads/properties/";
            echo "Upload Dizini: " . $upload_dir . "<br>";
            
            // Klasör yoksa oluştur
            if (!file_exists($upload_dir)) {
                echo "Upload dizini bulunamadı, oluşturuluyor...<br>";
                if (mkdir($upload_dir, 0777, true)) {
                    echo "Upload dizini başarıyla oluşturuldu.<br>";
                } else {
                    echo "Upload dizini oluşturulamadı! Hata: " . error_get_last()['message'] . "<br>";
                }
            } else {
                echo "Upload dizini mevcut.<br>";
                echo "Dizin yazma izinleri: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";
            }
            
            $image_values = [];
            $image_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            // Toplu resim ekleme için SQL hazırla
            $image_sql = "INSERT INTO property_images (property_id, image_name) VALUES ";
            $first = true;

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === 0 && 
                    in_array($_FILES['images']['type'][$key], $image_types) && 
                    $_FILES['images']['size'][$key] < 5000000) { // 5MB limit
                    
                    $file_name = uniqid() . '_' . $_FILES['images']['name'][$key];
                    $upload_path = $upload_dir . $file_name;
                    
                    echo "Resim yükleniyor: " . $file_name . "<br>";
                    echo "Geçici dosya: " . $tmp_name . "<br>";
                    echo "Hedef yol: " . $upload_path . "<br>";
                    
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        echo "Resim başarıyla yüklendi: " . $file_name . "<br>";
                        if (!$first) {
                            $image_sql .= ",";
                        }
                        $image_sql .= "($property_id, '" . $conn->real_escape_string($file_name) . "')";
                        $first = false;
                    } else {
                        echo "Resim yüklenemedi! Hata: " . error_get_last()['message'] . "<br>";
                        echo "Geçici dosya var mı: " . (file_exists($tmp_name) ? 'Evet' : 'Hayır') . "<br>";
                        echo "Hedef dizin yazılabilir mi: " . (is_writable($upload_dir) ? 'Evet' : 'Hayır') . "<br>";
                    }
                } else {
                    echo "Resim hatası: " . $_FILES['images']['error'][$key] . "<br>";
                    echo "Resim tipi: " . $_FILES['images']['type'][$key] . "<br>";
                    echo "Resim boyutu: " . $_FILES['images']['size'][$key] . "<br>";
                }
            }

            // Tüm resimleri tek sorguda ekle
            if (!$first) {
                echo "Resim SQL sorgusu: " . $image_sql . "<br>";
                if ($conn->query($image_sql)) {
                    echo "Resimler veritabanına eklendi.<br>";
                } else {
                    echo "Resimler veritabanına eklenemedi! Hata: " . $conn->error . "<br>";
                }
            }
        }

        // Video yükleme işlemini optimize et
        if (!empty($_FILES['video']['name']) && $_FILES['video']['error'] === 0) {
            $video_upload_dir = dirname(__DIR__) . "/uploads/videos/";
            
            // Klasör yoksa oluştur
            if (!file_exists($video_upload_dir)) {
                mkdir($video_upload_dir, 0777, true);
            }
            
            $video_name = uniqid() . '_' . $_FILES['video']['name'];
            $video_types = ['video/mp4', 'video/webm', 'video/ogg'];
            
            if (in_array($_FILES['video']['type'], $video_types) && 
                $_FILES['video']['size'] < 50000000) { // 50MB limit
                
                $video_path = $video_upload_dir . $video_name;
                if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
                    $video_stmt = $conn->prepare("UPDATE properties SET video_path = ? WHERE id = ?");
                    $video_stmt->bind_param("si", $video_name, $property_id);
                    $video_stmt->execute();
                }
            }
        }

        // İşlemleri onayla
        $conn->commit();
        $success_message = "İş yeri ilanı başarıyla eklendi!";
        
        // Form verilerini temizle
        $title = $price = $status = $neighborhood = $square_meters = $floor = '';
        $floor_location = $building_age = $room_count = $heating = $deed_status = $description = '';

    } catch (Exception $e) {
        // Hata durumunda işlemleri geri al
        $conn->rollback();
        $error_message = "İlan eklenirken bir hata oluştu. Lütfen tekrar deneyin.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Yeri İlanı Ekle - Nua Yapı Admin</title>
    <link href="../assets/img/nua_logo.jpg" rel="icon">
    <link href="../assets/img/nua_logo.jpg" rel="apple-touch-icon">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body class="admin-dashboard">
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
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">İş Yeri İlanı Ekle</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="title" class="form-label">İlan Başlığı *</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="location" class="form-label">Konum</label>
                                    <input type="text" class="form-control" id="location" value="Didim" disabled>
                                </div>

                                <div class="col-md-6">
                                    <label for="neighborhood" class="form-label">Mahalle *</label>
                                    <select class="form-select" id="neighborhood" name="neighborhood" required>
                                        <option value="">Seçiniz...</option>
                                        <?php
                                        $neighborhoods = [
                                            "Ak-yeniköy Mah.", "Akbük Mah.", "Akköy Mah.", "Altınkum Mah.",
                                            "Balat Mah.", "Batıköy Mah.", "Cumhuriyet Mah.", "Çamlık Mah.",
                                            "Denizköy Mah.", "Efeler Mah.", "Fevzipaşa Mah.", "Hisar Mah.",
                                            "Mavişehir Mah.", "Mersindere Mah.", "Yalıköy Mah.", "Yeni Mah."
                                        ];
                                        foreach ($neighborhoods as $n) {
                                            $selected = ($neighborhood === $n) ? 'selected' : '';
                                            echo "<option value=\"$n\" $selected>$n</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="price" class="form-label">Fiyat *</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
                                        <span class="input-group-text">₺</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label">Durumu *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Seçiniz...</option>
                                        <option value="Kiralık" <?php echo ($status === 'Kiralık') ? 'selected' : ''; ?>>Kiralık</option>
                                        <option value="Satılık" <?php echo ($status === 'Satılık') ? 'selected' : ''; ?>>Satılık</option>
                                        <option value="Devren Kiralık" <?php echo ($status === 'Devren Kiralık') ? 'selected' : ''; ?>>Devren Kiralık</option>
                                        <option value="Devren Satılık" <?php echo ($status === 'Devren Satılık') ? 'selected' : ''; ?>>Devren Satılık</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="square_meters" class="form-label">m² *</label>
                                    <input type="number" class="form-control" id="square_meters" name="square_meters" value="<?php echo htmlspecialchars($square_meters); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="floor" class="form-label">Kat</label>
                                    <input type="number" class="form-control" id="floor" name="floor" value="<?php echo htmlspecialchars($floor); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="floor_location" class="form-label">Bulunduğu Kat</label>
                                    <select class="form-select" id="floor_location" name="floor_location">
                                        <option value="">Seçiniz...</option>
                                        <?php
                                        $floor_options = [
                                            'Bodrum KAT', 'Yarı Bodrum KAT', 'Zemin KAT', 'Bahçe KAT', 'Yüksek Giriş',
                                            '1. KAT', '2. KAT', '3. KAT', '4. KAT', '5. KAT', '6. KAT', '7. KAT', '8. KAT',
                                            '9. KAT', '10. KAT', '11. KAT', '12. KAT ve üzeri', 'Çatı KAT'
                                        ];
                                        foreach ($floor_options as $option) {
                                            $selected = ($floor_location === $option) ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="building_age" class="form-label">Bina Yaşı</label>
                                    <select class="form-select" id="building_age" name="building_age">
                                        <option value="">Seçiniz...</option>
                                        <option value="0" <?php echo ($building_age === '0') ? 'selected' : ''; ?>>0 (Yeni)</option>
                                        <option value="1" <?php echo ($building_age === '1') ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo ($building_age === '2') ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo ($building_age === '3') ? 'selected' : ''; ?>>3</option>
                                        <option value="4" <?php echo ($building_age === '4') ? 'selected' : ''; ?>>4</option>
                                        <option value="5" <?php echo ($building_age === '5') ? 'selected' : ''; ?>>5</option>
                                        <option value="6" <?php echo ($building_age === '6') ? 'selected' : ''; ?>>6</option>
                                        <option value="7" <?php echo ($building_age === '7') ? 'selected' : ''; ?>>7</option>
                                        <option value="8" <?php echo ($building_age === '8') ? 'selected' : ''; ?>>8</option>
                                        <option value="9" <?php echo ($building_age === '9') ? 'selected' : ''; ?>>9</option>
                                        <option value="10" <?php echo ($building_age === '10') ? 'selected' : ''; ?>>10</option>
                                        <option value="11-15" <?php echo ($building_age === '11-15') ? 'selected' : ''; ?>>11-15</option>
                                        <option value="16-20" <?php echo ($building_age === '16-20') ? 'selected' : ''; ?>>16-20</option>
                                        <option value="21-25" <?php echo ($building_age === '21-25') ? 'selected' : ''; ?>>21-25</option>
                                        <option value="26+" <?php echo ($building_age === '26+') ? 'selected' : ''; ?>>26+</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="room_count" class="form-label">Bölüm & Oda Sayısı</label>
                                    <input type="number" class="form-control" id="room_count" name="room_count" value="<?php echo htmlspecialchars($room_count); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="heating" class="form-label">Isıtma</label>
                                    <select class="form-select" id="heating" name="heating">
                                        <option value="">Seçiniz...</option>
                                        <?php
                                        $heating_types = ["Doğalgaz", "Merkezi", "Klima", "Soba", "Yok"];
                                        foreach ($heating_types as $type) {
                                            $selected = ($heating === $type) ? 'selected' : '';
                                            echo "<option value=\"$type\" $selected>$type</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="credit_eligible" class="form-label">Krediye Uygun</label>
                                    <select class="form-select" id="credit_eligible" name="credit_eligible">
                                        <option value="Evet" <?php echo ($credit_eligible === 'Evet') ? 'selected' : ''; ?>>Evet</option>
                                        <option value="Hayır" <?php echo ($credit_eligible === 'Hayır') ? 'selected' : ''; ?>>Hayır</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="deed_status" class="form-label">Tapu Durumu</label>
                                    <select class="form-select" id="deed_status" name="deed_status">
                                        <option value="">Seçiniz...</option>
                                        <option value="Kat Mülkiyetli" <?php echo ($deed_status === 'Kat Mülkiyetli') ? 'selected' : ''; ?>>Kat Mülkiyetli</option>
                                        <option value="Kat İrtifaklı" <?php echo ($deed_status === 'Kat İrtifaklı') ? 'selected' : ''; ?>>Kat İrtifaklı</option>
                                        <option value="Müstakil Tapulu" <?php echo ($deed_status === 'Müstakil Tapulu') ? 'selected' : ''; ?>>Müstakil Tapulu</option>
                                        <option value="Hisseli Tapulu" <?php echo ($deed_status === 'Hisseli Tapulu') ? 'selected' : ''; ?>>Hisseli Tapulu</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">İlan Açıklaması</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label for="images" class="form-label">İlan Fotoğrafları</label>
                                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                </div>

                                <div class="col-12">
                                    <label for="video" class="form-label">İlan Videosu</label>
                                    <input type="file" class="form-control" id="video" name="video" accept="video/*">
                                </div>
                            </div>

                            <hr class="my-4">

                            <button class="btn btn-primary btn-lg w-100" type="submit">İlanı Yayınla</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fiyat input formatlaması
        const priceInput = document.getElementById('price');
        priceInput.addEventListener('input', function(e) {
            // Sadece sayıları al
            let value = this.value.replace(/\D/g, '');
            
            // Sayıyı formatla
            if (value !== '') {
                this.value = new Intl.NumberFormat('tr-TR').format(parseInt(value));
            }
        });

        // Form validation
        const form = document.querySelector('.needs-validation');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Form gönderilmeden önce fiyat alanındaki noktalama işaretlerini kaldır
                const priceInput = document.getElementById('price');
                priceInput.value = priceInput.value.replace(/\./g, '');
            }
            form.classList.add('was-validated');
        });
    });
    </script>
</body>
</html> 