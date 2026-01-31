#!/bin/bash

# Fabriku Docker Helper Script

set -e

COMPOSE="docker compose"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

echo_error() {
    echo -e "${RED}✗ $1${NC}"
}

echo_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

echo_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Check if Docker is installed
check_docker() {
    if ! command -v docker &> /dev/null; then
        echo_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
}

# Check if .env exists
check_env() {
    if [ ! -f .env ]; then
        echo_warning ".env file not found!"
        echo_info "Copying .env.docker.example to .env..."
        cp .env.docker.example .env
        echo_success ".env file created. Please update the values if needed."
        echo_info "Generating application key..."
        $COMPOSE run --rm app php artisan key:generate
        echo_success "Application key generated!"
    fi
}

# Initial setup
setup() {
    echo "🚀 Setting up Fabriku..."
    echo ""
    echo_warning "IMPORTANT: Ensure PostgreSQL and Redis are running on your host machine!"
    echo_info "  - PostgreSQL: localhost:5432"
    echo_info "  - Redis: localhost:6379"
    echo ""
    read -p "Press enter to continue..."
    
    check_docker
    check_env
    
    if [ ! -f .env ]; then
        echo_info "Please update .env with your PostgreSQL and Redis connection details:"
        echo_info "  - DB_HOST (default: localhost)"
        echo_info "  - REDIS_HOST (default: localhost)"
        echo ""
        read -p "Press enter when ready..."
    fi
    
    echo_info "Building Docker images..."
    $COMPOSE build
    
    echo_info "Starting containers..."
    $COMPOSE up -d
    
    echo_info "Waiting for containers to start..."
    sleep 5
    
    echo_info "Running migrations and seeders..."
    $COMPOSE exec app php artisan migrate --seed
    
    echo_success "Setup completed!"
    echo ""
    echo "🌐 Application is running at: http://localhost:8000"
    echo "🔐 Admin panel: http://localhost:8000/admin/login"
    echo ""
    echo "Demo Credentials:"
    echo "  - Konveksi: admin@konveksi.com / password"
    echo "  - Kue Mama: admin@kuemama.com / password"
    echo "  - Crafty: admin@crafty.com / password"
    echo "  - Beauty Lab: admin@glowbeauty.com / password"
    echo ""
    echo "Admin: admin@fabriku.com / password"
}

# Start containers
start() {
    echo_info "Starting containers..."
    $COMPOSE up -d
    echo_success "Containers started!"
}

# Stop containers
stop() {
    echo_info "Stopping containers..."
    $COMPOSE down
    echo_success "Containers stopped!"
}

# Restart containers
restart() {
    echo_info "Restarting containers..."
    $COMPOSE restart
    echo_success "Containers restarted!"
}

# Show logs
logs() {
    if [ -z "$1" ]; then
        $COMPOSE logs -f
    else
        $COMPOSE logs -f "$1"
    fi
}

# Show status
status() {
    $COMPOSE ps
}

# Run artisan command
artisan() {
    $COMPOSE exec app php artisan "$@"
}

# Access app shell
shell() {
    $COMPOSE exec app bash
}

# Access database shell
db_shell() {
    echo_info "Connecting to PostgreSQL on host..."
    echo_info "This connects to your host PostgreSQL, not a Docker container."
    psql -U fabriku -d fabriku
}

# Reset demo data
demo_reset() {
    echo_info "Resetting demo data..."
    $COMPOSE exec app php artisan demo:reset
    echo_success "Demo data reset completed!"
}

# Clear cache
clear_cache() {
    echo_info "Clearing cache..."
    $COMPOSE exec app php artisan cache:clear
    $COMPOSE exec app php artisan config:clear
    $COMPOSE exec app php artisan route:clear
    $COMPOSE exec app php artisan view:clear
    echo_success "Cache cleared!"
}

# Rebuild containers
rebuild() {
    echo_info "Rebuilding containers..."
    $COMPOSE down
    $COMPOSE build --no-cache
    $COMPOSE up -d
    echo_success "Containers rebuilt!"
}

# Backup database
backup() {
    BACKUP_FILE="backup-$(date +%Y%m%d-%H%M%S).sql"
    echo_info "Creating PostgreSQL backup: $BACKUP_FILE"
    pg_dump -U fabriku fabriku > "$BACKUP_FILE"
    echo_success "Backup created: $BACKUP_FILE"
}

# Show help
show_help() {
    cat << EOF
Fabriku Docker Helper Script

Usage: ./docker.sh [command]

Commands:
  setup         Initial setup (build, start, migrate)
  start         Start all containers
  stop          Stop all containers
  restart       Restart all containers
  logs [srv]    Show logs (optional: specify service)
  status        Show container status
  artisan       Run artisan command
  shell         Access app container shell
  db            Access database shell
  demo-reset    Reset demo data manually
  clear         Clear all caches
  rebuild       Rebuild containers from scratch
  backup        Backup database
  help          Show this help

Examples:
  ./docker.sh setup
  ./docker.sh artisan migrate
  ./docker.sh logs app
  ./docker.sh demo-reset

Services:
  app       - Main application (PHP 8.5 Apache)
  scheduler - Laravel scheduler (demo reset)
  queue     - Queue worker

External Services (on host):
  PostgreSQL - Database server (port 5432)
  Redis      - Cache and queue backend (port 6379)
EOF
}

# Main command handler
case "$1" in
    setup)
        setup
        ;;
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    logs)
        logs "$2"
        ;;
    status)
        status
        ;;
    artisan)
        shift
        artisan "$@"
        ;;
    shell)
        shell
        ;;
    db)
        db_shell
        ;;
    demo-reset)
        demo_reset
        ;;
    clear)
        clear_cache
        ;;
    rebuild)
        rebuild
        ;;
    backup)
        backup
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        echo_error "Unknown command: $1"
        echo ""
        show_help
        exit 1
        ;;
esac
