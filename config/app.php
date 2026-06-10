<?php

define('APP_NAME',    'Kadrora');
define('APP_VERSION', '1.0.0');

// BASE_URL определяется автоматически по адресу, с которого открыто приложение.
// Это позволяет работать и через localhost, и через 127.0.0.1, и по локальной сети
// без правки конфигурации. В CLI-режиме используется значение по умолчанию.
if (PHP_SAPI === 'cli') {
    define('BASE_URL', 'http://127.0.0.1:8000');
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
    define('BASE_URL', $scheme . '://' . $host);
}

define('BASE_PATH',   dirname(__DIR__));

define('UPLOAD_PATH',        BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
define('AVATAR_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'avatars');
define('PHOTO_UPLOAD_PATH',  UPLOAD_PATH . DIRECTORY_SEPARATOR . 'photos');
define('VIDEO_UPLOAD_PATH',  UPLOAD_PATH . DIRECTORY_SEPARATOR . 'video');
define('DOC_UPLOAD_PATH',    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'docs');

define('MAX_UPLOAD_SIZE',       10 * 1024 * 1024);  // 10 MB (изображения)
define('MAX_VIDEO_SIZE',       100 * 1024 * 1024);  // 100 MB
define('MAX_DOC_SIZE',          25 * 1024 * 1024);  // 25 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime']);

define('POSTS_PER_PAGE',    10);
define('USERS_PER_PAGE',    20);
define('MESSAGES_PER_PAGE', 30);

define('SESSION_LIFETIME', 7 * 24 * 3600); // 7 дней
