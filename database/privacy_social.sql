-- ============================================================
--  Kadrora — Приватность, закладки людей, заявки в группы
-- ============================================================

-- ---------- НАСТРОЙКИ ПРИВАТНОСТИ ПОЛЬЗОВАТЕЛЯ ----------
-- Значения: 'all' (все), 'friends' (только друзья), 'nobody' (никто)
ALTER TABLE users ADD COLUMN IF NOT EXISTS privacy_profile  VARCHAR(10) DEFAULT 'all'     CHECK (privacy_profile  IN ('all','friends'));
ALTER TABLE users ADD COLUMN IF NOT EXISTS privacy_wall     VARCHAR(10) DEFAULT 'friends' CHECK (privacy_wall     IN ('all','friends','nobody'));
ALTER TABLE users ADD COLUMN IF NOT EXISTS privacy_messages VARCHAR(10) DEFAULT 'all'     CHECK (privacy_messages IN ('all','friends'));
ALTER TABLE users ADD COLUMN IF NOT EXISTS privacy_friends  VARCHAR(10) DEFAULT 'all'     CHECK (privacy_friends  IN ('all','friends'));
ALTER TABLE users ADD COLUMN IF NOT EXISTS privacy_photos   VARCHAR(10) DEFAULT 'all'     CHECK (privacy_photos   IN ('all','friends'));

-- ---------- ЗАКЛАДКИ ЛЮДЕЙ ----------
CREATE TABLE IF NOT EXISTS user_bookmarks (
    id          SERIAL PRIMARY KEY,
    user_id     INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    target_id   INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at  TIMESTAMP DEFAULT NOW(),
    UNIQUE (user_id, target_id),
    CHECK (user_id <> target_id)
);
CREATE INDEX IF NOT EXISTS idx_user_bookmarks_user ON user_bookmarks(user_id);

-- ---------- ЗАЯВКИ НА ВСТУПЛЕНИЕ В ПРИВАТНЫЕ ГРУППЫ ----------
CREATE TABLE IF NOT EXISTS group_join_requests (
    id          SERIAL PRIMARY KEY,
    group_id    INT NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    user_id     INT NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    status      VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','accepted','declined')),
    created_at  TIMESTAMP DEFAULT NOW(),
    UNIQUE (group_id, user_id)
);
CREATE INDEX IF NOT EXISTS idx_gjr_group ON group_join_requests(group_id);
CREATE INDEX IF NOT EXISTS idx_gjr_user  ON group_join_requests(user_id);
