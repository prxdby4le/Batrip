ARG BASE_IMAGE=php:8.2-apache
FROM ${BASE_IMAGE}

# 1. Instala dependências do sistema
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
  && docker-php-ext-install gd pdo pdo_mysql soap zip \
  && rm -rf /var/lib/apt/lists/*

# Enable useful Apache modules
RUN a2enmod rewrite headers

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# --- CACHE DO COMPOSER ---
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-progress \
  && composer dump-autoload --optimize

# Copia o restante do projeto
COPY . /var/www/html

# Garante permissões das pastas de upload (mantive sua lógica original)
RUN mkdir -p /var/www/html/public/uploads \
    && chmod -R 777 /var/www/html/public/uploads

# Configure Apache
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
ENV APP_ENV=docker
EXPOSE 80

# -----------------------------------------------------------
# O PULO DO GATO: CRIANDO O ENTRYPOINT INLINE
# Usamos 'printf' para criar o script /docker-entrypoint.sh DENTRO da imagem
# Isso elimina a necessidade de ter o arquivo no seu computador.
# -----------------------------------------------------------
RUN printf '#!/bin/bash\n\
set -e\n\
\n\
# Verifica se .env existe, senão cria a partir do exemplo\n\
if [ ! -f /var/www/html/.env ]; then\n\
    echo "Entrypoint: Verificando .env..."\n\
    if [ -f /var/www/html/.env.example ]; then\n\
        echo "Entrypoint: Copiando .env.example para .env..."\n\
        cp /var/www/html/.env.example /var/www/html/.env\n\
        chmod 644 /var/www/html/.env\n\
        echo "✅ .env criado com sucesso."\n\
    else\n\
        echo "⚠️  Aviso: .env.example não encontrado."\n\
    fi\n\
fi\n\
\n\
# Executa scripts de correção e migração automaticamente\n\
if [ -f /var/www/html/docker/run-fixes.php ]; then\n\
    echo "🔧 Executando scripts de correção e migração..."\n\
    php /var/www/html/docker/run-fixes.php || echo "⚠️  Alguns scripts de correção falharam (pode ser normal)"\n\
fi\n\
\n\
# Executa o comando passado para o docker (apache)\n\
exec "$@"\n\
' > /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

# Define o script que acabamos de criar como ponto de entrada
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Comando padrão
CMD ["apache2-foreground"]