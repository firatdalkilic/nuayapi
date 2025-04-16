<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Danışmanlarımız - Nua Yapı</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/nua_logo.jpg" rel="icon">
  <link href="assets/img/nua_logo.jpg" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600;1,700&family=Roboto:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Work+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    .agent-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      margin-bottom: 30px;
      transition: transform 0.3s ease;
    }

    .agent-card:hover {
      transform: translateY(-5px);
    }

    .agent-image {
      width: 100%;
      height: 300px;
      object-fit: cover;
    }

    .agent-info {
      padding: 20px;
      text-align: center;
    }

    .agent-name {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2e3339;
      margin: 10px 0;
    }

    .agent-title {
      color: #838893;
      font-size: 1rem;
      margin-bottom: 15px;
    }

    .social-links {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 15px;
    }

    .social-links a {
      color: #838893;
      font-size: 1.2rem;
      transition: color 0.3s ease;
    }

    .social-links a:hover {
      color: #feb900;
    }
  </style>
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header d-flex align-items-center">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <img src="assets/img/nua_logo.jpg" alt="Nua Logo" style="max-height: 60px; border-radius: 50%;">
      </a>

      <i class="mobile-nav-toggle mobile-nav-show bi bi-list"></i>
      <i class="mobile-nav-toggle mobile-nav-hide d-none bi bi-x"></i>
      <nav id="navbar" class="navbar">
        <ul>
          <li><a href="index.html">Anasayfa</a></li>
          <li><a href="about.html">Hakkımızda</a></li>
          <li><a href="services.html">Hizmetlerimiz</a></li>
          <li><a href="properties.php">İlanlar</a></li>
          <li><a href="agents.php" class="active">Danışmanlarımız</a></li>
          <li><a href="contact.html">İletişim</a></li>
        </ul>
      </nav>

    </div>
  </header><!-- End Header -->

  <main id="main">

    <!-- ======= Breadcrumbs ======= -->
    <div class="breadcrumbs d-flex align-items-center" style="background-image: url('assets/img/breadcrumbs-bg.jpg');">
      <div class="container position-relative d-flex flex-column align-items-center" data-aos="fade">
        <h2>Danışmanlarımız</h2>
        <ol>
          <li><a href="index.html">Anasayfa</a></li>
          <li>Danışmanlarımız</li>
        </ol>
      </div>
    </div>

    <!-- ======= Agents Section ======= -->
    <section id="agents" class="agents section-bg">
      <div class="container" data-aos="fade-up">
        <div class="row">
          <?php
          require_once 'admin/config.php';

          // Danışmanları veritabanından çek
          $query = "SELECT * FROM agents ORDER BY agent_name ASC";
          $result = $conn->query($query);

          if ($result && $result->num_rows > 0) {
            while ($agent = $result->fetch_assoc()) {
              ?>
              <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="agent-card">
                  <img src="<?php echo htmlspecialchars($agent['image']); ?>" 
                       alt="<?php echo htmlspecialchars($agent['agent_name']); ?>" 
                       class="agent-image"
                       onerror="this.src='assets/img/nua_logo.jpg'">
                  <div class="agent-info">
                    <h3 class="agent-name"><?php echo htmlspecialchars($agent['agent_name']); ?></h3>
                    <p class="agent-title">Gayrimenkul Danışmanı</p>
                    <div class="social-links">
                      <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                      <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                      <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                      <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <?php
            }
          } else {
            echo '<div class="col-12 text-center"><p>Henüz danışman eklenmemiş.</p></div>';
          }
          ?>
        </div>
      </div>
    </section>

  </main>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">

    <div class="footer-content position-relative">
      <div class="container">
        <div class="row">

          <div class="col-lg-4 col-md-6">
            <div class="footer-info">
              <h3>Nua Yapı</h3>
              <p>
                Efeler Mahallesi <br>
                Kavala Caddesi No:24/A<br>
                Didim/AYDIN<br><br>
                <strong>Telefon:</strong> <a href="tel:05304416873">0530 441 68 73</a><br>
                <strong>Email:</strong> <a href="mailto:info@nuayapi.com">info@nuayapi.com</a><br>
              </p>
              <div class="social-links d-flex mt-3">
                <a href="#" class="d-flex align-items-center justify-content-center"><i class="bi bi-twitter"></i></a>
                <a href="#" class="d-flex align-items-center justify-content-center"><i class="bi bi-facebook"></i></a>
                <a href="#" class="d-flex align-items-center justify-content-center"><i class="bi bi-instagram"></i></a>
                <a href="#" class="d-flex align-items-center justify-content-center"><i class="bi bi-linkedin"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 footer-links">
            <h4>Hızlı Menü</h4>
            <ul>
              <li><a href="index.html">Anasayfa</a></li>
              <li><a href="about.html">Hakkımızda</a></li>
              <li><a href="services.html">Hizmetlerimiz</a></li>
              <li><a href="properties.php">İlanlar</a></li>
              <li><a href="contact.html">İletişim</a></li>
            </ul>
          </div>

          <div class="col-lg-2 col-md-3 footer-links">
            <h4>Hizmetlerimiz</h4>
            <ul>
              <li><a href="services.html">Emlak Danışmanlığı</a></li>
              <li><a href="services.html">Gayrimenkul Değerleme</a></li>
              <li><a href="services.html">Hukuki Danışmanlık</a></li>
              <li><a href="services.html">Konut Kredisi</a></li>
              <li><a href="services.html">Yatırım Danışmanlığı</a></li>
            </ul>
          </div>

          <div class="col-lg-2 col-md-3 footer-links">
            <h4>İletişim Saatleri</h4>
            <ul>
              <li><strong>Pazartesi - Cumartesi:</strong> <br>09:00 - 18:00</li>
              <li><strong>Pazar:</strong> <br>Kapalı</li>
            </ul>
          </div>

        </div>
      </div>
    </div>

    <div class="footer-legal text-center position-relative">
      <div class="container">
        <div class="copyright">
          &copy; <strong><span>Nua Yapı</span></strong> tüm hakları saklıdır.
        </div>
        <div class="credits">
          Bu site <a href="https://firatdalkilic.com/" target="_blank">Fırat Dalkılıç</a> tarafından yapılmıştır.
        </div>
      </div>
    </div>

  </footer>
  <!-- End Footer -->

  <a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html> 