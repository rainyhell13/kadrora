<?php

class ProfileController extends Controller
{
    private User       $userModel;
    private Post       $postModel;
    private Friendship $friendModel;
    private Photo      $photoModel;

    public function __construct()
    {
        $this->userModel   = new User();
        $this->postModel   = new Post();
        $this->friendModel = new Friendship();
        $this->photoModel  = new Photo();
    }

    public function show(string $username): void
    {
        $this->requireAuth();
        $uid  = $this->currentUserId();
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            http_response_code(404);
            $this->view('errors/404', [], 'main');
            return;
        }

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * POSTS_PER_PAGE;

        $isOwner  = $uid === $user['id'];
        $isFriend = $this->friendModel->isFriend($uid, $user['id']);
        // viewer удовлетворяет уровню приватности ('all' | 'friends')
        $meets = fn(string $level) => $isOwner || $level === 'all' || ($level === 'friends' && $isFriend);

        $canViewProfile = $meets($user['privacy_profile'] ?? 'all');
        $canViewFriends = $canViewProfile && $meets($user['privacy_friends'] ?? 'all');
        $canViewPhotos  = $canViewProfile && $meets($user['privacy_photos']  ?? 'all');

        // Кто может писать на стену (privacy_wall: all | friends | nobody)
        $wallLevel     = $user['privacy_wall'] ?? 'friends';
        $canPostToWall = $isOwner || ($wallLevel === 'all') || ($wallLevel === 'friends' && $isFriend);

        $posts        = $canViewProfile ? $this->postModel->getUserPosts($user['id'], $uid, POSTS_PER_PAGE, $offset) : [];
        $photos       = $canViewPhotos  ? $this->photoModel->getUserPhotos($user['id'], 9) : [];
        $stats        = $this->userModel->getStats($user['id']);
        $friendStatus = $this->friendModel->getStatus($uid, $user['id']);
        $mutualCount  = (!$isOwner) ? $this->friendModel->getMutualFriendsCount($uid, $user['id']) : 0;
        $friends      = $canViewFriends ? $this->userModel->getFriends($user['id']) : [];

