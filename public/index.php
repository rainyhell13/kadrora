<?php

declare(strict_types=1);

// Сессия
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', (string)(7 * 24 * 3600));
session_start();

// Конфиг
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Хелперы
require_once BASE_PATH . '/app/helpers.php';

// Ядро
require_once BASE_PATH . '/app/Router.php';

// Модели (Model.php первым — он базовый)
require_once BASE_PATH . '/app/models/Model.php';
foreach (glob(BASE_PATH . '/app/models/*.php') as $f) {
    if (basename($f) !== 'Model.php') require_once $f;
}

// Контроллеры (Controller.php первым — он базовый)
require_once BASE_PATH . '/app/controllers/Controller.php';
foreach (glob(BASE_PATH . '/app/controllers/*.php') as $f) {
    if (basename($f) !== 'Controller.php') require_once $f;
}

// ===================== МАРШРУТЫ =====================
$router = new Router();

// Auth
$router->get('/',                  'AuthController', 'loginPage');
$router->get('/login',             'AuthController', 'loginPage');
$router->post('/login',            'AuthController', 'login');
$router->get('/register',          'AuthController', 'registerPage');
$router->post('/register',         'AuthController', 'register');
$router->get('/logout',            'AuthController', 'logout');

// Feed
$router->get('/feed',              'FeedController', 'index');
$router->post('/post/create',      'FeedController', 'createPost');
$router->post('/post/delete',      'FeedController', 'deletePost');
$router->post('/post/like',        'FeedController', 'likePost');

// Comments
$router->post('/comment/create',   'CommentController', 'create');
$router->post('/comment/delete',   'CommentController', 'delete');
$router->get('/comment/list',      'CommentController', 'getByPost');

// Profile
$router->get('/profile/edit',      'ProfileController', 'editPage');
$router->post('/profile/update',   'ProfileController', 'update');
$router->post('/profile/password', 'ProfileController', 'changePassword');
$router->post('/profile/anketa',   'ProfileController', 'updateAnketa');
$router->post('/profile/privacy',  'ProfileController', 'updatePrivacy');
$router->post('/profile/status',   'ProfileController', 'updateStatus');
$router->post('/wall/post',        'ProfileController', 'wallPost');
$router->get('/profile/{username}', 'ProfileController', 'show');
$router->get('/profile/{username}/photos', 'ProfileController', 'photos');
$router->post('/photo/delete',     'ProfileController', 'deletePhoto');

// Video
$router->get('/video/{username}',  'VideoController', 'index');
$router->post('/video/upload',     'VideoController', 'upload');
$router->post('/video/delete',     'VideoController', 'delete');

// Bookmarks (Закладки)
$router->get('/bookmarks',           'BookmarkController', 'index');
$router->post('/bookmark/toggle',    'BookmarkController', 'toggle');
$router->post('/bookmark/person',    'BookmarkController', 'togglePerson');

// Documents (Документы)
$router->get('/documents',                 'DocumentController', 'index');
$router->post('/documents/upload',         'DocumentController', 'upload');
$router->post('/documents/delete',         'DocumentController', 'delete');
$router->get('/documents/{id}/download',   'DocumentController', 'download');

// Theme (тёмная/светлая)
$router->post('/theme/set',        'ThemeController', 'set');

// Reports (жалобы)
$router->post('/report',           'ReportController', 'create');

// Admin / Moderation
$router->get('/admin',                   'AdminController', 'dashboard');
$router->get('/admin/reports',           'AdminController', 'reports');
$router->post('/admin/report/resolve',   'AdminController', 'resolveReport');
$router->get('/admin/users',             'AdminController', 'users');
$router->post('/admin/user/action',      'AdminController', 'userAction');
$router->get('/admin/content',           'AdminController', 'content');
$router->post('/admin/content/action',   'AdminController', 'contentAction');
$router->get('/admin/words',             'AdminController', 'words');
$router->post('/admin/words/add',        'AdminController', 'wordAdd');
$router->post('/admin/words/remove',     'AdminController', 'wordRemove');
$router->get('/admin/log',               'AdminController', 'log');

// Friends
$router->get('/friends',           'FriendController', 'index');
$router->post('/friend/add',       'FriendController', 'sendRequest');
$router->post('/friend/accept',    'FriendController', 'accept');
$router->post('/friend/decline',   'FriendController', 'decline');
$router->post('/friend/remove',    'FriendController', 'remove');

// Messages
$router->get('/messages',          'MessageController', 'index');
$router->get('/messages/{id}',     'MessageController', 'conversation');
$router->post('/messages/send',    'MessageController', 'send');
$router->get('/messages/poll',     'MessageController', 'getNew');

// Notifications
$router->get('/notifications',     'NotificationController', 'index');
$router->get('/notifications/count', 'NotificationController', 'getCount');

// Search
$router->get('/search',            'SearchController', 'index');

// Groups
$router->get('/groups',                   'GroupController', 'index');
$router->get('/groups/create',            'GroupController', 'createPage');
$router->post('/groups/create',           'GroupController', 'create');
$router->post('/groups/join',             'GroupController', 'join');
$router->post('/groups/leave',            'GroupController', 'leave');
$router->post('/groups/request/cancel',   'GroupController', 'cancelRequest');
$router->post('/groups/request/accept',   'GroupController', 'acceptRequest');
$router->post('/groups/request/decline',  'GroupController', 'declineRequest');
$router->post('/groups/post/create',      'GroupController', 'createPost');
$router->post('/groups/post/delete',      'GroupController', 'deletePost');
$router->post('/groups/post/like',        'GroupController', 'likePost');
$router->get('/groups/{slug}',            'GroupController', 'show');
$router->get('/groups/{slug}/edit',       'GroupController', 'editPage');
$router->post('/groups/{slug}/edit',      'GroupController', 'edit');
$router->post('/groups/{slug}/delete',    'GroupController', 'delete');

// Диспетчеризация
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
$uri    = '/' . ltrim(substr($uri, strlen($base)), '/');

$router->dispatch($method, $uri);
