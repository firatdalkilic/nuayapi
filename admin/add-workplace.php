<?php
session_start();
require_once 'config.php';
require_once 'check_login.php';
require_once '../includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug fonksiyonu
function debug($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

// Initialize variables
$title = $price = $status = $neighborhood = $square_meters = $floor = $floor_location = '';
$building_age = $room_count = $heating = $credit_eligible = $deed_status = $description = '';
$success_message = $error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug için POST verilerini göster
        echo '<div class="alert alert-info">';
        echo 'POST Verileri:';
        debug($_POST);
        echo '</div>';

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
        $credit_eligible = isset($_POST['credit_eligible']) ? 1 : 0;
        $deed_status = sanitize_input($_POST['deed_status'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');

        // Debug için temizlenmiş verileri göster
        echo '<div class="alert alert-info">';
        echo 'Temizlenmiş Veriler:';
        debug([
            'title' => $title,
            'price' => $price,
            'status' => $status,
            'neighborhood' => $neighborhood,
            'square_meters' => $square_meters,
            'floor' => $floor,
            'floor_location' => $floor_location,
            'building_age' => $building_age,
            'room_count' => $room_count,
            'heating' => $heating,
            'credit_eligible' => $credit_eligible,
            'deed_status' => $deed_status,
            'description' => $description,
            'agent_id' => $_SESSION['agent_id'] ?? null
        ]);
        echo '</div>';

        // Validate required fields
        if (empty($title) || empty($price) || empty($status) || empty($neighborhood)) {
            throw new Exception("Lütfen zorunlu alanları doldurun.");
        }

        $stmt = $conn->prepare("INSERT INTO properties (
            title, price, status, location, neighborhood, property_type,
            square_meters, floor, floor_location, building_age,
            room_count, heating, credit_eligible, deed_status,
            description, agent_id, created_at
        ) VALUES (
            ?, ?, ?, 'Didim', ?, 'İş Yeri',
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, NOW()
        )");

        // Debug için SQL sorgusunu göster
        echo '<div class="alert alert-info">';
        echo 'SQL Sorgusu:';
        debug([
            'query' => $stmt->queryString,
            'params' => [
                $title, $price, $status, $neighborhood,
                $square_meters, $floor, $floor_location, $building_age,
                $room_count, $heating, $credit_eligible, $deed_status,
                $description, $_SESSION['agent_id'] ?? null
            ]
        ]);
        echo '</div>';

        $agent_id = $_SESSION['agent_id'] ?? null;

        $stmt->bind_param("sdssdisisisss",
            $title, $price, $status, $neighborhood,
            $square_meters, $floor, $floor_location, $building_age,
            $room_count, $heating, $credit_eligible, $deed_status,
            $description, $agent_id
        );

        if ($stmt->execute()) {
            $property_id = $conn->insert_id;
            echo '<div class="alert alert-success">Property inserted successfully. ID: ' . $property_id . '</div>';

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = "../uploads/properties/";
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === 0) {
                        $file_name = uniqid() . '_' . $_FILES['images']['name'][$key];
                        echo '<div class="alert alert-info">Uploading image: ' . $file_name . '</div>';
                        
                        if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
                            $img_stmt = $conn->prepare("INSERT INTO property_images (property_id, image_name) VALUES (?, ?)");
                            $img_stmt->bind_param("is", $property_id, $file_name);
                            
                            if ($img_stmt->execute()) {
                                echo '<div class="alert alert-success">Image uploaded and saved to database: ' . $file_name . '</div>';
                            } else {
                                echo '<div class="alert alert-warning">Failed to save image to database: ' . $file_name . '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-warning">Failed to move uploaded file: ' . $file_name . '</div>';
                        }
                    }
                }
            }

            // Handle video upload
            if (!empty($_FILES['video']['name'])) {
                $video_upload_dir = "../uploads/videos/";
                $video_name = uniqid() . '_' . $_FILES['video']['name'];
                echo '<div class="alert alert-info">Uploading video: ' . $video_name . '</div>';
                
                if (move_uploaded_file($_FILES['video']['tmp_name'], $video_upload_dir . $video_name)) {
                    $video_stmt = $conn->prepare("UPDATE properties SET video_path = ? WHERE id = ?");
                    $video_stmt->bind_param("si", $video_name, $property_id);
                    
                    if ($video_stmt->execute()) {
                        echo '<div class="alert alert-success">Video uploaded and saved to database: ' . $video_name . '</div>';
                    } else {
                        echo '<div class="alert alert-warning">Failed to save video to database: ' . $video_name . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-warning">Failed to move uploaded video: ' . $video_name . '</div>';
                }
            }

            $success_message = "İş yeri ilanı başarıyla eklendi!";
            // Clear form data after successful submission
            $title = $price = $status = $neighborhood = $square_meters = $floor = '';
            $floor_location = $building_age = $room_count = $heating = $deed_status = $description = '';
        } else {
            throw new Exception("SQL Error: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error in add-workplace.php: " . $e->getMessage());
        echo '<div class="alert alert-danger">Hata Detayı: ' . $e->getMessage() . '</div>';
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