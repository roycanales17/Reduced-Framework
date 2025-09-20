# Use official PHP with Apache image
FROM php:8.2-apache

# Enable necessary Apache modules
RUN a2enmod rewrite alias headers

# Update packages & install PHP extensions + Xdebug
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libzip-dev libonig-dev libjpeg62-turbo-dev libfreetype6-dev libxpm-dev libwebp-dev \
    libxml2-dev libcurl4-openssl-dev libssl-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-xpm --with-webp \
    && docker-php-ext-install gd zip pdo pdo_mysql mysqli mbstring bcmath soap sockets \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set document root to /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/htaccess.conf \
    && echo '    Options -Indexes +FollowSymLinks' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    Require all granted' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '<Directory /var/www/html/public>' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    Options -Indexes +FollowSymLinks' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    Require all granted' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/htaccess.conf \
    && a2enconf htaccess

# Copy project files
COPY . /var/www/html

# Set permissions (recommended secure defaults)
RUN find /var/www/html -type d -exec chmod 755 {} \; \
 && find /var/www/html -type f -exec chmod 644 {} \;

# Expose web port
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
