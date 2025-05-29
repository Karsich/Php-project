-- Таблица для реакций (лайков)
CREATE TABLE IF NOT EXISTS post_reactions (
    id SERIAL PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(post_id, user_id)
);

-- Добавляем поле для мягкого удаления постов
ALTER TABLE posts ADD COLUMN IF NOT EXISTS is_deleted BOOLEAN NOT NULL DEFAULT FALSE;

-- Добавляем поле для связи ответов
ALTER TABLE posts ADD COLUMN IF NOT EXISTS reply_to_id INTEGER REFERENCES posts(id) ON DELETE SET NULL; 