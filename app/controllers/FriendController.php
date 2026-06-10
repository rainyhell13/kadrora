<?php

class FriendController extends Controller
{
    private Friendship   $friendModel;
    private User         $userModel;
    private Notification $notifModel;

    public function __construct()
    {
        $this->friendModel = new Friendship();
        $this->userModel   = new User();
        $this->notifModel  = new Notification();
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid     = $this->currentUserId();
        $friends = $this->userModel->getFriends($uid);
        $pending = $this->friendModel->getPendingRequests($uid);
        $sent    = $this->friendModel->getSentRequests($uid);
        $me      = $this->userModel->findById($uid);

        $this->view('friends/index', [
            'friends' => $friends,
            'pending' => $pending,
            'sent'    => $sent,
            'me'      => $me,
            'csrf'    => $this->csrf(),
        ]);
    }

    public function sendRequest(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid      = $this->currentUserId();
        $targetId = (int)($_POST['user_id'] ?? 0);

        if ($targetId === $uid || !$this->userModel->findById($targetId)) {
            $this->json(['error' => 'Неверный пользователь'], 422);
            return;
        }

        $existing = $this->friendModel->getStatus($uid, $targetId);
        if ($existing) {
            $this->json(['error' => 'Заявка уже существует'], 422);
            return;
        }

        $this->friendModel->sendRequest($uid, $targetId);

        $me = $this->userModel->findById($uid);
        $this->notifModel->create(
            $targetId, $uid, 'friend_request',
            "{$me['first_name']} {$me['last_name']} отправил(а) вам заявку в друзья",
            $uid, 'user'
        );

        $this->json(['success' => true, 'status' => 'pending']);
    }

    public function accept(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid         = $this->currentUserId();
        $requesterId = (int)($_POST['user_id'] ?? 0);

        if ($this->friendModel->accept($requesterId, $uid)) {
            $me = $this->userModel->findById($uid);
            $this->notifModel->create(
                $requesterId, $uid, 'friend_accept',
                "{$me['first_name']} {$me['last_name']} принял(а) вашу заявку в друзья",
                $uid, 'user'
            );
            $this->json(['success' => true, 'status' => 'accepted']);
        } else {
            $this->json(['error' => 'Заявка не найдена'], 404);
        }
    }

    public function decline(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid         = $this->currentUserId();
        $requesterId = (int)($_POST['user_id'] ?? 0);

        $this->friendModel->decline($requesterId, $uid);
        $this->json(['success' => true]);
    }

    public function remove(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid      = $this->currentUserId();
        $targetId = (int)($_POST['user_id'] ?? 0);

        $this->friendModel->remove($uid, $targetId);
        $this->json(['success' => true, 'status' => 'none']);
    }
}
