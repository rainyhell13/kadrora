-- ============================================================
--  Kadrora Social Network — Database Schema
--  PostgreSQL 15+
-- ============================================================

-- Расширения
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- ============================================================
--  ПОЛЬЗОВАТЕЛИ
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name    VARCHAR(100) NOT NULL,
    last_name     VARCHAR(100) NOT NULL,
    avatar        VARCHAR(255) DEFAULT NULL,
    cover_photo   VARCHAR(255) DEFAULT NULL,
    bio           TEXT         DEFAULT NULL,
    city          VARCHAR(100) DEFAULT NULL,
    birth_date    DATE         DEFAULT NULL,
    gender        VARCHAR(10)  DEFAULT NULL CHECK (gender IN ('male','female','other')),
    website       VARCHAR(255) DEFAULT NULL,
    is_online     BOOLEAN      DEFAULT FALSE,
    last_seen     TIMESTAMP    DEFAULT NOW(),
    is_verified   BOOLEAN      DEFAULT FALSE,
    is_banned     BOOLEAN      DEFAULT FALSE,
    created_at    TIMESTAMP    DEFAULT NOW(),
    updated_at    TIMESTAMP    DEFAULT NOW()
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email    ON users(email);
CREATE INDEX idx_users_online   ON users(is_online);

-- ============================================================
--  ПОСТЫ
-- ============================================================
CREATE TABLE IF NOT EXISTS posts (
    id          SERIAL PRIMARY KEY,
    user_id     INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content     TEXT         NOT NULL,
    image       VARCHAR(255) DEFAULT NULL,
    privacy     VARCHAR(20)  DEFAULT 'public' CHECK (privacy IN ('public','friends','private')),
    likes_count INT          DEFAULT 0,
    views_count INT          DEFAULT 0,
    created_at  TIMESTAMP    DEFAULT NOW(),
    updated_at  TIMESTAMP    DEFAULT NOW()
);

CREATE INDEX idx_posts_user_id    ON posts(user_id);
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
CREATE INDEX idx_posts_privacy    ON posts(privacy);

-- ============================================================
--  ЛАЙКИ ПОСТОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS post_likes (
    id         SERIAL PRIMARY KEY,
    post_id    INT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (post_id, user_id)
);

CREATE INDEX idx_post_likes_post ON post_likes(post_id);
CREATE INDEX idx_post_likes_user ON post_likes(user_id);

-- ============================================================
--  КОММЕНТАРИИ
-- ============================================================
CREATE TABLE IF NOT EXISTS comments (
    id         SERIAL PRIMARY KEY,
    post_id    INT  NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id    INT  NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    parent_id  INT  DEFAULT NULL REFERENCES comments(id) ON DELETE CASCADE,
    content    TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_comments_post_id ON comments(post_id);
CREATE INDEX idx_comments_user_id ON comments(user_id);

-- ============================================================
--  ЛАЙКИ КОММЕНТАРИЕВ
-- ============================================================
CREATE TABLE IF NOT EXISTS comment_likes (
    id         SERIAL PRIMARY KEY,
    comment_id INT NOT NULL REFERENCES comments(id) ON DELETE CASCADE,
    user_id    INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (comment_id, user_id)
);

-- ============================================================
--  ДРУЗЬЯ
-- ============================================================
CREATE TABLE IF NOT EXISTS friendships (
    id          SERIAL PRIMARY KEY,
    requester   INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    addressee   INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status      VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','accepted','declined','blocked')),
    created_at  TIMESTAMP DEFAULT NOW(),
    updated_at  TIMESTAMP DEFAULT NOW(),
    UNIQUE (requester, addressee),
    CHECK (requester <> addressee)
);

CREATE INDEX idx_friendships_requester ON friendships(requester);
CREATE INDEX idx_friendships_addressee ON friendships(addressee);
CREATE INDEX idx_friendships_status    ON friendships(status);

-- ============================================================
--  ЛИЧНЫЕ СООБЩЕНИЯ
-- ============================================================
CREATE TABLE IF NOT EXISTS conversations (
    id         SERIAL PRIMARY KEY,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS conversation_participants (
    conversation_id INT NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    user_id         INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    PRIMARY KEY (conversation_id, user_id)
);

CREATE TABLE IF NOT EXISTS messages (
    id              SERIAL PRIMARY KEY,
    conversation_id INT  NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    sender_id       INT  NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content         TEXT NOT NULL,
    image           VARCHAR(255) DEFAULT NULL,
    is_read         BOOLEAN   DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_messages_conversation ON messages(conversation_id);
CREATE INDEX idx_messages_sender       ON messages(sender_id);
CREATE INDEX idx_messages_created_at   ON messages(created_at DESC);

-- ============================================================
--  УВЕДОМЛЕНИЯ
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id          SERIAL PRIMARY KEY,
    user_id     INT         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    actor_id    INT         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type        VARCHAR(50) NOT NULL,
    entity_id   INT         DEFAULT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    message     TEXT        NOT NULL,
    is_read     BOOLEAN     DEFAULT FALSE,
    created_at  TIMESTAMP   DEFAULT NOW()
);

CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);

-- ============================================================
--  ФОТОАЛЬБОМЫ
-- ============================================================
CREATE TABLE IF NOT EXISTS photos (
    id          SERIAL PRIMARY KEY,
    user_id     INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    filename    VARCHAR(255) NOT NULL,
    caption     TEXT         DEFAULT NULL,
    post_id     INT          DEFAULT NULL REFERENCES posts(id) ON DELETE SET NULL,
    created_at  TIMESTAMP    DEFAULT NOW()
);

CREATE INDEX idx_photos_user_id ON photos(user_id);

-- ============================================================
--  ТРИГГЕРЫ — автообновление likes_count
-- ============================================================
CREATE OR REPLACE FUNCTION update_post_likes_count()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE posts SET likes_count = likes_count + 1 WHERE id = NEW.post_id;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = OLD.post_id;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_post_likes_count
AFTER INSERT OR DELETE ON post_likes
FOR EACH ROW EXECUTE FUNCTION update_post_likes_count();

-- Автообновление updated_at
CREATE OR REPLACE FUNCTION touch_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated_at   BEFORE UPDATE ON users       FOR EACH ROW EXECUTE FUNCTION touch_updated_at();
CREATE TRIGGER trg_posts_updated_at   BEFORE UPDATE ON posts       FOR EACH ROW EXECUTE FUNCTION touch_updated_at();
CREATE TRIGGER trg_comments_updated_at BEFORE UPDATE ON comments   FOR EACH ROW EXECUTE FUNCTION touch_updated_at();
CREATE TRIGGER trg_friends_updated_at BEFORE UPDATE ON friendships FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

-- ============================================================
--  ПОЛНОТЕКСТОВЫЙ ПОИСК
-- ============================================================
ALTER TABLE users ADD COLUMN IF NOT EXISTS search_vector TSVECTOR;

CREATE OR REPLACE FUNCTION update_user_search_vector()
RETURNS TRIGGER AS $$
BEGIN
    NEW.search_vector = to_tsvector('russian',
        coalesce(NEW.first_name,'') || ' ' ||
        coalesce(NEW.last_name,'')  || ' ' ||
        coalesce(NEW.username,'')   || ' ' ||
        coalesce(NEW.city,'')
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_search_vector
BEFORE INSERT OR UPDATE ON users
FOR EACH ROW EXECUTE FUNCTION update_user_search_vector();

CREATE INDEX idx_users_fts ON users USING gin(search_vector);
