#!/bin/bash

# Ждем доступности базы данных
until nc -z -v -w30 db 5432
do
  echo "Waiting for database connection..."
  sleep 5
done

# Запускаем PHP-FPM
php-fpm 