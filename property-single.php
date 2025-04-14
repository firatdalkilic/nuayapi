<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'admin/config.php';
    
    if (!isset($conn) || !$conn) {
        throw new Exception("Veritabanı bağlantısı kurulamadı.");
    }

    // URL'den id parametresini al
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        throw new Exception("Geçersiz ilan ID'si");
    }

    // Test connection and basic query
    $test_query = "SELECT 1";
    if (!$conn->query($test_query)) {
        throw new Exception("Veritabanı bağlantı testi başarısız: " . $conn->error);
    }

    // İlan bilgilerini getir
    $stmt = $conn->prepare("SELECT *, 
        COALESCE(parking, 'Yok') as parking,
        COALESCE(usage_status, 'Boş') as usage_status,
        COALESCE(video_call_available, 'Hayır') as video_call_available,
        COALESCE(room_count, '') as room_count,
        COALESCE(living_room_count, '') as living_room_count,
        COALESCE(floor, '') as floor
    FROM properties WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Sorgu hazırlanamadı: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Sorgu çalıştırılamadı: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Sonuç alınamadı: " . $stmt->error);
    }
    
    $property = $result->fetch_assoc();
    if (!$property) {
        throw new Exception("İlan bulunamadı");
    }

    // Fotoğrafları getir
    $images_stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY id ASC");
    if (!$images_stmt) {
        throw new Exception("Fotoğraf sorgusu hazırlanamadı: " . $conn->error);
    }
    
    $images_stmt->bind_param("i", $id);
    
    if (!$images_stmt->execute()) {
        throw new Exception("Fotoğraf sorgusu çalıştırılamadı: " . $images_stmt->error);
    }
    
    $images_result = $images_stmt->get_result();
    if (!$images_result) {
        throw new Exception("Fotoğraf sonuçları alınamadı: " . $images_stmt->error);
    }
    
    $images = [];
    while ($image = $images_result->fetch_assoc()) {
        $images[] = $image;
    }

} catch (Exception $e) {
    // Hata mesajını göster
    echo "Bir hata oluştu: " . htmlspecialchars($e->getMessage());
    exit;
}
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
      position: relative;
    }

    .gallery-main {
      margin-bottom: 10px;
      border-radius: 8px;
      overflow: hidden;
      background-color: #f8f9fa;
      border: 1px solid #e5e7eb;
      height: 0;
      padding-bottom: 75%; /* 4:3 oranı için */
      position: relative;
      cursor: pointer;
    }

    .gallery-main img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: contain;
      object-position: center;
      display: block;
    }

    .gallery-counter {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0, 0, 0, 0.7);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 14px;
      z-index: 10;
    }

    .gallery-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin: 10px 0;
    }

    .gallery-btn {
      background: #f8f9fa;
      border: 1px solid #e5e7eb;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
      transition: all 0.3s ease;
      color: #2c3e50;
      text-decoration: none;
    }

    .gallery-btn:hover {
      background: #e9ecef;
      transform: translateY(-2px);
      color: #2c3e50;
    }

    .gallery-thumbnails-container {
      position: relative;
      margin-bottom: 20px;
      overflow: hidden;
    }

    .gallery-thumbnails {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      grid-template-rows: repeat(2, 100px);
      gap: 10px;
    }

    .gallery-thumbnail {
      height: 100px;
      border-radius: 4px;
      overflow: hidden;
      cursor: pointer;
      border: 2px solid transparent;
      transition: all 0.3s ease;
      background-color: #f8f9fa;
    }

    .gallery-thumbnail.active {
      border-color: #2563eb;
    }

    .gallery-thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      object-position: center;
      display: block;
    }

    .gallery-pagination {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 15px;
    }

    .gallery-pagination-btn {
      background: #f8f9fa;
      border: 1px solid #e5e7eb;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .gallery-pagination-btn:hover {
      background: #e9ecef;
    }

    .gallery-pagination-btn.disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .gallery-pagination-dots {
      display: flex;
      gap: 5px;
    }

    .gallery-pagination-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #e5e7eb;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .gallery-pagination-dot.active {
      background: #2563eb;
    }

    .gallery-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.9);
      border: none;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 10;
      transition: all 0.3s ease;
    }

    .gallery-nav:hover {
      background: white;
      transform: translateY(-50%) scale(1.1);
    }

    .gallery-nav.prev {
      left: 20px;
    }

    .gallery-nav.next {
      right: 20px;
    }

    @media (max-width: 768px) {
      .gallery-thumbnails {
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 100px);
      }
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

    /* Modal Stilleri */
    .photo-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.9);
      z-index: 9999;
    }

    .modal-content {
      position: relative;
      width: 80%;
      height: 90%;
      margin: 2% auto;
    }

    .modal-image {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .modal-close {
      position: absolute;
      top: -30px;
      right: 0;
      color: #fff;
      font-size: 24px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 5px;
    }

    .modal-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      color: #fff;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      border-radius: 50%;
      font-size: 20px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.1);
      z-index: 1000;
    }

    .modal-nav:hover {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    .modal-prev {
      left: 20px;
    }

    .modal-next {
      right: 20px;
    }

    @media (max-width: 768px) {
      .modal-content {
        width: 95%;
      }
      .modal-nav {
        width: 35px;
        height: 35px;
        font-size: 18px;
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
          <div class="col-lg-5">
            <div class="property-gallery">
              <div class="gallery-main">
                <?php if (!empty($images)): ?>
                  <img src="uploads/<?php echo htmlspecialchars($images[0]['image_name']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" id="mainImage" onclick="changeImage(1)">
                  <div class="gallery-counter">
                    <span id="currentImageIndex">1</span>/<span id="totalImages"><?php echo count($images); ?></span>
                  </div>
                <?php endif; ?>
              </div>

              <div class="gallery-actions">
                <a href="#" class="gallery-btn" onclick="openFullscreen(); return false;">
                  <i class="bi bi-arrows-fullscreen"></i> Büyük Fotoğraf
                </a>
                <?php if (!empty($property['video_url'])): ?>
                <a href="<?php echo htmlspecialchars($property['video_url']); ?>" class="gallery-btn" target="_blank">
                  <i class="bi bi-play-circle"></i> Video
                </a>
                <?php endif; ?>
              </div>

              <div class="gallery-thumbnails-container">
                <div class="gallery-thumbnails" id="galleryThumbnails">
                  <?php 
                  $currentPageImages = array_slice($images, 0, 10);
                  foreach ($currentPageImages as $index => $image): 
                  ?>
                    <div class="gallery-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                         onclick="selectImage(<?php echo $index; ?>)">
                      <img src="uploads/<?php echo htmlspecialchars($image['image_name']); ?>" 
                           alt="<?php echo htmlspecialchars($property['title']); ?> - Resim <?php echo $index + 1; ?>">
                    </div>
                  <?php endforeach; ?>
                </div>
                <?php if (count($images) > 10): ?>
                <div class="gallery-pagination">
                  <button class="gallery-pagination-btn prev-page" onclick="changePage(-1)">
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  <div class="gallery-pagination-dots">
                    <?php 
                      $totalPages = ceil(count($images) / 10);
                      for ($i = 0; $i < $totalPages; $i++):
                    ?>
                      <div class="gallery-pagination-dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                           onclick="goToPage(<?php echo $i; ?>)"></div>
                    <?php endfor; ?>
                  </div>
                  <button class="gallery-pagination-btn next-page" onclick="changePage(1)">
                    <i class="bi bi-chevron-right"></i>
                  </button>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Orta Kolon - İlan Detayları -->
          <div class="col-lg-4">
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
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-house"></i>
                                <span>Durum:</span>
                                <strong><?php echo htmlspecialchars($property['status']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-arrows-angle-expand"></i>
                                <span>m² (Brüt):</span>
                                <strong><?php echo !empty($property['gross_area']) ? htmlspecialchars($property['gross_area']) : '-'; ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-arrows-angle-contract"></i>
                                <span>m² (Net):</span>
                                <strong><?php echo !empty($property['net_area']) ? htmlspecialchars($property['net_area']) : '-'; ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-calendar3"></i>
                                <span>Bina Yaşı:</span>
                                <strong><?php 
                                $building_age = $property['building_age'];
                                if ($building_age !== null && $building_age !== '') {
                                    if ($building_age === '0' || $building_age === 0) {
                                        echo '0 (Yeni)';
                                    } elseif ($building_age == '11' || ($building_age >= 11 && $building_age <= 15)) {
                                        echo '11-15';
                                    } elseif ($building_age == '16' || ($building_age >= 16 && $building_age <= 20)) {
                                        echo '16-20';
                                    } elseif ($building_age == '21' || ($building_age >= 21 && $building_age <= 25)) {
                                        echo '21-25';
                                    } elseif ($building_age == '26' || $building_age >= 26) {
                                        echo '26+';
                                    } else {
                                        echo htmlspecialchars($building_age);
                                    }
                                } else {
                                    echo '-';
                                }
                                ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-layers"></i>
                                <span>Kat Sayısı:</span>
                                <strong><?php echo !empty($property['total_floors']) ? htmlspecialchars($property['total_floors']) : '-'; ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-arrow-up-square"></i>
                                <span>Bulunduğu Kat:</span>
                                <?php 
                                $floor_options = [
                                    'Bodrum Kat', 'Yarı Bodrum Kat', 'Zemin Kat', 'Bahçe Katı', 'Yüksek Giriş',
                                    '1. Kat', '2. Kat', '3. Kat', '4. Kat', '5. Kat', '6. Kat', '7. Kat', '8. Kat',
                                    '9. Kat', '10. Kat', '11. Kat', '12. Kat ve üzeri', 'Çatı Katı'
                                ];
                                $floor = isset($property['floor_location']) && in_array($property['floor_location'], $floor_options) 
                                    ? $property['floor_location'] 
                                    : '-';
                                ?>
                                <strong><?php echo htmlspecialchars($floor); ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($property['room_count'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-door-closed"></i>
                                <span>Oda Sayısı:</span>
                                <strong><?php echo htmlspecialchars($property['room_count']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['living_room_count'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-tv"></i>
                                <span>Salon Sayısı:</span>
                                <strong><?php echo htmlspecialchars($property['living_room_count']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['heating'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-thermometer-half"></i>
                                <span>Isıtma:</span>
                                <strong><?php echo htmlspecialchars($property['heating']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['bathroom_count'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-droplet"></i>
                                <span>Banyo Sayısı:</span>
                                <strong><?php echo htmlspecialchars($property['bathroom_count']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['balcony'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-door-closed"></i>
                                <span>Balkon:</span>
                                <strong><?php echo htmlspecialchars($property['balcony']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['parking'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-p-square"></i>
                                <span>Otopark:</span>
                                <strong><?php echo htmlspecialchars($property['parking']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['furnished'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-box"></i>
                                <span>Eşyalı:</span>
                                <strong><?php echo htmlspecialchars($property['furnished']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['usage_status'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-house-gear"></i>
                                <span>Kullanım Durumu:</span>
                                <strong><?php echo htmlspecialchars($property['usage_status']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['site_status'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-buildings"></i>
                                <span>Site İçerisinde:</span>
                                <strong><?php echo htmlspecialchars($property['site_status']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['site_name'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-building-check"></i>
                                <span>Site Adı:</span>
                                <strong><?php echo htmlspecialchars($property['site_name']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['eligible_for_credit'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-credit-card"></i>
                                <span>Krediye Uygun:</span>
                                <strong><?php echo htmlspecialchars($property['eligible_for_credit']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['video_call_available'])): ?>
                        <div class="col-md-6">
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

  <!-- Modal -->
  <div id="photoModal" class="photo-modal" onclick="closeModalFromOverlay(event)">
    <div class="modal-content">
      <button class="modal-close" onclick="closeModal()">
        <i class="bi bi-x-lg"></i>
      </button>
      <img id="modalImage" class="modal-image" src="" alt="">
      <a href="#" class="modal-nav modal-prev" onclick="changeModalImage(-1); return false;">
        <i class="bi bi-chevron-left"></i>
      </a>
      <a href="#" class="modal-nav modal-next" onclick="changeModalImage(1); return false;">
        <i class="bi bi-chevron-right"></i>
      </a>
    </div>
  </div>

  <script>
    let currentImageIndex = 0;
    const images = <?php echo json_encode(array_map(function($img) { 
        return 'uploads/' . $img['image_name']; 
    }, $images)); ?>;
    const totalImages = images.length;
    const allImages = <?php echo json_encode(array_map(function($img) {
        return ['image_path' => 'uploads/' . $img['image_name']];
    }, $images)); ?>;
    const imagesPerPage = 10;
    const totalPages = Math.ceil(totalImages / imagesPerPage);
    let currentPage = 0;

    function changeImage(direction) {
      currentImageIndex = (currentImageIndex + direction + totalImages) % totalImages;
      updateImage();
      syncPageWithImage();
    }

    function selectImage(index) {
      currentImageIndex = index;
      updateImage();
      syncPageWithImage();
    }

    function updateImage() {
      const mainImage = document.getElementById('mainImage');
      const counter = document.getElementById('currentImageIndex');
      
      mainImage.src = images[currentImageIndex];
      counter.textContent = currentImageIndex + 1;

      // Tüm thumbnail'ları güncelle
      updateThumbnailsActiveState();
    }

    function updateThumbnailsActiveState() {
      document.querySelectorAll('.gallery-thumbnail').forEach((thumb, i) => {
        const absoluteIndex = currentPage * imagesPerPage + i;
        thumb.classList.toggle('active', absoluteIndex === currentImageIndex);
      });
    }

    function syncPageWithImage() {
      const targetPage = Math.floor(currentImageIndex / imagesPerPage);
      if (targetPage !== currentPage) {
        goToPage(targetPage);
      }
    }

    function openFullscreen() {
      const modal = document.getElementById('photoModal');
      const modalImage = document.getElementById('modalImage');
      modalImage.src = images[currentImageIndex];
      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      const modal = document.getElementById('photoModal');
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    function closeModalFromOverlay(event) {
      if (event.target.className === 'photo-modal') {
        closeModal();
      }
    }

    function changeModalImage(direction) {
      event.stopPropagation();
      currentImageIndex = (currentImageIndex + direction + totalImages) % totalImages;
      const modalImage = document.getElementById('modalImage');
      modalImage.src = images[currentImageIndex];
      updateImage();
      syncPageWithImage();
    }

    // ESC tuşu ile modalı kapatma ve ok tuşları ile gezinme
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      } else if (e.key === 'ArrowLeft') {
        if (document.getElementById('photoModal').style.display === 'block') {
          changeModalImage(-1);
          e.preventDefault();
        } else {
          changeImage(-1);
        }
      } else if (e.key === 'ArrowRight') {
        if (document.getElementById('photoModal').style.display === 'block') {
          changeModalImage(1);
          e.preventDefault();
        } else {
          changeImage(1);
        }
      }
    });

    function changePage(direction) {
      const newPage = currentPage + direction;
      if (newPage >= 0 && newPage < totalPages) {
        goToPage(newPage);
      }
    }

    function goToPage(pageNumber) {
      if (pageNumber >= 0 && pageNumber < totalPages) {
        currentPage = pageNumber;
        const startIndex = currentPage * imagesPerPage;
        const endIndex = Math.min(startIndex + imagesPerPage, totalImages);
        const pageImages = allImages.slice(startIndex, endIndex);
        
        // Update thumbnails
        const thumbnailsContainer = document.getElementById('galleryThumbnails');
        thumbnailsContainer.innerHTML = '';
        
        pageImages.forEach((image, index) => {
          const absoluteIndex = startIndex + index;
          const thumbnail = document.createElement('div');
          thumbnail.className = `gallery-thumbnail ${absoluteIndex === currentImageIndex ? 'active' : ''}`;
          thumbnail.onclick = () => selectImage(absoluteIndex);
          
          const img = document.createElement('img');
          img.src = image.image_path;
          img.alt = `${document.querySelector('#mainImage').alt} - Resim ${absoluteIndex + 1}`;
          
          thumbnail.appendChild(img);
          thumbnailsContainer.appendChild(thumbnail);
        });

        // Update pagination dots
        document.querySelectorAll('.gallery-pagination-dot').forEach((dot, index) => {
          dot.classList.toggle('active', index === currentPage);
        });

        // Update navigation buttons
        document.querySelector('.prev-page').classList.toggle('disabled', currentPage === 0);
        document.querySelector('.next-page').classList.toggle('disabled', currentPage === totalPages - 1);
      }
    }

    // Initialize pagination
    document.addEventListener('DOMContentLoaded', function() {
      // Create pagination dots
      const paginationContainer = document.querySelector('.gallery-pagination');
      if (paginationContainer) {
        for (let i = 0; i < totalPages; i++) {
          const dot = document.createElement('span');
          dot.className = `gallery-pagination-dot ${i === 0 ? 'active' : ''}`;
          dot.onclick = () => goToPage(i);
          paginationContainer.appendChild(dot);
        }
      }
      
      // Initialize first page
      goToPage(0);
    });
  </script>

</body>

</html>