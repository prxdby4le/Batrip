@echo off
title BATRIP - Docker Management
color 0a

:MENU
cls
echo.
echo ╔══════════════════════════════════════════════════════════╗
echo ║                    BATRIP DOCKER                         ║
echo ║                  Gerenciamento Fácil                     ║
echo ╚══════════════════════════════════════════════════════════╝
echo.
echo  [1] 🚀 Iniciar Aplicação
echo  [2] 🛑 Parar Aplicação  
echo  [3] 📊 Status dos Containers
echo  [4] 📋 Ver Logs
echo  [5] 🌐 Abrir no Navegador
echo  [6] 🔄 Reiniciar Containers
echo  [7] ❌ Sair
echo.
set /p choice="Escolha uma opção (1-7): "

if "%choice%"=="1" goto START
if "%choice%"=="2" goto STOP
if "%choice%"=="3" goto STATUS
if "%choice%"=="4" goto LOGS
if "%choice%"=="5" goto BROWSER
if "%choice%"=="6" goto RESTART
if "%choice%"=="7" goto EXIT
goto MENU

:START
cls
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
echo 🚀 Iniciando Batrip...
docker-compose -f "%COMPOSE_FILE%" up -d
if %errorlevel% equ 0 (
    echo ✅ Aplicação iniciada com sucesso!
    echo.
    echo 🌐 Acesse: http://localhost:8080
    echo 🗄️  phpMyAdmin: http://localhost:8081
) else (
    echo ❌ Erro ao iniciar aplicação!
)
echo.
pause
goto MENU

:STOP
cls
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
echo 🛑 Parando Batrip...
docker-compose -f "%COMPOSE_FILE%" down
if %errorlevel% equ 0 (
    echo ✅ Aplicação parada com sucesso!
) else (
    echo ❌ Erro ao parar aplicação!
)
echo.
pause
goto MENU

:STATUS
cls
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
echo 📊 Status dos Containers:
echo.
docker-compose -f "%COMPOSE_FILE%" ps
echo.
pause
goto MENU

:LOGS
cls
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
echo 📋 Logs da Aplicação:
echo.
docker-compose -f "%COMPOSE_FILE%" logs --tail=50
echo.
pause
goto MENU

:BROWSER
cls
echo 🌐 Abrindo no navegador...
start http://localhost:8080
start http://localhost:8081
echo ✅ Páginas abertas!
echo.
pause
goto MENU

:RESTART
cls
set "COMPOSE_FILE=%~dp0docker-compose.yml"
set COMPOSE_CONVERT_WINDOWS_PATHS=1
echo 🔄 Reiniciando containers...
docker-compose -f "%COMPOSE_FILE%" restart
if %errorlevel% equ 0 (
    echo ✅ Containers reiniciados!
) else (
    echo ❌ Erro ao reiniciar!
)
echo.
pause
goto MENU

:EXIT
cls
echo.
echo 👋 Até logo!
echo.
exit /b 0
