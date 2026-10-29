FROM php:8.2-apache


RUN docker-php-ext-install -j"$(nproc)" pdo_mysql opcache \
    && a2enmod rewrite headers expires \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/arenix.ini"
COPY docker/apache.conf /etc/apache2/conf-enabled/arenix.conf


COPY . /var/www/html
RUN rm -rf docker \
    && mkdir -p public/assets/uploads \
    && chown -R www-data:www-data public/assets/uploads

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS -o /dev/null http://localhost/ || exit 1
