<?php
require_once 'admin/config.php';

// Danışman ID'sini kontrol et
if (!isset($_GET['id'])) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

$agent_id = (int)$_GET['id'];

// Danışmanı veritabanından al
$agent_query = "SELECT * FROM agents WHERE id = ?";
$stmt = $conn->prepare($agent_query);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();

// Danışman bulunamadıysa 404 sayfasına yönlendir
if ($result->num_rows === 0) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}

$agent = $result->fetch_assoc();

// Danışmanın ilanlarını getir
$stmt = $conn->prepare("SELECT * FROM properties WHERE agent_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Danışmanın toplam satış/kiralama sayısını getir
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM properties WHERE agent_id = ? AND status = 'sold'");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$sales = $stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo htmlspecialchars($agent['agent_name']); ?> - Nua Yapı</title>

    <!-- Favicons -->
    <link href="assets/img/nua_logo.jpg" rel="icon">
    <link href="assets/img/nua_logo.jpg" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <style>
        .agent-profile {
            padding: 40px 0;
            background-color: #f8f9fa;
        }

        .agent-info-card {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .agent-photo {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .agent-name {
            font-size: 24px;
            font-weight: 600;
            color: #002e5c;
            margin-bottom: 5px;
        }

        .agent-title {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .agent-contact {
            margin: 25px 0;
        }

        .agent-contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: #333;
        }

        .agent-contact-item i {
            width: 24px;
            color: #002e5c;
            margin-right: 10px;
        }

        .agent-contact-item a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }

        .agent-contact-item a:hover {
            color: #002e5c;
        }

        .agent-social {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .agent-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f8f9fa;
            color: #002e5c;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .agent-social a:hover {
            background: #002e5c;
            color: #fff;
        }

        .platform-icons {
            margin-top: 15px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .platform-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }

        .platform-icons a:hover {
            color: #002e5c;
        }

        .platform-icons img {
            width: 24px;
            height: 24px;
            margin-right: 5px;
        }

        .platform-icons span {
            font-size: 14px;
        }

        .nav-tabs {
            border: none;
            margin-bottom: 20px;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .nav-tabs .nav-link {
            border: none;
            color: #666;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link.active {
            background: #002e5c;
            color: #fff;
        }

        .tab-content {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .property-card {
            border: none;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .property-card:hover {
            transform: translateY(-5px);
            text-decoration: none;
        }

        .property-image {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .property-info {
            padding: 20px;
            background: #fff;
        }

        .property-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #002e5c;
        }

        .property-location {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .property-location i {
            color: #002e5c;
            margin-right: 5px;
        }

        .property-details {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }

        .property-details span {
            display: flex;
            align-items: center;
        }

        .property-details i {
            margin-right: 5px;
            color: #002e5c;
        }

        .property-price {
            font-size: 20px;
            font-weight: 600;
            color: #002e5c;
            margin-top: 10px;
        }

        .property-status {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-sale {
            background: #002e5c;
            color: #fff;
        }

        .status-rent {
            background: #28a745;
            color: #fff;
        }

        .breadcrumbs {
            background: #002e5c;
            padding: 30px 0;
            margin-top: 90px;
            color: #fff;
        }

        .breadcrumbs ol {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 15px;
        }

        .breadcrumbs ol li + li {
            padding-left: 10px;
        }

        .breadcrumbs ol li + li::before {
            display: inline-block;
            padding-right: 10px;
            color: #fff;
            content: "/";
        }

        .breadcrumbs ol li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumbs ol li.current {
            color: #fff;
            font-weight: 600;
        }

        .about-content {
            color: #666;
            line-height: 1.8;
        }

        .stats-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 30px;
        }

        .stats-box h3 {
            font-size: 24px;
            color: #002e5c;
            margin-bottom: 5px;
        }

        .stats-box p {
            color: #666;
            margin: 0;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="assets/img/nua_logo.jpg" alt="Nua Logo" style="max-height: 60px; border-radius: 50%;">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.html">Anasayfa</a></li>
                    <li><a href="about.html">Hakkımızda</a></li>
                    <li><a href="services.html">Hizmetlerimiz</a></li>
                    <li><a href="properties.php">İlanlar</a></li>
                    <li><a href="agents.php" class="active">Danışmanlarımız</a></li>
                    <li><a href="contact.html">İletişim</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>

    <main id="main">
        <!-- Breadcrumbs -->
        <div class="breadcrumbs">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <ol>
                            <li><a href="index.html">Anasayfa</a></li>
                            <li><a href="agents.php">Danışmanlarımız</a></li>
                            <li class="current"><?php echo htmlspecialchars($agent['agent_name']); ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent Profile Section -->
        <section class="agent-profile">
            <div class="container-fluid">
                <div class="row">
                    <!-- Sol Kolon - Danışman Bilgileri -->
                    <div class="col-lg-3">
                        <div class="agent-info-card text-center">
                            <?php
                            $agent_photo = 'assets/img/nua_logo.jpg';
                            if (!empty($agent['image']) && file_exists($agent['image'])) {
                                $agent_photo = $agent['image'];
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($agent_photo); ?>" alt="<?php echo htmlspecialchars($agent['agent_name']); ?>" class="agent-photo">
                            
                            <h2 class="agent-name"><?php echo htmlspecialchars($agent['agent_name']); ?></h2>
                            
                            <div class="agent-title">
                                <?php echo !empty($agent['agent_title']) ? htmlspecialchars($agent['agent_title']) : 'Gayrimenkul Danışmanı'; ?>
                                <div class="platform-icons">
                                <?php if (!empty($agent['sahibinden_link'])): ?>
                                  <a href="<?php echo htmlspecialchars($agent['sahibinden_link']); ?>" target="_blank" title="Sahibinden.com Mağazası" class="social-icon">
                                      <img src="assets/img/platforms/sahibinden-icon.png" alt="Sahibinden.com">
                                  </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($agent['emlakjet_link'])): ?>
                                  <a href="<?php echo htmlspecialchars($agent['emlakjet_link']); ?>" target="_blank" title="Emlakjet Profili" class="social-icon">
                                      <img src="assets/img/platforms/emlakjet-icon.png" alt="Emlakjet">
                                  </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($agent['facebook_link'])): ?>
                                  <a href="<?php echo htmlspecialchars($agent['facebook_link']); ?>" target="_blank" title="Facebook" class="social-icon facebook">
                                      <i class="bi bi-facebook"></i>
                                  </a>
                                <?php endif; ?>
                                </div>
                            </div>

                            <div class="agent-contact">
                                <div class="agent-contact-item">
                                    <i class="bi bi-telephone"></i>
                                    <a href="tel:<?php echo htmlspecialchars($agent['phone']); ?>"><?php echo htmlspecialchars($agent['phone']); ?></a>
                                </div>
                                <div class="agent-contact-item">
                                    <i class="bi bi-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($agent['email']); ?>"><?php echo htmlspecialchars($agent['email']); ?></a>
                                </div>
                            </div>

                            <div class="agent-social">
                                <?php if (!empty($agent['instagram_username'])): ?>
                                <a href="https://www.instagram.com/<?php echo htmlspecialchars($agent['instagram_username']); ?>" target="_blank" title="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($agent['twitter_username'])): ?>
                                <a href="https://twitter.com/<?php echo htmlspecialchars($agent['twitter_username']); ?>" target="_blank" title="Twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($agent['linkedin_username'])): ?>
                                <a href="https://www.linkedin.com/in/<?php echo htmlspecialchars($agent['linkedin_username']); ?>" target="_blank" title="LinkedIn">
                                    <i class="bi bi-linkedin"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ Kolon - Sekmeler -->
                    <div class="col-lg-9">
                        <div class="content-tabs">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#properties">İlanlar (<?php echo count($properties); ?>)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#about">Hakkımda</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Hakkımda Tab -->
                                <div id="about" class="tab-pane fade">
                                    <?php if (!empty($agent['about'])): ?>
                                        <?php echo nl2br(htmlspecialchars($agent['about'])); ?>
                                    <?php else: ?>
                                        <p>Henüz hakkında bilgisi eklenmemiş.</p>
                                    <?php endif; ?>
                                </div>

                                <!-- İlanlar Tab -->
                                <div id="properties" class="tab-pane fade show active">
                                    <?php if (!empty($properties)): ?>
                                        <div class="row">
                                            <?php foreach ($properties as $property): ?>
                                                <?php
                                                // İlan fotoğrafını al
                                                $image_query = "SELECT image_name FROM property_images WHERE property_id = ? AND is_featured = TRUE LIMIT 1";
                                                $stmt = $conn->prepare($image_query);
                                                $stmt->bind_param("i", $property['id']);
                                                $stmt->execute();
                                                $image_result = $stmt->get_result();
                                                $image_data = $image_result->fetch_assoc();
                                                
                                                $image_path = 'assets/img/property-default.jpg';
                                                if ($image_data && !empty($image_data['image_name'])) {
                                                    if (file_exists($image_data['image_name'])) {
                                                        $image_path = $image_data['image_name'];
                                                    } elseif (file_exists('uploads/' . basename($image_data['image_name']))) {
                                                        $image_path = 'uploads/' . basename($image_data['image_name']);
                                                    }
                                                }
                                                
                                                if (!file_exists($image_path)) {
                                                    $image_path = 'assets/img/nua_logo.jpg';
                                                }
                                                ?>
                                                <div class="col-md-6 col-lg-4 mb-4">
                                                    <div class="property-card h-100">
                                                        <a href="property-single.php?id=<?php echo $property['id']; ?>" class="text-decoration-none">
                                                            <div class="position-relative">
                                                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                                                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                                                     class="img-fluid w-100" 
                                                                     style="height: 200px; object-fit: cover;">
                                                                <div class="position-absolute top-0 start-0 m-3">
                                                                    <span class="badge <?php echo $property['status'] == 'sale' ? 'bg-primary' : 'bg-success'; ?>">
                                                                        <?php echo $property['status'] == 'sale' ? 'Satılık' : 'Kiralık'; ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="card-body p-3">
                                                                <h5 class="card-title text-dark mb-1"><?php echo htmlspecialchars($property['title']); ?></h5>
                                                                <p class="property-location mb-2">
                                                                    <i class="bi bi-geo-alt"></i>
                                                                    <?php echo htmlspecialchars($property['location']); ?>
                                                                    <?php if (!empty($property['neighborhood'])): ?>
                                                                        , <?php echo htmlspecialchars($property['neighborhood']); ?>
                                                                    <?php endif; ?>
                                                                </p>
                                                                <div class="property-details mb-2">
                                                                    <span class="detail-item">
                                                                        <i class="bi bi-house-door"></i>
                                                                        <?php echo $property['net_area']; ?> m²
                                                                    </span>
                                                                    <span class="detail-item">
                                                                        <i class="bi bi-door-open"></i>
                                                                        <?php echo $property['room_count']; ?> Oda
                                                                    </span>
                                                                    <?php if (!empty($property['living_room'])): ?>
                                                                        <span class="detail-item">
                                                                            <i class="bi bi-plus-circle"></i>
                                                                            <?php echo $property['living_room']; ?> Salon
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="property-price fw-bold text-primary">
                                                                    <?php echo number_format($property['price'], 0, ',', '.'); ?> TL
                                                                    <?php if ($property['status'] == 'rent'): ?>
                                                                        <small class="text-muted">/ay</small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p>Henüz ilan eklenmemiş.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container mt-4">
            <div class="stats-box row">
                <div class="col-12">
                    <p><?php echo count($properties); ?> adet aktif ilan listelenmiştir</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer">
        <div class="container">
            <div class="row gy-3">
                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-geo-alt icon"></i>
                    <div>
                        <h4>Adres</h4>
                        <p>Efeler, Kavala Cd. Aydın Apartmanı No:24/A, 09270</p>
                        <p>Didim/Aydın</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-telephone icon"></i>
                    <div>
                        <h4>İletişim</h4>
                        <p>
                            <strong>Telefon:</strong> <a href="tel:05304416873">0530 441 68 73</a><br>
                            <strong>Email:</strong> <a href="mailto:bilgi@didim.com">bilgi@didim.com</a>
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-clock icon"></i>
                    <div>
                        <h4>Çalışma Saatleri</h4>
                        <p>
                            <strong>Pzts-Cmts:</strong> 9:00 - 18:00<br>
                            <strong>Pazar:</strong> Kapalı
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h4>Bizi Takip Edin</h4>
                    <div class="social-links d-flex">
                        <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Telif Hakkı</span> <strong class="px-1">Nua Yapı</strong><span>'ya aittir</span></p>
            <div class="credits">
                Bu site <a href="https://firatdalkilic.com/" target="_blank">Fırat Dalkılıç</a> tarafından yapılmıştır.
            </div>
        </div>
    </footer>

    <!-- Scroll Top Button -->
    <a href="#" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>
</body>
</html> 