<?php

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        extract($data);
        if ($layout) {
            $content = $this->renderView($view, $data);
            include BASE_PATH . "/app/views/layouts/{$layout}.php";
        } else {
            include BASE_PATH . "/app/views/{$view}.php";
        }
    }

    protected function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        include BASE_PATH . "/app/views/{$view}.php";
        return ob_get_clean();
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Unauthorized'], 401);
            }
            $this->redirect('/login');
        }
    }

    protected function currentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    protected function currentUserRole(): string
    {
        if (!isset($_SESSION['user_id'])) return 'guest';
        return (new User())->getRole($_SESSION['user_id']);
    }

    protected function isStaff(): bool
    {
        return in_array($this->currentUserRole(), ['moderator', 'admin'], true);
    }

    /** Доступ только модераторам и администраторам */
    protected function requireStaff(): void
    {
        $this->requireAuth();
        if (!$this->isStaff()) {
            if ($this->isAjax()) $this->json(['error' => 'Доступ запрещён'], 403);
            http_response_code(403);
            $this->view('errors/403', [], 'main');
            exit;
        }
    }

    /** Доступ только администраторам */
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if ($this->currentUserRole() !== 'admin') {
            if ($this->isAjax()) $this->json(['error' => 'Только для администраторов'], 403);
            http_response_code(403);
            $this->view('errors/403', [], 'main');
            exit;
        }
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function csrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->json(['error' => 'CSRF token mismatch'], 403);
        }
    }

    protected function uploadImage(string $inputName, string $dir): ?string
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$inputName];

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new RuntimeException('Файл слишком большой (максимум 10 МБ)');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
            throw new RuntimeException('Недопустимый тип файла');
        }

        $ext      = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        };
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest     = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Ошибка сохранения файла');
        }

        return $filename;
    }

    /**
     * Загрузка медиафайла (аудио/видео) с проверкой MIME-типа и размера.
     */
    protected function uploadMedia(string $inputName, string $dir, array $allowedTypes, int $maxSize): ?string
    {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$inputName];

        if ($file['size'] > $maxSize) {
            throw new RuntimeException('Файл слишком большой (максимум ' . round($maxSize / 1048576) . ' МБ)');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedTypes, true)) {
            throw new RuntimeException('Недопустимый тип файла (' . $mime . ')');
        }

        $extMap = [
            'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3', 'audio/wav' => 'wav',
            'audio/ogg' => 'ogg', 'audio/x-m4a' => 'm4a', 'audio/mp4' => 'm4a',
            'video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov',
        ];
        $ext      = $extMap[$mime] ?? 'bin';
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            throw new RuntimeException('Ошибка сохранения файла');
        }

        return $filename;
    }

    /** Заблокирован ли текущий пользователь на публикацию (mute) */
    protected function isMuted(): bool
    {
        $u = (new User())->findById($this->currentUserId());
        return $u && (new User())->isCurrentlyMuted($u);
    }

    /**
     * Проверка текста автофильтром стоп-слов.
     * Возвращает ['action'=>'block'|'flag','word'=>...] либо null.
     */
    protected function moderateText(string $text): ?array
    {
        return (new BannedWord())->check($text);
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}
