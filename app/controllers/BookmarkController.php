<?php

class BookmarkController extends Controller
{
    private Bookmark     $bookmarkModel;
    private UserBookmark $userBookmarkModel;
    private User         $userModel;

    public function __construct()
    {
        $this->bookmarkModel     = new Bookmark();
        $this->userBookmarkModel = new UserBookmark();
        $this->userModel         = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid    = $this->currentUserId();
        $tab    = ($_GET['tab'] ?? 'posts') === 'people' ? 'people' : 'posts';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * POSTS_PER_PAGE;

        $posts = $tab === 'posts' ? $this->bookmarkModel->getUserBookmarks($uid, POSTS_PER_PAGE, $offset) : [];
        $users = $tab === 'people' ? $this->userBookmarkModel->getBookmarkedUsers($uid) : [];

        $this->view('bookmarks/index', [
            'posts'       => $posts,
            'users'       => $users,
            'tab'         => $tab,
            'postsCount'  => $this->bookmarkModel->count($uid),
            'peopleCount' => $this->userBookmarkModel->count($uid),
            'me'          => $this->userModel->findById($uid),
            'page'        => $page,
            'csrf'        => $this->csrf(),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function toggle(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $postId = (int)($_POST['post_id'] ?? 0);

        if (!(new Post())->findById($postId)) {
            $this->json(['error' => 'Запись не найдена'], 404);
            return;
        }

        $saved = $this->bookmarkModel->isBookmarked($uid, $postId);
        if ($saved) $this->bookmarkModel->remove($uid, $postId);
        else        $this->bookmarkModel->add($uid, $postId);

        $this->json(['success' => true, 'bookmarked' => !$saved]);
    }

    public function togglePerson(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid    = $this->currentUserId();
        $target = (int)($_POST['user_id'] ?? 0);

        if ($target === $uid || !$this->userModel->findById($target)) {
            $this->json(['error' => 'Неверный пользователь'], 422);
            return;
        }

        $saved = $this->userBookmarkModel->isBookmarked($uid, $target);
        if ($saved) $this->userBookmarkModel->remove($uid, $target);
        else        $this->userBookmarkModel->add($uid, $target);

        $this->json(['success' => true, 'bookmarked' => !$saved]);
    }
}
