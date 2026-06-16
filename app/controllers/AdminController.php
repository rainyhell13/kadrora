<?php

class AdminController extends Controller
{
    private User          $userModel;
    private Report        $reportModel;
    private ModerationLog $logModel;
    private BannedWord    $wordModel;

    public function __construct()
    {
        $this->userModel   = new User();
        $this->reportModel = new Report();
        $this->logModel    = new ModerationLog();
        $this->wordModel   = new BannedWord();
    }

    // ---------- ДАШБОРД ----------
    public function dashboard(): void
    {
        $this->requireStaff();
        $uid = $this->currentUserId();
        $this->view('admin/dashboard', [
            'stats'      => $this->userModel->globalStats(),
            'pending'    => $this->reportModel->pendingCount(),
            'modToday'   => $this->logModel->countToday(),
            'byCategory' => $this->reportModel->statsByCategory(),
            'me'         => $this->userModel->findById($uid),
            'role'       => $this->currentUserRole(),
            'csrf'       => $this->csrf(),
            'flash'      => $this->getFlash(),
        ]);
    }

    // ---------- ЖАЛОБЫ ----------
    public function reports(): void
    {
        $this->requireStaff();
        $queue = $this->reportModel->getQueue(60);
        foreach ($queue as &$row) {
            $row['preview'] = $this->previewTarget($row['target_type'], (int)$row['target_id']);
        }
        unset($row);
        $this->view('admin/reports', [
            'queue'   => $queue,
            'pending' => $this->reportModel->pendingCount(),
            'me'      => $this->userModel->findById($this->currentUserId()),
            'role'    => $this->currentUserRole(),
            'csrf'    => $this->csrf(),
            'flash'   => $this->getFlash(),
        ]);
    }

