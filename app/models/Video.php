<?php

class Video extends Model
{
    public function add(int $userId, string $title, string $filename): int
    {
        $this->query(
            'INSERT INTO videos (user_id, title, filename) VALUES (?, ?, ?)',
            [$userId, $title, $filename]
        );
        return (int)$this->lastInsertId();
    }

    public function getUserVideos(int $userId, int $limit = 30, int $offset = 0): array
    {
        return $this->fetchAll(
            'SELECT * FROM videos WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT v.*, u.username, u.first_name, u.last_name FROM videos v
             JOIN users u ON u.id = v.user_id WHERE v.id = ?',
            [$id]
        );
    }

    public function count(int $userId): int
    {
        $r = $this->fetchOne('SELECT COUNT(*) AS c FROM videos WHERE user_id = ?', [$userId]);
        return $r ? (int)$r['c'] : 0;
    }

    public function incrementViews(int $id): void
    {
        $this->execute('UPDATE videos SET views = views + 1 WHERE id = ?', [$id]);
    }

    public function delete(int $id, int $userId): ?string
    {
        $row = $this->fetchOne('SELECT filename FROM videos WHERE id = ? AND user_id = ?', [$id, $userId]);
        if (!$row) return null;
        $this->execute('DELETE FROM videos WHERE id = ?', [$id]);
        return $row['filename'];
    }
}
