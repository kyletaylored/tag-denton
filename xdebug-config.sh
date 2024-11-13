#!/bin/bash

if [ "$ENABLE_XDEBUG" = "1" ]; then
    echo "Enabling Xdebug..."
    echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini
    echo "xdebug.mode=develop,coverage,debug,profile" >> /usr/local/etc/php/php.ini
    echo "xdebug.client_host=${XDEBUG_CLIENT_HOST:-host.docker.internal}" >> /usr/local/etc/php/php.ini
    echo "xdebug.client_port=${XDEBUG_CLIENT_PORT:-9003}" >> /usr/local/etc/php/php.ini
    echo "xdebug.log=/dev/stdout" >> /usr/local/etc/php/php.ini
    echo "xdebug.log_level=0" >> /usr/local/etc/php/php.ini
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/php.ini
    echo "xdebug.idekey=VSCODE" >> /usr/local/etc/php/php.ini
else
    echo "Xdebug is disabled."
fi

# Start Apache
exec "$@"
