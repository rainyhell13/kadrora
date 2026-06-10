-- ============================================================
--  Kadrora — Закладки, Документы (и удаление аудио)
-- ============================================================

-- Аудиозаписи больше не используются
DROP TABLE IF EXISTS audios CASCADE;

-- ---------- ЗАКЛАДКИ ----------
-- Сохранённые записи пользователя
CREATE TABLE IF NOT EXISTS bookmarks (
    id         SERIAL PRIMARY KEY,
    user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    post_id    INT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (user_id, post_id)
);
CREATE INDEX IF NOT EXISTS idx_bookmarks_user ON bookmarks(user_id);
CREATE INDEX IF NOT EXISTS idx_bookmarks_post ON bookmarks(post_id);

-- ---------- ДОКУМЕНТЫ ----------
CREATE TABLE IF NOT EXISTS documents (
    id            SERIAL PRIMARY KEY,
    user_id       INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title         VARCHAR(200) NOT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    ext           VARCHAR(20)  NOT NULL,
    size_bytes    BIGINT       NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_documents_user ON documents(user_id);
