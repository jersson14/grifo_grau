FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql mysqli zip \
    && a2enmod rewrite headers \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configuración de producción: los avisos de PHP van al registro, nunca a la página.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'date.timezone = America/Lima'; \
        echo 'expose_php = Off'; \
        echo 'upload_max_filesize = 8M'; \
        echo 'post_max_size = 10M'; \
        echo 'memory_limit = 256M'; \
        echo 'session.cookie_httponly = 1'; \
        echo 'session.use_strict_mode = 1'; \
    } > "$PHP_INI_DIR/conf.d/grifo.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html/

# vendor/ viene en el repositorio; solo se instala si falta.
RUN if [ ! -f vendor/autoload.php ]; then composer install --no-dev --optimize-autoloader; fi \
    && chown -R www-data:www-data /var/www/html

COPY scripts/docker-entrypoint.sh /usr/local/bin/grifo-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/grifo-entrypoint && chmod +x /usr/local/bin/grifo-entrypoint

EXPOSE 80
ENTRYPOINT ["grifo-entrypoint"]
CMD ["apache2-foreground"]
