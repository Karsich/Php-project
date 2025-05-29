FROM php:8.2-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Создание директории для логов
RUN mkdir -p /var/log && \
    touch /var/log/php_errors.log && \
    chown www-data:www-data /var/log/php_errors.log

# Копирование php.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

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

EXPOSE 9000

CMD ["php-fpm"] 