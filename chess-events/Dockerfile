FROM php:8.2-apache

# Instalar extensiones necesarias para conectar PHP con PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar todo tu proyecto al servidor web
COPY . /var/www/html/

# Dar permisos de lectura
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80