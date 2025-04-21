<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>404 - Sayfa Bulunamadı | Nua Yapı</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/nua_logo.jpg" rel="icon">
  <link href="assets/img/nua_logo.jpg" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    .error-404 {
      padding: 120px 0;
      text-align: center;
      min-height: 100vh;
      display: flex;
      align-items: center;
      background-color: #f8f9fa;
    }

    .error-404 h1 {
      font-size: 120px;
      font-weight: 700;
      color: #002e5c;
      margin-bottom: 20px;
      line-height: 1;
    }

    .error-404 h2 {
      font-size: 24px;
      font-weight: 500;
      color: #6c757d;
      margin-bottom: 30px;
    }

    .error-404 p {
      color: #6c757d;
      margin-bottom: 30px;
    }

    .error-404 .btn-primary {
      background-color: #002e5c;
      border-color: #002e5c;
      padding: 12px 30px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .error-404 .btn-primary:hover {
      background-color: #001f3f;
      border-color: #001f3f;
    }
  </style>
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <main id="main">
    <section class="error-404">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-6">
            <h1>404</h1>
            <h2>Sayfa Bulunamadı</h2>
            <p>Aradığınız sayfa taşınmış, silinmiş veya hiç var olmamış olabilir.</p>
            <a href="/" class="btn btn-primary">Ana Sayfaya Dön</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html> 