<?php

class UserBookmark extends Model
{
    public function add(int $userId, int $targetId): bool
    {
        if ($userId === $targetId) return false;
        try {
            $this->execute(
                'INSERT INTO user_bookmarks (user_id, target_id) VALUES (?, ?)',
                [$userId, $targetId]
            );
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function remove(int $userId, int $targetId): bool
    {
        return $this->execute(
            'DELETE FROM user_bookmarks WHERE user_id = ? AND target_id = ?',
            [$userId, $targetId]
        );
    }

    public function isBookmarked(int $userId, int $targetId): bool
    {
        return (bool)$this->fetchOne(
            'SELECT 1 FROM user_bookmarks WHERE user_id = ? AND target_id = ?',
            [$userId, $targetId]
        );
    }

    public function count(int $userId): int
    {
        $r = $this->fetchOne('SELECT COUNT(*) AS c FROM user_bookmarks WHERE user_id = ?', [$userId]);
        return $r ? (int)$r['c'] : 0;
    }

    public function getBookmarkedUsers(int $userId): array
    {
        return $this->fetchAll(
            "SELECT u.*, b.created_at AS bookmarked_at
             FROM user_bookmarks b
             JOIN users u ON u.id = b.target_id
             WHERE b.user_id = ?
             ORDER BY b.created_at DESC",
            [$userId]
        );
    }
}
