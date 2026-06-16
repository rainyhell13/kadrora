<?php

class GroupController extends Controller
{
    private Group $groupModel;
    private User  $userModel;

    public function __construct()
    {
        $this->groupModel = new Group();
        $this->userModel  = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid    = $this->currentUserId();
        $tab    = $_GET['tab'] ?? 'all';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * 20;

        $groups = $tab === 'my'
            ? $this->groupModel->getUserGroups($uid)
            : $this->groupModel->getAll($uid, 20, $offset);

        $this->view('groups/index', [
            'groups' => $groups,
            'tab'    => $tab,
            'me'     => $this->userModel->findById($uid),
            'page'   => $page,
            'csrf'   => $this->csrf(),
            'flash'  => $this->getFlash(),
        ]);
    }

    public function show(string $slug): void
    {
        $this->requireAuth();
        $uid   = $this->currentUserId();
        $group = $this->groupModel->findBySlug($slug);
        if (!$group) { http_response_code(404); $this->view('errors/404', [], 'main'); return; }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * POSTS_PER_PAGE;
        $role    = $this->groupModel->getRole($group['id'], $uid);
        $isAdmin = in_array($role, ['admin','moderator']);
        $isMember= $role !== null;

        // Приватное сообщество: контент виден только участникам
        $isPrivate = $group['privacy'] === 'private';
        $canView   = !$isPrivate || $isMember;

        $posts    = $canView ? $this->groupModel->getPosts($group['id'], $uid, POSTS_PER_PAGE, $offset) : [];
        $members  = $this->groupModel->getMembers($group['id'], 6);
        $requests = $isAdmin ? $this->groupModel->getJoinRequests($group['id']) : [];

        $this->view('groups/show', [
            'group'      => $group,
            'posts'      => $posts,
            'members'    => $members,
            'requests'   => $requests,
            'role'       => $role,
            'isAdmin'    => $isAdmin,
            'isMember'   => $isMember,
            'isPrivate'  => $isPrivate,
            'canView'    => $canView,
            'hasRequest' => !$isMember && $this->groupModel->hasPendingRequest($group['id'], $uid),
            'me'         => $this->userModel->findById($uid),
            'page'       => $page,
            'csrf'       => $this->csrf(),
            'flash'      => $this->getFlash(),
        ]);
    }

