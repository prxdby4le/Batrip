@echo off
echo ====================================
echo    BATRIP - Parando containers
echo ====================================
echo.

echo 🛑 Parando todos os containers...
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
docker compose -f "%COMPOSE_FILE%" down

if %errorlevel% equ 0 (
    echo.
    echo ✅ Containers parados com sucesso!
    echo.
    echo ℹ️  Para remover volumes e dados persistentes:
    echo    docker compose -f "%COMPOSE_FILE%" down -v
    echo.
    echo ℹ️  Para iniciar novamente: start-docker.bat
) else (
    echo.
    echo ❌ Erro ao parar containers!
)

echo.
pause
