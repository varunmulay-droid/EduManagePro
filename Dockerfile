# Use official PHP image with Apache
FROM php:8.2-apache

# Copy all project files to Apache's public directory
COPY . /var/www/html/

# Enable Apache mod_rewrite (if your app needs it)
RUN a2enmod rewrite

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
