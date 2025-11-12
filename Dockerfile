# Imagen base con PHP y Apache
FROM php:8.2-apache

# Copiar el contenido del proyecto al contenedor
COPY . /var/www/html/

# Configurar Apache para escuchar el puerto dinámico de Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf && \
    sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Añadir ServerName para evitar advertencias
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Exponer el puerto que Render asignará
EXPOSE ${PORT}

# Mantener Apache corriendo en primer plano
CMD ["apache2-foreground"]
