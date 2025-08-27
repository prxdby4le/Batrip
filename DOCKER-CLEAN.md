# 📁 Estrutura Docker Limpa - Batrip

## Arquivos Essenciais

```
Batrip/
├── 🐳 docker-compose.yml      # Orquestração principal
├── 🐳 Dockerfile-stable       # Build da imagem web (único Dockerfile)
├── 🐳 .dockerignore           # Arquivos ignorados
├── 🎮 batrip-manager.bat      # Script de gerenciamento
├── 🎮 batrip.bat              # Script simples
└── 📖 README-DOCKER.md        # Documentação
```

## Comandos Básicos

```bash
# Iniciar
docker compose up -d --build

# Parar  
docker compose down

# Status
docker compose ps

# Logs
docker compose logs -f
```

## Acessos

- **Web**: http://localhost:8080
- **Admin**: http://localhost:8081

## Script de Gerenciamento

Execute `.\batrip-manager.bat` para interface amigável.

---
✅ **Configuração otimizada e funcional!**
