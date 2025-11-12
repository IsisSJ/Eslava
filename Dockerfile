# Imagen base con PHP
FROM php:8.2-apache

# Copiar el contenido del proyecto al contenedor
COPY . /var/www/html/

# Exponer el puerto estándar de Apache
EXPOSE 80
