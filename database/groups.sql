-- ============================================================
--  Kadrora — Groups (Сообщества)
-- ============================================================

CREATE TABLE IF NOT EXISTS groups (
    id          SERIAL PRIMARY KEY,
    owner_id    INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name        VARCHAR(150) NOT NULL,
    slug        VARCHAR(150) NOT NULL UNIQUE,
    description TEXT         DEFAULT NULL,
    avatar      VARCHAR(255) DEFAULT NULL,
    cover       VARCHAR(255) DEFAULT NULL,
    privacy     VARCHAR(20)  DEFAULT 'public' CHECK (privacy IN ('public','private')),
    members_count INT        DEFAULT 0,
    posts_count   INT        DEFAULT 0,
    created_at  TIMESTAMP    DEFAULT NOW(),
    updated_at  TIMESTAMP    DEFAULT NOW()
);

CREATE INDEX idx_groups_owner  ON groups(owner_id);
CREATE INDEX idx_groups_slug   ON groups(slug);

CREATE TABLE IF NOT EXISTS group_members (
    id         SERIAL PRIMARY KEY,
    group_id   INT NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    user_id    INT NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    role       VARCHAR(20) DEFAULT 'member' CHECK (role IN ('member','moderator','admin')),
    joined_at  TIMESTAMP DEFAULT NOW(),
    UNIQUE (group_id, user_id)
);

CREATE INDEX idx_group_members_group ON group_members(group_id);
CREATE INDEX idx_group_members_user  ON group_members(user_id);

CREATE TABLE IF NOT EXISTS group_posts (
    id         SERIAL PRIMARY KEY,
    group_id   INT  NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    user_id    INT  NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    content    TEXT NOT NULL,
    image      VARCHAR(255) DEFAULT NULL,
    likes_count INT  DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_group_posts_group ON group_posts(group_id);
CREATE INDEX idx_group_posts_user  ON group_posts(user_id);

CREATE TABLE IF NOT EXISTS group_post_likes (
    id         SERIAL PRIMARY KEY,
    post_id    INT NOT NULL REFERENCES group_posts(id) ON DELETE CASCADE,
    user_id    INT NOT NULL REFERENCES users(id)       ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (post_id, user_id)
);

-- Триггеры счётчиков
CREATE OR REPLACE FUNCTION update_group_members_count()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE groups SET members_count = members_count + 1 WHERE id = NEW.group_id;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE groups SET members_count = GREATEST(members_count - 1, 0) WHERE id = OLD.group_id;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_group_members_count
AFTER INSERT OR DELETE ON group_members
FOR EACH ROW EXECUTE FUNCTION update_group_members_count();

CREATE OR REPLACE FUNCTION update_group_post_likes_count()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE group_posts SET likes_count = likes_count + 1 WHERE id = NEW.post_id;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE group_posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = OLD.post_id;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_group_post_likes_count
AFTER INSERT OR DELETE ON group_post_likes
FOR EACH ROW EXECUTE FUNCTION update_group_post_likes_count();

CREATE TRIGGER trg_groups_updated_at BEFORE UPDATE ON groups FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

-- Тестовые группы
INSERT INTO groups (owner_id, name, slug, description, privacy) VALUES
(1, 'Kadrora Official', 'kadrora_official', 'Официальное сообщество Kadrora. Новости, обновления, анонсы.', 'public'),
(2, 'Путешественники', 'travelers', 'Делимся фото и историями из путешествий по всему миру!', 'public'),
(4, 'Разработчики', 'developers', 'Сообщество программистов. PHP, PostgreSQL, JS и всё что связано с кодом.', 'public');

INSERT INTO group_members (group_id, user_id, role) VALUES
(1, 1, 'admin'), (1, 2, 'member'), (1, 3, 'member'), (1, 4, 'member'), (1, 5, 'member'),
(2, 2, 'admin'), (2, 3, 'member'), (2, 5, 'member'),
(3, 4, 'admin'), (3, 2, 'member');

INSERT INTO group_posts (group_id, user_id, content) VALUES
(1, 1, 'Добро пожаловать в официальное сообщество Kadrora! Здесь вы найдёте все новости о платформе. Рады видеть вас! 🎉'),
(2, 2, 'Только что вернулся из Санкт-Петербурга. Потрясающий город, особенно белые ночи! Рекомендую всем 🌃'),
(3, 4, 'Кто использует PostgreSQL в продакшене? Поделитесь опытом оптимизации запросов!');
