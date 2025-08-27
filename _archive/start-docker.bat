@echo off
echo ====================================
echo    BATRIP - Iniciando containers
echo ====================================
echo.

REM Verifica se o Docker Desktop está rodando
echo Verificando Docker Desktop...
docker version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Desktop não está rodando!
    echo Por favor, inicie o Docker Desktop e tente novamente.
    pause
    exit /b 1
)

echo ✅ Docker Desktop está ativo!
echo.

echo 📦 Construindo e iniciando containers...
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
docker compose -f "%COMPOSE_FILE%" up -d --build

if %errorlevel% equ 0 (
    echo.
    echo ✅ Containers iniciados com sucesso!
    echo.
    echo 🌐 Acesse a aplicação:
    echo    - Aplicação Batrip: http://localhost:8080
    echo    - phpMyAdmin: http://localhost:8081
    echo.
    echo 📊 Status dos containers:
    docker compose -f "%COMPOSE_FILE%" ps
    echo.
    echo ℹ️  Para parar os containers, execute: stop-docker.bat
) else (
    echo.
    echo ❌ Erro ao iniciar containers!
    echo Verifique os logs para mais detalhes.
)

echo.
pause
