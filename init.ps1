# Создание .env файла
Copy-Item .env.example .env -Force

# Установка зависимостей
composer update
composer install

# Запуск Docker контейнеров
docker-compose up --build -d

Write-Host "Проект успешно инициализирован!"
Write-Host "Доступные сервисы:"
Write-Host "- Основное приложение: http://localhost:8000"
Write-Host "- Auth сервис: http://localhost:8001"
Write-Host "- Notification сервис: http://localhost:8002"
Write-Host "- Mailhog (тестирование email): http://localhost:8025" 