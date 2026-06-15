<?php

class Report extends Model
{
    /** Подать жалобу (одна от пользователя на объект) */
    public function create(int $reporterId, string $type, int $targetId, string $category, ?string $comment = null): bool
    {
        try {
            $this->execute(
                'INSERT INTO reports (reporter_id, target_type, target_id, category, comment)
                 VALUES (?, ?, ?, ?, ?)',
                [$reporterId, $type, $targetId, $category, $comment]
            );
            return true;
        } catch (PDOException $e) {
            return false; // повторная жалоба от того же пользователя
        }
    }

    public function alreadyReported(int $reporterId, string $type, int $targetId): bool
    {
        return (bool)$this->fetchOne(
            'SELECT 1 FROM reports WHERE reporter_id = ? AND target_type = ? AND target_id = ?',
            [$reporterId, $type, $targetId]
        );
    }

    public function pendingCount(): int
    {
        $r = $this->fetchOne("SELECT COUNT(*) AS c FROM reports WHERE status = 'pending'");
        return $r ? (int)$r['c'] : 0;
    }

    /** Очередь жалоб, сгруппированная по объекту (приоритет — число жалоб) */
    public function getQueue(int $limit = 50): array
    {
        return $this->fetchAll(
            "SELECT target_type, target_id,
                    COUNT(*)                                  AS reports_count,
                    MAX(created_at)                           AS last_reported,
                    string_agg(DISTINCT category, ',')        AS categories
             FROM reports
             WHERE status = 'pending'
             GROUP BY target_type, target_id
             ORDER BY reports_count DESC, last_reported DESC
             LIMIT ?",
            [$limit]
        );
    }

    /** Подробности по конкретному объекту */
    public function getForTarget(string $type, int $targetId): array
    {
        return $this->fetchAll(
            "SELECT r.*, u.username, u.first_name, u.last_name
             FROM reports r JOIN users u ON u.id = r.reporter_id
             WHERE r.target_type = ? AND r.target_id = ? AND r.status = 'pending'
             ORDER BY r.created_at DESC",
            [$type, $targetId]
        );
    }

    /** Закрыть все жалобы на объект */
    public function resolveTarget(string $type, int $targetId, int $moderatorId, string $status = 'resolved'): void
    {
        $this->execute(
            "UPDATE reports SET status = ?, reviewed_by = ?, resolved_at = NOW()
             WHERE target_type = ? AND target_id = ? AND status = 'pending'",
            [$status, $moderatorId, $type, $targetId]
        );
    }

    public function statsByCategory(): array
    {
        return $this->fetchAll(
            "SELECT category, COUNT(*) AS c FROM reports WHERE status='pending' GROUP BY category ORDER BY c DESC"
        );
    }
}
