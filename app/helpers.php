<?php

/**
 * Возвращает относительное время: "5 минут назад", "вчера" и т.д.
 */
function timeAgo(?string $datetime): string
{
    if (!$datetime) return '';

    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->getTimestamp() - $past->getTimestamp();

    if ($diff < 60)     return 'только что';
    if ($diff < 3600)   return floor($diff / 60) . ' мин. назад';
    if ($diff < 86400)  return floor($diff / 3600) . ' ч. назад';
    if ($diff < 604800) return floor($diff / 86400) . ' дн. назад';

    return $past->format('d.m.Y');
}

/**
 * Экранирование HTML
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * URL до аватара пользователя
 */
function avatarUrl(?string $filename): string
{
    if ($filename) return BASE_URL . '/uploads/avatars/' . $filename;
    return BASE_URL . '/img/default-avatar.png';
}

/**
 * Обрезка текста
 */
function truncate(string $text, int $len = 100): string
{
    if (mb_strlen($text) <= $len) return $text;
    return mb_substr($text, 0, $len) . '…';
}

/**
 * Пагинация
 */
function paginate(int $page, int $total, int $perPage, string $url): string
{
    $pages = (int)ceil($total / $perPage);
    if ($pages <= 1) return '';

    $html = '<nav><ul class="pagination pagination-sm justify-content-center">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i === $page ? ' active' : '';
        $html  .= "<li class=\"page-item{$active}\">
                     <a class=\"page-link\" href=\"{$url}?page={$i}\">{$i}</a>
                   </li>";
    }
    $html .= '</ul></nav>';
    return $html;
}
