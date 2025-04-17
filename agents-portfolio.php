<?php
require_once 'admin/config.php';

// URL'den danışman ID'sini al
$agent_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($agent_id <= 0) {
    header("Location: agents.php");
    exit;
}

// Danışman bilgilerini getir
$stmt = $conn->prepare("SELECT * FROM agents WHERE id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$agent = $stmt->get_result()->fetch_assoc();

if (!$agent) {
    header("Location: agents.php");
    exit;
}

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
                            <li><a href="index.html">Ana Sayfa</a></li>
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
                                <?php if (!empty($agent['facebook_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['facebook_url']); ?>" target="_blank"><i class="bi bi-facebook"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($agent['instagram_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['instagram_url']); ?>" target="_blank"><i class="bi bi-instagram"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($agent['linkedin_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['linkedin_url']); ?>" target="_blank"><i class="bi bi-linkedin"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ Kolon - Sekmeler -->
                    <div class="col-lg-9">
                        <div class="content-tabs">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#about">Hakkımda</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#properties">İlanlar (<?php echo count($properties); ?>)</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Hakkımda Tab -->
                                <div id="about" class="tab-pane fade show active">
                                    <?php if (!empty($agent['about'])): ?>
                                        <?php echo nl2br(htmlspecialchars($agent['about'])); ?>
                                    <?php else: ?>
                                        <p>Henüz hakkında bilgisi eklenmemiş.</p>
                                    <?php endif; ?>
                                </div>

                                <!-- İlanlar Tab -->
                                <div id="properties" class="tab-pane fade">
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
                                                $image_path = $image_data ? $image_data['image_name'] : 'assets/img/property-default.jpg';
                                                ?>
                                                <div class="col-md-6">
                                                    <a href="property-single.php?id=<?php echo $property['id']; ?>" class="property-card">
                                                        <?php if ($property['status'] == 'sale'): ?>
                                                            <div class="property-status status-sale">Satılık</div>
                                                        <?php else: ?>
                                                            <div class="property-status status-rent">Kiralık</div>
                                                        <?php endif; ?>
                                                        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
                                                        <div class="property-info">
                                                            <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                                                            <div class="property-location">
                                                                <i class="bi bi-geo-alt"></i>
                                                                <?php echo htmlspecialchars($property['location']); ?>
                                                                <?php if (!empty($property['neighborhood'])): ?>
                                                                    , <?php echo htmlspecialchars($property['neighborhood']); ?>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="property-details">
                                                                <span><i class="bi bi-house-door"></i> <?php echo $property['net_area']; ?> m²</span>
                                                                <span><i class="bi bi-door-open"></i> <?php echo $property['room_count']; ?> Oda</span>
                                                                <?php if (!empty($property['living_room'])): ?>
                                                                    <span><i class="bi bi-plus-circle"></i> <?php echo $property['living_room']; ?> Salon</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="property-price">
                                                                <?php echo number_format($property['price'], 0, ',', '.'); ?> TL
                                                                <?php if ($property['status'] == 'rent'): ?>
                                                                    <small>/ay</small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </a>
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
                <div class="col-4">
                    <h3><?php echo count($properties); ?></h3>
                    <p>Aktif İlan</p>
                </div>
                <div class="col-4">
                    <h3><?php echo $sales; ?></h3>
                    <p>Satış/Kiralama</p>
                </div>
                <div class="col-4">
                    <h3><?php echo date('Y') - (!empty($agent['start_year']) ? $agent['start_year'] : date('Y')); ?>+</h3>
                    <p>Yıl Deneyim</p>
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