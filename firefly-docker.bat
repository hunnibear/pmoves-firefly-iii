@echo off
echo Firefly III Docker Setup - Windows Command Prompt
echo ==================================================
echo.
echo This script provides basic Docker commands for Windows users.
echo For full functionality, please use PowerShell scripts instead.
echo.

:menu
echo Choose an option:
echo 1. Start Local Development Environment
echo 2. Stop Local Development Environment
echo 3. Start Production Environment
echo 4. Stop Production Environment
echo 5. View Logs
echo 6. Check Service Status
echo 7. Exit
echo.
set /p choice="Enter your choice (1-7): "

if "%choice%"=="1" goto start_local
if "%choice%"=="2" goto stop_local
if "%choice%"=="3" goto start_production
if "%choice%"=="4" goto stop_production
if "%choice%"=="5" goto view_logs
if "%choice%"=="6" goto check_status
if "%choice%"=="7" goto exit
echo Invalid choice. Please try again.
goto menu

:start_local
echo Starting local development environment...
if not exist .env.local (
    echo Error: .env.local not found. Please copy from .env.local.example first.
    pause
    goto menu
)
docker-compose -f docker-compose.local.yml up -d
echo Local environment started. Access at http://localhost:8080
pause
goto menu

:stop_local
echo Stopping local development environment...
docker-compose -f docker-compose.local.yml down
echo Local environment stopped.
pause
goto menu

:start_production
echo Starting production environment...
if not exist .env.production (
    echo Error: .env.production not found. Please copy from .env.production.example first.
    pause
    goto menu
)
docker-compose -f docker-compose.production.yml up -d
echo Production environment started.
pause
goto menu

:stop_production
echo Stopping production environment...
docker-compose -f docker-compose.production.yml down
echo Production environment stopped.
pause
goto menu

:view_logs
echo Choose environment:
echo 1. Local Development
echo 2. Production
set /p env_choice="Enter choice (1-2): "
if "%env_choice%"=="1" (
    docker-compose -f docker-compose.local.yml logs -f
) else if "%env_choice%"=="2" (
    docker-compose -f docker-compose.production.yml logs -f
) else (
    echo Invalid choice.
)
pause
goto menu

:check_status
echo Checking Docker containers status...
docker ps
echo.
echo Checking Docker Compose services...
if exist docker-compose.local.yml (
    echo Local development services:
    docker-compose -f docker-compose.local.yml ps
)
if exist docker-compose.production.yml (
    echo Production services:
    docker-compose -f docker-compose.production.yml ps
)
pause
goto menu

:exit
echo Thank you for using Firefly III Docker Setup!
pause
exit