    /** Действие по жалобе: hide | remove | dismiss + при необходимости предупреждение/бан автора */
    public function resolveReport(): void
    {
        $this->requireStaff();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $type   = $_POST['target_type'] ?? '';
        $id     = (int)($_POST['target_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if (!in_array($type, ['post','comment','user','group','message'], true) || $id < 1) {
            $this->json(['error' => 'Некорректные данные'], 422);
            return;
        }

        $db = Database::getConnection();

        if ($action === 'hide' || $action === 'remove') {
            $status = $action === 'hide' ? 'hidden' : 'removed';
            if ($type === 'post')    $db->prepare('UPDATE posts SET status = ? WHERE id = ?')->execute([$status, $id]);
            if ($type === 'comment') $db->prepare('UPDATE comments SET status = ? WHERE id = ?')->execute([$status, $id]);
            $this->reportModel->resolveTarget($type, $id, $uid, 'resolved');
            $this->logModel->add($uid, $action . '_' . $type, $type, $id, 'По жалобе');
        } elseif ($action === 'dismiss') {
            $this->reportModel->resolveTarget($type, $id, $uid, 'rejected');
            $this->logModel->add($uid, 'dismiss_reports', $type, $id, 'Жалобы отклонены');
        } else {
            $this->json(['error' => 'Неизвестное действие'], 422);
            return;
        }
        $this->json(['success' => true]);
    }

    /** Массовое действие по выбранным жалобам */
    public function bulkResolve(): void
    {
        $this->requireStaff();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $action = $_POST['action'] ?? '';
        $items  = json_decode($_POST['items'] ?? '[]', true);
        if (!is_array($items) || empty($items)) { $this->json(['error' => 'Ничего не выбрано'], 422); return; }
        if (!in_array($action, ['hide','remove','dismiss'], true)) { $this->json(['error' => 'Неверное действие'], 422); return; }

        $db = Database::getConnection();
        $done = 0;
        foreach ($items as $it) {
            $type = $it['type'] ?? ''; $id = (int)($it['id'] ?? 0);
            if (!in_array($type, ['post','comment','user','group','message'], true) || $id < 1) continue;
            if ($action === 'dismiss') {
                $this->reportModel->resolveTarget($type, $id, $uid, 'rejected');
            } else {
                $status = $action === 'hide' ? 'hidden' : 'removed';
                if ($type === 'post')    $db->prepare('UPDATE posts SET status=? WHERE id=?')->execute([$status, $id]);
                if ($type === 'comment') $db->prepare('UPDATE comments SET status=? WHERE id=?')->execute([$status, $id]);
                $this->reportModel->resolveTarget($type, $id, $uid, 'resolved');
            }
            $done++;
        }
        $this->logModel->add($uid, 'bulk_' . $action, null, null, "обработано: $done");
        $this->json(['success' => true, 'count' => $done]);
    }

    // ---------- ПОЛЬЗОВАТЕЛИ ----------
    public function users(): void
    {
        $this->requireStaff();
        $q      = trim($_GET['q'] ?? '');
        $filter = $_GET['filter'] ?? 'all';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $list   = $this->userModel->adminList($q, $filter, 30, ($page - 1) * 30);

        $this->view('admin/users', [
            'list'    => $list,
            'q'       => $q,
            'filter'  => $filter,
            'page'    => $page,
            'role'    => $this->currentUserRole(),
            'me'      => $this->userModel->findById($this->currentUserId()),
            'pending' => $this->reportModel->pendingCount(),
            'csrf'    => $this->csrf(),
            'flash'   => $this->getFlash(),
        ]);
    }

    public function userAction(): void
    {
        $this->requireStaff();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $target = (int)($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $reason = trim($_POST['reason'] ?? '') ?: null;

        $user = $this->userModel->findById($target);
        if (!$user) { $this->json(['error' => 'Пользователь не найден'], 404); return; }
        // нельзя модерировать администраторов и самого себя
        if ($user['role'] === 'admin' || $target === $uid) {
            $this->json(['error' => 'Действие недоступно для этого пользователя'], 403);
            return;
        }
        // смена ролей — только администратор
        if ($action === 'role' && $this->currentUserRole() !== 'admin') {
            $this->json(['error' => 'Смена ролей доступна только администратору'], 403);
            return;
        }

        switch ($action) {
            case 'ban':
                $days = (int)($_POST['days'] ?? 0);
                $until = $days > 0 ? date('Y-m-d H:i:s', time() + $days * 86400) : null;
                $this->userModel->ban($target, $reason, $until);
                $this->userModel->setOnline($target, false);
                $this->logModel->add($uid, 'ban', 'user', $target, $days > 0 ? "на $days дн.: $reason" : "перманентно: $reason");
                break;
            case 'unban':
                $this->userModel->unban($target);
                $this->logModel->add($uid, 'unban', 'user', $target, null);
                break;
            case 'mute':
                $days = (int)($_POST['days'] ?? 0);
                $until = $days > 0 ? date('Y-m-d H:i:s', time() + $days * 86400) : null;
                $this->userModel->mute($target, $until);
                $this->logModel->add($uid, 'mute', 'user', $target, $days > 0 ? "на $days дн." : 'бессрочно');
                break;
            case 'unmute':
                $this->userModel->unmute($target);
                $this->logModel->add($uid, 'unmute', 'user', $target, null);
                break;
            case 'warn':
                $count = $this->userModel->addWarning($target);
                $detail = "предупреждение #$count" . ($reason ? ": $reason" : '');
                // авто-эскалация: 3 предупреждения → мьют на 3 дня
                if ($count >= 3) {
                    $this->userModel->mute($target, date('Y-m-d H:i:s', time() + 3 * 86400));
                    $detail .= ' (авто-мьют на 3 дня)';
                }
                (new Notification())->create($target, $uid, 'warning',
                    'Вы получили предупреждение от модерации' . ($reason ? ': ' . $reason : ''), null, null);
                $this->logModel->add($uid, 'warn', 'user', $target, $detail);
                break;
            case 'role':
                $role = $_POST['role'] ?? 'user';
                $this->userModel->setRole($target, $role);
                $this->logModel->add($uid, 'set_role', 'user', $target, "роль: $role");
                break;
            case 'verify':
                $val = ($_POST['value'] ?? '1') === '1';
                $this->userModel->setVerified($target, $val);
                $this->logModel->add($uid, $val ? 'verify' : 'unverify', 'user', $target, null);
                break;
            default:
                $this->json(['error' => 'Неизвестное действие'], 422);
                return;
        }
        $this->json(['success' => true]);
    }

    // ---------- КОНТЕНТ ----------
    public function content(): void
    {
        $this->requireStaff();
        $db = Database::getConnection();
        $tab = $_GET['tab'] ?? 'flagged';
        $where = $tab === 'flagged' ? "WHERE p.status IN ('flagged','hidden')" : '';
        $stmt = $db->query(
            "SELECT p.*, u.username, u.first_name, u.last_name
             FROM posts p JOIN users u ON u.id = p.user_id
             $where ORDER BY p.created_at DESC LIMIT 50"
        );
        $this->view('admin/content', [
            'posts'   => $stmt->fetchAll(),
            'tab'     => $tab,
            'role'    => $this->currentUserRole(),
            'me'      => $this->userModel->findById($this->currentUserId()),
            'pending' => $this->reportModel->pendingCount(),
            'csrf'    => $this->csrf(),
            'flash'   => $this->getFlash(),
        ]);
    }

    public function contentAction(): void
    {
        $this->requireStaff();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $id     = (int)($_POST['post_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['active','hidden','removed'], true)) { $this->json(['error' => 'Неверный статус'], 422); return; }
        Database::getConnection()->prepare('UPDATE posts SET status = ? WHERE id = ?')->execute([$status, $id]);
        $this->reportModel->resolveTarget('post', $id, $uid, 'resolved');
        $this->logModel->add($uid, 'set_status_' . $status, 'post', $id, null);
        $this->json(['success' => true]);
    }

    // ---------- СТОП-СЛОВА ----------
    public function words(): void
    {
        $this->requireStaff();
        $this->view('admin/words', [
            'words'   => $this->wordModel->all(),
            'role'    => $this->currentUserRole(),
            'me'      => $this->userModel->findById($this->currentUserId()),
            'pending' => $this->reportModel->pendingCount(),
            'csrf'    => $this->csrf(),
            'flash'   => $this->getFlash(),
        ]);
    }

    public function wordAdd(): void
    {
        $this->requireStaff();
        $this->verifyCsrf();
        $word   = trim($_POST['word'] ?? '');
        $action = ($_POST['action'] ?? 'block') === 'flag' ? 'flag' : 'block';
        if ($this->wordModel->add($word, $action)) {
            $this->logModel->add($this->currentUserId(), 'add_word', null, null, "$word ($action)");
            $this->flash('success', 'Стоп-слово добавлено');
        } else {
            $this->flash('error', 'Не удалось добавить (пусто или уже есть)');
        }
        $this->redirect('/admin/words');
    }

    public function wordRemove(): void
    {
        $this->requireStaff();
        $this->verifyCsrf();
        $this->wordModel->remove((int)($_POST['id'] ?? 0));
        $this->logModel->add($this->currentUserId(), 'remove_word', null, null, null);
        $this->json(['success' => true]);
    }

    // ---------- ЖУРНАЛ ----------
    public function log(): void
    {
        $this->requireStaff();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $this->view('admin/log', [
            'entries' => $this->logModel->recent(80, ($page - 1) * 80),
            'page'    => $page,
            'role'    => $this->currentUserRole(),
            'me'      => $this->userModel->findById($this->currentUserId()),
            'pending' => $this->reportModel->pendingCount(),
            'csrf'    => $this->csrf(),
            'flash'   => $this->getFlash(),
        ]);
    }

    // ---------- preview объекта жалобы ----------
    private function previewTarget(string $type, int $id): array
    {
        $db = Database::getConnection();
        if ($type === 'post') {
            $s = $db->prepare("SELECT p.content, p.status, u.username, u.first_name, u.last_name, u.id AS uid FROM posts p JOIN users u ON u.id=p.user_id WHERE p.id=?");
            $s->execute([$id]); $r = $s->fetch();
            if (!$r) return ['exists' => false];
            return ['exists' => true, 'text' => mb_substr($r['content'] ?? '', 0, 200), 'author' => $r['first_name'].' '.$r['last_name'], 'username' => $r['username'], 'authorId' => $r['uid'], 'status' => $r['status'], 'link' => BASE_URL . '/feed#post-' . $id];
        }
        if ($type === 'comment') {
            $s = $db->prepare("SELECT c.content, c.status, u.username, u.first_name, u.last_name, u.id AS uid FROM comments c JOIN users u ON u.id=c.user_id WHERE c.id=?");
            $s->execute([$id]); $r = $s->fetch();
            if (!$r) return ['exists' => false];
            return ['exists' => true, 'text' => mb_substr($r['content'] ?? '', 0, 200), 'author' => $r['first_name'].' '.$r['last_name'], 'username' => $r['username'], 'authorId' => $r['uid'], 'status' => $r['status'], 'link' => '#'];
        }
        if ($type === 'user') {
            $s = $db->prepare("SELECT username, first_name, last_name, bio FROM users WHERE id=?");
            $s->execute([$id]); $r = $s->fetch();
            if (!$r) return ['exists' => false];
            return ['exists' => true, 'text' => $r['bio'] ?? '', 'author' => $r['first_name'].' '.$r['last_name'], 'username' => $r['username'], 'authorId' => $id, 'link' => BASE_URL . '/profile/' . $r['username']];
        }
        if ($type === 'group') {
            $s = $db->prepare("SELECT name, slug, description FROM groups WHERE id=?");
            $s->execute([$id]); $r = $s->fetch();
            if (!$r) return ['exists' => false];
            return ['exists' => true, 'text' => $r['description'] ?? '', 'author' => $r['name'], 'link' => BASE_URL . '/groups/' . $r['slug']];
        }
        return ['exists' => false];
    }
}
