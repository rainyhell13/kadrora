<?php

class CommentController extends Controller
{
    private Comment      $commentModel;
    private Post         $postModel;
    private User         $userModel;
    private Notification $notifModel;

    public function __construct()
    {
        $this->commentModel = new Comment();
        $this->postModel    = new Post();
        $this->userModel    = new User();
        $this->notifModel   = new Notification();
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid      = $this->currentUserId();
        $postId   = (int)($_POST['post_id'] ?? 0);
        $content  = trim($_POST['content'] ?? '');
        $parentId = ((int)($_POST['parent_id'] ?? 0)) ?: null;

        if (mb_strlen($content) < 1) {
            $this->json(['error' => 'Комментарий не может быть пустым'], 422);
            return;
        }

        $commentId = $this->commentModel->create($postId, $uid, $content, $parentId);
        $comment   = $this->commentModel->findById($commentId);

        $post = $this->postModel->findById($postId);
        if ($post && $post['user_id'] !== $uid) {
            $me = $this->userModel->findById($uid);
            $this->notifModel->create(
                $post['user_id'], $uid, 'comment',
                "{$me['first_name']} {$me['last_name']} прокомментировал(а) ваш пост",
                $postId, 'post'
            );
        }

        $html = $this->renderView('feed/partials/comment_item', [
            'comment' => $comment,
            'uid'     => $uid,
            'csrf'    => $this->csrf(),
        ]);

        $this->json(['success' => true, 'html' => $html, 'comment_id' => $commentId]);
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid       = $this->currentUserId();
        $commentId = (int)($_POST['comment_id'] ?? 0);

        if ($this->commentModel->delete($commentId, $uid)) {
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Нет прав'], 403);
        }
    }

    public function getByPost(): void
    {
        $this->requireAuth();
        $uid    = $this->currentUserId();
        $postId = (int)($_GET['post_id'] ?? 0);

        $comments = $this->commentModel->getByPost($postId);
        $html     = '';
        foreach ($comments as $comment) {
            $html .= $this->renderView('feed/partials/comment_item', [
                'comment' => $comment,
                'uid'     => $uid,
                'csrf'    => $this->csrf(),
            ]);
        }
        $this->json(['success' => true, 'html' => $html, 'count' => count($comments)]);
    }
}
