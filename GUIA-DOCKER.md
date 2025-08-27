# Guia rápido: rodando o Docker do Batrip (Windows e Linux)

Este guia explica como iniciar, parar e inspecionar o ambiente Docker do projeto Batrip no Windows (PowerShell) e no Linux (bash).

## Pré‑requisitos
- Windows 10/11 com Docker Desktop em execução (PowerShell recomendado)
- OU Linux com Docker Engine e Docker Compose Plugin instalados

Notas rápidas:
- Windows: abra o Docker Desktop e aguarde “Docker Desktop is running”.
- Linux: verifique o serviço `docker` com `systemctl status docker` (inicie com `sudo systemctl start docker`).
- Linux: para usar Docker sem sudo, rode `sudo usermod -aG docker $USER` e faça logoff/login.

## Arquivos importantes
- `docker-compose.yml` — orquestra os serviços (web, db, phpMyAdmin)
- `Dockerfile-stable` — build da imagem web (PHP + Apache)
- Scripts (opcionais): `batrip-manager.bat`, `batrip.bat`, `start-docker.bat`, `stop-docker.bat`
- SQL inicial: `database/batrip.sql` (carregado automaticamente no 1º start)

## Como iniciar

### Windows — com interface (recomendado)
No PowerShell, dentro da pasta `C:\coding\Batrip`:

```powershell
./batrip-manager.bat
```

- Escolha “Iniciar Aplicação”. O script já usa o `docker-compose.yml` correto e faz o build se necessário.

### Windows — manual pelo Docker Compose
No PowerShell, dentro da pasta `C:\coding\Batrip`:

```powershell
# construir e iniciar em segundo plano
docker compose up -d --build

# ver status
docker compose ps
```

### Linux — manual pelo Docker Compose
No terminal (bash), dentro da pasta do projeto:

```bash
cd /caminho/para/Batrip
# construir e iniciar em segundo plano
docker compose up -d --build

# ver status
docker compose ps
```

Observações no Linux:
- Se `docker compose` não existir, use `docker-compose` (binário legado).
- Se receber erro de permissão, rode com `sudo` ou adicione seu usuário ao grupo `docker`.

## URLs de acesso
- Aplicação: http://localhost:8080
- phpMyAdmin: http://localhost:8081

Credenciais padrão phpMyAdmin (variáveis definidas no compose):
- Host: `db` (dentro da rede Docker) ou `localhost:3307` fora do Docker
- Usuário: `root`
- Senha: `batrip_root_2024`

## Comandos úteis
Windows (PowerShell) ou Linux (bash):
```bash
# ver logs (tempo real)
docker compose logs -f

# reiniciar containers
docker compose restart

# parar containers
docker compose down

# parar e remover volumes (dados do MySQL)
docker compose down -v
```

## Backup e restore do banco
Windows (PowerShell) ou Linux (bash):
```bash
# backup do banco 'batrip' para arquivo local
docker exec batrip_db mysqldump -uroot -pbatrip_root_2024 batrip > backup.sql

# restaurar arquivo para o banco 'batrip'
docker exec -i batrip_db mysql -uroot -pbatrip_root_2024 batrip < backup.sql
```

## Solução de problemas
- Docker não está rodando
  - Abra o Docker Desktop e aguarde ficar “Running”. Depois, tente novamente.

- Conflito de portas (8080/8081/3307)
  - Windows (PowerShell):
    ```powershell
    netstat -ano | findstr :8080
    netstat -ano | findstr :8081
    netstat -ano | findstr :3307
    ```
  - Linux (bash):
    ```bash
    ss -ltnp | grep :8080
    ss -ltnp | grep :8081
    ss -ltnp | grep :3307
    ```
  - Feche o processo que bloqueia a porta ou ajuste as portas no `docker-compose.yml`.

- Primeiro start demora
  - O MySQL pode levar 30–60s para inicializar e carregar `database/batrip.sql`.

- Precisa reconstruir a imagem web
  - Use `--no-cache` para garantir rebuild completo:
    ```powershell
    docker compose build --no-cache
    docker compose up -d
    ```

- Volumes/paths
  - Windows: os scripts `.bat` já exportam `COMPOSE_CONVERT_WINDOWS_PATHS=1` para evitar problemas de mapeamento.
  - Linux: não é necessário ajuste; paths são nativos.

## Estrutura dos serviços (resumo)
- Web (PHP + Apache, porta 8080) — serve `public/`
- DB (MySQL 8.0, porta 3307 exposta) — persiste em volume `mysql_data`
- phpMyAdmin (porta 8081) — gerencia o banco via browser

---
Se preferir, use sempre `batrip-manager.bat` para gerenciar tudo de forma interativa (iniciar, parar, status, logs, abrir no navegador).
