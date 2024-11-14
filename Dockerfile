# Use PHP-FPM base image
FROM php:8.2-fpm

# Install Composer
ARG COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install necessary packages and PHP extensions
RUN apt-get update && apt-get install -y \
    nginx supervisor git unzip zip \
    libcurl4-openssl-dev pkg-config libssl-dev \
    && pecl install mongodb xdebug \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies with Composer
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Create self-signed certificate (for development)
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/selfsigned.key \
    -out /etc/ssl/certs/selfsigned.crt \
    -subj "/C=US/ST=Texas/L=Denton/O=TagDenton/OU=Dev/CN=localhost"

# Nginx config
COPY config/nginx/nginx.conf /etc/nginx/nginx.conf

# Copy the Xdebug configuration script
COPY config/scripts/xdebug-config.sh /usr/local/bin/xdebug-config.sh
RUN chmod +x /usr/local/bin/xdebug-config.sh

# Add .ini files
COPY config/php/. /usr/local/etc/php/conf.d/.

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# Copy Supervisor configuration
COPY config/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create Supervisor log directories
RUN mkdir -p /var/log/supervisor /var/run && \
    chown -R www-data:www-data /var/log/supervisor /var/run

# Entrypoint script to handle pre-start tasks and start Supervisor
COPY config/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Use entrypoint script to start services
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]