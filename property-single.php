<?php
require_once 'admin/config.php';

if (!isset($_GET['id'])) {
    header('Location: properties.php');
    exit;
}

$id = (int)$_GET['id'];

// İlan bilgilerini getir
$stmt = $conn->prepare("SELECT *, 
    COALESCE(parking, 'Yok') as parking,
    COALESCE(usage_status, 'Boş') as usage_status,
    COALESCE(video_call_available, 'Hayır') as video_call_available 
FROM properties WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    header('Location: properties.php');
    exit;
}

// İlan resimlerini getir
$img_stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_featured DESC");
$img_stmt->bind_param("i", $id);
$img_stmt->execute();
$images = $img_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($property['title']); ?> - Gayrimenkul</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    .property-details {
      padding: 2rem 0;
    }

    .property-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 1rem;
      text-align: left;
    }

    .property-price {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2563eb;
      margin-bottom: 1rem;
      text-align: left;
    }

    .property-location {
      color: #666;
      margin-bottom: 1.5rem;
      text-align: left;
    }

    .property-location i {
      color: #666;
      margin-right: 0.5rem;
    }

    .detail-item {
      display: flex;
      align-items: center;
      margin-bottom: 0.75rem;
      padding: 0.75rem;
      border: 1px solid #e5e7eb;
      border-radius: 4px;
      background-color: #f8f9fa;
      transition: all 0.2s ease;
      height: 100%;
    }

    .detail-item:hover {
      background-color: #f1f5f9;
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .detail-item i {
      font-size: 1rem;
      color: #2563eb;
      margin-right: 0.75rem;
      width: 20px;
      text-align: center;
    }

    .detail-item span {
      font-size: 0.875rem;
      color: #4b5563;
      margin-right: 0.5rem;
      font-weight: 500;
    }

    .detail-item strong {
      font-size: 0.875rem;
      color: #1f2937;
    }

    .row.g-2 {
      margin: -0.5rem;
    }

    .row.g-2 > [class*="col-"] {
      padding: 0.5rem;
      flex: 0 0 50%;
      max-width: 50%;
    }

    .property-description {
      margin-top: 3rem;
      padding: 2rem;
      background-color: #f8f9fa;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .property-description h3 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #e5e7eb;
    }

    .property-description p {
      line-height: 1.8;
      color: #4b5563;
      margin-bottom: 0;
      font-size: 1rem;
      white-space: pre-line;
    }

    .property-gallery {
      margin-bottom: 2rem;
    }

    .gallery-main {
      margin-bottom: 10px;
      border-radius: 8px;
      overflow: hidden;
      background-color: #fff;
      border: 1px solid #e5e7eb;
      height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .gallery-main img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }

    .property-id {
      color: #6b7280;
      font-size: 0.875rem;
      margin-bottom: 1rem;
      text-align: left;
    }

    .property-id span {
      font-weight: 500;
      color: #4b5563;
    }

    .agent-info {
      background-color: #f8f9fa;
      padding: 1.5rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      border: 1px solid #e5e7eb;
    }

    .agent-info h3 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2c3e50;
    }

    .contact-info {
      margin-top: 1.5rem;
    }

    .contact-info p {
      margin-bottom: 0.75rem;
      color: #4b5563;
      font-size: 0.875rem;
    }

    .contact-info i {
      width: 20px;
      color: #2563eb;
      margin-right: 0.5rem;
    }

    .btn-whatsapp {
      background-color: #25d366;
      color: white;
      border: none;
      padding: 0.75rem 1rem;
      border-radius: 4px;
      font-weight: 500;
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: background-color 0.2s ease;
    }

    .btn-whatsapp:hover {
      background-color: #128c7e;
      color: white;
    }

    @media (max-width: 768px) {
      .gallery-main {
        height: 300px;
      }

      .property-details {
        padding: 1rem 0;
      }

      .detail-item {
        margin-bottom: 0.5rem;
        padding: 0.5rem;
      }

      .row.g-2 > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }

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

