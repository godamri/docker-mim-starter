FROM php:8.1.5-alpine

RUN mkdir -p /var/www/app
WORKDIR /var/www/app/

RUN echo "UTC" > /etc/timezone

#RUN apk add --no-cache \
RUN apk add \
    php8 \
    php8-common \
    php8-pdo \
    php8-opcache \
    php8-zip \
    php8-phar \
    php8-iconv \
    php8-cli \
    php8-curl \
    php8-openssl \
    php8-mbstring \
    php8-fileinfo \
    php8-json \
    php8-xml \
    php8-xmlwriter \
    php8-simplexml \
    php8-dom \
    php8-pdo_sqlite \
    php8-tokenizer \
    php8-gd \
    php8-pecl-redis \
    php8-posix \
    zip \
    unzip \
    curl \
    supervisor \
    busybox-suid \
    libpng-dev \
    git


RUN set -ex \
    && apk --no-cache add postgresql-libs postgresql-dev \
    && docker-php-ext-install pcntl pgsql pdo_pgsql pdo_mysql \
    && apk del postgresql-dev

RUN \
    curl -sfL https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
    chmod +x /usr/bin/composer                                                                     && \
    composer self-update --clean-backups 2.1.14

# Configure supervisor
RUN mkdir -p /etc/supervisor.d/
COPY ./deploy/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
# COPY ./deploy/supervisor/conf.d/worker.conf /etc/supervisor/conf.d/worker.conf

COPY ./deploy/php/www.conf /etc/php7/php-fpm.d/www.conf
COPY ./deploy/php/php.ini /etc/php7/conf.d/custom.ini

# Configure nginx
# RUN echo "daemon off;" >> /etc/nginx/nginx.conf
COPY ./deploy/nginx/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /run/nginx/
RUN touch /run/nginx/nginx.pid

RUN ln -sf /dev/stdout /var/log/nginx/access.log
RUN ln -sf /dev/stderr /var/log/nginx/error.log


# Building process
# COPY --chown=nobody . /var/www/app/
COPY . /var/www/app/
RUN composer install --no-dev

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/app && \
    chown -R nobody.nobody /run && \
    chown -R nobody.nobody /var/lib/nginx && \
    chown -R nobody.nobody /var/log/nginx

# Switch to use a non-root user from here on
USER nobody

EXPOSE 8080

# Run app via supervisor
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
# HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping
