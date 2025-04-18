<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'admin/config.php';

try {
    if (isset($_GET['id'])) {
        $property_id = (int)$_GET['id'];
        
        // İlan detaylarını ve öne çıkan resmi al
        $sql = "SELECT p.id, p.title, p.description, p.price, p.location, p.property_type, p.room_count, 
                p.bathroom_count, p.net_area, p.gross_area, p.heating, p.building_age, p.floor_location, 
                p.total_floors, p.furnished, p.status, p.balcony, p.eligible_for_credit, p.site_status,
                p.created_at, p.updated_at, p.agent_id, p.neighborhood, p.usage_status, p.dues,
                p.block_no, p.parcel_no, p.sheet_no, p.zoning_status, p.floor_area_ratio,
                p.height_limit, p.deed_status, p.site_name, p.video_call_available, p.living_room,
                pi.image_name, a.agent_name, a.phone as agent_phone, a.email as agent_email, 
                a.image as agent_image, a.sahibinden_link, a.emlakjet_link, a.facebook_link 
                FROM properties p 
                LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_featured = 1 
                LEFT JOIN agents a ON p.agent_id = a.id
                WHERE p.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $property = $result->fetch_assoc();
            
            // Tüm resimleri al
            $images_sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_featured DESC";
            $images_stmt = $conn->prepare($images_sql);
            $images_stmt->bind_param("i", $property_id);
            $images_stmt->execute();
            $images_result = $images_stmt->get_result();
            
            $images = [];
            while ($image = $images_result->fetch_assoc()) {
                $images[] = $image;
            }

            // Tüm resimleri aldıktan sonra, floor_options tanımlanıyor
            $floor_options = [
                'Bodrum KAT', 'Yarı Bodrum KAT', 'Zemin KAT', 'Bahçe KAT', 'Yüksek Giriş',
                '1. KAT', '2. KAT', '3. KAT', '4. KAT', '5. KAT', '6. KAT', '7. KAT', '8. KAT',
                '9. KAT', '10. KAT', '11. KAT', '12. KAT ve üzeri', 'Çatı KAT'
            ];
            
            // Trim kullanarak boşlukları temizle
            $floor_location = isset($property['floor_location']) ? trim($property['floor_location']) : '';
            
            // Sadece floor_options listesinde var mı kontrol et
            $floor = !empty($floor_location) && in_array($floor_location, $floor_options, true) 
                ? $floor_location 
                : '-';
        } else {
            header("Location: index.html");
            exit;
        }
    } else {
        header("Location: index.html");
        exit;
    }

    // Sayfalama için değişkenleri tanımla
    $imagesPerPage = 10;
    $totalPages = ceil(count($images) / $imagesPerPage);
    $currentPage = 0; // Başlangıç sayfası

    // Mevcut sayfa için resimleri al
    $currentPageImages = array_slice($images, $currentPage * $imagesPerPage, $imagesPerPage);

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
  <link href="assets/img/nua_logo.jpg" rel="icon">
  <link href="assets/img/nua_logo.jpg" rel="apple-touch-icon">

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
      width: 100%;
    }

    .property-description h3 {
      font-size: 1.25rem;
      color: #2c3e50;
      margin-bottom: 1rem;
      width: 100%;
    }

    .property-description p {
      color: #4b5563;
      line-height: 1.6;
      margin-bottom: 0;
      width: 100%;
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
      margin: 10px 0;
      padding: 0;
    }

    .action-links {
      display: flex;
      width: 100%;
      gap: 1px;
      background: #f0f0f0;
      border-radius: 4px;
      overflow: hidden;
    }

    .gallery-link {
      flex: 1;
      padding: 10px;
      text-align: center;
      background: #fff;
      color: #0d6efd;
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .gallery-link i {
      margin-right: 5px;
      font-size: 16px;
    }

    .gallery-link:hover:not(.disabled) {
      background: #f8f9fa;
      color: #0a58ca;
    }

    .gallery-link.disabled {
      background: #f8f9fa;
      color: #6c757d;
      cursor: not-allowed;
      opacity: 0.8;
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
      gap: 15px;
      margin-top: 10px;
    }

    .gallery-pagination-btn {
      width: 30px;
      height: 30px;
      background: rgba(0, 0, 0, 0.1);
      border: none;
      border-radius: 50%;
      cursor: pointer;
      color: #0d6efd;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .gallery-pagination-btn::before {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      background: rgba(108, 117, 125, 0.15);
      border-radius: 50%;
      z-index: 0;
    }

    .gallery-pagination-btn i {
      position: relative;
      z-index: 1;
      font-size: 18px;
    }

    .gallery-pagination-btn:hover {
      background: rgba(0, 0, 0, 0.15);
      color: #0a58ca;
      transform: scale(1.1);
    }

    .gallery-pagination-dots {
      display: flex;
      gap: 5px;
    }

    .gallery-pagination-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #dee2e6;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .gallery-pagination-dot:hover {
      background: #adb5bd;
    }

    .gallery-pagination-dot.active {
      background: #0d6efd;
      transform: scale(1.2);
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
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .btn-whatsapp:hover {
      background-color: #128c7e;
      color: white;
      transform: translateY(-2px);
    }

    .btn-whatsapp i {
      font-size: 1.1rem;
    }

    .btn-other-listings {
      display: block;
      text-align: center;
      color: #002e5c;
      text-decoration: none;
      padding: 10px;
      margin-top: 15px;
      border-top: 1px solid #eee;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .btn-other-listings:hover {
      color: #0056b3;
      background: #f8f9fa;
    }

    .btn-other-listings i {
      margin-left: 5px;
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

    .video-container {
      position: relative;
      width: 100%;
      height: 100%;
      background: #000;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .video-loader {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 2;
      display: none;
    }

    .video-fallback {
      color: white;
      text-align: center;
      padding: 20px;
    }

    .modal-video {
      width: 100%;
      height: 100%;
      object-fit: contain;
      background: black;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal-video.loaded {
      opacity: 1;
    }

    .agent-card {
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        overflow: hidden;
    }

    .agent-card .card {
        border: none;
    }

    .agent-card .card-body {
        padding: 1.5rem;
    }

    .agent-name {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.75rem;
    }

    .agent-image {
        margin-bottom: 0.5rem;
    }

    .agent-contact-info {
        margin-bottom: 1rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        padding: 0.5rem;
        transition: all 0.3s ease;
    }

    .contact-item:hover {
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .contact-item i {
        font-size: 1.1rem;
        color: #2563eb;
        margin-right: 0.75rem;
        width: 20px;
        text-align: center;
    }

    .contact-link {
        color: #4b5563;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .contact-link:hover {
        color: #2563eb;
    }

    .agent-photo {
      width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .platform-icons {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 15px 0;
    }

    .social-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: #f8f9fa;
      color: #002e5c;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .social-icon img {
      width: 20px;
      height: 20px;
      object-fit: contain;
    }

    .social-icon:hover {
      background: #002e5c;
      transform: translateY(-2px);
    }

    .social-icon.facebook {
      background: #f8f9fa;
    }

    .social-icon.facebook:hover {
      background: #002e5c;
      color: #fff;
    }

    .social-icon.facebook i {
      font-size: 18px;
    }

    .share-buttons {
      display: flex;
      gap: 10px;
      align-items: center;
      margin: 15px 0;
    }

    .share-buttons-label {
      color: #666;
      font-size: 0.9rem;
      margin-right: 5px;
    }

    .share-button {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      color: #fff;
      text-decoration: none;
      border: none;
      cursor: pointer;
    }

    .share-button:hover {
      transform: translateY(-2px);
      opacity: 0.9;
    }

    .share-facebook {
      background-color: #1877f2;
    }

    .share-twitter {
      background-color: #000000;
    }

    .share-whatsapp {
      background-color: #25d366;
    }

    .share-email {
      background-color: #666;
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
          <li><a href="about.php">Hakkımızda</a></li>
          <li><a href="services.html">Hizmetlerimiz</a></li>
          <li><a href="properties.php" class="active">İlanlar</a></li>
          <li><a href="agents.php">Danışmanlarımız</a></li>
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
                <div class="action-links">
                  <a href="#" class="gallery-link" onclick="openFullscreen(); return false;">
                    <i class="bi bi-arrows-fullscreen"></i> Büyük Fotoğraf
                  </a>
                  <a href="#" class="gallery-link <?php echo empty($property['video_file']) ? 'disabled' : ''; ?>" 
                     onclick="<?php echo !empty($property['video_file']) ? 'openVideoModal(); return false;' : 'return false;'; ?>">
                    <i class="bi bi-play-circle"></i> Video
                  </a>
                </div>
              </div>

              <div class="gallery-thumbnails-container">
                <div class="gallery-thumbnails" id="galleryThumbnails">
                  <?php foreach ($currentPageImages as $index => $image): ?>
                    <div class="gallery-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                         onclick="selectImage(<?php echo $index; ?>)">
                      <img src="uploads/<?php echo htmlspecialchars($image['image_name']); ?>" 
                           alt="<?php echo htmlspecialchars($property['title']); ?> - Resim <?php echo $index + 1; ?>">
                    </div>
                  <?php endforeach; ?>
                </div>
                
                <?php if (count($images) > $imagesPerPage): ?>
                <div class="gallery-pagination">
                  <button class="gallery-pagination-btn prev-page" onclick="changePage(-1)">
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  
                  <div class="gallery-pagination-dots">
                    <?php for ($i = 0; $i < $totalPages; $i++): ?>
                      <div class="gallery-pagination-dot <?php echo $i === $currentPage ? 'active' : ''; ?>" 
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

            <div class="share-buttons">
              <span class="share-buttons-label">Paylaş:</span>
              <?php
              $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
              $share_text = htmlspecialchars($property['title']);
              ?>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" 
                 target="_blank" 
                 class="share-button share-facebook" 
                 title="Facebook'ta Paylaş">
                <i class="bi bi-facebook"></i>
              </a>
              <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo urlencode($share_text); ?>" 
                 target="_blank" 
                 class="share-button share-twitter" 
                 title="X'te Paylaş">
                <i class="bi bi-twitter-x"></i>
              </a>
              <a href="https://wa.me/?text=<?php echo urlencode($share_text . ' ' . $current_url); ?>" 
                 target="_blank" 
                 class="share-button share-whatsapp" 
                 title="WhatsApp'ta Paylaş">
                <i class="bi bi-whatsapp"></i>
              </a>
              <a href="mailto:?subject=<?php echo urlencode($share_text); ?>&body=<?php echo urlencode($current_url); ?>" 
                 class="share-button share-email" 
                 title="E-posta ile Paylaş">
                <i class="bi bi-envelope"></i>
              </a>
            </div>

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
                            <div class="detail-box">
                                <i class="bi bi-rulers"></i>
                                <span>Alan</span>
                                <strong><?php echo number_format($property['square_meters'], 0, ',', '.'); ?> m²</strong>
                            </div>
                        </div>
                        <?php if (!empty($property['zoning_status'])): ?>
                        <div class="col-6 col-md-4">
                            <div class="detail-box">
                                <i class="bi bi-clipboard-check"></i>
                                <span>İmar Durumu</span>
                                <strong><?php echo htmlspecialchars($property['zoning_status']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($property['property_type'] == 'İş Yeri'): ?>
                    <!-- İş Yeri özellikleri -->
                    <div class="row g-2">
                        <?php if (!empty($property['status'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-bookmark"></i>
                                <span>Durum:</span>
                                <strong><?php echo htmlspecialchars($property['status']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($property['property_type'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-building"></i>
                                <span>Emlak Tipi:</span>
                                <strong><?php echo htmlspecialchars($property['property_type']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($property['square_meters'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-rulers"></i>
                                <span>Alan (m²):</span>
                                <strong><?php echo number_format($property['square_meters'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['room_count'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-door-open"></i>
                                <span>Bölüm & Oda Sayısı:</span>
                                <strong><?php echo htmlspecialchars($property['room_count']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['floor_location'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-layers"></i>
                                <span>Bulunduğu Kat:</span>
                                <strong><?php echo htmlspecialchars($property['floor_location']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['building_age'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-calendar3"></i>
                                <span>Bina Yaşı:</span>
                                <strong><?php echo htmlspecialchars($property['building_age']); ?></strong>
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

                        <?php if (isset($property['credit_eligible'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-credit-card"></i>
                                <span>Krediye Uygun:</span>
                                <strong><?php echo $property['credit_eligible'] == 'Evet' ? 'Evet' : 'Hayır'; ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($property['deed_status'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Tapu Durumu:</span>
                                <strong><?php echo htmlspecialchars($property['deed_status']); ?></strong>
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
                <?php else: ?>
                    <!-- Konut özellikleri (daire, villa, müstakil ev) -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-house"></i>
                                <span>Durum:</span>
                                <strong><?php echo htmlspecialchars($property['status']) . ' ' . htmlspecialchars($property['property_type']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-arrows-angle-expand"></i>
                                <span>m² (Brüt):</span>
                                <strong><?php echo !empty($property['gross_area']) ? number_format((float)$property['gross_area'], 0, ',', '.') : '-'; ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-arrows-angle-contract"></i>
                                <span>m² (Net):</span>
                                <strong><?php echo !empty($property['net_area']) ? number_format((float)$property['net_area'], 0, ',', '.') : '-'; ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-calendar3"></i>
                                <span>Bina Yaşı:</span>
                                <strong><?php 
                                $building_age = $property['building_age'];
                                if ($building_age !== null && $building_age !== '') {
                                    if ($building_age === '0' || $building_age === 0 || $building_age === '0-1') {
                                        echo 'Yeni';
                                    } elseif (is_numeric($building_age)) {
                                        echo htmlspecialchars($building_age) . ' Yaşında';
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
                                    'Bodrum KAT', 'Yarı Bodrum KAT', 'Zemin KAT', 'Bahçe KAT', 'Yüksek Giriş',
                                    '1. KAT', '2. KAT', '3. KAT', '4. KAT', '5. KAT', '6. KAT', '7. KAT', '8. KAT',
                                    '9. KAT', '10. KAT', '11. KAT', '12. KAT ve üzeri', 'Çatı KAT'
                                ];
                                
                                // Trim kullanarak boşlukları temizle
                                $floor_location = isset($property['floor_location']) ? trim($property['floor_location']) : '';
                                
                                // Sadece floor_options listesinde var mı kontrol et
                                $floor = !empty($floor_location) && in_array($floor_location, $floor_options, true) 
                                    ? $floor_location 
                                    : '-';
                                ?>
                                <strong><?php echo htmlspecialchars($floor); ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($property['living_room']) && $property['living_room'] > 0): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-door-closed"></i>
                                <span>Oda Sayısı:</span>
                                <strong><?php 
                                    echo htmlspecialchars($property['room_count']); 
                                    if (!empty($property['living_room']) && $property['living_room'] > 0) {
                                        echo '+' . htmlspecialchars($property['living_room']);
                                    }
                                ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($property['heating'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-thermometer-half"></i>
                                <span>Isıtma:</span>
                                <strong><?php echo htmlspecialchars($property['heating']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($property['eligible_for_credit'])): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-credit-card"></i>
                                <span>Krediye Uygun:</span>
                                <strong><?php echo $property['eligible_for_credit'] == 'Evet' ? 'Evet' : 'Hayır'; ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($property['bathroom_count']) && $property['bathroom_count'] > 0): ?>
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
                                <strong><?php echo $property['balcony'] == 'Var' ? 'Var' : 'Yok'; ?></strong>
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
                                <strong><?php echo $property['furnished'] == 'Evet' ? 'Evet' : 'Hayır'; ?></strong>
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
                                <strong><?php echo $property['site_status'] == 'Evet' ? 'Evet' : 'Hayır'; ?></strong>
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
                    </div>
                <?php endif; ?>
            </div>
          </div>

          <!-- Sağ Kolon - İlan ve Danışman Bilgileri -->
          <div class="col-lg-3">
            <!-- Danışman Bilgileri Kartı -->
            <div class="agent-card mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <?php
                            $agent_photo = 'assets/img/nua_logo.jpg'; // Varsayılan fotoğraf olarak Nua Yapı logosu
                            if (!empty($property['agent_image']) && file_exists($property['agent_image'])) {
                                $agent_photo = $property['agent_image']; // Veritabanından gelen fotoğraf yolu
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($agent_photo); ?>" alt="<?php echo !empty($property['agent_name']) ? htmlspecialchars($property['agent_name']) : 'NUA YAPI'; ?>" class="agent-photo">
                        </div>
                        
                        <div class="agent-contact-info">
                            <h3 class="agent-name text-center mb-4">
                                <?php echo !empty($property['agent_name']) ? htmlspecialchars($property['agent_name']) : 'NUA YAPI'; ?>
                            </h3>
                            <?php if (!empty($property['agent_phone'])): ?>
                            <div class="contact-item">
                                <i class="bi bi-telephone"></i>
                                <a href="tel:<?php echo htmlspecialchars($property['agent_phone']); ?>" class="contact-link">
                                    <?php echo htmlspecialchars($property['agent_phone']); ?>
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="contact-item">
                                <i class="bi bi-telephone"></i>
                                <a href="tel:905304416873" class="contact-link">
                                    0530 441 68 73
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['agent_email'])): ?>
                            <div class="contact-item">
                                <i class="bi bi-envelope"></i>
                                <a href="mailto:<?php echo htmlspecialchars($property['agent_email']); ?>" class="contact-link">
                                    <?php echo htmlspecialchars($property['agent_email']); ?>
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="contact-item">
                                <i class="bi bi-envelope"></i>
                                <a href="mailto:info@nuayapi.com" class="contact-link">
                                    info@nuayapi.com
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="platform-icons">
                            <?php if (!empty($property['sahibinden_link'])): ?>
                                <a href="<?php echo htmlspecialchars($property['sahibinden_link']); ?>" target="_blank" title="Sahibinden.com Mağazası" class="social-icon">
                                    <img src="assets/img/platforms/sahibinden-icon.png" alt="Sahibinden.com">
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['emlakjet_link'])): ?>
                                <a href="<?php echo htmlspecialchars($property['emlakjet_link']); ?>" target="_blank" title="Emlakjet Profili" class="social-icon">
                                    <img src="assets/img/platforms/emlakjet-icon.png" alt="Emlakjet">
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($property['facebook_link'])): ?>
                                <a href="<?php echo htmlspecialchars($property['facebook_link']); ?>" target="_blank" title="Facebook" class="social-icon facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4">
                            <a href="https://wa.me/<?php 
                                $phone = !empty($property['agent_phone']) ? preg_replace('/[^0-9]/', '', $property['agent_phone']) : '905304416873';
                                if (substr($phone, 0, 1) !== '9') {
                                    $phone = '9' . $phone;
                                }
                                echo $phone;
                            ?>?text=<?php 
                                $message = "Merhaba, " . (!empty($property['agent_name']) ? $property['agent_name'] : 'NUA YAPI') . ", ";
                                $message .= "ilan no " . str_pad($property['id'], 10, '0', STR_PAD_LEFT) . " olan ";
                                $message .= $property['title'] . " ilanınız hakkında bilgi almak istiyorum.\n\n";
                                $message .= "İlan linki: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                                echo urlencode($message);
                            ?>" class="btn-whatsapp" target="_blank">
                                <i class="bi bi-whatsapp"></i>WhatsApp'tan Mesaj Gönder
                            </a>
                        </div>

                        <?php if (!empty($property['agent_id'])): ?>
                        <a href="agents-portfolio.php?id=<?php echo $property['agent_id']; ?>#properties" class="btn-other-listings">
                            Danışmanın Diğer İlanları <i class="bi bi-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
          </div>
        </div>

        <?php if (!empty($property['description'])): ?>
        <div class="row mt-4">
          <div class="col-12">
            <div class="property-description">
                <h4>İlan Açıklaması</h4>
                <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($property['video_file'])): ?>
        <div class="row mt-4">
          <div class="col-12">
            <div class="property-video">
                <h4>Video</h4>
                <video controls class="w-100">
                    <source src="uploads/videos/<?php echo htmlspecialchars($property['video_file']); ?>" type="video/mp4">
                    Tarayıcınız video oynatmayı desteklemiyor.
                </video>
            </div>
          </div>
        </div>
        <?php endif; ?>
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

  <!-- Video Modal -->
  <div id="videoModal" class="photo-modal" onclick="closeVideoModalFromOverlay(event)">
    <div class="modal-content">
      <button class="modal-close" onclick="closeVideoModal()">
        <i class="bi bi-x-lg"></i>
      </button>
      <div class="video-container">
        <div id="videoLoader" class="video-loader">
          <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Yükleniyor...</span>
          </div>
        </div>
        <video id="propertyVideo" class="modal-video" controls controlsList="nodownload" playsinline>
          <source src="uploads/videos/<?php echo htmlspecialchars($property['video_file']); ?>" type="video/mp4">
          <p class="video-fallback">Tarayıcınız video oynatmayı desteklemiyor.</p>
        </video>
      </div>
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
    const imagesPerPage = <?php echo $imagesPerPage; ?>;
    const totalPages = <?php echo $totalPages; ?>;
    let currentPage = <?php echo $currentPage; ?>;

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

        // Update navigation buttons visibility and state
        const prevButton = document.querySelector('.prev-page');
        const nextButton = document.querySelector('.next-page');
        
        prevButton.style.visibility = currentPage === 0 ? 'hidden' : 'visible';
        nextButton.style.visibility = currentPage === totalPages - 1 ? 'hidden' : 'visible';
      }
    }

    // Initialize pagination
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize first page
      goToPage(0);
    });

    function openVideoModal() {
      const modal = document.getElementById('videoModal');
      const video = document.getElementById('propertyVideo');
      const loader = document.getElementById('videoLoader');
      
      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';
      
      // Show loader
      loader.style.display = 'block';
      
      // Video yükleme olaylarını dinle
      video.addEventListener('loadeddata', handleVideoLoad);
      video.addEventListener('error', handleVideoError);
      
      // Video yüklenene kadar bekle
      if (video.readyState >= 3) {
        handleVideoLoad();
      }
      
      video.play().catch(function(error) {
        console.log("Video otomatik oynatma engellendi:", error);
      });
    }

    function handleVideoLoad() {
      const video = document.getElementById('propertyVideo');
      const loader = document.getElementById('videoLoader');
      
      // Loader'ı gizle
      loader.style.display = 'none';
      
      // Videoyu göster
      video.classList.add('loaded');
    }

    function handleVideoError() {
      const loader = document.getElementById('videoLoader');
      const video = document.getElementById('propertyVideo');
      
      // Loader'ı gizle
      loader.style.display = 'none';
      
      // Hata mesajını göster
      video.innerHTML = '<p class="video-fallback">Video yüklenirken bir hata oluştu.</p>';
    }

    function closeVideoModal() {
      const modal = document.getElementById('videoModal');
      const video = document.getElementById('propertyVideo');
      const loader = document.getElementById('videoLoader');
      
      // Event listener'ları temizle
      video.removeEventListener('loadeddata', handleVideoLoad);
      video.removeEventListener('error', handleVideoError);
      
      // Videoyu sıfırla
      video.pause();
      video.currentTime = 0;
      video.classList.remove('loaded');
      
      // Modal'ı kapat
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
      loader.style.display = 'none';
    }

    function closeVideoModalFromOverlay(event) {
      if (event.target.className === 'photo-modal') {
        closeVideoModal();
      }
    }

    // ESC tuşu ile video modalını kapatma
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        if (document.getElementById('videoModal').style.display === 'block') {
          closeVideoModal();
        }
      }
    });

    // Video kontrollerini klavye ile yönetme
    document.addEventListener('keydown', function(e) {
      const video = document.getElementById('propertyVideo');
      const videoModal = document.getElementById('videoModal');
      
      if (videoModal.style.display === 'block') {
        switch(e.key) {
          case 'Escape':
            closeVideoModal();
            break;
          case ' ':
            // Boşluk tuşu ile oynat/duraklat
            e.preventDefault();
            if (video.paused) {
              video.play();
            } else {
              video.pause();
            }
            break;
          case 'ArrowLeft':
            // 5 saniye geri
            e.preventDefault();
            video.currentTime = Math.max(0, video.currentTime - 5);
            break;
          case 'ArrowRight':
            // 5 saniye ileri
            e.preventDefault();
            video.currentTime = Math.min(video.duration, video.currentTime + 5);
            break;
        }
      }
    });

    // Video yükleme durumunu izle
    document.getElementById('propertyVideo').addEventListener('waiting', function() {
      document.getElementById('videoLoader').style.display = 'block';
    });

    document.getElementById('propertyVideo').addEventListener('playing', function() {
      document.getElementById('videoLoader').style.display = 'none';
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Elementlerin varlığını kontrol et
        function goToPage(url) {
            if (url) {
                window.location.href = url;
            }
        }

        // PureCounter'ı koşullu olarak başlat
        if (typeof PureCounter !== 'undefined') {
            new PureCounter();
        }
    });
  </script>

</body>

</html>