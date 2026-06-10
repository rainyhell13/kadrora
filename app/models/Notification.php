<?php

class Notification extends Model
{
    public function create(int $userId, int $actorId, string $type, string $message, ?int $entityId = null, ?string $entityType = null): void
    {
        if ($userId === $actorId) return;

        $this->execute(
            'INSERT INTO notifications (user_id, actor_id, type, message, entity_id, entity_type)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$userId, $actorId, $type, $message, $entityId, $entityType]
        );
    }

    public function getForUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT n.*, u.username, u.first_name, u.last_name, u.avatar
             FROM notifications n
             JOIN users u ON u.id = n.actor_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = false',
            [$userId]
        );
        return $row ? (int)$row['cnt'] : 0;
    }

    public function markAllRead(int $userId): void
    {
        $this->execute(
            'UPDATE notifications SET is_read = true WHERE user_id = ? AND is_read = false',
            [$userId]
        );
    }

    public function markRead(int $id, int $userId): void
    {
        $this->execute(
            'UPDATE notifications SET is_read = true WHERE id = ? AND user_id = ?',
            [$id, $userId]
        );
    }
}
