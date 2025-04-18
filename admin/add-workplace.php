<?php
require_once 'header.php';
require_once 'config.php';
require_once 'check-login.php';
require_once '../includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$title = $price = $status = $neighborhood = $square_meters = $floor = $floor_location = '';
$building_age = $room_count = $heating = $credit_eligible = $deed_status = $description = '';
$success_message = $error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Validate required fields
    if (empty($title) || empty($price) || empty($status) || empty($neighborhood)) {
        $error_message = "Lütfen zorunlu alanları doldurun.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO properties (
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

            $stmt->execute([
                $title, $price, $status, $neighborhood,
                $square_meters, $floor, $floor_location, $building_age,
                $room_count, $heating, $credit_eligible, $deed_status,
                $description, $_SESSION['agent_id']
            ]);

            $property_id = $db->lastInsertId();

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = "../uploads/properties/";
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === 0) {
                        $file_name = uniqid() . '_' . $_FILES['images']['name'][$key];
                        if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
                            $stmt = $db->prepare("INSERT INTO property_images (property_id, image_path) VALUES (?, ?)");
                            $stmt->execute([$property_id, $file_name]);
                        }
                    }
                }
            }

            // Handle video upload
            if (!empty($_FILES['video']['name'])) {
                $video_upload_dir = "../uploads/videos/";
                $video_name = uniqid() . '_' . $_FILES['video']['name'];
                if (move_uploaded_file($_FILES['video']['tmp_name'], $video_upload_dir . $video_name)) {
                    $stmt = $db->prepare("UPDATE properties SET video_path = ? WHERE id = ?");
                    $stmt->execute([$video_name, $property_id]);
                }
            }

            $success_message = "İş yeri ilanı başarıyla eklendi!";
            // Clear form data after successful submission
            $title = $price = $status = $neighborhood = $square_meters = $floor = '';
            $floor_location = $building_age = $room_count = $heating = $deed_status = $description = '';
        } catch (PDOException $e) {
            error_log("Error in add-workplace.php: " . $e->getMessage());
            $error_message = "İlan eklenirken bir hata oluştu. Lütfen tekrar deneyin.";
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">İş Yeri İlanı Ekle</h1>
            </div>

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
                                "Altınkum", "Efeler", "Çamlık", "Yeni Mahalle", "Mavişehir",
                                "Hisar", "Cumhuriyet", "Yalı", "Akbük"
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
                        <label for="property_type" class="form-label">Emlak Tipi</label>
                        <input type="text" class="form-control" id="property_type" value="İş Yeri" disabled>
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
                        <input type="text" class="form-control" id="floor_location" name="floor_location" value="<?php echo htmlspecialchars($floor_location); ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="building_age" class="form-label">Bina Yaşı</label>
                        <select class="form-select" id="building_age" name="building_age">
                            <option value="">Seçiniz...</option>
                            <?php
                            $ages = ["0-1", "1-5", "5-10", "10-15", "15-20", "20+"];
                            foreach ($ages as $age) {
                                $selected = ($building_age === $age) ? 'selected' : '';
                                echo "<option value=\"$age\" $selected>$age</option>";
                            }
                            ?>
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
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="credit_eligible" name="credit_eligible" <?php echo $credit_eligible ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="credit_eligible">
                                Krediye Uygun
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="deed_status" class="form-label">Tapu Durumu</label>
                        <input type="text" class="form-control" id="deed_status" name="deed_status" value="<?php echo htmlspecialchars($deed_status); ?>">
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
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Price input formatting
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value === '') return;
        value = parseInt(value).toLocaleString('tr-TR');
        this.value = value;
    });

    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php require_once 'footer.php'; ?> 