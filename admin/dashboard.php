<?php
require_once 'session_manager.php';
require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// İlan silme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    
    // Danışman ise sadece kendi ilanlarını silebilir
    if (isAgent()) {
        $sql = "DELETE FROM properties WHERE id = ? AND agent_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $delete_id, getAgentId());
    } else {
        // Admin tüm ilanları silebilir
        $sql = "DELETE FROM properties WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "İlan başarıyla silindi.";
    } else {
        $_SESSION['error'] = "İlan silinirken bir hata oluştu.";
    }
    
    header("Location: dashboard.php");
    exit;
}

// İlanları getir
if (isAgent()) {
    // Danışman sadece kendi ilanlarını görür
    $sql = "SELECT p.*, a.agent_name FROM properties p 
            LEFT JOIN agents a ON p.agent_id = a.id 
            WHERE p.agent_id = ? 
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $agent_id = getAgentId();
    $stmt->bind_param("i", $agent_id);
} else {
    // Admin tüm ilanları görür
    $sql = "SELECT p.*, a.agent_name FROM properties p 
            LEFT JOIN agents a ON p.agent_id = a.id 
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nua Yapı Admin</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
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
                        <a class="nav-link active" href="dashboard.php">İlanlar</a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-agents.php">Danışmanlar</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html" target="_blank">
                            <i class="bi bi-house"></i> Siteyi Görüntüle
                        </a>
                    </li>
                    <?php if (isAgent()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['agent_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item" href="edit-profile.php">
                                    <i class="bi bi-pencil-square me-2"></i>Profili Düzenle
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="change-password.php">
                                    <i class="bi bi-key me-2"></i>Şifre Değiştir
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="change-password.php">
                            <i class="bi bi-key"></i> Şifre Değiştir
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <a href="add-property.php" class="btn btn-primary me-2">
                        <i class="fas fa-plus"></i> Yeni İlan Ekle
                    </a>
                    <a href="add-land.php" class="btn btn-success me-2">
                        <i class="fas fa-plus"></i> Arsa İlanı Ekle
                    </a>
                </div>
            </div>

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

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Resim</th>
                                    <th>Başlık</th>
                                    <th>Fiyat</th>
                                    <th>Durum</th>
                                    <th>Konum</th>
                                    <th>Mahalle</th>
                                    <?php if (isAdmin()): ?>
                                    <th>Danışman</th>
                                    <?php endif; ?>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($properties as $property): ?>
                                <tr>
                                    <td><?php echo $property['id']; ?></td>
                                    <td>
                                        <?php if (!empty($property['image_name'])): ?>
                                            <div style="width: 100px; height: 100px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                                <img src="../uploads/<?php echo htmlspecialchars($property['image_name']); ?>" 
                                                     alt="<?php echo htmlspecialchars($property['title']); ?>"
                                                     style="width: 100%; height: 100%; object-fit: contain;">
                                            </div>
                                        <?php else: ?>
                                            <div style="width: 100px; height: 100px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                <div class="text-muted small">Resim yok</div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($property['title']); ?></td>
                                    <td><?php echo number_format($property['price'], 2, ',', '.') . ' TL'; ?></td>
                                    <td><?php echo htmlspecialchars($property['status']); ?></td>
                                    <td><?php echo !empty($property['location']) ? htmlspecialchars($property['location']) : 'Didim'; ?></td>
                                    <td><?php echo htmlspecialchars($property['neighborhood']); ?></td>
                                    <?php if (isAdmin()): ?>
                                    <td><?php echo htmlspecialchars($property['agent_name'] ?? 'Admin'); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit-property.php?id=<?php echo $property['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil"></i> Düzenle
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal" 
                                                    data-id="<?php echo $property['id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($property['title']); ?>">
                                                <i class="bi bi-trash"></i> Sil
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="modal-header">
                        <h5 class="modal-title">İlanı Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bu ilanı silmek istediğinizden emin misiniz?</p>
                        <p class="text-danger" id="delete_title"></p>
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
        // Delete modal için veri aktarımı
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_id').value = this.dataset.id;
                document.getElementById('delete_title').textContent = this.dataset.title;
            });
        });
    </script>
</body>
</html> 