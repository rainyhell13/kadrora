<?php

class Bookmark extends Model
{
    public function add(int $userId, int $postId): bool
    {
        try {
            $this->execute(
                'INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)',
                [$userId, $postId]
            );
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function remove(int $userId, int $postId): bool
    {
        return $this->execute(
            'DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?',
            [$userId, $postId]
        );
    }

    public function isBookmarked(int $userId, int $postId): bool
    {
        return (bool)$this->fetchOne(
            'SELECT 1 FROM bookmarks WHERE user_id = ? AND post_id = ?',
            [$userId, $postId]
        );
    }

    public function count(int $userId): int
    {
        $r = $this->fetchOne('SELECT COUNT(*) AS c FROM bookmarks WHERE user_id = ?', [$userId]);
        return $r ? (int)$r['c'] : 0;
    }

    /** Сохранённые записи с полной информацией для отображения как пост */
    public function getUserBookmarks(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT p.*, u.username, u.first_name, u.last_name, u.avatar,
                    wo.username AS wall_username, wo.first_name AS wall_first_name, wo.last_name AS wall_last_name,
                    true AS bookmarked_by_me,
                    EXISTS(SELECT 1 FROM post_likes pl WHERE pl.post_id = p.id AND pl.user_id = ?) AS liked_by_me,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comments_count,
                    b.created_at AS bookmarked_at
             FROM bookmarks b
             JOIN posts p  ON p.id = b.post_id
             JOIN users u  ON u.id = p.user_id
             JOIN users wo ON wo.id = p.wall_owner_id
             WHERE b.user_id = ?
             ORDER BY b.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $userId, $limit, $offset]
        );
    }
}
