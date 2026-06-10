<?php

class Friendship extends Model
{
    public function getStatus(int $userId, int $targetId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM friendships
             WHERE (requester = ? AND addressee = ?) OR (requester = ? AND addressee = ?)",
            [$userId, $targetId, $targetId, $userId]
        );
    }

    public function sendRequest(int $from, int $to): bool
    {
        try {
            $this->execute(
                "INSERT INTO friendships (requester, addressee, status) VALUES (?, ?, 'pending')",
                [$from, $to]
            );
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function accept(int $requesterId, int $addresseeId): bool
    {
        return $this->execute(
            "UPDATE friendships SET status='accepted' WHERE requester=? AND addressee=? AND status='pending'",
            [$requesterId, $addresseeId]
        );
    }

    public function decline(int $requesterId, int $addresseeId): bool
    {
        return $this->execute(
            "DELETE FROM friendships WHERE requester=? AND addressee=? AND status='pending'",
            [$requesterId, $addresseeId]
        );
    }

    public function remove(int $userId, int $targetId): bool
    {
        return $this->execute(
            "DELETE FROM friendships
             WHERE (requester=? AND addressee=?) OR (requester=? AND addressee=?)",
            [$userId, $targetId, $targetId, $userId]
        );
    }

    public function block(int $userId, int $targetId): bool
    {
        $this->remove($userId, $targetId);
        return $this->execute(
            "INSERT INTO friendships (requester, addressee, status) VALUES (?, ?, 'blocked')",
            [$userId, $targetId]
        );
    }

    public function getPendingRequests(int $userId): array
    {
        return $this->fetchAll(
            "SELECT f.*, u.username, u.first_name, u.last_name, u.avatar
             FROM friendships f
             JOIN users u ON u.id = f.requester
             WHERE f.addressee = ? AND f.status = 'pending'
             ORDER BY f.created_at DESC",
            [$userId]
        );
    }

    public function getSentRequests(int $userId): array
    {
        return $this->fetchAll(
            "SELECT f.*, u.username, u.first_name, u.last_name, u.avatar
             FROM friendships f
             JOIN users u ON u.id = f.addressee
             WHERE f.requester = ? AND f.status = 'pending'
             ORDER BY f.created_at DESC",
            [$userId]
        );
    }

    public function isFriend(int $userId, int $targetId): bool
    {
        $row = $this->fetchOne(
            "SELECT 1 FROM friendships
             WHERE ((requester=? AND addressee=?) OR (requester=? AND addressee=?)) AND status='accepted'",
            [$userId, $targetId, $targetId, $userId]
        );
        return $row !== null;
    }

    public function getMutualFriendsCount(int $userId, int $targetId): int
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM (
                SELECT CASE WHEN f1.requester=? THEN f1.addressee ELSE f1.requester END AS fid
                FROM friendships f1
                WHERE (f1.requester=? OR f1.addressee=?) AND f1.status='accepted'
                INTERSECT
                SELECT CASE WHEN f2.requester=? THEN f2.addressee ELSE f2.requester END AS fid
                FROM friendships f2
                WHERE (f2.requester=? OR f2.addressee=?) AND f2.status='accepted'
             ) t",
            [$userId, $userId, $userId, $targetId, $targetId, $targetId]
        );
        return $row ? (int)$row['cnt'] : 0;
    }
}
