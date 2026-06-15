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
        $this->json(['success' => true]);
    }
}
