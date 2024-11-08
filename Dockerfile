# Stage 1: Builder Stage
FROM debian:stable AS builder

# Install necessary packages
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        apache2 \
        php libapache2-mod-php php-mysql \
        curl \
        iputils-ping && \
    rm -rf /var/lib/apt/lists/*

# Copy the web application source code
COPY ./public-html/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Stage 2: Production Stage
FROM debian:stable

# Install minimal packages
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        apache2 \
        libapache2-mod-php php-mysql \
        curl \
        iputils-ping && \
    rm -rf /var/lib/apt/lists/*

#just for trash warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy the built application from the builder stage
COPY --from=builder /var/www/html/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
