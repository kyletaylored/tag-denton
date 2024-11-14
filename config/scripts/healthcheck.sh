#!/bin/bash

# Check if Apache is running
if ! pgrep apache2 > /dev/null; then
  echo "Apache is not running"
  exit 1
fi

# Test HTTP response
HTTP_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:$HTTP_PORT)

if [ "$HTTP_RESPONSE" -ne 200 ]; then
  echo "Health check failed with HTTP response: $HTTP_RESPONSE"
  exit 1
fi

echo "Health check passed"
exit 0
