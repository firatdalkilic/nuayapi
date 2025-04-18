<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'admin/config.php';

// Debug için bağlantı bilgilerini kontrol et
error_log("Database Connection Info - Host: " . $servername . ", User: " . $username . ", DB: " . $dbname);

// Sayfalama için değişkenler
$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 9; // Her sayfada gösterilecek ilan sayısı
$baslangic = ($sayfa - 1) * $limit;

// Filtreleme parametrelerini al
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$property_type = isset($_GET['property_type']) ? trim($_GET['property_type']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : '';
$room_count = isset($_GET['room_count']) ? trim($_GET['room_count']) : '';
$min_area = isset($_GET['min_area']) && $_GET['min_area'] !== '' ? (int)$_GET['min_area'] : '';
$max_area = isset($_GET['max_area']) && $_GET['max_area'] !== '' ? (int)$_GET['max_area'] : '';
$heating = isset($_GET['heating']) ? trim($_GET['heating']) : '';
$furnished = isset($_GET['furnished']) ? trim($_GET['furnished']) : '';
$site_status = isset($_GET['site_status']) ? trim($_GET['site_status']) : '';
$eligible_for_credit = isset($_GET['eligible_for_credit']) ? trim($_GET['eligible_for_credit']) : '';
$building_age = isset($_GET['building_age']) ? trim($_GET['building_age']) : '';

// SQL sorgusunu oluştur
$where_conditions = [];
$params = [];
$param_types = "";

// Ana sorguyu tanımla
$base_query = "SELECT p.*, pi.image_name, a.agent_name, a.phone as agent_phone, a.email as agent_email 
               FROM properties p 
               LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_featured = 1 
               LEFT JOIN agents a ON p.agent_id = a.id";

// Aktif filtreleri kontrol eden fonksiyon
function hasActiveFilters() {
    $filterParams = [
        'search', 'status', 'property_type', 'min_price', 'max_price',
        'room_count', 'min_area', 'max_area', 'heating',
        'furnished', 'site_status', 'eligible_for_credit', 'building_age'
    ];
    
    foreach ($filterParams as $param) {
        if (!empty($_GET[$param])) {
            return true;
        }
    }
    return false;
}

if (!empty($search)) {
    $where_conditions[] = "(p.location LIKE ? OR p.neighborhood LIKE ? OR p.title LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "sss";
}

if (!empty($status)) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status;
    $param_types .= "s";
}

if (!empty($property_type)) {
    $where_conditions[] = "p.property_type = ?";
    $params[] = $property_type;
    $param_types .= "s";
}

if (!empty($min_price)) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
    $param_types .= "i";
}

if (!empty($max_price)) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
    $param_types .= "i";
}

if (!empty($room_count)) {
    if ($room_count === '5+') {
        $where_conditions[] = "p.room_count >= 5";
    } else {
        $where_conditions[] = "p.room_count = ?";
        $params[] = (int)$room_count;
    }
}

if (!empty($min_area)) {
    $where_conditions[] = "p.net_area >= ?";
    $params[] = $min_area;
    $param_types .= "i";
}

if (!empty($max_area)) {
    $where_conditions[] = "p.net_area <= ?";
    $params[] = $max_area;
    $param_types .= "i";
}

if (!empty($heating)) {
    $where_conditions[] = "p.heating = ?";
    $params[] = $heating;
    $param_types .= "s";
}

if (!empty($furnished)) {
    $where_conditions[] = "p.furnished = ?";
    $params[] = $furnished;
    $param_types .= "s";
}

if (!empty($site_status)) {
    $where_conditions[] = "p.site_status = ?";
    $params[] = $site_status;
    $param_types .= "s";
}

if (!empty($eligible_for_credit)) {
    $where_conditions[] = "p.eligible_for_credit = ?";
    $params[] = $eligible_for_credit;
    $param_types .= "s";
}

