<?php

class FeedController extends Controller
{
    private Post         $postModel;
    private User         $userModel;
    private Notification $notifModel;

    public function __construct()
    {
        $this->postModel  = new Post();
        $this->userModel  = new User();
        $this->notifModel = new Notification();
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid     = $this->currentUserId();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * POSTS_PER_PAGE;

        $posts     = $this->postModel->getFeed($uid, POSTS_PER_PAGE, $offset);
        $suggested = $this->userModel->getSuggestedUsers($uid, 5);
        $me        = $this->userModel->findById($uid);

        // «Интересное» — случайные публичные записи (показываем на первой странице,
        // исключая те, что уже есть в основной ленте)
        $discover = [];
        if ($page === 1) {
            $shownIds = array_column($posts, 'id');
            foreach ($this->postModel->getDiscover($uid, 12) as $d) {
                if (!in_array($d['id'], $shownIds, true)) {
                    $discover[] = $d;
                }
                if (count($discover) >= 6) break;
            }
        }

        $this->view('feed/index', [
            'posts'     => $posts,
            'discover'  => $discover,
            'suggested' => $suggested,
            'me'        => $me,
            'page'      => $page,
            'csrf'      => $this->csrf(),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function createPost(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $uid     = $this->currentUserId();
        $content = trim($_POST['content'] ?? '');
        $privacy = $_POST['privacy'] ?? 'public';

        if (!in_array($privacy, ['public','friends','private'])) $privacy = 'public';

        if (mb_strlen($content) < 1 && !isset($_FILES['image'])) {
            $this->json(['error' => 'Пост не может быть пустым'], 422);
            return;
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $image = $this->uploadImage('image', PHOTO_UPLOAD_PATH);
                (new Photo())->add($uid, $image);
            } catch (RuntimeException $e) {
                $this->json(['error' => $e->getMessage()], 422);
                return;
            }
        }

        $postId = $this->postModel->create($uid, $content, $image, $privacy);
        $post   = $this->postModel->findById($postId);

        $html = $this->renderView('feed/partials/post_card', [
            'post' => $post,
            'me'   => $this->userModel->findById($uid),
            'csrf' => $this->csrf(),
        ]);

        $this->json(['success' => true, 'html' => $html, 'post_id' => $postId]);
    }

    public function deletePost(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $postId = (int)($_POST['post_id'] ?? 0);

        if ($this->postModel->delete($postId, $uid)) {
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

        $liked = $this->postModel->isLikedBy($postId, $uid);

        if ($liked) {
            $this->postModel->unlike($postId, $uid);
        } else {
            $this->postModel->like($postId, $uid);
            $post = $this->postModel->findById($postId);
            if ($post && $post['user_id'] !== $uid) {
                $me = $this->userModel->findById($uid);
                $this->notifModel->create(
                    $post['user_id'], $uid, 'post_like',
                    "{$me['first_name']} {$me['last_name']} оценил(а) ваш пост",
                    $postId, 'post'
                );
            }
        }

        $this->json([
            'success' => true,
            'liked'   => !$liked,
            'count'   => $this->postModel->getLikesCount($postId),
        ]);
    }
}
