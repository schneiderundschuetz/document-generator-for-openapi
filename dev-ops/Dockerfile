FROM wordpress:php8.2-apache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x /usr/local/bin/wp
RUN apt-get update && apt-get install sudo less unzip



ENTRYPOINT ["/var/www/html/wp-content/plugins/document-generator-for-openapi/dev-ops/wordpress-restore.sh"]
CMD ["apache2-foreground"]