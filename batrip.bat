@echo off
title Batrip Docker Manager
color 0a

:MENU
cls
echo.
echo ╔══════════════════════════════════════╗
echo ║          BATRIP DOCKER               ║
echo ╚══════════════════════════════════════╝
echo.
echo  [1] 🚀 Iniciar
echo  [2] 🛑 Parar
echo  [3] 📊 Status
echo  [4] 🔄 Reiniciar
echo  [5] 🌐 Abrir
echo  [0] ❌ Sair
echo.
set /p choice="Opção: "

if "%choice%"=="1" goto START
if "%choice%"=="2" goto STOP
if "%choice%"=="3" goto STATUS
if "%choice%"=="4" goto RESTART
if "%choice%"=="5" goto OPEN
if "%choice%"=="0" goto EXIT
goto MENU

:START
cls
echo 🚀 Iniciando Batrip...
docker-compose up -d
if %errorlevel% equ 0 (
    echo ✅ Sucesso! Acesse: http://localhost:8080
) else (
    echo ❌ Erro ao iniciar!
)
pause
goto MENU

:STOP
cls
echo 🛑 Parando Batrip...
docker-compose down
echo ✅ Parado!
pause
goto MENU

:STATUS
cls
echo 📊 Status:
docker-compose ps
pause
goto MENU

:RESTART
cls
echo 🔄 Reiniciando...
docker-compose restart
echo ✅ Reiniciado!
pause
goto MENU

:OPEN
start http://localhost:8080
start http://localhost:8081
goto MENU

:EXIT
exit /b 0
