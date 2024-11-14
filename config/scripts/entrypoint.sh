#!/bin/bash

# Run the Xdebug configuration script
/usr/local/bin/xdebug-config.sh

# Start Apache in foreground mode
echo "Starting Apache..."
exec apache2-foreground

# Setup SSL certificates (will use self-signed for local, Let's Encrypt for prod)
/usr/local/bin/setup-ssl.sh
