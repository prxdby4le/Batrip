@echo off
title BATRIP - Setup Completo Docker
color 0a

echo.
echo ╔══════════════════════════════════════════════════════════╗
echo ║                    BATRIP DOCKER SETUP                   ║
echo ║                 Inicialização Completa                   ║
echo ╚══════════════════════════════════════════════════════════╝
echo.

REM Verificação do Docker Desktop
echo [1/5] 🔍 Verificando Docker Desktop...
timeout /t 2 >nul

docker version >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ❌ Docker Desktop não detectado!
    echo.
    echo 📋 Instruções para resolver:
    echo    1. Instale o Docker Desktop: https://www.docker.com/products/docker-desktop
    echo    2. Inicie o Docker Desktop
    echo    3. Aguarde até ver "Docker Desktop is running"
    echo    4. Execute este script novamente
    echo.
    pause
    exit /b 1
)

echo ✅ Docker Desktop está ativo!

REM Limpeza de containers antigos
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
echo.
echo [2/5] 🧹 Limpando containers antigos...
docker compose -f "%COMPOSE_FILE%" down -v >nul 2>&1
docker system prune -f >nul 2>&1

REM Build das imagens
echo.
echo [3/5] 🔨 Construindo imagens Docker...
echo    - Isso pode levar alguns minutos na primeira vez...
docker compose -f "%COMPOSE_FILE%" build --no-cache
if %errorlevel% neq 0 (
    echo ❌ Erro no build das imagens!
    pause
    exit /b 1
)

REM Inicialização dos containers
echo.
echo [4/5] 🚀 Iniciando containers...
docker compose -f "%COMPOSE_FILE%" up -d

if %errorlevel% neq 0 (
    echo ❌ Erro ao iniciar containers!
    echo 📋 Verifique os logs: docker compose -f "%COMPOSE_FILE%" logs
    pause
    exit /b 1
)

REM Aguardando inicialização completa
echo.
echo [5/5] ⏳ Aguardando inicialização completa...
echo    - Banco de dados inicializando...
timeout /t 10 >nul

echo    - Verificando saúde dos containers...
timeout /t 5 >nul

REM Status final
echo.
echo ═══════════════════════════════════════════════════════════
echo                        ✅ SUCESSO!
echo ═══════════════════════════════════════════════════════════
echo.
echo 🌐 ACESSOS DISPONÍVEIS:
echo    ┌─────────────────────────────────────────┐
echo    │  🎯 Aplicação Batrip                    │
echo    │     http://localhost:8080               │
echo    │                                         │
echo    │  🗄️  phpMyAdmin                        │
echo    │     http://localhost:8081               │
echo    │     User: root                          │
echo    │     Pass: batrip_root_2024              │
echo    └─────────────────────────────────────────┘
echo.

echo 📊 STATUS DOS CONTAINERS:
docker compose -f "%COMPOSE_FILE%" ps

echo.
echo 📋 COMANDOS ÚTEIS:
echo    • Ver logs:        docker compose -f "%COMPOSE_FILE%" logs
echo    • Parar tudo:      stop-docker.bat
echo    • Reiniciar:       docker compose -f "%COMPOSE_FILE%" restart
echo    • Acessar shell:   docker exec -it batrip_web bash
echo.

echo 🎉 Aplicação Batrip está pronta para uso!
echo    Abra http://localhost:8080 no seu navegador
echo.
pause
