# 📁 Estrutura Docker Limpa - Batrip

## Arquivos Essenciais

```
Batrip/
├── 🐳 docker-compose.yml      # Orquestração principal
├── 🐳 Dockerfile              # Build da imagem web  
├── 🐳 .dockerignore           # Arquivos ignorados
├── 🎮 batrip.bat              # Script de gerenciamento
└── 📖 README-DOCKER.md        # Documentação
```

## Comandos Básicos

```bash
# Iniciar
docker-compose up -d

# Parar  
docker-compose down

# Status
docker-compose ps

# Logs
docker-compose logs -f
```

## Acessos

- **Web**: http://localhost:8080
- **Admin**: http://localhost:8081

## Script de Gerenciamento

Execute `.\batrip.bat` para interface amigável.

---
✅ **Configuração otimizada e funcional!**
