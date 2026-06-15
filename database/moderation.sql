-- ============================================================
--  Kadrora — Модерация и администрирование
-- ============================================================

-- ---------- РОЛИ И ОГРАНИЧЕНИЯ ПОЛЬЗОВАТЕЛЕЙ ----------
ALTER TABLE users ADD COLUMN IF NOT EXISTS role           VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user','moderator','admin'));
ALTER TABLE users ADD COLUMN IF NOT EXISTS banned_until   TIMESTAMP   DEFAULT NULL;   -- временный бан (NULL = нет/перманент)
ALTER TABLE users ADD COLUMN IF NOT EXISTS ban_reason     TEXT        DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS warnings_count INT         DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_muted       BOOLEAN     DEFAULT FALSE;  -- запрет публиковать
ALTER TABLE users ADD COLUMN IF NOT EXISTS muted_until    TIMESTAMP   DEFAULT NULL;

-- Главный администратор
UPDATE users SET role = 'admin', is_verified = true WHERE username = 'admin';

-- ---------- СТАТУС КОНТЕНТА (мягкая модерация) ----------
-- active — обычный; flagged — на проверке; hidden — скрыт модератором; removed — удалён
ALTER TABLE posts    ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active','flagged','hidden','removed'));
ALTER TABLE comments ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active','flagged','hidden','removed'));

-- ---------- ЖАЛОБЫ ----------
CREATE TABLE IF NOT EXISTS reports (
    id          SERIAL PRIMARY KEY,
    reporter_id INT         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    target_type VARCHAR(20) NOT NULL CHECK (target_type IN ('post','comment','user','group','message')),
    target_id   INT         NOT NULL,
    category    VARCHAR(30) NOT NULL,
    comment     TEXT        DEFAULT NULL,
    status      VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','resolved','rejected')),
    reviewed_by INT         DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
    created_at  TIMESTAMP   DEFAULT NOW(),
    resolved_at TIMESTAMP   DEFAULT NULL,
    UNIQUE (reporter_id, target_type, target_id)   -- одна жалоба от пользователя на объект
);
CREATE INDEX IF NOT EXISTS idx_reports_status ON reports(status);
CREATE INDEX IF NOT EXISTS idx_reports_target ON reports(target_type, target_id);

-- ---------- ЖУРНАЛ ДЕЙСТВИЙ МОДЕРАЦИИ ----------
CREATE TABLE IF NOT EXISTS moderation_log (
    id           SERIAL PRIMARY KEY,
    moderator_id INT         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    action       VARCHAR(40) NOT NULL,
    target_type  VARCHAR(20) DEFAULT NULL,
    target_id    INT         DEFAULT NULL,
    details      TEXT        DEFAULT NULL,
    created_at   TIMESTAMP   DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_modlog_moderator ON moderation_log(moderator_id);

-- ---------- АВТОФИЛЬТР: СТОП-СЛОВА ----------
CREATE TABLE IF NOT EXISTS banned_words (
    id         SERIAL PRIMARY KEY,
    word       VARCHAR(100) NOT NULL UNIQUE,
    action     VARCHAR(10)  DEFAULT 'block' CHECK (action IN ('block','flag')),
    created_at TIMESTAMP    DEFAULT NOW()
);
-- Примеры (демонстрация механизма; реальный список настраивается в админ-панели)
INSERT INTO banned_words (word, action) VALUES
    ('http://spam', 'block'),
    ('казино',      'flag'),
    ('viagra',      'block')
ON CONFLICT (word) DO NOTHING;
