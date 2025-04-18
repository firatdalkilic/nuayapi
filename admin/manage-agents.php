<?php
require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// Properties tablosunu kontrol et ve oluştur
$check_properties_query = "SELECT 1 FROM properties LIMIT 1";
$table_exists = true;
try {
    $conn->query($check_properties_query);
} catch (Exception $e) {
    $table_exists = false;
}

if (!$table_exists) {
    // Properties tablosunu oluştur
    $create_properties_query = "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(15,2),
        location VARCHAR(255),
        neighborhood VARCHAR(255),
        status VARCHAR(50),
        property_type VARCHAR(50),
        image_name VARCHAR(255),
        agent_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (agent_id) REFERENCES agents(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->query($create_properties_query);
}

// Agents tablosunu kontrol et ve yoksa oluştur
$check_agents_query = "SHOW TABLES LIKE 'agents'";
$result = $conn->query($check_agents_query);
if ($result->num_rows == 0) {
    // Agents tablosu yoksa oluştur
    $create_table_query = "CREATE TABLE agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        agent_name VARCHAR(255) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(255) NOT NULL,
        about TEXT,
        image VARCHAR(255),
        sahibinden_link VARCHAR(255),
        emlakjet_link VARCHAR(255),
        facebook_link VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($create_table_query)) {
        error_log("Agents table creation error: " . $conn->error);
        die("Danışman tablosu oluşturma hatası: " . $conn->error);
    }
}

// Properties tablosundaki foreign key'i kontrol et ve yoksa ekle
$check_fk_query = "SELECT * FROM information_schema.TABLE_CONSTRAINTS 
                   WHERE CONSTRAINT_SCHEMA = DATABASE() 
                   AND CONSTRAINT_NAME = 'properties_ibfk_1'";
$result = $conn->query($check_fk_query);

if ($result->num_rows == 0) {
    try {
        $add_foreign_key = "ALTER TABLE properties ADD CONSTRAINT properties_ibfk_1 FOREIGN KEY (agent_id) REFERENCES agents(id)";
        $conn->query($add_foreign_key);
    } catch (Exception $e) {
        // Foreign key eklenirken hata olursa işleme devam et
        error_log("Foreign key addition error: " . $e->getMessage());
    }
}

