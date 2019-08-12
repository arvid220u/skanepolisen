FROM php:7.3-apache
RUN a2enmod rewrite
COPY website/ /var/www/html/
RUN docker-php-ext-install mysqli
# ADD https://github.com/sendgrid/sendgrid-php/releases/download/v7.3.0/sendgrid-php.zip /var/www/html/sendgrid/
RUN apt-get update && apt-get install -y wget unzip \
    && cd .. \
    && wget https://github.com/sendgrid/sendgrid-php/releases/download/v7.3.0/sendgrid-php.zip sendgrid-php.zip \
    && unzip sendgrid-php.zip