if ($building_age !== '') {
    error_log("Debug - Building Age Filter: " . var_export($building_age, true));
    
    if ($building_age === '0') {
        $where_conditions[] = "(p.building_age = '0' OR p.building_age = 0 OR p.building_age = '0 (Yeni)')";
    } elseif (strpos($building_age, '-') !== false) {
        list($min, $max) = explode('-', $building_age);
        if ($max === '+') {
            $where_conditions[] = "(CAST(NULLIF(REPLACE(p.building_age, ' (Yeni)', ''), '') AS SIGNED) >= ?)";
            $params[] = (int)$min;
            $param_types .= "i";
        } else {
            $where_conditions[] = "(CAST(NULLIF(REPLACE(p.building_age, ' (Yeni)', ''), '') AS SIGNED) BETWEEN ? AND ?)";
            $params[] = (int)$min;
            $params[] = (int)$max;
            $param_types .= "ii";
        }
    } else {
        $where_conditions[] = "(p.building_age = ? OR CAST(NULLIF(REPLACE(p.building_age, ' (Yeni)', ''), '') AS SIGNED) = ?)";
        $params[] = $building_age;
        $params[] = (int)$building_age;
        $param_types .= "si";
    }
}

// Toplam ilan sayısını al
$total_query = "SELECT COUNT(DISTINCT p.id) as total FROM properties p";
if (!empty($where_conditions)) {
    $total_query .= " WHERE " . implode(" AND ", $where_conditions);
}

