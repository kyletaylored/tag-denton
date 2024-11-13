#!/bin/bash

# Run the Xdebug configuration script
echo "Running Xdebug configuration script..."
/usr/local/bin/xdebug-config.sh

# Execute the main process (e.g., Supervisor)
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
