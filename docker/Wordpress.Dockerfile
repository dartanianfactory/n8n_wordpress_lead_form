FROM wordpress:6.0-php8.0

RUN apt-get update && apt-get install -y \
    less \
    && rm -rf /var/lib/apt/lists/*

COPY ./wordpress/plugins/ /var/www/html/wp-content/plugins/

RUN chown -R www-data:www-data /var/www/html/wp-content

RUN { \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
} > /usr/local/etc/php/conf.d/wordpress.ini

WORKDIR /var/www/html