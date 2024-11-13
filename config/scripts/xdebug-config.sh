#!/bin/bash

# Enable Xdebug if the environment variable is set
if [ "$ENABLE_XDEBUG" = "1" ]; then
    echo "Enabling Xdebug..."
    cp /usr/local/etc/php/conf.d/xdebug.ini.disabled /usr/local/etc/php/conf.d/xdebug.ini
else
    echo "Disabling Xdebug..."
    rm -f /usr/local/etc/php/conf.d/xdebug.ini
fi