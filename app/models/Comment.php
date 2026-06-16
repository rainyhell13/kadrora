<?php

class Comment extends Model
{
    public function getByPost(int $postId, int $viewerId = 0): array
    {
        return $this->fetchAll(
            "SELECT c.*, u.username, u.first_name, u.last_name, u.avatar,
                    (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS likes_count,
                    EXISTS(SELECT 1 FROM comment_likes cl2 WHERE cl2.comment_id = c.id AND cl2.user_id = ?) AS liked_by_me
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.post_id = ? AND c.parent_id IS NULL AND c.status NOT IN ('hidden','removed')
             ORDER BY c.created_at ASC",
            [$viewerId, $postId]
        );
    }

    public function getLikesCount(int $commentId): int
    {
        $r = $this->fetchOne('SELECT COUNT(*) AS c FROM comment_likes WHERE comment_id = ?', [$commentId]);
        return $r ? (int)$r['c'] : 0;
    }

    public function isLikedBy(int $commentId, int $userId): bool
    {
        return (bool)$this->fetchOne('SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?', [$commentId, $userId]);
    }

    public function getReplies(int $parentId): array
    {
        return $this->fetchAll(
            "SELECT c.*, u.username, u.first_name, u.last_name, u.avatar
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.parent_id = ?
             ORDER BY c.created_at ASC",
            [$parentId]
        );
    }

    public function create(int $postId, int $userId, string $content, ?int $parentId = null): int
    {
        $this->query(
            'INSERT INTO comments (post_id, user_id, content, parent_id) VALUES (?, ?, ?, ?)',
            [$postId, $userId, $content, $parentId]
        );
        return (int)$this->lastInsertId();
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->execute(
            'DELETE FROM comments WHERE id = ? AND user_id = ?',
            [$id, $userId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT c.*, u.username, u.first_name, u.last_name, u.avatar
             FROM comments c JOIN users u ON u.id = c.user_id WHERE c.id = ?",
            [$id]
        );
    }

    public function like(int $commentId, int $userId): bool
    {
        try {
            $this->execute(
                'INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)',
                [$commentId, $userId]
            );
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function unlike(int $commentId, int $userId): bool
    {
        return $this->execute(
            'DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?',
            [$commentId, $userId]
        );
    }
}
