<?php

class ReportController extends Controller
{
    private Report $reportModel;

    private const CATEGORIES = ['spam','insult','violence','adult','fraud','hate','other'];
    private const TYPES      = ['post','comment','user','group','message'];

    public function __construct()
    {
        $this->reportModel = new Report();
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $uid      = $this->currentUserId();
        $type     = $_POST['target_type'] ?? '';
        $targetId = (int)($_POST['target_id'] ?? 0);
        $category = $_POST['category'] ?? 'other';
        $comment  = trim($_POST['comment'] ?? '') ?: null;

        if (!in_array($type, self::TYPES, true) || !in_array($category, self::CATEGORIES, true) || $targetId < 1) {
            $this->json(['error' => 'Некорректные данные жалобы'], 422);
            return;
        }

        if ($this->reportModel->alreadyReported($uid, $type, $targetId)) {
            $this->json(['error' => 'Вы уже отправляли жалобу на этот объект'], 409);
            return;
        }

        $this->reportModel->create($uid, $type, $targetId, $category, $comment);

        // Уведомить модераторов при первой жалобе на объект (без спама)
        if (count($this->reportModel->getForTarget($type, $targetId)) === 1) {
            $typeName = ['post'=>'запись','comment'=>'комментарий','user'=>'пользователя','group'=>'сообщество','message'=>'сообщение'][$type] ?? 'объект';
            $notif = new Notification();
            $me    = (new User())->findById($uid);
            foreach ((new User())->staffIds() as $sid) {
                if ($sid === $uid) continue;
                $notif->create($sid, $uid, 'report',
                    "{$me['first_name']} {$me['last_name']} пожаловался(ась) на $typeName", $targetId, $type);
            }
        }

        $this->json(['success' => true]);
    }
}
