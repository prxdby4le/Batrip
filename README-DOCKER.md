# 🎯 Batrip - Aplicação Dockerizada

## 📋 Descrição
Aplicação web Batrip totalmente containerizada com Docker, mantendo a identidade visual original com fonte UnifrakturMaguntia e design responsivo Bootstrap.

## 🚀 Início Rápido

### Pré-requisitos
- Docker Desktop instalado e funcionando
- Windows 10/11

### 🎮 Método 1: Script Automático
```bash
# Execute o gerenciador interativo
.\batrip.bat
```

### ⚡ Método 2: Comandos Manuais
```bash
# Iniciar aplicação
docker-compose up -d

# Parar aplicação
docker-compose down

# Ver status
docker-compose ps
```

## 🌐 Acessos

| Serviço | URL | Credenciais |
|---------|-----|-------------|
| 🎯 **Aplicação Batrip** | http://localhost:8080 | - |
| 🗄️ **phpMyAdmin** | http://localhost:8081 | user: `root`<br>pass: `batrip_root_2024` |

## 🏗️ Arquitetura

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Batrip Web    │    │   MySQL 8.0     │    │   phpMyAdmin    │
│   PHP Apache    │◄───┤   Database      │◄───┤   Admin Panel   │
│   Port: 8080    │    │   Port: 3307    │    │   Port: 8081    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🎨 Recursos da Aplicação

### ✨ Design & UI
- **Fonte Principal**: UnifrakturMaguntia (identidade Batrip)
- **Fonte Corpo**: Arvo (serif elegante)
- **Cores**: Cinza (#808080), Preto (#0a0a0a), Branco (#ffffff)
- **Framework**: Bootstrap 5 (responsivo)

### 🗂️ Estrutura Organizada
```
Batrip/
├── 📁 assets/
│   ├── 📁 css/          # Estilos organizados
│   └── 📁 js/           # Scripts organizados
├── 📁 public/           # Ponto de entrada
├── 📁 includes/         # Configurações PHP
├── 📁 database/         # Scripts SQL
└── 🐳 Docker files      # Containerização
```

### 🔧 Funcionalidades
- Sistema de autenticação
- Catálogo de produtos
- Carrinho de compras
- Painel administrativo
- Gestão de encomendas
- Perfil de usuário

## 🛠️ Comandos Úteis

```bash
# Ver logs em tempo real
docker-compose logs -f

# Acessar shell do container web
docker exec -it batrip_web bash

# Backup do banco de dados
docker exec batrip_db mysqldump -uroot -pbatrip_root_2024 batrip > backup.sql

# Restaurar banco de dados
docker exec -i batrip_db mysql -uroot -pbatrip_root_2024 batrip < backup.sql
```

## 🐛 Solução de Problemas

### Container não inicia
```bash
# Verificar logs
docker-compose logs

# Reconstruir containers
docker-compose up -d --force-recreate
```

### Banco não conecta
1. Verificar se o container MySQL está rodando
2. Confirmar credenciais em `includes/db.php`
3. Aguardar inicialização completa do banco (30-60s)

### Porta ocupada
```bash
# Verificar processos usando as portas
netstat -ano | findstr :8080
netstat -ano | findstr :8081
netstat -ano | findstr :3307
```

## 📊 Monitoramento

### Status dos Serviços
- ✅ **Web**: http://localhost:8080
- ✅ **Database**: localhost:3307
- ✅ **Admin**: http://localhost:8081

### Performance
- **Startup Time**: ~30-60 segundos
- **Memory Usage**: ~2GB RAM
- **Disk Usage**: ~3GB

## 🚀 Próximos Passos

1. **Backup Automatizado**: Scripts para backup regular
2. **SSL/HTTPS**: Certificados para produção
3. **Load Balancer**: Múltiplas instâncias
4. **CI/CD**: Pipeline de deployment

## 📝 Changelog

- ✅ **v1.0**: Containerização completa
- ✅ **v1.1**: Interface de gerenciamento
- ✅ **v1.2**: Documentação completa

---

💡 **Dica**: Use o `batrip-manager.bat` para uma experiência mais amigável!

🎯 **Aplicação pronta para produção com Docker!**
