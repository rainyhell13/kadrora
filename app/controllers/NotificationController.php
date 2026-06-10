<?php

class NotificationController extends Controller
{
    private Notification $notifModel;
    private User         $userModel;

    public function __construct()
    {
        $this->notifModel = new Notification();
        $this->userModel  = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid   = $this->currentUserId();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * 20;

        $notifs = $this->notifModel->getForUser($uid, 20, $offset);
        $this->notifModel->markAllRead($uid);

        $this->view('notifications/index', [
            'notifications' => $notifs,
            'me'            => $this->userModel->findById($uid),
            'page'          => $page,
            'csrf'          => $this->csrf(),
        ]);
    }

    public function getCount(): void
    {
        $this->requireAuth();
        $uid = $this->currentUserId();
        $this->json([
            'notif_count'   => $this->notifModel->getUnreadCount($uid),
            'message_count' => (new Message())->getTotalUnread($uid),
        ]);
    }
}
