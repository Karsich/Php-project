FROM php:8.1-apache

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Включаем mod_rewrite для Apache
RUN a2enmod rewrite

# Копируем конфигурацию Apache
COPY docker/php/apache.conf /etc/apache2/sites-available/000-default.conf

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем composer.json и composer.lock
COPY composer.json composer.lock ./

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем зависимости
RUN composer install --no-scripts --no-autoloader

# Копируем остальные файлы проекта
COPY . .

# Генерируем автозагрузчик
RUN composer dump-autoload --optimize

# Устанавливаем права на директории
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage 