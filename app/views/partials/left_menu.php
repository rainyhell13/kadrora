<?php
/**
 * Левое меню сайта. Доступно на всех страницах для авторизованных.
 * Ожидает: $meNav (текущий пользователь), $unreadM, $pendingF.
 */
$myUsername = $_SESSION['username'] ?? '';
$basePath   = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
$curPath    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$curPath    = '/' . ltrim(substr($curPath, strlen($basePath)), '/');

$isActive = function (string $pattern) use ($curPath): bool {
    if ($pattern === '/feed')   return $curPath === '/feed' || $curPath === '/';
    return str_starts_with($curPath, $pattern);
};

$menu = [
    ['Моя Страница', 'person',           "/profile/{$myUsername}",        null,
        $curPath === "/profile/{$myUsername}"],
    ['Новости',      'newspaper',        '/feed',                         null, $isActive('/feed')],
    ['Сообщения',    'chat-dots',        '/messages',                     $unreadM ?? 0, $isActive('/messages')],
    ['Друзья',       'people',           '/friends',                      $pendingF ?? 0, $isActive('/friends')],
    ['Сообщества',   'collection',       '/groups',                       null, $isActive('/groups')],
    ['Фотографии',   'image',            "/profile/{$myUsername}/photos", null, $isActive("/profile/{$myUsername}/photos")],
    ['Видеозаписи',  'camera-video',     "/video/{$myUsername}",          null, $isActive('/video')],
    ['Закладки',     'bookmark-star',    '/bookmarks',                    null, $isActive('/bookmarks')],
    ['Документы',    'file-earmark-text','/documents',                    null, $isActive('/documents')],
];
?>
<aside class="side-nav">
  <nav class="side-menu">
    <?php foreach ($menu as [$label, $icon, $url, $count, $active]): ?>
    <a href="<?= BASE_URL . $url ?>" class="side-menu-item<?= $active ? ' active' : '' ?>">
      <i class="bi bi-<?= $icon ?>"></i>
      <span><?= $label ?></span>
      <?php if ($count): ?><span class="menu-count"><?= $count ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>
</aside>
