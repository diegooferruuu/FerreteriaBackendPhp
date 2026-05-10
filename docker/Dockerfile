FROM php:8.2-apache

  #      libapache2-mod-php \\
RUN apt-get update && \
	apt-get install -y \
    apache2 \
    libapache2-mod-fcgid \
	build-essential \
	libzip-dev \
    libpng-dev \
    libgd-dev \
    libonig-dev \
	libjpeg62-turbo-dev \
	libfreetype6-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libpq-dev \
    supervisor \
	zip \
	unzip \
	git \
	nano \
	curl \
	vim \
	wget

#extensioens de php
RUN docker-php-ext-install zip gd mbstring curl bcmath soap xml pdo pdo_pgsql pgsql
# Copy virtual host into container
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
# Enable rewrite mode
RUN a2enmod rewrite
# copy config supervisor
COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf


#instalacion de componser
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./php.ini /usr/local/etc/php/
  #--no-dev
#RUN cd /var/www/html/facturacion && composer install --dev --no-interaction --optimize-autoloader
#EXPOSE  80
# Change the current working directory
WORKDIR /var/www/html

RUN chown -Rf www-data:www-data /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
# Start Apache in foreground
#CMD apache2-foreground ; /usr/bin/supervisord, -c, /etc/supervisor/supervisord.conf
#CMD service apache2 restart
