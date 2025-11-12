# Imagen base con PHP y Apache
FROM php:8.2-apache

# Instalar extensiones de MySQL y dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Configurar Apache para Render
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copiar la aplicación
COPY . /var/www/html/

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Puerto que Render usa
EXPOSE 10000

CMD ["apache2-foreground"]