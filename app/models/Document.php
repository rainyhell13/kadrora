<?php

class Document extends Model
{
    public function add(int $userId, string $title, string $filename, string $originalName, string $ext, int $size): int
    {
        $this->query(
            'INSERT INTO documents (user_id, title, filename, original_name, ext, size_bytes)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$userId, $title, $filename, $originalName, $ext, $size]
        );
        return (int)$this->lastInsertId();
    }

    public function getUserDocs(int $userId, int $limit = 100, int $offset = 0): array
    {
        return $this->fetchAll(
            'SELECT * FROM documents WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $limit, $offset]
        );
    }

    public function count(int $userId): int
    {
        $r = $this->fetchOne('SELECT COUNT(*) AS c FROM documents WHERE user_id = ?', [$userId]);
        return $r ? (int)$r['c'] : 0;
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM documents WHERE id = ?', [$id]);
    }

    public function delete(int $id, int $userId): ?string
    {
        $row = $this->fetchOne('SELECT filename FROM documents WHERE id = ? AND user_id = ?', [$id, $userId]);
        if (!$row) return null;
        $this->execute('DELETE FROM documents WHERE id = ?', [$id]);
        return $row['filename'];
    }
}
