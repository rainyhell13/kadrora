<?php

class VideoController extends Controller
{
    private Video $videoModel;
    private User  $userModel;

    public function __construct()
    {
        $this->videoModel = new Video();
        $this->userModel  = new User();
    }

    public function index(string $username): void
    {
        $this->requireAuth();
        $uid  = $this->currentUserId();
        $user = $this->userModel->findByUsername($username);
        if (!$user) { http_response_code(404); $this->view('errors/404', [], 'main'); return; }

        $this->view('media/video', [
            'profile' => $user,
            'videos'  => $this->videoModel->getUserVideos($user['id']),
            'isOwner' => $uid === $user['id'],
            'me'      => $this->userModel->findById($uid),
            'csrf'    => $this->csrf(),
            'flash'   => $this->getFlash(),
        ]);
    }

    public function upload(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid   = $this->currentUserId();
        $title = trim($_POST['title'] ?? '');

        if ($title === '') {
            $this->flash('error', 'Укажите название видео');
            $this->redirect('/video/' . $_SESSION['username']);
            return;
        }

        try {
            $filename = $this->uploadMedia('video', VIDEO_UPLOAD_PATH, ALLOWED_VIDEO_TYPES, MAX_VIDEO_SIZE);
            if (!$filename) {
                $this->flash('error', 'Выберите видеофайл');
                $this->redirect('/video/' . $_SESSION['username']);
                return;
            }
            $this->videoModel->add($uid, $title, $filename);
            $this->flash('success', 'Видеозапись добавлена');
        } catch (RuntimeException $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/video/' . $_SESSION['username']);
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid = $this->currentUserId();
        $id  = (int)($_POST['video_id'] ?? 0);
        $filename = $this->videoModel->delete($id, $uid);
        if ($filename) {
            @unlink(VIDEO_UPLOAD_PATH . '/' . $filename);
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Нет прав'], 403);
        }
    }
}
