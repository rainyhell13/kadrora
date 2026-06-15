<?php

class Post extends Model
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT p.*, u.username, u.first_name, u.last_name, u.avatar,
                    wo.username AS wall_username, wo.first_name AS wall_first_name, wo.last_name AS wall_last_name
             FROM posts p
             JOIN users u  ON u.id  = p.user_id
             JOIN users wo ON wo.id = p.wall_owner_id
             WHERE p.id = ?",
            [$id]
        );
    }

    public function create(int $userId, string $content, ?string $image = null, string $privacy = 'public', ?int $wallOwnerId = null): int
    {
        $wallOwnerId = $wallOwnerId ?: $userId;
        $this->query(
            'INSERT INTO posts (user_id, content, image, privacy, wall_owner_id) VALUES (?, ?, ?, ?, ?)',
            [$userId, $content, $image, $privacy, $wallOwnerId]
        );
        return (int)$this->lastInsertId();
    }

    public function update(int $id, int $userId, string $content, string $privacy): bool
    {
        return $this->execute(
            'UPDATE posts SET content = ?, privacy = ? WHERE id = ? AND user_id = ?',
            [$content, $privacy, $id, $userId]
        );
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->execute(
            'DELETE FROM posts WHERE id = ? AND user_id = ?',
            [$id, $userId]
        );
    }

    /** Лента: посты текущего пользователя + посты его друзей */
    public function getFeed(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT p.*, u.username, u.first_name, u.last_name, u.avatar,
                    EXISTS(SELECT 1 FROM post_likes pl WHERE pl.post_id = p.id AND pl.user_id = ?) AS liked_by_me,
                    EXISTS(SELECT 1 FROM bookmarks bm WHERE bm.post_id = p.id AND bm.user_id = ?) AS bookmarked_by_me,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comments_count
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.status NOT IN ('hidden','removed') AND (
                 p.user_id = ?
                 OR (
                     p.user_id IN (
                         SELECT CASE WHEN requester = ? THEN addressee ELSE requester END
                         FROM friendships WHERE (requester=? OR addressee=?) AND status='accepted'
                     )
                     AND p.privacy IN ('public','friends')
                 )
             )
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $userId, $userId, $userId, $userId, $userId, $limit, $offset]
        );
    }

    /** Записи на стене пользователя (свои + написанные другими) */
    public function getUserPosts(int $profileUserId, int $viewerId, int $limit = 10, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT p.*, u.username, u.first_name, u.last_name, u.avatar,
                    wo.username AS wall_username, wo.first_name AS wall_first_name, wo.last_name AS wall_last_name,
                    EXISTS(SELECT 1 FROM post_likes pl WHERE pl.post_id = p.id AND pl.user_id = ?) AS liked_by_me,
                    EXISTS(SELECT 1 FROM bookmarks bm WHERE bm.post_id = p.id AND bm.user_id = ?) AS bookmarked_by_me,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comments_count
             FROM posts p
             JOIN users u  ON u.id  = p.user_id
             JOIN users wo ON wo.id = p.wall_owner_id
             WHERE p.wall_owner_id = ?
               AND p.status NOT IN ('hidden','removed')
               AND (
                   p.privacy = 'public'
                   OR p.user_id = ?
                   OR (p.privacy = 'friends' AND EXISTS(
                       SELECT 1 FROM friendships
                       WHERE ((requester=? AND addressee=p.user_id) OR (addressee=? AND requester=p.user_id))
                         AND status='accepted'
                   ))
               )
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$viewerId, $viewerId, $profileUserId, $viewerId, $viewerId, $viewerId, $limit, $offset]
        );
    }

    public function like(int $postId, int $userId): bool
    {
        try {
            $this->execute(
                'INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)',
                [$postId, $userId]
            );
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function unlike(int $postId, int $userId): bool
    {
        return $this->execute(
            'DELETE FROM post_likes WHERE post_id = ? AND user_id = ?',
            [$postId, $userId]
        );
    }

    public function isLikedBy(int $postId, int $userId): bool
    {
        $row = $this->fetchOne(
            'SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?',
            [$postId, $userId]
        );
        return $row !== null;
    }

    public function getLikesCount(int $postId): int
    {
        $row = $this->fetchOne('SELECT likes_count FROM posts WHERE id = ?', [$postId]);
        return $row ? (int)$row['likes_count'] : 0;
    }

    public function incrementViews(int $postId): void
    {
        $this->execute('UPDATE posts SET views_count = views_count + 1 WHERE id = ?', [$postId]);
    }

    /** Поиск публичных записей по тексту */
    public function searchPublic(string $query, int $viewerId, int $limit = 10): array
    {
        $q = '%' . mb_strtolower($query) . '%';
        return $this->fetchAll(
            "SELECT p.*, u.username, u.first_name, u.last_name, u.avatar,
                    wo.username AS wall_username, wo.first_name AS wall_first_name, wo.last_name AS wall_last_name,
                    EXISTS(SELECT 1 FROM post_likes pl WHERE pl.post_id = p.id AND pl.user_id = ?) AS liked_by_me,
                    EXISTS(SELECT 1 FROM bookmarks bm WHERE bm.post_id = p.id AND bm.user_id = ?) AS bookmarked_by_me,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comments_count
             FROM posts p
             JOIN users u  ON u.id  = p.user_id
             JOIN users wo ON wo.id = p.wall_owner_id
             WHERE p.privacy = 'public' AND p.status NOT IN ('hidden','removed') AND lower(p.content) LIKE ?
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$viewerId, $viewerId, $q, $limit]
        );
    }

    /** Случайные публичные записи для общей ленты (раздел «Интересное») */
    public function getDiscover(int $viewerId, int $limit = 10): array
    {
        return $this->fetchAll(
            "SELECT p.*, u.username, u.first_name, u.last_name, u.avatar,
                    wo.username AS wall_username, wo.first_name AS wall_first_name, wo.last_name AS wall_last_name,
                    EXISTS(SELECT 1 FROM post_likes pl WHERE pl.post_id = p.id AND pl.user_id = ?) AS liked_by_me,
                    EXISTS(SELECT 1 FROM bookmarks bm WHERE bm.post_id = p.id AND bm.user_id = ?) AS bookmarked_by_me,
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comments_count
             FROM posts p
             JOIN users u  ON u.id  = p.user_id
             JOIN users wo ON wo.id = p.wall_owner_id
             WHERE p.privacy = 'public' AND p.wall_owner_id = p.user_id AND p.status NOT IN ('hidden','removed')
             ORDER BY RANDOM()
             LIMIT ?",
            [$viewerId, $viewerId, $limit]
        );
    }
}
