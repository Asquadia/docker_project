FROM debian:stable AS builder
# Install necessary packages in a single layer
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        apache2 \
        php \
        libapache2-mod-php \
        php-mysql \
        curl \
        iputils-ping && \
    rm -rf /var/lib/apt/lists/* && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy the web application source code and set permissions in a single layer
COPY ./public-html/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Production stage
FROM debian:stable
# Install minimal packages in a single layer
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        apache2 \
        libapache2-mod-php \
        php-mysql \
        curl \
        iputils-ping && \
    rm -rf /var/lib/apt/lists/* && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    # Pre-create necessary directories and set permissions
    mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2 && \
    chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2

# Copy the built application from the builder stage
COPY --from=builder /var/www/html/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Configure Apache to run in foreground
ENV APACHE_RUN_DIR=/var/run/apache2 \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_PID_FILE=/var/run/apache2/apache2.pid

EXPOSE 80
CMD ["apache2ctl", "-D", "FOREGROUND"]
