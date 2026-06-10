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
