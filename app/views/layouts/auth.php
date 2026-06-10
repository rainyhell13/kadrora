<?php $__theme = (($_COOKIE['kadrora_theme'] ?? 'dark') === 'light') ? 'light' : 'dark'; ?>
<!DOCTYPE html>
<html lang="ru" data-theme="<?= $__theme ?>" data-bs-theme="<?= $__theme ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/vendor/icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= @filemtime(BASE_PATH . '/public/css/app.css') ?>">
</head>
<body class="auth-body d-flex align-items-center justify-content-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

        <div class="text-center mb-4">
          <a href="<?= BASE_URL ?>/" class="text-decoration-none">
            <div class="auth-logo mb-1">
              <i class="bi bi-camera-reels-fill me-2"></i><?= APP_NAME ?>
            </div>
          </a>
          <p style="color:var(--text-muted);font-size:.9rem">Общайся. Делись. Будь собой.</p>
        </div>

        <?= $content ?>

      </div>
    </div>
  </div>
  <script src="<?= BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
