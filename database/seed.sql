-- ============================================================
--  Kadrora — Тестовые данные
--  Пароль для всех пользователей: password123
--  bcrypt hash: $2y$12$c7nc7YiBab7f3ZpLdGeVQeuEtFLFCPUYAez/tMBI8E.xllZOVUaUK
-- ============================================================

-- Пользователи
INSERT INTO users (username, email, password_hash, first_name, last_name, city, bio, gender, birth_date) VALUES
('admin',       'admin@kadrora.ru',  '$2y$12$c7nc7YiBab7f3ZpLdGeVQeuEtFLFCPUYAez/tMBI8E.xllZOVUaUK', 'Администратор', 'Kadrora',   'Москва',          'Основатель Kadrora',                    'male',   '1990-01-15'),
('ivan_petrov', 'ivan@kadrora.ru',   '$2y$12$c7nc7YiBab7f3ZpLdGeVQeuEtFLFCPUYAez/tMBI8E.xllZOVUaUK', 'Иван',          'Петров',    'Москва',          'Люблю путешествовать и фотографировать', 'male',   '1995-03-22'),
('anna_k',      'anna@kadrora.ru',   '$2y$12$c7nc7YiBab7f3ZpLdGeVQeuEtFLFCPUYAez/tMBI8E.xllZOVUaUK', 'Анна',          'Козлова',   'Санкт-Петербург', 'Дизайнер интерфейсов',                  'female', '1997-07-10'),
('dima_s',      'dima@kadrora.ru',   '$2y$12$c7nc7YiBab7f3ZpLdGeVQeuEtFLFCPUYAez/tMBI8E.xllZOVUaUK', 'Дмитрий',       'Смирнов',   'Казань',          'Backend-разработчик',                   'male',   '1993-11-05'),
('olga_v',      'olga@kadrora.ru',   '$2y$12$c7nc7YiBab7f3ZpLdGeVQeuEtFLFCPUYAez/tMBI8E.xllZOVUaUK', 'Ольга',         'Васильева', 'Новосибирск',     'Учитель математики',                    'female', '1988-04-30');

-- Дружба (ivan и anna — друзья)
INSERT INTO friendships (requester, addressee, status) VALUES
(2, 3, 'accepted'),
(2, 4, 'accepted'),
(3, 5, 'accepted'),
(4, 5, 'pending');

-- Посты
INSERT INTO posts (user_id, content, privacy) VALUES
(2, 'Привет всем! Я только что зарегистрировался на Kadrora 🎉 Рад быть здесь!', 'public'),
(3, 'Закончила новый дизайн-проект. Очень довольна результатом!', 'public'),
(2, 'Сегодня отличная погода в Москве. Пошёл гулять в парк 🌳', 'friends'),
(4, 'Изучаю PostgreSQL. Очень мощная СУБД, рекомендую!', 'public'),
(5, 'Готовлюсь к новому учебному году. Много работы впереди!', 'public'),
(1, 'Добро пожаловать в Kadrora! Общайтесь, делитесь, будьте собой. ❤️', 'public');

-- Лайки
INSERT INTO post_likes (post_id, user_id) VALUES
(1, 3), (1, 4), (1, 5),
(2, 2), (2, 4),
(4, 2), (4, 3),
(6, 2), (6, 3), (6, 4), (6, 5);

-- Комментарии
INSERT INTO comments (post_id, user_id, content) VALUES
(1, 3, 'Добро пожаловать! Рада видеть тебя здесь 😊'),
(1, 4, 'Отличное начало! Надеюсь, тебе здесь понравится'),
(2, 2, 'Покажи проект, очень интересно!'),
(6, 2, 'Спасибо за платформу! Уже нравится 👍'),
(6, 3, 'Kadrora — лучшая соцсеть!');