// Form işlemleri
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $username = trim($_POST['username']);
                $raw_password = trim($_POST['password']);
                
                // Şifreyi özel ayarlarla hashle
                $password = password_hash($raw_password, PASSWORD_BCRYPT, [
                    'cost' => 10
                ]);
                
                error_log("Password hashing details:");
                error_log("Raw password: " . $raw_password);
                error_log("Hash algorithm: BCRYPT");
                error_log("Hash cost: 10");
                error_log("Generated hash: " . $password);
                
                $phone = trim($_POST['phone']);
                $email = trim($_POST['email']);
                $about = trim($_POST['about']);
                $sahibinden_link = trim($_POST['sahibinden_link']);
                $emlakjet_link = trim($_POST['emlakjet_link']);
                $facebook_link = trim($_POST['facebook_link']);
                
                // Resim yükleme işlemi
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "../uploads/agents/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }

                    // Dosya uzantısını kontrol et
                    $allowed = ['jpg', 'jpeg', 'png'];
                    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    if (!in_array($imageFileType, $allowed)) {
                        $_SESSION['error'] = "Sadece JPG, JPEG ve PNG dosyaları yüklenebilir.";
                        header("Location: manage-agents.php");
                        exit;
                    }

                    // Benzersiz dosya adı oluştur
                    $image = 'uploads/agents/' . uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $imageFileType;
                    $target_file = "../" . $image;
                    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        // Resmi yeniden boyutlandır
                        list($width, $height) = getimagesize($target_file);
                        $new_width = 800;
                        $new_height = (int)(($height / $width) * $new_width);
                        
                        $temp = imagecreatetruecolor($new_width, $new_height);
                        
                        if ($imageFileType == "png") {
                            $source = imagecreatefrompng($target_file);
                            // PNG için şeffaflığı koru
                            imagealphablending($temp, false);
                            imagesavealpha($temp, true);
                        } else {
                            $source = imagecreatefromjpeg($target_file);
                        }
                        
                        imagecopyresampled($temp, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        
                        if ($imageFileType == "png") {
                            imagepng($temp, $target_file, 8);
                        } else {
                            imagejpeg($temp, $target_file, 80);
                        }
                        
                        imagedestroy($temp);
                        imagedestroy($source);
                    } else {
                        $_SESSION['error'] = "Fotoğraf yüklenirken bir hata oluştu.";
                        header("Location: manage-agents.php");
                        exit;
                    }
                }

                $sql = "INSERT INTO agents (agent_name, username_panel, password, phone, email, about, image, sahibinden_link, emlakjet_link, facebook_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssss", $name, $username, $password, $phone, $email, $about, $image, $sahibinden_link, $emlakjet_link, $facebook_link);
                
                if ($stmt->execute()) {
                    error_log("Agent created successfully. Testing password verification:");
                    error_log("Verification test: " . (password_verify($raw_password, $password) ? "PASSED" : "FAILED"));
                    $_SESSION['success'] = "Danışman başarıyla eklendi.";
                } else {
                    error_log("Error creating agent: " . $conn->error);
                    $_SESSION['error'] = "Danışman eklenirken bir hata oluştu.";
                }
                break;

            case 'edit':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                
                // Fotoğraf işlemleri
                $image_path = '';
                $current_image = '';
                
                // Mevcut fotoğrafı al
                $stmt = $conn->prepare("SELECT image FROM agents WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $current_image = $row['image'];
                }
                
                // Fotoğraf kaldırma işlemi
                if (isset($_POST['remove_photo']) && $_POST['remove_photo'] == 'on') {
                    if (!empty($current_image) && file_exists("../" . $current_image)) {
                        unlink("../" . $current_image);
                    }
                    $image_path = '';
                }
                // Yeni fotoğraf yükleme işlemi
                else if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png'];
                    $filename = $_FILES['image']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        // Yükleme dizinini kontrol et ve oluştur
                        $upload_dir = "../uploads/agents/";
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Eski fotoğrafı sil
                        if (!empty($current_image) && file_exists("../" . $current_image)) {
                            unlink("../" . $current_image);
                        }
                        
                        // Yeni fotoğrafı yükle
                        $newname = 'uploads/agents/' . uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $filetype;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], "../" . $newname)) {
                            // Resmi yeniden boyutlandır
                            list($width, $height) = getimagesize("../" . $newname);
                            $new_width = 800;
                            $new_height = (int)(($height / $width) * $new_width);
                            
                            $temp = imagecreatetruecolor($new_width, $new_height);
                            
                            if ($filetype == "png") {
                                $source = imagecreatefrompng("../" . $newname);
                                // PNG için şeffaflığı koru
                                imagealphablending($temp, false);
                                imagesavealpha($temp, true);
                            } else {
                                $source = imagecreatefromjpeg("../" . $newname);
                            }
                            
                            imagecopyresampled($temp, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                            
                            if ($filetype == "png") {
                                imagepng($temp, "../" . $newname, 8);
                            } else {
                                imagejpeg($temp, "../" . $newname, 80);
                            }
                            
                            imagedestroy($temp);
                            imagedestroy($source);
                            
                            $image_path = $newname;
                        } else {
                            $_SESSION['error'] = "Fotoğraf yüklenirken bir hata oluştu.";
                            header("Location: manage-agents.php");
                            exit();
                        }
                    } else {
                        $_SESSION['error'] = "Sadece JPG, JPEG ve PNG dosyaları yüklenebilir.";
                        header("Location: manage-agents.php");
                        exit();
                    }
                }
                // Fotoğraf değişmedi
                else {
                    $image_path = $current_image;
                }
                
                // Veritabanını güncelle
                $stmt = $conn->prepare("UPDATE agents SET agent_name=?, email=?, phone=?, image=? WHERE id=?");
                $stmt->bind_param("ssssi", $name, $email, $phone, $image_path, $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Danışman başarıyla güncellendi.";
                } else {
                    $_SESSION['error'] = "Danışman güncellenirken bir hata oluştu.";
                }
                
                header("Location: manage-agents.php");
                exit();
                break;

            case 'delete':
                $id = $_POST['id'];
                $sql = "DELETE FROM agents WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Danışman silindi.";
                } else {
                    $_SESSION['error'] = "Silme işlemi sırasında bir hata oluştu.";
                }
                break;
        }
        header("Location: manage-agents.php");
        exit;
    }
}

