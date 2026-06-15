<?php

class ModerationLog extends Model
{
    public function add(int $moderatorId, string $action, ?string $targetType = null, ?int $targetId = null, ?string $details = null): void
    {
        $this->execute(
            'INSERT INTO moderation_log (moderator_id, action, target_type, target_id, details)
             VALUES (?, ?, ?, ?, ?)',
            [$moderatorId, $action, $targetType, $targetId, $details]
        );
    }

    public function recent(int $limit = 100, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT l.*, u.username, u.first_name, u.last_name, u.avatar
             FROM moderation_log l JOIN users u ON u.id = l.moderator_id
             ORDER BY l.created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function countToday(): int
    {
        $r = $this->fetchOne("SELECT COUNT(*) AS c FROM moderation_log WHERE created_at >= NOW() - INTERVAL '7 days'");
        return $r ? (int)$r['c'] : 0;
    }
}
