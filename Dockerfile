FROM php:7.3-apache
RUN a2enmod rewrite
COPY website/ /var/www/html/
RUN docker-php-ext-install mysqli