ALTER TABLE topics ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT NULL;
ALTER TABLE posts ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT NULL; 