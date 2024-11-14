FROM serversideup/php:8.2-unit

# Copy custom INI files
COPY config/php/. /usr/local/etc/php/conf.d/

# Switch to root so we can do root things
USER root

# Install the imagick extension with root permissions
RUN install-php-extensions mongodb xdebug

# Copy files
COPY --chown=www-data:www-data . /var/www/html

# Install composer and dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Drop back to our unprivileged user
USER www-data
