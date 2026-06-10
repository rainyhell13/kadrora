<?php

class ThemeController extends Controller
{
    public function set(): void
    {
        $this->verifyCsrf();
        $theme = ($_POST['theme'] ?? 'dark') === 'light' ? 'light' : 'dark';
        // Cookie на 1 год, доступна всему приложению
        setcookie('kadrora_theme', $theme, [
            'expires'  => time() + 365 * 24 * 3600,
            'path'     => '/',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $this->json(['success' => true, 'theme' => $theme]);
    }
}
