<?php
session_start();
require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// Girdi temizleme fonksiyonu
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add':
                $fullname = sanitize_input($_POST['fullname']);
                $username = sanitize_input($_POST['username']);
                $phone = sanitize_input($_POST['phone']);
                $email = sanitize_input($_POST['email']);
                $about = sanitize_input($_POST['about']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $status = sanitize_input($_POST['status']);
                
                // Resim yükleme işlemi
                $image_name = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "../uploads/agents/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
                    $target_file = $target_dir . $image_name;
                    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        // Resim başarıyla yüklendi
                    } else {
                        $_SESSION['error'] = "Resim yüklenirken bir hata oluştu.";
                        header("Location: manage-agents.php");
                        exit;
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO agents (fullname, username, phone, email, about, password, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $fullname, $username, $phone, $email, $about, $password, $image_name, $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Emlakçı başarıyla eklendi.";
                } else {
                    $_SESSION['error'] = "Emlakçı eklenirken bir hata oluştu.";
                }
                break;
                
            case 'edit':
                $id = sanitize_input($_POST['id']);
                $fullname = sanitize_input($_POST['fullname']);
                $username = sanitize_input($_POST['username']);
                $phone = sanitize_input($_POST['phone']);
                $email = sanitize_input($_POST['email']);
                $about = sanitize_input($_POST['about']);
                $status = sanitize_input($_POST['status']);
                
                // Resim güncelleme işlemi
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "../uploads/agents/";
                    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
                    $target_file = $target_dir . $image_name;
                    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        // Eski resmi sil
                        $old_image_query = $conn->prepare("SELECT image FROM agents WHERE id = ?");
                        $old_image_query->bind_param("i", $id);
                        $old_image_query->execute();
                        $old_image_result = $old_image_query->get_result();
                        $old_image = $old_image_result->fetch_assoc();
                        
                        if ($old_image && $old_image['image']) {
                            @unlink("../uploads/agents/" . $old_image['image']);
                        }
                        
                        // Resim adını güncelle
                        $image_update = $conn->prepare("UPDATE agents SET image = ? WHERE id = ?");
                        $image_update->bind_param("si", $image_name, $id);
                        $image_update->execute();
                    }
                }
                
                // Şifre kontrolü ve güncelleme
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE agents SET fullname=?, username=?, phone=?, email=?, about=?, password=?, status=? WHERE id=?");
                    $stmt->bind_param("sssssssi", $fullname, $username, $phone, $email, $about, $password, $status, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE agents SET fullname=?, username=?, phone=?, email=?, about=?, status=? WHERE id=?");
                    $stmt->bind_param("ssssssi", $fullname, $username, $phone, $email, $about, $status, $id);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Emlakçı bilgileri güncellendi.";
                } else {
                    $_SESSION['error'] = "Güncelleme sırasında bir hata oluştu.";
                }
                break;
                
            case 'delete':
                $id = sanitize_input($_POST['id']);
                
                // Resmi sil
                $image_query = $conn->prepare("SELECT image FROM agents WHERE id = ?");
                $image_query->bind_param("i", $id);
                $image_query->execute();
                $image_result = $image_query->get_result();
                $image = $image_result->fetch_assoc();
                
                if ($image && $image['image']) {
                    @unlink("../uploads/agents/" . $image['image']);
                }
                
                $stmt = $conn->prepare("DELETE FROM agents WHERE id=?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Emlakçı silindi.";
                } else {
                    $_SESSION['error'] = "Silme işlemi sırasında bir hata oluştu.";
                }
                break;
        }
        
        header('Location: manage-agents.php');
        exit();
    }
}

// Tüm emlakçıları getir
$result = $conn->query("SELECT * FROM agents ORDER BY created_at DESC");
$agents = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emlakçı Yönetimi - Nua Yapı Admin</title>
    
    <!-- Favicons -->
    <link href="../assets/img/nua_logo.jpg" rel="icon">
    <link href="../assets/img/nua_logo.jpg" rel="apple-touch-icon">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .agent-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .table > tbody > tr > td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Emlakçı Yönetimi</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                <i class="bi bi-plus-lg"></i> Yeni Emlakçı Ekle
            </button>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Fotoğraf</th>
                        <th>Ad Soyad</th>
                        <th>Kullanıcı Adı</th>
                        <th>Telefon</th>
                        <th>E-posta</th>
                        <th>Durum</th>
                        <th>Kayıt Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td>
                                <?php if ($agent['image']): ?>
                                    <img src="../uploads/agents/<?php echo $agent['image']; ?>" class="agent-image" alt="<?php echo htmlspecialchars($agent['fullname']); ?>">
                                <?php else: ?>
                                    <img src="../assets/img/default-agent.jpg" class="agent-image" alt="Varsayılan profil">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($agent['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($agent['username']); ?></td>
                            <td><?php echo htmlspecialchars($agent['phone']); ?></td>
                            <td><?php echo htmlspecialchars($agent['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $agent['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $agent['status'] == 'active' ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($agent['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editAgentModal"
                                        data-id="<?php echo $agent['id']; ?>"
                                        data-fullname="<?php echo htmlspecialchars($agent['fullname']); ?>"
                                        data-username="<?php echo htmlspecialchars($agent['username']); ?>"
                                        data-phone="<?php echo htmlspecialchars($agent['phone']); ?>"
                                        data-email="<?php echo htmlspecialchars($agent['email']); ?>"
                                        data-about="<?php echo htmlspecialchars($agent['about']); ?>"
                                        data-status="<?php echo $agent['status']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteAgentModal"
                                        data-id="<?php echo $agent['id']; ?>"
                                        data-fullname="<?php echo htmlspecialchars($agent['fullname']); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Yeni Emlakçı Ekleme Modal -->
    <div class="modal fade" id="addAgentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Emlakçı Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="manage-agents.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="about" class="form-label">Hakkında</label>
                            <textarea class="form-control" id="about" name="about" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Profil Fotoğrafı</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Durum</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Pasif</option>
                            </select>
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

    <!-- Emlakçı Düzenleme Modal -->
    <div class="modal fade" id="editAgentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Emlakçı Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="manage-agents.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_fullname" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="edit_fullname" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_about" class="form-label">Hakkında</label>
                            <textarea class="form-control" id="edit_about" name="about" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Şifre (Boş bırakılırsa değişmez)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Profil Fotoğrafı</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Durum</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Pasif</option>
                            </select>
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

    <!-- Emlakçı Silme Modal -->
    <div class="modal fade" id="deleteAgentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Emlakçı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="manage-agents.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>
                            <strong><span id="delete_fullname"></span></strong> isimli emlakçıyı silmek istediğinizden emin misiniz?
                            Bu işlem geri alınamaz.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Sil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Düzenleme modalı veri doldurma
        document.getElementById('editAgentModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const fullname = button.getAttribute('data-fullname');
            const username = button.getAttribute('data-username');
            const phone = button.getAttribute('data-phone');
            const email = button.getAttribute('data-email');
            const about = button.getAttribute('data-about');
            const status = button.getAttribute('data-status');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_fullname').value = fullname;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_about').value = about;
            document.getElementById('edit_status').value = status;
        });

        // Silme modalı veri doldurma
        document.getElementById('deleteAgentModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const fullname = button.getAttribute('data-fullname');
            
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_fullname').textContent = fullname;
        });
    </script>
</body>
</html> 