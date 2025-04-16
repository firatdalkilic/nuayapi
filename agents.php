<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Danışmanlarımız - Nua Yapı</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/nua_logo.jpg" rel="icon">
  <link href="assets/img/nua_logo.jpg" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

</head>

<body class="agents-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <img src="assets/img/nua_logo.jpg" alt="Nua Logo" style="max-height: 60px; border-radius: 50%;">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.html">Anasayfa</a></li>
          <li><a href="about.html">Hakkımızda</a></li>
          <li><a href="services.html">Hizmetlerimiz</a></li>
          <li><a href="properties.php">İlanlar</a></li>
          <li><a href="agents.html" class="active">Danışmanlarımız</a></li>
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
              <h1>Danışmanlarımız</h1>
              <p class="mb-0">Uzman ve deneyimli danışman kadromuz, gayrimenkul sektöründeki derin bilgi birikimleri ve profesyonel yaklaşımlarıyla size en iyi hizmeti sunmak için çalışmaktadır. Her bir danışmanımız, müşterilerimizin ihtiyaçlarını en iyi şekilde anlayıp, onlara en uygun çözümleri sunmak için özenle seçilmiştir.</p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.html">Anasayfa</a></li>
            <li class="current">Danışmanlarımız</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Page Title -->

    <!-- Agents Section -->
    <section id="agents" class="agents section">

      <div class="container">

        <div class="row gy-5">

          <?php
          require_once 'admin/config.php';

          try {
              $stmt = $db->query("SELECT * FROM agents ORDER BY agent_name ASC");
              $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if (count($agents) > 0) {
                  foreach ($agents as $agent) {
                      $agent_photo = !empty($agent['agent_photo']) ? $agent['agent_photo'] : 'assets/img/team/team-1.jpg';
                      $agent_name = !empty($agent['agent_name']) ? $agent['agent_name'] : 'NUA YAPI';
                      $agent_title = !empty($agent['agent_title']) ? $agent['agent_title'] : 'Gayrimenkul Danışmanı';
                      ?>
                      
                      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="member">
                          <div class="pic"><img src="<?php echo htmlspecialchars($agent_photo); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($agent_name); ?>"></div>
                          <div class="member-info">
                            <h4><?php echo htmlspecialchars($agent_name); ?></h4>
                            <span><?php echo htmlspecialchars($agent_title); ?></span>
                            <div class="social">
                              <?php if (!empty($agent['twitter_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['twitter_url']); ?>"><i class="bi bi-twitter-x"></i></a>
                              <?php endif; ?>
                              <?php if (!empty($agent['facebook_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['facebook_url']); ?>"><i class="bi bi-facebook"></i></a>
                              <?php endif; ?>
                              <?php if (!empty($agent['instagram_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['instagram_url']); ?>"><i class="bi bi-instagram"></i></a>
                              <?php endif; ?>
                              <?php if (!empty($agent['linkedin_url'])): ?>
                                <a href="<?php echo htmlspecialchars($agent['linkedin_url']); ?>"><i class="bi bi-linkedin"></i></a>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div><!-- End Team Member -->

                      <?php
                  }
              } else {
                  echo '<div class="text-center"><p>Henüz danışman eklenmemiş.</p></div>';
              }
          } catch(PDOException $e) {
              echo '<div class="text-center"><p>Danışman bilgileri yüklenirken bir hata oluştu.</p></div>';
          }
          ?>

        </div>

      </div>

    </section><!-- End Agents Section -->

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
              <strong>Email:</strong> <a href="mailto:bilgi@didim.com">bilgi@didim.com</a>
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

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>