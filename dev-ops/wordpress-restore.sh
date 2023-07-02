#!/usr/bin/env bash
set -Eeuo pipefail

#"noop" apache2 call so preperation work is done correcly by docker-entrypoint of wordpress docker image...
/usr/local/bin/docker-entrypoint.sh apache2ctl configtest

#wp cli commands must be called as www-data user
sudo -E -u www-data /bin/bash << EOF

cd /var/www/html

if ! wp core is-installed; then  
    echo "wordpress is not installed. Setup"
    wp core install --url=$WORDPRESS_HOST --title=wordpress-document-generator-for-openapi \
         --admin_user=$ADMIN_PASS --admin_password=$ADMIN_USER --admin_email=mail@wordpress.local --skip-email
fi
wp rewrite structure '/%postname%/'
wp rewrite flush --hard

wp plugin activate document-generator-for-openapi

EOF


exec "$@"