// Tüm danışmanları getir
$agents = [];
$sql = "SELECT * FROM agents";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $agents[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danışman Yönetimi - Nua Yapı Admin</title>
    <link href="../assets/img/nua_logo.jpg" rel="icon">
    <link href="../assets/img/nua_logo.jpg" rel="apple-touch-icon">
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
                    <li class="nav-item">
                        <a class="nav-link active" href="manage-agents.php">Danışmanlar</a>
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
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Danışmanlar</h3>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                            <i class="bi bi-plus"></i> Yeni Danışman Ekle
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>İsim</th>
                                        <th>Telefon</th>
                                        <th>E-posta</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($agents as $agent): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($agent['agent_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($agent['phone'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($agent['email'] ?? ''); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-agent" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editAgentModal"
                                                    data-id="<?php echo $agent['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($agent['agent_name'] ?? ''); ?>"
                                                    data-username="<?php echo htmlspecialchars($agent['username_panel'] ?? ''); ?>"
                                                    data-phone="<?php echo htmlspecialchars($agent['phone'] ?? ''); ?>"
                                                    data-email="<?php echo htmlspecialchars($agent['email'] ?? ''); ?>"
                                                    data-about="<?php echo htmlspecialchars($agent['about'] ?? ''); ?>"
                                                    data-image="<?php echo htmlspecialchars($agent['image'] ?? ''); ?>"
                                                    data-sahibinden_link="<?php echo htmlspecialchars($agent['sahibinden_link'] ?? ''); ?>"
                                                    data-emlakjet_link="<?php echo htmlspecialchars($agent['emlakjet_link'] ?? ''); ?>"
                                                    data-facebook_link="<?php echo htmlspecialchars($agent['facebook_link'] ?? ''); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-agent"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteAgentModal"
                                                    data-id="<?php echo $agent['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($agent['agent_name'] ?? ''); ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Agent Modal -->
    <div class="modal fade" id="addAgentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Danışman Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">İsim Soyisim</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Şifre</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="about" class="form-label">Hakkımda</label>
                                    <textarea class="form-control" id="about" name="about" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Profil Resmi</label>
                                    <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Önerilen boyut: 800x800 piksel. Maksimum dosya boyutu: 2MB</small>
                                </div>
                                <?php if (!empty($agent['image']) && file_exists($agent['image'])): ?>
                                <div class="form-group mt-2">
                                    <label>Mevcut Fotoğraf:</label><br>
                                    <img src="<?php echo '../' . $agent['image']; ?>" alt="<?php echo $agent['agent_name']; ?>" style="max-width: 200px; height: auto;" class="img-thumbnail">
                                </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="sahibinden_link" class="form-label">Sahibinden Linki</label>
                                    <input type="url" class="form-control" id="sahibinden_link" name="sahibinden_link">
                                </div>
                                <div class="mb-3">
                                    <label for="emlakjet_link" class="form-label">Emlakjet Linki</label>
                                    <input type="url" class="form-control" id="emlakjet_link" name="emlakjet_link">
                                </div>
                                <div class="mb-3">
                                    <label for="facebook_link" class="form-label">Facebook Linki</label>
                                    <input type="url" class="form-control" id="facebook_link" name="facebook_link">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Agent Modal -->
    <div class="modal fade" id="editAgentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Danışman Düzenle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">İsim Soyisim</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_username" class="form-label">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="edit_username" name="username_panel" readonly>
                                    <small class="form-text text-muted">Kullanıcı adı değiştirilemez.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_password" class="form-label">Şifre (Boş bırakılırsa değişmez)</label>
                                    <input type="password" class="form-control" id="edit_password" name="password">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_phone" class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_about" class="form-label">Hakkımda</label>
                                    <textarea class="form-control" id="edit_about" name="about" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_image" class="form-label">Profil Resmi</label>
                                    <div class="current-photo mb-2">
                                        <div id="current_image" class="text-center">
                                            <!-- JavaScript ile doldurulacak -->
                                        </div>
                                        <div class="mt-2" id="remove_photo_container" style="display: none;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo">
                                                <label class="form-check-label text-danger" for="remove_photo">
                                                    Mevcut fotoğrafı kaldır
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control" id="edit_image" name="image" accept=".jpg,.jpeg,.png">
                                    <div id="image_preview" class="mt-2 text-center" style="display: none;">
                                        <img id="preview_img" src="#" alt="Fotoğraf Önizleme" style="max-width: 200px; height: auto;" class="img-thumbnail">
                                    </div>
                                    <small class="form-text text-muted">
                                        Önerilen boyut: 800x800 piksel<br>
                                        İzin verilen formatlar: JPG, JPEG, PNG<br>
                                        Maksimum dosya boyutu: 2MB
                                    </small>
                                </div>
                                <?php if (!empty($agent['image']) && file_exists($agent['image'])): ?>
                                <div class="form-group mt-2">
                                    <label>Mevcut Fotoğraf:</label><br>
                                    <img src="<?php echo '../' . $agent['image']; ?>" alt="<?php echo $agent['agent_name']; ?>" style="max-width: 200px; height: auto;" class="img-thumbnail">
                                </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="edit_sahibinden_link" class="form-label">Sahibinden Linki</label>
                                    <input type="url" class="form-control" id="edit_sahibinden_link" name="sahibinden_link">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_emlakjet_link" class="form-label">Emlakjet Linki</label>
                                    <input type="url" class="form-control" id="edit_emlakjet_link" name="emlakjet_link">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_facebook_link" class="form-label">Facebook Linki</label>
                                    <input type="url" class="form-control" id="edit_facebook_link" name="facebook_link">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Agent Modal -->
    <div class="modal fade" id="deleteAgentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Danışman Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bu danışmanı silmek istediğinizden emin misiniz?</p>
                        <p class="text-danger" id="delete_agent_name"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Sil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit modal veri doldurma
        document.querySelectorAll('.edit-agent').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_username').value = this.dataset.username;
                document.getElementById('edit_phone').value = this.dataset.phone;
                document.getElementById('edit_email').value = this.dataset.email;
                document.getElementById('edit_about').value = this.dataset.about;
                document.getElementById('edit_sahibinden_link').value = this.dataset.sahibinden_link;
                document.getElementById('edit_emlakjet_link').value = this.dataset.emlakjet_link;
                document.getElementById('edit_facebook_link').value = this.dataset.facebook_link;
                
                // Mevcut resmi göster
                const currentImage = document.getElementById('current_image');
                const removePhotoContainer = document.getElementById('remove_photo_container');
                if (this.dataset.image) {
                    currentImage.innerHTML = `
                        <img src="../${this.dataset.image}" alt="Mevcut Profil Resmi" 
                             style="max-width: 300px; max-height: 300px;" class="img-thumbnail">`;
                    removePhotoContainer.style.display = 'block';
                } else {
                    currentImage.innerHTML = '<p class="text-muted">Profil fotoğrafı yok</p>';
                    removePhotoContainer.style.display = 'none';
                }
                
                // Önizlemeyi temizle
                document.getElementById('image_preview').style.display = 'none';
                document.getElementById('edit_image').value = '';
                document.getElementById('remove_photo').checked = false;
            });
        });

        // Delete modal veri doldurma
        document.querySelectorAll('.delete-agent').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_id').value = this.dataset.id;
                document.getElementById('delete_agent_name').textContent = this.dataset.name;
            });
        });

        // Fotoğraf önizleme
        document.getElementById('edit_image').addEventListener('change', function(e) {
            const preview = document.getElementById('image_preview');
            const previewImg = document.getElementById('preview_img');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html> 