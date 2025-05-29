FROM php:8.2-cli

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копирование файлов проекта
COPY . .

# Установка зависимостей через Composer
RUN composer update --no-scripts \
    && composer install --no-scripts --no-autoloader

# Генерация автозагрузчика
RUN composer dump-autoload --optimize

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8001

CMD ["php", "-S", "0.0.0.0:8001", "public/index.php"] 