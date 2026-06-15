<?php
/** Шапка админ-панели с вкладками. Ожидает $active, $pending. */
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
$cur = '/' . ltrim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen($basePath)), '/');
$tabs = [
  ['/admin',         'speedometer2',      'Дашборд',      null],
  ['/admin/reports', 'flag',              'Жалобы',       $pending ?? 0],
  ['/admin/users',   'people',            'Пользователи', null],
  ['/admin/content', 'card-text',         'Контент',      null],
  ['/admin/words',   'slash-circle',      'Стоп-слова',   null],
  ['/admin/log',     'clock-history',     'Журнал',       null],
];
?>
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <h4 class="fw-bold mb-0"><i class="bi bi-shield-shaded me-2" style="color:var(--accent)"></i>Панель модерации</h4>
  <a href="<?= BASE_URL ?>/feed" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>К сайту</a>
</div>
<ul class="nav nav-tabs mb-3">
  <?php foreach ($tabs as [$url, $icon, $label, $badge]):
    $isAct = ($url === '/admin') ? ($cur === '/admin') : str_starts_with($cur, $url); ?>
  <li class="nav-item">
    <a class="nav-link <?= $isAct ? 'active' : '' ?>" href="<?= BASE_URL . $url ?>">
      <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?>
      <?php if ($badge): ?><span class="badge bg-danger ms-1"><?= $badge ?></span><?php endif; ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>
