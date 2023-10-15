FROM php:8.2-apache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get upgrade -y && apt-get install nano wget libpq-dev libzip-dev unzip libfreetype6-dev libjpeg62-turbo-dev libpng-dev -y && docker-php-ext-install bcmath pdo_pgsql pdo_mysql mysqli pgsql exif zip && docker-php-ext-enable exif

RUN apt-get install cron -y \
    && echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" >> /etc/cron.d/scheduler \
    && chmod 644 /etc/cron.d/scheduler \
    && crontab /etc/cron.d/scheduler

RUN chmod -R 777 /var/www/html && rm /etc/localtime && ln -s /usr/share/zoneinfo/America/Bahia /etc/localtime && echo "America/Bahia" > /etc/timezone 

EXPOSE 80 8000