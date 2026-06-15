<?php $__theme = (($_COOKIE['kadrora_theme'] ?? 'dark') === 'light') ? 'light' : 'dark'; ?>
<!DOCTYPE html>
<html lang="ru" data-theme="<?= $__theme ?>" data-bs-theme="<?= $__theme ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/vendor/icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= @filemtime(BASE_PATH . '/public/css/app.css') ?>">
</head>
<body>
<?php
$uid      = $_SESSION['user_id'] ?? null;
$unreadN  = $uid ? (new Notification())->getUnreadCount($uid)  : 0;
$unreadM  = $uid ? (new Message())->getTotalUnread($uid)        : 0;
$meNav    = $uid ? (new User())->findById($uid)                 : null;
$pendingF = $uid ? count((new Friendship())->getPendingRequests($uid)) : 0;
$navRole  = $meNav['role'] ?? 'user';
$isStaff  = in_array($navRole, ['moderator','admin'], true);
$pendingR = $isStaff ? (new Report())->pendingCount() : 0;
?>

<!-- ====== NAVBAR ====== -->
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">

    <a class="navbar-brand" href="<?= BASE_URL ?>/feed">
      <i class="bi bi-camera-reels-fill"></i><?= APP_NAME ?>
    </a>

    <?php if ($uid): ?>
    <form class="d-flex mx-auto search-form" action="<?= BASE_URL ?>/search" method="GET">
      <input class="form-control" type="search" name="q"
             placeholder="Найти людей, группы..."
             value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off">
      <button class="btn" type="submit"><i class="bi bi-search"></i></button>
    </form>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <i class="bi bi-list text-white fs-4"></i>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navMain">
      <ul class="navbar-nav align-items-center gap-1 ms-2">

        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/feed" title="Лента">
            <i class="bi bi-house-fill"></i>
          </a>
        </li>

        <li class="nav-item position-relative">
          <a class="nav-link" href="<?= BASE_URL ?>/friends" title="Друзья">
            <i class="bi bi-people-fill"></i>
            <?php if ($pendingF > 0): ?>
            <span class="badge-nav"><?= $pendingF ?></span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/groups" title="Сообщества">
            <i class="bi bi-collection-fill"></i>
          </a>
        </li>

        <li class="nav-item position-relative">
          <a class="nav-link" href="<?= BASE_URL ?>/messages" title="Сообщения">
            <i class="bi bi-chat-fill"></i>
            <?php if ($unreadM > 0): ?>
            <span class="badge-nav" id="msg-badge"><?= $unreadM ?></span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-item position-relative">
          <a class="nav-link" href="<?= BASE_URL ?>/notifications" title="Уведомления">
            <i class="bi bi-bell-fill"></i>
            <?php if ($unreadN > 0): ?>
            <span class="badge-nav" id="notif-badge"><?= $unreadN ?></span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-item">
          <button class="theme-toggle" type="button" onclick="toggleTheme()" title="Сменить тему" id="themeToggleBtn">
            <i class="bi bi-<?= $__theme === 'light' ? 'moon-stars-fill' : 'sun-fill' ?>"></i>
          </button>
        </li>

        <li class="nav-item dropdown ms-1">
          <a class="nav-link d-flex align-items-center gap-2 px-2" href="#" data-bs-toggle="dropdown">
            <?php if ($meNav && $meNav['avatar']): ?>
            <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($meNav['avatar']) ?>"
                 class="nav-avatar" alt="">
            <?php else: ?>
            <div class="avatar-placeholder" style="width:32px;height:32px;font-size:.8rem;border-radius:50%">
              <?= mb_strtoupper(mb_substr($meNav['first_name'] ?? 'U', 0, 1)) ?>
            </div>
            <?php endif; ?>
            <span class="d-none d-lg-inline" style="font-size:.875rem;font-weight:600;color:var(--nav-text)">
              <?= htmlspecialchars($meNav['first_name'] ?? '') ?>
            </span>
            <i class="bi bi-chevron-down d-none d-lg-inline" style="font-size:.65rem;color:var(--nav-text)"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/profile/<?= htmlspecialchars($_SESSION['username'] ?? '') ?>">
                <i class="bi bi-person me-2 text-accent"></i>Мой профиль
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/groups?tab=my">
                <i class="bi bi-collection me-2 text-accent"></i>Мои сообщества
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/profile/edit">
                <i class="bi bi-gear me-2 text-accent"></i>Настройки
              </a>
            </li>
            <?php if ($isStaff): ?>
            <li>
              <a class="dropdown-item" href="<?= BASE_URL ?>/admin">
                <i class="bi bi-shield-shaded me-2 text-accent"></i>Панель модерации
                <?php if ($pendingR > 0): ?><span class="badge bg-danger ms-1"><?= $pendingR ?></span><?php endif; ?>
              </a>
            </li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout">
                <i class="bi bi-box-arrow-right me-2"></i>Выйти
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </div>
    <?php endif; ?>
  </div>
</nav>

<!-- ====== CONTENT ====== -->
<div class="main-content">
  <div class="container">
    <?php if ($uid): ?>
    <div class="app-layout">
      <?php include BASE_PATH . '/app/views/partials/left_menu.php'; ?>
      <div class="main-col">
        <?php foreach ($flash ?? [] as $type => $msg): ?>
        <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-3" role="alert">
          <i class="bi bi-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endforeach; ?>
        <?= $content ?>
      </div>
    </div>
    <?php else: ?>
      <?= $content ?>
    <?php endif; ?>
  </div>
</div>

<!-- Report modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-bold"><i class="bi bi-flag me-2" style="color:var(--danger)"></i>Пожаловаться</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="reportType"><input type="hidden" id="reportTargetId">
        <label class="form-label">Причина жалобы</label>
        <select id="reportCategory" class="form-select mb-3">
          <option value="spam">Спам или реклама</option>
          <option value="insult">Оскорбления, травля</option>
          <option value="violence">Насилие, угрозы</option>
          <option value="adult">Порнография (18+)</option>
          <option value="fraud">Мошенничество</option>
          <option value="hate">Разжигание вражды</option>
          <option value="other">Другое</option>
        </select>
        <label class="form-label">Комментарий (необязательно)</label>
        <textarea id="reportComment" class="form-control" rows="2" placeholder="Опишите проблему..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-danger" onclick="submitReport()"><i class="bi bi-flag me-1"></i>Отправить жалобу</button>
      </div>
    </div>
  </div>
</div>

<!-- Photo modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pt-0">
        <img id="photoModalImg" src="" class="img-fluid rounded" style="max-height:88vh">
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/js/app.js?v=<?= @filemtime(BASE_PATH . '/public/js/app.js') ?>"></script>
<?php if ($uid): ?>
<script>
const CSRF_TOKEN      = '<?= $csrf ?? $_SESSION['csrf_token'] ?? '' ?>';
const BASE_URL        = '<?= BASE_URL ?>';
const CURRENT_USER_ID = <?= $uid ?>;
</script>
<script src="<?= BASE_URL ?>/js/notifications.js?v=<?= @filemtime(BASE_PATH . '/public/js/notifications.js') ?>"></script>
<?php endif; ?>
</body>
</html>
