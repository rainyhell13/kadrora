<?php

class Message extends Model
{
    /** Получить или создать диалог между двумя пользователями */
    public function getOrCreateConversation(int $userA, int $userB): int
    {
        $row = $this->fetchOne(
            "SELECT cp1.conversation_id
             FROM conversation_participants cp1
             JOIN conversation_participants cp2 ON cp2.conversation_id = cp1.conversation_id
             WHERE cp1.user_id = ? AND cp2.user_id = ?",
            [$userA, $userB]
        );
        if ($row) return (int)$row['conversation_id'];

        $this->execute('INSERT INTO conversations DEFAULT VALUES');
        $convId = (int)$this->lastInsertId();
        $this->execute('INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)', [$convId, $userA]);
        $this->execute('INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)', [$convId, $userB]);
        return $convId;
    }

    public function getConversations(int $userId): array
    {
        return $this->fetchAll(
            "SELECT c.id AS conversation_id,
                    u.id, u.username, u.first_name, u.last_name, u.avatar, u.is_online,
                    m.content AS last_message,
                    m.created_at AS last_message_at,
                    m.sender_id AS last_sender_id,
                    (SELECT COUNT(*) FROM messages mx
                     WHERE mx.conversation_id = c.id AND mx.sender_id <> ? AND mx.is_read = false) AS unread_count
             FROM conversations c
             JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = ?
             JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id <> ?
             JOIN users u ON u.id = cp2.user_id
             LEFT JOIN LATERAL (
                 SELECT content, created_at, sender_id FROM messages
                 WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1
             ) m ON true
             ORDER BY COALESCE(m.created_at, c.created_at) DESC",
            [$userId, $userId, $userId]
        );
    }

    public function getMessages(int $conversationId, int $limit = 30, int $offset = 0): array
    {
        return $this->fetchAll(
            "SELECT m.*, u.username, u.first_name, u.last_name, u.avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.conversation_id = ?
             ORDER BY m.created_at DESC
             LIMIT ? OFFSET ?",
            [$conversationId, $limit, $offset]
        );
    }

    public function send(int $conversationId, int $senderId, string $content, ?string $image = null): int
    {
        $this->query(
            'INSERT INTO messages (conversation_id, sender_id, content, image) VALUES (?, ?, ?, ?)',
            [$conversationId, $senderId, $content, $image]
        );
        return (int)$this->lastInsertId();
    }

    public function markAsRead(int $conversationId, int $userId): void
    {
        $this->execute(
            "UPDATE messages SET is_read = true WHERE conversation_id = ? AND sender_id <> ? AND is_read = false",
            [$conversationId, $userId]
        );
    }

    public function getTotalUnread(int $userId): int
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM messages m
             JOIN conversation_participants cp ON cp.conversation_id = m.conversation_id AND cp.user_id = ?
             WHERE m.sender_id <> ? AND m.is_read = false",
            [$userId, $userId]
        );
        return $row ? (int)$row['cnt'] : 0;
    }
}