        $this->view('profile/show', [
            'profile'        => $user,
            'canViewProfile' => $canViewProfile,
            'canViewFriends' => $canViewFriends,
            'canViewPhotos'  => $canViewPhotos,
            'posts'        => $posts,
            'photos'       => $photos,
            'stats'        => $stats,
            'friendStatus' => $friendStatus,
            'mutualCount'  => $mutualCount,
            'friends'      => array_slice($friends, 0, 6),
            'isOwner'      => $uid === $user['id'],
            'canPostToWall'=> $canPostToWall,
            'isBookmarked' => ($uid !== $user['id']) && (new UserBookmark())->isBookmarked($uid, $user['id']),
            'videoCount'   => (new Video())->count($user['id']),
            'me'           => $this->userModel->findById($uid),
            'page'         => $page,
            'csrf'         => $this->csrf(),
            'flash'        => $this->getFlash(),
        ]);
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $status = trim($_POST['status'] ?? '');
        $status = mb_substr($status, 0, 255) ?: null;
        $this->userModel->updateStatus($uid, $status);
        $this->json(['success' => true, 'status' => $status]);
    }

    public function wallPost(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid       = $this->currentUserId();
        $ownerId   = (int)($_POST['owner_id'] ?? 0);
        $content   = trim($_POST['content'] ?? '');

        $owner = $this->userModel->findById($ownerId);
        if (!$owner) { $this->json(['error' => 'Пользователь не найден'], 404); return; }

        // Писать на стену можно себе или друзьям
        if ($ownerId !== $uid && !$this->friendModel->isFriend($uid, $ownerId)) {
            $this->json(['error' => 'Писать на стену можно только друзьям'], 403);
            return;
        }
        if ($content === '' && empty($_FILES['image']['name'])) {
            $this->json(['error' => 'Запись не может быть пустой'], 422);
            return;
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $image = $this->uploadImage('image', PHOTO_UPLOAD_PATH);
                $this->photoModel->add($uid, $image);
            } catch (RuntimeException $e) {
                $this->json(['error' => $e->getMessage()], 422);
                return;
            }
        }

        $postId = $this->postModel->create($uid, $content, $image, 'public', $ownerId);
        $post   = $this->postModel->findById($postId);

        // Уведомление владельцу стены
        if ($ownerId !== $uid) {
            $me = $this->userModel->findById($uid);
            (new Notification())->create(
                $ownerId, $uid, 'wall_post',
                "{$me['first_name']} {$me['last_name']} оставил(а) запись на вашей стене",
                $ownerId, 'user'
            );
        }

        $html = $this->renderView('feed/partials/post_card', [
            'post' => $post,
            'uid'  => $uid,
            'csrf' => $this->csrf(),
        ]);
        $this->json(['success' => true, 'html' => $html]);
    }

    public function editPage(): void
    {
        $this->requireAuth();
        $uid = $this->currentUserId();
        $me  = $this->userModel->findById($uid);

        $this->view('profile/edit', [
            'me'   => $me,
            'csrf' => $this->csrf(),
            'flash' => $this->getFlash(),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = $this->currentUserId();

        $data = [
            'first_name'   => trim($_POST['first_name'] ?? ''),
            'last_name'    => trim($_POST['last_name']  ?? ''),
            'bio'          => trim($_POST['bio']        ?? '') ?: null,
            'city'         => trim($_POST['city']       ?? '') ?: null,
            'birth_date'   => $_POST['birth_date']      ?? null ?: null,
            'gender'       => $_POST['gender']          ?? null,
            'website'      => trim($_POST['website']    ?? '') ?: null,
            // Расширенная анкета
            'relationship' => $_POST['relationship']    ?? null ?: null,
            'interests'    => trim($_POST['interests']  ?? '') ?: null,
            'fav_music'    => trim($_POST['fav_music']  ?? '') ?: null,
            'fav_films'    => trim($_POST['fav_films']  ?? '') ?: null,
            'fav_books'    => trim($_POST['fav_books']  ?? '') ?: null,
            'fav_games'    => trim($_POST['fav_games']  ?? '') ?: null,
            'fav_quotes'   => trim($_POST['fav_quotes'] ?? '') ?: null,
            'activities'   => trim($_POST['activities'] ?? '') ?: null,
            'life_main'    => $_POST['life_main']        ?? null ?: null,
            'people_main'  => $_POST['people_main']      ?? null ?: null,
        ];

        if (mb_strlen($data['first_name']) < 2 || mb_strlen($data['last_name']) < 2) {
            $this->flash('error', 'Имя и фамилия обязательны');
            $this->redirect('/profile/edit');
            return;
        }

        // Загрузка аватара
        if (!empty($_FILES['avatar']['name'])) {
            try {
                $filename    = $this->uploadImage('avatar', AVATAR_UPLOAD_PATH);
                $data['avatar'] = $filename;
            } catch (RuntimeException $e) {
                $this->flash('error', $e->getMessage());
                $this->redirect('/profile/edit');
                return;
            }
        }

        // Загрузка обложки
        if (!empty($_FILES['cover_photo']['name'])) {
            try {
                $filename           = $this->uploadImage('cover_photo', PHOTO_UPLOAD_PATH);
                $data['cover_photo'] = $filename;
            } catch (RuntimeException $e) {
                $this->flash('error', $e->getMessage());
                $this->redirect('/profile/edit');
                return;
            }
        }

        $this->userModel->update($uid, $data);
        $this->flash('success', 'Профиль обновлён');
        $this->redirect('/profile/' . $_SESSION['username']);
    }

    public function updateAnketa(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = $this->currentUserId();

        $data = [
            'relationship' => $_POST['relationship']    ?? null ?: null,
            'interests'    => trim($_POST['interests']  ?? '') ?: null,
            'activities'   => trim($_POST['activities'] ?? '') ?: null,
            'fav_music'    => trim($_POST['fav_music']  ?? '') ?: null,
            'fav_films'    => trim($_POST['fav_films']  ?? '') ?: null,
            'fav_books'    => trim($_POST['fav_books']  ?? '') ?: null,
            'fav_games'    => trim($_POST['fav_games']  ?? '') ?: null,
            'fav_quotes'   => trim($_POST['fav_quotes'] ?? '') ?: null,
            'life_main'    => $_POST['life_main']        ?? null ?: null,
            'people_main'  => $_POST['people_main']      ?? null ?: null,
        ];
        $this->userModel->update($uid, $data);
        $this->flash('success', 'Анкета сохранена');
        $this->redirect('/profile/edit');
    }

    public function updatePrivacy(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = $this->currentUserId();

        $prof = in_array($_POST['privacy_profile']  ?? '', ['all','friends'])          ? $_POST['privacy_profile']  : 'all';
        $wall = in_array($_POST['privacy_wall']     ?? '', ['all','friends','nobody']) ? $_POST['privacy_wall']     : 'friends';
        $msg  = in_array($_POST['privacy_messages'] ?? '', ['all','friends'])          ? $_POST['privacy_messages'] : 'all';
        $frnd = in_array($_POST['privacy_friends']  ?? '', ['all','friends'])          ? $_POST['privacy_friends']  : 'all';
        $phot = in_array($_POST['privacy_photos']   ?? '', ['all','friends'])          ? $_POST['privacy_photos']   : 'all';

        $this->userModel->update($uid, [
            'privacy_profile'  => $prof,
            'privacy_wall'     => $wall,
            'privacy_messages' => $msg,
            'privacy_friends'  => $frnd,
            'privacy_photos'   => $phot,
        ]);
        $this->flash('success', 'Настройки приватности сохранены');
        $this->redirect('/profile/edit');
    }

    public function changePassword(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid  = $this->currentUserId();
        $user = $this->userModel->findById($uid);

        $old  = $_POST['old_password'] ?? '';
        $new  = $_POST['new_password'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';

        if (!password_verify($old, $user['password_hash'])) {
            $this->flash('error', 'Неверный текущий пароль');
        } elseif (strlen($new) < 6) {
            $this->flash('error', 'Новый пароль не менее 6 символов');
        } elseif ($new !== $new2) {
            $this->flash('error', 'Пароли не совпадают');
        } else {
            $this->userModel->updatePassword($uid, $new);
            $this->flash('success', 'Пароль изменён');
        }

        $this->redirect('/profile/edit');
    }

    public function photos(string $username): void
    {
        $this->requireAuth();
        $uid  = $this->currentUserId();
        $user = $this->userModel->findByUsername($username);
        if (!$user) { http_response_code(404); return; }

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * USERS_PER_PAGE;
        $photos = $this->photoModel->getUserPhotos($user['id'], USERS_PER_PAGE, $offset);

        $this->view('profile/photos', [
            'profile' => $user,
            'photos'  => $photos,
            'isOwner' => $uid === $user['id'],
            'me'      => $this->userModel->findById($uid),
            'page'    => $page,
            'csrf'    => $this->csrf(),
        ]);
    }

    public function deletePhoto(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid     = $this->currentUserId();
        $photoId = (int)($_POST['photo_id'] ?? 0);

        $filename = (new Photo())->delete($photoId, $uid);
        if ($filename) {
            @unlink(PHOTO_UPLOAD_PATH . '/' . $filename);
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Нет прав'], 403);
        }
    }
}
