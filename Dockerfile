# Base image: PHP 8.2 with required extensions
FROM php:8.2-fpm

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \ 
    unzip \
    git \
    curl \
    nginx \
    && docker-php-ext-install zip gd pdo pgsql pdo_pgsql


# Install Composer globally
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html/symfony

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set appropriate permissions
RUN chown -R www-data:www-data /var/www/html/symfony

# Copy existing application files
COPY . /var/www/html/symfony

# Expose the ports for nginx and PHP
EXPOSE 80

# Start Nginx and PHP-FPM together
CMD service nginx start && php-fpm
