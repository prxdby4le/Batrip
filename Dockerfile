ARG BASE_IMAGE=php:8.2-apache
FROM ${BASE_IMAGE}

# 1. Instala dependências do sistema
# ADICIONADO: git, unzip, libzip-dev (essenciais para o Composer)
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
      curl \
      git \
      unzip \
      libzip-dev \
      libpng-dev \
      libjpeg-dev \
      libwebp-dev \
      libxml2-dev \
  && docker-php-ext-configure gd --with-jpeg --with-webp \
  # ADICIONADO: zip na lista de extensões
  && docker-php-ext-install gd pdo pdo_mysql soap zip \
  && rm -rf /var/lib/apt/lists/*

# Enable useful Apache modules
RUN a2enmod rewrite headers

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# --- MELHORIA DE CACHE ---
# Copia apenas os arquivos do composer primeiro. 
# Isso permite que o Docker use o cache se você mudar o código, mas não as dependências.
COPY composer.json composer.lock ./


# Instala dependências
RUN composer install --no-interaction --no-scripts --no-progress \
  && composer dump-autoload --optimize

# Agora copia o restante do projeto
COPY . /var/www/html

# Configure Apache to serve from /public
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Environment
ENV APP_ENV=docker

# Expose HTTP port
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=15s --retries=3 \
  CMD curl -fsS http://localhost/ || exit 1