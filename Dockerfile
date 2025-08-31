ARG BASE_IMAGE=php:8.2-apache
FROM ${BASE_IMAGE}


# Install PHP extensions (incluindo GD para upload/edição de imagens)
RUN apt-get update \
  && apt-get install -y --no-install-recommends curl libpng-dev libjpeg-dev libwebp-dev \
  && docker-php-ext-configure gd --with-jpeg --with-webp \
  && docker-php-ext-install gd pdo pdo_mysql \
  && rm -rf /var/lib/apt/lists/*

# Enable useful Apache modules
RUN a2enmod rewrite headers

# Configure Apache to serve from /public
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Set workdir and copy application
WORKDIR /var/www/html
COPY . /var/www/html

# Environment
ENV APP_ENV=docker

# Expose HTTP port
EXPOSE 80

# Healthcheck (basic)
HEALTHCHECK --interval=30s --timeout=3s --start-period=15s --retries=3 \
  CMD curl -fsS http://localhost/ || exit 1
