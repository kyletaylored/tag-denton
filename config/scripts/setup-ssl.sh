#!/bin/bash
# config/scripts/setup-ssl.sh

# Check if we're in production (DOMAIN is set)
if [ -n "$DOMAIN" ]; then
    echo "Production environment detected. Setting up Let's Encrypt certificates..."
    
    # Remove www. if present to get base domain
    BASE_DOMAIN=${DOMAIN#www.}
    
    # Request certificate for both www and non-www
    certbot --apache \
        --non-interactive \
        --agree-tos \
        --email ${SSL_EMAIL:-webmaster@$BASE_DOMAIN} \
        -d $BASE_DOMAIN \
        -d www.$BASE_DOMAIN \
        --keep-until-expiring
    
    # Update Apache SSL configuration to use Let's Encrypt certificates
    sed -i "s|SSLCertificateFile.*|SSLCertificateFile /etc/letsencrypt/live/$BASE_DOMAIN/fullchain.pem|" /etc/apache2/sites-available/default-ssl.conf
    sed -i "s|SSLCertificateKeyFile.*|SSLCertificateKeyFile /etc/letsencrypt/live/$BASE_DOMAIN/privkey.pem|" /etc/apache2/sites-available/default-ssl.conf
    
    # Set up auto-renewal
    echo "0 */12 * * * root certbot renew --quiet" > /etc/cron.d/certbot-renew
else
    echo "Development environment detected. Using self-signed certificates..."
fi

# Restart Apache to apply any changes
apache2ctl graceful