$stmt = $conn->prepare($total_query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$toplam_ilan = $total_row['total'];
$toplam_sayfa = ceil($toplam_ilan / $limit);

// İlanları veritabanından çek
$query = $base_query;
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}
$query .= " ORDER BY p.created_at DESC LIMIT ?, ?";
$params[] = $baslangic;
$params[] = $limit;
$param_types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Uploads klasörünü oluştur
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>İlanlarımız</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/nua_logo.jpg" rel="icon">
  <link href="assets/img/nua_logo.jpg" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Dosyaları -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Ana CSS Dosyası -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    /* Genel Başlık Stilleri */
    .page-title h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 2.5rem;
      color: #2c3e50;
      margin-bottom: 1rem;
      position: relative;
      display: inline-block;
    }

    .page-title h1:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
      border-radius: 2px;
    }

    .page-title p {
      font-family: 'Roboto', sans-serif;
      font-size: 1.1rem;
      color: #6c757d;
      line-height: 1.6;
    }

    /* Filtreleme Bölümü Stilleri */
    .card-header h5 {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      color: #2c3e50;
      font-size: 1.25rem;
    }

    .form-label {
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      color: #2c3e50;
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
    }

    .form-label i {
      color: var(--primary-color);
      font-size: 1.1rem;
    }

    .form-select, .form-control {
      font-family: 'Roboto', sans-serif;
      font-size: 0.95rem;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
    }

    .form-select:focus, .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.1);
    }

    /* İlan Kartı Stilleri */
    .property-card {
      background-color: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      overflow: hidden;
      transition: all 0.3s ease;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      height: 170px;
    }

    .property-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .property-card .property-image {
      height: 200px;
      width: 200px;
      overflow: hidden;
      position: relative;
      border-radius: 8px 0 0 8px;
      background-color: #ffffff;
    }

    .property-card .property-image img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      object-position: center;
      background-color: #ffffff;
    }

    .card-body {
      padding: 0.5rem 0.75rem;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .property-details {
      display: flex;
      gap: 0.75rem;
      margin: 0;
      padding: 0.25rem 0;
    }

    .detail-item {
      display: flex;
      align-items: center;
      gap: 0.2rem;
      color: #4b5563;
      font-size: 0.8rem;
    }

    .property-location {
      color: #6b7280;
      font-size: 0.8rem;
      margin-bottom: 0.2rem;
      padding: 0;
    }

    .property-location i {
      color: #2563eb;
      margin-right: 0.2rem;
    }

    .property-features {
      font-size: 0.75rem;
      color: #6b7280;
      margin: 0;
      padding: 0.2rem 0;
    }

    .property-features i {
      color: #2563eb;
    }

    .card-title {
      font-size: 1rem;
      line-height: 1.2;
      margin-bottom: 0.2rem;
      padding: 0;
    }

    .text-primary {
      font-size: 1rem !important;
    }

    .badge {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }

    /* Sayfalama Stilleri */
    .pagination .page-link {
      font-family: 'Roboto', sans-serif;
      color: #2c3e50;
      border: 1px solid #e0e0e0;
      margin: 0 3px;
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .pagination .page-link:hover {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }

    .pagination .page-item.active .page-link {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    /* Filtre Butonları */
    .btn-primary {
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      padding: 0.6rem 1.2rem;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .btn-outline-secondary {
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      padding: 0.6rem 1.2rem;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    /* Alert Mesajları */
    .alert {
      font-family: 'Roboto', sans-serif;
      border-radius: 8px;
      padding: 1rem;
    }

    .alert i {
      font-size: 1.2rem;
      margin-right: 0.5rem;
    }

    /* Mobil Cihazlar İçin Düzenlemeler */
    @media (max-width: 768px) {
      .page-title h1 {
        font-size: 2rem;
      }

      .page-title p {
        font-size: 1rem;
      }

      .card-title {
        font-size: 1.1rem;
      }

      .text-primary {
        font-size: 1.2rem;
      }

      /* Filtre bölümü düzenlemeleri */
      .filter-section {
        width: 100% !important;
        margin-bottom: 20px;
      }
      
      /* İlan kartları düzenlemeleri */
      .property-item {
        width: 100% !important;
        margin-right: 0 !important;
      }
      
      /* Form elemanları düzenlemeleri */
      .form-select, .form-control {
        font-size: 16px !important; /* iOS'ta zoom sorununu önler */
      }
      
      /* Sayfalama butonları düzenlemeleri */
      .pagination .page-link {
        padding: 12px 18px !important;
        font-size: 16px !important;
      }
      
      /* Filtre temizleme butonu */
      .clear-filters {
        width: 100% !important;
        margin-top: 10px !important;
      }
    }

    /* Tablet İçin Düzenlemeler */
    @media (min-width: 769px) and (max-width: 991px) {
      .property-item {
        width: calc(50% - 15px) !important;
      }
    }

    /* Genel Düzenlemeler */
    .property-item img {
      width: 100%;
      height: auto;
      object-fit: cover;
    }

    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
  </style>
</head>

<body class="properties-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

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
          <!-- <li class="dropdown"><a href="#"><span>Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li><a href="#">Dropdown 1</a></li>
              <li class="dropdown"><a href="#"><span>Deep Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                  <li><a href="#">Deep Dropdown 1</a></li>
                  <li><a href="#">Deep Dropdown 2</a></li>
                  <li><a href="#">Deep Dropdown 3</a></li>
                  <li><a href="#">Deep Dropdown 4</a></li>
                  <li><a href="#">Deep Dropdown 5</a></li>
                </ul>
              </li>
              <li><a href="#">Dropdown 2</a></li>
              <li><a href="#">Dropdown 3</a></li>
              <li><a href="#">Dropdown 4</a></li>
            </ul>
          </li> -->
          <li><a href="contact.html">İletişim</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1>İlanlarımız</h1>
              <p class="mb-0">Size en uygun gayrimenkulü bulmanız için geniş portföyümüzü inceleyebilirsiniz. Detaylı bilgi için danışmanlarımızla iletişime geçebilirsiniz.</p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.html">Ana Sayfa</a></li>
            <li class="current">İlanlar</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Page Title -->

    <!-- İlanlar Bölümü -->
    <section class="properties-section py-5">
      <div class="container">
        <h2 class="text-center mb-5">Güncel İlanlarımız</h2>
        
        <div class="row">
          <!-- Filtreleme Bölümü -->
          <div class="col-md-3">
            <div class="card mb-4">
              <div class="card-header">
                <h5 class="mb-0">Filtreleme</h5>
              </div>
              <div class="card-body">
                <form id="filterForm" method="GET" action="">
                  <div class="mb-3">
                    <label class="form-label">Arama</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-search"></i></span>
                      <input type="text" class="form-control filter-input" name="search" placeholder="Konum, mahalle veya başlık..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-tag me-2"></i>İlan Durumu</label>
                    <select class="form-select filter-input" name="status">
                      <option value="">Tümü</option>
                      <option value="Kiralık" <?php echo $status == 'Kiralık' ? 'selected' : ''; ?>>Kiralık</option>
                      <option value="Satılık" <?php echo $status == 'Satılık' ? 'selected' : ''; ?>>Satılık</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-house me-2"></i>Emlak Tipi</label>
                    <select class="form-select filter-input" name="property_type">
                      <option value="">Tümü</option>
                      <option value="Daire" <?php echo $property_type == 'Daire' ? 'selected' : ''; ?>>Daire</option>
                      <option value="Villa" <?php echo $property_type == 'Villa' ? 'selected' : ''; ?>>Villa</option>
                      <option value="Müstakil Ev" <?php echo $property_type == 'Müstakil Ev' ? 'selected' : ''; ?>>Müstakil Ev</option>
                      <option value="Arsa" <?php echo $property_type == 'Arsa' ? 'selected' : ''; ?>>Arsa</option>
                      <option value="İş Yeri" <?php echo $property_type == 'İş Yeri' ? 'selected' : ''; ?>>İş Yeri</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-currency-exchange me-2"></i>Fiyat Aralığı</label>
                    <div class="input-group mb-2">
                      <span class="input-group-text"><i class="bi bi-currency-lira"></i></span>
                      <input type="number" class="form-control filter-input" name="min_price" placeholder="Min. Fiyat" value="<?php echo htmlspecialchars($min_price); ?>">
                    </div>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-currency-lira"></i></span>
                      <input type="number" class="form-control filter-input" name="max_price" placeholder="Max. Fiyat" value="<?php echo htmlspecialchars($max_price); ?>">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-door-open me-2"></i>Oda Sayısı</label>
                    <select class="form-select filter-input" name="room_count">
                      <option value="">Oda Sayısı</option>
                      <option value="1" <?php echo $room_count == '1' ? 'selected' : ''; ?>>1</option>
                      <option value="2" <?php echo $room_count == '2' ? 'selected' : ''; ?>>2</option>
                      <option value="3" <?php echo $room_count == '3' ? 'selected' : ''; ?>>3</option>
                      <option value="4" <?php echo $room_count == '4' ? 'selected' : ''; ?>>4</option>
                      <option value="5+" <?php echo $room_count == '5+' ? 'selected' : ''; ?>>5+</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-rulers me-2"></i>Net Alan (m²)</label>
                    <div class="input-group mb-2">
                      <input type="number" class="form-control filter-input" name="min_area" placeholder="Min. m²" value="<?php echo htmlspecialchars($min_area); ?>">
                    </div>
                    <div class="input-group">
                      <input type="number" class="form-control filter-input" name="max_area" placeholder="Max. m²" value="<?php echo htmlspecialchars($max_area); ?>">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-thermometer-sun me-2"></i>Isıtma</label>
                    <select class="form-select filter-input" name="heating">
                      <option value="">Tümü</option>
                      <option value="Kombi (Doğalgaz)" <?php echo isset($_GET['heating']) && $_GET['heating'] == 'Kombi (Doğalgaz)' ? 'selected' : ''; ?>>Kombi (Doğalgaz)</option>
                      <option value="Merkezi" <?php echo isset($_GET['heating']) && $_GET['heating'] == 'Merkezi' ? 'selected' : ''; ?>>Merkezi</option>
                      <option value="Klima" <?php echo isset($_GET['heating']) && $_GET['heating'] == 'Klima' ? 'selected' : ''; ?>>Klima</option>
                      <option value="Yerden Isıtma" <?php echo isset($_GET['heating']) && $_GET['heating'] == 'Yerden Isıtma' ? 'selected' : ''; ?>>Yerden Isıtma</option>
                      <option value="Soba" <?php echo isset($_GET['heating']) && $_GET['heating'] == 'Soba' ? 'selected' : ''; ?>>Soba</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-box-seam me-2"></i>Eşyalı</label>
                    <select class="form-select filter-input" name="furnished">
                      <option value="">Tümü</option>
                      <option value="Evet" <?php echo $furnished == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                      <option value="Hayır" <?php echo $furnished == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-building me-2"></i>Site İçerisinde</label>
                    <select class="form-select filter-input" name="site_status">
                      <option value="">Tümü</option>
                      <option value="Evet" <?php echo $site_status == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                      <option value="Hayır" <?php echo $site_status == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label"><i class="bi bi-credit-card me-2"></i>Krediye Uygun</label>
                    <select class="form-select filter-input" name="eligible_for_credit">
                      <option value="">Tümü</option>
                      <option value="Evet" <?php echo $eligible_for_credit == 'Evet' ? 'selected' : ''; ?>>Evet</option>
                      <option value="Hayır" <?php echo $eligible_for_credit == 'Hayır' ? 'selected' : ''; ?>>Hayır</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Bina Yaşı</label>
                    <select class="form-select filter-input" id="building_age" name="building_age">
                      <option value="">Tümü</option>
                      <option value="0" <?php echo $building_age == '0' ? 'selected' : ''; ?>>0 (Yeni)</option>
                      <option value="1" <?php echo $building_age == '1' ? 'selected' : ''; ?>>1</option>
                      <option value="2" <?php echo $building_age == '2' ? 'selected' : ''; ?>>2</option>
                      <option value="3" <?php echo $building_age == '3' ? 'selected' : ''; ?>>3</option>
                      <option value="4" <?php echo $building_age == '4' ? 'selected' : ''; ?>>4</option>
                      <option value="5" <?php echo $building_age == '5' ? 'selected' : ''; ?>>5</option>
                      <option value="6" <?php echo $building_age == '6' ? 'selected' : ''; ?>>6</option>
                      <option value="7" <?php echo $building_age == '7' ? 'selected' : ''; ?>>7</option>
                      <option value="8" <?php echo $building_age == '8' ? 'selected' : ''; ?>>8</option>
                      <option value="9" <?php echo $building_age == '9' ? 'selected' : ''; ?>>9</option>
                      <option value="10" <?php echo $building_age == '10' ? 'selected' : ''; ?>>10</option>
                      <option value="11-15" <?php echo $building_age == '11-15' ? 'selected' : ''; ?>>11-15</option>
                      <option value="16-20" <?php echo $building_age == '16-20' ? 'selected' : ''; ?>>16-20</option>
                      <option value="21-25" <?php echo $building_age == '21-25' ? 'selected' : ''; ?>>21-25</option>
                      <option value="26+" <?php echo $building_age == '26+' ? 'selected' : ''; ?>>26+</option>
                    </select>
                  </div>

                  <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-search me-2"></i>Filtrele
                    </button>
                    <?php if (hasActiveFilters()): ?>
                    <a href="properties.php" class="btn btn-outline-secondary">
                      <i class="bi bi-x-circle me-2"></i>Filtreleri Temizle
                    </a>
                    <?php endif; ?>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- İlan Listesi -->
          <div class="col-md-9">
            <div id="loadingSpinner" class="text-center d-none">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
              </div>
            </div>

            <?php if ($toplam_ilan == 0): ?>
            <div class="alert alert-info text-center" id="noResultsAlert">
              <i class="bi bi-info-circle me-2"></i> Arama kriterlerinize uygun ilan bulunamadı.
            </div>
            <?php endif; ?>

            <div class="properties-list" id="propertiesList">
              <?php while ($ilan = $result->fetch_assoc()): ?>
              <a href="property-single.php?id=<?php echo $ilan['id']; ?>" class="text-decoration-none text-dark">
              <div class="property-card mb-4">
                <div class="row g-0">
                  <div class="col-md-4 position-relative">
                    <div class="property-image">
                        <?php
                        echo '<img src="' . 
                            (!empty($ilan['image_name']) 
                                ? (strpos($ilan['image_name'], 'assets/') === 0 
                                   ? $ilan['image_name'] 
                                   : 'uploads/' . htmlspecialchars($ilan['image_name']))
                                : 'assets/img/property-default.jpg') 
                            . '" class="img-fluid w-100" style="height: 250px; object-fit: contain; background-color: #ffffff;" alt="' . htmlspecialchars($ilan['title']) . '">';
                        ?>
                    </div>
                    <div class="position-absolute top-0 end-0 m-2">
                      <span class="badge bg-primary"><?php echo htmlspecialchars($ilan['status']); ?></span>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="card-body h-100 d-flex flex-column">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0" style="font-weight: 700; font-size: 1.25rem;">
                            <?php echo htmlspecialchars($ilan['title']); ?>
                        </h5>
                        <span class="text-primary fw-bold fs-4"><?php echo number_format($ilan['price'], 0, ',', '.'); ?> TL</span>
                      </div>
                      <div class="property-location">
                        <i class="bi bi-geo-alt"></i>
                        <?php 
                        echo !empty($ilan['location']) ? htmlspecialchars($ilan['location']) : 'Didim';
                        if (!empty($ilan['neighborhood'])) {
                            echo ' / ' . htmlspecialchars($ilan['neighborhood']);
                        }
                        ?>
                      </div>
                      <div class="property-features d-flex justify-content-start gap-4 mt-1">
                        <div class="feature">
                          <i class="fas fa-calendar-alt me-1"></i>
                          <span><?php echo date('d-m-Y', strtotime($ilan['created_at'])); ?></span>
                        </div>
                      </div>
                      <div class="property-details mt-2">
                        <?php if ($ilan['property_type'] == 'Arsa'): ?>
                            <div class="detail-item me-4">
                                <i class="bi bi-building"></i>
                                <span><?php echo htmlspecialchars($ilan['status'] . ' ' . $ilan['property_type']); ?></span>
                            </div>
                            <div class="detail-item me-4">
                                <i class="bi bi-rulers"></i>
                                <span><?php echo number_format($ilan['net_area'], 0, ',', '.'); ?> m²</span>
                            </div>
                            <?php if (!empty($ilan['zoning_status'])): ?>
                            <div class="detail-item me-4">
                                <i class="bi bi-clipboard-check"></i>
                                <span><?php echo htmlspecialchars($ilan['zoning_status']); ?></span>
                            </div>
                            <?php endif; ?>
                        <?php elseif ($ilan['property_type'] == 'İş Yeri'): ?>
                            <div class="detail-item me-4">
                                <i class="bi bi-building"></i>
                                <span><?php echo htmlspecialchars($ilan['status'] . ' ' . $ilan['property_type']); ?></span>
                            </div>
                            <?php if (!empty($ilan['square_meters'])): ?>
                            <div class="detail-item me-4">
                                <i class="bi bi-rulers"></i>
                                <span><?php echo number_format($ilan['square_meters'], 0, ',', '.'); ?> m²</span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($ilan['room_count'])): ?>
                            <div class="detail-item">
                                <i class="bi bi-door-open"></i>
                                <span><?php echo htmlspecialchars($ilan['room_count']); ?> Bölüm</span>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="detail-item me-4">
                                <i class="bi bi-building"></i>
                                <span><?php echo htmlspecialchars($ilan['status'] . ' ' . $ilan['property_type']); ?></span>
                            </div>
                            <?php if (!empty($ilan['gross_area'])): ?>
                            <div class="detail-item me-4">
                                <i class="bi bi-rulers"></i>
                                <span><?php echo number_format($ilan['gross_area'], 0, ',', '.'); ?> m²</span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($ilan['room_count'])): ?>
                            <div class="detail-item">
                                <i class="bi bi-door-open"></i>
                                <span><?php 
                                    echo htmlspecialchars($ilan['room_count']); 
                                    if (!empty($ilan['living_room'])) {
                                        echo '+' . htmlspecialchars($ilan['living_room']);
                                    }
                                ?></span>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              </a>
              <?php endwhile; ?>
            </div>

            <!-- Sayfalama -->
            <?php if ($toplam_sayfa > 1): ?>
            <nav aria-label="Sayfalama" class="mt-4">
              <ul class="pagination justify-content-center">
                <?php
                // Mevcut URL parametrelerini al
                $params = $_GET;
                
                // Sayfa parametresini güncelle/ekle
                $params['sayfa'] = $sayfa - 1;
                $prev_url = '?' . http_build_query($params);
                
                $params['sayfa'] = $sayfa + 1;
                $next_url = '?' . http_build_query($params);
                ?>
                <li class="page-item <?php echo $sayfa <= 1 ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo $sayfa <= 1 ? '#' : $prev_url; ?>" <?php echo $sayfa <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>Önceki</a>
                </li>
                <?php for ($i = 1; $i <= $toplam_sayfa; $i++): 
                  $params['sayfa'] = $i;
                  $page_url = '?' . http_build_query($params);
                ?>
                <li class="page-item <?php echo $i == $sayfa ? 'active' : ''; ?>">
                  <a class="page-link" href="<?php echo $page_url; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $sayfa >= $toplam_sayfa ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo $sayfa >= $toplam_sayfa ? '#' : $next_url; ?>" <?php echo $sayfa >= $toplam_sayfa ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>Sonraki</a>
                </li>
              </ul>
            </nav>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer light-background">

    <div class="container">
      <div class="row gy-3">
        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-geo-alt icon"></i>
          <div class="address">
            <h4>Adres</h4>
            <p>Efeler, Kavala Cd. Aydın Apartmanı No:24/A, 09270</p>
            <p>Didim/Aydın</p>
            <p></p>
          </div>

        </div>

        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-telephone icon"></i>
          <div>
            <h4>İletişim</h4>
            <p>
              <strong>Telefon:</strong> <a href="tel:05304416873">0530 441 68 73</a><br>
              <strong>Email:</strong> <a href="mailto:bilgi@didim.com">bilgi@didim.com</a><br>
            </p>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 d-flex">
          <i class="bi bi-clock icon"></i>
          <div>
            <h4>Çalışma Saatleri</h4>
            <p>
              <strong>Pzts-Cmts:</strong> <span>9:00 - 18:00</span><br>
              <strong>Pazar</strong>: <span>Kapalı</span>
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
      <p>© <span>Telif Hakkı</span> <strong class="px-1 sitename">Nua Yapı</strong><span>'ya aittir. Tüm Hakları Saklıdır</span></p>
      <div class="credits">
        Bu site <a href="https://firatdalkilic.com/" target="_blank">Fırat Dalkılıç</a> tarafından yapılmıştır.
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Yükleniyor Göstergesi -->
  <div id="preloader"></div>

  <!-- Gerekli JS Dosyaları -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>

  <!-- Ana JS Dosyası -->
  <script src="assets/js/main.js"></script>

  <!-- AJAX Filtreleme Scripti -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const filterForm = document.getElementById('filterForm');
      const filterInputs = document.querySelectorAll('.filter-input');
      const propertiesList = document.getElementById('propertiesList');
      const loadingSpinner = document.getElementById('loadingSpinner');
      const clearFiltersBtn = document.getElementById('clearFilters');
      let typingTimer;
      const doneTypingInterval = 500;

      // Input değişikliklerini dinle
      filterInputs.forEach(input => {
        if (input.tagName.toLowerCase() === 'select') {
          // Select elemanları için değişiklik olayı
          input.addEventListener('change', function() {
            submitForm();
          });
        } else {
          // Metin ve sayı girişleri için yazma olayı
          input.addEventListener('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
              submitForm();
            }, doneTypingInterval);
          });
        }
      });

      // Form gönderme olayını dinle
      filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm();
      });

      // Filtreleri temizle
      if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
          filterInputs.forEach(input => {
            input.value = '';
          });
          submitForm();
        });
      }

      // Form gönderme fonksiyonu
      function submitForm() {
        // Yükleniyor göstergesini göster
        loadingSpinner.classList.remove('d-none');
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        // Boş olmayan değerleri URL'e ekle
        for (const [key, value] of formData.entries()) {
          if (value.trim() !== '') {
            params.append(key, value);
          }
        }

        // Sayfa numarasını sıfırla
        params.delete('sayfa');

        // URL'i güncelle
        const newUrl = params.toString() ? `${window.location.pathname}?${params.toString()}` : window.location.pathname;
        window.history.pushState({}, '', newUrl);

        // AJAX isteği gönder
        fetch(newUrl)
          .then(response => response.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // İlanlar listesini güncelle
            const newList = doc.getElementById('propertiesList');
            if (newList) {
              propertiesList.innerHTML = newList.innerHTML;
            }

            // Sayfalama bölümünü güncelle
            const pagination = document.querySelector('nav[aria-label="Sayfalama"]');
            const newPagination = doc.querySelector('nav[aria-label="Sayfalama"]');
            if (pagination && newPagination) {
              pagination.innerHTML = newPagination.innerHTML;
            } else if (pagination) {
              pagination.innerHTML = '';
            }

            // Sonuç bulunamadı mesajını kontrol et
            const noResults = doc.getElementById('noResultsAlert');
            const currentNoResults = document.getElementById('noResultsAlert');
            
            if (noResults) {
              if (!currentNoResults) {
                const propertiesListContainer = document.getElementById('propertiesList');
                propertiesListContainer.insertAdjacentHTML('beforebegin', noResults.outerHTML);
              }
            } else if (currentNoResults) {
              currentNoResults.remove();
            }

            // Temizle butonunun görünürlüğünü güncelle
            const hasFilters = Array.from(filterInputs).some(input => input.value.trim() !== '');
            clearFiltersBtn.style.display = hasFilters ? 'block' : 'none';

            // Yükleniyor göstergesini gizle
            loadingSpinner.classList.add('d-none');
          })
          .catch(error => {
            console.error('Filtreleme hatası:', error);
            loadingSpinner.classList.add('d-none');
          });
      }
    });
  </script>

</body>

</html>