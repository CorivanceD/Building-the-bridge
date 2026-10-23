ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-apache

RUN case "$PHP_VERSION" in \
    5.*) \
      printf 'deb [trusted=yes] http://archive.debian.org/debian stretch main\ndeb [trusted=yes] http://archive.debian.org/debian-security stretch/updates main\n' > /etc/apt/sources.list \
      && apt-get -o Acquire::Check-Valid-Until=false update \
      && apt-get install -y --no-install-recommends \
         libfreetype6-dev libjpeg62-turbo-dev libpng-dev zlib1g-dev msmtp openssl \
      && (apt-get install -y --no-install-recommends libzip-dev || true) \
      && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ \
      && docker-php-ext-install mysqli gd zip ;; \
    *) \
      apt-get update \
      && apt-get install -y --no-install-recommends \
         libfreetype6-dev libjpeg62-turbo-dev libpng-dev zlib1g-dev libzip-dev msmtp openssl \
      && docker-php-ext-configure gd --with-freetype --with-jpeg \
      && docker-php-ext-install mysqli gd zip opcache ;; \
    esac \
 && a2enmod rewrite headers expires ssl \
 && mkdir -p /etc/apache2/ssl \
 && openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -subj /CN=localhost \
    -keyout /etc/apache2/ssl/localhost.key -out /etc/apache2/ssl/localhost.crt \
 && (a2dissite default-ssl || true) \
 && rm -rf /var/lib/apt/lists/*

COPY docker/apache/servername.conf /etc/apache2/conf-available/servername.conf
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enconf servername
COPY docker/php/php.ini /usr/local/etc/php/conf.d/btb.ini
COPY docker/php/msmtprc /etc/msmtprc
RUN chmod 644 /etc/msmtprc

RUN mkdir -p /var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html
WORKDIR /var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html
EXPOSE 80 443
