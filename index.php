<?php
require_once 'admin/config.php';

// Son eklenen 10 ilanı getir
$query = "SELECT p.*, pi.image_name,
          CASE 
              WHEN p.property_type = 'Arsa' THEN p.net_area 
              ELSE p.net_area
          END as display_area,
          CONCAT(
              CASE 
                  WHEN p.room_count > 0 THEN p.room_count
                  ELSE ''
              END,
              CASE 
                  WHEN p.room_count > 0 AND p.living_room > 0 THEN '+'
                  ELSE ''
              END,
              CASE 
                  WHEN p.living_room > 0 THEN p.living_room
                  ELSE ''
              END
          ) as room_display
          FROM properties p 
          LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_featured = 1 
          ORDER BY p.created_at DESC 
          LIMIT 10";
$result = $conn->query($query);
$latest_properties = [];
while ($row = $result->fetch_assoc()) {
    $latest_properties[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Nua Yapı - Gayrimenkul</title>
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
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    .latest-properties {
      padding: 60px 0;
      background: #f8f9fa;
    }

    .latest-properties .section-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .latest-properties .section-header h2 {
      font-size: 32px;
      font-weight: 700;
      color: #002e5c;
    }

    .latest-properties .section-header p {
      color: #6c757d;
    }

    .property-card {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      height: 450px; /* Sabit yükseklik */
      position: relative;
      display: flex;
      flex-direction: column;
    }

    .property-card .status-badge {
      position: absolute;
      top: 15px;
      left: 15px;
      padding: 5px 15px;
      border-radius: 4px;
      font-size: 14px;
      font-weight: 500;
      z-index: 1;
    }

    .property-card .status-badge.kiralık {
      background: #28a745;
      color: #fff;
    }

    .property-card .status-badge.satılık {
      background: #002e5c;
      color: #fff;
    }

    .property-card .image {
      position: relative;
      height: 250px; /* Sabit yükseklik */
      overflow: hidden;
    }

    .property-card .image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .property-card:hover .image img {
      transform: scale(1.1);
    }

    .property-card .content {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .property-card h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #002e5c;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .property-card .location {
      display: flex;
      align-items: center;
      color: #6c757d;
      font-size: 14px;
      margin-bottom: 10px;
    }

    .property-card .location i {
      margin-right: 5px;
      color: #002e5c;
    }

    .property-card .details {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 15px;
    }

    .property-card .detail-item {
      display: flex;
      align-items: center;
      color: #6c757d;
      font-size: 14px;
      background: #f8f9fa;
      padding: 5px 10px;
      border-radius: 4px;
    }

    .property-card .detail-item i {
      margin-right: 5px;
      color: #002e5c;
    }

    .property-card .price {
      font-size: 20px;
      font-weight: 700;
      color: #002e5c;
      margin-top: auto;
    }

    .swiper-button-next,
    .swiper-button-prev {
      color: #002e5c;
      background: rgba(255, 255, 255, 0.9);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
      font-size: 20px;
    }

    .swiper-pagination-bullet {
      background: #002e5c;
    }

    @media (max-width: 1200px) {
      .swiper-slide {
        width: 33.333%;
      }
    }

    @media (max-width: 992px) {
      .swiper-slide {
        width: 50%;
      }
    }

    @media (max-width: 576px) {
      .swiper-slide {
        width: 100%;
      }
    }
  </style>

</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <img src="assets/img/nua_logo.jpg" alt="Nua Logo" style="max-height: 60px; border-radius: 50%;">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.html" class="active">Anasayfa</a></li>
          <li><a href="about.php">Hakkımızda</a></li>
          <li><a href="services.html">Hizmetlerimiz</a></li>
          <li><a href="properties.php">İlanlar</a></li>
          <li><a href="agents.php">Danışmanlarımız</a></li>
          <li><a href="contact.html">İletişim</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

      <div id="hero-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

        <div class="carousel-item active">
          <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80" alt="Modern Emlak Ofisi">
          <div class="carousel-container">
            <div>
              <h2>Hayalinizdeki Eve</h2>
              <p>Bir Adım Uzaktasınız</p>
              <a href="properties.php" class="btn-get-started">İlanları İncele</a>
            </div>
          </div>
        </div><!-- End Carousel Item -->

        <div class="carousel-item">
          <img src="https://images.unsplash.com/photo-1628744448840-55bdb2497bd4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80" alt="Lüks Villa">
          <div class="carousel-container">
            <div>
              <h2>Güvenilir Yatırımın Adresi</h2>
              <p>20 Yıllık Tecrübemizle Yanınızdayız</p>
              <a href="about.php" class="btn-get-started">Bizi Tanıyın</a>
            </div>
          </div>
        </div><!-- End Carousel Item -->

        <div class="carousel-item">
          <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80" alt="Modern Villa">
          <div class="carousel-container">
            <div>
              <h2>Mutluluğunuz İçin</h2>
              <p>En Doğru Adresleri Sunuyoruz</p>
              <a href="contact.html" class="btn-get-started">Bize Ulaşın</a>
            </div>
          </div>
        </div><!-- End Carousel Item -->

        <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
          <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
        </a>

        <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
          <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
        </a>

        <ol class="carousel-indicators"></ol>

      </div>

    </section><!-- /Hero Section -->

        <!-- ======= Latest Properties Section ======= -->
        <section class="latest-properties">
      <div class="container">
        <div class="section-header">
          <h2>Son Eklenen İlanlar</h2>
          <p>En son eklenen gayrimenkul fırsatlarını keşfedin</p>
        </div>

        <div class="swiper latest-properties-slider">
          <div class="swiper-wrapper">
            <?php foreach ($latest_properties as $property): ?>
              <div class="swiper-slide">
                <div class="property-card">
                  <a href="property-single.php?id=<?php echo $property['id']; ?>" class="text-decoration-none">
                    <span class="status-badge <?php echo $property['status'] == 'sale' ? 'satılık' : 'kiralık'; ?>">
                      <?php echo $property['status'] == 'sale' ? 'Satılık' : 'Kiralık'; ?>
                    </span>
                    <div class="image">
                      <img src="<?php 
                        echo !empty($property['image_name']) 
                          ? (strpos($property['image_name'], 'assets/') === 0 
                             ? $property['image_name'] 
                             : 'uploads/' . htmlspecialchars($property['image_name']))
                          : 'assets/img/property-default.jpg';
                      ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                    </div>
                    <div class="content">
                      <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                      <div class="location">
                        <i class="bi bi-geo-alt"></i>
                        <?php 
                          echo !empty($property['location']) ? htmlspecialchars($property['location']) : 'Didim';
                          if (!empty($property['neighborhood'])) {
                              echo ' / ' . htmlspecialchars($property['neighborhood']);
                          }
                        ?>
                      </div>
                      <div class="details">
                        <div class="detail-item">
                          <i class="bi bi-building"></i>
                          <?php echo $property['status'] == 'sale' ? 'Satılık' : 'Kiralık'; ?> <?php echo htmlspecialchars($property['property_type']); ?>
                        </div>
                        <?php if (!empty($property['display_area'])): ?>
                        <div class="detail-item">
                          <i class="bi bi-rulers"></i>
                          <?php echo number_format($property['display_area'], 0, ',', '.'); ?> m²
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['room_display'])): ?>
                        <div class="detail-item">
                          <i class="bi bi-door-open"></i>
                          <?php echo htmlspecialchars($property['room_display']); ?>
                        </div>
                        <?php endif; ?>
                      </div>
                      <div class="price">
                        <?php 
                          $price = !empty($property['price']) ? (float)$property['price'] : 0;
                          echo $price > 0 ? number_format($price, 0, ',', '.') . ' TL' : 'Fiyat Sorunuz';
                          if ($property['status'] == 'rent' && $price > 0): 
                        ?>
                          <small>/ay</small>
                        <?php endif; ?>
                      </div>
                    </div>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
        </div>
      </div>
    </section><!-- End Latest Properties Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Müşteri Yorumları</h2>
        <p>Değerli müşterilerimizin bizimle ilgili düşünceleri</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 1,
                  "spaceBetween": 40
                },
                "1200": {
                  "slidesPerView": 3,
                  "spaceBetween": 1
                }
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Profesyonel ve güvenilir hizmet anlayışları ile hayalinizdeki eve kavuşmanızı sağlıyorlar. Çok memnun kaldım ve herkese tavsiye ederim.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/default-avatar.jpg" class="testimonial-img" alt="Müşteri Yorumu">
                  <h3>Mehmet Yıldırım</h3>
                  <h4>İş İnsanı</h4>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Kiralama sürecinde gösterdikleri ilgi ve alakadan dolayı çok teşekkür ederim. Her şey sorunsuz ve hızlı bir şekilde halledildi.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/default-avatar.jpg" class="testimonial-img" alt="Müşteri Yorumu">
                  <h3>Ayşe Kaya</h3>
                  <h4>Tasarımcı</h4>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Gayrimenkul danışmanları çok ilgili ve bilgiliydi. İhtiyacımıza en uygun evi bulmamıza yardımcı oldular.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/default-avatar.jpg" class="testimonial-img" alt="Müşteri Yorumu">
                  <h3>Zeynep Demir</h3>
                  <h4>Mağaza Sahibi</h4>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Ev alma sürecinde gösterdikleri profesyonel yaklaşım ve şeffaflık için teşekkür ederim. Kesinlikle tavsiye ediyorum.
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/default-avatar.jpg" class="testimonial-img" alt="Müşteri Yorumu">
                  <h3>Ali Öztürk</h3>
                  <h4>Serbest Çalışan</h4>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  Müşteri memnuniyetini ön planda tutan, güvenilir ve profesyonel bir ekip. Teşekkürler!
                </p>
                <div class="profile mt-auto">
                  <img src="assets/img/testimonials/default-avatar.jpg" class="testimonial-img" alt="Müşteri Yorumu">
                  <h3>Fatma Çelik</h3>
                  <h4>Girişimci</h4>
                </div>
              </div>
            </div><!-- End testimonial item -->

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->



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
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      new Swiper('.latest-properties-slider', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        breakpoints: {
          576: {
            slidesPerView: 2,
          },
          992: {
            slidesPerView: 3,
          },
          1200: {
            slidesPerView: 5,
          },
        },
      });
    });
  </script>

</body>

</html>