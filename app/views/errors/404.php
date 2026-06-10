<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Страница не найдена</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
  <div class="text-center">
    <h1 class="display-1 fw-bold text-primary">404</h1>
    <h4 class="mb-3">Страница не найдена</h4>
    <p class="text-muted">Запрошенная страница не существует или была удалена.</p>
    <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/feed" class="btn btn-primary">
      На главную
    </a>
  </div>
</body>
</html>
