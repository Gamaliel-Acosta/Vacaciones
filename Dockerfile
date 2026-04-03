FROM php:8.2-apache

WORKDIR /var/www/html

RUN a2enmod rewrite \
    && docker-php-ext-install pdo pdo_pgsql

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -name "*.php" -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

EXPOSE 80

CMD ["apache2-foreground"]