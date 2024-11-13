# Use the official PHP image with Apache
FROM php:8.2-apache

# Install Composer
ARG COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies for Composer, PHP extensions, and Xdebug
RUN apt-get update && \
    apt-get install -y git unzip zip libcurl4-openssl-dev pkg-config libssl-dev && \
    pecl install mongodb xdebug && \
    docker-php-ext-enable mongodb && \
    docker-php-ext-install pdo pdo_mysql && \
    rm -rf /var/lib/apt/lists/*

# Copy the project files
COPY . /var/www/html/

# Install PHP dependencies with Composer
RUN composer install

# Set up permissions for the web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable Apache rewrite and SSL modules
RUN a2enmod rewrite ssl

# Create a self-signed certificate (for development)
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/selfsigned.key \
    -out /etc/ssl/certs/selfsigned.crt \
    -subj "/C=US/ST=Texas/L/Denton/O=TagDenton/OU=Dev/CN=localhost"

# Configure Apache for HTTPS and custom 404 page
RUN echo "\n<IfModule mod_ssl.c>\n\
    <VirtualHost _default_:443>\n\
    DocumentRoot /var/www/html\n\
    ServerName localhost\n\
    SSLEngine on\n\
    SSLCertificateFile /etc/ssl/certs/selfsigned.crt\n\
    SSLCertificateKeyFile /etc/ssl/private/selfsigned.key\n\
    ErrorDocument 404 /404.html\n\
    </VirtualHost>\n\
    </IfModule>" > /etc/apache2/sites-available/default-ssl.conf

# Also configure 404 for the HTTP virtual host
RUN echo "\n<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    ServerName localhost\n\
    ErrorDocument 404 /404.html\n\
    </VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

# Enable the default SSL site and rewrite module
RUN a2ensite default-ssl && a2enmod rewrite

# Expose both HTTP and HTTPS ports
EXPOSE 80 443

# Copy dynamic Xdebug configuration script
COPY xdebug-config.sh /usr/local/bin/xdebug-config.sh
RUN chmod +x /usr/local/bin/xdebug-config.sh

# Set the PHP error log file to the standard error log
RUN echo "error_log=/dev/stderr" >> /usr/local/etc/php/conf.d/error_logging.ini

# Configure Apache to log to stdout/stderr
RUN sed -i 's/ErrorLog \/var\/log\/apache2\/error.log/ErrorLog \/dev\/stderr/g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/ErrorLog \/var\/log\/apache2\/error.log/ErrorLog \/dev\/stderr/g' /etc/apache2/sites-available/default-ssl.conf && \
    sed -i 's/CustomLog \/var\/log\/apache2\/access.log combined/CustomLog \/dev\/stdout combined/g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/CustomLog \/var\/log\/apache2\/access.log combined/CustomLog \/dev\/stdout combined/g' /etc/apache2/sites-available/default-ssl.conf

# Start the container using the dynamic Xdebug script
ENTRYPOINT ["/usr/local/bin/xdebug-config.sh"]
CMD ["apache2-foreground"]
