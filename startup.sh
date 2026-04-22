#!/bin/bash
echo "Removing default Azure hosting start page"
rm -f /home/site/wwwroot/hostingstart.html

echo "Applying custom NGINX configuration for Laravel routing"
cp /home/site/wwwroot/default /etc/nginx/sites-available/default

echo "Reloading NGINX"
service nginx reload

echo "Startup complete"