    public function createPage(): void
    {
        $this->requireAuth();
        $this->view('groups/create', [
            'me'   => $this->userModel->findById($this->currentUserId()),
            'csrf' => $this->csrf(),
            'flash'=> $this->getFlash(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid  = $this->currentUserId();
        $name = trim($_POST['name'] ?? '');
        if (mb_strlen($name) < 3) {
            $this->flash('error', 'Название минимум 3 символа');
            $this->redirect('/groups/create');
            return;
        }
        $data = [
            'name'        => $name,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'privacy'     => $_POST['privacy'] ?? 'public',
        ];
        $id    = $this->groupModel->create($uid, $data);
        $group = $this->groupModel->findById($id);
        $this->flash('success', 'Сообщество создано!');
        $this->redirect('/groups/' . $group['slug']);
    }

    public function editPage(string $slug): void
    {
        $this->requireAuth();
        $uid   = $this->currentUserId();
        $group = $this->groupModel->findBySlug($slug);
        if (!$group || $group['owner_id'] !== $uid) { $this->redirect('/groups'); return; }

        $this->view('groups/edit', [
            'group' => $group,
            'me'    => $this->userModel->findById($uid),
            'csrf'  => $this->csrf(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function edit(string $slug): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid   = $this->currentUserId();
        $group = $this->groupModel->findBySlug($slug);
        if (!$group || $group['owner_id'] !== $uid) { $this->redirect('/groups'); return; }

        $data = [
            'name'        => trim($_POST['name']        ?? $group['name']),
            'description' => trim($_POST['description'] ?? '') ?: null,
            'privacy'     => $_POST['privacy'] ?? $group['privacy'],
        ];

        if (!empty($_FILES['avatar']['name'])) {
            try { $data['avatar'] = $this->uploadImage('avatar', AVATAR_UPLOAD_PATH); }
            catch (RuntimeException $e) { $this->flash('error', $e->getMessage()); $this->redirect('/groups/'.$slug.'/edit'); return; }
        }
        if (!empty($_FILES['cover']['name'])) {
            try { $data['cover'] = $this->uploadImage('cover', PHOTO_UPLOAD_PATH); }
            catch (RuntimeException $e) { $this->flash('error', $e->getMessage()); $this->redirect('/groups/'.$slug.'/edit'); return; }
        }

        $this->groupModel->update($group['id'], $data);
        $this->flash('success', 'Сообщество обновлено');
        $this->redirect('/groups/' . $group['slug']);
    }

    public function delete(string $slug): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid   = $this->currentUserId();
        $group = $this->groupModel->findBySlug($slug);
        if ($group && $group['owner_id'] === $uid) {
            $this->groupModel->delete($group['id'], $uid);
            $this->flash('success', 'Сообщество удалено');
        }
        $this->redirect('/groups');
    }

    public function join(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid     = $this->currentUserId();
        $groupId = (int)($_POST['group_id'] ?? 0);
        $group   = $this->groupModel->findById($groupId);
        if (!$group) { $this->json(['error' => 'Сообщество не найдено'], 404); return; }

        if ($group['privacy'] === 'private') {
            // Приватное — отправляем заявку администратору
            $this->groupModel->requestJoin($groupId, $uid);
            $me = $this->userModel->findById($uid);
            (new Notification())->create(
                $group['owner_id'], $uid, 'group_request',
                "{$me['first_name']} {$me['last_name']} хочет вступить в сообщество «{$group['name']}»",
                $groupId, 'group'
            );
            $this->json(['success' => true, 'status' => 'requested']);
            return;
        }

        $this->groupModel->join($groupId, $uid);
        $group = $this->groupModel->findById($groupId);
        $this->json(['success' => true, 'status' => 'joined', 'members' => $group['members_count']]);
    }

    public function cancelRequest(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid     = $this->currentUserId();
        $groupId = (int)($_POST['group_id'] ?? 0);
        $this->groupModel->cancelRequest($groupId, $uid);
        $this->json(['success' => true, 'status' => 'none']);
    }

    public function acceptRequest(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid     = $this->currentUserId();
        $groupId = (int)($_POST['group_id'] ?? 0);
        $target  = (int)($_POST['user_id'] ?? 0);
        if (!in_array($this->groupModel->getRole($groupId, $uid), ['admin','moderator'])) {
            $this->json(['error' => 'Нет прав'], 403); return;
        }
        if ($this->groupModel->acceptRequest($groupId, $target)) {
            $group = $this->groupModel->findById($groupId);
            (new Notification())->create(
                $target, $uid, 'group_accept',
                "Ваша заявка в сообщество «{$group['name']}» принята",
                $groupId, 'group'
            );
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Заявка не найдена'], 404);
        }
    }

    public function declineRequest(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid     = $this->currentUserId();
        $groupId = (int)($_POST['group_id'] ?? 0);
        $target  = (int)($_POST['user_id'] ?? 0);
        if (!in_array($this->groupModel->getRole($groupId, $uid), ['admin','moderator'])) {
            $this->json(['error' => 'Нет прав'], 403); return;
        }
        $this->groupModel->declineRequest($groupId, $target);
        $this->json(['success' => true]);
    }

    public function leave(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid     = $this->currentUserId();
        $groupId = (int)($_POST['group_id'] ?? 0);
        $this->groupModel->leave($groupId, $uid);
        $group = $this->groupModel->findById($groupId);
        $this->json(['success' => true, 'status' => 'none', 'members' => $group['members_count']]);
    }

    public function createPost(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid     = $this->currentUserId();
        $groupId = (int)($_POST['group_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        $group = $this->groupModel->findById($groupId);
        if (!$group || !$this->groupModel->isMember($groupId, $uid)) {
            $this->json(['error' => 'Нет доступа'], 403); return;
        }
        if (!$content && empty($_FILES['image']['name'])) {
            $this->json(['error' => 'Пустой пост'], 422); return;
        }
        // Модерация
        if ($this->isMuted()) { $this->json(['error' => 'Вам временно запрещена публикация'], 403); return; }
        if ($content !== '') {
            $hit = $this->moderateText($content);
            if ($hit && $hit['action'] === 'block') { $this->json(['error' => 'Запись содержит недопустимое содержимое'], 422); return; }
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            try { $image = $this->uploadImage('image', PHOTO_UPLOAD_PATH); }
            catch (RuntimeException $e) { $this->json(['error' => $e->getMessage()], 422); return; }
        }

        $postId = $this->groupModel->createPost($groupId, $uid, $content, $image);
        $posts  = $this->groupModel->getPosts($groupId, $uid, 1, 0);
        $post   = $posts[0] ?? null;

        $html = $this->renderView('groups/partials/group_post_card', [
            'post'    => $post,
            'group'   => $group,
            'uid'     => $uid,
            'isAdmin' => in_array($this->groupModel->getRole($groupId, $uid), ['admin','moderator']),
            'csrf'    => $this->csrf(),
        ]);
        $this->json(['success' => true, 'html' => $html]);
    }

    public function deletePost(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $postId = (int)($_POST['post_id'] ?? 0);
        if ($this->groupModel->deletePost($postId, $uid)) {
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Нет прав'], 403);
        }
    }

    public function likePost(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $postId = (int)($_POST['post_id'] ?? 0);
        $liked  = (bool)$this->db()->query("SELECT 1 FROM group_post_likes WHERE post_id=$postId AND user_id=$uid")->fetch();
        if ($liked) $this->groupModel->unlikePost($postId, $uid);
        else        $this->groupModel->likePost($postId, $uid);
        $this->json(['success'=>true,'liked'=>!$liked,'count'=>$this->groupModel->getPostLikesCount($postId)]);
    }

    private function db(): PDO { return Database::getConnection(); }
}
