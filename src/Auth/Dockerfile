FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/auth

# Create public directory
RUN mkdir -p public

# Copy auth service files
COPY src/Auth ./src/Auth
COPY src/Core/Models/User.php ./src/Core/Models/User.php
COPY src/Db ./src/Db
COPY composer.json ./

# Install dependencies
RUN composer install --no-scripts --no-dev

# Generate autoloader
RUN composer dump-autoload --optimize

# Change ownership
RUN chown -R www-data:www-data /var/www/auth 