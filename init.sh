#!/bin/bash

# Создание .env файла
cp .env.example .env

# Установка зависимостей
composer update
composer install

# Запуск Docker контейнеров
docker-compose up --build -d

echo "Проект успешно инициализирован!"
echo "Доступные сервисы:"
echo "- Основное приложение: http://localhost:8000"
echo "- Auth сервис: http://localhost:8001"
echo "- Notification сервис: http://localhost:8002"
echo "- Mailhog (тестирование email): http://localhost:8025" 