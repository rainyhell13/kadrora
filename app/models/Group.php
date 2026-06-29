<?php

class Group extends Model
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM groups WHERE id = ?', [$id]);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne('SELECT * FROM groups WHERE slug = ?', [$slug]);
    }

    public function create(int $ownerId, array $data): int
    {
        $slug = $this->makeSlug($data['name']);
        $this->query(
            'INSERT INTO groups (owner_id, name, slug, description, privacy) VALUES (?, ?, ?, ?, ?)',
            [$ownerId, $data['name'], $slug, $data['description'] ?? null, $data['privacy'] ?? 'public']
        );
        $id = (int)$this->lastInsertId();
        // Владелец сразу становится admin-участником
        $this->execute(
            "INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')",
            [$id, $ownerId]
        );
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['name','description','privacy','avatar','cover'] as $f) {
            if (array_key_exists($f, $data)) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        return $this->execute('UPDATE groups SET ' . implode(', ', $fields) . ' WHERE id = ?', $params);
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->execute('DELETE FROM groups WHERE id = ? AND owner_id = ?', [$id, $userId]);
    }

    public function getAll(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT g.*,
                    u.username AS owner_username, u.first_name AS owner_first, u.last_name AS owner_last,
                    EXISTS(SELECT 1 FROM group_members gm WHERE gm.group_id=g.id AND gm.user_id=?) AS is_member
             FROM groups g
             JOIN users u ON u.id = g.owner_id
             ORDER BY g.members_count DESC, g.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public function getUserGroups(int $userId): array
    {
        return $this->fetchAll(
            "SELECT g.*, gm.role,
                    EXISTS(SELECT 1 FROM group_members gm2 WHERE gm2.group_id=g.id AND gm2.user_id=?) AS is_member
             FROM groups g
             JOIN group_members gm ON gm.group_id = g.id AND gm.user_id = ?
             ORDER BY g.name",
            [$userId, $userId]
        );
    }

    public function join(int $groupId, int $userId): bool
    {
        try {
            $this->execute(
                "INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')",
                [$groupId, $userId]
            );
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function leave(int $groupId, int $userId): bool
    {
        return $this->execute(
            'DELETE FROM group_members WHERE group_id = ? AND user_id = ? AND role != \'admin\'',
            [$groupId, $userId]
        );
    }

    // ---------- Заявки в приватные сообщества ----------
    public function requestJoin(int $groupId, int $userId): bool
    {
        try {
            $this->execute(
                "INSERT INTO group_join_requests (group_id, user_id, status) VALUES (?, ?, 'pending')
                 ON CONFLICT (group_id, user_id) DO UPDATE SET status='pending', created_at=NOW()",
                [$groupId, $userId]
            );
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function hasPendingRequest(int $groupId, int $userId): bool
    {
        return (bool)$this->fetchOne(
            "SELECT 1 FROM group_join_requests WHERE group_id=? AND user_id=? AND status='pending'",
            [$groupId, $userId]
        );
    }

    public function cancelRequest(int $groupId, int $userId): bool
    {
        return $this->execute(
            "DELETE FROM group_join_requests WHERE group_id=? AND user_id=? AND status='pending'",
            [$groupId, $userId]
        );
    }

    public function getJoinRequests(int $groupId): array
    {
        return $this->fetchAll(
            "SELECT r.*, u.username, u.first_name, u.last_name, u.avatar
             FROM group_join_requests r
             JOIN users u ON u.id = r.user_id
             WHERE r.group_id = ? AND r.status = 'pending'
             ORDER BY r.created_at DESC",
            [$groupId]
        );
    }

    public function countJoinRequests(int $groupId): int
    {
        $r = $this->fetchOne("SELECT COUNT(*) AS c FROM group_join_requests WHERE group_id=? AND status='pending'", [$groupId]);
        return $r ? (int)$r['c'] : 0;
    }

    public function acceptRequest(int $groupId, int $userId): bool
    {
        $req = $this->fetchOne(
            "SELECT 1 FROM group_join_requests WHERE group_id=? AND user_id=? AND status='pending'",
            [$groupId, $userId]
        );
        if (!$req) return false;
        $this->join($groupId, $userId);
        $this->execute(
            "UPDATE group_join_requests SET status='accepted' WHERE group_id=? AND user_id=?",
            [$groupId, $userId]
        );
        return true;
    }

    public function declineRequest(int $groupId, int $userId): bool
    {
        return $this->execute(
            "UPDATE group_join_requests SET status='declined' WHERE group_id=? AND user_id=? AND status='pending'",
            [$groupId, $userId]
        );
    }

    public function isMember(int $groupId, int $userId): bool
    {
        return (bool)$this->fetchOne(
            'SELECT 1 FROM group_members WHERE group_id = ? AND user_id = ?',
            [$groupId, $userId]
        );
    }

    public function getRole(int $groupId, int $userId): ?string
    {
        $row = $this->fetchOne(
            'SELECT role FROM group_members WHERE group_id = ? AND user_id = ?',
            [$groupId, $userId]
        );
        return $row ? $row['role'] : null;
    }

    public function getMembers(int $groupId, int $limit = 30): array
    {
        return $this->fetchAll(
            "SELECT u.*, gm.role, gm.joined_at
             FROM group_members gm
             JOIN users u ON u.id = gm.user_id
             WHERE gm.group_id = ?
             ORDER BY CASE gm.role WHEN 'admin' THEN 0 WHEN 'moderator' THEN 1 ELSE 2 END, u.first_name
             LIMIT ?",
            [$groupId, $limit]
        );
    }

    // Посты группы
    public function getPosts(int $groupId, int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT gp.*, u.username, u.first_name, u.last_name, u.avatar,
                    EXISTS(SELECT 1 FROM group_post_likes l WHERE l.post_id=gp.id AND l.user_id=?) AS liked_by_me
             FROM group_posts gp
             JOIN users u ON u.id = gp.user_id
             WHERE gp.group_id = ?
             ORDER BY gp.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $groupId, $limit, $offset]
        );
    }

    public function createPost(int $groupId, int $userId, string $content, ?string $image = null): int
    {
        $this->query(
            'INSERT INTO group_posts (group_id, user_id, content, image) VALUES (?, ?, ?, ?)',
            [$groupId, $userId, $content, $image]
        );
        $this->execute('UPDATE groups SET posts_count = posts_count + 1 WHERE id = ?', [$groupId]);
        return (int)$this->lastInsertId();
    }

    public function deletePost(int $postId, int $userId): bool
    {
        $post = $this->fetchOne('SELECT * FROM group_posts WHERE id = ?', [$postId]);
        if (!$post) return false;
        $role = $this->getRole($post['group_id'], $userId);
        if ($post['user_id'] !== $userId && !in_array($role, ['admin','moderator'])) return false;
        $this->execute('UPDATE groups SET posts_count = GREATEST(posts_count-1,0) WHERE id = ?', [$post['group_id']]);
        return $this->execute('DELETE FROM group_posts WHERE id = ?', [$postId]);
    }

    public function likePost(int $postId, int $userId): bool
    {
        try {
            $this->execute('INSERT INTO group_post_likes (post_id, user_id) VALUES (?, ?)', [$postId, $userId]);
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function unlikePost(int $postId, int $userId): bool
    {
        return $this->execute('DELETE FROM group_post_likes WHERE post_id=? AND user_id=?', [$postId, $userId]);
    }

    public function getPostLikesCount(int $postId): int
    {
        $r = $this->fetchOne('SELECT likes_count FROM group_posts WHERE id=?', [$postId]);
        return $r ? (int)$r['likes_count'] : 0;
    }

    // Лента: посты из групп пользователя
    public function getFeedPosts(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT gp.*, g.name AS group_name, g.slug AS group_slug, g.avatar AS group_avatar,
                    u.username, u.first_name, u.last_name, u.avatar,
                    EXISTS(SELECT 1 FROM group_post_likes l WHERE l.post_id=gp.id AND l.user_id=?) AS liked_by_me
             FROM group_posts gp
             JOIN groups g ON g.id = gp.group_id
             JOIN users u ON u.id = gp.user_id
             WHERE gp.group_id IN (SELECT group_id FROM group_members WHERE user_id=?)
             ORDER BY gp.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $userId, $limit, $offset]
        );
    }

    public function search(string $q, int $userId, int $limit = 20): array
    {
        $q = '%' . mb_strtolower($q) . '%';
        return $this->fetchAll(
            "SELECT g.*,
                    EXISTS(SELECT 1 FROM group_members gm WHERE gm.group_id=g.id AND gm.user_id=?) AS is_member
             FROM groups g
             WHERE lower(g.name) LIKE ? OR lower(g.description) LIKE ?
             ORDER BY g.members_count DESC LIMIT ?",
            [$userId, $q, $q, $limit]
        );
    }

    private function makeSlug(string $name): string
    {
        $slug = mb_strtolower($name);
        // Транслитерация кириллицы в латиницу (без расширения intl)
        $map = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh',
            'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
            'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c',
            'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        ];
        $slug = strtr($slug, $map);
        $slug = preg_replace('/\s+/', '_', trim($slug));
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
        $slug = $slug ?: 'group';
        // Гарантируем уникальность
        $base = substr($slug, 0, 100);
        $candidate = $base;
        $i = 2;
        while ($this->findBySlug($candidate)) {
            $candidate = $base . '_' . $i++;
        }
        return $candidate;
    }
}
