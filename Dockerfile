FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

COPY ./composer.json ./composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY ./ .

RUN if [ ! -f .env ]; then \
      return "File .env not found"; \
    fi

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html
EXPOSE 8000


CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
