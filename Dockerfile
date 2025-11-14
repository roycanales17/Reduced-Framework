# --------------------------
# Base Image
# --------------------------
FROM php:8.2-apache

# --------------------------
# Enable Apache Modules
# --------------------------
RUN a2enmod rewrite alias headers proxy proxy_http proxy_wstunnel ssl

# --------------------------
# Install System Packages + Dev Libraries
# --------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libzip-dev libonig-dev libjpeg62-turbo-dev libfreetype6-dev libxpm-dev libwebp-dev \
    libxml2-dev libcurl4-openssl-dev libssl-dev unzip git cron time openssl \
    libmemcached-dev zlib1g-dev pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-xpm --with-webp \
    && docker-php-ext-install gd zip pdo pdo_mysql mysqli mbstring bcmath soap sockets \
    && pecl install xdebug memcached redis \
    && docker-php-ext-enable xdebug memcached redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --------------------------
# Set Document Root
# --------------------------
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# --------------------------
# Proxy for Socket.IO
# --------------------------
RUN sed -i '/DocumentRoot \/var\/www\/html\/public/a \ \n    # Proxy for Socket.IO\n    ProxyPreserveHost On\n    ProxyPass /socket.io http://node:3000/socket.io\n    ProxyPassReverse /socket.io http://node:3000/socket.io\n' /etc/apache2/sites-available/000-default.conf

# --------------------------
# Accept APP_URL from build args
# --------------------------
ARG APP_URL
RUN if [ -n "$APP_URL" ]; then \
      echo "ServerName ${APP_URL#*://}" >> /etc/apache2/apache2.conf; \
    else \
      echo "ServerName localhost" >> /etc/apache2/apache2.conf; \
    fi

# --------------------------
# Allow .htaccess
# --------------------------
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/htaccess.conf \
    && echo '    Options -Indexes +FollowSymLinks' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    Require all granted' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '<Directory /var/www/html/public>' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    Options -Indexes +FollowSymLinks' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    AllowOverride All' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '    Require all granted' >> /etc/apache2/conf-available/htaccess.conf \
    && echo '</Directory>' >> /etc/apache2/conf-available/htaccess.conf \
    && a2enconf htaccess

# --------------------------
# Create log directories
# --------------------------
RUN mkdir -p /var/log/app /tmp/xdebug && chown -R www-data:www-data /var/log/app /tmp/xdebug

# --------------------------
# SSL Setup
# --------------------------
RUN mkdir -p /etc/apache2/ssl; \
    DOMAIN=$(echo ${APP_URL} | sed -E 's#https?://##'); \
    openssl req -x509 -nodes -days 365 \
      -subj "/C=US/ST=State/L=City/O=$DOMAIN/CN=$DOMAIN" \
      -newkey rsa:2048 \
      -keyout /etc/apache2/ssl/$DOMAIN.key \
      -out /etc/apache2/ssl/$DOMAIN.crt; \
    a2enmod ssl; \
    sed -i "s|SSLCertificateFile.*|SSLCertificateFile /etc/apache2/ssl/$DOMAIN.crt|" /etc/apache2/sites-available/default-ssl.conf; \
    sed -i "s|SSLCertificateKeyFile.*|SSLCertificateKeyFile /etc/apache2/ssl/$DOMAIN.key|" /etc/apache2/sites-available/default-ssl.conf; \
    a2ensite default-ssl.conf

# --------------------------
# Disable Apache access logs & redirect error logs to stdout
# --------------------------
RUN ln -sf /dev/null /var/log/apache2/access.log \
    && ln -sf /dev/stdout /var/log/apache2/error.log \
    && ln -sf /dev/stderr /var/log/apache2/other_vhosts_access.log

# --------------------------
# Expose Ports
# --------------------------
EXPOSE 80 443

# --------------------------
# Copy Project Files
# --------------------------
COPY . /var/www/html

# --------------------------
# Set Permissions
# --------------------------
RUN find /var/www/html -type d -exec chmod 755 {} \; \
 && find /var/www/html -type f -exec chmod 644 {} \;

# --------------------------
# Start Apache
# --------------------------
CMD ["apache2-foreground"]
