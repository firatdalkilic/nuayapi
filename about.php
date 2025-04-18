<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Hakkımızda - Nua Yapı</title>
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

<body class="about-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <img src="assets/img/nua_logo.jpg" alt="Nua Logo" style="max-height: 60px; border-radius: 50%;">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.html">Anasayfa</a></li>
          <li><a href="about.php" class="active">Hakkımızda</a></li>
          <li><a href="services.html">Hizmetlerimiz</a></li>
          <li><a href="properties.php">İlanlar</a></li>
          <li><a href="agents.html">Danışmanlarımız</a></li>
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
              <h1>Hakkımızda</h1>
              <p class="mb-0">20 yılı aşkın tecrübemizle Didim'de gayrimenkul sektörünün öncü firmalarından biri olarak, müşterilerimize en iyi hizmeti sunmaya devam ediyoruz.</p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.html">Anasayfa</a></li>
            <li class="current">Hakkımızda</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Page Title -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
            <p class="who-we-are">Biz Kimiz?</p>
            <h3>Güvenilir Gayrimenkul Çözüm Ortağınız</h3>
            <p class="fst-italic">
              2003 yılından bu yana Didim ve çevresinde, müşterilerimize kaliteli ve güvenilir gayrimenkul hizmetleri sunuyoruz. Deneyimli ekibimiz ve geniş portföyümüzle hayalinizdeki eve ulaşmanızı sağlıyoruz.
            </p>
            <ul>
              <li><i class="bi bi-check-circle"></i> <span>Profesyonel ve deneyimli gayrimenkul danışmanlarımızla kişiye özel hizmet.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Geniş portföy ağımız ve güncel piyasa bilgisiyle doğru yatırım fırsatları.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Satış ve kiralama süreçlerinde şeffaf ve güvenilir danışmanlık hizmeti.</span></li>
            </ul>
          </div>

          <div class="col-lg-6 about-images" data-aos="fade-up" data-aos-delay="200">
            <?php
            require_once 'admin/config.php';

            try {
                $stmt = $db->query("SELECT * FROM agents ORDER BY agent_name ASC");
                $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($agents) > 0) {
                    echo '<div class="row gy-4">';
                    $count = 0;
                    foreach ($agents as $agent) {
                        $agent_photo = !empty($agent['agent_photo']) ? $agent['agent_photo'] : 'assets/img/nua_logo.jpg';
                        $agent_name = !empty($agent['agent_name']) ? $agent['agent_name'] : 'NUA YAPI';
                        
                        if ($count === 0) {
                            // İlk resim - büyük kart
                            echo '<div class="col-lg-6">';
                            echo '<img src="' . htmlspecialchars($agent_photo) . '" class="img-fluid" alt="' . htmlspecialchars($agent_name) . '">';
                            echo '</div>';
                        } else if ($count <= 2) {
                            // Diğer resimler - küçük kartlar
                            if ($count === 1) {
                                echo '<div class="col-lg-6"><div class="row gy-4">';
                            }
                            echo '<div class="col-lg-12">';
                            echo '<img src="' . htmlspecialchars($agent_photo) . '" class="img-fluid" alt="' . htmlspecialchars($agent_name) . '">';
                            echo '</div>';
                            if ($count === 2) {
                                echo '</div></div>';
                            }
                        }
                        $count++;
                        if ($count >= 3) break; // Sadece ilk 3 danışmanı göster
                    }
                    echo '</div>';
                }
            } catch(PDOException $e) {
                echo '<div class="text-center"><p>Danışman bilgileri yüklenirken bir hata oluştu.</p></div>';
            }
            ?>
          </div>

        </div>

      </div>
    </section><!-- /About Section -->

    <!-- Stats Section -->
    <section id="stats" class="stats section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-3 col-md-6">
            <div class="stats-item d-flex align-items-center w-100 h-100">
              <i class="bi bi-emoji-smile color-blue flex-shrink-0"></i>
              <div>
                <span data-purecounter-start="0" data-purecounter-end="1250" data-purecounter-duration="1" class="purecounter"></span>
                <p>Mutlu Müşteri</p>
              </div>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6">
            <div class="stats-item d-flex align-items-center w-100 h-100">
              <i class="bi bi-journal-richtext color-orange flex-shrink-0"></i>
              <div>
                <span data-purecounter-start="0" data-purecounter-end="2500" data-purecounter-duration="1" class="purecounter"></span>
                <p>Tamamlanan Satış</p>
              </div>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6">
            <div class="stats-item d-flex align-items-center w-100 h-100">
              <i class="bi bi-house-check color-green flex-shrink-0"></i>
              <div>
                <span data-purecounter-start="0" data-purecounter-end="500" data-purecounter-duration="1" class="purecounter"></span>
                <p>Aktif İlan</p>
              </div>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6">
            <div class="stats-item d-flex align-items-center w-100 h-100">
              <i class="bi bi-people color-pink flex-shrink-0"></i>
              <div>
                <span data-purecounter-start="0" data-purecounter-end="25" data-purecounter-duration="1" class="purecounter"></span>
                <p>Uzman Kadro</p>
              </div>
            </div>
          </div><!-- End Stats Item -->

        </div>

      </div>

    </section><!-- /Stats Section -->

    <!-- Features Section -->
    <section id="features" class="features section">

      <div class="container">

        <div class="row justify-content-around gy-4">
          <div class="features-image col-lg-6" data-aos="fade-up" data-aos-delay="100"><img src="assets/img/features-bg.jpg" alt=""></div>

          <div class="col-lg-5 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <h3>Neden Bizi Tercih Etmelisiniz?</h3>
            <p>20 yıllık sektör tecrübemiz ve profesyonel ekibimizle, gayrimenkul alım-satım süreçlerinizde yanınızdayız. Müşteri memnuniyeti odaklı çalışma anlayışımızla fark yaratıyoruz.</p>

            <div class="icon-box d-flex position-relative" data-aos="fade-up" data-aos-delay="300">
              <i class="bi bi-shield-check flex-shrink-0"></i>
              <div>
                <h4 class="stretched-link">Güvenilir Hizmet</h4>
                <p>Tüm işlemlerinizde yasal süreçleri titizlikle takip ediyor, şeffaf ve güvenilir hizmet sunuyoruz.</p>
              </div>
            </div><!-- End Icon Box -->

            <div class="icon-box d-flex position-relative" data-aos="fade-up" data-aos-delay="400">
              <i class="bi bi-graph-up-arrow flex-shrink-0"></i>
              <div>
                <h4 class="stretched-link">Piyasa Uzmanlığı</h4>
                <p>Güncel piyasa analizleri ve doğru değerleme ile en iyi yatırım fırsatlarını sizinle buluşturuyoruz.</p>
              </div>
            </div><!-- End Icon Box -->

            <div class="icon-box d-flex position-relative" data-aos="fade-up" data-aos-delay="500">
              <i class="bi bi-person-check flex-shrink-0"></i>
              <div>
                <h4 class="stretched-link">Kişiye Özel Danışmanlık</h4>
                <p>Her müşterimizin ihtiyaç ve beklentilerine özel çözümler üretiyor, süreç boyunca yanınızda oluyoruz.</p>
              </div>
            </div><!-- End Icon Box -->

            <div class="icon-box d-flex position-relative" data-aos="fade-up" data-aos-delay="600">
              <i class="bi bi-buildings flex-shrink-0"></i>
              <div>
                <h4 class="stretched-link">Geniş Portföy</h4>
                <p>Didim ve çevresinde geniş bir portföye sahip olup, her bütçeye uygun seçenekler sunuyoruz.</p>
              </div>
            </div><!-- End Icon Box -->

          </div>
        </div>

      </div>

    </section><!-- /Features Section -->

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