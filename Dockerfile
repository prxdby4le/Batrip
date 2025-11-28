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

# Se .env não existir, copia .env.example para .env
RUN if [ ! -f /var/www/html/.env ]; then \
        if [ -f /var/www/html/.env.example ]; then \
            cp /var/www/html/.env.example /var/www/html/.env && \
            echo ".env criado a partir de .env.example" && \
            chmod 644 /var/www/html/.env; \
        else \
            echo "Aviso: .env.example não encontrado - você pode precisar criar o arquivo .env manualmente"; \
        fi; \
    else \
        echo ".env já existe, mantendo arquivo existente"; \
    fi

# Garante que os diretórios de upload existam e tenham permissões corretas
# E copia assets de assets/ para public/assets/ se necessário
RUN rm -rf /var/www/html/public/assets/js/bootstrap-js 2>/dev/null || true \
    && mkdir -p /var/www/html/public/uploads/profile_bg \
    && mkdir -p /var/www/html/public/uploads/products \
    && mkdir -p /var/www/html/public/uploads/sets \
    && mkdir -p /var/www/html/public/assets/img/perfil \
    && mkdir -p /var/www/html/public/assets/img/sets \
    && mkdir -p /var/www/html/public/assets/js/bootstrap-js \
    && chmod -R 777 /var/www/html/public/uploads \
    && chmod -R 777 /var/www/html/public/assets/img/perfil \
    && chmod -R 777 /var/www/html/public/assets/img/sets \
    && chmod -R 755 /var/www/html/public/assets \
    && if [ -d /var/www/html/assets ] && [ ! -d /var/www/html/public/assets/js/bootstrap-js ] || [ ! -f /var/www/html/public/assets/js/bootstrap-js/bootstrap.bundle.min.js ]; then \
        cp -r /var/www/html/assets/js/bootstrap-js/* /var/www/html/public/assets/js/bootstrap-js/ 2>/dev/null || true; \
        cp /var/www/html/assets/js/utils.js /var/www/html/public/assets/js/utils.js 2>/dev/null || true; \
        cp /var/www/html/assets/js/script.js /var/www/html/public/assets/js/script.js 2>/dev/null || true; \
    fi \
    && if [ -d /var/www/html/assets/img/perfil ] && [ ! -z "$(ls -A /var/www/html/assets/img/perfil 2>/dev/null)" ]; then \
        cp -r /var/www/html/assets/img/perfil/* /var/www/html/public/assets/img/perfil/ 2>/dev/null || true; \
    fi

# Configure Apache to serve from /public
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Environment
ENV APP_ENV=docker

# Expose HTTP port
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=15s --retries=3 \
  CMD curl -fsS http://localhost/ || exit 1