# Для начала указываем исходный образ, он будет использован как основа
FROM --platform=linux/amd64 devilbox/php-fpm-8.1:latest


RUN apt-get update && apt-get install -y \
       curl \
               wget \
               git \
               iputils-ping \
               netcat \
               libfreetype6-dev \
               libjpeg62-turbo-dev \
               libpng-dev \
               libonig-dev \
               libzip-dev

RUN docker-php-ext-install iconv mbstring mysqli pdo_mysql zip

# Куда же без composer'а.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Указываем рабочую директорию для PHP
WORKDIR /var/www

# COPY ./ .

# RUN composer install --optimize-autoloader --no-interaction --no-progress && rm -rf /var/www/.composer/cache

# ADD ./.env.example .env

# RUN php artisan key:generate
#RUN php artisan storage:link

# RUN chown www-data:www-data /var/www/
# RUN chown -R www-data:www-data /var/www/storage
# RUN chmod -R a+w /var/www/storage

# ADD ./entrypoint.sh /entrypoint.sh
# RUN ["chmod", "a+w", "/entrypoint.sh"]

# USER root

# EXPOSE 9000
# Запускаем контейнер
# Из документации: The main purpose of a CMD is to provide defaults for an executing container. These defaults can include an executable,
# or they can omit the executable, in which case you must specify an ENTRYPOINT instruction as well.
# ENTRYPOINT ["bash","/entrypoint.sh"]
