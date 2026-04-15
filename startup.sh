#!/bin/bash
echo "Removing default Azure hosting start page"
rm -f /home/site/wwwroot/hostingstart.html

echo "Updating NGINX root to serve Laravel public folder"
cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.bak
sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/public;|g' /etc/nginx/sites-available/default

echo "Reloading NGINX"
service nginx reload

echo "Startup complete"
