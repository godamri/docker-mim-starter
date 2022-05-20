FROM godamri/docker-mim-starter-php7:0.0.1

USER root

RUN mkdir -p /var/www/app
WORKDIR /var/www/app/

COPY ./etc/mim-installer.sh /tmp
RUN chmod +x /tmp/mim-installer.sh
RUN /tmp/mim-installer.sh

COPY ./appsrc /var/www/app/

# Configure supervisor
RUN mkdir -p /etc/supervisor.d/
COPY ./config/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

COPY ./config/php/www.conf /etc/php7/php-fpm.d/www.conf
COPY ./config/php/php.ini /etc/php7/php-fpm.d/custom.ini

# Configure nginx
COPY ./config/nginx/nginx.conf /etc/nginx/nginx.conf

COPY . /var/www/app/

RUN chown -R petugas.petugas /var/www/app

# Switch to use a non-root user from here on
USER petugas

EXPOSE 8080

# Run app via supervisor
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
