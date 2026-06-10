-- Выполните этот файл ПЕРВЫМ в pgAdmin, подключившись к базе postgres
-- Создаёт базу данных kadrora

CREATE DATABASE kadrora
    WITH
    OWNER = postgres
    ENCODING = 'UTF8'
    LC_COLLATE = 'Russian_Russia.1251'
    LC_CTYPE = 'Russian_Russia.1251'
    TEMPLATE = template0
    CONNECTION LIMIT = -1;
