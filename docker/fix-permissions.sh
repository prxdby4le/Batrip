#!/bin/bash
# Script para ajustar permissões das pastas de upload no container Docker

echo "🔐 Ajustando permissões das pastas de upload..."

# Cria diretórios se não existirem
mkdir -p /var/www/html/public/uploads/profile_bg
mkdir -p /var/www/html/public/uploads/products
mkdir -p /var/www/html/public/uploads/sets
mkdir -p /var/www/html/public/assets/img/perfil
mkdir -p /var/www/html/public/assets/img/sets
mkdir -p /var/www/html/logs

# Ajusta permissões
chmod -R 777 /var/www/html/public/uploads 2>/dev/null || true
chmod -R 777 /var/www/html/public/assets/img/perfil 2>/dev/null || true
chmod -R 777 /var/www/html/public/assets/img/sets 2>/dev/null || true
chmod -R 775 /var/www/html/logs 2>/dev/null || true

# Ajusta owner (www-data é o usuário do Apache)
chown -R www-data:www-data /var/www/html/public/uploads 2>/dev/null || true
chown -R www-data:www-data /var/www/html/public/assets/img/perfil 2>/dev/null || true
chown -R www-data:www-data /var/www/html/public/assets/img/sets 2>/dev/null || true

echo "✅ Permissões ajustadas com sucesso!"
echo ""
echo "📋 Diretórios configurados:"
echo "  ✓ /var/www/html/public/uploads (777)"
echo "  ✓ /var/www/html/public/assets/img/perfil (777)"
echo "  ✓ /var/www/html/public/assets/img/sets (777)"
echo "  ✓ /var/www/html/logs (775)"

