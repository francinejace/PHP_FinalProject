# Use the official PHP image with Apache
FROM php:8.2-apache

# Install extensions if needed (e.g., MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files into the container
COPY . /var/www/html/

# Give Apache permission to read/write
RUN chown -R www-data:www-data /var/www/html
