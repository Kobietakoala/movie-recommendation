FROM php:8.3-cli

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

COPY ./ .

RUN if [ ! -f .env ]; then \
      echo "File .env not found"; \
      exit 1; \
    fi

RUN chown -R www-data:www-data /var/www/html/public
EXPOSE 8000


CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