<body class="property-single">
  <a href="properties.php" class="back-button">
    <i class="bi bi-arrow-left"></i>
  </a>

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
          <li><a href="properties.php" class="active">İlanlar</a></li>
          <li><a href="agents.html">Danışmanlarımız</a></li>
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
              <li><a href="properties.php">İlanlar</a></li>
              <li class="current"><?php echo htmlspecialchars($property['title']); ?></li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Property Details -->
    <section class="property-details">
      <div class="container">
        <div class="row">
          <!-- Sol Kolon - Fotoğraf Galerisi -->
          <div class="col-lg-4">
            <div class="property-gallery">
              <div class="gallery-main mb-3">
                <?php if (!empty($images)): ?>
                  <img src="uploads/<?php echo htmlspecialchars($images[0]['image_name']); ?>" 
                      alt="Ana Görsel" 
                      id="mainImage"
                      class="img-fluid"
                      style="width: 100%; height: 300px; object-fit: contain;">
                <?php else: ?>
                  <img src="assets/img/no-image.jpg" 
                      alt="<?php echo htmlspecialchars($property['title']); ?>"
                      class="img-fluid">
                <?php endif; ?>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="photo-counter">
                  <span id="currentPhotoIndex">1</span>/<?php echo count($images); ?> Fotoğraf
                </div>
                <div class="gallery-actions">
                  <button class="btn btn-sm btn-outline-primary me-2" onclick="openPhotoModal()">
                    <i class="bi bi-search"></i> Büyük Fotoğraf
                  </button>
                  <?php if (!empty($property['video_file'])): ?>
                  <button class="btn btn-sm btn-outline-primary" onclick="openVideoModal()">
                    <i class="bi bi-play-circle"></i> Video
                  </button>
                  <?php endif; ?>
                </div>
              </div>

              <?php if (count($images) > 1): ?>
              <div class="gallery-thumbs position-relative">
                <button class="gallery-nav prev" onclick="prevPage()">❮</button>
                <div class="gallery-thumbs-container">
                  <div class="gallery-thumbs-wrapper" style="display: flex; transition: transform 0.3s ease;">
                    <?php foreach ($images as $index => $image): ?>
                    <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                         onclick="showImage(<?php echo $index; ?>)"
                         data-index="<?php echo $index; ?>">
                      <img src="uploads/<?php echo htmlspecialchars($image['image_name']); ?>" 
                           alt="Thumbnail <?php echo $index + 1; ?>"
                           class="img-fluid">
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <button class="gallery-nav next" onclick="nextPage()">❯</button>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Orta Kolon - İlan Detayları -->
          <div class="col-lg-3">
            <div class="property-id">
              İlan No: <span><?php echo str_pad($property['id'], 10, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="property-id">
              İlan Tarihi: <span><?php echo date('d.m.Y', strtotime($property['created_at'])); ?></span>
            </div>
            <h1 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h1>
            <div class="property-price">
              <?php echo number_format($property['price'], 0, ',', '.'); ?> TL
            </div>
            <div class="property-location">
              <i class="bi bi-geo-alt"></i>
              <?php 
              echo !empty($property['location']) ? htmlspecialchars($property['location']) : 'Didim';
              if (!empty($property['neighborhood'])) {
                  echo ' / ' . htmlspecialchars($property['neighborhood']);
              }
              ?>
            </div>

            <div class="property-details">
                <?php if ($property['property_type'] == 'Arsa'): ?>
                    <!-- Arsa özellikleri -->
                    <div class="row g-2">
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-building"></i>
                                <span>Durum:</span>
                                <?php echo htmlspecialchars($property['status']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-rulers"></i>
                                <span>m²:</span>
                                <?php echo number_format($property['net_area'], 0, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-clipboard-check"></i>
                                <span>İmar Durumu:</span>
                                <?php echo htmlspecialchars($property['zoning_status']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-geo-alt"></i>
                                <span>Ada No:</span>
                                <?php echo htmlspecialchars($property['block_no']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-geo"></i>
                                <span>Parsel No:</span>
                                <?php echo htmlspecialchars($property['parcel_no']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-map"></i>
                                <span>Pafta No:</span>
                                <?php echo htmlspecialchars($property['sheet_no']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-arrows-angle-expand"></i>
                                <span>Kaks (Emsal):</span>
                                <?php echo htmlspecialchars($property['floor_area_ratio']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-building-up"></i>
                                <span>Gabari:</span>
                                <?php echo htmlspecialchars($property['height_limit']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-credit-card"></i>
                                <span>Krediye Uygun:</span>
                                <?php echo htmlspecialchars($property['eligible_for_credit']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Tapu Durumu:</span>
                                <?php echo htmlspecialchars($property['deed_status']); ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-cash"></i>
                                <span>m² Fiyatı:</span>
                                <?php 
                                    if ($property['net_area'] > 0) {
                                        $price_per_sqm = $property['price'] / $property['net_area'];
                                        echo number_format($price_per_sqm, 2, ',', '.') . ' ₺/m²';
                                    } else {
                                        echo "Hesaplanamadı";
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Konut özellikleri (daire, villa, müstakil ev) -->
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-building"></i>
                                <span>Durum:</span>
                                <strong><?php echo htmlspecialchars($property['status']); ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($property['gross_area'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-rulers"></i>
                                <span>m² (Brüt):</span>
                                <strong><?php echo number_format($property['gross_area'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['net_area'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-rulers"></i>
                                <span>m² (Net):</span>
                                <strong><?php echo number_format($property['net_area'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['room_count'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-door-open"></i>
                                <span>Oda Sayısı:</span>
                                <strong><?php echo htmlspecialchars($property['room_count']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['building_age'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-building-add"></i>
                                <span>Bina Yaşı:</span>
                                <strong><?php echo htmlspecialchars($property['building_age']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['floor'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-layers"></i>
                                <span>Bulunduğu Kat:</span>
                                <strong><?php echo htmlspecialchars($property['floor']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['total_floors'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-building"></i>
                                <span>Kat Sayısı:</span>
                                <strong><?php echo htmlspecialchars($property['total_floors']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['heating'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-thermometer-half"></i>
                                <span>Isıtma:</span>
                                <strong><?php echo htmlspecialchars($property['heating']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['bathroom_count'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-droplet"></i>
                                <span>Banyo Sayısı:</span>
                                <strong><?php echo htmlspecialchars($property['bathroom_count']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['balcony'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-door-closed"></i>
                                <span>Balkon:</span>
                                <strong><?php echo htmlspecialchars($property['balcony']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['parking'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-p-square"></i>
                                <span>Otopark:</span>
                                <strong><?php echo htmlspecialchars($property['parking']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['furnished'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-box"></i>
                                <span>Eşyalı:</span>
                                <strong><?php echo htmlspecialchars($property['furnished']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['usage_status'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-house-gear"></i>
                                <span>Kullanım Durumu:</span>
                                <strong><?php echo htmlspecialchars($property['usage_status']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['site_status'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-buildings"></i>
                                <span>Site İçerisinde:</span>
                                <strong><?php echo htmlspecialchars($property['site_status']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['site_name'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-building-check"></i>
                                <span>Site Adı:</span>
                                <strong><?php echo htmlspecialchars($property['site_name']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['eligible_for_credit'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-credit-card"></i>
                                <span>Krediye Uygun:</span>
                                <strong><?php echo htmlspecialchars($property['eligible_for_credit']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['video_call_available'])): ?>
                        <div class="col-6">
                            <div class="detail-item">
                                <i class="bi bi-camera-video"></i>
                                <span>Görüntülü Arama:</span>
                                <strong><?php echo htmlspecialchars($property['video_call_available']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
          </div>

          <!-- Sağ Kolon - Firma ve İletişim Bilgileri -->
          <div class="col-lg-3">
            <div class="agent-info">
              <div class="agent-logo text-center mb-4">
                <img src="assets/img/nua_logo.jpg" alt="Nua Yapı" class="img-fluid rounded-circle" style="max-width: 150px;">
              </div>
              <h3 class="text-center mb-4">NUA YAPI</h3>
              <div class="contact-info">
                <p><i class="bi bi-person"></i> Ayşenur Eker</p>
                <p><i class="bi bi-telephone"></i> <a href="tel:05304416873" class="text-dark text-decoration-none">0 (530) 441 68 73</a></p>
                <p><i class="bi bi-envelope"></i> <a href="mailto:bilgi@didim.com" class="text-dark text-decoration-none">bilgi@didim.com</a></p>
                <a href="https://wa.me/905304416873?text=<?php 
                $propertyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $message = $property['title'] . " ilanı hakkında bilgi almak istiyorum.\n\nİlan No: " . str_pad($property['id'], 10, '0', STR_PAD_LEFT) . "\n\nİlan detayları: " . $propertyUrl;
                echo urlencode($message);
                ?>" 
                   class="btn btn-whatsapp w-100 mt-3" 
                   target="_blank">
                  <i class="bi bi-whatsapp"></i> WhatsApp'tan Mesaj Gönder
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- İlan Açıklaması -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="property-description">
              <h3>İlan Açıklaması</h3>
              <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer id="footer" class="footer light-background">
    <div class="container">
      <div class="row gy-3">
        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-geo-alt icon text-success"></i>
          <div>
            <h4>Adres</h4>
            <p>Efeler, Kavala Cd. Aydın Apartmanı No:24/A, 09270</p>
            <p>Didim/Aydın</p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-telephone icon text-success"></i>
          <div>
            <h4>İletişim</h4>
            <p>
              <strong>Telefon:</strong> <a href="tel:05304416873" class="text-success">0530 441 68 73</a><br>
              <strong>Email:</strong> <a href="mailto:bilgi@didim.com" class="text-success">bilgi@didim.com</a>
            </p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-clock icon text-success"></i>
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
      <p class="mb-1">© <span>Telif Hakkı</span> <strong class="px-1">Nua Yapı</strong><span>'ya aittir</span></p>
      <div class="credits">
        Bu site <a href="https://firatdalkilic.com/" target="_blank" class="text-success">Fırat Dalkılıç</a> tarafından yapılmıştır.
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
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    let currentPage = 0;
    const thumbsPerPage = 6;
    const totalImages = <?php echo count($images); ?>;
    const totalPages = Math.ceil(totalImages / thumbsPerPage);

    function showImage(index) {
      // Ana görseli güncelle
      document.getElementById('mainImage').src = document.querySelector(`.gallery-thumb[data-index="${index}"] img`).src;
      
      // Aktif thumb'ı güncelle
      document.querySelectorAll('.gallery-thumb').forEach(thumb => thumb.classList.remove('active'));
      document.querySelector(`.gallery-thumb[data-index="${index}"]`).classList.add('active');
      
      // Sayacı güncelle
      document.getElementById('currentPhotoIndex').textContent = index + 1;
    }

    function updateGalleryPosition() {
      const wrapper = document.querySelector('.gallery-thumbs-wrapper');
      const thumbWidth = 110; // thumb genişliği + margin
      wrapper.style.transform = `translateX(-${currentPage * thumbWidth * thumbsPerPage}px)`;
    }

    function nextPage() {
      if (currentPage < totalPages - 1) {
        currentPage++;
        updateGalleryPosition();
      }
    }

    function prevPage() {
      if (currentPage > 0) {
        currentPage--;
        updateGalleryPosition();
      }
    }

    // Büyük fotoğraf modalı
    function openPhotoModal() {
      const currentSrc = document.getElementById('mainImage').src;
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        cursor: pointer;
      `;
      
      const img = document.createElement('img');
      img.src = currentSrc;
      img.style.cssText = `
        max-width: 90%;
        max-height: 90vh;
        object-fit: contain;
      `;
      
      modal.appendChild(img);
      modal.onclick = () => modal.remove();
      document.body.appendChild(modal);
    }

    // Video modalı
    function openVideoModal() {
      const videoFile = '<?php echo !empty($property['video_file']) ? "uploads/videos/" . htmlspecialchars($property['video_file']) : ""; ?>';
      if (!videoFile) return;

      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        cursor: pointer;
      `;
      
      const video = document.createElement('video');
      video.src = videoFile;
      video.controls = true;
      video.style.cssText = `
        max-width: 90%;
        max-height: 90vh;
      `;
      
      modal.appendChild(video);
      modal.onclick = (e) => {
        if (e.target === modal) modal.remove();
      };
      document.body.appendChild(modal);
    }
  </script>

</body>

</html>