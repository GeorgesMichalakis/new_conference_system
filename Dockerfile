FROM php:8.1-apache

# Install system dependencies and PHP extensions in one layer
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY src/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# Create Apache configuration
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/conference.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/conf-available/conference.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/conference.conf && \
    echo '    Require all granted' >> /etc/apache2/conf-available/conference.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/conference.conf && \
    a2enconf conference

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]