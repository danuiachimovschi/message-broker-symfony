FROM php:8.2-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /var/symfony-rabbit

# Copy project files
COPY ../.. /var/symfony-rabbit

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    librabbitmq-dev \
    redis \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install ext-rdkafka PHP
RUN apt-get update && apt-get install -y \
    librdkafka-dev \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Redis Server and PHP Redis extension
RUN apt-get update && apt-get install -y redis \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Symfony dependencies
RUN composer install --no-scripts --no-autoloader

# Autoload and cache
RUN composer dump-autoload --optimize && composer run-script post-install-cmd

# Permissions
RUN chown -R www-data:www-data /var/symfony-rabbit

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start Redis and PHP-FPM
CMD ["sh", "-c", "redis-server & php-fpm"]
