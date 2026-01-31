# Docker Quick Reference

## Production Setup (External PostgreSQL & Redis)

### Initial Setup
```bash
# 1. Ensure PostgreSQL and Redis are running on host
psql -U postgres -c "CREATE DATABASE fabriku;"

# 2. Configure environment
cp .env.docker.example .env
# Edit .env: DB_HOST=localhost, REDIS_HOST=localhost

# 3. Start
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate --seed
```

### Daily Commands
```bash
# Start/Stop
docker compose up -d
docker compose down

# Logs
docker compose logs -f app
docker compose logs -f scheduler

# Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan demo:reset

# Shell
docker compose exec app bash
```

---

## Development Setup (All in Docker)

### Initial Setup
```bash
# 1. Configure environment
cp .env.dev.example .env

# 2. Start (includes PostgreSQL & Redis)
docker compose -f docker-compose.dev.yml run --rm app php artisan key:generate
docker compose -f docker-compose.dev.yml up -d
sleep 10
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
```

### Daily Commands
```bash
# Start/Stop
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml down

# Logs
docker compose -f docker-compose.dev.yml logs -f app

# Artisan
docker compose -f docker-compose.dev.yml exec app php artisan migrate
docker compose -f docker-compose.dev.yml exec app php artisan demo:reset

# Database
docker compose -f docker-compose.dev.yml exec db psql -U fabriku -d fabriku
```

---

## Helper Scripts (Recommended)

### Windows
```bash
docker.bat setup         # Initial setup
docker.bat start         # Start containers
docker.bat stop          # Stop containers
docker.bat logs          # View logs
docker.bat artisan migrate
docker.bat shell         # Access bash
docker.bat demo-reset    # Reset demo data
```

### Linux/Mac
```bash
chmod +x docker.sh
./docker.sh setup
./docker.sh start
./docker.sh artisan migrate
./docker.sh demo-reset
```

### Makefile
```bash
make setup
make start
make logs
make migrate
make demo-reset
make shell
```

---

## Troubleshooting

### Check Status
```bash
docker compose ps
docker compose logs app
```

### Rebuild Containers
```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Fix Permissions
```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Clear Cache
```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### Test Database Connection
```bash
# PostgreSQL
psql -h localhost -U fabriku -d fabriku

# Redis
redis-cli ping

# From container
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
>>> Redis::ping();
```

---

## Services Overview

### Production (docker-compose.yml)
**1 Container - All-in-One:**
- **app** - Apache + Cron + Queue Worker (port 8000)
  - Supervisor manages all processes
  - Hemat resource, efficient

External: PostgreSQL (5432), Redis (6379)

### Development (docker-compose.dev.yml)
**3 Containers:**
- **app** - Apache + Cron + Queue Worker (port 8000)
- **db** - PostgreSQL 16 (port 5432)
- **redis** - Redis 7 (port 6379)

All services in Docker.

---

## Access URLs

- **Application**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin/login

### Demo Credentials
- Konveksi: admin@konveksi.com / password
- Kue Mama: admin@kuemama.com / password
- Crafty: admin@crafty.com / password
- Beauty Lab: admin@glowbeauty.com / password

### Super Admin
- admin@fabriku.com / password
