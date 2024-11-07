# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy the project files to the container's web root
COPY . /var/www/html/

# Set the correct permissions for the web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable Apache rewrite and SSL modules
RUN a2enmod rewrite ssl

# Create a self-signed certificate (for development)
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/selfsigned.key \
    -out /etc/ssl/certs/selfsigned.crt \
    -subj "/C=US/ST=Texas/L=Denton/O=TagDenton/OU=Dev/CN=localhost"

# Configure Apache for HTTPS
RUN echo "\n<IfModule mod_ssl.c>\n\
    <VirtualHost _default_:443>\n\
        DocumentRoot /var/www/html\n\
        ServerName localhost\n\
        SSLEngine on\n\
        SSLCertificateFile /etc/ssl/certs/selfsigned.crt\n\
        SSLCertificateKeyFile /etc/ssl/private/selfsigned.key\n\
    </VirtualHost>\n\
</IfModule>" > /etc/apache2/sites-available/default-ssl.conf

# Enable the default SSL site
RUN a2ensite default-ssl

# Expose both HTTP and HTTPS ports
EXPOSE 80 443

# Start the Apache server
CMD ["apache2-foreground"]
