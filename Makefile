.PHONY: help setup start stop restart logs status shell db artisan demo-reset clear rebuild backup

# Default target
help:
	@echo "Fabriku Docker Commands"
	@echo ""
	@echo "Usage: make [command]"
	@echo ""
	@echo "Commands:"
	@echo "  setup         Initial setup (build, start, migrate)"
	@echo "  start         Start all containers"
	@echo "  stop          Stop all containers"
	@echo "  restart       Restart all containers"
	@echo "  logs          Show logs for all services"
	@echo "  logs-app      Show app logs"
	@echo "  logs-db       Show database logs"
	@echo "  logs-scheduler Show scheduler logs"
	@echo "  logs-queue    Show queue logs"
	@echo "  status        Show container status"
	@echo "  shell         Access app container shell"
	@echo "  db            Access database shell"
	@echo "  demo-reset    Reset demo data manually"
	@echo "  clear         Clear all caches"
	@echo "  rebuild       Rebuild containers from scratch"
	@echo "  backup        Backup database"
	@echo ""
	@echo "Artisan Commands:"
	@echo "  migrate       Run migrations"
	@echo "  seed          Run seeders"
	@echo "  tinker        Open tinker shell"
	@echo "  test          Run tests"
	@echo ""

# Setup
setup:
	@echo "Setting up Fabriku..."
	@echo ""
	@echo "⚠️  IMPORTANT: Ensure PostgreSQL and Redis are running on your host machine!"
	@echo "   - PostgreSQL: localhost:5432"
	@echo "   - Redis: localhost:6379"
	@echo ""
	@read -p "Press enter to continue..."
	@if [ ! -f .env ]; then \
		echo "Copying .env.docker.example to .env..."; \
		cp .env.docker.example .env; \
		echo "Please update .env with your connection details:"; \
		echo "  - DB_HOST (default: localhost)"; \
		echo "  - REDIS_HOST (default: localhost)"; \
		echo ""; \
		read -p "Press enter when ready..."; \
		echo "Generating application key..."; \
		docker compose run --rm app php artisan key:generate; \
	fi
	@echo "Building Docker images..."
	@docker compose build
	@echo "Starting containers..."
	@docker compose up -d
	@echo "Waiting for containers..."
	@sleep 5
	@echo "Running migrations and seeders..."
	@docker compose exec app php artisan migrate --seed
	@echo ""
	@echo "✓ Setup completed!"
	@echo ""
	@echo "Application: http://localhost:8000"
	@echo "Demo: admin@konveksi.com / password"

# Container management
start:
	@docker compose up -d
	@echo "✓ Containers started!"

stop:
	@docker compose down
	@echo "✓ Containers stopped!"

restart:
	@docker compose restart
	@echo "✓ Containers restarted!"

# Logs
logs:
	@docker compose logs -f

logs-app:
	@docker compose logs -f app

logs-db:
	@docker compose logs -f db

logs-scheduler:
	@docker compose logs -f scheduler

logs-queue:
	@docker compose logs -f queue

# Status
status:
	@docker compose ps

# Shell access
shell:
	@docker compose exec app bash

db:
	@echo "Connecting to PostgreSQL on host..."
	@psql -U fabriku -d fabriku

# Artisan commands
artisan:
	@docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

migrate:
	@docker compose exec app php artisan migrate

seed:
	@docker compose exec app php artisan db:seed

tinker:
	@docker compose exec app php artisan tinker

test:
	@docker compose exec app php artisan test

# Demo reset
demo-reset:
	@echo "Resetting demo data..."
	@docker compose exec app php artisan demo:reset
	@echo "✓ Demo data reset completed!"

# Clear cache
clear:
	@echo "Clearing cache..."
	@docker compose exec app php artisan cache:clear
	@docker compose exec app php artisan config:clear
	@docker compose exec app php artisan route:clear
	@docker compose exec app php artisan view:clear
	@echo "✓ Cache cleared!"

# Rebuild
rebuild:
	@echo "Rebuilding containers..."
	@docker compose down
	@docker compose build --no-cache
	@docker compose up -d
	@echo "✓ Containers rebuilt!"

# Backup
backup:
	@BACKUP_FILE=baPostgreSQL backup: $$BACKUP_FILE"; \
	pg_dump -U fabriku
	docker compose exec db mysqldump -u fabriku -psecret fabriku > $$BACKUP_FILE; \
	echo "✓ Backup created: $$BACKUP_FILE"

# Catch-all target for artisan commands
%:
	@:
