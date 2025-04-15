<?php
session_start();
require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// İlanları getir
$sql = "SELECT * FROM properties ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Nua Yapı Admin</title>
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
                <img src="../assets/img/nua_logo.jpg" alt="Nua Logo" style="max-height: 60px; border-radius: 50%;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">İlanlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-agents.php">
                            <i class="bi bi-people"></i> Danışman Yönetimi
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html" target="_blank">
                            <i class="bi bi-house"></i> Siteyi Görüntüle
                        </a>
                    </li>
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
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // İlanları ve vitrin fotoğraflarını getir
                                $query = "SELECT p.*, pi.image_name 
                                         FROM properties p 
                                         LEFT JOIN property_images pi ON p.id = pi.property_id 
                                         AND (
                                             pi.is_featured = TRUE 
                                             OR pi.id = (
                                                 SELECT MIN(id) 
                                                 FROM property_images 
                                                 WHERE property_id = p.id 
                                                 AND NOT EXISTS (
                                                     SELECT 1 
                                                     FROM property_images 
                                                     WHERE property_id = p.id 
                                                     AND is_featured = TRUE
                                                 )
                                             )
                                         )
                                         ORDER BY p.id DESC";
                                $result = $conn->query($query);
                                
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td>
                                        <?php if (!empty($row['image_name'])): ?>
                                            <div style="width: 100px; height: 100px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                                <img src="../uploads/<?php echo htmlspecialchars($row['image_name']); ?>" 
                                                     alt="<?php echo htmlspecialchars($row['title']); ?>"
                                                     style="width: 100%; height: 100%; object-fit: contain;">
                                            </div>
                                        <?php else: ?>
                                            <div style="width: 100px; height: 100px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                <div class="text-muted small">Resim yok</div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo number_format($row['price'], 2, ',', '.') . ' TL'; ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo !empty($row['location']) ? htmlspecialchars($row['location']) : 'Didim'; ?></td>
                                    <td><?php echo htmlspecialchars($row['neighborhood']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit-property.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil"></i> Düzenle
                                            </a>
                                            <a href="javascript:void(0);" 
                                               onclick="if(confirm('Bu ilanı silmek istediğinizden emin misiniz?')) window.location.href='delete-property.php?id=<?php echo $row['id']; ?>'" 
                                               class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Sil
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">Henüz ilan bulunmamaktadır.</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    function ilanSil(id) {
        if (confirm('Bu ilanı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
            window.location.href = 'delete-property.php?id=' + id;
        }
    }
    </script>
</body>
</html> 