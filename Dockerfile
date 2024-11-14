# Use PHP-Apache base image
FROM php:8.2-apache

# Install Composer
ARG COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install necessary packages and PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libssl-dev \
    openssl certbot python3-certbot-apache \
    && pecl install mongodb xdebug \
    && docker-php-ext-enable mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies with Composer
RUN composer install --no-dev --optimize-autoloader

# Create SSL directory
RUN mkdir -p /etc/apache2/ssl

# Copy virtual host configuration
COPY config/apache/000-default.conf /etc/apache2/sites-available/
COPY config/apache/default-ssl.conf /etc/apache2/sites-available/

# Enable the configurations
RUN a2ensite 000-default.conf
RUN a2ensite default-ssl.conf

# Enable necessary Apache modules
RUN a2enmod rewrite ssl headers

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Create a self-signed certificate (for development)
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/selfsigned.key \
    -out /etc/ssl/certs/selfsigned.crt \
    -subj "/C=US/ST=Texas/L=Denton/O=TagDenton/OU=Dev/CN=localhost"


# Validate configuration during build
RUN apache2ctl configtest

# Copy the PHP ini configs
COPY config/php/. /usr/local/etc/php/conf.d/

# Copy scripts
COPY config/scripts/xdebug-config.sh /usr/local/bin/
COPY config/scripts/setup-ssl.sh /usr/local/bin/
COPY config/scripts/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

# Expose ports
EXPOSE 80 443

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]