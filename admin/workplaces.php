<?php
session_start();
require_once 'config.php';
require_once 'check_login.php';

// Sayfalama için değişkenler
$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 10;
$offset = ($sayfa - 1) * $limit;

// Toplam iş yeri ilanı sayısını al
$count_sql = "SELECT COUNT(*) as total FROM properties WHERE property_type = 'İş Yeri'";
$count_result = $conn->query($count_sql);
$total_count = $count_result->fetch_assoc()['total'];
$toplam_sayfa = ceil($total_count / $limit);

// İş yeri ilanlarını getir
$sql = "SELECT p.*, 
        (SELECT pi.image_name FROM property_images pi WHERE pi.property_id = p.id LIMIT 1) as image_name
        FROM properties p 
        WHERE p.property_type = 'İş Yeri'
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Yeri İlanları - Nua Yapı Admin</title>
    <link href="../assets/img/nua_logo.jpg" rel="icon">
    <link href="../assets/img/nua_logo.jpg" rel="apple-touch-icon">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        .property-card {
            transition: transform 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .property-details span {
            margin-right: 1rem;
            color: #666;
        }
        .property-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body class="admin-dashboard">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>İş Yeri İlanları</h2>
                    <a href="add-workplace.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Yeni İş Yeri İlanı Ekle
                    </a>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($property = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card property-card h-100">
                                    <div class="position-relative">
                                        <?php
                                        $image_path = !empty($property['image_name']) 
                                            ? "../uploads/properties/" . $property['image_name']
                                            : "../assets/img/no-image.jpg";
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($property['title']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                        <div class="position-absolute top-0 start-0 m-3">
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($property['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                        <p class="card-text">
                                            <i class="bi bi-geo-alt"></i>
                                            <?php 
                                            echo htmlspecialchars($property['location']);
                                            if (!empty($property['neighborhood'])) {
                                                echo ' / ' . htmlspecialchars($property['neighborhood']);
                                            }
                                            ?>
                                        </p>
                                        <div class="property-details mb-3">
                                            <span><i class="bi bi-building"></i> <?php echo htmlspecialchars($property['square_meters']); ?> m²</span>
                                            <?php if (!empty($property['room_count'])): ?>
                                                <span><i class="bi bi-door-open"></i> <?php echo htmlspecialchars($property['room_count']); ?> Bölüm</span>
                                            <?php endif; ?>
                                            <?php if (!empty($property['floor_location'])): ?>
                                                <span><i class="bi bi-layers"></i> <?php echo htmlspecialchars($property['floor_location']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="property-price mb-3">
                                            <?php echo number_format($property['price'], 0, ',', '.'); ?> ₺
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                                                <i class="bi bi-pencil"></i> Düzenle
                                            </a>
                                            <button type="button" class="btn btn-danger" onclick="deleteProperty(<?php echo $property['id']; ?>)">
                                                <i class="bi bi-trash"></i> Sil
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php if ($toplam_sayfa > 1): ?>
                        <nav aria-label="Sayfalama">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $toplam_sayfa; $i++): ?>
                                    <li class="page-item <?php echo $i === $sayfa ? 'active' : ''; ?>">
                                        <a class="page-link" href="?sayfa=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        Henüz iş yeri ilanı bulunmamaktadır.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    function deleteProperty(id) {
        if (confirm('Bu ilanı silmek istediğinizden emin misiniz?')) {
            window.location.href = 'delete-property.php?id=' + id;
        }
    }
    </script>
</body>
</html> 