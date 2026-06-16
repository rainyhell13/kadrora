<?php

class MessageController extends Controller
{
    private Message    $msgModel;
    private User       $userModel;
    private Friendship $friendModel;

    public function __construct()
    {
        $this->msgModel    = new Message();
        $this->userModel   = new User();
        $this->friendModel = new Friendship();
    }

    /** Может ли $uid писать пользователю $target с учётом его приватности */
    private function canMessage(int $uid, array $target): bool
    {
        if ($uid === (int)$target['id']) return false;
        $level = $target['privacy_messages'] ?? 'all';
        if ($level === 'all') return true;
        return $this->friendModel->isFriend($uid, (int)$target['id']);
    }

    public function index(): void
    {
        $this->requireAuth();
        $uid           = $this->currentUserId();
        $conversations = $this->msgModel->getConversations($uid);
        $me            = $this->userModel->findById($uid);

        $this->view('messages/index', [
            'conversations' => $conversations,
            'me'            => $me,
            'csrf'          => $this->csrf(),
        ]);
    }

    public function conversation($targetUserId): void
    {
        $this->requireAuth();
        $targetUserId = (int)$targetUserId;
        $uid    = $this->currentUserId();
        $target = $this->userModel->findById($targetUserId);

        if (!$target) {
            http_response_code(404);
            $this->view('errors/404', [], 'main');
            return;
        }

        if (!$this->canMessage($uid, $target)) {
            $this->flash('error', 'Этот пользователь разрешил писать только друзьям');
            $this->redirect('/profile/' . $target['username']);
            return;
        }

        $convId   = $this->msgModel->getOrCreateConversation($uid, $targetUserId);
        $messages = array_reverse($this->msgModel->getMessages($convId, MESSAGES_PER_PAGE));
        $this->msgModel->markAsRead($convId, $uid);

        $conversations = $this->msgModel->getConversations($uid);

        $this->view('messages/conversation', [
            'target'        => $target,
            'messages'      => $messages,
            'conversations' => $conversations,
            'conv_id'       => $convId,
            'me'            => $this->userModel->findById($uid),
            'csrf'          => $this->csrf(),
        ]);
    }

    public function send(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid      = $this->currentUserId();
        $targetId = (int)($_POST['target_id'] ?? 0);
        $content  = trim($_POST['content'] ?? '');

        $target = $this->userModel->findById($targetId);
        if (!$target || !$this->canMessage($uid, $target)) {
            $this->json(['error' => 'Нельзя отправить сообщение этому пользователю'], 403);
            return;
        }

        if (!$content && empty($_FILES['image']['name'])) {
            $this->json(['error' => 'Пустое сообщение'], 422);
            return;
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $image = $this->uploadImage('image', PHOTO_UPLOAD_PATH);
            } catch (RuntimeException $e) {
                $this->json(['error' => $e->getMessage()], 422);
                return;
            }
        }

        $convId = $this->msgModel->getOrCreateConversation($uid, $targetId);
        $msgId  = $this->msgModel->send($convId, $uid, $content ?: '📷 Фото', $image);

        $me = $this->userModel->findById($uid);
        $html = $this->renderView('messages/partials/message_bubble', [
            'msg' => [
                'id'         => $msgId,
                'content'    => $content ?: '📷 Фото',
                'image'      => $image,
                'sender_id'  => $uid,
                'created_at' => date('Y-m-d H:i:s'),
                'first_name' => $me['first_name'],
                'last_name'  => $me['last_name'],
                'avatar'     => $me['avatar'],
            ],
            'uid' => $uid,
        ]);

        $this->json(['success' => true, 'html' => $html, 'msg_id' => $msgId]);
    }

    public function getNew(): void
    {
        $this->requireAuth();
        $uid    = $this->currentUserId();
        $convId = (int)($_GET['conv_id'] ?? 0);
        $lastId = (int)($_GET['last_id'] ?? 0);

        $msgs = (new Database())->getConnection()->prepare(
            "SELECT m.*, u.first_name, u.last_name, u.avatar
             FROM messages m JOIN users u ON u.id = m.sender_id
             WHERE m.conversation_id = ? AND m.id > ?
             ORDER BY m.created_at ASC"
        );
        $msgs->execute([$convId, $lastId]);
        $rows = $msgs->fetchAll();

        $this->msgModel->markAsRead($convId, $uid);

        $html = '';
        foreach ($rows as $msg) {
            $html .= $this->renderView('messages/partials/message_bubble', [
                'msg' => $msg,
                'uid' => $uid,
            ]);
        }

        $this->json([
            'html'     => $html,
            'count'    => count($rows),
            'last_id'  => $rows ? end($rows)['id'] : $lastId,
            'unread'   => $this->msgModel->getTotalUnread($uid),
        ]);
    }
}
