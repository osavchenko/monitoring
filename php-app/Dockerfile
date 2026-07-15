FROM php:8.2-cli

RUN apt-get update && apt-get install -y git unzip libzip-dev \
    && docker-php-ext-install zip \
    && pecl install opentelemetry \
    && docker-php-ext-enable opentelemetry

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json ./
RUN composer install

COPY index.php ./

EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80", "index.php"]
