@echo off
REM Fabriku Docker Helper Script for Windows

set COMPOSE=docker compose

if "%1"=="setup" goto setup
if "%1"=="start" goto start
if "%1"=="stop" goto stop
if "%1"=="restart" goto restart
if "%1"=="logs" goto logs
if "%1"=="status" goto status
if "%1"=="artisan" goto artisan
if "%1"=="shell" goto shell
if "%1"=="db" goto db
if "%1"=="demo-reset" goto demo_reset
if "%1"=="clear" goto clear
if "%1"=="rebuild" goto rebuild
if "%1"=="backup" goto backup
if "%1"=="help" goto help
goto help

:setup
echo Setting up Fabriku...
echo.
echo IMPORTANT: Ensure PostgreSQL and Redis are running on your host machine!
echo - PostgreSQL: localhost:5432
echo - Redis: localhost:6379
echo.
pause
if not exist .env (
    echo Copying .env.docker.example to .env...
    copy .env.docker.example .env
    echo.
    echo Please update .env with your PostgreSQL and Redis connection details:
    echo - DB_HOST (default: localhost)
    echo - REDIS_HOST (default: localhost)
    echo.
    pause
    echo Generating application key...
    %COMPOSE% run --rm app php artisan key:generate
)
echo Building Docker images...
%COMPOSE% build
echo Starting containers...
%COMPOSE% up -d
echo Waiting for containers to start...
timeout /t 5 /nobreak > nul
echo Running migrations and seeders...
%COMPOSE% exec app php artisan migrate --seed
echo.
echo Setup completed!
echo.
echo Application: http://localhost:8000
echo Admin panel: http://localhost:8000/admin/login
echo.
echo Demo: admin@konveksi.com / password
echo Admin: admin@fabriku.com / password
goto end

:start
echo Starting containers...
%COMPOSE% up -d
echo Containers started!
goto end

:stop
echo Stopping containers...
%COMPOSE% down
echo Containers stopped!
goto end

:restart
echo Restarting containers...
%COMPOSE% restart
echo Containers restarted!
goto end

:logs
if "%2"=="" (
    %COMPOSE% logs -f
) else (
    %COMPOSE% logs -f %2
)
goto end

:status
%COMPOSE% ps
goto end

:artisan
shift
%COMPOSE% exec app php artisan %*
goto end

:shell
%COMPOSE% exec app bash
goto end

:db
echo Connecting to PostgreSQL...
echo This connects to your host PostgreSQL, not a Docker container.
psql -U fabriku -d fabriku
goto end

:demo_reset
echo Resetting demo data...
%COMPOSE% exec app php artisan demo:reset
echo Demo data reset completed!
goto end

:clear
echo Clearing cache...
%COMPOSE% exec app php artisan cache:clear
%COMPOSE% exec app php artisan config:clear
%COMPOSE% exec app php artisan route:clear
%COMPOSE% exec app php artisan view:clear
echo Cache cleared!
goto end

:rebuild
echo Rebuilding containers...
%COMPOSE% down
%COMPOSE% build --no-cache
%COMPOSE% up -d
echo Containers rebuilt!
goto end

:backup
set BACKUP_FILE=backup-%date:~-4,4%%date:~-7,2%%date:~-10,2%-%time:~0,2%%time:~3,2%%time:~6,2%.sql
set BACKUP_FILE=%BACKUP_FILE: =0%
echo Creating database backup: %BACKUP_FILE%
%COMPOSE% exec db mysqldump -u fabriku -psecret fabriku > %BACKUP_FILE%
echo Backup created: %BACKUP_FILE%
goto end

:help
echo Fabriku Docker Helper Script
echo.
echo Usage: docker.bat [command]
echo.
echo Commands:
echo   setup         Initial setup (build, start, migrate)
echo   start         Start all containers
echo   stop          Stop all containers
echo   restart       Restart all containers
echo   logs [srv]    Show logs (optional: specify service)
echo   status        Show container status
echo   artisan       Run artisan command
echo   shell         Access app container shell
echo   db            Access database shell
echo   demo-reset    Reset demo data manually
echo   clear         Clear all caches
echo   rebuild       Rebuild containers from scratch
echo   backup        Backup database
echo   help          Show this help
echo.
echo Examples:
echo   docker.bat setup
echo   docker.bat artisan migrate
echo   docker.bat logs app
echo   docker.bat demo-reset
goto end

:end
