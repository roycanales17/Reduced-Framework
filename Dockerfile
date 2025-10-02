# Use official PHP with Apache image
FROM php:8.2-apache

# Enable necessary Apache modules (added proxy, proxy_http, proxy_wstunnel)
RUN a2enmod rewrite alias headers proxy proxy_http proxy_wstunnel

# Update packages & install PHP extensions + Xdebug + cron + time
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libzip-dev libonig-dev libjpeg62-turbo-dev libfreetype6-dev libxpm-dev libwebp-dev \
    libxml2-dev libcurl4-openssl-dev libssl-dev unzip git cron time \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-xpm --with-webp \
    && docker-php-ext-install gd zip pdo pdo_mysql mysqli mbstring bcmath soap sockets \
    && pecl install xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set document root to /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Inject reverse proxy config for Socket.IO
RUN sed -i '/DocumentRoot \/var\/www\/html\/public/a \ \n    # Proxy for Socket.IO\n    ProxyPreserveHost On\n    ProxyPass /socket.io http://node:3000/socket.io\n    ProxyPassReverse /socket.io http://node:3000/socket.io\n' /etc/apache2/sites-available/000-default.conf

# Configure ServerName (avoid FQDN warnings)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

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

# Create log directory
RUN mkdir -p /var/log/app && chown -R www-data:www-data /var/log/app

# Configure Apache logging:
# 1. Rewrite debugging (trace8)
# 2. Custom access log with URL, status, UA
RUN echo 'LogLevel alert rewrite:trace8' > /etc/apache2/conf-available/htaccess-logging.conf \
    && echo 'ErrorLog /var/log/app/htaccess.log' >> /etc/apache2/conf-available/htaccess-logging.conf \
    && echo 'LogFormat "%h %l %u %t \\"%r\\" %>s %b \\"%{Referer}i\\" \\"%{User-Agent}i\\"" custom' >> /etc/apache2/conf-available/htaccess-logging.conf \
    && echo 'CustomLog /var/log/app/htaccess.log custom' >> /etc/apache2/conf-available/htaccess-logging.conf \
    && a2enconf htaccess-logging

# Copy project files
COPY . /var/www/html

# Set permissions (recommended secure defaults)
RUN find /var/www/html -type d -exec chmod 755 {} \; \
 && find /var/www/html -type f -exec chmod 644 {} \;

# Expose web port
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
