# Docker Setup for Fabriku

## Architecture

**Single Container Approach** - untuk hemat resource, semua services (Apache, Cron, Queue Worker) berjalan dalam satu container yang dikelola oleh Supervisor.

### Production Setup (docker-compose.yml)
- **1 Container** menjalankan Apache + Cron + Queue Worker
- PostgreSQL dan Redis eksternal (host machine)

### Development Setup (docker-compose.dev.yml)  
- **1 App Container** menjalankan Apache + Cron + Queue Worker
- **1 PostgreSQL Container** (port 5432)
- **1 Redis Container** (port 6379)

---

## Production Setup

> Uses external PostgreSQL and Redis on host machine

### Prerequisites

- PostgreSQL 14+ running on host (port 5432)
- Redis 6+ running on host (port 6379)
- Docker and Docker Compose installed

### Quick Start

#### 1. Prepare Database

```bash
# Create PostgreSQL database
psql -U postgres
CREATE DATABASE fabriku;
CREATE USER fabriku WITH PASSWORD 'secret';
GRANT ALL PRIVILEGES ON DATABASE fabriku TO fabriku;
\q
```

#### 2. Configure Environment

```bash
# Copy and configure environment file
cp .env.docker.example .env

# Update database and Redis connection:
# DB_CONNECTION=pgsql
# DB_HOST=localhost (or your PostgreSQL host)
# DB_PORT=5432
# REDIS_HOST=localhost (or your Redis host)

# Generate application key
docker compose run --rm app php artisan key:generate
```

#### 3. Build and Start

```bash
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --seed
```

#### 4. Access Application

- **Application**: http://localhost:8000
- **Admin Panel**: http://localhost:8000/admin/login

---

## Development Setup

> Includes PostgreSQL and Redis in Docker containers

### Quick Start

#### 1. Configure Environment

```bash
# Copy environment file
cp .env.docker.example .env

# Update for dev setup:
# APP_ENV=local
# APP_DEBUG=true
# DB_HOST=db
# REDIS_HOST=redis

# Generate application key
docker compose -f docker-compose.dev.yml run --rm app php artisan key:generate
```

#### 2. Build and Start

```bash
# Build and start all services (including PostgreSQL and Redis)
docker compose -f docker-compose.dev.yml up -d

# Wait for database to be ready
sleep 10

# Run migrations and seeders
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
```

#### 3. Access Application

- **Application**: http://localhost:8000
- **PostgreSQL**: localhost:5432 (user: fabriku, pass: secret)
- **Redis**: localhost:6379

---

## Demo Credentials

**Tenant Users:**
- Konveksi Fabriku: `admin@konveksi.com` / `password`
- Kue Mama Homemade: `admin@kuemama.com` / `password`
- Crafty Handmade: `admin@crafty.com` / `password`
- Glow Beauty Lab: `admin@glowbeauty.com` / `password`

**Super Admin:**
- `admin@fabriku.com` / `password`

## Services

### Production Setup (docker-compose.yml)
**1 Container:**
- **app**: Apache + Cron + Queue Worker (managed by Supervisor)

**External Dependencies:**
- PostgreSQL 14+ (host machine)
- Redis 7 (host machine)

### Development Setup (docker-compose.dev.yml)
**3 Containers:**
- **app**: Apache + Cron + Queue Worker (managed by Supervisor)
- **db**: PostgreSQL 16 Alpine
- **redis**: Redis 7 Alpine

---

## Container Management

### Production Commands

```bash
# Start/stop services
docker compose up -d
docker compose down
docker compose restart app

# View logs
docker compose logs -f app
docker compose logs -f scheduler
docker compose logs -f queue
```

### Development Commands

```bash
# Start/stop services
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml down

# View logs
docker compose -f docker-compose.dev.yml logs -f app

# Access database
docker compose -f docker-compose.dev.yml exec db psql -U fabriku -d fabriku
```

### Database Access

```bash
# Access PostgreSQL from host machine
psql -U fabriku -d fabriku

# Or using pgAdmin, DBeaver, etc.
```

### Application Commands

```bash
# Run artisan commands
docker compose exec app php artisan demo:reset
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:cache

# Access container shell
docker compose exec app bash

# Check scheduler is running
docker compose logs -f scheduler
```

## Demo Data Auto-Reset

The scheduler service automatically runs the demo reset command every hour:
- Resets all demo tenant data to initial state
- Keeps demo environments clean and consistent
- Configured in `routes/console.php`

To manually reset demo data:
```bash
docker compose exec app php artisan demo:reset
```

## Production Deployment

### 1. Update Environment

```bash
# Set production values in .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Ensure database and Redis hosts are correct
DB_HOST=your-postgres-host
REDIS_HOST=your-redis-host
```

### 2. Build Production Image

```bash
docker compose build --no-cache
```

### 3. Deploy with SSL

Use a reverse proxy like Nginx or Traefik for SSL termination.

### 4. Backup Strategy

```bash
# Backup PostgreSQL database
pg_dump -U fabriku fabriku > backup-$(date +%Y%m%d).sql

# Or from remote host
pg_dump -h your-postgres-host -U fabriku fabriku > backup.sql
```

## Troubleshooting

### Container won't start
```bash
# Check logs
docker compose logs app

# Remove and rebuild
docker compose down
docker compose up --build
```

### Permission errors
```bash
# Fix storage permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Database connection issues
```bash
# Test PostgreSQL connection from host
psql -h localhost -U fabriku -d fabriku

# Test Redis connection
redis-cli ping

# Check container can reach host
docker compose exec app ping host.docker.internal
```

### Scheduler not running
```bash
# Check scheduler logs
docker compose logs -f scheduler

# Manually test scheduler
docker compose exec scheduler php artisan schedule:list
docker compose exec scheduler php artisan demo:reset
```

### Redis connection failed
```bash
# Test Redis from container
docker compose exec app php artisan tinker
>>> Redis::ping();

# Or check Redis host
docker compose exec app nc -zv host.docker.internal 6379
```

## Development vs Production

### Development Setup
- Set `APP_DEBUG=true`
- Set `FORCE_MIGRATE=true` for auto migrations
- Use hot reload with `docker compose watch` (Docker Compose 2.22+)

### Production Setup
- Set `APP_DEBUG=false`
- Set `FORCE_MIGRATE=false`
- Use optimized composer: `--no-dev --optimize-autoloader`
- Enable OPcache in PHP
- Use proper SSL certificates
- Set up monitoring and logging

## Resource Requirements

**Minimum:**
- CPU: 2 cores
- RAM: 2GB
- Disk: 5GB

**Recommended:**
- CPU: 4 cores
- RAM: 4GB
- Disk: 10GB

## Scaling

### Add more queue workers
```yaml
# In docker-compose.yml, modify queue service:
deploy:
  replicas: 3
```

### Use external database
```bash
# Point DB_HOST to external database server
DB_HOST=your-db-server.com
```

### Load balancing
Deploy multiple app containers behind a load balancer (Nginx, HAProxy, or cloud LB).
