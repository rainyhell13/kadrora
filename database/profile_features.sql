-- ============================================================
--  Kadrora — Расширение профиля: статус, анкета, стена, видео
--  Стена, статус, расширенная анкета, аудио, видео
-- ============================================================

-- ---------- СТАТУС + РАСШИРЕННАЯ АНКЕТА ----------
ALTER TABLE users ADD COLUMN IF NOT EXISTS status        VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS relationship  VARCHAR(30)  DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS interests     TEXT         DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS fav_music     TEXT         DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS fav_films     TEXT         DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS fav_books     TEXT         DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS fav_games     TEXT         DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS fav_quotes    TEXT         DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS activities    TEXT         DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS life_main     VARCHAR(50)  DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS people_main   VARCHAR(50)  DEFAULT NULL;

-- ---------- СТЕНА ----------
-- Владелец стены, на которой опубликована запись.
-- Если wall_owner_id = user_id — запись на собственной стене.
-- Если отличается — кто-то написал на стене другого пользователя.
ALTER TABLE posts ADD COLUMN IF NOT EXISTS wall_owner_id INT REFERENCES users(id) ON DELETE CASCADE;
UPDATE posts SET wall_owner_id = user_id WHERE wall_owner_id IS NULL;

CREATE INDEX IF NOT EXISTS idx_posts_wall_owner ON posts(wall_owner_id);

-- ---------- ВИДЕОЗАПИСИ ----------
CREATE TABLE IF NOT EXISTS videos (
    id         SERIAL PRIMARY KEY,
    user_id    INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title      VARCHAR(200) NOT NULL,
    filename   VARCHAR(255) NOT NULL,
    thumbnail  VARCHAR(255) DEFAULT NULL,
    views      INT          DEFAULT 0,
    created_at TIMESTAMP    DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_videos_user ON videos(user_id);

-- ---------- Тестовые данные ----------
UPDATE users SET status = 'Добро пожаловать в Kadrora!' WHERE username = 'admin';
UPDATE users SET status = 'Путешествую по миру 🌍', relationship = 'in_search'
  WHERE username = 'ivan_petrov';
UPDATE users SET status = 'Творю и вдохновляюсь ✨', relationship = 'married',
  interests = 'Дизайн, искусство, фотография',
  fav_music = 'Indie, lo-fi, джаз',
  fav_films = 'Амели, Большой Лебовски',
  fav_books = 'Маленький принц, Мастер и Маргарита'
  WHERE username = 'anna_k';
