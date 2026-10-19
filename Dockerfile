# Runs the REPENT website + PHP API on any Docker host (used by Render's free web service).
FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers

WORKDIR /var/www/html
COPY . /var/www/html

# The Android project, tests and docs are not needed on the server.
RUN rm -rf android tests docs .github Repent.zip

# Render tells the container which port to listen on through $PORT.
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-80}/; s/:80>/:${PORT:-80}>/\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
