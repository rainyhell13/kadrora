<?php

class Photo extends Model
{
    public function add(int $userId, string $filename, ?string $caption = null, ?int $postId = null): int
    {
        $this->query(
            'INSERT INTO photos (user_id, filename, caption, post_id) VALUES (?, ?, ?, ?)',
            [$userId, $filename, $caption, $postId]
        );
        return (int)$this->lastInsertId();
    }

    public function getUserPhotos(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->fetchAll(
            'SELECT * FROM photos WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $limit, $offset]
        );
    }

    public function delete(int $id, int $userId): ?string
    {
        $photo = $this->fetchOne('SELECT filename FROM photos WHERE id = ? AND user_id = ?', [$id, $userId]);
        if (!$photo) return null;
        $this->execute('DELETE FROM photos WHERE id = ?', [$id]);
        return $photo['filename'];
    }
}
