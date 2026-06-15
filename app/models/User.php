<?php

class User extends Model
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO users (username, email, password_hash, first_name, last_name, gender, birth_date, city)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['first_name'],
                $data['last_name'],
                ($data['gender']     ?? '') ?: null,
                ($data['birth_date'] ?? '') ?: null,
                ($data['city']       ?? '') ?: null,
            ]
        );
        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        $allowed = ['first_name','last_name','bio','city','birth_date','gender','website','avatar','cover_photo',
                    'status','relationship','interests','fav_music','fav_films','fav_books','fav_games',
                    'fav_quotes','activities','life_main','people_main',
                    'privacy_profile','privacy_wall','privacy_messages','privacy_friends','privacy_photos'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[]  = $data[$field];
            }
        }
        if (empty($fields)) return false;

        $params[] = $id;
        return $this->execute(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $params
        );
    }

    public function updatePassword(int $id, string $password): bool
    {
        return $this->execute(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [password_hash($password, PASSWORD_BCRYPT), $id]
        );
    }

    public function updateStatus(int $id, ?string $status): bool
    {
        return $this->execute('UPDATE users SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function setOnline(int $id, bool $status): void
    {
        $this->execute(
            'UPDATE users SET is_online = ?, last_seen = NOW() WHERE id = ?',
            [$status ? 'true' : 'false', $id]
        );
    }

    // ===================== МОДЕРАЦИЯ =====================

    public function getRole(int $id): string
    {
        $r = $this->fetchOne('SELECT role FROM users WHERE id = ?', [$id]);
        return $r ? $r['role'] : 'user';
    }

    /** Активен ли бан (перманентный is_banned или временный banned_until) */
    public function isCurrentlyBanned(array $user): bool
    {
        if (!empty($user['is_banned'])) return true;
        if (!empty($user['banned_until']) && strtotime($user['banned_until']) > time()) return true;
        return false;
    }

    public function isCurrentlyMuted(array $user): bool
    {
        if (!empty($user['is_muted'])) return true;
        if (!empty($user['muted_until']) && strtotime($user['muted_until']) > time()) return true;
        return false;
    }

    public function ban(int $id, ?string $reason, ?string $until = null): void
    {
        if ($until === null) {
            $this->execute('UPDATE users SET is_banned = true, ban_reason = ?, banned_until = NULL WHERE id = ?', [$reason, $id]);
        } else {
            $this->execute('UPDATE users SET is_banned = false, ban_reason = ?, banned_until = ? WHERE id = ?', [$reason, $until, $id]);
        }
    }

    public function unban(int $id): void
    {
        $this->execute('UPDATE users SET is_banned = false, banned_until = NULL, ban_reason = NULL WHERE id = ?', [$id]);
    }

    public function mute(int $id, ?string $until = null): void
    {
        if ($until === null) {
            $this->execute('UPDATE users SET is_muted = true, muted_until = NULL WHERE id = ?', [$id]);
        } else {
            $this->execute('UPDATE users SET is_muted = false, muted_until = ? WHERE id = ?', [$until, $id]);
        }
    }

    public function unmute(int $id): void
    {
        $this->execute('UPDATE users SET is_muted = false, muted_until = NULL WHERE id = ?', [$id]);
    }

    /** Предупреждение. Возвращает новое число предупреждений (для авто-эскалации) */
    public function addWarning(int $id): int
    {
        $this->execute('UPDATE users SET warnings_count = warnings_count + 1 WHERE id = ?', [$id]);
        $r = $this->fetchOne('SELECT warnings_count FROM users WHERE id = ?', [$id]);
        return $r ? (int)$r['warnings_count'] : 0;
    }

    public function setRole(int $id, string $role): void
    {
        if (!in_array($role, ['user','moderator','admin'], true)) return;
        $this->execute('UPDATE users SET role = ? WHERE id = ?', [$role, $id]);
    }

    public function setVerified(int $id, bool $value): void
    {
        $this->execute('UPDATE users SET is_verified = ? WHERE id = ?', [$value ? 'true' : 'false', $id]);
    }

    /** Список пользователей для админ-панели (поиск + фильтр) */
    public function adminList(string $q = '', string $filter = 'all', int $limit = 30, int $offset = 0): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = "(lower(first_name || ' ' || last_name) LIKE ? OR lower(username) LIKE ? OR lower(email) LIKE ?)";
            $like = '%' . mb_strtolower($q) . '%';
            $params = [$like, $like, $like];
        }
        if ($filter === 'banned')     $where[] = "(is_banned = true OR (banned_until IS NOT NULL AND banned_until > NOW()))";
        if ($filter === 'muted')      $where[] = "(is_muted = true OR (muted_until IS NOT NULL AND muted_until > NOW()))";
        if ($filter === 'staff')      $where[] = "role IN ('moderator','admin')";
        $sql = 'SELECT * FROM users';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit; $params[] = $offset;
        return $this->fetchAll($sql, $params);
    }

    public function globalStats(): array
    {
        $one = fn($sql) => (int)($this->fetchOne($sql)['c'] ?? 0);
        return [
            'users'        => $one('SELECT COUNT(*) AS c FROM users'),
            'banned'       => $one("SELECT COUNT(*) AS c FROM users WHERE is_banned = true OR (banned_until IS NOT NULL AND banned_until > NOW())"),
            'posts'        => $one('SELECT COUNT(*) AS c FROM posts'),
            'groups'       => $one('SELECT COUNT(*) AS c FROM groups'),
            'new_week'     => $one("SELECT COUNT(*) AS c FROM users WHERE created_at >= NOW() - INTERVAL '7 days'"),
            'online'       => $one('SELECT COUNT(*) AS c FROM users WHERE is_online = true'),
        ];
    }

    public function search(string $query, int $currentUserId, int $limit = 20, int $offset = 0): array
    {
        $q = '%' . mb_strtolower($query) . '%';
        return $this->fetchAll(
            "SELECT u.*,
                    CASE WHEN f.status = 'accepted' THEN true ELSE false END AS is_friend
             FROM users u
             LEFT JOIN friendships f ON (
                 (f.requester = ? AND f.addressee = u.id) OR
                 (f.addressee = ? AND f.requester = u.id)
             )
             WHERE u.id <> ?
               AND u.is_banned = false
               AND (
                   lower(u.first_name || ' ' || u.last_name) LIKE ?
                   OR lower(u.username) LIKE ?
                   OR lower(u.city) LIKE ?
               )
             ORDER BY u.first_name, u.last_name
             LIMIT ? OFFSET ?",
            [$currentUserId, $currentUserId, $currentUserId, $q, $q, $q, $limit, $offset]
        );
    }

    public function getFriends(int $userId): array
    {
        return $this->fetchAll(
            "SELECT u.*, f.created_at AS friends_since
             FROM users u
             JOIN friendships f ON (
                 (f.requester = ? AND f.addressee = u.id) OR
                 (f.addressee = ? AND f.requester = u.id)
             )
             WHERE f.status = 'accepted'
               AND u.is_banned = false
             ORDER BY u.first_name, u.last_name",
            [$userId, $userId]
        );
    }

    public function getSuggestedUsers(int $userId, int $limit = 6): array
    {
        return $this->fetchAll(
            "SELECT u.*
             FROM users u
             WHERE u.id <> ?
               AND u.is_banned = false
               AND u.id NOT IN (
                   SELECT CASE WHEN requester = ? THEN addressee ELSE requester END
                   FROM friendships
                   WHERE requester = ? OR addressee = ?
               )
             ORDER BY RANDOM()
             LIMIT ?",
            [$userId, $userId, $userId, $userId, $limit]
        );
    }

    public function getStats(int $userId): array
    {
        $friends = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM friendships WHERE (requester=? OR addressee=?) AND status='accepted'",
            [$userId, $userId]
        );
        $posts = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM posts WHERE user_id = ?",
            [$userId]
        );
        $photos = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM photos WHERE user_id = ?",
            [$userId]
        );
        return [
            'friends' => (int)$friends['cnt'],
            'posts'   => (int)$posts['cnt'],
            'photos'  => (int)$photos['cnt'],
        ];
    }
}
