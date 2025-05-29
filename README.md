# PHP Forum

Форум на PHP с поддержкой тем, постов, комментариев и реакций.

## Требования

- PHP 8.1 или выше
- PostgreSQL 12 или выше
- Composer

## Установка

1. Клонируйте репозиторий:
```bash
git clone [url-репозитория]
cd php-forum
```

2. Установите зависимости:
```bash
composer install
```

3. Создайте файл .env на основе .env.example и настройте параметры подключения к базе данных:
```bash
cp .env.example .env
```

4. Создайте базу данных и импортируйте схему:
```bash
psql -U postgres
CREATE DATABASE forum;
\q
psql -U postgres forum < database/schema.sql
```

5. Запустите встроенный PHP сервер для разработки:
```bash
php -S localhost:8000 -t public
```

## API Endpoints

### Темы

- `GET /topics` - Получить список тем
- `GET /topics/{id}` - Получить тему по ID
- `POST /topics` - Создать новую тему
- `PUT /topics/{id}` - Обновить существующую тему
- `PUT /topics/{id}/pin` - Закрепить/открепить тему
- `PUT /topics/{id}/close` - Закрыть/открыть тему
- `GET /topics/{id}/posts` - Получить посты в теме

### Посты

- `POST /posts` - Создать новый пост в теме
- `GET /posts/{id}` - Получить пост по ID
- `PUT /posts/{id}` - Обновить существующий пост
- `DELETE /posts/{id}` - Удалить пост (мягкое удаление)
- `POST /posts/{id}/reply` - Ответить на пост
- `GET /posts/{id}/replies` - Получить ответы на пост
- `PUT /posts/{id}/react` - Поставить/убрать реакцию на пост

### Структура проекта

```
├── src/
│   ├── Config/
│   │   └── Database.php
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── TopicController.php
│   │   └── PostController.php
│   └── Models/
│       ├── BaseModel.php
│       ├── Topic.php
│       └── Post.php
├── public/
│   └── index.php
├── database/
│   └── schema.sql
├── composer.json
├── .env.example
└── README.md
```

## Особенности

- Древовидная структура постов (поддержка ответов на посты)
- Мягкое удаление постов (с сохранением в базе и отображением "Пост удален")
- Система реакций (лайки с возможностью отмены)
- Закрепление и закрытие тем
- Поддержка PostgreSQL
- JWT аутентификация
- CORS заголовки

## Безопасность

- Все пароли хешируются
- Используется JWT для аутентификации
- Реализована защита от CSRF
- Настроены CORS заголовки

## Лицензия

MIT
